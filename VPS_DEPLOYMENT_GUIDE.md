# VPS Deployment Guide (PHP Backend)

This guide provides step-by-step instructions for deploying the Course Management System PHP backend to a VPS (Ubuntu/Debian recommended).

## 1. Server Prerequisites

Ensure your server meets the following requirements:
- **Operating System:** Ubuntu 22.04 LTS or newer.
- **PHP:** 8.3 or higher.
- **Extensions:** `mysqli`, `mbstring`, `json`, `openssl`, `curl`.
- **Web Server:** Apache (with `mod_rewrite` enabled).
- **Composer:** Latest stable version.

## 2. Server Preparation

### Install PHP 8.3 and Extensions
```bash
sudo apt update
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.3 php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip
```

### Install Apache
```bash
sudo apt install apache2
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## 3. Application Setup

### Clone the Repository
Navigate to your desired directory (e.g., `/var/www/`):
```bash
cd /var/www
git clone <your-repo-url> course-management-system
cd course-management-system
```

### Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Permissions
Set the correct ownership and permissions for the web server (assuming `www-data` is the user):
```bash
sudo chown -R www-data:www-data /var/www/course-management-system
sudo chmod -R 755 /var/www/course-management-system
# Ensure upload directories are writable
sudo chmod -R 775 PL/public/uploads
```

## 4. Apache Configuration

Create a new VirtualHost configuration:
```bash
sudo nano /etc/apache2/sites-available/course-cms.conf
```

Add the following configuration (replace `your-domain.com` with your actual domain or IP):
```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/course-management-system/PL/public

    <Directory /var/www/course-management-system/PL/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/course-cms-error.log
    CustomLog ${APACHE_LOG_DIR}/course-cms-access.log combined
</VirtualHost>
```

Enable the site and restart Apache:
```bash
sudo a2ensite course-cms.conf
sudo a2dissite 000-default.conf
sudo systemctl restart apache2
```

## 5. Database Setup

1.  Log in to your MySQL/MariaDB server.
2.  Create the database: `CREATE DATABASE course_management_system;`
3.  Create a user and grant permissions:
    ```sql
    CREATE USER 'cms_user'@'localhost' IDENTIFIED BY 'your_password';
    GRANT ALL PRIVILEGES ON course_management_system.* TO 'cms_user'@'localhost';
    FLUSH PRIVILEGES;
    ```
4.  The application will automatically attempt to create tables on the first request via `InitialCreate.php`.

## 6. SSL Configuration (Let's Encrypt)

It is highly recommended to use HTTPS:
```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d your-domain.com
```

## 7. Verification

- Access `http://your-domain.com/api/health` to check if the API is responsive.
- Access `http://your-domain.com/api/testdatabase` to verify database connectivity.

## Important Notes

- **PHP.ini:** Ensure `variables_order = "EGPCS"` is set in `/etc/php/8.3/apache2/php.ini` so `$_ENV` works correctly.
- **SSL Cert:** The project expects `DigiCertGlobalRootG2.crt.pem` in the project root for secure database connections (common for Azure MySQL). If your VPS database doesn't require this, you might need to adjust `DAL/Database/Database.php`.
