FROM php:8.2-cli

COPY . /var/www/html
WORKDIR /var/www/html

ENV PORT 10000
EXPOSE $PORT

CMD ["php", "-S", "0.0.0.0:10000", "-t", "."]
