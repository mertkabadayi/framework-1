<?php

namespace Pagekit\Markdown;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;

/**
 * @copyright Copyright (c) Pagekit, http://pagekit.com
 */
class MarkdownServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['markdown'] = function() {
            return new Markdown;
        };
    }

    public function boot(Application $app)
    {
    }
}
