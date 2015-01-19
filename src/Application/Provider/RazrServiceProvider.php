<?php

namespace Pagekit\Application\Provider;

use Pagekit\Application;
use Pagekit\Application\Templating\Helper\GravatarHelper;
use Pagekit\Application\Templating\Helper\TokenHelper;
use Pagekit\Application\Templating\Razr\Directive\SectionDirective;
use Pagekit\Application\Templating\Razr\Directive\TransDirective;
use Pagekit\Application\Templating\RazrEngine;
use Pagekit\ServiceProviderInterface;
use Razr\Directive\FunctionDirective;
use Razr\Loader\FilesystemLoader;

class RazrServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['tmpl.razr'] = function($app) {

            $parser = $app['tmpl.parser'];
            $parser->addEngine('razr', '.razr');

            $engine = new RazrEngine($parser, new FilesystemLoader, $app['path'].'/app/cache/templates');
            $engine->addDirective(new FunctionDirective('gravatar', [new GravatarHelper, 'get']));
            $engine->addGlobal('app', $app);

            $engine->addDirective(new FunctionDirective('url', [$app['url'], 'get']));
            $engine->addFunction('url', [$app['url'], 'get']);

            $engine->addDirective(new FunctionDirective('static_url', [$app['url'], 'getStatic']));
            $engine->addFunction('static_url', [$app['url'], 'getStatic']);

            if (isset($app['styles'])) {
                $engine->addDirective(new FunctionDirective('style', function($name, $asset = null, $dependencies = [], $options = []) use ($app) {
                    $app['styles']->queue($name, $asset, $dependencies, $options);
                }));
            }

            if (isset($app['scripts'])) {
                $engine->addDirective(new FunctionDirective('script', function($name, $asset = null, $dependencies = [], $options = []) use ($app) {
                    $app['scripts']->queue($name, $asset, $dependencies, $options);
                }));
            }

            if (isset($app['sections'])) {
                $engine->addDirective(new SectionDirective);
                $engine->addFunction('hasSection', [$app['sections'], 'has']);
            }

            if (isset($app['csrf'])) {
                $engine->addDirective(new FunctionDirective('token', [new TokenHelper($app['csrf']), 'generate']));
            }

            if (isset($app['markdown'])) {
                $engine->addDirective(new FunctionDirective('markdown', [$app['markdown'], 'parse']));
            }

            if (isset($app['translator'])) {
                $engine->addDirective(new TransDirective);
            }

            return $engine;
        };
    }

    public function boot(Application $app)
    {
        $app['tmpl']->addEngine($app['tmpl.razr']);
    }
}
