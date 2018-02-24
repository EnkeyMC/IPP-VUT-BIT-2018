<?php

/**
 * Class EventTrigger
 *
 * Notifies attached EventListeners
 */
abstract class EventTrigger {
    /** @var array EventListeners */
    protected $eventListeners = array();

    /**
     * Attach event listeners
     *
     * @param EventListener $listener listener to attach
     */
    public function attach(EventListener $listener) {
        $this->eventListeners[] = $listener;
    }

    /**
     * Detach event listeners
     *
     * @param EventListener $listener listener to detach
     */
    public function detach(EventListener $listener) {
        $key = array_search($listener, $this->eventListeners);
        unset($this->eventListeners[$key]);
    }

    /**
     * Notify attached event listeners with given event
     *
     * @param $event string event type
     */
    protected function notify($event) {
        foreach ($this->eventListeners as $eventListener) {
            $eventListener->onEvent($event);
        }
    }
}