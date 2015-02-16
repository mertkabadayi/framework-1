<?php

namespace Pagekit\View\Asset;

class AssetFactory
{
    /**
     * @var string[]
     */
    protected $assets = [
        'file'   => 'Pagekit\View\Asset\FileAsset',
        'string' => 'Pagekit\View\Asset\StringAsset'
    ];

    /**
     * @var string
     */
    protected $version;

    /**
     * Constructor.
     *
     * @param string $version
     */
    public function __construct($version = null)
    {
        $this->version = $version;
    }

    /**
     * Creates an asset instance.
     *
     * @param  string $name
     * @param  mixed  $asset
     * @param  array  $dependencies
     * @param  array  $options
     * @throws \InvalidArgumentException
     * @return AssetInterface
     */
    public function create($name, $asset, $dependencies = [], $options = [])
    {
        if (is_string($options)) {
            $options = ['type' => $options];
        }

        if (!isset($options['type'])) {
            $options['type'] = 'file';
        }

        if (!isset($options['version'])) {
            $options['version'] = $this->version;
        }

        if ($dependencies) {
            $options = array_merge($options, ['dependencies' => (array) $dependencies]);
        }

        if (isset($this->assets[$options['type']])) {

            $class = $this->assets[$options['type']];

            return new $class($name, $asset, $options);
        }

        throw new \InvalidArgumentException('Unable to determine asset type.');
    }

    /**
     * Registers a new asset type.
     *
     * @param  string $type
     * @param  string $class
     * @return self
     */
    public function register($type, $class)
    {
        if (!is_string($class) || !is_subclass_of($class, 'Pagekit\View\Asset\AssetInterface')) {
            throw new \InvalidArgumentException(sprintf('Given type class "%s" is not of type Pagekit\View\Asset\AssetInterface', (string) $class));
        }

        $this->assets[$type] = $class;

        return $this;
    }
}
