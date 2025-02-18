
FROM ehewon/ezwork-ai-php

# 设置工作目录
WORKDIR /var/www/ezwork/

COPY ./ /var/www/ezwork/

RUN chmod -R 777 storage

RUN composer install
RUN php artisan migrate

# 暴露 PHP-FPM 默认端口
EXPOSE 9000

# 启动 PHP-FPM
CMD ["php-fpm"]
