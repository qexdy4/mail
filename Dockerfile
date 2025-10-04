# Используем официальный PHP с встроенным сервером
FROM php:8.2-cli

# Копируем все файлы проекта в контейнер
COPY . /var/www/html

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

RUN a2enmod rewrite

# Переменная PORT задается Render
ENV PORT 10000
EXPOSE $PORT

# Запуск встроенного PHP-сервера
CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
