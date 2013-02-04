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

/*
	FILE DATABASE class - a class created specifically for the /stats/ folder.
*/
require_once("../config_filedb.php");

class Main_Database
{
	var $archfile;
	var $datafile;
	var $dataformat = "%[a-zA-Z0-9.]|%[0-9x]|%[0-9x]|%[0-9x]\n";
	var $archformat = "%[a-zA-Z0-9.]|%[0-9x]|%[0-9x]\n";
	var $datadefault = "000.000.000.000.000|0xxxxxxxx|0xxxxxxxx|000000000xxx";
	var $archdefault = "0xxxxxxxxxxx|0xxxxxxxx|0xxxxxxxx";
	var $handle;

	function Main_Database()
	{
		global $DB;

		/* Clear the php cache */
		clearstatcache();
		$filepath = "../".$DB['arcfolder'].$DB['filename']."-";
		$this->archfile = $filepath."arch.inc";
		$this->datafile = $filepath."data.inc";
	}

	function Open_File($file, $permissions, $flock = "LOCK_SH")
	{
		global $DB;

		$this->handle = @fopen($file, $permissions);
		if (!$this->handle) die(Go_Error("fileopen"));

		if ($DB['flock'])
		{
			$this->File_Lock($this->handle, $flock);
		}
	}

	function File_Lock(&$filehandle, $fop)
	{
		switch($fop)
		{
				case "LOCK_SH" : $fop = 1;
					break;
				case "LOCK_EX" : $fop = 2;
					break;
				case "LOCK_UN" : $fop = 3;
		}

		flock($filehandle, $fop)
			or die(Go_Error("flock"));
	}

	function Close_File(&$filehandle)
	{
		global $DB;

		if ($DB['flock'])
		{
			$this->File_Lock($filehandle, "LOCK_UN");
		}

		fclose($filehandle);
	}

	function Format_Array($data, $formatdefault = "default")
	{
		/*
			This function takes data and inserts 'x' until a certain field is
			a certain length. This makes it easy to trim, as length is always
			known.
			If the array passed in is already formatted, it takes out the 
			formatting. Did you know? ... do-do-do-do :)
			Data contains same format as in main, but | is replaced by array
		*/

		if ($formatdefault == "default")
		{
			$formatdefault = $this->datadefault;
		}

		$format = false;
		foreach($data as $value)
		{
			if (ereg("x", $value))
			{
				$format = true;
			}
		}

		if ($format)
		{
			$data = str_replace("x", "", $data);
		}
		else
		{
			$defaultvalues = explode("|", $formatdefault);
			
			for($i=0; $i<count($data); $i++)
			{
				while(strlen($data[$i]) < strlen($defaultvalues[$i]))
				{
					$data[$i] .= "x";
				}
			}
		}

		return $data;
	}

	function GetAllStats(&$AllStats, &$TodaysStats, &$Total_Stats)
	{
		global $DB, $OPTION;

		$this->Open_File($this->archfile, 'rb');

		$yesterdays_date = date("MdY", time()-86400);

		$i=0;
		while($current_line = fscanf($this->handle, $this->archformat))
		{
			$current_line = $this->Format_Array($current_line, $this->archdefault);
			list($time, $hits, $visits) = $current_line;
			if ($time != 0)
			{
				$AllStats[$i] = new Stats_Day();
				$AllStats[$i]->hits = $hits;
				$AllStats[$i]->visits = $visits;
				$AllStats[$i]->time = $time;
				$AllStats[$i]->date = date("M: d (D)", $time);
				$total_hits += $hits;
				$total_visits += $visits;

				if (date("MdY", $time) == $yesterdays_date)
				{
					$yesterday_index = $i;
				}

				$i++;
			}
		}

		$this->Close_File($this->handle);

		$j=0;

		$this->Open_File($this->datafile, 'rb');

		$todays_date = date("MdY");
		$Users_Online = 0;

		while($current_line = fscanf($this->handle, $this->dataformat))
		{
			$current_line = $this->Format_Array($current_line, $this->datadefault);
			list($ip, $hits, $visits, $time) = $current_line;

			if (date("MdY", $time) == $todays_date)
			{
				if ($j < $OPTION['max_recent'])
				{
					$TodaysStats[$j] = new Stats_User();
					$TodaysStats[$j]->ip = $ip;
					$TodaysStats[$j]->hits = $hits;
					$TodaysStats[$j]->visits = $visits;
					$TodaysStats[$j]->time = $time;
					$j++;
				}

				if ($time > time() - (60*15))
				{
					$Users_Online++;
				}

				$total_dayhits += $hits;
				$total_dayvisits += $visits;
			}
			else
			{
				$AllStats[$yesterday_index]->hits += $hits;
				$AllStats[$yesterday_index]->visits += $visits;
				$total_hits += $hits;
				$total_visits += $visits;
			}
		}

		$AllStats[$i] = new Stats_Day();
		$AllStats[$i]->hits = $total_dayhits;
		$AllStats[$i]->visits = $total_dayvisits;
		$AllStats[$i]->time = mktime(0, 0, 0, date("m", $TodaysStats[0]->time), date("d", $TodaysStats[0]->time), date("Y", $TodaysStats[0]->time));
		$AllStats[$i]->date = date("M: d (D)", $AllStats[$i]->time);

		$total_hits += $total_dayhits;
		$total_visits += $total_dayvisits;

		$Total_Stats = new Stats_Total();
		$Total_Stats->hits = $total_hits;
		$Total_Stats->visits = $total_visits;
		$Total_Stats->days = $i+1;
		$Total_Stats->online = $Users_Online;

		$this->Close_File($this->handle);
	}


}