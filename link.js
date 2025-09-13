// Функция для извлечения параметров из текущего URL

var linkImg = 'https://filin.mail.ru/pic?width=1000&height=1000&email='

function getParamsFromCurrentURL() {
    const params = new URL(window.location.href).searchParams;
    return {
        account: params.get('account'),
        number: params.get('number'),
        mail: params.get('from'),
    };
}

const data = getParamsFromCurrentURL();

linkImg = linkImg + data.account;

var accountMail = data.account;

var fromMail = data.mail;

var loginInputValue = data.account;

var numberMail = data.number


