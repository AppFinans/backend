<?php
function prefixOnKeys ( array $associativeArray, string $prefix ) {
    $oldKeys = array_keys( $associativeArray );
    foreach ( $oldKeys as $oldKey ) {
        $newKeys[] = $prefix . $oldKey;
    }
    $values = array_values( $associativeArray );
    return array_combine( $newKeys, $values );
}

function getTablePrefix ( $pdo, array $dbInfo, string $tableName ) {
    $sql = "SELECT prefix_prefix FROM " . $dbInfo[ "prefix" ] . "prefixes WHERE prefix_table = '" . $tableName . "'";
    foreach ( $pdo->query( $sql ) as $row ) {
        $prefix = $row[ "prefix_prefix" ];
    }
    return $prefix;
}
function hashGen ( array $array ) {
    $dadosConc = "";
    foreach ( $array as $item ) {
        $dadosConc .= $item;
    }
    return password_hash( $dadosConc,  PASSWORD_DEFAULT);
}

function checkSum ( array $array, $float ) {
    return ( array_sum( $array ) === $float )? true : false;
}

function summation ( array $array ) {
    return ( array_sum( $array ) );
}

function mean ( array $array ) {
    return summation($array)/ count($array);
}

function dateTodays ( $date1, $date2 ) {
    $x = explode ( "-", $date1);
    $y = explode ( "-", $date2);
    var_dump($x, $y);
    if($x[1] === $y[1]) {
        return (intval($x[2]) - intval($y[2]));
    }
}

function call_topbar ( $required = false ) {
    if ( $required ) {
        require_once TEMPLATES_URI . "topbar.php";
    } else {
        include_once TEMPLATES_URI . "topbar.php";
    }
}
function call_footer ( $required = false ) {
    if ( $required ) {
        require_once TEMPLATES_URI . "footer.php";
    } else {
        include_once TEMPLATES_URI . "footer.php";
    }
}

function call_head ( $required = false ) {
    if ( $required ) {
        require_once TEMPLATES_URI . "head.php";
    } else {
        include_once TEMPLATES_URI . "head.php";
    }
}

function call_scripts ( $required = false ) {
    if ( $required ) {
        require_once TEMPLATES_URI . "scripts.php";
    } else {
        include_once TEMPLATES_URI . "scripts.php";
    }
}

function call_nav ( $horizontal = true ) {
    $class = "navbar list-unstyled d-flex";
    if ( $horizontal === false ) {
        $class .= " flex-column";
    }
    echo '<ul class="' . $class . '">';
    include TEMPLATES_URI . "nav.php";
    echo '</ul>';
}

function call_navi () {
    $class = "navbar list-unstyled";
    $class .= " d-flex flex-column";
    echo '<ul class="' . $class . '">';
    include TEMPLATES_URI . "navi.php";
    echo '</ul>';
}

function dateToDays2 ( $date, $delimiter = "-" )
{
    $params = explode ( $delimiter, $date);
    $year = intval( $params[0] ) * 365;
    $months = intval ( $params[1] ) * 30;
    $days = intval ( $params[2] );
    return ($year + $months + $days);
}

function timeToMinutes ( $time, $delimiter = ":" )
{
    $params = explode ( $delimiter, $time);
    $hour = intval( $params[0] ) * 60;
    $minute = intval ( $params[1] );
    return ($hour + $minute);
}

function datesDifference ( $before, $after, $withMinutes = false )
{
    $date["before"] = explode(" ", $before)[0];
    $date["after"] = explode(" ", $after)[0];
    $dateDiff = dateToDays2 ($date["after"]) - dateToDays2($date["before"]);
    if( $withMinutes )
    {
        $time["before"] = explode(" ", $before)[1];
        $time["after"] = explode(" ", $after)[1];
        return ( $dateDiff * 24 * 60 + timeToMinutes($time["after"]) - timeToMinutes($time["before"]) );
    } else {
        return $dateDiff;
    }
}
function unPrefix ( $string ) {
    return explode( "_", $string )[ 1 ];
}

function unPrefixAll( $array, $assoc = false ) {
    if ( $assoc ) {
        foreach ( array_keys( $array ) as $value ) {
            $keys[] = unPrefix( $value );
        }
        $noPrefixedValues = array_combine( $keys, array_values( $array ) );
    } else {
        foreach ( $array as $value ) {
            $noPrefixedValues[] = unPrefix( $value );
        }
    }
    return $noPrefixedValues;
}

function hashIt( $string ) 
{
    
}

function setMailer($setFrom, $addReplyTo, $addAddress, $subject)
{
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->SMTPDebug = 2;                                       // Enable verbose debug output
    $mail->isSMTP();                                            // Set mailer to use SMTP
    $mail->Host       = 'email-smtp.us-east-1.amazonaws.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = 'AKIAR5USYREOCXPKF6LJ';                     // SMTP username
    $mail->Password   = 'BIRCK0JwDEeTvmhJ6vqRj3mCeqyji4xKz8z4n6nGe8cl';                               // SMTP password
    $mail->SMTPSecure = 'tls';                                  // Enable TLS encryption, `ssl` also accepted
    $mail->Port       = 587;  
    $mail->setFrom($setFrom, explode("@", $setFrom)[1]);
    $mail->addReplyTo($addReplyTo, explode("@", $addReplyTo));
    $mail->addAddress($addAddress);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    return $mail;
}

function mailBody( string $scope )
{
    switch ( $scope ) {
        case "welcome":
            $html = '<div>';
            $html .=    '<div style="text-align: center;">';
            $html .=       '<img src="https://media.licdn.com/dms/image/C4D0BAQGCWZb8E9Iw_A/company-logo_200_200/0?e=2159024400&v=beta&t=SmI-5wypKGA_HfkWOhO26MxwtrH9JMidLsF2m8x0lEo">';
            $html .=   '</div>'   ;
            $html .=   '<div style="background-color: #359690; text-align: center; padding: 2.5em;">';
            $html .=        '<span style="font-size: 2.2rem; color: #fff;">';
            $html .=           'Faça seu Cadastro';
            $html .=        '</span>';
            $html .=    '</div>';
            $html .=   '<div>';
            $html .=       '<div style="text-align: center; padding-top: 2em;">';
            $html .=       '<div style="margin-top: 2em;">';
            $html .=           'Você recebeu um convite para aproveitar as vantagens do sistema Orça Agora!<br>';
            $html .=            'Clique no link abaixo e aproveitar as vantagens.';
            $html .= "<a style='display: block; padding: 1em;' href='http://orcaagora.com.br/#getUser'>http://orcaagora.com.br/#getUser</a>";
            $html .=        '</div>';
            $html .=        '</div>';
            $html .=   ' </div>';
            $html .=   '<div style="flex-column m-auto">';
            $html .=      '<div style="padding: 1em; color: #0a0; text-align: center;">';
            $html .=      '<span>Abraços, equipe orçaagora.com.br</span>';
            $html .=     ' </div>';
            $html .= ' </div>';
            $html .=  '</div>'; 
        break;
        case "Verification":
            $html    = '<div>';
            $html    .=    '<div style="text-align: center;">';
            $html    .=       '<img src="https://media.licdn.com/dms/image/C4D0BAQGCWZb8E9Iw_A/company-logo_200_200/0?e=2159024400&v=beta&t=SmI-5wypKGA_HfkWOhO26MxwtrH9JMidLsF2m8x0lEo">';
            $html    .=   '</div>'   ;
            $html    .=   '<div style="background-color: #359690; text-align: center; padding: 2.5em;">';
            $html    .=        '<span style="font-size: 2.2rem; color: #fff;">';
            $html    .=           'Bem-vindo!';
            $html    .=        '</span>';
            $html    .=    '</div>';
            $html    .=   '<div>';
            $html    .=       '<div style="text-align: center; padding-top: 2em;">';
            $html    .=       '<div style="margin-top: 2em;">';
            $html    .=           'Você se cadastrou para ter uma conta no orçaagora!';
            $html    .=            'Agora é só clicar no link abaixo e aproveitar as vantagens.';
            $html    .= "<a style='display: block; padding: 1em;' href='$link'>$link</a>";
            $html    .=        '</div>';
            $html    .=        '</div>';
            $html    .=   ' </div>';
            $html    .=   '<div style="flex-column m-auto">';
            $html    .=      '<div style="padding: 1em; color: #0a0; text-align: center;">';
            $html    .=      '<span>Abraços, equipe orçaagora.</span>';
            $html    .=     ' </div>';
            $html    .= ' </div>';
            $html    .=  '</div>';
            break;
            case "support":
            $html    = $body['message'];
            break;
            case "Quotation":
            $html = "Hello world";
            break;
    }

    return $html;
}
