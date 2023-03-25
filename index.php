<?php

declare(strict_types=1);

define('MAIN_PATH', __DIR__);

spl_autoload_register(function ($class) {
    include_once __DIR__ . "/controller/api/v1/$class.php";
});

set_error_handler("ErrorHandler::handleError");
set_exception_handler('ErrorHandler::handleException');

header('Access-Control-Allow-Origin: *');
header('Content-type: application/json; charset=UTF-8');

$route = explode('/', $_SERVER['REQUEST_URI']);

$class = ucfirst($route[2]) . 'Controller';
if (!class_exists($class)) {
    http_response_code(404);
    exit;
}

$id = $route[3] ?? null;

$controller = new $class();
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);
