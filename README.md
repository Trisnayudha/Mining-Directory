# Lumen Activity Logging

Proyek ini menyediakan contoh bagaimana mengimplementasikan pencatatan aktivitas pengguna dalam aplikasi Lumen menggunakan Monolog dan sistem antrian untuk logging asinkron.

## Prasyarat

-   PHP >= 7.3
-   Composer
-   Lumen 7.x atau lebih baru
-   Database yang didukung (MySQL, PostgreSQL, dll.)

## Instalasi

1. Clone repositori ini ke mesin lokal Anda:

    ```sh
    git clone https://github.com/username/lumen-activity-logging.git
    cd lumen-activity-logging
    ```

2. Instal dependensi menggunakan Composer:

    ```sh
    composer install
    ```

3. Salin file `.env.example` menjadi `.env` dan sesuaikan pengaturannya:

    ```sh
    cp .env.example .env
    ```

4. Buat kunci aplikasi:

    ```sh
    php artisan key:generate
    ```

5. Atur konfigurasi database di `.env`:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password
    ```

6. Jalankan migrasi untuk membuat tabel yang diperlukan:
    ```sh
    php artisan migrate
    ```

## Menggunakan Sistem Antrian

1. Pastikan Anda telah mengatur koneksi queue di `.env`:

    ```env
    QUEUE_CONNECTION=database
    ```

2. Buat tabel antrian:

    ```sh
    php artisan queue:table
    php artisan migrate
    ```

3. Jalankan worker antrian untuk memproses job yang ada dalam antrian:
    ```sh
    php artisan queue:work
    ```

## Middleware Pencatatan Aktivitas Pengguna

Middleware `LogUserActivity` digunakan untuk mencatat aktivitas pengguna. Middleware ini sudah didaftarkan dalam aplikasi dan dapat diterapkan ke rute yang diinginkan.

### Menambahkan Middleware ke Rute

Tambahkan middleware ke grup rute di `routes/web.php` atau `routes/api.php`:

```php
$router->group(['middleware' => 'log.user.activity'], function () use ($router) {
    $router->get('/example', 'ExampleController@index');
    // Tambahkan rute lain di sini
});
```
