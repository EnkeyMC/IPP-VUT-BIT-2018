<?php

/**
 * Interface EventListener
 *
 * Listens for events from EventTrigger that is attached to
 */
interface EventListener {
    /**
     * This method is called on event triggered by EventTrigger
     *
     * @param $event string type of event
     */
    public function onEvent($event);
}