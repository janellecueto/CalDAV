<?php
/**
 * The goal of this script is to connect to a calendar using the SimpleCalDavClient.php API and add events to a given weekday
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


foreach ($rows as $row) {     //loop through all employees
        // $client->setCalendar($arrayOfCalendars["old"]);
    $client->setCalendar($arrayOfCalendars[strtolower($row[1])]);   //set their calendar

    //we need to keep track of dates, the event data that $client->getEvents() returns does not contain information on date of event    
    $cd = new DateTime($startDate);
    // echo $cd->format("Y-m-d")."<br><br>";
    $counter = 0;
    while($cd <= $ed) {
        $st = $cd->getTimestamp();
        $tzoffset = date_offset_get($cd);
        $tstart = $st - ($st % (24 * 60 * 60)) - $tzoffset; // Take off seconds in the current day and take off the tzoffset since $t is in UTC
        $tend   = $tstart + (24 * 60 * 60) - $tzoffset -1;   // Add in offset for a day and take off the tzoffset since $t is in UTC and 1 second to keep in same day

        $stdate = gmstrftime("%Y%m%dT%H%M%SZ",$tstart);         //DateTime()->format("Ymd\THis\Z");
        $endate = gmstrftime("%Y%m%dT%H%M%SZ",$tend);

        $events = $client->getEvents($stdate, $endate);
        
        foreach ($events as $event) {
            $item = $event->getData(); // grab the ics format data
        //    echo "<br><pre>$item</pre>\n";                    //the $item is basically a huge blob of new line separated text values.

            $itemArr = explode("\n", $item);

            $summaryFound = $startFound = $endFound = false;        //there may be multiple entries for the summary and dates,
            $msummary = $mstart = $mend = "";                       //i believe the first occurrence is the most up to date...
            foreach ($itemArr as $e) {
                if (strpos($e, "SUMMARY") !== false && !$summaryFound) {
                    $arr = explode(":", $e);
                    $msummary = $arr[sizeof($arr) - 1];
                    $summaryFound = true;
                }
                if (strpos($e, "DTSTART;TZID=America/Chicago:") !== false && !$startFound) {
                    $arr = explode(":", $e);
                    $time = explode("T", $arr[1])[1];
                    $mstart = $time;
                    $startFound = true;
                }
                if (strpos($e, "DTEND;TZID=America/Chicago:") !== false && !$endFound) {
                    $arr = explode(":", $e);
                    $time = explode("T", $arr[1])[1];
                    $mend = $time;
                    $endFound = true;
                }
            }
            // $newWeekDay->addEvent(strtoupper($row[0]), $msummary, $mstart, $mend);
            // echo $row[0]."---$msummary---$mstart-$mend<br>";
            $weekdays[$counter]->addEvent(strtoupper($row[0]), $msummary, $mstart, $mend);
        } // foreach event

        $cd->modify('+1 day');
        $counter++;
    }   //end while
}   // foreach employee


// print_r($weekdays);

if($debug) {
    $t2 = time();
    echo $t2."<br>";
    echo "time elasped: ".($t2-$t1)."s<br>";
}
echo json_encode($weekdays);


