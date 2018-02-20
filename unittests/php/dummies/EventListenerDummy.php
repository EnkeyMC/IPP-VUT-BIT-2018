<?php

class EventListenerDummy implements EventListener {
    public $lastEvent;

    public function __construct()
    {
        $this->lastEvent = '';
    }

    public function onEvent($event)
    {
        $this->lastEvent = $event;
    }
}