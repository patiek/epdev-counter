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
	MYSQL DATABASE class - a class created specifically for the /stats/ folder.
*/

class Main_Database
{
	var $cn;
	var $db;

	function Main_Database()
	{
		global $DB;
		$this->cn = mysql_connect($DB['host'],$DB['username'],$DB['password'])
				or die(Go_Error(mysql_errno()));

		$this->db = mysql_select_db($DB['name'], $this->cn)
				or die(Go_Error(mysql_errno()));
	}

	function query($sql)
	{
		$return = mysql_query($sql, $this->cn)
			or die(Go_Error(mysql_errno()));

		return $return;
	}

	function GetAllStats(&$AllStats, &$TodaysStats, &$Total_Stats)
	{
		global $DB, $OPTION;

		$result = $this->query("SELECT date, hits, visits FROM ".$DB['ext']."_archives");

		$yesterdays_date = date("MdY", (time()-86400));

		$i=0;
		while(list($time, $hits, $visits) = mysql_fetch_array($result))
		{
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

		$j=0;
		$result2 = $this->query("SELECT ip, hits, visits, time FROM ".$DB['ext']."_stats");

		$todays_date = date("MdY");
		$Users_Online=0;

		/* Make sure there is something to work with */
		if(mysql_num_rows($result2))
		{
			while(list($ip, $hits, $visits, $time) = mysql_fetch_array($result2))
			{
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
		}
		else
		{
			$TodaysStats[$j] = new Stats_User();
			$TodaysStats[$j]->ip = "localhost";
			$TodaysStats[$j]->hits = 0;
			$TodaysStats[$j]->visits = 0;
			$TodaysStats[$j]->time = time();
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
	}

}