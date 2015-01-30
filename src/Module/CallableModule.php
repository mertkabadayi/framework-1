<?php

namespace Pagekit\Module;

use Pagekit\Application;

class CallableModule extends Module
{
    /**
     * @var callable
     */
    protected $callable;

    /**
     * Constructor.
     *
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritdoc}
     */
    public function load(Application $app, array $config)
    {
        return call_user_func($this->callable, $app, $config);
    }
}
