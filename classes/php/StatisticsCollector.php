<?php

/**
 * Class StatisticsCollector
 *
 * Collects statistics from code analyzer
 */
class StatisticsCollector implements EventListener
{
    /** @var int number of comments */
    private $comments;
    /** @var int number of lines of code */
    private $locs;

    /**
     * StatisticsCollector constructor.
     */
    public function __construct()
    {
        $this->comments = 0;
        $this->locs = 0;
    }

    /**
     * This method is called on event triggered by CodeAnalyzer
     *
     * @param string $event
     */
    public function onEvent($event)
    {
        if ($event === CodeAnalyzer::EVENT_ON_COMMENT)
            $this->comments++;
        else if ($event === CodeAnalyzer::EVENT_ON_LOC)
            $this->locs++;
    }

    /**
     * @return int number of comments
     */
    public function getCommentStatistics() {
        return $this->comments;
    }

    /**
     * @return int number of lines of code
     */
    public function getLOCStatistics() {
        return $this->locs;
    }
}