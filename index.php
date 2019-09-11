<?php

header( "Access-Control-Allow-Origin: *" );
header("Access-Control-Allow-Headers: Content-Type");
header( "Access-Control-Allow-Methods: GET, POST, PUT, DELETE" );

define( "IN_DEVELOPMENT", true );
define( "IN_LOCAL", false );

require_once "functions.php";
require_once "definitions.php";
require VENDOR_URI . "autoload.php";

session_start();

// Instantiate the app
$settings = require SOURCE_URI . "settings.php";
$app = new \Slim\App($settings);

// Set up dependencies
require SOURCE_URI . "dependencies.php";

// Register middleware
require SOURCE_URI . "middleware.php";

// Register routes
require SOURCE_URI . "routes.php";



// Run app
$app->run();
