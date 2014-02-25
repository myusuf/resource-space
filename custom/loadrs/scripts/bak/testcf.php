<?php
$stringin = "clean=true,check=true";
$ProcessConfig=array();
$parse1 = explode(",",$stringin);
print_r($parse1);
sleep(5);
$cback = create_function('$val,&$config','$parse2 = explode(\'=\',$val);if ($parse2) { $config[strtoupper($parse2[0])] = $parse2[1];} else { $config[strtoupper($parse2[0])] = \'1\';}');
array_walk($parse1, $cback,$ProcessConfig);
print_r($ProcessConfig);

?>
