# Jiny Admin 설치 및 배포 가이드

## 📋 개요

이 문서는 Jiny Admin 패키지를 설치하고 배포하는 방법을 단계별로 설명합니다. Laravel 프로젝트에 Jiny Admin을 통합하고 설정하는 모든 과정을 다룹니다.

## 🚀 빠른 시작

### 1. 패키지 설치

```bash
composer require jiny/admin
```

### 2. 서비스 프로바이더 등록

```php
// config/app.php
'providers' => [
    // ... 기존 프로바이더들
    
    Jiny\Admin\JinyAdminServiceProvider::class,
],
```

### 3. 마이그레이션 실행

```bash
php artisan migrate
```

### 4. 환경 설정

```bash
php artisan vendor:publish --tag=jiny-admin-config
```

### 5. 기본 관리자 생성

```bash
php artisan admin:user
```

## 📦 상세 설치 가이드

### 시스템 요구사항

#### PHP 요구사항
- **PHP**: 8.1 이상
- **확장 모듈**:
  - BCMath PHP Extension
  - Ctype PHP Extension
  - JSON PHP Extension
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension

#### Laravel 요구사항
- **Laravel**: 10.x
- **데이터베이스**: MySQL 8.0+, PostgreSQL 13+, SQLite 3.35+

#### 웹 서버 요구사항
- **Apache**: 2.4+ (mod_rewrite 활성화)
- **Nginx**: 1.18+
- **Node.js**: 16+ (프론트엔드 빌드용)

### 단계별 설치 과정

#### 1단계: Composer를 통한 패키지 설치

```bash
# 프로젝트 디렉토리로 이동
cd your-laravel-project

# 패키지 설치
composer require jiny/admin

# 의존성 확인
composer install
```

#### 2단계: 서비스 프로바이더 등록

```php
// config/app.php
'providers' => [
    // ... 기존 프로바이더들
    
    /*
     * Package Service Providers...
     */
    Jiny\Admin\JinyAdminServiceProvider::class,
],

'aliases' => [
    // ... 기존 별칭들
    
    /*
     * Package Aliases...
     */
    'Admin' => Jiny\Admin\Facades\Admin::class,
],
```

#### 3단계: 설정 파일 발행

```bash
# 기본 설정 파일 발행
php artisan vendor:publish --tag=jiny-admin-config

# 뷰 파일 발행 (선택사항)
php artisan vendor:publish --tag=jiny-admin-views

# 언어 파일 발행 (선택사항)
php artisan vendor:publish --tag=jiny-admin-lang

# 플래그 아이콘 발행 (선택사항)
php artisan vendor:publish --tag=jiny-admin-flags
```

#### 4단계: 데이터베이스 마이그레이션

```bash
# 마이그레이션 실행
php artisan migrate

# 마이그레이션 상태 확인
php artisan migrate:status

# 마이그레이션 롤백 (필요시)
php artisan migrate:rollback
```

#### 5단계: 기본 데이터 시드

```bash
# 기본 데이터 시드 실행
php artisan db:seed --class=JinyAdminSeeder

# 또는 개별 시드 실행
php artisan db:seed --class=AdminCountrySeeder
php artisan db:seed --class=AdminLanguageSeeder
```

#### 6단계: 기본 관리자 계정 생성

```bash
# 대화형으로 관리자 생성
php artisan admin:user

# 또는 직접 생성
php artisan admin:user:create \
    --name="관리자" \
    --email="admin@example.com" \
    --password="secure_password" \
    --type="super"
```

## ⚙️ 환경 설정

### 환경 변수 설정

```env
# .env
# 기본 설정
APP_NAME="Your App Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 데이터베이스 설정
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Jiny Admin 설정
ADMIN_USE_GUARD=true
ADMIN_GUARD_NAME=admin
ADMIN_SESSION_TIMEOUT=120
ADMIN_2FA_ENABLED=true
ADMIN_MAX_LOGIN_ATTEMPTS=5
ADMIN_LOCKOUT_TIME=15

# 보안 설정
ADMIN_ALLOWED_IPS=192.168.1.0/24,10.0.0.0/8
ADMIN_SESSION_SECURE=true
ADMIN_SESSION_HTTP_ONLY=true
ADMIN_SESSION_SAME_SITE=strict

# 메일 설정
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 설정 파일 커스터마이징

```php
// config/admin.php
return [
    'name' => env('ADMIN_NAME', 'Jiny Admin'),
    'prefix' => env('ADMIN_PREFIX', 'admin'),
    
    'auth' => [
        'use_guard' => env('ADMIN_USE_GUARD', true),
        'guard_name' => env('ADMIN_GUARD_NAME', 'admin'),
        'session_timeout' => env('ADMIN_SESSION_TIMEOUT', 120),
        '2fa_enabled' => env('ADMIN_2FA_ENABLED', true),
        'max_login_attempts' => env('ADMIN_MAX_LOGIN_ATTEMPTS', 5),
        'lockout_time' => env('ADMIN_LOCKOUT_TIME', 15),
    ],
    
    'security' => [
        'allowed_ips' => env('ADMIN_ALLOWED_IPS', ''),
        'session_secure' => env('ADMIN_SESSION_SECURE', true),
        'session_http_only' => env('ADMIN_SESSION_HTTP_ONLY', true),
        'session_same_site' => env('ADMIN_SESSION_SAME_SITE', 'strict'),
    ],
    
    'ui' => [
        'theme' => env('ADMIN_THEME', 'default'),
        'sidebar_collapsed' => env('ADMIN_SIDEBAR_COLLAPSED', false),
        'show_footer' => env('ADMIN_SHOW_FOOTER', true),
    ],
];
```

## 🔐 인증 설정

### Guard 기반 인증 설정 (권장)

```php
// config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    
    'admin' => [
        'driver' => 'session',
        'provider' => 'admin_users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    
    'admin_users' => [
        'driver' => 'eloquent',
        'model' => Jiny\Admin\App\Models\AdminUser::class,
    ],
],

'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
    ],
    
    'admin_users' => [
        'provider' => 'admin_users',
        'table' => 'admin_password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
    ],
],
```

### 미들웨어 설정

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... 기존 미들웨어들
    ],
    
    'admin' => [
        'web',
        'auth:admin',
        'admin.session',
        'admin.permission',
    ],
];

protected $routeMiddleware = [
    // ... 기존 미들웨어들
    
    'admin.auth' => \Jiny\Admin\Http\Middleware\AdminAuthMiddleware::class,
    'admin.session' => \Jiny\Admin\Http\Middleware\AdminSessionMiddleware::class,
    'admin.permission' => \Jiny\Admin\Http\Middleware\AdminPermissionMiddleware::class,
    'admin.ip' => \Jiny\Admin\Http\Middleware\AdminIpRestrictionMiddleware::class,
];
```

## 🛣️ 라우트 설정

### 기본 라우트 등록

```php
// routes/web.php
use Jiny\Admin\Routes\AdminRouteServiceProvider;

// Jiny Admin 라우트 등록
AdminRouteServiceProvider::routes();
```

### 커스텀 라우트 설정

```php
// routes/admin.php
<?php

use Illuminate\Support\Facades\Route;
use Jiny\Admin\Http\Controllers\AdminDashboardController;
use Jiny\Admin\Http\Controllers\AdminUserController;

Route::prefix('admin')->name('admin.')->group(function () {
    // 대시보드
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // 관리자 사용자 관리
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::get('/create', [AdminUserController::class, 'create'])->name('create');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::get('/{id}', [AdminUserController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdminUserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{id}', [AdminUserController::class, 'destroy'])->name('destroy');
    });
    
    // 기타 관리자 라우트...
});
```

## 🎨 프론트엔드 설정

### NPM 패키지 설치

```bash
# 프론트엔드 의존성 설치
npm install

# 개발 빌드
npm run dev

# 프로덕션 빌드
npm run build
```

### Tailwind CSS 설정

```javascript
// tailwind.config.js
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './vendor/jiny/admin/resources/**/*.blade.php',
        './vendor/jiny/admin/resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                'admin': {
                    50: '#f0f9ff',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
```

### Vite 설정

```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'vendor/jiny/admin/resources/css/admin.css',
                'vendor/jiny/admin/resources/js/admin.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '~admin': '/vendor/jiny/admin/resources',
        },
    },
});
```

## 🚀 배포 설정

### 프로덕션 환경 설정

```bash
# 환경 설정
cp .env.example .env
php artisan key:generate

# 데이터베이스 설정
php artisan migrate --force

# 캐시 최적화
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 프론트엔드 빌드
npm run build

# 권한 설정
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 웹 서버 설정

#### Apache 설정

```apache
# .htaccess
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

#### Nginx 설정

```nginx
# nginx.conf
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Docker 배포

```dockerfile
# Dockerfile
FROM php:8.1-fpm

# 시스템 패키지 설치
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# PHP 확장 모듈 설치
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composer 설치
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 작업 디렉토리 설정
WORKDIR /var/www

# 애플리케이션 파일 복사
COPY . /var/www

# 의존성 설치
RUN composer install --optimize-autoloader --no-dev

# 권한 설정
RUN chown -R www-data:www-data /var/www

EXPOSE 9000
CMD ["php-fpm"]
```

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: jiny-admin-app
    restart: unless-stopped
    working_dir: /var/www
    volumes:
      - ./:/var/www
      - ./docker/php/local.ini:/usr/local/etc/php/conf.d/local.ini
    networks:
      - jiny-admin

  webserver:
    image: nginx:alpine
    container_name: jiny-admin-nginx
    restart: unless-stopped
    ports:
      - "8000:80"
    volumes:
      - ./:/var/www
      - ./docker/nginx/conf.d/:/etc/nginx/conf.d/
    networks:
      - jiny-admin

  db:
    image: mysql:8.0
    container_name: jiny-admin-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: jiny_admin
      MYSQL_ROOT_PASSWORD: your_root_password
      MYSQL_PASSWORD: your_password
      MYSQL_USER: your_user
    volumes:
      - dbdata:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/my.cnf
    networks:
      - jiny-admin

networks:
  jiny-admin:
    driver: bridge

volumes:
  dbdata:
    driver: local
```

## 🔧 문제 해결

### 일반적인 설치 문제

#### 1. Composer 메모리 부족 오류

```bash
# PHP 메모리 제한 증가
php -d memory_limit=-1 /usr/local/bin/composer require jiny/admin

# 또는 환경 변수 설정
export COMPOSER_MEMORY_LIMIT=-1
composer require jiny/admin
```

#### 2. 권한 문제

```bash
# 저장소 및 캐시 디렉토리 권한 설정
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

#### 3. 마이그레이션 오류

```bash
# 마이그레이션 상태 확인
php artisan migrate:status

# 마이그레이션 롤백
php artisan migrate:rollback

# 마이그레이션 재실행
php artisan migrate
```

#### 4. 라우트 캐시 문제

```bash
# 라우트 캐시 클리어
php artisan route:clear

# 설정 캐시 클리어
php artisan config:clear

# 뷰 캐시 클리어
php artisan view:clear
```

### 로그 확인

```bash
# Laravel 로그 확인
tail -f storage/logs/laravel.log

# 웹 서버 로그 확인 (Apache)
tail -f /var/log/apache2/error.log

# 웹 서버 로그 확인 (Nginx)
tail -f /var/log/nginx/error.log
```

## 📚 추가 리소스

### 유용한 명령어

```bash
# 관리자 사용자 관리
php artisan admin:user:list
php artisan admin:user:create
php artisan admin:user:update
php artisan admin:user:delete

# 시스템 정보 확인
php artisan admin:system:info
php artisan admin:system:health

# 캐시 관리
php artisan admin:cache:clear
php artisan admin:cache:optimize
```

### 지원 및 문의

- **GitHub Issues**: [이슈 트래커](https://github.com/your-repo/jiny-admin/issues)
- **문서**: [공식 문서](../README.md)
- **커뮤니티**: [사용자 포럼](https://forum.your-domain.com)

---

**설치 과정에서 문제가 발생하면 위의 문제 해결 섹션을 참조하거나 GitHub Issues를 통해 문의해주세요.**
