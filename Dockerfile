# Base image
FROM php:8-apache

# Install required dependencies
RUN apt-get update && \
    apt-get install -y --fix-missing \
    libzip-dev \
    zip \
    unzip \
    nano \
    curl \
    cron \
    dos2unix \
    unixodbc-dev gpg libzip-dev \
    openssh-server

# Install Microsoft SQL Server drivers and PHP extensions
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - \
    && curl https://packages.microsoft.com/config/debian/10/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql17 mssql-tools \
    && pecl install sqlsrv \
    && pecl install pdo_sqlsrv \
    && docker-php-ext-install pdo opcache bcmath zip \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo 'extension=sqlsrv.so' >> "$PHP_INI_DIR/php.ini" \
    && echo 'extension=pdo_sqlsrv.so' >> "$PHP_INI_DIR/php.ini" \
    && a2enmod rewrite

# Install GD extension
RUN apt-get install -y libpng-dev libjpeg-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Extend Apache timeout
RUN echo "Timeout 480" >> /etc/apache2/apache2.conf

# Install Supervisor
RUN apt-get install -y supervisor

# Create a Supervisor configuration directory
RUN mkdir -p /etc/supervisor/conf.d

# Copy Supervisor configuration file into the container directory
COPY supervisor/docker-worker.conf /etc/supervisor/conf.d/docker-worker.conf

# Set working directory
WORKDIR /var/www/html

# Copy app files
COPY . /var/www/html
COPY apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set permissions for the files folder
RUN mkdir -p /var/www/html/public/files
RUN chmod -R 775 /var/www/html/public/files
RUN chown -R www-data:www-data /var/www/html/public/files

# Adjust PHP memory_limit
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory.ini

# Install the required version of Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install dependencies
RUN composer install --prefer-dist --no-interaction

# Set file permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Enable Apache modules
RUN a2enmod rewrite

# Configure cron
RUN touch /var/log/cron.log

# Script file copied into container.
COPY ./start.sh /start.sh

# convert to UNIX style
RUN dos2unix /start.sh

# Giving executable permission to script file.
RUN chmod +x /start.sh

# Configure SSH for root login
RUN mkdir /var/run/sshd && \
    echo 'root:0sp3d4l3' | chpasswd && \
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config

# Generate SSH host keys
RUN ssh-keygen -A

# Expose ports 80 (HTTP) and 22 (SSH)
EXPOSE 80 22

# Do house-keeping
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan optimize && \
    php artisan config:clear && \
    php artisan cache:clear

# Start Apache server, SSH server, cron service, and Supervisor
CMD ["/bin/bash", "-c", "/usr/sbin/sshd && service apache2 start && /start.sh && cron && supervisord -n"]
