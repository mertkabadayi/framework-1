<?php

namespace Pagekit\Application\Provider;

use Pagekit\Application;
use Pagekit\Application\Templating\Helper\GravatarHelper;
use Pagekit\Application\Templating\Helper\MarkdownHelper;
use Pagekit\Application\Templating\Helper\ScriptHelper;
use Pagekit\Application\Templating\Helper\SectionHelper;
use Pagekit\Application\Templating\Helper\StyleHelper;
use Pagekit\Application\Templating\Helper\TokenHelper;
use Pagekit\Application\Templating\Section\DelayedRenderer;
use Pagekit\Application\Templating\TemplateNameParser;
use Pagekit\ServiceProviderInterface;
use Symfony\Component\Templating\DelegatingEngine;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;

class TemplatingServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['tmpl'] = function() {
            return new DelegatingEngine;
        };

        $app['tmpl.parser'] = function($app) {

            $parser = new TemplateNameParser($app['events']);
            $parser->addEngine('php', '.php');

            return $parser;
        };

        $app['tmpl.php'] = function($app) {

            $helpers = [new SlotsHelper, new GravatarHelper];

            if (isset($app['styles'])) {
                $helpers[] = new StyleHelper($app['styles']);
            }

            if (isset($app['scripts'])) {
                $helpers[] = new ScriptHelper($app['scripts']);
            }

            if (isset($app['sections'])) {
                $helpers[] = new SectionHelper($app['sections']);
            }

            if (isset($app['csrf'])) {
                $helpers[] = new TokenHelper($app['csrf']);
            }

            if (isset($app['markdown'])) {
                $helpers[] = new MarkdownHelper($app['markdown']);
            }

            $engine = new PhpEngine($app['tmpl.parser'], new FilesystemLoader([]));
            $engine->addHelpers($helpers);

            return $engine;
        };
    }

    public function boot(Application $app)
    {
        $app['tmpl']->addEngine($app['tmpl.php']);
        $app['sections']->addRenderer('delayed', new DelayedRenderer($app['events']));
    }
}
