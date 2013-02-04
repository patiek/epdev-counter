<?php global $DB;
// ------------------------------------------------ //
// MySQL DATABASE SETUP (ignore if not using MySQL) //
// ------------------------------------------------ //
// MySQL database username:
$DB['username'] = "root";
// MySQL database password:
$DB['password'] = "";
// MySQL database name:
$DB['name'] = "ep-dev-counter";
// MySQL database hostname:
$DB['host'] = "localhost";
// MySQL database ext (default is "ep")
$DB['ext'] = "ep";

// -- Do Not Edit Below This Point -- //

if (eregi("config_mysql.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}