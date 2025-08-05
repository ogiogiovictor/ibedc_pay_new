<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
           
            // ✅ Disable connection pooling
            'options' => [
                'APPEND' => ';ConnectionPooling=0'
            ],

            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'zone_connection' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST2', 'localhost'),
            'port' => env('DB_PORT2', '1433'),
            'database' => env('DB_DATABASE2', 'forge'),
            'username' => env('DB_USERNAME2', 'forge'),
            'password' => env('DB_PASSWORD2', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

         'data_warehouse' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_DATAWAREHOUSE', 'localhost'),
            'port' => env('DB_PORT_DATAWAREHOUSE', '1433'),
            'database' => env('DB_DATABASE_DATAWAREHOUSE', 'forge'),
            'username' => env('DB_USERNAME_DATAWAREHOUSE', 'forge'),
            'password' => env('DB_PASSWORD_DATAWAREHOUSE', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'ecmi_prod' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_ECMI', 'localhost'),
            'port' => env('DB_PORT_ECMI', '1433'),
            'database' => env('DB_DATABASE_ECMI', 'forge'),
            'username' => env('DB_USERNAME_ECMI', 'forge'),
            'password' => env('DB_PASSWORD_ECMI', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],

        'ibedc_engine' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST4', 'localhost'),
            'port' => env('DB_PORT4', '1433'),
            'database' => env('DB_DATABASE4', 'forge'),
            'username' => env('DB_USERNAME4', 'forge'),
            'password' => env('DB_PASSWORD4', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],


        'middleware' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST4', 'localhost'),
            'port' => env('DB_PORT4', '1433'),
            'database' => env('DB_DATABASE4', 'forge'),
            'username' => env('DB_USERNAME4', 'forge'),
            'password' => env('DB_PASSWORD4', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],


        'middleware2' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_MIDDLEWARE2', 'localhost'),
            'port' => env('DB_PORT_MIDDLEWARE2', '1433'),
            'database' => env('DB_DATABASE_MIDDLEWARE2', 'forge'),
            'username' => env('DB_USERNAME_MIDDLEWARE2', 'forge'),
            'password' => env('DB_PASSWORD_MIDDLEWARE2', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'middleware1' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_MIDDLEWARE1', 'localhost'),
            'port' => env('DB_PORT_MIDDLEWARE1', '1433'),
            'database' => env('DB_DATABASE_MIDDLEWARE1', 'forge'),
            'username' => env('DB_USERNAME_MIDDLEWARE1', 'forge'),
            'password' => env('DB_PASSWORD_MIDDLEWARE1', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

        'castingmvp' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST_VP', 'localhost'),
            'port' => env('DB_PORT_VP', '1433'),
            'database' => env('DB_DATABASE_VP', 'forge'),
            'username' => env('DB_USERNAME_VP', 'forge'),
            'password' => env('DB_PASSWORD_VP', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
