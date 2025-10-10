# نبدأ بصورة PHP
FROM php:8.2-cli

# نحدد مكان العمل جوه الكونتينر
WORKDIR /app

# نثبت الأدوات الأساسية وامتدادات PHP اللي Laravel محتاجها
RUN apt-get update && apt-get install -y \
    unzip git libzip-dev libpng-dev libonig-dev libxml2-dev \
 && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd

# نضيف Composer (مدير الحزم الخاص بـ PHP)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ننسخ كل ملفات المشروع جوه الكونتينر
COPY . .

# نثبت الباكدجات
RUN composer install --no-dev --optimize-autoloader

# نخلي storage و bootstrap/cache لهم صلاحيات
RUN chmod -R 777 storage bootstrap/cache

# نعرض البورت اللي السيرفر هيشتغل عليه
EXPOSE 8080

# أمر التشغيل
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
