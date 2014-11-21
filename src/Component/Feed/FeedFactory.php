<?php

namespace Pagekit\Component\Feed;

use Pagekit\Component\Feed\Feed\Atom;
use Pagekit\Component\Feed\Feed\RSS1;
use Pagekit\Component\Feed\Feed\RSS2;

class FeedFactory
{
    /**
     * Creates a feed.
     *
     * @param string $type
     * @return FeedInterface
     */
    public function create($type = null)
    {
        switch ($type) {
            case Feed::ATOM:
                return new Atom;
            case Feed::RSS1:
                return new RSS1;
            default:
                return new RSS2;
        }
    }
}
