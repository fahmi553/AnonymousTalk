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
RUN cp nginx-site.conf /etc/nginx/sites-available/default.conf
RUN chown -R www-data:www-data storage bootstrap/cache
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts
RUN rm -f bootstrap/cache/*.php
ENV WEBROOT=/var/www/html/public
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV SESSION_DRIVER=cookie

EXPOSE 80
