<?php

    $host = $_SERVER['WP_HOST'];
    $protocol = $_SERVER['PROTOCOL'] = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https' : 'http';

    $full_root_path = '/var/www/html';

    if (getenv('WP_SSL')) {
        $_SERVER['HTTPS'] = 1;
        $protocol = 'https';
    }

    $url = "https://digitalgxp.com/blog";
    define('WP_HOME', "$url/");
    define('WP_SITEURL', "$url/");

    define('WP_CONTENT_DIR', "$full_root_path/wp-content");
    define('WP_CONTENT_URL', "$url/wp-content");

    define( 'DB_NAME', getenv('MYSQL_DATABASE') );

    /** Database username */
    define( 'DB_USER', getenv('MYSQL_USER') );

    /** Database password */
    define( 'DB_PASSWORD', getenv('MYSQL_PASSWORD') );

    /** Database hostname */
    define( 'DB_HOST', getenv('DB_HOST') );

    /** Database charset to use in creating database tables. */
    define( 'DB_CHARSET', getenv('DB_CHARSET') );

    /** The database collate type. Don't change this if in doubt. */
    define( 'DB_COLLATE', '' );

    $table_prefix = 'wp_';