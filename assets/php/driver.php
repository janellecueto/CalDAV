<?php
/**
 * The goal of this script is to connect to a calendar using the SimpleCalDavClient.php API and add events to a given weekday
 */

require_once("../../simpleCalDAV/SimpleCalDAVClient.php");
include("Event.php");
include("WeekDay.php");
include("info.php");

$t = time(); // get timestamp for now
$d = new DateTime($t);
//  $t += (24 * 60 * 60); // add in a day to get to tomorrow
$tzoffset = date_offset_get(new DateTime);
$tstart = $t - ($t % (24 * 60 * 60)) - $tzoffset; // Take off seconds in the current day and take off the tzoffset since $t is in UTC
$tend   = $tstart + (24 * 60 * 60) - $tzoffset -1;   // Add in offset for a day and take off the tzoffset since $t is in UTC and 1 second to keep in same day
$stdate = gmstrftime("%Y%m%dT%H%M%SZ",$tstart);
$endate = gmstrftime("%Y%m%dT%H%M%SZ",$tend);

$employee = "greg-work_shared_by_greg.doffin";       //we need to loop through all employees,
$initials = "GD";

$client = new SimpleCalDAVClient();
$client->connect($owncloud, $oUser, $oPassword);
$arrayOfCalendars = $client->findCalendars();

$newWeekDay = new WeekDay($d->format("D"), gmstrftime("%Y-%m-%d", $tstart));

try{

    $client->setCalendar($arrayOfCalendars[$employee]);

    $events = $client->getEvents($stdate, $endate);

    foreach ($events as $event) {
        $item = $event->getData(); // grab the ics format data
        //	  echo "<br><pre>$item</pre>\n";                    //the $item is basically a huge blob of new line separated text values.

        $itemArr = explode("\n", $item);

        $summaryFound = $startFound = $endFound = false;        //there may be multiple entries for the summary and dates,
        $msummary = $mstart = $mend = "";                       //i believe the first occurrence is the most up to date...
        foreach($itemArr as $e){
            if(strpos($e, "SUMMARY") !== false && !$summaryFound){
                $arr = explode(":", $e);
                $msummary = $arr[sizeof($arr)-1];
                $summaryFound = true;
            }
            if(strpos($e, "DTSTART;TZID=America/Chicago:") !== false && !$startFound){
                $arr = explode(":", $e);
                $time = explode("T", $arr[1])[1];
                $mstart = $time;
                $startFound = true;
            }
            if(strpos($e, "DTEND;TZID=America/Chicago:") !== false && !$endFound){
                $arr = explode(":", $e);
                $time = explode("T", $arr[1])[1];
                $mend = $time;
                $endFound = true;
            }
        }
        $newWeekDay->addEvent($initials, $msummary, $mstart, $mend);
    } // foreach
    $newWeekDay->printEvents();
    echo "<br>";
    // print_r($newWeekDay->events);

} catch(Exception $e){
    echo $e->__toString();
}