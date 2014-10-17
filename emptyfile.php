<?php
header("Content-Type: application/force-download; name=\"".$_GET['name']."\""); 
header("Content-Transfer-Encoding: binary"); 
header("Content-Length: 0"); 
header("Content-Disposition: attachment; filename=\"".$_GET['name']."\""); 
header("Expires: 0"); 
header("Cache-Control: no-cache, must-revalidate"); 
header("Pragma: no-cache"); 
exit();
?>