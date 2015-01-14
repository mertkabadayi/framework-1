<?php

namespace Pagekit;

class Container extends \Pimple
{
    /**
     * @var Container[]
     */
    protected static $instances = [];

    /**
     * Constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        static::$instances[get_class($this)] = $this;
    }

    /**
     * Checks if a service exists.
     *
     * @param  string $id
     * @return bool
     */
    public static function has($id)
    {
        $instance = static::getInstance();

        return isset($instance[$id]);
    }

    /**
     * Gets a service.
     *
     * @param  string $id
     * @return mixed
     */
    public static function get($id)
    {
        return static::getInstance()[$id];
    }

    /**
     * Sets a service.
     *
     * @param  string $id
     * @param  mixed  $value
     * @return mixed
     */
    public static function set($id, $value)
    {
        return static::getInstance()[$id] = $value;
    }

    /**
     * @return Container
     */
    public static function getInstance()
    {
        return static::$instances[get_called_class()];
    }

    /**
     * Magic method to access the container in a static context.
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        static $methods;

        $instance = static::getInstance();

        if (!$methods) {
            $methods = array_flip(get_class_methods($instance));
        }

        if (isset($methods[$name])) {
            return call_user_func_array([$instance, $name], $arguments);
        }

        return $instance[$name];
    }
}
