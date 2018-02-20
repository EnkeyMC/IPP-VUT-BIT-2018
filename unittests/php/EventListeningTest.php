<?php

use PHPUnit\Framework\TestCase;

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

class EventTriggerDummy extends EventTrigger {
    public function notifyListeners($event) {
        $this->notify($event);
    }
}

class EventListeningTest extends TestCase {
    public function testSimpleAttachAndNotify() {
        $listener = new EventListenerDummy();
        $trigger = new EventTriggerDummy();
        $trigger->attach($listener);
    }
}