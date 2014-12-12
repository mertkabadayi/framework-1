<?php

namespace Pagekit\Component\Routing\Controller;

use Symfony\Component\Routing\RouteCollection;

interface ControllerCollectionInterface
{
    /**
     * Gets the route collection.
     *
     * @return RouteCollection
     */
    public function flush();

    /**
     * Returns an array of resources of this collection.
     *
     * @return array
     */
    public function getResources();
}
