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
     * Checks if a parameter or an object is set.
     *
     * @param  string $id
     * @return bool
     */
    public static function has($id)
    {
        return static::getInstance()->offsetExists($id);
    }

    /**
     * Gets a parameter or an object.
     *
     * @param  string $id
     * @return mixed
     */
    public static function get($id)
    {
        return static::getInstance()->offsetGet($id);
    }

    /**
     * Sets a parameter or an object.
     *
     * @param  string $id
     * @param  mixed  $value
     */
    public static function set($id, $value)
    {
        static::getInstance()->offsetSet($id, $value);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param  string $id
     */
    public static function remove($id)
    {
        static::getInstance()->offsetUnset($id);
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
