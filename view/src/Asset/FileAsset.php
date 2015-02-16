<?php

namespace Pagekit\View\Asset;

class FileAsset extends Asset
{
	/**
	 * @var string
	 */
	protected $content;

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        if ($this->content === null && $this->asset) {
            $this->content = file_get_contents($this->asset);
        }

        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
