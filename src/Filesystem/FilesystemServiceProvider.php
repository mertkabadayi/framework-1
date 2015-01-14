<?php

namespace Pagekit\Filesystem;

use Pagekit\Application;
use Pagekit\Framework\ServiceProviderInterface;

class FilesystemServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['file'] = function () {
            return new File;
        };
    }

    public function boot(Application $app)
    {
        StreamWrapper::setFile($app['file']);
    }
}
