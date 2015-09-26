<?php

class Logger
{
    private static $_instance;
    private $_log;
    
    /**
     * We need only one instance, which will lock the file
     * from further editing.
     */
    private function __construct()
    {
        $this->_log = fopen(__DIR__.'/venues_report.txt', 'w');
    }

    public function __destruct()
    {
        fclose($this->_log);
    }

    public function log($text)
    {
        $line = $text . PHP_EOL;
        fwrite($this->_log, $line);
    }

    /**
     * An instance of this class is lazy created and returned.
     * No further instances are created if there is already one available.
     * This method is the standard way to create a Singleton.
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}