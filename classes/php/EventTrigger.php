<?php

abstract class EventTrigger {
    /** @var array EventListeners */
    protected $eventListeners = array();

    public function attach(EventListener $listener) {
        $this->eventListeners[] = $listener;
    }

    public function detach(EventListener $listener) {
        $key = array_search($listener, $this->eventListeners);
        unset($this->eventListeners[$key]);
    }

    protected function notify($event) {
        foreach ($this->eventListeners as $eventListener) {
            $eventListener->onEvent($event);
        }
    }
}