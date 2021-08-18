<?php

/*
    ttvstream.php - Twitch TV PHP Wrapper
    Copyright © 2021 Alex Pensinger (APLumaFreak500)

    This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// GET parsing

if (isset($_GET["channel"])) {
	$ch_name = htmlspecialchars($_GET["channel"]);
}
else {
	$ch_name = "twitch";
}

if (isset($_GET["v"])) {
	$v = intval(htmlspecialchars($_GET["v"]));
}
else {
	$v = 1;
}

if (isset($_GET["fmt"])) {
	$fmt = intval(htmlspecialchars($_GET["fmt"]));
}
else {
	$fmt = 0;
}

if (isset($_GET["raw"])) {
	$process_m3u8 = True;
}
else {
	$process_m3u8 = False;
}

// 2021-08-17: "App Access Token" changes
include("config.php");

if (!isset($client_id)) {
	die("Client-ID isn't set.");
}

if (!isset($client_secret)) {
	die("Client Secret isn't set.");
}

if (!isset($access_token)) {
	die("Access token isn't set.");
}

// Get ID for specified login

$ch_json=@fopen("https://api.twitch.tv/helix/users?login=$ch_name", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Z717VL Build/LMY47V; U; en-us) TTVStreamHandler/1.5 (PHP/5.4; Apache/2.4)\r\nClient-ID: $client_id\r\nAuthorization: Bearer $access_token\r\nConnection: close"),
	"ssl"=>array(
		"security_level"=>1)
)));
if ($ch_json===false) {
	$ch="0";
}
else {
	$ch_inf=json_decode(stream_get_contents($ch_json), true);
	$ch=$ch_inf["data"][0]["id"];
}

// Check if this channel is hosting someone

$check_host=@fopen("https://tmi.twitch.tv/hosts?include_logins=1&host=$ch", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Z717VL Build/LMY47V; U; en-us) TTVStreamHandler/1.5 (PHP/5.4; Apache/2.4)\r\nClient-ID: $client_id\r\nAuthorization: Bearer $access_token\r\nConnection: close"),
	"ssl"=>array(
		"security_level"=>1)
)));

if ($check_host===false) {
	$host_info=False;
}
else {
	$host_info=json_decode(stream_get_contents($check_host), true);
}

// If the target is hosting someone, redirect the stream requests to the hosted channel

if ($host_info!==False && @$host_info["hosts"][0]["target_id"]) {
	$ch_host=$host_info["hosts"][0]["host_display_name"];
	$ch_name=$host_info["hosts"][0]["target_login"];
	$ch=$host_info["hosts"][0]["target_id"];
}

$stmjson = @fopen("https://api.twitch.tv/helix/channels?broadcaster_id=$ch", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Z717VL Build/LMY47V; U; en-us) TTVStreamHandler/1.5 (PHP/5.4; Apache/2.4)\r\nClient-ID: $client_id\r\nAuthorization: Bearer $access_token\r\nConnection: close"),
	"ssl"=>array(
		"security_level"=>1)
)));

if ($stmjson===false) {
	$stminf=json_decode("{\"status\":null,\"display_name\":\"$ch_name\",\"game\":null}",True);
}
else {
	$stminf=json_decode(stream_get_contents($stmjson), true);
}

$token = @fopen("https://api.twitch.tv/api/channels/$ch_name/access_token", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Z717VL Build/LMY47V; U; en-us) TTVStreamHandler/1.5 (PHP/5.4; Apache/2.4)\r\nClient-ID: $client_id\r\nAuthorization: Bearer $access_token\r\nConnection: close\r\nHost: api.twitch.tv"),
	"ssl"=>array(
		"security_level"=>1)
)));

if ($token===false) {
	header("HTTP/1.1 404 Not Found");
	header("Content-Type: text/plain");
	echo "Could not obtain an access token for channel $ch_name. Try again later.";
	exit;
}

$stream_token=json_decode(stream_get_contents($token), true);

$p=rand(0,100000000);

$m3u = @fopen("https://usher.ttvnw.net/api/channel/hls/$ch_name.m3u8?player=twitchweb&token=".urlencode($stream_token["token"])."&sig=".$stream_token["sig"]."&allow_audio_only=true&allow_source=true&type=any&p=$p&allow_spect=true", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Z717VL Build/LMY47V; U; en-us) TTVStreamHandler/1.5 (PHP/5.4; Apache/2.4)\r\nClient-ID: $client_id,\r\nConnection: close\r\nHost: usher.ttvnw.net"),
	"ssl"=>array(
		"security_level"=>1)
)));
if ($m3u===false) {
	header("HTTP/1.1 404 Not Found");
	header("Content-Type: text/plain");
	echo "The channel $ch_name is not live at the moment. Try again later.";
	exit;
}
else {
	//header("Content-Type: audio/x-mpegurl");
	header("Content-Type: text/plain");
}

$m3u_array=explode("\n", stream_get_contents($m3u));

if ($v<=0) {
	$index=count($m3u_array)-2;
}
else {
	if ((($fmt+1)*3)+1 < count($m3u_array)) {
		$index=(($fmt+1)*3)+1;
	}
	else {
		$index=4;
	}
}

$ao_url=$m3u_array[$index];

if ($process_m3u8 != True) {
	echo $ao_url;
	exit;
}

$ao_m3u = @fopen("$ao_url", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Z717VL Build/LMY47V; U; en-us) TTVStreamHandler/1.5 (PHP/5.4; Apache/2.4)\r\nClient-ID: $client_id\r\nAuthorization: Bearer $access_token\r\nConnection: close"),
	"ssl"=>array(
		"security_level"=>1)
)));

$ao_m3tx=stream_get_contents($ao_m3u);

$pos=strpos($ao_url, "index-live.m3u8");

$ao_host=substr($ao_url, 0, $pos);

if (@$ch_host) {
	$stm_metadata=$ch_host." hosting ".$stminf["display_name"]." - ".$stminf["status"]." (Playing ".$stminf["game"].")";
}
else {
	$stm_metadata=$stminf["display_name"]." - ".$stminf["status"]." (Playing ".$stminf["game"].")";
}

$ao_m3tx=str_replace("#EXTINF:2.000,", "#EXTINF:2.000,".$stm_metadata, $ao_m3tx);

$ao_m3tx=str_replace("index-", $ao_host."index-", $ao_m3tx);

echo $ao_m3tx;
?>
