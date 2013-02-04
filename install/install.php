<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	<title>EP-Dev Counter Install</title>
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
				- Install</font></td>
			</tr>
		  </table></td>
	  </tr>
	  <tr>
		<td><p><b>NOTE: More information and troubleshooting can be found in the README.TXT file.<br><br>Instructions Install (using MySQL database or File database):</b><br>
1. Edit counter.php file and modify settings to your desired settings.<br><br>
2. Edit either mysql_config.php or filedb_config.php (depending on which you wish to use).<br><br>
3. Upload all .php files (and image files if using graphical counter).<br><br>
4. Place <<!-- Stop PHP from parsing-->?php include("/path/to/your/counter.php"); ?> in the html of all of your pages, where /path/to/your/ is the absolute path to counter.php. To discover this path, you can usually run the file <a href="abs.php">abs.php</a> that has been included with this counter. On most systems it will report your absolute path. Remember, absolute path must also be set within counter.php as well.<br><br>
5. Visit the <a href="../counter.php">counter.php</a> file in your browser.<br><br>
NOTE: If using file databases, your counter will show up. If using mysql, your counter will first install, after which you can refresh the page and your counter will show up.<br><br>
<font color="red"><b>NOTE 2: Remember to delete the install folder, as it poses a threat to security.</b></font></p>
<p><b>Instructions for PHP-Nuke Install:</b><br>
1. Upload /counter folder to php-nuke root folder (where php-nuke is installed).<br><br>
2. Upload /extras/PHP-Nuke/blocks/block-EP-Dev_Counter.php to PHP-Nuke's /blocks/ folder.<br><br>
3. Go into PHP-Nuke's Admin, click on blocks, and then add the block "EP-Dev Counter".<br><br>
4. (optional) Edit counter.php<br><br>
NOTE: Your counter will first install, after which you can refresh the page and your counter will show up.</p>
		  </td>
	  </tr>
	  <tr>
		<td><div align="center">Copyright &copy; 2003-2004 <a href="http://www.ep-dev.com" target="_blank">EP-Dev.com</a></div></td>
	  </tr>
	</table>
	</body>
	</html>