<?php

/**
 * Class Singleton
 */
abstract class Singleton
{
    private static $instances = [];

    protected function __construct()
    {
    }

    /**
     * @param null $className
     * @return mixed
     * @throws Exception
     */
    public static function getInstance($className = null)
    {
        $className = $className ?? get_called_class();
        if (!class_exists($className) || $className == 'Controller') {
            throw new Exception('Class ' . $className . ' does not exist!');
        }

        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className();
        }
        return self::$instances[$className];
    }
}