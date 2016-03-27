<?php

/*
    ttvstream.php - Twitch TV PHP Wrapper
    Copyright © 2016 Alex Pensinger (APLumaFreak500)

    This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (isset($_GET["channel"])) {
	$ch = htmlspecialchars($_GET["channel"]);
}
else {
	$ch = "twitch";
}

if (isset($_GET["v"])) {
	$v = intval(htmlspecialchars($_GET["v"]));
}
else {
	$v = 1;
}

$token = @fopen("http://api.twitch.tv/api/channels/$ch/access_token", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (PHP/5.4, TTVStreamHandler/1.0, Apache/2.4, cgi like Gecko)\r\nConnection: close\r\nHost: api.twitch.tv"))));

$stream_token=json_decode(stream_get_contents($token), true);

$m3u = @fopen("http://usher.twitch.tv/api/channel/hls/$ch.m3u8?player=twitchweb&token=".$stream_token["token"]."&sig=".$stream_token["sig"]."&allow_audio_only=true&allow_source=true&type=any&p=0", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (PHP/5.4, TTVStreamHandler/1.0, Apache/2.4, cgi like Gecko)\r\nConnection: close\r\nHost: usher.twitch.tv"))));
	
if ($m3u===false) {
	header("HTTP/1.1 404 Not Found");
	header("Content-Type: text/plain");
	echo "This channel is not live at the moment.";
	exit;
}
else {
	header("Content-Type: audio/x-mpegurl");
	// header("Content-Type: text/plain");
}

if ($v>=1) {
	echo stream_get_contents($m3u);
	exit;
}

else {
	$m3u_array=explode("\n", stream_get_contents($m3u));
	unset($m3u_array[0]);
	unset($m3u_array[1]);

	$m3u_array=array_chunk($m3u_array, 3);

	end($m3u_array);
	$ao_stm=prev($m3u_array);

	$ao_url=$ao_stm[2];

	$ao_m3u = @fopen("$ao_url", "r", false, stream_context_create(array(
		"http"=>array(
			"method"=>"GET",
			"header" =>"User-Agent: Mozilla/5.0 (PHP/5.4, TTVStreamHandler/1.0, Apache/2.4, cgi like Gecko)\r\nConnection: close"))));

	$ao_m3tx=stream_get_contents($ao_m3u);

	$pos=strpos($ao_url, "index-live.m3u8");

	$ao_host=substr($ao_url, 0, $pos);
	
	$stmjson = @fopen("https://api.twitch.tv/kraken/channels/$ch", "r", false, stream_context_create(array(
		"http"=>array(
			"method"=>"GET",
			"header" =>"User-Agent: Mozilla/5.0 (PHP/5.4, TTVStreamHandler/1.0, Apache/2.4, cgi like Gecko)\r\nConnection: close"))));
	
	$stminf=json_decode(stream_get_contents($stmjson), true);
	
	$ao_m3tx=str_replace("#EXTINF:4.000,", "#EXTINF:4.000,".$stminf["display_name"]." - ".$stminf["status"]." (Playing ".$stminf["game"].")", $ao_m3tx);

	$ao_m3tx=str_replace("index-", $ao_host."index-", $ao_m3tx);

	echo $ao_m3tx;
}
?>