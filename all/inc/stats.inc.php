<?php
	namespace Stats;
	error_reporting(E_ALL);
	require_once("conf.inc.php");

	function addToStats()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		if ($ip != '127.0.0.1')
		{
			$contents = fetch("http://www.telize.com/geoip/$ip");
			$details = json_decode($contents);
			$city = $details->city;
			$country = $details->country;
			$isp = $details->isp;
			
			if (!isset($city) || !isset($country))
			{
				$contents = fetch("http://api.hostip.info/get_json.php?ip=$ip");
				$details = json_decode($contents);
				$city = $details->city;
				$country = $details->country_code;
			}
			
			if (!isset($city) || !isset($country))
			{
				$contents = fetch("https://ipinfo.io/$ip/json");
				$details = json_decode($contents);		
				$city = $details->city;
				$country = $details->country;
			
			}
			
			if (!isset($city) || !isset($country))
			{
				$contents = fetch('https://www.geoplugin.net/php.gp?ip='.$ip);
				$addrDetailsArr = unserialize($contents); 
				$city = $addrDetailsArr['geoplugin_city'];
				$country = $addrDetailsArr['geoplugin_countryName'];
			}
			
			$ua = substr($_SERVER['HTTP_USER_AGENT'], 0, 30);

			if (!file_exists(dirname(CONST_FILE_STATS))) mkdir(dirname(CONST_FILE_STATS));
			if (filesize(CONST_FILE_STATS) > 10000) rename(CONST_FILE_STATS, CONST_FILE_STATS.".old.".date("Ymd-His"));
			$fh = fopen(CONST_FILE_STATS, 'a') or die("can't open file");
			fwrite($fh, stripslashes(date("Y-m-d H:i:s")."\t".$_SERVER['REQUEST_URI']."\t".$ip."\t".$_SERVER['REMOTE_USER']."\t$city\t$country\t$isp\t$ua\n"));
			fclose($fh);
		}
	}
	
	function fetch($host) {
	
		if ( function_exists('curl_init') ) {
			//use cURL to fetch data
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'geoPlugin PHP Class v1.0');
			$response = curl_exec($ch);
			curl_close ($ch);
		} else if ( ini_get('allow_url_fopen') ) {
			//fall back to fopen()
			$response = file_get_contents($host, 'r');
		} else {
			trigger_error ('geoPlugin class Error: Cannot retrieve data. Either compile PHP with cURL support or enable allow_url_fopen in php.ini ', E_USER_ERROR);
			return;
		}
		//echo "### $response $host ###";
		return $response;
	}
	
?>