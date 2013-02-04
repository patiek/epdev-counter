<?php global $DB;
// ------------------- //
// FILE DATABASE SETUP //
// ------------------- //
// File Database default filename (default: "fdb"):
$DB['filename'] = "fdb";
// File Database data folder (with trailing slash):
$DB['arcfolder'] = "data/";
/* flock is essential for sites receiving high traffic
	(however, it's recommended that high traffic sites use mysql): */
$DB['flock'] = 1;

// -- Do Not Edit Below This Point -- //

if (eregi("config_filedb.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}