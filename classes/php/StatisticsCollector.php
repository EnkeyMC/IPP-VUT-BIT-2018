<?php

class StatisticsCollector implements EventListener
{
    private $comments;
    private $locs;

    public function __construct()
    {
        $this->comments = 0;
        $this->locs = 0;
    }

    public function onEvent($event)
    {
        if ($event === CodeAnalyzer::EVENT_ON_COMMENT)
            $this->comments++;
        else if ($event === CodeAnalyzer::EVENT_ON_LOC)
            $this->locs++;
    }

    public function getCommentStatistics() {
        return $this->comments;
    }

    public function getLOCStatistics() {
        return $this->locs;
    }
}