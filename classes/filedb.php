<?php

if (eregi("filedb.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}

function Initiate_FileDB()
{
	global $OPTION, $DB;
	require_once($OPTION['Absolute_Path']."config_filedb.php");
}

class Main_Database
{
	var $handle;
	var $datafile;
	var $archfile;
	var $dataformat;
	var $datalength;
	var $datadefault;
	var $archformat;
	var $archlength;
	var $archdefault;
	var $cday;
	var $ippos;
	var $openpos;
	var $query_num;

	function Main_Database()
	{
		Initiate_FileDB();
		global $DB, $OPTION;

		/* Clear the php cache */
		clearstatcache();

		/* 
			Line format is IP | hits | visits | time
			line format: 000.000.000.000.000|123456789|123456789|123456789xxx
			Byte length (for above format): 52 + 1 (\n)
			arch format is time | hits | visits
		*/
		$this->dataformat = "%[a-zA-Z0-9.]|%[0-9x]|%[0-9x]|%[0-9x]\n";
		$this->datalength = 53;
		$this->datadefault = "000.000.000.000.000|0xxxxxxxx|0xxxxxxxx|000000000xxx";

		$this->archformat = "%[a-zA-Z0-9.]|%[0-9x]|%[0-9x]\n";
		$this->archlength = 33;
		$this->archdefault = "0xxxxxxxxxxx|0xxxxxxxx|0xxxxxxxx";

		$this->cday = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
		$filepath = $OPTION['Absolute_Path'].$DB['arcfolder'].$DB['filename']."-";
		$this->archfile = $filepath."arch.inc";
		$this->datafile = $filepath."data.inc";
		$this->referrerfile = $filepath."referrer.inc";

		$this->query_num = "N/A -- File Database Used";
	}

	function Open_File($file, $permissions, $flock = "LOCK_SH")
	{
		global $DB;

		$this->handle = @fopen($file, $permissions);
		if (!$this->handle) die(EPCNTR_Go_Error("fileopen"));

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
			or die(EPCNTR_Go_Error("flock"));
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

	function Create_Line($data_array)
	{
		for ($i=0; $i<count($data_array) - 1; $i++)
		{
			$newline .= $data_array[$i]."|";
		}
		$newline .= $data_array[$i]."\n";

		return $newline;
	}

	function Get_User_Data($ip)
	{
		global $OPTION;

		$this->Open_File($this->datafile, 'rb');

		/*
			Format $ip will format according to file format. $ipfound will help
			save time by stopping the search when $ip is found in file.
		*/
		$ip2 = $this->Format_Array(array($ip));
		$ip = $ip2[0];
		$cline = 0;
		$ipfound = false;
		$j=0;

		while(($current_line = fscanf($this->handle, $this->dataformat)) && !$ipfound)
		{
			if ($current_line[0] == $ip)
			{
				$ipfound = true;
				$result_array = $this->Format_Array($current_line);
				$this->ippos[$result_array[0]] = $cline * $this->datalength;
			}
			elseif($current_line[0] == "000.000.000.000.000")
			{
				$this->openpos[$j] = $cline * $this->datalength;
				$j++;
				$cline++;
			}
			else
			{
				$cline++;
			}
		}

		$this->Close_File($this->handle);

		if ($ipfound)
		{
			if ($OPTION['ArchiveStats'])
			{
				$archived = $this->Archive_Old_User($result_array[0], $result_array[1], $result_array[2], $result_array[3]);
			}
			else
			{
				$archived = false;
			}

			if ($archived)
			{
				/* Marked as newvisit, since old was removed by archiving */
				$return['newvisit'] = true;
			}
			else
			{
				$return['hits'] = $result_array[1];
				$return['visits'] = $result_array[2];
				$return['oldtime'] = $result_array[3];
				$return['newvisit'] = false;
			}
		}
		else
		{
			$return['newvisit'] = true;
		}

		return $return;
	}

	function Insert_Into_Database($ip, $time)
	{
		$this->Open_File($this->datafile, 'r+b', "LOCK_EX");

		if (!empty($this->openpos))
		{
			fseek($this->handle, $this->openpos[0]);
		}
		else
		{
			fseek($this->handle, 0, SEEK_END);
		}

		$newline = $this->Create_Line($this->Format_Array(array($ip, "1", "1", $time)));

		fwrite($this->handle, $newline);

		$this->Close_File($this->handle);
	}

	function Update_Database_Entry($ip, $hits, $visits, $time)
	{
		$this->Open_File($this->datafile, 'r+b', "LOCK_EX");
		fseek($this->handle, $this->ippos[$ip]);

		$newline = $this->Create_Line($this->Format_Array(array($ip, $hits, $visits, $time)));
		fwrite($this->handle, $newline);

		$this->Close_File($this->handle);
	}

	function Archive_Old_User($ip, $hits, $visits, $otime)
	{
		/* Checks if user's time day is older than current day and trims if needed */
		if (date("mdY", $otime) != date("mdY"))
		{
			$users[0] = new User_Information();
			$users[0]->ip = $ip;
			$users[0]->hits = $hits;
			$users[0]->visits = $visits;
			$users[0]->otime = $otime;
			$this->Trim_Database($users);
			$return = true;
		}
		else
		{
			$return = false;
		}
		return $return;
	}

	function Get_All_Users()
	{
		$this->Open_File($this->datafile, 'rb');

		$i=0;
		$cline=0;
		while ($current_line = fscanf($this->handle, $this->dataformat))
		{
			$formatted_line = $this->Format_Array($current_line);
			list($ip, $hits, $visits, $time) = $formatted_line;
			if ($ip != "000.000.000.000.000")
			{
				$this->ippos[$ip] = $cline * $this->datalength;
				$AllUsers[$i] = new User_Information();
				$AllUsers[$i]->ip = $ip;
				$AllUsers[$i]->hits = $hits;
				$AllUsers[$i]->visits = $visits;
				$AllUsers[$i]->otime = $time;
				$AllUsers[$i]->Get_Online();
				$AllUsers[$i]->Determine_Trim();
				$i++;
			}
			$cline++;
		}

		$this->Close_File($this->handle);

		$this->Open_File($this->archfile, 'rb');

		$main_archive = fgets($this->handle, $this->archlength);
		$archive_array = explode("|", $main_archive);
		$main_data = $this->Format_Array($archive_array, $this->archdefault);

		$AllUsers[$i] = new User_Information();
		$AllUsers[$i]->ip = "0";
		$AllUsers[$i]->hits = $main_data[1];
		$AllUsers[$i]->visits = $main_data[2];
		$AllUsers[$i]->otime = time_offset();

		$this->Close_File($this->handle);

		return $AllUsers;
	}

	function Trim_Database($users)
	{
		global $DB, $OPTION;

		/* 
			Cycle through users, deleting them, storing their hits/visits in seperate daily archives, 
			and storing the total of all deleted hits/visits in row "0" of archive table.
		*/

		$this->Open_File($this->datafile, 'r+b', "LOCK_EX");

		for ($i=0; $i<count($users); $i++)
		{
			fseek($this->handle, $this->ippos[$users[$i]->ip]);
			$result = fwrite($this->handle, $this->datadefault);
		}

		$this->Close_File($this->handle);
		$this->Open_File($this->archfile, 'r+b', "LOCK_EX");

		for ($i=0; $i<count($users); $i++)
		{
			$cline = 0;
			rewind($this->handle);

			if ($result)
			{
				$visits += $users[$i]->visits;
				$hits += $users[$i]->hits;

				if ($OPTION['ArchiveStats'])
				{
					$time[$i] = mktime(0,0,0,date("m", $users[$i]->otime), date("d", $users[$i]->otime), date("Y", $users[$i]->otime));

					$time_temp = $this->Format_Array(array($time[$i]), $this->archdefault);
					$real_time = $time_temp[0]; 

					$timefound = false;
					while(($current_line = fscanf($this->handle, $this->archformat)) && !$timefound)
					{
						if ($current_line[0] == $real_time)
						{
							$timefound = true;
							$result_array = $this->Format_Array($current_line, $this->archdefault);
							$curpos[$cline] = $cline * $this->archlength;

							$newline = $this->Create_Line($this->Format_Array(array($result_array[0], ($result_array[1] + $users[$i]->hits), ($result_array[2] + $users[$i]->visits)), $this->archdefault));

							fseek($this->handle, $curpos[$cline]);
							fwrite($this->handle, $newline);
						}
						else
						{
							$cline++;
						}
					}

					if (!$timefound)
					{
						fseek($this->handle, 0, SEEK_END);
						$newline = $this->Create_Line($this->Format_Array(array($time[$i], $users[$i]->hits, $users[$i]->visits), $this->archdefault));
						fwrite($this->handle, $newline);
					}
				}
			}
		}
		
		rewind($this->handle);
		$main_archive = fgets($this->handle, $this->archlength);
		$archive_array = explode("|", $main_archive);
		$main_data = $this->Format_Array($archive_array, $this->archdefault);

		rewind($this->handle);
		$newline = $this->Create_Line($this->Format_Array(array($main_data[0], ($main_data[1] + $hits), ($main_data[2] + $visits)), $this->archdefault));
		fwrite($this->handle, $newline);

		$this->Close_File($this->handle);
	}

	function Log_Referrer($referrer)
	{
		$this->Open_File($this->referrerfile, 'rb', "LOCK_EX");

		while ($current_line = fscanf($this->handle, $this->referrerformat))
		{

		}

	}
}