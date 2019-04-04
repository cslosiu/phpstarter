<?php

function get_filebasedir($instance_id) {
    return "/somepath/$instance_id";
}

function get_mysqli() {
    return new mysqli('host', 'user', 'password', 'database');
}

?>
