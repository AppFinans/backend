<?php
header( "Access-Control-Allow-Origin: *" );
header( "Access-Control-Allow-Headers: Content-Type" );
header( "Access-Control-Allow-Methods: GET, POST, PUT, DELETE" );
//header( "Cache-control: no-cache, no-store, must-revalidate");
//header("Pragma: no-cache");
//header("Expires:0");

use Slim\Http\Request;
use Slim\Http\Response;
use SebastianBergmann\GlobalState\Exception;
use PHPMailer\PHPMailer\PHPMailer;


// 
// 
// API
//
//  
$app->group( '/api', function () use ( $app ) {
    $app->get("/count/{table}", function(Request $request, Response $response, array $args) {
        $queryParams = $request->getQueryParams();
        $connection = $this->database;
        $prefix = getTablePrefix($connection, $this->dbInfo, $args['table']);
        $db = $this->dbInfo;
        $sql = "SELECT count( {$prefix}id ) from oag_{$args['table']}";
        if ( ! empty ( $queryParams ) ) {
            foreach ( $queryParams as $field => $value ) {
                if ( in_array( $field, $haystack) ) {
                    $paramValues = explode(",", $value);
                    foreach ( explode( ",", $value ) as $paramValue ) {
                        if ( ! empty( $paramValue ) ) {
                            $where[] = $field . " LIKE '%" . $paramValue . "%'";
                        }
                    }
                } else {
                    $where[] = $field . " = '" . $value . "'";
                }
            }
            $sql .= " WHERE " . implode( " AND ", $where );
        }
        //ORDER BY
        $x = $connection->prepare($sql);
        $x->execute();
        $sql_result = $x->fetch();
        $response = $response->withJson( [ "count" => $sql_result["count( account_id )"] ] );
        return $response;
    });

    $this->map( [ "POST", "GET" ], "/{param}", function( Request $request, Response $response, array $args ) {
        $db = $this->dbInfo;
        $route = $args['param'];
        $connection = $this->database;
        $tablePrefix = $request->getAttribute( "tablePrefix" );
        if( $request->isGet() ){
            $queryParams = $request->getQueryParams();
            $statement = "SELECT * FROM " . $db[ "prefix" ] . $route;
            $haystack = ['quotation_providerIds'];
            $orderBy = "";
            if ( ! empty ( $queryParams ) ) {
                foreach ( $queryParams as $field => $value ) {
                    if( $field !== "order_by" ) {
                        if ( in_array( $field, $haystack) ) {
                            $paramValues = explode(",", $value);
                            foreach ( explode( ",", $value ) as $paramValue ) {
                                if ( ! empty( $paramValue ) ) {
                                    $where[] = $field . " LIKE '%" . $paramValue . "%'";
                                }
                            }
                        } else {
                            $where[] = $field . " = '" . $value . "'";
                        }
                    } else {
                        $orderBy = " ORDER BY " . $value . " ASC";
                    }
                }
                $statement .= " WHERE " . implode( " AND ", $where ) . $orderBy;
            }
            try {
                if ( in_array( $field, $haystack) ) {
                    foreach ( $connection->query( $statement ) as $row ) {
                        $idsInQueryParam = explode( ",", $request->getQueryParam( "quotation_providerIds" ) );
                        $rowProvIds = explode( ",", $row[ "quotation_providerIds" ] );
                        foreach( $idsInQueryParam as $id ) {
                           if (in_array($id, $rowProvIds))
                           {
                                $idFilter = array_intersect($idsInQueryParam, $rowProvIds);
                                $output[] = $row;
                           }
                        }
                    }
                } else {
                    foreach ( $connection->query( $statement ) as $row ) {
                        $output[] = $row;
                    }
                }

            } catch ( PDOExcepetion $e ) {
                $output[ "code" ] = $e->getCode();
                $output[ "error" ] = ( $e->getCode() == 23000 )? "Informação já existente no banco de dados." : $e->getMessage();
            } finally {
                $response = $response->withJson( $output );
            }
        }
        if ( $request->isPost() ) {
            $body = $request->getParsedBody();
            $keys = implode( ", ", array_keys( $body ) );
            $values = implode( "', '", array_values( $body ) );

            $statement = "INSERT INTO " . $db[ "prefix" ] . $route . " ( " . $keys . " ) VALUES ( '" . $values . "' )";
            try {
                $connection->prepare( $statement );
                $affectedRows = $connection->exec( $statement );
                if ( $affectedRows !== 0 ) {
                    $sql = "SELECT * FROM {$db[ "prefix" ]}{$route} WHERE {$tablePrefix}id=(";
                    $sql .= " SELECT max({$tablePrefix}id) FROM {$db[ "prefix" ]}{$route}";
                    $sql .= " )";
                    $pdoStatement = $connection->query( $sql );
                    $output = $pdoStatement->fetch();
                }
            } catch ( PDOExcepetion $e ) {
                $output[ "code" ] = $e->getCode();
                $output[ "error" ] = ( $e->getCode() == 23000 )? "Informação já existente no banco de dados." : $e->getMessage();
            } finally {
                $response = $response->withJson( $output );
            }
        }
        
        return $response;
    } );
    
    $this->map( [ "PUT", "DELETE", "GET" ], "/{param}/{id}", function( Request $request, Response $response, array $args ) {
        
        $db = $this->dbInfo;
        $connection = $this->database;
        $tablePrefix = $request->getAttribute( "tablePrefix" );
        
        if( $request->isPut() ){
            $body = $request->getParsedBody();
            foreach ( $body as $key => $value ) {
                $pairs[] = $key . " = '" . $value . "'";
            }
            $sql = "UPDATE " . $db[ "prefix" ] . $args[ "param" ] . " SET ". implode( ", ", $pairs ) ." WHERE " . $tablePrefix . "id = " . $args[ "id" ];
            try {
                foreach ( $connection->query( $sql ) as $row ) {
                    $output = $row;
                }
                $updated = true;
            }
            catch ( PDOExcepetion $e ) {
                $output = $e->getMessage();
                $updated = false;
            }
            if( $updated ) {
                $sql = "SELECT * FROM " . $db[ "prefix" ] . $args[ "param" ] . " WHERE " . $tablePrefix . "id = " . $args[ "id" ];
                foreach ( $connection->query( $sql ) as $row ) {
                    $output = $row;
                }
            } else {
                $output = NULL;                
            }
            
            $response = $response->withJson( $output );
        }
        
        if ( $request->isDelete() ) {
            $sql = "DELETE FROM " . $db[ "prefix" ] . $args[ "param" ] . " WHERE " . $tablePrefix . "id = " . $args[ "id" ];
            $output = ( $this->database->exec( $sql ) ) ? [ "status" => "ok" ]: [ "status" => "error" ];
            $response = $response->withJson( $output );
        }
        if ( $request->isGet() ) {
            $sql = "SELECT * FROM " . $db[ "prefix" ] . $args[ "param" ] . " WHERE " . $tablePrefix . "id = " . $args[ "id" ];
            foreach ( $connection->query( $sql ) as $row ) {
                $output = $row;
            }
            $response = $response->withJson( $output );
        }
            
        return $response;
    } );
    
} )->add($mw[ "api" ])->add( $mw[ "hashIt" ] );
