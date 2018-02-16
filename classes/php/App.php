<?php

abstract class App
{
    protected $configuration;

    protected static $instance;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    private function __construct() {
        $this->loadConfiguration();
    }

    public function getConfig($key) {
        if (isset($this->configuration[$key])) {
            if ($this->configuration[$key] === false) {
                return true;
            }

            return $this->configuration[$key];
        }

        return false;
    }

    public abstract function run();
    protected abstract function loadConfiguration();
}