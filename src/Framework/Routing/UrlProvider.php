<?php

namespace Pagekit\Framework\Routing;

use Pagekit\Filesystem\File;
use Pagekit\Routing\Generator\UrlGenerator;
use Pagekit\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class UrlProvider
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var File
     */
    protected $file;

    /**
     * Constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router, File $file)
    {
        $this->router = $router;
        $this->file   = $file;
    }

    /**
     * Get the base path for the current request.
     *
     * @param  mixed $referenceType
     * @return string
     */
    public function base($referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        return $this->file->getUrl('', $referenceType);
    }

    /**
     * Get the URL for the current request.
     *
     * @param  mixed $referenceType
     * @return string
     */
    public function current($referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        $request = $this->router->getRequest();

        $url = $request->getBaseUrl();

        if ($referenceType === UrlGenerator::ABSOLUTE_URL) {
            $url = $request->getSchemeAndHttpHost().$url;
        }

        if ($qs = $request->getQueryString()) {
            $qs = '?'.$qs;
        }

        return $url.$request->getPathInfo().$qs;
    }

    /**
     * Get the URL for the previous request.
     *
     * @return string
     */
    public function previous()
    {
        if ($referer = $this->router->getRequest()->headers->get('referer')) {
            return $this->to($referer);
        }

        return '';
    }

    /**
     * Get the URL to a path resource.
     *
     * @param  string $path
     * @param  mixed  $parameters
     * @param  mixed  $referenceType
     * @return string
     */
    public function to($path = '', $parameters = [], $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        if (0 === strpos($path, '@')) {
            return $this->route($path, $parameters, $referenceType);
        }

        if (false === $path = $this->file->getUrl($url = $path, $referenceType)) {
            return $url;
        }

        if ($query = http_build_query($parameters, '', '&')) {
            $query = '?'.$query;
        }

        if ($referenceType === UrlGenerator::BASE_PATH) {
            $path = substr($path, strlen($this->base($referenceType)));
        }

        return $path.$query;
    }

    /**
     * Get the URL to a named route.
     *
     * @param  string $name
     * @param  mixed  $parameters
     * @param  mixed  $referenceType
     * @return string|false
     */
    public function route($name = '', $parameters = [], $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        try {

            return $this->router->generate($name, $parameters, $referenceType);

        } catch (RouteNotFoundException $e) {}

        return false;
    }

    /**
     * To shortcut.
     *
     * @see to()
     */
    public function __invoke($path = '', $parameters = [], $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        return $this->to($path, $parameters, $referenceType);
    }
}
