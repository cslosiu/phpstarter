<?
require_once('env_config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
$errorlevel=error_reporting();
error_reporting($errorlevel & ~E_NOTICE);

date_default_timezone_set('Asia/Hong_Kong');

session_start();

//https://stackoverflow.com/questions/10752815/mysqli-get-result-alternative
function get_result( $Statement ) {
    $RESULT = array();
    $Statement->store_result();
    for ( $i = 0; $i < $Statement->num_rows; $i++ ) {
        $Metadata = $Statement->result_metadata();
        $PARAMS = array();
        while ( $Field = $Metadata->fetch_field() ) {
            $PARAMS[] = &$RESULT[ $i ][ $Field->name ];
        }
        call_user_func_array( array( $Statement, 'bind_result' ), $PARAMS );
        $Statement->fetch();
    }
    return $RESULT;
}

function get_connection()
{
    $mysqli = get_mysqli();
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') '
                . $mysqli->connect_error);
    }

    $mysqli->set_charset("utf8");
    return $mysqli;
}

function dbquery($sql,$types = "", $params = array(), $getresult=0) {
    // e.g.dbquery("select..","isss",array(n,s1,s2,s3))
    $con = get_connection();
    $st = $con->prepare($sql);

    if(strlen($types) > 0 && $params)
    {
        $bind_names[] = $types;
        for ($i=0; $i<count($params);$i++)
        {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        $return = call_user_func_array(array($st,'bind_param'),$bind_names);
    }

    $st->execute();
    if ($st->errno != 0) {
        echo "<p>executed, result#: ". $st->errno. " ".$st->error;
        /*
        echo "<pre>";
        var_dump($sql,$types,$params,$st);
        echo "</pre>";
        */
    }
    if ($getresult) {
        $result = get_result($st);
        //echo "<p>get_result";
    }
    else {
        $result = $st->insert_id;
    }
    $st->close();
    $con->close();
    //echo "<p>exit...";
    return $result;
}

// shortcut version to get resultset
function query($sql,$types = "", $params = array()) {
    return dbquery($sql,$types,$params,TRUE);
}

// query and return a scalar value (single row, single col.)
function queryscalar($sql,$types = "", $params = array()) {
    $rs = query($sql,$types,$params);
    if(count($rs)>0) {
        return reset($rs[0]);
    }
    else {
        return '';
    }
}

function hash_pwd($userpwd)  {
    return hash("sha256",$userpwd);
}


$mysqli = get_connection();

?>
