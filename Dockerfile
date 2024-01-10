FROM php:8.3.1-fpm

# Set environment variables
ENV USER=www-data
ENV GROUP=www-data

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    cron \
    supervisor \
    libpq-dev 

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip pdo pdo_pgsql intl

# Get latest Composer
COPY --from=composer:lts /usr/bin/composer /usr/local/bin/composer

# Install Supervisor and create log directory
RUN mkdir -p /var/log/supervisor && mkdir -p /var/run/supervisor
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/config/tambahan.ini /usr/local/etc/php/conf.d/tambahan.ini
COPY docker/config/tambahan-fpm.ini /usr/local/etc/php-fpm.d/tambahan.ini


RUN set -uex; \
    apt-get update; \
    apt-get install -y ca-certificates curl gnupg; \
    mkdir -p /etc/apt/keyrings; \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
     | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg; \
    NODE_MAJOR=20; \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" \
     > /etc/apt/sources.list.d/nodesource.list; \
    apt-get -qy update; \
    apt-get -qy install nodejs;

# Setup working directory
WORKDIR /app

# Laravel scheduler cronjob
RUN echo "* * * * * ${USER} /usr/local/bin/php /app/artisan schedule:run >> /dev/null 2>&1"  >> /etc/cron.d/laravel-scheduler
RUN chmod 0644 /etc/cron.d/laravel-scheduler

# Create User and Group
# RUN groupadd -g 1000 ${GROUP} && useradd -u 1000 -ms /bin/bash -g ${GROUP} ${USER}

# Grant Permissions
RUN chown -R ${USER}:${GROUP} /app /var/log/supervisor /var/run/supervisor

# Copy permissions to the selected user
COPY --chown=${USER}:${GROUP} . .

# Expose port 9000
EXPOSE 9000

# Define the command to run
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
