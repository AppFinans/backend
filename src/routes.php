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

// 
// 
// USER INTERFACE
// 
// 
$app->map( [ "GET", "POST" ], "/login", function ( Request $request, Response $response, array $args ) {
    if ( $request->isGet() ) {
        return $this->view->render( $response, "login.php", $args );
    }
    $secretKey = '6LdrHKQUAAAAACwLU_5sYce9bnvVvBSKmeFNUieg';
    $captcha = $_POST['g-recaptcha-response'];
    if( ! $captcha ){
        return $response->withRedirect( BASENAME . "/login?no-captcha=1" );
    }
    $connection = $this->database;
    $db = $this->dbInfo;
    $body = $request -> getParsedBody();
    $userName = $body["userName"];
    $passwordCon = $body["password"];
    $requiredFields = ["account_password", "account_userName"];
    $sql = "SELECT ".implode(",", $requiredFields)." FROM " . $db[ "prefix" ] . "accounts WHERE account_userName = '{$userName}'";
    foreach( $connection->query($sql) as $row ) {
        $account = $row;
    }
    if ( empty( $account ) ) {
        return $response->withRedirect( BASENAME . "/login?no-user=1" );
    }
    $tablePrefix = getTablePrefix($connection, $this->dbInfo, "people");
    if( password_verify($passwordCon, $account['account_password']) ) {
        $requiredFields = ["account_id", "account_personId"];
            $sql = "SELECT ".implode(",", $requiredFields)." FROM {$db["prefix"]}accounts WHERE account_userName = '{$userName}'";
                foreach( $connection->query($sql) as $row ) {
                    $personData = $row;
                }
                $_SESSION['user']['id'] = $personData['account_id'];
                $_SESSION['user']['person'] = $personData;
                
                $hashKeys = array ($userName, $passwordCon);
                $token = hashGen( $hashKeys );
                $accId = $personData['account_id'];
                $_SESSION["user"]["token"] = $token;
                // rever expires. Deve entrar a data de expiração do token para ser comparada em /user
                // alternativamente, date pode ser a data de login e comparar com date + $this->token[ 'time' ] em /user
                $date = date('Y/m/d h:i:s', time() );
                $sql = "INSERT INTO oag_accesstokens (oauth_accessToken, oauth_accountId, oauth_expires) VALUES ('{$token}', '{$accId}', '{$date}')";
                $connection->exec( $sql );
                return $response->withRedirect( BASENAME . "/user/{$userName}/");
    } else {

        if( $passwordCon === $account['account_password'] ) {
            $requiredFields = ["account_id", "account_personId"];
            $sql = "SELECT ".implode(",", $requiredFields)." FROM {$db["prefix"]}accounts WHERE account_userName = '{$userName}'";
            foreach( $connection->query($sql) as $row ) {
                $personData = $row;
            }
            $_SESSION['user']['id'] = $personData['account_id'];
            $_SESSION['user']['person'] = $personData;
            
            $hashKeys = array ($userName, $passwordCon);
            $token = hashGen( $hashKeys );
            $accId = $personData['account_id'];
            $_SESSION["user"]["token"] = $token;
            // rever expires. Deve entrar a data de expiração do token para ser comparada em /user
            // alternativamente, date pode ser a data de login e comparar com date + $this->token[ 'time' ] em /user
            $date = date('Y/m/d h:i:s', time() );
            $sql = "INSERT INTO oag_accesstokens (oauth_accessToken, oauth_accountId, oauth_expires) VALUES ('{$token}', '{$accId}', '{$date}')";
            $connection->exec( $sql );
            return $response->withRedirect( BASENAME . "/user/{$userName}/");
        } else {
            return $response->withRedirect( BASENAME . "/login?no-password=1" );
        }
    }
} );

$app->get( '/logout', function(Request $request, Response $response, array $args )  {
    session_destroy();
    return $response->withRedirect( BASENAME . "/login");
} );

$app->get( '/user[/{userName}[/{params:.*}]]', function ( Request $request, Response $response, array $args ) {

    $currentRoute = "/" . $request->getAttribute( "params", "" );
    $connection = $this->database;
    
    // from middleware 'user'
    $user = $request->getAttribute( "userData" );

    // get current page
    $sql = "SELECT * FROM " . $this->dbInfo[ "prefix" ] . "navigation WHERE nav_scope = '{$user[ "scope" ]}' AND nav_url = '{$currentRoute}'";
    $pdoStatement = $connection->prepare( $sql );
    $pdoStatement->execute();
    $page = $pdoStatement->fetch();
    if ( empty( $page ) ) {
        $page[ "file" ] = "404.php";
    } else {
        $page = unPrefixAll( $page, true );
    }
    
    $sql = "SELECT * FROM " . $this->dbInfo[ "prefix" ] . "navigation WHERE nav_scope = '{$user[ "scope" ]}' ORDER BY nav_order";
    foreach ( $connection->query( $sql ) as $navItem ) {
        $user[ "nav" ][] = unPrefixAll( $navItem, true );
    }
    $user[ "page" ] = $page;
    $user[ "json" ] = json_encode( $user );
    
    return $this->view->render( $response, $page[ "file" ], $user );

} )->add( $mw[ "user" ] );

$app->map( [ "POST" ], "/services/update", function( Request $request, Response $response, array $args ){
    $connection = $this->database;
    $body = $request->getParsedbody();
    $i = 0;
    $sql = "SELECT * FROM oag_accounts WHERE account_scope='provider' AND account_services='%$body[name]%'";
    foreach( $sql as $row )
    {
        $update = "UPDATE oag_accounts SET account_services='$body[id]' WHERE account_services = '%$body[name]%'";
        $i++;
        $response = $i;
    }
    return $response;
});

$app->get( '/[{page}]', function( Request $request, Response $response, array $args ) {
    $navUrl = "/" . $request->getAttribute( "page", "" );
    $connection = $this->database;
    $sql = "SELECT * FROM oag_navigation WHERE nav_scope = 'guest' AND nav_url = '{$navUrl}'";

    $pdoStatement = $connection->prepare( $sql );
    $pdoStatement->execute();
    $page = $pdoStatement->fetch();

   if ( empty( $page ) ) {
        $page[ "file" ] = "404.php";
    } else {
        $page = unPrefixAll( $page, true );
    }

    return $this->view->render( $response, $page[ "file" ], $args );
} );
