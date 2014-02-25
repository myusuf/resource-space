<?php
###############################
## ResourceSpace
## DO NOT COMMIT
###############################

# MySQL database settings
$mysql_server = '192.168.2.2';
$mysql_username = 'resourcespace';
$mysql_password = 'abc123';
$mysql_db = 'resourcespace';

# Base URL of the installation
$baseurl = ($_SERVER['HTTP_HOST'] == 'osidev-imac2.loctest.gov') ? 'http://osidev-imac2.loctest.gov/resourcespace' : "http://{$_SERVER['HTTP_HOST']}";

# Email settings
$email_from = 'fleo@loc.gov';
$email_notify = 'fleo@loc.gov';