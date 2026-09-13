<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Foso ERP Enterprise - Secure Database Configuration Template
 * Tự động đọc thông tin từ Environment Variables hoặc Fallback an toàn
 */

function get_env_val($key, $default = '') {
    $val = getenv($key);
    return ($val !== false && $val !== '') ? $val : (isset($_ENV[$key]) ? $_ENV[$key] : $default);
}

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn'	       => '',
    'hostname'     => get_env_val('DB_HOSTNAME', 'localhost'),
    'username'     => get_env_val('DB_USERNAME', 'root'),
    'password'     => get_env_val('DB_PASSWORD', ''),
    'database'     => get_env_val('DB_DATABASE', 'foso_erp'),
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => FALSE,
    'db_debug'     => (ENVIRONMENT !== 'production'),
    'cache_on'     => FALSE,
    'cachedir'     => '',
    'char_set'     => 'utf8mb4',
    'dbcollat'     => 'utf8mb4_unicode_ci',
    'swap_pre'     => '',
    'encrypt'      => FALSE,
    'compress'     => FALSE,
    'stricton'     => FALSE,
    'failover'     => array(),
    'save_queries' => (ENVIRONMENT !== 'production')
);
