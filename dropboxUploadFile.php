<?php
$filename = $argv[1];
$filename_arr = explode('/', $filename);
shell_exec("/home/pi/Dropbox-Uploader/dropbox_uploader.sh upload $filename ".end($filename_arr));
