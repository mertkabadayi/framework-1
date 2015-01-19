<?php

namespace Pagekit\Filesystem;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;

class FilesystemServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['file'] = function () {
            return new Filesystem;
        };
    }

    public function boot(Application $app)
    {
        StreamWrapper::setFilesystem($app['file']);
    }
}
