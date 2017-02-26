<?php

require_once("Twitter.php");

$tw = new Twitter();
//fill database for 1st time.
//$tw->fillDataBase();
//recomended to update databes every 15 min cron job. 
$tw->cornUpdateDb();
