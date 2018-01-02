<?php
$filename = $argv[1];
$filename_arr = explode('/', $filename);
$shareOutput = trim(shell_exec("/home/pi/Dropbox-Uploader/dropbox_uploader.sh share ".end($filename_arr)));
$link = substr($shareOutput, strpos($shareOutput, 'https'));
$link = substr($link, 0, -1)."1";
echo $link;
