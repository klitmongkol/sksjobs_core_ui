# ใช้ Image พื้นฐานที่มี PHP
FROM php:8.2-fpm

# ติดตั้ง Dependencies ที่จำเป็น
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    # ติดตั้ง Node.js v20 และ NPM
    # ซึ่งจะแก้ปัญหาเรื่อง version mismatch กับ Vite
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring zip gd

# 🌟🌟🌟 เพิ่มโค้ดแก้ไข PHP INI สำหรับ CLI/FPM เป็น 500M 🌟🌟🌟
# การสร้างไฟล์ .ini ใน conf.d เป็นวิธีมาตรฐานที่ใช้งานได้ถาวร
RUN echo "post_max_size = 500M" > /usr/local/etc/php/conf.d/custom-upload.ini \
    && echo "upload_max_filesize = 500M" >> /usr/local/etc/php/conf.d/custom-upload.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/custom-upload.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/custom-upload.ini \
    && echo "date.timezone = Asia/Bangkok" >> /usr/local/etc/php/conf.d/custom-upload.ini

# ติดตั้ง Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# คัดลอกไฟล์ทั้งหมดของโปรเจกต์
COPY . .

# ติดตั้ง PHP และ Node.js dependencies
# ขั้นตอนนี้จะรัน npm install และสร้าง node_modules ให้
RUN composer install
RUN npm install

# ตั้งค่าสิทธิ์การเข้าถึงไฟล์
RUN chown -R www-data:www-data /var/www

# กำหนด Port ที่ Container จะเปิดใช้งาน
EXPOSE 8000

# กำหนดคำสั่งที่จะรันอัตโนมัติเมื่อ Container เริ่มทำงาน
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
