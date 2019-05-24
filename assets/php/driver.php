<?php
/**
 * The goal of this script is to connect to a calendar using the SimpleCalDavClient.php API and add events to a given weekday
 */

require_once("../../simpleCalDAV/SimpleCalDAVClient.php");
include("Event.php");
include("WeekDay.php");
include("info.php");

// $t = time(); // get timestamp for now, the plan is to pass in time for each day.

$startDate = "";
if(array_key_exists("startDate", $_GET)) $startDate = $_GET['startDate'];
$endDate = "";
if(array_key_exists("endDate", $_GET)) $endDate = $_GET['endDate'];
$emp = 0;
if(array_key_exists("emp", $_GET)) $employee = $_GET['emp'];

try {
    $sd = new DateTime($startDate);
    $ed = new DateTime($endDate);
}catch(Exception $e){
    echo "Error 1: ".$e->__toString();
    exit(0);
}       //exit if dates are in incorrect format

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

//we need to keep track of dates, the event data that $client->getEvents() returns does not contain information on date of event
while($sd <= $ed) {
    $st = $sd->getTimestamp();
    $tzoffset = date_offset_get($sd);
    $tstart = $st - ($st % (24 * 60 * 60)) - $tzoffset; // Take off seconds in the current day and take off the tzoffset since $t is in UTC
    $tend   = $tstart + (24 * 60 * 60) - $tzoffset -1;   // Add in offset for a day and take off the tzoffset since $t is in UTC and 1 second to keep in same day

    $stdate = gmstrftime("%Y%m%dT%H%M%SZ",$tstart);         //DateTime()->format("Ymd\THis\Z");
    $endate = gmstrftime("%Y%m%dT%H%M%SZ",$tend);

    $newWeekDay = new WeekDay($sd->format("l"), $sd->format("Y-m-d"));

    foreach ($rows as $row) {     //loop through all employees
        // $client->setCalendar($arrayOfCalendars["old"]);
        $client->setCalendar($arrayOfCalendars[strtolower($row[1])]);   //set their calendar

        $events = $client->getEvents($stdate, $endate);

        foreach ($events as $event) {
            $item = $event->getData(); // grab the ics format data
//            echo "<br><pre>$item</pre>\n";                    //the $item is basically a huge blob of new line separated text values.

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
            $newWeekDay->addEvent(strtoupper($row[0]), $msummary, $mstart, $mend);
        } // foreach event
    }   // foreach employee
    $sd->modify('+1 day');
    $weekdays[] = $newWeekDay;
}   //end while


// $newWeekDay->printEvents();
//    echo "<br>";
// print_r($newWeekDay->events);
//echo json_encode($newWeekDay);
echo json_encode($weekdays);


