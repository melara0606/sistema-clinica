<?php

use Slim\Views\PhpRenderer;

$container = $app->getContainer();

// view renderer
$container['view'] = function ($c) {
	$settings = $c->get('settings')['renderer'];
	$view = new \Slim\Views\Twig($settings['template_path']);
	$basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
	return $view;
};

$container["baseUrl"] = function() {
	return "http://localhost/clinica/";
};

$container["dir"] = function() {
	return __DIR__;
};

// monolog
$container['logger'] = function ($c) {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	return $logger;
};

$container["session"] = function($c){
	return new \SlimSession\Helper;
};

$container['db'] = function($c){
	$db = new MysqliDb (Array (
		'host' => 'localhost',
		'username' => 'root',
		'password' => '',
		'db'=> 'clinica-db',
		'port' => 3306,
		'charset' => 'utf8')
	);
	return $db;
};

$container['flash'] = function($c){
	return new \Slim\Flash\Messages();
};

$container['renderer'] = function($c){
	return new PhpRenderer("./templates");
};

$container['auth'] = function ($c) {
    return new \Auths\Auth;
};

$container['datatables'] = function ($c) {
		return new \Auths\MysqlDatatable;
};
