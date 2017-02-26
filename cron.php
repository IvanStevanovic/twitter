<?php

require_once("Twitter.php");

$tw = new Twitter();
$servername1 = "localhost";
$username1 = "root";
$password1 = "";
$dbname1 = "tweetsdb";
$connections1 = mysqli_connect($servername1,$username1,$password1,$dbname1);
$query1 = "SELECT title FROM tweets";
$res1 = mysqli_query($connections1,$query1);
//check if table tweets is empty
if(mysqli_num_rows($res1)==0){
 //fill database's tabel tweets for 1st time if emtpy.
 $tw->fillDataBase();   
}
else{
//recomended to update databes every 15 min cron job. 
$tw->cornUpdateDb();
}

