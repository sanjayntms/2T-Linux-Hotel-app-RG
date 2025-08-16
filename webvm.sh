#!/bin/bash
# webvm.sh - Setup Nginx + PHP on Web VM
# Check php version if site is not working and then check /etc/nginx/sites-available/default. It changes on different version of Ubuntu.
set -e

# Install Nginx and PHP
sudo apt update -y
sudo apt install -y nginx php-fpm php-mysql

# Enable and start Nginx
sudo systemctl enable nginx
sudo systemctl start nginx

# Configure Nginx default site for PHP
sudo tee /etc/nginx/sites-available/default > /dev/null <<'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;

    root /var/www/html;
    index hotel.php index.php index.html index.htm;

    server_name _;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Reload Nginx config
sudo nginx -t
sudo systemctl reload nginx
