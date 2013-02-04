<?php global $DB, $OPTION;
// --------------------------------------------
// | The EP-Dev Counter script        
// |                                           
// | Copyright (c) 2002-2004 EP-Dev.com :           
// | This program is distributed as free       
// | software under the GNU General Public     
// | License as published by the Free Software 
// | Foundation. You may freely redistribute     
// | and/or modify this program.               
// |                                           
// --------------------------------------------

// ------------- //
// Database Type //
// ------------- //
// Use MySQL Database (1 = use mysql, 0 = use files)
$DB['type'] = 1;


// ----------------- //
// SITE CONFIG SETUP //
// ----------------- //
/* Absolute path to counter script (ex: "/mywebsite/counter/") with trailing slash.
	NOTE: If you do not know what your absolute path is, please run run abs.php that
	is included with this counter's zip file. */
$OPTION['Absolute_Path'] = "/mywebsite/counter/";


// --------------- //
// COUNTER OPTIONS //
// --------------- //
// Online Time - Number of minutes a visitor is considered online:
$OPTION['OnlineTime'] = 15;

/* Recent Mode - "Today's visitors" - Set to 1 to reset daily, or number or hours (2-23).
	NOTE: If archiving = 1, it is recommended that this is set to 1. */
$OPTION['RecentMode'] = 1;

// Visit Length - Sets how long a "visit" is considered. 0 = once per trim/archive time, 1 = daily.
$OPTION['VisitLength'] = 1;

// Time Database - Sets how often to trim database (weeks) (does not apply if archiving):
$OPTION['TrimDatabase'] = 1;

// Archive Hits/Visits - 1 = archive hits/visits, 0 = do not keep archives.
$OPTION['ArchiveStats'] = 1;

// Debug Mode - Set to 1 to see errors, 0 to hide errors (when possible):
$OPTION['DebugMode'] = 1;

// Hits Offset - Number of hits to be added to total, useful if you are replacing a counter (EX: 1000).
$OPTION['Hits_Offset'] = 0;

// Visits Offset - Does the same thing as hits offset (above), but with the visits count.
$OPTION['Visits_Offset'] = 0;

/*
	Time Offset - Offsets time to reflect your timezone.
	Example: Your time is 3 hours ahead of server time: $OPTION['time_offset'] = 3;
			 Your time is 3 hours behind server time: $OPTION['time_offset'] = -3;
*/
$OPTION['time_offset'] = 0;

// IP ignore - enter any IPs you would like to ignore for counter: array("123.123.123.123", "456.456.456.456")
$OPTION['IP_Ignore'] = array("");

// IP block - enter any IPs you would like to block from your page: array("123.123.123.123", "456.456.456.456")
$OPTION['IP_Block'] = array("");

// IP Block page - enter page you want blocked IPs redirected to.
$OPTION['Block_Page'] = "http://www.yahoo.com";

// Log Referrers (will display in /stats/ if enabled), 1 = enable, 0 = disable.
$OPTION['Log_Referrers'] = 1;


// --------------- //
// COUNTER DISPLAY //
// --------------- //
// Graphics - 1 = Graphical counter, 0 = text counter:
$OPTION['Graphical'] = 0;

// Image Directory - directory to counter images (URL or relative path)... with trailing slash:
$OPTION['Images_Dir'] = "images/default/";

// Image Extension - the extension of your counter graphics (such as .gif or .jpg):
$OPTION['Image_ext'] = ".jpg"; 

// The number of positions to show on counter (EX: 8 spaces with only 500 hits would show up as: 00000500):
$OPTION['Counter_Spaces'] = 8; 

// Add number notation (1 = "1,000"; 2 = "1 000"; 0 = "1000").
$OPTION['Add_Char'] = 1;


// --------------- //
// COUNTER ACCESS  //
// --------------- //
// Hidden Counter - Set to 1 to hide counter, 0 to display (admin access: counter.php?p=stats)
$OPTION['Hide_Counter'] = 0;

/*
	Clean IP Mode - Unless you are having IP recognition problems with proxy-cache or for some
	other reason, you should leave this as 0.
*/
$OPTION['IP_Clean'] = 0;

// --------------------------------------------
// DO NOT EDIT BELOW THIS LINE                   
// --------------------------------------------


// Version Number
$version_number = "3.45";


/* ------------------------------------------------------------------ */
//	The user information class contains user information for individual
//	entries (one object per entry).
/* ------------------------------------------------------------------ */

class User_Information
{
	// +------------------------------
	//	Initialize Variables
	// +------------------------------
	var $ip;
	var $otime;
	var $trim = false;
	var $self = false;
	var $online = false;
	var $recent = false;

	var $hits;
	var $visits;


	/* ------------------------------------------------------------------ */
	//	Gets the user "online". Checks if the user is recent (visitor) as
	//	well as online (current visitor) and marks them accordingly.
	/* ------------------------------------------------------------------ */

	function Get_Online()
	{
		global $OPTION;

		// If not visiting user (i.e. user calling script)
		if (!$this->self)
		{
			// Determine if visit is recent
			if (time_offset() - $this->otime < ($OPTION['RecentMode'] > 1 ? ($OPTION['RecentMode'] * 3600) : ((date("d", $this->otime) == date("d", time_offset()) ? 86400 : 0))))
			{
				// Mark as recent
				$this->recent = true;
			}

			// Determine if visit is online 
			if (time_offset() - $this->otime < $OPTION['OnlineTime'] * 60)
			{
				// Mark as online
				$this->online = true;
			}
		}

		// If self assume both recent and online.
		else
		{
			// Marking recent & online
			$this->recent = true;
			$this->online = true;
		}
	}

	
	/* ------------------------------------------------------------------ */
	//	Updates current user's visit & hit count
	/* ------------------------------------------------------------------ */

	function Update_Counts()
	{
		global $OPTION;

		// Ensure that we only update current user
		if ($this->self)
		{
			// If past recent, update visits
			if (time_offset() - $this->otime > ($OPTION['VisitLength'] > 0 ? $OPTION['VisitLength'] * 86400 : time_offset()))
				$this->visits++;

			// Always update hits
			$this->hits++;
		}
	}


	/* ------------------------------------------------------------------ */
	//	Determine if user needs to be trimmed from database
	/* ------------------------------------------------------------------ */

	function Determine_Trim()
	{
		global $OPTION;

		// Set trim if last visit time is older than specified archive time (or default 1 day)
		if ($this->otime < time_offset() - (86400 * ($OPTION['ArchiveStats'] ? 1 : (7 * $OPTION['TrimDatabase']))))
			$this->trim = true;
	}

}


/* ------------------------------------------------------------------ */
//	Class contains all the counters & functions to manipulate counters
/* ------------------------------------------------------------------ */

class TotalCounts
{
	// +------------------------------
	//	Initialize Variables
	// +------------------------------

	var $online=0;
	var $recent=0;
	var $visits=0;
	var $hits=0;

	var $count_array;
	var $count_array2;

	
	/* ------------------------------------------------------------------ */
	//	Make counters into array.
	/* ------------------------------------------------------------------ */
	
	function CreateArray()
	{
		$this->count_array = array($this->online, $this->recent, $this->visits, $this->hits);
	}

	
	/* ------------------------------------------------------------------ */
	//	Deconstruct counters array
	/* ------------------------------------------------------------------ */

	function DismantleArray()
	{
		$this->online = $this->count_array[0];
		$this->recent = $this->count_array[1];
		$this->visits = $this->count_array[2];
		$this->hits = $this->count_array[3];
	}

	
	/* ------------------------------------------------------------------ */
	//	Manipulate number output to reflect config settings
	//	For example, call on function to format numbers as images.
	/* ------------------------------------------------------------------ */

	function Format()
	{
		global $OPTION;

		// Make into array
		$this->CreateArray();

		// Format images accordingly 
		if($OPTION['Graphical'])
		{
			$this->Format_to_Images();
		}

		// Else format as number accordingly
		elseif($OPTION['Add_Char'])
		{
			$this->Format_Numbers();
		}

		// Convert back to individual ints
		$this->DismantleArray();
	}

	
	/* ------------------------------------------------------------------ */
	//	Format number into images
	/* ------------------------------------------------------------------ */
	
	function Format_to_Images()
	{
		global $OPTION;

		// Store original counter
		$count_orig = $this->count_array;

		// Attempt to fix image url if broken by default
		if (!file_exists($OPTION['Images_Dir'].'0'.$OPTION['Image_ext']))
			$OPTION['Images_Dir'] = $OPTION['Absolute_Path'].$OPTION['Images_Dir'];

		// Get counter image information & check that it exists
		$CounterImage = getimagesize($OPTION['Images_Dir'].'0'.$OPTION['Image_ext'])
					or die(EPCNTR_Go_Error("image_404"));

		// Cycle through counters
		for ($i=0; $i<4; $i++)
		{
			// Change numbers to numbers with specific format
			for ($j=0; $j<10; $j++)
				$this->count_array[$i] = str_replace($j, "~~".$j."~~", $this->count_array[$i]);

			// Change numbers to images
			for ($j=0; $j<10; $j++)
				$this->count_array[$i] = str_replace("~~".$j."~~", "<img src=\"".$OPTION['Images_Dir'].$j.$OPTION['Image_ext']."\">", $this->count_array[$i]);

			// Add in extra zeros according to config
			for ($j=0; $j<($OPTION['Counter_Spaces']-strlen($count_orig[$i])); $j++)
				$this->count_array[$i] = "<img src=\"".$OPTION['Images_Dir'].'0'.$OPTION['Image_ext']."\">".$this->count_array[$i];

			// Add on image size
			$this->count_array[$i]=str_replace(">", $CounterImage[3].">", $this->count_array[$i]);
		}
	}

	
	/* ------------------------------------------------------------------ */
	//	Format numbers into character format
	/* ------------------------------------------------------------------ */
	
	function Format_Numbers()
	{
		global $OPTION;

		// Format english
		if($OPTION['Add_Char'] == 1)
		{
			for($i=0; $i<4; $i++)
				$this->count_array[$i] = number_format($this->count_array[$i]);
		}

		// Format SI standard
		elseif($OPTION['Add_Char'] == 2)
		{
			for($i=0; $i<4; $i++)
				$this->count_array[$i] = number_format($this->count_array[$i], 2, ',', ' ');
		}
	}

	
	/* ------------------------------------------------------------------ */
	//	Formats counters to template & displays result
	/* ------------------------------------------------------------------ */

	function Parse()
	{
		global $OPTION;

		// Get counter display file.
		$file = $OPTION['Absolute_Path']."counter-display.html";
		$display_config = fopen($file, 'rb')
			or die("Counter absolute path incorrect!");
		$display = fread($display_config, filesize($file));
		fclose($display_config);

		// For each counter, replace counter in template with actual count
		$display = ereg_replace("!COUNTER-ONPAGE!", strval($this->online), $display);
		$display = ereg_replace("!COUNTER-TODAY!", strval($this->recent), $display);
		$display = ereg_replace("!COUNTER-VISITS!", strval($this->visits), $display);
		$display = ereg_replace("!COUNTER-HITS!", strval($this->hits), $display);

		// return resulting display
		return $display;
	}

}

class EP_Dev_Counter
{
	var $USR;
	var $MDB;
	var $counter_display;

	
	/* ------------------------------------------------------------------ */
	//	Call on various functions throughout script to
	//  process / show counter.
	/* ------------------------------------------------------------------ */
	
	function EP_Dev_Counter()
	{
		global $OPTION;

		// Initialize Database
		$this->Initialize_Database();
		
		// Initialize Current User
		$this->Initialize_Current_User();

		// Perform old IP block feature with javascript
		if (!empty($OPTION['IP_Block']) && in_array($this->USR->ip, $OPTION['IP_Block']))
			$this->Block_IP();

		// Perform old IP Ignore Feature
		if (empty($OPTION['IP_Ignore']))
		{
			$this->User_Update();
		}

		// Only check if IP exists in array after confirming array isn't empty.
		elseif (!in_array($this->USR->ip, $OPTION['IP_Ignore']))
			$this->User_Update();


		// Log referrer
		// NOT YET. $this->MDB->Log_Referrer($referrer);

		// Get all the counter information from db
		$All_Users = $this->Get_Counters();

		// Trim any counter information that needs to be trimmed.
		$this->Start_Trim($All_Users);

		// Only tally counts, format, and display if counter display is enabled.
		if (!$OPTION['Hide_Counter'])
		{
			// Get Counter Object (contains counts)
			$CNT = $this->Tally_Counts($All_Users);

			// Format counts
			$CNT->Format();

			// Display counts
			$this->counter_display = $CNT->Parse();
		}
	}


	/* ------------------------------------------------------------------ */
	//	Initialize database.
	/* ------------------------------------------------------------------ */

	function Initialize_Database()
	{
		global $OPTION, $DB;
		/* Include proper class file */
		if ($DB['type'])
		{
			// require mysql database class
			require($OPTION['Absolute_Path']."classes/mysqldb.php");
		}
		else
		{
			// require file database class
			require($OPTION['Absolute_Path']."classes/filedb.php");
		}


		// +------------------------------
		//	Initialize Database
		// +------------------------------

		$this->MDB = new Main_Database();
	}


	/* ------------------------------------------------------------------ */
	//	Initialize visiting user.
	/* ------------------------------------------------------------------ */

	function Initialize_Current_User()
	{
		global $OPTION;

		// Setup $USR object ~ contains current visiting user data.
		$this->USR = new User_Information();

		// +------------------------------
		//	IP Clean will try to find users IP.
		//  Thanks to SiMo for notifying me.
		// +------------------------------
		if ($OPTION['IP_Clean'])
		{
			// Code taken from php.net
			if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
				   $this->USR->ip = getenv("HTTP_CLIENT_IP");
			   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
				   $this->USR->ip = getenv("HTTP_X_FORWARDED_FOR");
			   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
				   $this->USR->ip = getenv("REMOTE_ADDR");
			   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
				   $this->USR->ip = $_SERVER['REMOTE_ADDR'];
			   else
			{
				   global $HTTP_SERVER_VARS;
				   $this->USR->ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
			}
		}

		// Else use our default method of getting IP from env.
		else
		{
			$this->USR->ip = getenv("REMOTE_ADDR");
		}


		// +------------------------------
		//	Initialize User
		// +------------------------------

		// Set current users time to current time
		$this->USR->otime = time_offset();

		// Set self to true, making the script special-recognize this user later
		$this->USR->self = true;

		// Get the user Online
		$this->USR->Get_Online();
	}

	
	/* ------------------------------------------------------------------ */
	//	Try to block IP with javascript. Kind of the only way considering
	//  how far down in the page the counter may be (headers probably sent
	//  long ago).
	/* ------------------------------------------------------------------ */
	
	function Block_IP()
	{
		?>
			<script language="javascript" type="text/javascript">
				window.location="<? echo $OPTION['Block_Page']; ?>";
			</script>
		<?
	}

	
	/* ------------------------------------------------------------------ */
	//	Update user
	/* ------------------------------------------------------------------ */
	
	function User_Update()
	{
		$cUser = $this->MDB->Get_User_Data($this->USR->ip);

		if ($cUser['newvisit'])
		{
			$this->MDB->Insert_Into_Database($this->USR->ip, time_offset());
		}
		else
		{
			/* If not a new visit, pull old hit/visit/time info and update accordingly */
			$this->USR->hits = $cUser['hits'];
			$this->USR->visits = $cUser['visits'];
			$this->USR->otime = $cUser['oldtime'];
			$this->USR->Update_Counts();
			$this->MDB->Update_Database_Entry($this->USR->ip, $this->USR->hits, $this->USR->visits, time_offset());
		}
	}

	function Get_Counters()
	{
		return $this->MDB->Get_All_Users();
	}

	function Tally_Counts($All_Users)
	{
		global $OPTION;

		$counter = new TotalCounts();

		for($i=0; $i<count($All_Users); $i++)
		{
			if($All_Users[$i]->online)
			{
				$counter->online++;
			}

			if($All_Users[$i]->recent)
			{
				$counter->recent++;
			}

			$counter->hits += $All_Users[$i]->hits;
			$counter->visits += $All_Users[$i]->visits;
		}

		$counter->hits += $OPTION['Hits_Offset'];
		$counter->visits += $OPTION['Visits_Offset'];

		return $counter;
	}

	function Start_Trim($All_Users)
	{
		global $OPTION;

		if($OPTION['TrimDatabase'])
		{
			$j=0;
			for($i=0; $i<count($All_Users); $i++)
			{
				if($All_Users[$i]->trim)
				{
					$Users_to_Trim[$j] = $All_Users[$i];
					$j++;
				}
			}

			if($j)
				$this->MDB->Trim_Database($Users_to_Trim);
		}
	}
}


function EPCNTR_Go_Error($error)
	{
		global $OPTION;
		
		if (file_exists($OPTION['Absolute_Path']."counter-error.php"))
		{	
			if ($OPTION['DebugMode'])
			{
				include($OPTION['Absolute_Path']."counter-error.php");
				ignore_user_abort(false);
				exit();
			}
		}
		elseif ($error = "bad_absolute_path")
		{
			echo "<b>COUNTER ERROR: Bad Absolute Path</b><br>Your absolute path value is incorrectly set in counter.php. To discover your absolute path, run the file abs.php that was packaged in the counter's zip file. After modifying counter.php refresh this page.";
			exit();
		}
		else
		{
			echo "<b>COUNTER ERROR: </b><br>The counter has an error. However, the error file could not be found to provide additional assistance. It is likely that your \"absolute path\" is not correctly set in counter.php or the counter's error file is missing.";
			exit();
		}
	}


/* ------------------------------------------------------------------ */
//	This function is called as opposed to the time() function throughout
//	the script. The function will return the current time adjusted for
//  the time offset variable.
/* ------------------------------------------------------------------ */

function time_offset()
{
	global $OPTION;
	return (time() + ($OPTION['time_offset'] * 3600)); 
}


/* We set this because aborting on a trim job could really mess up stats.
	By setting ignore_user_abort to true, we ensure the script executes all the way through. */
ignore_user_abort(true);


// Only check with this value if we have to, as the script including the counter may be using it
if (eregi("counter.php",$_SERVER['PHP_SELF'])) {
	// Yes, there are actually people out there who complain about getting notice errors.
	// This way we ensure that p gets assigned a value.
	if (!isset($_GET['p']))
	{
		$_GET['p'] = "main";
	}

	switch ($_GET['p'])
	{
		case "stats": $OPTION['Hide_Counter'] = 0;
					  $EPDEV_COUNTER = new EP_Dev_Counter();
					  echo $EPDEV_COUNTER->counter_display;
			break;

		case "time" : $EPDEV_COUNTER = new EP_Dev_Counter();
					echo $EPDEV_COUNTER->counter_display;
					$shorten = microtime(); 
					echo "<br>Page took ".number_format($shorten,3)." seconds.<br>"."Number of Queries: ".$EPDEV_COUNTER->MDB->query_num."<br><small>EP-Dev Counter version "."</small>";
			break;

		default : $EPDEV_COUNTER = new EP_Dev_Counter();
					echo $EPDEV_COUNTER->counter_display;
	}
}

// If we are being included (not accessed directly in browser), then do this
else
{
	global $EP_DEV_PHPNUKE_COUNTER_BLOCK;

	// Check if this counter is being included as a phpnuke block
	if (isset($EP_DEV_PHPNUKE_COUNTER_BLOCK) && $EP_DEV_PHPNUKE_COUNTER_BLOCK)
	{
		require_once("config.php");
		global $dbuname, $dbpass, $dbname, $dbhost, $prefix, $DB, $OPTION;

		// MySQL database username:
		$DB['username'] = $dbuname;
		// MySQL database password:
		$DB['password'] = $dbpass;
		// MySQL database name:
		$DB['name'] = $dbname;
		// MySQL database hostname:
		$DB['host'] = $dbhost;
		// MySQL database ext (default is "ep")
		$DB['ext'] = $prefix."_epdev_counter";
		// Set DB type to mysql
		$DB['type'] = 1;

		// Since we are working with php-nuke, we know the path.
		$OPTION['Absolute_Path'] = "counter/";

		// This will trip the script to not auto-load mysql config
		$DB['skip_config'] = true;

		$EPDEV_COUNTER = new EP_Dev_Counter();

		// $content is the variable that php-nuke wants returned.
		global $content;
		$content = $EPDEV_COUNTER->counter_display;
	}

	// if not block, echo out as usual.
	else
	{
		$EPDEV_COUNTER = new EP_Dev_Counter();
		echo $EPDEV_COUNTER->counter_display;
	}
}

// Final Cleanup ~ put user abort back to false in case this person has other php in the page.
ignore_user_abort(false);