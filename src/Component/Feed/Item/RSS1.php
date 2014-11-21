<?php

namespace Pagekit\Component\Feed\Item;

use Pagekit\Component\Feed\Item;
use Pagekit\Component\Feed\Feed;

class RSS1 extends Item
{
    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setElement('description', $description);
    }

    /**
     * {@inheritdoc}
     */
    public function setDate(\DateTime $date)
    {
        return $this->setElement('dc:date', date('Y-m-d', $date->getTimestamp()));
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthor($author, $email = null, $uri = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLink($link)
    {
        return $this->setElement('link', $link);
    }

    /**
     * {@inheritdoc}
     */
    public function addEnclosure($url, $length, $type, $multiple = true)
    {
        return $this;
    }
}
