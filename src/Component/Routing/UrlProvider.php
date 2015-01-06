<?php

namespace Pagekit\Component\Routing;

use Pagekit\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class UrlProvider
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var RequestContext
     */
    protected $context;

    /**
     * Constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router  = $router;
        $this->context = $router->getContext();
    }

    /**
     * Get the base path for the current request.
     *
     * @param  mixed $referenceType
     * @return string
     */
    public function base($referenceType = UrlGenerator::ABSOLUTE_PATH)
    {
        $url = $this->context->getBasePath();

        if ($referenceType === UrlGenerator::ABSOLUTE_URL) {
            $url = $this->context->getSchemeAndHttpHost().$url;
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
        $url = $this->context->getBaseUrl();

        if ($referenceType === UrlGenerator::ABSOLUTE_URL) {
            $url = $this->context->getSchemeAndHttpHost().$url;
        }

        if ($qs = $this->context->getQueryString()) {
            $qs = '?'.$qs;
        }

        return $url.$this->context->getPathInfo().$qs;
    }

    /**
     * Get the URL for the previous request.
     *
     * @return string
     */
    public function previous()
    {
        if ($referer = $this->context->getReferer()) {
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

        if ($query = http_build_query($parameters, '', '&')) {
            $query = '?'.$query;
        }

        if ($referenceType !== UrlGenerator::BASE_PATH) {
            $path = $this->base($referenceType).'/'.trim($path, '/');
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
}
