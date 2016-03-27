<?php

/*
    ttvplayer.php - Twitch TV PHP Wrapper
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

if (isset($_GET["v"]) && intval($_GET["v"])>=1) {
	$v = intval(htmlspecialchars($_GET["v"]));
}
else {
	$v = 1;
}
?>
<html>
	<head>
		<title>ttvplayer</title>
	</head>
	<body>
		<video controls width="720" height="360">
			<?php
				echo "<source src=\"/ttvstream.php?channel=$ch&v=$v\">\n";
			?>
		</video>
	</body>
</html>