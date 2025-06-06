<?php
define('BASEPATH', true);
if (!function_exists('db_prefix')) {
    function db_prefix() { return ''; }
}
if (!class_exists('App_Model')) {
    class App_Model {
        public function __construct() {}
    }
}
require_once __DIR__ . '/../models/Planocontas_model.php';
