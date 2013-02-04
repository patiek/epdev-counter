<?php
$OPTION['Absolute_Path'] = "../";

if($_GET['dbtype'] == "mysql")
{
	require_once("../classes/mysqldb.php");
	$MDB = new Main_Database();
}
else
{
	require_once("../classes/filedb.php");
	$MDB = new Main_Database();
}

require_once("../config_mysql.php");
require_once("../config_filedb.php");

if(file_exists("upgrade.lock"))
{
	die("Upgrade has been locked for security reasons. To unlock, delete the upgrade.lock file in the upgrade folder.");
}

function Main()
{
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	<title>EP-Dev Counter Upgrade</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>

	<body>
	<table width="100%" border="1" cellpadding="2" cellspacing="0" bordercolor="#000000">
	  <tr>
		<td height="85"> 
		  <table width="100%" border="0" cellpadding="0">
			<tr>
			  <td width="223"><a href="http://www.ep-dev.com" target="_blank"><img src="logo.gif" width="223" height="69" border="0"></a></td>
			  <td><font color="#000066" size="+7" face="Verdana, Arial, Helvetica, sans-serif"> 
				- Upgrade to v3.0</font></td>
			</tr>
		  </table></td>
	  </tr>
	  <tr>
		<td><p>This script will upgrade your old data from 1.x and 2.x versions of 
			EP-Dev Counter to the new format of version 3.0. <b>DO NOT use this upgrade script if you are upgrading from a version of 3.x or later.</b> Before you continue, please ensure that you have already edited the variables in either config_mysql.php or config_filedb.php. To continue, please select 
			which database type you used in the previous version of the EP-Dev Counter:</p>
		  <p>Old Database Type:</p>
		  <form name="form1" method="get" action="upgrade-v3.php">
		  <input name="page" type="hidden" value="ProcessUpgrade-v3">
			<p>
			<input name="dbtype" type="radio" value="mysql" checked>
			MYSQL<br>
			<input type="radio" name="dbtype" value="filedb">
			FILE DB (data files)</p>
		  <p>
			<input type="submit" name="Submit" value="Continue With Upgrade">
		  </p>
		  </form>
		  </td>
	  </tr>
	  <tr>
		<td><div align="center">Copyright &copy; 2003 EP-Dev.com</div></td>
	  </tr>
	</table>
	</body>
	</html>
	<?
}

function File_DB_v2_Name()
{
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	<title>EP-Dev Counter Upgrade</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	</head>

	<body>
	<table width="100%" border="1" cellpadding="2" cellspacing="0" bordercolor="#000000">
	  <tr>
		<td height="85"> 
		  <table width="100%" border="0" cellpadding="0">
			<tr>
			  <td width="223"><a href="http://www.ep-dev.com" target="_blank"><img src="logo.gif" width="223" height="69" border="0"></a></td>
			  <td><font color="#000066" size="+7" face="Verdana, Arial, Helvetica, sans-serif"> 
				- Upgrade to v3.0</font></td>
			</tr>
		  </table></td>
	  </tr>
	  <tr>
		<td>File Database Upgrade<br>
		  In order to upgrade your file database, we need additional information about 
		  your old database. Please type in the filename of your old file database 
		  file below, the default is already filled in for you:<br>
		  <p>File Database filename:</p>
		  <form name="form1" method="get" action="upgrade-v3.php">
		  <input name="page" type="hidden" value="ProcessUpgrade-v3-filedb">
			<p>FileDB: 
			  <input name="filedbname" type="text" id="filedbname" value="counter-db.txt">
			</p>
		  <p>
			<input type="submit" name="Submit" value="Continue With Upgrade">
		  </p>
		  </form>
		  </td>
	  </tr>
	  <tr>
		<td><div align="center">Copyright &copy; 2003 <a href="http://www.ep-dev.com" target="_blank">EP-Dev.com</a></div></td>
	  </tr>
	</table>
	</body>
	</html>
	<?
}

function Upgrade_MySQL_Tables()
{
	global $MDB;

	$result = mysql_query("SELECT `visits` FROM `ep_stats`", $MDB->cn);
	if (!$result)
	{
		$sql[0] = "ALTER TABLE `ep_stats` ADD `visits` INT(9) DEFAULT '1' NOT NULL;";
		$sql[1] = "INSERT INTO ep_stats VALUES ('oldtotals', '00', '00', 0, 0);";
		$sql[2] = "ALTER TABLE `ep_stats` ADD `hour` INT(2) DEFAULT '00' NOT NULL;";

		foreach($sql as $val)
		{
			$MDB->query($val);
		}

		Upgrade_MySQL_v3();
	}
	else
	{
		Upgrade_MySQL_v3();
	}
}

function Upgrade_MySQL_v3()
{
	global $DB, $MDB;

	$result = $MDB->query("SELECT ipaddress, day, min, hits, visits, hour FROM ep_stats");
	
	$i=0;
	echo "<br><br><b>Beginning upgrade...</b><br><br>";

	while($main_data = mysql_fetch_row($result))
	{
		if ($main_data[0] != "oldtotals")
		{
			echo "grabbing data for ip ".$main_data[0]." from old database<br>"; 
			$users[$i] = $main_data;
			$i++;
		}
	}

	echo "<br><br><b>Finished grabbing data, beginning conversion...</b><br><br>";

	for($i=0; $i<count($users); $i++)
	{
		list($ipaddress, $day, $min, $hits, $visits, $hour) = $users[$i];

		if(!checkdate(date('n'), $day, date('Y')))
		{
			$month = date('n');
		}
		else
		{
			$month = date('n')-1;
		}

		$cuser_time = mktime($hour, $min, 0, $month, $day, date('Y'));

		if ($cuser_time > time())
		{
			$cuser_time = mktime($hour, $min, 0, $month, $day, (date('Y')-1));
		}

		$new_data[$i] = array($ipaddress, $hits, $visits, $cuser_time);

		echo "formatted ip ".$ipaddress." to new data standard.<br>";
	}

	echo "<br><br><b>Finished conversion, dumping old database...</b><br><br>";

	$result_dump = $MDB->query("DROP TABLE `ep_stats`");

	echo "<br><br><b>Finished dumping old, creating new database...</b><br><br>";

	Install_MySQL_Tables($MDB);

	echo "<br><br><b>Finished creating new database, dumping data into it</b><br><br>";

	for($i=0; $i<count($new_data); $i++)
	{
		list($ip, $hits, $visits, $time) = $new_data[$i];
		$sql = "INSERT INTO ".$DB['ext']."_stats (ip, hits, visits, time) VALUES('".$ip."', '".$hits."', '".$visits."', '".$time."')";
		$result = $MDB->query($sql);

		if ($result)
		{
			echo "Inserted ip ".$ip." successfully<br>";
		}
	}

	echo "<br><br><b>Finished updating... Update successful. Please visit your page to see counter!</b><br><font color=\"red\"><b>For security reasons, it is highly recommended that you delete the upgrade folder that was included with the counter now!</b></font></b>";

	Finish_Upgrades();
}


function Upgrade_FileDB_v3($oldfile)
{
	global $MDB, $DB;

	$linedefault = "oldtotalsxxxxxx|01|01|0xxxxxxxx|0xxxxxxxx|01";
	$lineformat = "%[a-zA-Z0-9.]|%[0-9x]|%[0-9x]|%[0-9x]|%[0-9x]|%[0-9x]\n";
	echo "<h1>Starting upgrade...</h1>";
	echo "<br><br><b>Connecting to file ".$oldfile."...</b><br><br>";

	$connect = @fopen("../".$oldfile, 'rb');

	if ($connect)
	{
		echo "<br><br><b>Pulling old data from ".$oldfile."</b><br><br>";
	}
	else
	{
		echo "<br><br><b><font color=\"red\">Failed to connect to file specified, not found</font></b><br><br>";
		die();
	}
	$i=-1;

	while($current_line = fscanf($connect, $lineformat))
	{
		$MDB->Format_Array($current_line, $linedefault);
		list($ipaddress, $day, $min, $hits, $visits, $hour) = $current_line;

		if ($ipaddress == "oldtotalsxxxxxx")
		{
			$archive_data = array("0x", $hits, $visits);
			$archive_data = $MDB->Format_Array($archive_data);
			$archive_data_lines = $MDB->Create_Line($MDB->Format_Array($archive_data, "0xxxxxxxxxxx|0xxxxxxxx|0xxxxxxxx"));

			echo "formatted archive ".$ipaddress." to new data standard.<br>";
		}
		else
		{
			$i++;
			if(!checkdate(date('n'), $day, date('Y')))
			{
				$month = date('n');
			}
			else
			{
				$month = date('n')-1;
			}

			$cuser_time = mktime($hour, $min, 0, $month, $day, date('Y'));

			if ($cuser_time > time())
			{
				$cuser_time = mktime($hour, $min, 0, $month, $day, (date('Y')-1));
			}

			$new_data[$i] = array($ipaddress, $hits, $visits, $cuser_time);

			$temp = $MDB->Format_Array($new_data[$i]);
			$temp = $MDB->Format_Array($temp);

			$new_data_lines[$i] = $MDB->Create_Line($temp);

			echo "formatted ip ".$ipaddress." to new data standard.<br>";
		}
	}

	fclose($connect);

	echo "<br><br><b>Finished formatting... inserting new data</b><br><br>";

	$connect = fopen("../".$DB['arcfolder'].$DB['filename']."-data.inc", 'r+b');

	for($i=0; $i<count($new_data_lines); $i++)
	{
		fwrite($connect, $new_data_lines[$i]);
		echo "Copied '".$new_data_lines[$i]."' to ".$DB['arcfolder'].$DB['filename']."-data.inc<br>";
	}

	fclose($connect);

	$connect = fopen("../".$DB['arcfolder'].$DB['filename']."-arch.inc", 'r+b');
	fwrite($connect, $archive_data_lines);

	fclose($connect);

	echo "<br><br><b>Finished Writing Data</b><br><br>";

	echo "<big><b>Upgrade Complete</b></big><br>Please visit your page to see counter!<br><font color=\"red\"><b>For security reasons, it is highly recommended that you delete the upgrade folder that was included with the counter now!</b></font>";

	Finish_Upgrades();
}

function Install_MySQL_Tables()
{
	global $MDB, $DB;
	$handle = fopen("../dbtables.sql", 'rb');
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
		echo "<br><br><b>MySQL DB Data Successful.</b><br><br>";
	}
}

function Finish_Upgrades()
{
	touch("upgrade.lock");
}

function Go_Error($error)
{
	global $OPTION, $MDB;
	
	if (file_exists($OPTION['Absolute_Path']."counter-error.php"))
	{	
		if ($OPTION['DebugMode'])
		{
			include($OPTION['Absolute_Path']."counter-error.php");
			ignore_user_abort(false);
			exit();
		}
	}
	else
	{
		echo "<b>COUNTER ERROR: </b><br>The counter has an error. However, the error file could not be found to provide additional assistance. It is likely that your \"absolute path\" is not correctly set in counter.php.";
	}
}

switch($_GET['page'])
{
	case "ProcessUpgrade-v3-filedb" : Upgrade_FileDB_v3($_GET['filedbname']);
		break;

	case "ProcessUpgrade-v3" : if ($_GET['dbtype'] == "mysql")
								{
									Upgrade_MySQL_v3();
								}
								else
								{
									File_DB_v2_Name();
								}
		break;
	
	default : Main();
}