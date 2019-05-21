<?php


class WeekDay
{
    public function __construct($day, $date)
    {
        $this->day = $day;              //day as string (i.e. "Monday", "Wednesday", etc.)
        $this->date = $date;            //date in format (Y-m-d)
        $this->events = [];             //array of Event objects
    }

    //adds new Event object to events in order by start time
    public function addEvent($emp, $summary, $start, $end){
        $i = 0;
        $newEvent = new Event($emp, $summary, $start, $end);
        $original = $this->events;
        $newArr = [];
        foreach ($original as $event) {
            if (intval($start) <= intval($event->start)) {
                if (intval($start) == intval($event->start) && intval($end) <= intval($event->end)) {
                    $i++;
                    break;
                }
                break;
            }
            $i++;
            $newArr[] = $event;
        }

        $newArr[] = $newEvent;
        foreach(array_splice($original, $i) as $o){
            $newArr[] = $o;
        }
        $this->events = $newArr;
        echo "<br>";
        print_r($newEvent);
    }
    public function printEvents(){
        foreach($this->events as $event){
            echo "<br>".convertTo12($event->start)." | ".convertTo12($event->end)." - ".$event->employee." ---  ".$event->summary;
        }
    }
}