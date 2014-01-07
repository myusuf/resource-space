<?php
###############################
## ResourceSpace
## Local Configuration Script
###############################

# All custom settings should be entered in this file.
# Options may be copied from config.default.php and configured here.

# MySQL database settings
$mysql_server = '192.168.2.2';
$mysql_username = 'resourcespace';
$mysql_password = 'abc123';
$mysql_db = 'resourcespace';

# Base URL of the installation
$baseurl = 'http://resourcespace-local';

# Email settings
$email_from = 'fleo@loc.gov';
$email_notify = 'fleo@loc.gov';

$spider_password = 'qeMA3E5YraPy';
$scramble_key = 'nyNeGy8A6uNy';

$api_scramble_key = '7YnAGenUmA4y';

# Paths
$ftp_server = 'my.ftp.server';
$ftp_username = 'my_username';
$ftp_password = 'my_password';
$ftp_defaultfolder = 'temp/';
$thumbs_display_fields = array(8,3);
$list_display_fields = array(8,3,12);
$sort_fields = array(12);

# image magick
$imagemagick_path='/usr/bin';
$ghostscript_path='/usr/bin';
$ghostscript_executable='gs';

# If using FFMpeg to generate video thumbs and previews, uncomment and set next line.
$ffmpeg_path='/usr/bin';