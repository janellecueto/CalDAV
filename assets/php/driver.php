<?php
/**
 * The goal of this script is to connect to a calendar using the SimpleCalDavClient.php API and add events to a given weekday
 */

require_once("../../../simpleCalDAV/CalDAVClient.php");
include("Event.php");
include("WeekDay.php");
include("info.php");

$t = time(); // get timestamp for now
$d = date($t);
//  $t += (24 * 60 * 60); // add in a day to get to tomorrow
$tzoffset = date_offset_get(new DateTime);
$tstart = $t - ($t % (24 * 60 * 60)) - $tzoffset; // Take off seconds in the current day and take off the tzoffset since $t is in UTC
$tend   = $tstart + (24 * 60 * 60) - $tzoffset -1;   // Add in offset for a day and take off the tzoffset since $t is in UTC and 1 second to keep in same day
$stdate = gmstrftime("%Y%m%dT%H%M%SZ",$tstart);
$endate = gmstrftime("%Y%m%dT%H%M%SZ",$tend);

$employee = "steve-word_shared_by_steve.barnard";       //we need to loop through all employees,

$client = new SimpleCalDAVClient();
$newWeekDay = new WeekDay($d->format("D"), gmstrftime("%Y-%m-%d", $tstart));

try{
    $client->connect($owncloud, $oUser, $oPassword);

    $arrayOfCalendars = $client->findCalendars();

    $client->setCalendar($arrayOfCalendars[$employee]);

    $events = $client->getEvents($stdate, $endate);



} catch(Exception $e){
    echo $e->__toString();
}