<?php

/*
    ttvplayer.php - Twitch TV PHP Wrapper
    Copyright © 2016 Alex Pensinger (APLumaFreak500)

    This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (isset($_GET["channel"])) {
	$ch_name = htmlspecialchars($_GET["channel"]);
}
else {
	$ch_name = "twitch";
}

if (isset($_GET["v"]) && intval($_GET["v"])>=1) {
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

// Get ID for specified login

$ch_json=@fopen("https://api.twitch.tv/kraken/users?login=$ch_name", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Z717VL Build/LMY47V; U; en-us) TTVStreamHandler/1.5 (PHP/5.4; Apache/2.4)\r\nClient-ID: 1akvowyyvu4s4avdx9ftilze7zt7jtb\r\nAccept: application/vnd.twitchtv.v5+json\r\nConnection: close"))));
if ($ch_json===false) {
	$ch="0";
}
else {
	$ch_inf=json_decode(stream_get_contents($ch_json), true);
	$ch=$ch_inf["users"][0]["_id"];
}

// Check if this channel is hosting someone

$check_host=@fopen("https://tmi.twitch.tv/hosts?include_logins=1&host=$ch", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Z717VL Build/LMY47V; U; en-us) TTVStreamHandler/1.5 (PHP/5.4; Apache/2.4)\r\nClient-ID: 1akvowyyvu4s4avdx9ftilze7zt7jtb\r\nAccept: application/vnd.twitchtv.v5+json\r\nConnection: close"))));
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

// Stream metadata

$stmjson = @fopen("https://api.twitch.tv/kraken/channels/$ch", "r", false, stream_context_create(array(
	"http"=>array(
		"method"=>"GET",
		"header" =>"User-Agent: Mozilla/5.0 (Linux; Android 5.1.1; Z717VL Build/LMY47V; U; en-us) TTVStreamHandler/1.5 (PHP/5.4; Apache/2.4)\r\nClient-ID: 1akvowyyvu4s4avdx9ftilze7zt7jtb\r\nAccept: application/vnd.twitchtv.v5+json\r\nConnection: close"))));

if ($stmjson===false) {
	$stminf=json_decode("{\"status\":null,\"display_name\":\"$ch_name\",\"game\":null}",True);
}
else {
	$stminf=json_decode(stream_get_contents($stmjson), true);
}

if (@$ch_host) {
	$stm_metadata=$ch_host." hosting ".$stminf["display_name"]." - ".$stminf["status"]." (Playing ".$stminf["game"].")";
	$ch_access=$host_info["hosts"][0]["host_login"];
}
else {
	$stm_metadata=$stminf["display_name"]." - ".$stminf["status"]." (Playing ".$stminf["game"].")";
	$ch_access=$ch_name;
}

?>
<html>
	<head>
		<?php
			echo "<title>".$stm_metadata."</title>";
		?>
	</head>
	<body>
		<h1><?php echo $stminf["status"]; ?></h1>
		<h2><?php echo $stminf["display_name"]." playing ".$stminf["game"]; ?></h2>
		<video controls width="720" height="360">
			<?php
				echo "<source src=\"ttvstream.php?channel=$ch_access&v=$v&fmt=$fmt\">\n";
			?>
		</video>
	</body>
</html>
