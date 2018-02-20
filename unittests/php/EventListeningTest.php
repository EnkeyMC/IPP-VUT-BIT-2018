<?php

use PHPUnit\Framework\TestCase;


class EventListeningTest extends TestCase {
    public function testSimpleAttachAndNotify() {
        $listener = new EventListenerDummy();
        $trigger = new EventTriggerDummy();
        $trigger->attach($listener);
        $trigger->notifyListeners('event');
        $this->assertSame('event', $listener->lastEvent);
    }

    const LISTERNERS_COUNT = 5;
    private $listeners = [];
    /** @var  EventTriggerDummy */
    private $trigger;

    public function setUp()
    {
        $this->trigger = new EventTriggerDummy();
        for ($i = 0; $i < self::LISTERNERS_COUNT; $i++) {
            $this->listeners[] = new EventListenerDummy();
            $this->trigger->attach($this->listeners[$i]);
        }
    }

    public function testMultipleListeners() {
        $msg = 'event';

        $this->trigger->notifyListeners($msg);

        foreach($this->listeners as $listener) {
            $this->assertSame($msg, $listener->lastEvent);
        }
    }

    public function testDetach() {
        $msg = 'event';

        $this->trigger->detach($this->listeners[0]);
        $this->trigger->notifyListeners($msg);

        $this->assertNotSame($msg, $this->listeners[0]);
        for ($i = 1; $i < self::LISTERNERS_COUNT; $i++) {
            $this->assertSame($msg, $this->listeners[$i]->lastEvent);
        }
    }
}