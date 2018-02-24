<?php

/**
 * Class App
 *
 * Abstract base singleton class for applications
 */
abstract class App
{
    /** @var array App configuration */
    protected $configuration;

    /** @var App singleton instance */
    protected static $instance;

    /**
     * Get singleton instance
     * @return App|static
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * App constructor.
     *
     * Calls loadConfiguration
     */
    protected function __construct() {
        $this->loadConfiguration();
    }

    /**
     * Get app configuration option
     *
     * @param $key string option
     * @return bool|string
     */
    public function getConfig($key) {
        if (isset($this->configuration[$key])) {
            if ($this->configuration[$key] === false) {
                return true;
            }

            return $this->configuration[$key];
        }

        return false;
    }

    /**
     * Run application
     *
     * @return int Exit code
     */
    public abstract function run();

    /**
     * Load application configuration
     */
    protected abstract function loadConfiguration();
}