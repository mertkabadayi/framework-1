<?php

namespace Pagekit\Component\Feed;

class FeedFactory
{
    /**
     * @var string[]
     */
    protected $feeds = [
        Feed::ATOM => 'Pagekit\Component\Feed\Feed\Atom',
        Feed::RSS1 => 'Pagekit\Component\Feed\Feed\RSS1',
        Feed::RSS2 => 'Pagekit\Component\Feed\Feed\RSS2'
    ];

    /**
     * Creates a feed.
     *
     * @param  string $type
     * @return FeedInterface
     */
    public function create($type = null)
    {
        $class = isset($this->feeds[$type]) ? $this->feeds[$type] : $this->feeds[Feed::RSS2];
        return new $class;
    }

    /**
     * Registers a new feed type.
     *
     * @param string $type
     * @param string $class
     */
    public function register($type, $class)
    {
        if (!is_string($class) || !is_subclass_of($class, 'Pagekit\Component\Feed\FeedInterface')) {
            throw new \InvalidArgumentException(sprintf('Given type class "%s" is not of type Pagekit\Component\Feed\FeedInterface', (string) $class));
        }

        $this->feeds[$type] = $class;
    }
}
