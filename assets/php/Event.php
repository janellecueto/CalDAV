<?php


class Event
{
    public function __construct($emp, $summary, $start, $end){
        $this->employee = $emp;
        $this->summary = $summary;
        $this->start = $start;
        $this->end = $end;
    }

    public function getStartTime(){
        return convertTo12($this->start);
    }

    public function getEndTime(){
        return convertTo12($this->end);
    }

}

function convertTo12($time24){
    //takes time string in 24 hour format and returns string as AM PM
    $hrs = substr($time24, 0, 2);
    $mins = substr($time24, 2, 2);
//    $seconds = substr($time24, 4);
    $ampm = "AM";

    if (intval($hrs) >= 12){
        $ampm = "PM";
        if(intval($hrs) > 12){
            $hrs -= 12;
        }
    }
    return "$hrs:$mins $ampm";
}