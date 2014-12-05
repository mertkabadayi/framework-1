<?php

namespace Pagekit\Component\Filesystem\Adapter;

interface AdapterInterface
{
    /**
     * Parses file path info.
     *
     * @param  array $info
     * @return array
     */
    public function parsePathInfo(array $info);
}
