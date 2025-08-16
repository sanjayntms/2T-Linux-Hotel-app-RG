#!/bin/bash
# db.sh - Setup MySQL on DB VM

set -e

# Install MySQL
sudo apt update -y
sudo apt install -y mysql-server

# Start and enable MySQL
sudo systemctl enable mysql
sudo systemctl start mysql

# Secure setup & create DB, user and table
MYSQL_ROOT_PASSWORD="MyRootPass123!"
MYSQL_USER="sampleuser"
MYSQL_PASS="SamplePass123!"
DB_NAME="sampledb"

# Set root password and allow local root login without prompt
sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$MYSQL_ROOT_PASSWORD'; FLUSH PRIVILEGES;"

# Create DB and user (run only if DB doesn't exist)
sudo mysql -u root -p$MYSQL_ROOT_PASSWORD -e "
CREATE DATABASE IF NOT EXISTS $DB_NAME;
CREATE USER IF NOT EXISTS '$MYSQL_USER'@'%' IDENTIFIED BY '$MYSQL_PASS';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$MYSQL_USER'@'%';
FLUSH PRIVILEGES;
"

# Create hotel_guests table
sudo mysql -u root -p$MYSQL_ROOT_PASSWORD $DB_NAME -e "
CREATE TABLE IF NOT EXISTS hotel_guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    check_in DATETIME NOT NULL,
    check_out DATETIME
);
"

# Allow remote connections (bind-address)
sudo sed -i "s/bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/mysql.conf.d/mysqld.cnf
sudo systemctl restart mysql

echo "? DB setup complete. MySQL root password: $MYSQL_ROOT_PASSWORD, user: $MYSQL_USER, password: $MYSQL_PASS"