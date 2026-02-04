FROM richarvey/nginx-php-fpm:3.1.6
WORKDIR /var/www/html
COPY . .
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV APP_DEBUG false
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build
EXPOSE 80
CMD ["/start.sh"]
