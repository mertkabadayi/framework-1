<?php

namespace Pagekit;

/**
 * Container implementation based on Pimple, http://pimple.sensiolabs.org
 *
 * @copyright (c) Fabien Potencier <fabien@symfony.com>
 */
class Container implements \ArrayAccess
{
    protected $values = [];
    protected $factories;
    protected $protected;
    protected $frozen = [];
    protected $raw = [];
    protected $keys = [];

    /**
     * Constructor.
     *
     * @param array $values
     */
    protected function __construct(array $values = [])
    {
        $this->factories = new \SplObjectStorage();
        $this->protected = new \SplObjectStorage();

        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    /**
     * Gets a container instance.
     *
     * @return Container
     */
    public static function getInstance(array $values = [])
    {
        static $instance;

        if (!$instance) {
            $instance = new static($values);
        }

        return $instance;
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
     * @param string $id
     * @param mixed  $value
     */
    public static function set($id, $value)
    {
        static::getInstance()->offsetSet($id, $value);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id
     */
    public static function remove($id)
    {
        static::getInstance()->offsetUnset($id);
    }

    /**
     * Sets a callable as a factory service.
     *
     * @param string   $id
     * @param callable $callable
     */
    public static function factory($id, $callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException('Service definition is not a Closure or invokable object.');
        }

        $instance = static::getInstance();
        $instance->offsetSet($id, $callable);
        $instance->factories->attach($callable);
    }

    /**
     * Sets a protected callable from being interpreted as a service.
     *
     * @param string   $id
     * @param callable $callable
     */
    public static function protect($id, $callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException('Callable is not a Closure or invokable object.');
        }

        $instance = static::getInstance();
        $instance->offsetSet($id, $callable);
        $instance->factories->attach($callable);
    }

    /**
     * Extends an object definition.
     *
     * @param string   $id
     * @param callable $callable
     */
    public static function extend($id, $callable)
    {
        $instance = static::getInstance();

        if (!isset($instance->keys[$id])) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        if (!is_object($instance->values[$id]) || !method_exists($instance->values[$id], '__invoke')) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" does not contain an object definition.', $id));
        }

        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException('Extension service definition is not a Closure or invokable object.');
        }

        $factory  = $instance->values[$id];
        $extended = function ($c) use ($callable, $factory) {
            return $callable($factory($c), $c);
        };

        if (isset($instance->factories[$factory])) {
            $instance->factories->detach($factory);
            $instance->factories->attach($extended);
        }

        return $instance[$id] = $extended;
    }

    /**
     * Returns all defined value names.
     *
     * @return array
     */
    public static function keys()
    {
        $instance = static::getInstance();

        return array_keys($instance->values);
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

    /**
     * Checks if a parameter or an object is set.
     *
     * @param  string $id
     * @return bool
     */
    public function offsetExists($id)
    {
        return array_key_exists($id, $this->values);
    }

    /**
     * Gets a parameter or an object.
     *
     * @param  string $id
     * @return mixed
     */
    public function offsetGet($id)
    {
        if (!isset($this->keys[$id])) {
            throw new \InvalidArgumentException(sprintf('Identifier "%s" is not defined.', $id));
        }

        if (
            isset($this->raw[$id])
            || !is_object($this->values[$id])
            || isset($this->protected[$this->values[$id]])
            || !method_exists($this->values[$id], '__invoke')
        ) {
            return $this->values[$id];
        }

        if (isset($this->factories[$this->values[$id]])) {
            return $this->values[$id]($this);
        }

        $this->frozen[$id] = true;
        $this->raw[$id] = $this->values[$id];

        return $this->values[$id] = $this->values[$id]($this);
    }

    /**
     * Sets a parameter or an object.
     *
     * @param string $id
     * @param mixed  $value
     */
    public function offsetSet($id, $value)
    {
        if (isset($this->frozen[$id])) {
            throw new \RuntimeException(sprintf('Cannot override frozen service "%s".', $id));
        }

        $this->values[$id] = $value;
        $this->keys[$id] = true;
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id
     */
    public function offsetUnset($id)
    {
        if (isset($this->keys[$id])) {
            if (is_object($this->values[$id])) {
                unset($this->factories[$this->values[$id]], $this->protected[$this->values[$id]]);
            }

            unset($this->values[$id], $this->frozen[$id], $this->raw[$id], $this->keys[$id]);
        }
    }
}
