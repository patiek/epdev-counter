EP-Dev Counter
=============
EP-Dev Counter is one the most option-packed counters on the internet. Online Visitors/Daily Visitors/Total 
Visitors/Total Hits counting. Both file and mysql databases supported. 
Easy-to-edit counter display file. Automatic Database Trimming (both  file and mysql). 
Configurable Online Time, Auto Archive Option, Hit Offset Feature, notation format (none/english/french), 
Graphical or  Text, configurable Number Length, IP Ignore/block, hide counter option, 
and statistic viewing (averages, stats, graphs).

INSTALL
=============
## MySQL or File DB Installation
1. Edit counter.php file and modify settings to your desired settings.
2. Edit either mysql_config.php or filedb_config.php (depending on which you wish to use).
3. Upload all .php files and image files (if using graphical counter).
4. Place <?php include("/path/to/your/counter.php"); ?> in the html of all of your pages.
5. Visit the counter.php file in your browser.
NOTE: If using filedb, your counter will show up. If using MySQL, your counter will first install, after which you can refresh the page and your counter will show up.
NOTE 2: Remember to delete install folder.

## Advanced User Installation
1. Edit counter & config files.
2. If using mysql, load dbtables.sql into database.

## PHP-Nuke w/ MySQL
1. Upload entire counter folder to php-nuke root folder (where php-nuke is installed).
2. Upload /extras/PHP-Nuke/blocks/block-EP-Dev_Counter.php to PHP-Nuke's /blocks/ folder.
3. Go into PHP-Nuke's Admin, click on blocks, and then add the block "EP-Dev Counter".
4. (optional) Edit counter.php
NOTE: Your counter will first install, after which you can refresh the page and your counter will show up.

## UPGRADE 
BACK UP YOUR DATABASE OR DATA FILES!
### Upgrading from 3.x --> 3.x
1. Edit counter.php file and modify settings to your desired settings.
2. Edit either mysql_config.php or filedb_config.php (depending on which you wish to use).
3. Upload all .php files and image files (if using graphical counter).
4. (OPTIONAL: Visit the new /stats/ page to review your old stats!)

### Upgrading from 1.x or 2.x --> 3.x
1. Edit counter.php file and modify settings to your desired settings.
2. Edit either mysql_config.php or filedb_config.php (depending on which you wish to use).
3. Upload all .php files and image files (if using graphical counter).
4. Visit install/upgrade-v3.php in your browser and follow any on-screen instructions.
NOTE: Your old file database files/tables will be upgraded to new format, no data will be lost.

Feature List
==============
* Online Visitors/Daily Visitors/Total Visitors/Total Hits counting
* Both file databases and mysql databases supported.
* Easy-to-edit counter display file.
* Automatic Database Trimming (both file and mysql)
* Configurable Online Time
* Configurable Trim Database Time
* Auto Archive Option
* Hit/visit Offset Feature
* Commas on/off
* Either Graphical or Text
* Configurable Number Length
* IP Ignoring
* IP Blocking
* Many other features (I can't keep up with them anymore)

Troubleshooting
================
Problem: My stats page always displays that I have 0 visitors online. I have time_offset enabled.  
Solution: Currently there is no solution for this problem. It occurs as a result of the script checking server time against time values in database. If you want to check your visitors online, just visit your actual counter.php file ;)


Problem: The counter is telling me that my "absolute path" is not set correctly!?!?  
Solution: You can find the absolute path setting in counter.php . If you do not know your absolute path, you can visit the file /install/abs.php (included with this counter) in your web browser. The file abs.php will display the absolute path required for the counter.


Problem: My counter isn't showing up, instead it shows up as <?php include("counter.php"); ?>.  
Solution: Ensure that your page is a php page (.php) and that php is available on your server.


Problem: I don't know how to edit the layout of the counter.  
Solution: Edit the counter-display.html file. Include the following to reflect the different stats:  
Online visitors- !COUNTER-ONPAGE!  
Today's Visitors- !COUNTER-TODAY!  
Total Visitor- !COUNTER-VISITS!  
Total Hits- !COUNTER-HITS!  


Problem: I don't know what a mysql database is, or if I even have one!
Solution: A MySQL database is simple way and place to store data. You can ask your webhost if you have one (and if you do, what your username, password, and database name is). If you do not have a mysql database, you can set the counter to use file databases instead in the counter.php file.


Problem: I receive "Parse error: parse error, unexpected ',' ... on line ##".
Solution: You probably have commas in your Hits_Offset, please remove them.


Problem: My counter is not displaying visitors correctly! It always stays at 1 or 0.  
Solution: The problem is occuring with the way you are including the file. This is a known problem and will occur if you are:  
a) Linking to the script via domain <?php include("http://domain.com/counter.php"); ?>  
b) Having the server pull the script in some other fashion than what ep-dev has recommended.  
TO FIX: simply include the browser with absolute path on your server (often used for CGI scripts). Thus, your include would look similar to: <?php include("/my/absolute/path/counter/counter.php"); ?>. It may also work by just taking off the domain name (if domain was mydomain.com, then include would look like): <?php include("/counter/counter.php"); ?>

Known Bugs
================
/stats/ may display incorrect recent visitors on main index.php page. All stats are still accurate, just the display of specific recent visitors may not really be your most recent visitors. This is caused by sorting the visitors AFTER you grab them, at which point recent visitors have already been established. This will be fixed in some future version, as it requires some rewriting of code. To view your most recent visitors, you can still use the "view all vistors" link on the main page.

Old Version History:
=================
3.45 - July 29 2004:
IMPROVED - Improved script to be more object oriented and to stop relying on a couple of common globals.
ADDED FEATURE - Added ability for counter to be turned into a PHP-Nuke block. Simply upload the /counter folder to PHP-Nuke's root folder and the PHP-Nuke block file found in extras to PHP-Nuke's blocks folder. The script should start working automatically ~ no config required.
IMPROVED - Improved /stats/ to automatically pick up on a PHP-Nuke setup.

3.44 - March 02 2004:
FIXED BUG - A bug in the archiving function of the file database has been fixed. The bug resulted in multiple archives for the same date. While this did not affect the stats or functionality of the counter, it did cause the /stats/ folder to display strange graphs.

3.43 - February 03 2004:
FIXED BUG - A major bug was found and fixed in classes/filedb.php involving the archiving of stats. Hits and visits would be accumulated and added onto the total for each archived day, as opposed to archiving one day's own hits and visits.
FIXED BUG - A bug was found involving the stats display file. The bug, in stats/file_db.php, caused a global to not be initialized, resulting in a negative time value that caused major havoc with people's displays of stats using file databases. (A big thanks to Guy for bringing this error to my attention)

3.42 - February 01 2004:
ADDED FEATURE - Counter now supports time offset via $OPTION['time_offset'] value in counter.php. (requested by Goran Todorov).
IMPROVED - Improved IP detection and fixing. You can enable this improved detection by enabling the clean_ip feature.

3.41 - November 29 2003:
IMPROVED/FIXED BUG - Added clean ip mode feature to help people with recognizing ips with proxy-cache, and to stop the missing IP problems for those who do not need this support. If you have had problems with recognizing IPs, set this to 0. NOTE: This value should be kept at 0 unless you absolutely want to recognize proxy-cache.

3.4 - November 16 2003:
IMPROVED - Improved IP retrieval to, when possible, work with ISPs that include its proxy-cache in IP. (Thanks SiMo).
FIXED BUG - Fixed "Currently Online" bug displaying incorrect number of people online within last 15 minutes.
IMPROVED - Added default N/A and No information available instead of 1 second prior Unix epoch date (where time = 0) to graphs with days that have not been filled yet (such as the month graph on a new counter).
IMPROVED - Added more limits to graphs when strange data occurs, making page long.
ADDED FEATURE - New install file & folder and more organization allows you to delete install folder to get rid of all the junk that isn't needed to run counter.
FIXED BUG - Fixed graphs showing incorrect stats for last two days (results from db lookups).
ADDED FEATURE - Added "View all visitors" page to display all visitors accessing your page recently.
ADDED FEATURE - Now you can limit recent visitors output by editing value in stats/index.php.
FIXED BUG - Fixed graphic image digital-blue 0.GIF to be 0.gif (case sensitive) (Thanks Ken).
FIXED BUG - Fixed /stats bug that stopped more than 1 IP being shown as recent in filedb and mysqldb classes (Thanks Thomas).
IMPROVED - Mysql class now supresses mysql connect errors. I forgot to carry over from old version. Thanks to someone who emailed me (sorry, lost your name) for bringing this to my attention.
IMPROVED - Created new absolute path file (abs.php) to reflect different versions of PHP and perhaps provide the absolute path in most occasions.
ADDED FEATURE - Added optional ip block page redirect.
IMPROVED - Improved IP ignore logic to be inactive if feature not used.
IMPROVED - Improved IP block logic to be inactive if feature not used.
IMPROVED - Added global registers to register some of the global variables on systems with globals set to off (complies with register globals being turned off by default in php 4.2.0 release).

3.3 - October 04 2003:
FIXED BUG - Fixed un-even stats bug where huge stat one day caused stats page to be rather long in length and graphs to be corrupt.
FIXED BUG - Fixed line 139 pass by reference error.

3.2 - August 30 2003:
ADDED FEATURE - Visits offset now available as well as hits offset.
FIXED BUG - Stats page drop down links now correctly start on selected page.
FIXED BUG - "Hide Counter" accidentally removed with last release. It now works again.

3.1 - August 29 2003:
ADDED FEATURE - Added version detection.
ADDED FEATURE - Added stat page.

3.0 - August 14 2003:
ADDED FEATURE - Added archiving option, allowing hits and visits to be archived for later viewing.
IMPROVED - Modified "Add Commas" feature to include French notation support.
ADDED FEATURE - Added "Visit Length", allowing one to set how long a new visit is condidered (once daily or once in a lifetime).
ADDED FEATURE - Added "RecentMode", allowing one to select what period of time they consider recent (daily) traffic to be (default is day).
IMPROVED - Database Trimming can now be set for any number of weeks.
IMPROVED - Total re-write of all the code, organizing it into classes.

2.1 - July 16 2003:
IMPROVED - Many absolute url problems fixed.
IMPROVED - Errors are handled better now.
IMPROVED - Config file merged back into counter.php to stop confusion and some include errors.
FIXED BUG - Fixed Hits Offset, old variable was invalid in new script.
FIXED BUG - Fixed broken image display when absolute path or url to image contained number(s).
IMPROVED - Error checking now attempts to fix chmod errors and tells how to fix them yourself.
IMPROVED - Files can now be handled with absolute path by setting $OPTION['Absolute_Path'] to a custom setting in config. This allows for the script to be used easier on multiple pages in different folders.
ADDED FEATURE - The counter can now protect your website by blocking out IPs with IP_Block in config.
ADDED FEATURE - The counter now has a hide option. When hidden, stats can be accessed via counter.php?p=stats .
ADDED FEATURE - IP ignore. Put IPs that you want the counter to ignore in the IP_Ignore option in config.

2.0 - April 19 2003:
ADDED FEATURE - A file database can now be used instead of mysql. While the script works the same, the file database may be slower and harder on the server. It is still recommended you use mysql if available.
IMPROVED - Improved logic for trimming database (old logic very buggy, but new code works great).
IMPROVED - Improved logic for online users (old logic very buggy, but new code works great).
IMPROVED - HTML is no longer edited via counter.php file. Instead, edit the counter-display.html file.
IMPROVED - Error checking is now in a different file, only called upon if an error occurs.
IMPROVED - Configuration is now in config.inc.php file for better security. The file comments have also been cleaned up.
IMPROVED - Re-wrote all of the code, organizing it better.

1.4 - March 27 2003:
FIXED BUG - Fixed Hit_Offset bug that multiplied hit offset by number of visitors.

1.3 - March 11 2003:
ADDED FEATURE - Graphics can now be used instead of text to display numbers. Any type of image can be used, as well as any height and width. The script will automatically detect the height and width. Three different counter sets included or make your own!
ADDED FEATURE - It is now possible to turn commas off. (EX: 1,000 becomes 1000);

1.2 - March 10 2003:
FIXED BUG - Fixed bug that reported incorrect number of online visitors.

1.1 - March 09 2003:
FIXED BUG - Fixed visit counter to count seperate 24 hour visits towards the total visit count.
FIXED BUG - Online visitors reported incorrectly due to lack of hour.
FIXED BUG - Online visitors reported incorrectly due to lack of logging.
ADDED FEATURE - "trim database" option. Setting this value to 1 will trim the database of IPs over a week old, but keeping count of their hits and visits (1 = 1 week, 2 = 2 weeks...).
ADDED FEATURE - "online time" option. This value specifies how long a user is considered to still be "online" since last logged (in minutes).

1.0 - March 08 2003: First released. Can display online visitors, visitors that day, visitors total, and hits total. Has debug mode option and hit offset option (to add hits onto the total count). Supports Auto-Install.
