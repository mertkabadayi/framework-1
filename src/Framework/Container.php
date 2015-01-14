<?php

namespace Pagekit\Framework;

class Container extends \Pimple
{
    /**
     * @var array
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
    public function has($id)
    {
        return isset($this[$id]);
    }

    /**
     * Gets a service.
     *
     * @param  string $id
     * @return mixed
     */
    public function get($id)
    {
        return $this[$id];
    }

    /**
     * Sets a service.
     *
     * @param  string $id
     * @param  mixed  $value
     * @return mixed
     */
    public function set($id, $value)
    {
        return $this[$id] = $value;
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

        $class = get_called_class();

        if (!$methods) {
            $methods = array_flip(get_class_methods($class));
        }

        if (isset($methods[$name])) {
            return call_user_func_array([static::$instances[$class], $name], $arguments);
        }

        return static::$instances[$class][$name];
    }
}
