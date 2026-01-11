<?php
$serverName = "localhost"; 
$connectionOptions = [
    "Database" => "AccidenteFabrica", 
    "Uid" => "sa", 
    "PWD" => "1706"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
