<?php

// 
// UTILITIES
// 
//  IN_DEVELOPMENT true if on development, false if on production
//
define( "DS", DIRECTORY_SEPARATOR );
// 
// BASIC SETTINGS
// 
//  _URI constant points to absolute path on server
//  _PATH constant points to path to be appended on url browser's client
// 
define( "SITE_TITLE", "App Finans" );
define( "ROOT_PATH", DS );
define( "ROOT_URI", __DIR__ . DS );

// Private
define( "VENDOR_URI", ROOT_URI . "vendor" . DS );
define( "SOURCE_URI", ROOT_URI . "src" . DS );

// Public
if ( IN_DEVELOPMENT ) {
    define( "PUBLIC_PATH", ROOT_PATH );
} else {
    define( "PUBLIC_PATH", ROOT_PATH );
}
define( "PUBLIC_URI", ROOT_URI );

define( "TEMPLATES_PATH", ROOT_PATH . "templates" . DS );
define( "TEMPLATES_URI", ROOT_URI . "templates" . DS );

define( "DOCS_PATH", ROOT_PATH . "docs" . DS );
define( "DOCS_URI", ROOT_URI . "docs" . DS );

// 
// ASSETS
// 
//  _URI constant points to absolute path on server
//  _PATH constant points to path to be appended on url browser's client
//

define( "USERS_PATH", PUBLIC_PATH . "users" . DS);
define( "USERS_URI", PUBLIC_URI . "users" . DS);

define( "STYLES_PATH", PUBLIC_PATH . "styles" . DS );
define( "STYLES_URI", PUBLIC_URI . "styles" . DS );

define( "SCRIPTS_PATH", PUBLIC_PATH . "js" . DS );
define( "SCRIPTS_URI", PUBLIC_URI . "js" . DS );

// Routes
define( "API_PATH", "/api" );
define( "ADM_PATH", "/adm" );
define( "VALIDATION_PATH", "/validation" );

if ( IN_DEVELOPMENT ) {
    $serverName = $_SERVER[ "SERVER_NAME" ];
    $protocol = ( empty( $_SERVER[ "HTTPS" ] ) && IN_DEVELOPMENT ) ? "http" : "https";
    if ( $_SERVER[ "SERVER_PORT" ] !== "80" && $_SERVER[ "SERVER_PORT" ] !== "443" ) {
        $serverName .= ":" . $_SERVER[ "SERVER_PORT" ];
    }
    if ( IN_LOCAL ) {
        $serverName = $_SERVER[ "SERVER_NAME" ] . "/appfinans";
    }
} else {
    $protocol = "https";
    $serverName = "appfinans.com.br";
}
define( "BASENAME", $protocol . "://" . $serverName );
