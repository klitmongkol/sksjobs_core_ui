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
# Dockerfile ที่ดีควรใช้คำสั่งที่ทำงานค้างอยู่ (persistent command)
# แต่เนื่องจากคุณใช้ php artisan serve เป็นหลัก เราจะใช้มันเป็นตัวอย่าง
# หมายเหตุ: สำหรับ Production อาจใช้ Nginx/Apache + php-fpm แทน
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]