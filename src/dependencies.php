<?php
// DIC configuration

$container = $app->getContainer();

$container[ "database" ] = function ( $c ) {
    $dbInfo = $c->get( 'settings' )[ "database" ];
	$dsn = "mysql:host=" . $dbInfo[ "host" ] . ";dbname=" . $dbInfo[ "name" ] . ";charset=" . $dbInfo[ "charset" ];
    $options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    return new PDO( $dsn, $dbInfo[ "user" ], $dbInfo[ "password" ], $options );
};

$container[ "dbInfo" ] = function ( $c ) {
    $database = $c->get( 'settings' )[ "database" ];
    unset( $database[ "password" ] );
    return $database;
};

$container[ "view" ] = function ( $c ) {
    $settings = $c->get( "settings" )[ "view" ];
    return new Slim\Views\PhpRenderer( $settings[ "template_path" ] );
};

$container[ "logger" ] = function ( $c ) {
    $settings = $c->get( "settings" )[ "logger" ];
    $logger = new Monolog\Logger( $settings[ "name" ] );
    $logger->pushProcessor( new Monolog\Processor\UidProcessor() );
    $logger->pushHandler( new Monolog\Handler\StreamHandler( $settings[ "path" ], $settings[ "level" ] ) );
    return $logger;
};