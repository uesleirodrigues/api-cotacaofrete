FROM php:8.2-fpm-alpine

# Instalar pacotes necessários
RUN apk add --no-cache --update libzip-dev libpng-dev freetype-dev \
    icu-dev gmp-dev postgresql-dev mariadb-dev

# Instalar extensões PHP necessárias
RUN docker-php-ext-install -j$(nproc) zip gd intl gmp pdo pdo_pgsql pdo_mysql

# Instalar o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Definir o diretório de trabalho principal
WORKDIR /app

# Copiar os arquivos do Composer primeiro para otimizar cache
COPY composer.json ./

# Instalar dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# Copiar todo o código da API
COPY . /app

# Definir o diretório de trabalho da API
WORKDIR /app/public

# Expor a porta 80
EXPOSE 80

# Iniciar o servidor PHP embutido com roteador
CMD ["php", "-S", "0.0.0.0:80", "-t", "/app/public", "/app/public/router.php"]
