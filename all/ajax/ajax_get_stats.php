<?php
	error_reporting(E_ALL);
	require_once("../inc/conf.inc.php");
	
	chdir("..");// change directory to the same than index.php before going further
	
	$want_log = (isset($_GET['all_log']) || isset($_GET['last_log']) || isset($_GET['all_log_per_ip']));
	
	if (isset($_GET['all_log_digest']))
	{
		$nb_lines = 0;
		$countries_hash = array();
		$cities_hash = array();
		$users_hash = array();
		$last_date = 'None';
		foreach (glob(CONST_FILE_STATS."*") as $filepath)
		{
			foreach (file($filepath) as $line)
			{
				$tab = explode("\t", $line);
				$nb_lines++;
				$users_hash[$tab[3]] = 1;
				$cities_hash[$tab[4]] = 1;
				$countries_hash[$tab[5]] = 1;
				$last_date = $tab[0];
			}
		}
		echo "Total number of requests: $nb_lines<br />\n"
			."Last connection date: $last_date<br />\n"
			."Users: ".implode(',', array_keys($users_hash))."<br />\n"
			."Countries: ".implode(',', array_keys($countries_hash))."<br />\n"
			."Cities: ".implode(',', array_keys($cities_hash))."\n";
	}
	elseif ($want_log)
	{
		$lines = array();
		
		echo "<div class='connection_log'>\n"
			."<table>\n";

		if (isset($_GET['all_log_per_ip']))
		{	
			echo 
				"<tr style='background-color: #aaaaaa;'><td>Ip</td><td>Date begin</td><td>Date end</td><td>User(s)</td><td>City</td><td>Country</td>"
				."<td>Internet service provider</td><td>User-Agent (Browser)</td></tr>";
			$lines_per_ip;
			foreach (glob(CONST_FILE_STATS."*") as $filepath)
			{
				$lines = array_merge($lines, file($filepath));
			}
			sort($lines);
			$groups = array();
			$last_group = null;
			foreach ($lines as $line)
			{
				list($date_and_time, $path, $ip_adress, $user, $city, $country, $isp, $ua) = explode("\t", $line);
				if (!isset($last_group) || $last_group["ip"] != $ip_adress)
				{
					$last_group["date_end"] = $date_and_time;
					array_push($groups, $last_group);
					$last_group = array(
						"ip" => $ip_adress, "date_begin" => $date_and_time,
						"users" => array(), "city" => array(), "country" => array(), "isp" => array(), "ua" => array());
				}
				$last_group["users"][$user] = 1;
				$last_group["city"][$city] = 1;
				$last_group["country"][$country] = 1;
				$last_group["isp"][$isp] = 1;
				$last_group["ua"][$ua] = 1;
			}
			if (isset($last_group))
			{
				$last_group["date_end"] = $date_and_time;
				array_push($groups, $last_group);
			}
			sort($lines);
			foreach ($groups as $group)
			{
				echo "<tr>";
				echo "<td>".$group["ip"]."</td><td>".$group["date_begin"]."</td><td>".$group["date_end"]."</td>";
				echo "<td>".implode(',', array_keys($group["users"]))."</td>";
				echo "<td>".implode(',', array_keys($group["city"]))."</td>";
				echo "<td>".implode(',', array_keys($group["country"]))."</td>";
				echo "<td>".implode(',', array_keys($group["isp"]))."</td>";
				echo "<td>".implode(',', array_keys($group["ua"]))."</td>";
				echo "</tr>\n";
			}
		}
		else
		{
			echo 
				"<tr style='background-color: #aaaaaa;'><td>Date and time</td><td>Path</td><td>Ip address</td>"
				."<td>User</td><td>City</td><td>Country</td><td>Internet service provider</td><td>User-Agent (Browser)</td></tr>";
			if (isset($_GET['all_log']))
			{	
				foreach (glob(CONST_FILE_STATS."*") as $filepath)
				{
					$lines = array_merge($lines, file($filepath));
				}
			}
			elseif (isset($_GET['last_log']))
			{
				if (!file_exists(CONST_FILE_STATS)) echo '<tr><td>missing file '.CONST_FILE_STATS.'</td></tr>';
				$lines = array_merge($lines, file(CONST_FILE_STATS));
			}
			
			sort($lines);
			foreach ($lines as $line)
			{
				echo "<tr>";
				foreach (explode("\t", $line) as $tab) echo "<td>$tab</td>";
				echo "</tr>\n";
			}
		}
		echo "</table></div>";
	}
?>
			