#!/bin/bash

# Update dependencies
sudo apt-get update && apt-get upgrade

# Install nginx
nginx=stable
sudo add-apt-repository ppa:nginx/$nginx
sudo apt-get update
sudo apt-get install nginx

# Install php
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install php7.4 php7.4-pgsql php7.4-fpm php7.4-xml php7.4-gd php7.4-opcache php7.4-mbstring zip unzip php7.4-zip php7.4-cli -y

# Install composer
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# After download project
# chown -R www-data:www-data <name>/
# chmod -R 775 <name>/
