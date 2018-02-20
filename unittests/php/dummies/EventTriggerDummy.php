<?php

class EventTriggerDummy extends EventTrigger {
    public function notifyListeners($event) {
        $this->notify($event);
    }
}