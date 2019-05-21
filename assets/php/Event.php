<?php


class Event
{
    public function __construct($emp, $summary, $start, $end){
        $this->employee = $emp;
        $this->summary = $summary;
        $this->start = $start;
        $this->end = $end;
    }

}