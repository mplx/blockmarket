#!/usr/bin/env bash

# update pkg database
/bin/sed -i 's/us.archive/de.archive/g' /etc/apt/sources.list
/usr/bin/apt-get update

# timezone
/bin/echo "Europe/Berlin" | /usr/bin/tee /etc/timezone
/usr/sbin/dpkg-reconfigure --frontend noninteractive tzdata

# php, curl
/usr/bin/apt-get install -y php5 php5-json
/usr/bin/apt-get install -y curl php5-curl

# mysql
/usr/bin/debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password password root'
/usr/bin/debconf-set-selections <<< 'mysql-server-5.5 mysql-server/root_password_again password root'
/usr/bin/apt-get install -y mysql-server-5.5 php5-mysql
/usr/bin/mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'root';"
/usr/bin/mysql -uroot -proot -e "create database blockmarketdb;"
/bin/sed -i 's/bind-address/#bind-address/g' /etc/mysql/my.cnf
/usr/sbin/service mysql restart

# composer
/usr/bin/curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# apache
/usr/bin/apt-get install -y apache2

/bin/rm -rf /var/www
/bin/ln -fs /vagrant /var/www

/bin/cat <<EOF >>/etc/apache2/sites-available/001-vagrant
<VirtualHost *:80>
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/public
        <Directory /var/www/>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride All
                Order allow,deny
                allow from all
        </Directory>
        ErrorLog ${APACHE_LOG_DIR}/error.log
        LogLevel warn
        CustomLog ${APACHE_LOG_DIR}/access.log combined
		SetEnv DB1_HOST localhost
		SetEnv DB1_USER root
		SetEnv DB1_PASS root
		SetEnv DB1_NAME blockmarketdb
        SetEnv DB1_PORT 3306
</VirtualHost>
EOF

/usr/sbin/a2ensite 001-vagrant
/usr/sbin/a2dissite 000-default
/usr/sbin/a2enmod rewrite

/bin/sed -i 's/display_errors = Off/display_errors = On/g' /etc/php5/apache2/php.ini

/usr/sbin/service apache2 restart

# system
/usr/bin/updatedb
