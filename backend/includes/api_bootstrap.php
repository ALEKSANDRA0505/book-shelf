<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/db_connection_fixed.php';
require_once __DIR__ . '/api_helpers.php';
setup_cors();
function api_response_exit($status, $data) {
    send_json_response($status, $data);
    
    ob_end_flush();
    exit;
}
register_shutdown_function(function() {
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}); 