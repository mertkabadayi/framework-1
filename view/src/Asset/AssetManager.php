<?php

namespace Pagekit\View\Asset;

class AssetManager implements \IteratorAggregate
{
    /**
     * @var AssetFactory
     */
    protected $factory;

    /**
     * @var AssetCollection
     */
    protected $registered;

    /**
     * @var array
     */
    protected $queued = [];

    /**
     * Constructor.
     *
     * @param AssetFactory $factory
     */
    public function __construct(AssetFactory $factory = null)
    {
        $this->factory    = $factory ?: new AssetFactory;
        $this->registered = new AssetCollection;
    }

    /**
     * Queue shortcut.
     *
     * @see queue()
     */
    public function __invoke($name, $asset = null, $dependencies = [], $options = [])
    {
        return $this->queue($name, $asset, $dependencies, $options);
    }

    /**
     * Registers an asset.
     *
     * @param  string $name
     * @param  mixed  $asset
     * @param  array  $dependencies
     * @param  array  $options
     * @return self
     */
    public function register($name, $asset, $dependencies = [], $options = [])
    {
        $this->registered->add($this->factory->create($name, $asset, $dependencies, $options));

        return $this;
    }

    /**
     * Unregisters an asset.
     *
     * @param  string $name
     * @return self
     */
    public function unregister($name)
    {
        $this->registered->remove($name);
        $this->dequeue($name);

        return $this;
    }

    /**
     * Queues a previously registered asset or a new asset.
     *
     * @param  string $name
     * @param  mixed  $asset
     * @param  array  $dependencies
     * @param  array  $options
     * @return self
     */
    public function queue($name, $asset = null, $dependencies = [], $options = [])
    {
        if (!$instance = $this->registered->get($name)) {
            $this->registered->add($instance = $this->factory->create($name, $asset, $dependencies, $options));
        }

        $this->queued[$instance->getName()] = true;

        return $this;
    }

    /**
     * Dequeues an asset.
     *
     * @param  string $name
     * @return self
     */
    public function dequeue($name)
    {
        unset($this->queued[$name]);

        return $this;
    }

    /**
     * Gets an registered asset.
     *
     * @param  $name
     * @return AssetInterface
     */
    public function get($name)
    {
        return $this->registered->get($name);
    }

    /**
     * IteratorAggregate interface implementation.
     */
    public function getIterator()
    {
        $assets = [];

        foreach (array_keys($this->queued) as $name) {
            $this->resolveDependencies($this->registered->get($name), $assets);
        }

        return new \ArrayIterator($assets);
    }

    /**
     * Resolves asset dependencies.
     *
     * @param  AssetInterface $asset
     * @param  array          $resolved
     * @param  array          $unresolved
     * @return array
     * @throws \RuntimeException
     */
    public function resolveDependencies($asset, &$resolved = [], &$unresolved = [])
    {
        $unresolved[$asset->getName()] = $asset;

        if (isset($asset['dependencies'])) {
            foreach ($asset['dependencies'] as $dependency) {
                if (!isset($resolved[$dependency])) {

                    if (isset($unresolved[$dependency])) {
                        throw new \RuntimeException(sprintf('Circular asset dependency "%s > %s" detected.', $asset->getName(), $dependency));
                    }

                    if ($d = $this->registered->get($dependency)) {
                        $this->resolveDependencies($d, $resolved, $unresolved);
                    }
                }
            }
        }

        $resolved[$asset->getName()] = $asset;
        unset($unresolved[$asset->getName()]);

        return $resolved;
    }
}
