<?php

$mw[ "api" ] = function ( $request, $response, $next ) {
    $db = $this->dbInfo;
    $path = $request->getUri()->getPath();
    $params = explode( "/", $path );
    $index = array_search( "api", $params );
    $tableName = $params[ $index + 1 ];
    $tablePrefix = getTablePrefix( $this->database, $db, $tableName );
    $request = $request->withAttribute( "tablePrefix", $tablePrefix );

    $allowedTables = array (
        "caixas",
        "contas_bancarias",
        "empresas",
        "pessoas",
        "hist_nome",
        "histpadrao",
        "lancamentos",
        "subcontas",
        "plano_contas",
        "usuarios",
        "navigation"
    );

    $allowedTools = array(
        "count"
    );

    if ( $request->isPost() || $request->isPut() ) {
        $requestBody = $request->getParsedBody();
        $prefixedBody = prefixOnKeys( $requestBody, $tablePrefix );
        $request = $request->withParsedBody( $prefixedBody );
    }
    if ( in_array( $tableName, $allowedTables ) || in_array( $tableName, $allowedTools ) ) {
        $response = $next( $request, $response );
    } else {
        $response = $response->withJson( [ "status" => "error", "message" => "Route not found" ] );
    }
    return $response;
};

$mw[ "hashIt" ] = function ( $request, $response, $next ) {
    if ( $request->isPost() || $request->isPut() ) {
        $body = $request->getParsedBody();
        foreach ( $body as $key => $value ) {
            if ( strpos( $key, "password" ) !== false ) {
                $newBody[ $key ] = password_hash( $value,  PASSWORD_DEFAULT );
            } else {
                $newBody[ $key ] = $value;
            }
        }
        $request = $request->withParsedBody( $newBody );
    }
    $response = $next( $request, $response );
    return $response;
};

$mw["noPass"] = function ( $request, $response, $next ) {
    $response = $next( $request, $response );
            foreach ( $response as $key => $value ) {
                if ( strpos( $key, "password" ) !== false ) {
                    unset($value);
                    $response = $response;
                } else {
                    $response = $response;
                }
            }
    return $response;
};