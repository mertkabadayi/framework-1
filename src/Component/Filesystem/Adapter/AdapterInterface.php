<?php

namespace Pagekit\Component\Filesystem\Adapter;

interface AdapterInterface
{
    /**
     * Get stream wrapper classname.
     *
     * @return string
     */
    public function getStreamWrapper();

    /**
     * Parses file path info.
     *
     * @param  array $info
     * @return array
     */
    public function parsePathInfo(array $info);
}
