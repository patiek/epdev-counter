<?php
// --------------------------------------------
// | The EP-Dev Counter script        
// |                                           
// | Copyright (c) 2002-2003 EP-Dev.com :           
// | This program is distributed as free       
// | software under the GNU General Public     
// | License as published by the Free Software 
// | Foundation. You may freely redistribute     
// | and/or modify this program.               
// |                                           
// --------------------------------------------

global $HTTP_SERVER_VARS;
if (!empty($_ENV['PATH_TRANSLATED']))
{
	$path = str_replace("abs.php", "", $_ENV['PATH_TRANSLATED']);
}
elseif (!empty($_SERVER['PATH_TRANSLATED']))
{
	$path = str_replace("abs.php", "", $_SERVER['PATH_TRANSLATED']);
}
elseif (!empty($HTTP_SERVER_VARS['PATH_TRANSLATED']))
{
	$path = str_replace("abs.php", "", $HTTP_SERVER_VARS['PATH_TRANSLATED']);
}

if (isset($path))
{
	$path = str_replace("\\\\install\\\\", "\\\\", $path);
	$path = str_replace("/install/", "/", $path);
	echo "The absolute path to this directory is:<br><b>".$path."</b><br><br><br> Please set \$OPTION['Absolute_Path'] to equal the above bolded path. For example...<br><br>\$OPTION['Absolute_Path'] = \"".$path."\";";
}
else
{
	echo "Unforunately this script was not able to determine your absolute path. Please contact your host for this information.";
}