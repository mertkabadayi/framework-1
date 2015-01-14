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
        $instance = static::getInstance();

        return $instance[$id];
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
        $instance = static::getInstance();

        return $instance[$id] = $value;
    }

    /**
     * Gets a container instance.
     *
     * @return Container
     */
    public static function getInstance()
    {
        return static::$instances[get_called_class()];
    }

    /**
     * Magic method to access the container in a static context.
     *
     * @param  string $id
     * @param  array  $args
     * @return mixed
     */
    public static function __callStatic($id, $args)
    {
        $instance = static::getInstance();

        return $args ? call_user_func_array($instance[$id], $args) : $instance[$id];
    }
}
