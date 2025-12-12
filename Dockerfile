FROM python:3.11-slim

# -----------------------------
# Небольшие улучшения: unbuffered, не записывать байт-код
# -----------------------------
ENV PYTHONUNBUFFERED=1
ENV PYTHONDONTWRITEBYTECODE=1

WORKDIR /app

# -----------------------------
# Установка системных зависимостей (ffmpeg и базовых сертификатов)
# Используем --no-install-recommends чтобы уменьшить размер образа
# -----------------------------
RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
	   ffmpeg \
	   ca-certificates \
	&& rm -rf /var/lib/apt/lists/*

# -----------------------------
# Копируем зависимости и устанавливаем Python-зависимости
# -----------------------------
COPY requirements.txt ./
RUN pip install --upgrade pip setuptools wheel \
	&& pip install --no-cache-dir -r requirements.txt

# -----------------------------
# Копируем проект
# -----------------------------
COPY . .

# -----------------------------
# Создаём непривилегированного пользователя и даём права на /app
# -----------------------------
RUN adduser --disabled-password --gecos "" botuser \
	&& chown -R botuser:botuser /app

USER botuser

# -----------------------------
# Запуск бота (exec form)
# -----------------------------
CMD ["python", "-u", "bot.py"]
