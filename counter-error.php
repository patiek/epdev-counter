<?php

if(is_numeric($error))
{
	MySQL_Errors($error);
}
else
{
	Text_Errors($error);
}

function MySQL_Errors($err)
{
	$MDB = new Main_Database();
	$type = "MySQL";
	switch($err)
	{
		case 1049 : $error = "The database name you have specified in mysql_config.php does not exist. Please correct the database name.";
		break;

		case 1146 : $error = "MySQL table not found in database specified. The initial mysql data for the counter could not be found. Attempting to install default database ...<br><br>";
		Report_Error("MySQL", $error);
		$error ="";
		Install_MySQL_Tables();
		/*
		else
		{
		$error = "Database data could not be installed. You must run counter-install.php again. However, this file currently does not exist, as you have already deleted it. You will have to retrieve the file from the original zip file containing this counter. If you have deleted that file, you can download it again from <a href=http://www.ep-dev.com>http://www.ep-dev.com</a>.<br><br>As an alternative for more advanced users, you can load the file dbtables.sql into your mysql database.";
		}
		*/
		break;

		case 1054 : $error = "One or more table columns not found! Please run the upgrade-v3.php script that can be found in the /upgrade/ folder that came with the counter.";
		break;

		case 1045 : $error = "Username and/or password for mysql database are incorrect. Please correct them in mysql_config.php";
		break;

		default : $error = mysql_error();
	}

	Report_Error("MySQL", $error);
}

function Text_Errors($err)
{
	$type = "";
	switch($err)
	{
		case "install_exists" : $error = "The file counter-install.php still exists. If you have not visited counter-install.php in your browser yet please do so now. After doing so you must delete this file for your counter to work as the file is a security risk.";
		break;

		case "image_404" : $error = "The path to your images for your counter is incorrect. Please edit counter.php and ensure your ['Images_Dir'] and ['Image_ext'] variables are set correctly.";
		break;

		case "fileopen" : $error = "There was an error opening up the text database files. Please ensure they exist and are chmodded to 666. By default, text database files are located in the /data/ folder.";
		$type = "File DB";
		break;

		case "flock" : $error = "flock has failed to initiate. This is usually caused by a system that does not support flock. To fix this error set flock to = 0";
		$type = "File DB";
		break;
	}

	Report_Error($type, $error);
}

function Install_MySQL_Tables()
{
	global $DB, $OPTION;

	$MDB = new Main_Database();

	$handle = fopen($OPTION['Absolute_Path']."install/dbtables.sql", 'rb');
	$i=0;
	while($current_line = fscanf($handle, "%[^;];"))
	{
		$current_line[0] = str_replace("ep_", $DB['ext']."_", $current_line[0]);
		$result[$i] = $MDB->query($current_line[0]);
		if ($result[$i])
			echo $current_line[0]."<br>";
		$i++;
	}
	if (!in_array(false, $result))
	{
		echo "<br><b>MySQL Install Successful. Please <a href=\"javascript:history.go()\">refresh this page</a> to see counter!</b>";
	}
}

function Report_Error($type, $error_text)
{
	if (!empty($error_text))
	{
		echo "<br><big><b>Counter: $type Error:</b></big><br>".$error_text;
	}
}
