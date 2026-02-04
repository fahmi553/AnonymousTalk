FROM node:20-alpine AS build-stage
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build
FROM richarvey/nginx-php-fpm:3.1.6
WORKDIR /var/www/html
COPY . .
COPY --from=build-stage /app/public/build ./public/build
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN composer install --no-dev --optimize-autoloader
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV APP_DEBUG false

EXPOSE 80
