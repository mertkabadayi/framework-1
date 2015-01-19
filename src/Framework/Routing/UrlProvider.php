<?php

namespace Pagekit\Framework\Routing;

use Pagekit\Filesystem\File;
use Pagekit\Filesystem\Path;
use Pagekit\Routing\Generator\UrlGenerator;
use Pagekit\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class UrlProvider
{
    /**
     * Generates a path relative to the executed script, e.g. "/dir/file".
     */
    const BASE_PATH = 'base';

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
        $request = $this->router->getRequest();
        $url = $request->getBasePath();

        if ($referenceType === UrlGenerator::ABSOLUTE_URL) {
            $url = $request->getSchemeAndHttpHost().$url;
        }

        return $url;
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
        return $this->router->getRequest()->headers->get('referer');
    }

    /**
     * Get the URL appending the URI to the base URI.
     *
     * @param  string $path
     * @param  mixed  $parameters
     * @param  mixed  $referenceType
     * @return string
     */
    public function get($path = '', $parameters = [], $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        if (0 === strpos($path, '@')) {
            return $this->getRoute($path, $parameters, $referenceType);
        }

        $path = $this->parseQuery($path, $parameters);

        if (filter_var($path, FILTER_VALIDATE_URL) !== false) {
            return $path;
        }

        return $this->base($referenceType).'/'.ltrim($path, '/');
    }

    /**
     * Get the URL to a named route.
     *
     * @param  string $name
     * @param  mixed  $parameters
     * @param  mixed  $referenceType
     * @return string|false
     */
    public function getRoute($name, $parameters = [], $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        try {

            $url = $this->router->generate($name, $parameters, $referenceType);

            if ($referenceType === self::BASE_PATH) {
                $url = substr($url, strlen($this->router->getRequest()->getBaseUrl()));
            }

            return $url;

        } catch (RouteNotFoundException $e) {}

        return false;
    }

    /**
     * Get the URL to a path resource.
     *
     * @param  string $path
     * @param  mixed  $parameters
     * @param  mixed  $referenceType
     * @return string
     */
    public function getStatic($path, $parameters = [], $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        return $this->parseQuery($this->file->getUrl($path, $referenceType), $parameters);
    }

    /**
     * To shortcut.
     *
     * @see get()
     */
    public function __invoke($path = '', $parameters = [], $referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        return $this->get($path, $parameters, $referenceType);
    }

    /**
     * @param  string $url
     * @param  array  $parameters
     * @return string
     */
    protected function parseQuery($url, $parameters = [])
    {
        if ($query = substr(strstr($url, '?'), 1)) {
            parse_str($query, $params);
            $url        = strstr($url, '?', true);
            $parameters = array_replace($parameters, $params);
        }

        if ($query = http_build_query($parameters, '', '&')) {
            $url .= '?'.$query;
        }

        return $url;
    }
}
