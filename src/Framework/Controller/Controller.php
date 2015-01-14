<?php

namespace Pagekit\Framework\Controller;

use Pagekit\Framework\Application as App;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Controller
{
    /**
     * Returns a redirect response.
     *
     * @param  string  $url
     * @param  array   $parameters
     * @param  int     $status
     * @param  array   $headers
     * @return RedirectResponse
     */
    public function redirect($url = '', $parameters = [], $status = 302, $headers = [])
    {
        return new RedirectResponse(App::url()->to($url, $parameters), $status, $headers);
    }
}
