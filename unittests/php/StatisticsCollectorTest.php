<?php

use PHPUnit\Framework\TestCase;

class StatisticsCollectorTest extends TestCase
{
    /** @var  EventTriggerDummy */
    private $trigger;
    /** @var  StatisticsCollector */
    private $stats;

    public function setUp()
    {
        $this->trigger = new EventTriggerDummy();
        $this->stats = new StatisticsCollector();
        $this->trigger->attach($this->stats);
    }

    public function testInit() {
        $this->assertSame(0, $this->stats->getCommentStatistics());
        $this->assertSame(0, $this->stats->getLOCStatistics());
    }

    public function testSingleComment() {
        $this->trigger->notifyListeners(CodeAnalyzer::EVENT_ON_COMMENT);
        $this->assertSame(1, $this->stats->getCommentStatistics());
    }

    public function testSignleLOC() {
        $this->trigger->notifyListeners(CodeAnalyzer::EVENT_ON_LOC);
        $this->assertSame(1, $this->stats->getLOCStatistics());
    }

    public function testMultipleComments() {
        $count = 5;
        foreach (range(1, $count) as $i) {
            $this->trigger->notifyListeners(CodeAnalyzer::EVENT_ON_COMMENT);
        }

        $this->assertSame($count, $this->stats->getCommentStatistics());
    }

    public function testMultipleLOCs() {
        $count = 5;
        foreach (range(1, $count) as $i) {
            $this->trigger->notifyListeners(CodeAnalyzer::EVENT_ON_LOC);
        }

        $this->assertSame($count, $this->stats->getLOCStatistics());
    }

    public function testMixStats() {
        $count = 5;
        foreach (range(1, $count*2) as $i) {
            if ($i % 2 == 0)
                $this->trigger->notifyListeners(CodeAnalyzer::EVENT_ON_LOC);
            else
                $this->trigger->notifyListeners(CodeAnalyzer::EVENT_ON_COMMENT);
        }

        $this->assertSame($count, $this->stats->getCommentStatistics());
        $this->assertSame($count, $this->stats->getLOCStatistics());
    }

    public function testInvalidEvent() {
        $this->trigger->notifyListeners('invalid');

        $this->assertSame(0, $this->stats->getCommentStatistics());
        $this->assertSame(0, $this->stats->getLOCStatistics());
    }
}
