<?php
/**
 * The goal of this script is to connect to a calendar using the SimpleCalDavClient.php library and add events to a given weekday
 */

require_once("../../simpleCalDAV/SimpleCalDAVClient.php");
include("Event.php");
include("WeekDay.php");
include("info.php");

$debug = true;

$startDate = "";
if(array_key_exists("startDate", $_GET)) $startDate = $_GET['startDate'];
$endDate = "";
if(array_key_exists("endDate", $_GET)) $endDate = $_GET['endDate'];
$emp = 0;
if(array_key_exists("emp", $_GET)) $emp = $_GET['emp'];

$t1 = time();
if ($debug) echo $t1."<br><br>";

try {
    $sd = new DateTime($startDate);
    $ed = new DateTime($endDate);
}catch(Exception $e){
    echo "Error 1: ".$e->__toString();
    exit(0);
}       //exit if dates are in incorrect format

// echo "start date sd: ".$sd->format("Y-m-d")."<br><br>";

$conn = new mysqli($host, $user, $password, $timeTbl);
if($conn->errno){
    echo "Error: ".$conn->error;
    exit(0);
}       //exit if we can't connect to database

$query = "SELECT initials, caldav FROM time.employee";
if($emp){
    $query .= " WHERE initials = '$emp'";
}

$result = $conn->query($query);
$rows = $result->fetch_all();       //array of arrays size 2 [0] => initials, [1] => caldav

$conn->close();

$conn = new mysqli("owncloud.dei-pe.com", "root", "bard\$rover");
if($conn->errno){
    echo "Error connecting to db: ".$conn->error;
    exit();
}

$query = "SHOW DATABASES";
$result = $conn->query($query);
while($row = $result->fetch_row()){
    echo $row[0]."<br>";
}
$conn->close();

//set up SimpleCalDAVClient and connect to DEI calendars
$client = new SimpleCalDAVClient();
$client->connect($owncloud, $oUser, $oPassword);
$arrayOfCalendars = $client->findCalendars();

$weekdays = [];

//init weekdays array of WeekDay objects
$cd = new DateTime($startDate);
while($cd <= $ed) {
    $newWeekDay = new WeekDay($cd->format("l"), $cd->format("Y-m-d"));
    $weekdays[] = $newWeekDay;
    $cd->modify('+1 day');
}   





