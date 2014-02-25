<?php
$tstring="wht e-ver is the matter.txt";
echo $tstring . PHP_EOL;
$tstring = preg_replace("/[^\w.-]/","",$tstring);
echo $tstring . PHP_EOL;
