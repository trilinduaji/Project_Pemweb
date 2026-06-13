<?php


define('BASE_PATH', __DIR__);


require_once BASE_PATH . '/core/App.php';
App::init(BASE_PATH);

require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/View.php';
require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/config/session.php';

View::setPath(BASE_PATH . '/app/Views');


require_once BASE_PATH . '/routes/web.php';


$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$route  = trim($_GET['route'] ?? '', '/');

Router::dispatch($method, $route);

