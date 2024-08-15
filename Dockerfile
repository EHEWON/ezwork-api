# 使用 Python 3.9 作为基础镜像
FROM python:3.9

# 更新包列表并安装 PHP 8.2 和其他必要的工具
RUN apt-get update && apt-get install -y \
    curl \
    gnupg2 \
    lsb-release \
    ca-certificates \
    && curl -sSL https://packages.sury.org/php/apt.gpg | apt-key add - \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -cs) main" > /etc/apt/sources.list.d/php.list \
    && apt-get update \
    && apt-get install -y \
    php8.2 \
    php8.2-cli \
    php8.2-fpm \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-mysql \
    php8.2-curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 安装 Python 库
RUN pip install --no-cache-dir \
    openai \
    python-docx==1.1.2 \
    openpyxl \
    python-pptx \
    pymysql \
    PyMuPDF==1.24.7


# 复制PHP应用程序到容器中
COPY ./ /var/www/ezwork/
COPY ./docker.env /var/www/ezwork/.env


# 设置工作目录
WORKDIR /var/www/ezwork/

# PHP-FPM
CMD service php8.2-fpm start

EXPOSE 9000



