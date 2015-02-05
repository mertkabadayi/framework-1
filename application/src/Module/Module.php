<?php

namespace Pagekit\Module;

use Pagekit\Application as App;

class Module
{
    /**
     * Constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $key => $value) {
            if ($value instanceof \Closure) {
                $this->$key = $value->bindTo($this);
            } else {
                $this->$key = $value;
            }
        }
    }

    /**
     * Main bootstrap method.
     *
     * @param  App   $app
     * @param  array $config
     * @return mixed
     */
    public function main(App $app, array $config)
    {
        return call_user_func($this->main, $app, $config);
    }
}
