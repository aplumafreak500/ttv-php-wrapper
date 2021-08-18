# Notice
Currently, host redirection and grabbing stream access keys are not working due to changes in Twitch's API. Feel free to submit pull requests to get this working if I'm too slow myself :p

# Twitch TV PHP Handler
This is a simple PHP framework for accessing Twitch TV streams.

## How to Use
* Upload the contents of this repository to a web server that supports PHP.

* Copy config.php.example to config.php.

* Edit config.php and insert the Client ID and Client Secret you get from Twitch's developer console. Also, be sure to generate an App Access Token and put it there too. (Details to be written)

* Then, open ttvplayer.php in a web browser:

[http://localhost/ttvplayer.php](http://127.0.0.1/ttvplayer.php)

## GET Parameters

* channel - Channel to tune into. Channel must be live. Defaults to "twitch".
* v - Tune to the video stream. If set to 0, use the audio-only stream. Defaults to 1.
* fmt - Zero-based stream ID. If a channel has quality options, this can be set from 0 for "Source" through 6 for 144p (some channels may have less options  available, depending on that channel's streaming settings). "fmt=6" is equalivalent to "v=0". Channels that do not have quality options can only use 0 for Source or 1 for Audio Only. Defaults to 0.

## License
This software is licensed under the GNU General Public License v3.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.

Copyright © 2016,2019,2021 Alex Pensinger (APLumaFreak500). All rights reserved.

This project is not affiliated with Twitch TV.
