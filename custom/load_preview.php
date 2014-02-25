<?php
include("rspaceapi.php");

$rtn = upload_preview($_POST['id']);
echo "{$rtn}\n";

?>
