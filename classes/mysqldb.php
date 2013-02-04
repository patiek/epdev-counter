<?php

if (eregi("mysqldb.php",$_SERVER['PHP_SELF'])) {
    Header("Location: index.php");
    die();
}

function Initiate_MySQL()
{
	global $OPTION, $DB;
	require_once($OPTION['Absolute_Path']."config_mysql.php");
}

class Main_Database
{
	var $cn;
	var $db;
	var $query_num;

	function Main_Database()
	{
		global $DB;

		if (!$DB['skip_config'])
			Initiate_MySQL();

		$this->cn = @mysql_connect($DB['host'],$DB['username'],$DB['password'])
				or die(EPCNTR_Go_Error(mysql_errno()));

		$this->db = @mysql_select_db($DB['name'], $this->cn)
				or die(EPCNTR_Go_Error(mysql_errno()));

		$this->query_num = 0;
	}

	function query($sql)
	{
		$return = mysql_query($sql, $this->cn)
			or die(EPCNTR_Go_Error(mysql_errno()));

		$this->query_num++;
		return $return;
	}

	function Get_User_Data($ip)
	{
		global $DB, $OPTION;

		$result = $this->query("SELECT hits, visits, time FROM ".$DB['ext']."_stats WHERE ip = '".$ip."'");

		if (mysql_num_rows($result))
		{
			$result_array = mysql_fetch_array($result);

			if ($OPTION['ArchiveStats'])
			{
				$archived = $this->Archive_Old_User($ip, $result_array[0], $result_array[1], $result_array[2]);
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
				$return['hits'] = $result_array[0];
				$return['visits'] = $result_array[1];
				$return['oldtime'] = $result_array[2];
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
		global $DB;

		$result = $this->query("INSERT INTO ".$DB['ext']."_stats (ip, time) VALUES ('".$ip."', '".$time."')");
	}

	function Update_Database_Entry($ip, $hits, $visits, $time)
	{
		global $DB;

		$result = $this->query("UPDATE ".$DB['ext']."_stats SET hits='".$hits."', visits='".$visits."', time='".$time."' WHERE ip='".$ip."'");
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
		global $DB;

		$result = $this->query("SELECT ip, hits, visits, time FROM ".$DB['ext']."_stats");

		$i=0;
		while(list($ip, $hits, $visits, $time) = mysql_fetch_row($result))
		{
			$AllUsers[$i] = new User_Information();
			$AllUsers[$i]->ip = $ip;
			$AllUsers[$i]->hits = $hits;
			$AllUsers[$i]->visits = $visits;
			$AllUsers[$i]->otime = $time;
			$AllUsers[$i]->Get_Online();
			$AllUsers[$i]->Determine_Trim();
			$i++;
		}

		/* 
			This next part grabs the total old hits/visits out of the archives and
			stores the information as a user that is not online and not recent.
		*/
		$result2 = $this->query("SELECT hits, visits FROM ".$DB['ext']."_archives WHERE date='0'");

		list($arc_hits, $arc_visits) = mysql_fetch_row($result2);

		$AllUsers[$i] = new User_Information();
		$AllUsers[$i]->ip = "0";
		$AllUsers[$i]->hits = $arc_hits;
		$AllUsers[$i]->visits = $arc_visits;
		$AllUsers[$i]->otime = time_offset();

		return $AllUsers;
	}

	function Trim_Database($users)
	{
		global $DB, $OPTION;

		/* 
			Cycle through users, deleting them, storing their hits/visits in seperate daily archives, 
			and storing the total of all deleted hits/visits in row "0" of archive table.
		*/
		for ($i=0; $i<count($users); $i++)
		{
			$result = $this->query("DELETE FROM ".$DB['ext']."_stats WHERE ip='".$users[$i]->ip."'");

			if ($result)
			{
				$visits += $users[$i]->visits;
				$hits += $users[$i]->hits;

				if ($OPTION['ArchiveStats'])
				{
					$time[$i] = mktime(0,0,0,date("m", $users[$i]->otime), date("d", $users[$i]->otime), date("Y", $users[$i]->otime));
					$result2 = $this->query("UPDATE ".$DB['ext']."_archives SET hits=hits+".$users[$i]->hits.", visits=visits+".$users[$i]->visits." WHERE date='".$time[$i]."'");

					if(!mysql_affected_rows($this->cn))
					{
						$this->query("INSERT INTO ".$DB['ext']."_archives (date, hits, visits) VALUES ('".$time[$i]."', '".$users[$i]->hits."', '".$users[$i]->visits."')");
					}
				}
			}
		}

		$result3 = $this->query("UPDATE ".$DB['ext']."_archives SET hits=hits+".$hits.", visits=visits+".$visits." WHERE date='0'");
	}
}