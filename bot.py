import os
import subprocess
import uuid
import json
import concurrent.futures
import logging
from telebot import TeleBot, types
from dotenv import load_dotenv
from flask import Flask, request, abort
from urllib.parse import urlparse
from threading import Lock
import re
import time
import threading

# -----------------------------
# Настройка логирования
# -----------------------------
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    handlers=[logging.StreamHandler()]
)

# -----------------------------
load_dotenv()
BOT_TOKEN = os.getenv("BOT_TOKEN")
if not BOT_TOKEN:
    print("ОШИБКА: BOT_TOKEN не найден в .env")
    exit(1)

bot = TeleBot(BOT_TOKEN)

DOWNLOAD_DIR = "downloads"
os.makedirs(DOWNLOAD_DIR, exist_ok=True)

executor = concurrent.futures.ThreadPoolExecutor(max_workers=20)
active_users = {}
lock = Lock()

MAX_DURATION = 180       # 3 минуты
FREE_MAX_HEIGHT = 1080   # бесплатно до 1080p

url_storage = {}         # {short_id: url}

# ===== SUBSCRIPTION CONFIG =====
# Список каналов, на которые должны подписаться пользователи
# Структура: (channel_id_or_username, is_private, invite_link_or_username, display_name)
# is_private=True: проверяем подписку через tracking set (пользователь жмёт инвайт-ссылку)
# is_private=False: проверяем через getChatMember API
REQUIRED_CHANNELS = [
    ("@qexdy_test1", False, "@qexdy_test1", "Публичный канал"),
    ("-1003496022123", True, "https://t.me/+jmnsXgplikkyMGFk", "Приватный канал"),
    # Для приватного канала используйте инвайт-ссылку:
    # ("your_private_channel_id", True, "https://t.me/+xxxxxxxxx", "Приватный канал"),
]

# Отслеживаем подписки на приватные каналы: {user_id: set([channel_id, ...])}
private_subscriptions = {}  # {user_id: {channel_id_1, channel_id_2, ...}}
private_lock = Lock()

# Отслеживаем ручные подтверждения подписки (пользователь переслал сообщение из канала)
manual_subscriptions = {}  # {user_id: {channel_identifier, ...}}
manual_lock = Lock()

def generate_short_id():
    return str(uuid.uuid4())[:8]

# -----------------------------
# Фоновая очистка (необязательно)
# -----------------------------
def cleanup_old_urls():
    while True:
        time.sleep(3600)
        # Можно добавить очистку старых URL через 1 час
        pass

threading.Thread(target=cleanup_old_urls, daemon=True).start()


def get_video_info(url: str):
    cmd = [
        "yt-dlp",
        "--dump-single-json",
        "--no-playlist",
        "--no-warnings",
        "--ignore-config",
        "--age-limit", "99",
        "--extractor-args", "youtube:skip=hls,dash",
        "--quiet",                    
        url
    ]

    try:
        # Capture stderr to get detailed error info for diagnostics (useful on Render)
        result = subprocess.run(
            cmd,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=60
        )

        if result.returncode != 0:
            # Log stderr for debugging (cut to reasonable length)
            err = (result.stderr or "").strip()
            logging.error(f"yt-dlp вернул ошибку для {url}: {err[:1000]}")
            return None

        json_str = result.stdout.strip()

        if "{" not in json_str:
            logging.error(f"yt-dlp вывел не JSON: {json_str[:200]}")
            return None

        try:
            data = json.loads(json_str)
            return data
        except json.JSONDecodeError:
  
            def _extract_last_json(s: str):
                last = None
                depth = 0
                start = None
                for i, ch in enumerate(s):
                    if ch == '{':
                        if depth == 0:
                            start = i
                        depth += 1
                    elif ch == '}':
                        depth -= 1
                        if depth == 0 and start is not None:
                            last = s[start:i+1]
                            start = None
                return last

            last_json = _extract_last_json(json_str)
            if not last_json:
                logging.error(f"JSON всё ещё битый и не удалось извлечь объект. Вывод: {json_str[:500]}")
                return None

            try:
                data = json.loads(last_json)
                return data
            except json.JSONDecodeError as e:
                logging.error(f"Не удалось распарсить извлечённый JSON: {e}\nВывод-фрагмент: {last_json[:500]}")
                return None
    except Exception as e:
        logging.error(f"Критическая ошибка get_video_info: {e}")
        return None
# -----------------------------
def get_available_resolutions(info):
    formats = info.get("formats", [])
    heights = set()
    for f in formats:
        h = f.get("height")
        try:
            if h:
                heights.add(int(h))
        except Exception:
            continue
    # Возвращаем отсортированный список по возрастанию (начинаем с низких разрешений)
    return sorted(heights)


def format_duration(seconds):
    """Return human-readable duration from seconds: H:MM:SS or M:SS."""
    try:
        s = int(round(float(seconds)))
    except Exception:
        return "0:00"
    h = s // 3600
    m = (s % 3600) // 60
    sec = s % 60
    if h:
        return f"{h}:{m:02d}:{sec:02d}"
    else:
        return f"{m}:{sec:02d}"


def build_subscription_message(user_id):
    """Собирает универсальное сообщение со статусом подписок для пользователя.
    Это сообщение используется в /subscribe, после ручной верификации и при блокировке.
    """
    status = get_user_subscription_status(user_id)
    lines = ["📢 <b>Требуемые подписки</b>", ""]

    for display_name, is_subscribed, is_private, link in status:
        # Формируем корректный URL для ссылки: если дан @username — делаем https://t.me/username
        if link and isinstance(link, str):
            if link.startswith('http'):
                url = link
            else:
                url = 'https://t.me/' + link.lstrip('@')
        else:
            url = None

        # Текст строки: имя канала превращаем в ссылку
        name_with_link = f"<a href='{url}'>{display_name}</a>" if url else f"{display_name}"

        if is_subscribed:
            lines.append(f"✅ {name_with_link}")
        else:
            lines.append(f"❌ {name_with_link}")

        lines.append("")

    lines.append("ℹ️ Если автоматическая проверка недоступна, перешлите любое сообщение из канала сюда — бот подтвердит подписку вручную.")
    lines.append("")
    lines.append("Подпишитесь на все каналы и попробуйте снова! Для подсказок используйте /subscribe")
    return "\n".join(lines)


def is_user_subscribed_to_public_channel(user_id, channel_id_or_username):
    """Проверяет подписку пользователя на публичный канал через API.
    channel_id_or_username может быть числовым ID (-100...) или @username.
    Требует, чтобы бот был администратором канала или канал был публичным.
    """
    try:
        member = bot.get_chat_member(channel_id_or_username, user_id)
        # Проверяем статус: 'member', 'administrator', 'creator', 'restricted' (с правами), 'left', 'kicked'
        return member.status in ['member', 'administrator', 'creator', 'restricted']
    except Exception as e:
        # Иногда API возвращает 400 "member list is inaccessible" для каналов,
        # в этом случае разрешаем ручную верификацию через пересланное сообщение.

        # Проверяем, есть ли ручная метка подписки
        with manual_lock:
            user_set = manual_subscriptions.get(user_id, set())
            if channel_id_or_username in user_set or channel_id_or_username.lstrip('@') in user_set:
                return True
        return False


def mark_user_manually_subscribed(user_id, channel_identifier):
    with manual_lock:
        if user_id not in manual_subscriptions:
            manual_subscriptions[user_id] = set()
        manual_subscriptions[user_id].add(channel_identifier)
    logging.info(f"Пользователь {user_id} вручную подтверждён для {channel_identifier}")


def is_user_subscribed_to_private_channel(user_id, channel_id):
    """Проверяет подписку на приватный канал через tracking set."""
    with private_lock:
        if user_id not in private_subscriptions:
            return False
        return channel_id in private_subscriptions[user_id]


def mark_user_subscribed_to_private(user_id, channel_id):
    """Отмечает пользователя как подписанного на приватный канал."""
    with private_lock:
        if user_id not in private_subscriptions:
            private_subscriptions[user_id] = set()
        private_subscriptions[user_id].add(channel_id)
        logging.info(f"Пользователь {user_id} отмечен как подписанный на приватный канал {channel_id}")


def get_user_subscription_status(user_id):
    """Возвращает статус подписок пользователя: list of (channel_display_name, is_subscribed, is_private, link)."""
    status = []
    for channel_id, is_private, link, display_name in REQUIRED_CHANNELS:
        if is_private:
            subscribed = is_user_subscribed_to_private_channel(user_id, channel_id)
        else:
            subscribed = is_user_subscribed_to_public_channel(user_id, channel_id)
        status.append((display_name, subscribed, is_private, link))
    return status


def user_subscribed_to_all(user_id):
    """Проверяет, подписан ли пользователь на все требуемые каналы."""
    status = get_user_subscription_status(user_id)
    return all(subscribed for _, subscribed, _, _ in status)


# Обработчик пересланных сообщений: пользователь может переслать сообщение из канала
# чтобы подтвердить подписку, если автоматическая проверка недоступна.
@bot.message_handler(func=lambda m: getattr(m, 'forward_from_chat', None) is not None)
def handle_forwarded_from_channel(msg):
    try:
        fchat = msg.forward_from_chat
        if not fchat:
            return
        # Попробуем сопоставить канал пересылки с REQUIRED_CHANNELS
        matched = False
        for channel_id, is_private, link, display_name in REQUIRED_CHANNELS:
            # Сопоставление по username (@name) или по id
            try:
                if isinstance(channel_id, str) and channel_id.startswith('@'):
                    if getattr(fchat, 'username', None) and fchat.username.lower() == channel_id.lstrip('@').lower():
                        # Если канал приватный — отмечаем в private_subscriptions,
                        # иначе — в manual_subscriptions
                        if is_private:
                            mark_user_subscribed_to_private(msg.from_user.id, channel_id)
                        else:
                            mark_user_manually_subscribed(msg.from_user.id, channel_id)
                        bot.reply_to(msg, f"✅ Подписка подтверждена: {display_name}")
                        matched = True
                        break
                else:
                    # channel_id может быть числовым идентификатором
                    if str(getattr(fchat, 'id', '')) == str(channel_id):
                        if is_private:
                            mark_user_subscribed_to_private(msg.from_user.id, channel_id)
                        else:
                            mark_user_manually_subscribed(msg.from_user.id, channel_id)
                        bot.reply_to(msg, f"✅ Подписка подтверждена: {display_name}")
                        matched = True
                        break
            except Exception:
                continue

        if not matched:
            bot.reply_to(msg, "❌ Не удалось сопоставить пересланное сообщение с требуемыми каналами. Перешлите, пожалуйста, сообщение непосредственно из канала.")
        else:
            # После успешного подтверждения отправляем статус подписки
            user_id = msg.from_user.id
            status_text = build_subscription_message(user_id)
            bot.send_message(user_id, status_text, parse_mode="HTML")
            
            # Если пользователь теперь подписан на все каналы, отправляем сообщение "готово"
            if user_subscribed_to_all(user_id):
                bot.send_message(user_id, "✅ Отлично! Вы подписаны на все требуемые каналы. Теперь вы можете загружать видео.", parse_mode="HTML")
    except Exception as e:
        logging.error(f"Ошибка в handle_forwarded_from_channel: {e}")
        bot.reply_to(msg, "Произошла ошибка при проверке пересланного сообщения.")

# -----------------------------
def download_video(url: str, resolution: int):
    file_id = str(uuid.uuid4())
    output_path = f"{DOWNLOAD_DIR}/{file_id}.mp4"
    format_code = f"bestvideo[height<={resolution}]+bestaudio/best"
    # Добавляем --no-playlist чтобы не скачивать весь плейлист, если ссылка содержит list=
    # И указываем --merge-output-format mp4 — если установлен ffmpeg, аудио и видео будут слиты в mp4.
    cmd = [
        "yt-dlp",
        "--no-playlist",
        "--no-warnings",
        "--ignore-config",
        "-f", format_code,
        "-o", output_path,
        "--merge-output-format", "mp4",
        url
    ]
    try:
        logging.info(f"Скачивание {resolution}p: {url}")
        subprocess.run(cmd, check=True, timeout=300)
        return output_path
    except Exception as e:
        logging.error(f"Ошибка скачивания: {e}")
        return None

# -----------------------------
def process_video(chat_id, url, resolution, user_id):
    try:
        bot.send_message(chat_id, f"Скачиваю видео в {resolution}p...")
        file_path = download_video(url, resolution)
        if not file_path or not os.path.exists(file_path):
            bot.send_message(chat_id, "Ошибка при скачивании видео.")
            return

        with open(file_path, "rb") as video:
            bot.send_video(chat_id, video, timeout=60)

        os.remove(file_path)
        logging.info(f"Видео отправлено пользователю {user_id}")

    except Exception as e:
        bot.send_message(chat_id, "Не удалось отправить видео.")
        logging.error(f"Ошибка отправки: {e}")
    finally:
        with lock:
            active_users.pop(user_id, None)

# -----------------------------
def ask_quality_thread(chat_id, url, user_id):
    info = get_video_info(url)
    if not info:
        bot.send_message(chat_id, "⚠️ Не удалось получить информацию о видео. Проверьте ссылку и попробуйте снова.")
        with lock:
            active_users.pop(user_id, None)
        return

    duration = info.get("duration", 0)
    if duration > MAX_DURATION:
        duration_str = format_duration(duration)
        bot.send_message(chat_id, f"⏱️ Видео слишком длинное ({duration_str}). Максимум {int(MAX_DURATION//60)} минут.")
        with lock:
            active_users.pop(user_id, None)
        return

    available_heights = get_available_resolutions(info)
    if not available_heights:
        bot.send_message(chat_id, "⚠️ Не найдено доступных качеств.")
        with lock:
            active_users.pop(user_id, None)
        return

    # Генерируем кнопки динамически на основе реальных доступных высот
    # Отфильтруем разрешения ниже 240p (если есть) — пользователь просил начинать с 240p
    heights = [h for h in available_heights if h >= 240]
    if not heights:
        # Если нет разрешений >=144 (редкий случай), используем все доступные
        heights = available_heights

    short_id = generate_short_id()
    url_storage[short_id] = url

    markup = types.InlineKeyboardMarkup(row_width=3)
    buttons = []
    # Сортируем по возрастанию: 240,360,... — удобнее пользователю выбирать от низкого к высокому
    for h in sorted(heights):
        # Показываем все доступные качества как доступные для скачивания
        buttons.append(types.InlineKeyboardButton(f"{h}p", callback_data=f"{short_id}|{h}"))

    if buttons:
        markup.add(*buttons)

    title = info.get("title", "Без названия")
    uploader = info.get("uploader", "Неизвестно")
    duration_str = format_duration(duration) if duration else "0:00"
    # try to extract domain from the provided url (source of the video)
    try:
        domain = urlparse(url).netloc.lower()
        if domain.startswith("www."):
            domain = domain[4:]
    except Exception:
        domain = "unknown"

    description = (
        f"🎬 <b>{title}</b>\n"
        f"👤 {uploader}\n"
        f"🌐 Источник: {domain}\n"
        f"⏱️ Длительность: {duration_str}\n\n"
        "Выберите качество ниже ⬇️"
    )

    thumbnail = info.get("thumbnail")
    if thumbnail:
        bot.send_photo(chat_id, thumbnail, caption=description, reply_markup=markup, parse_mode="HTML")
    else:
        bot.send_message(chat_id, description, reply_markup=markup, parse_mode="HTML")

    logging.info(f"Превью отправлено | ID: {short_id} | {title}")

# -----------------------------
# Обработчики
# -----------------------------
@bot.message_handler(commands=["start", "help"])
def send_welcome(msg):
    bot.reply_to(msg, "👋 Привет! Отправь ссылку на видео (до 3 минут).\nДля проверки подписок используйте /subscribe")

@bot.message_handler(commands=["subscribe"])
def show_subscription_status(msg):
    """Команда /subscribe показывает статус подписок и ссылки на требуемые каналы."""
    user_id = msg.from_user.id
    msg_text = build_subscription_message(user_id)
    bot.send_message(msg.chat.id, msg_text, parse_mode="HTML")

@bot.message_handler(func=lambda m: True)
def handle_link(msg):
    # Игнорируем пересланные сообщения — их обрабатывает handle_forwarded_from_channel
    if msg.forward_from_chat is not None:
        return
    
    text = msg.text.strip()
    user_id = msg.from_user.id

    # Игнорируем команды
    if text.startswith("/"):
        return

    # Простая проверка на URL
    if not any(x in text.lower() for x in ["http", "youtube.com", "youtu.be", "tiktok.com"]):
        bot.reply_to(msg, "🔗 Пожалуйста, отправь ссылку на видео.")
        return

    # Проверяем, подписан ли пользователь на все требуемые каналы
    if not user_subscribed_to_all(user_id):
        msg_text = build_subscription_message(user_id)
        bot.send_message(msg.chat.id, msg_text, parse_mode="HTML")
        return

    with lock:
        if user_id in active_users:
            bot.send_message(msg.chat.id, "⏳ Подожди, ты уже скачиваешь видео.")
            return
        active_users[user_id] = True

    logging.info(f"Пользователь {user_id} отправил: {text}")
    executor.submit(ask_quality_thread, msg.chat.id, text, user_id)

# -----------------------------
@bot.callback_query_handler(func=lambda call: True)
def callback_quality(call):
    try:
        # Сразу подтверждаем callback, чтобы телеграм не показывал загрузку бесконечно
        try:
            bot.answer_callback_query(call.id)
        except Exception:
            pass
        short_id, height_str = call.data.split("|", 1)
        height = int(height_str)

        if short_id not in url_storage:
            bot.answer_callback_query(call.id, "Ссылка устарела. Отправь видео заново.")
            return

        url = url_storage[short_id]
        user_id = call.from_user.id

        # Раньше ограничивали скачивание по FREE_MAX_HEIGHT — теперь разрешаем любое доступное качество

        # Если сообщение содержит фото/медиа, у него нет 'text' — нужно редактировать caption.
        # Попытка редактировать caption, иначе fallback на edit_message_text для текстовых сообщений.
        try:
            bot.edit_message_caption(
                f"⬇️ Скачиваю в {height}p... Пожалуйста, подождите.",
                call.message.chat.id,
                call.message.message_id
            )
        except Exception as e_caption:
            logging.debug(f"edit_message_caption failed: {e_caption}; trying edit_message_text")
            try:
                bot.edit_message_text(
                    f"⬇️ Скачиваю в {height}p... Пожалуйста, подождите.",
                    call.message.chat.id,
                    call.message.message_id
                )
            except Exception as e_text:
                logging.error(f"Не удалось обновить сообщение (caption/text): {e_text}")

        executor.submit(process_video, call.message.chat.id, url, height, user_id)

    except Exception as e:
            logging.error(f"Ошибка в callback: {e}")
            bot.answer_callback_query(call.id, "Ошибка!", show_alert=True)

def _log_updates(updates):
    for u in updates:
        try:
            logging.debug(f"RAW UPDATE: {u}")
        except Exception as e:
            logging.debug(f"Ошибка логирования апдейта: {e}")

bot.set_update_listener(_log_updates)


# Обработчик для отслеживания присоединения пользователя к приватному каналу
@bot.message_handler(content_types=['new_chat_members'])
def handle_user_joined_channel(msg):
    """Отслеживаем, когда пользователь присоединяется к приватному каналу через инвайт-ссылку."""
    try:
        for new_member in msg.new_chat_members:
            if not new_member.is_bot:
                user_id = new_member.id
                chat_id = msg.chat.id
                # Проверяем, если chat_id находится в REQUIRED_CHANNELS как приватный
                for channel_id, is_private, _, _ in REQUIRED_CHANNELS:
                    if is_private and (str(chat_id) in str(channel_id) or channel_id == str(chat_id)):
                        mark_user_subscribed_to_private(user_id, channel_id)
                        logging.info(f"Пользователь {user_id} присоединился к приватному каналу {channel_id}")
                        break
    except Exception as e:
        logging.error(f"Ошибка в handle_user_joined_channel: {e}")

# Альтернатива: используйте мой_chat_member для более точной обработки
@bot.message_handler(content_types=['my_chat_member'])
def handle_my_chat_member(msg):
    """Обработка события присоединения бота или пользователя к чату/каналу."""
    try:
        member = msg.my_chat_member
        user_id = member.user.id
        chat_id = msg.chat.id
        
        if member.new_chat_member and member.new_chat_member.status in ['member', 'restricted']:
            # Пользователь присоединился или восстановил доступ
            for channel_id, is_private, _, _ in REQUIRED_CHANNELS:
                if is_private and (str(chat_id) in str(channel_id) or channel_id == str(chat_id)):
                    mark_user_subscribed_to_private(user_id, channel_id)
                    logging.info(f"Отмечена подписка {user_id} на приватный канал {channel_id}")
                    break
    except Exception as e:
        logging.error(f"Ошибка в handle_my_chat_member: {e}")

# -----------------------------
# Webhook (Flask) app to support hosting on platforms without shell access (e.g., Render free)
app = Flask(__name__)


# Healthcheck
@app.route("/", methods=["GET"])
def index():
    return "OK"


# Telegram webhook endpoint (path is configurable via WEBHOOK_PATH)
WEBHOOK_BASE = os.environ.get("WEBHOOK_URL") or os.environ.get("RENDER_EXTERNAL_URL")
WEBHOOK_PATH = os.environ.get("WEBHOOK_PATH") or f"/webhook/{os.environ.get('WEBHOOK_TOKEN') or BOT_TOKEN[-20:]}"


@app.route(WEBHOOK_PATH, methods=["POST"])
def telegram_webhook():
    if request.headers.get('content-type') != 'application/json':
        abort(400)
    try:
        update = types.Update.de_json(request.stream.read().decode('utf-8'))
        bot.process_new_updates([update])
    except Exception as e:
        logging.error(f"Ошибка при обработке webhook update: {e}")
        abort(500)
    return "", 200


# При старте модуля — если задан WEBHOOK_BASE, регистрируем webhook у Telegram
if WEBHOOK_BASE:
    full_url = WEBHOOK_BASE.rstrip("/") + WEBHOOK_PATH
    try:
        bot.remove_webhook()
    except Exception:
        pass

    try:
        ok = bot.set_webhook(full_url, allowed_updates=["message", "callback_query", "my_chat_member"])
        if ok:
            logging.info(f"Webhook установлен: {full_url}")
        else:
            logging.error(f"Не удалось установить webhook: {full_url}")
    except Exception as e:
        logging.error(f"Ошибка при установке webhook: {e}")
else:
    logging.warning("WEBHOOK_URL/RENDER_EXTERNAL_URL не задан — бот будет работать через polling (не рекомендуется для Render)")


if __name__ == '__main__':
    # Если запускаем скрипт напрямую (локальная разработка), хотим автоматически стартовать
    # В режиме webhook (если задан WEBHOOK_BASE) запускаем встроенный Flask сервер
    if WEBHOOK_BASE:
        port = int(os.environ.get('PORT', 5000))
        logging.info(f"Запускаю Flask dev-server на 0.0.0.0:{port} (WEBHOOK_MODE)")
        app.run(host='0.0.0.0', port=port)
    else:
        # По умолчанию — polling для локальной разработки (автозапуск без интерактивности)
        logging.info("WEBHOOK не настроен — запускаю polling (локальный режим)")
        try:
            bot.polling(none_stop=True, allowed_updates=["message", "callback_query", "my_chat_member"])
        except KeyboardInterrupt:
            logging.info("Остановка по сигналу KeyboardInterrupt")
        except Exception as e:
            logging.error(f"Ошибка при polling: {e}")
