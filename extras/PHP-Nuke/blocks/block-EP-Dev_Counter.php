<?php

// --------------------------------------------
// | The EP-Dev Counter script - NUKED       
// |                                           
// | Copyright (c) 2002-2004 EP-Dev.com :           
// | This program is distributed as free       
// | software under the GNU General Public     
// | License as published by the Free Software 
// | Foundation. You may freely redistribute     
// | and/or modify this program.               
// |                                           
// --------------------------------------------

if (eregi("block-EP-Dev_Counter.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}

/*
	NO CONFIGURATION REQUIRED. Just place /counter/ folder in your php-nuke directory (where your
	php-nuke site is located) and place this file (block-EP-Dev_Counter.php) in your the /blocks
	folder of your php-nuke root folder.
*/


// We let the script know how we are calling it.
global $EP_DEV_PHPNUKE_COUNTER_BLOCK;
$EP_DEV_PHPNUKE_COUNTER_BLOCK = true;

// call script
include("counter/counter.php");
global $content;
unset($GLOBALS['content']);

?>