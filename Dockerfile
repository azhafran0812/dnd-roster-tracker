# Menggunakan base image PHP 8.2 dengan server Apache
FROM php:8.2-apache

# Mengaktifkan modul rewrite Apache
RUN a2enmod rewrite

# Menyalin seluruh file dari folder public ke direktori web server
COPY public/ /var/www/html/

# Memberikan hak akses yang tepat pada folder
RUN chown -R www-data:www-data /var/www/html