<?php

namespace Pagekit\Component\Feed;

trait ElementsTrait
{
    /**
     * @var array[]
     */
    protected $elements;

    /**
     * {@inheritdoc}
     */
    public function setElement($name, $value, $attributes = null)
    {
        unset($this->elements[$name]);
        return $this->addElement($name, $value, $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function addElement($name, $value, $attributes = null)
    {
        $this->elements[$name][] = [$name, $value, $attributes];
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addElements(array $elements)
    {
        foreach ($elements as $name => $content) {
            $this->addElement($name, $content);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getElements()
    {
        return call_user_func_array('array_merge', $this->elements);
    }
}
