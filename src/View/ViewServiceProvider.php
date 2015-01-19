<?php

namespace Pagekit\View;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;
use Pagekit\View\Event\ViewListener;
use Pagekit\View\Section\SectionManager;

class ViewServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['view'] = function($app) {

            $view = new View($app['sections']);
            $view->set('app', $app);

            return $view;
        };

        $app['sections'] = function() {
            return new SectionManager;
        };
    }

    public function boot(Application $app)
    {
        $app->subscribe(new ViewListener($view = $app['view']));

        $app['sections']->prepend('head', function() use ($view) {

            $result = [];

            if ($title = $view->get('head.title')) {
                $result[] = sprintf('        <title>%s</title>', $title);
            }

            if ($links = $view->get('head.link', [])) {
                foreach($links as $rel => $attributes) {

                    if (!$attributes) {
                        continue;
                    }

                    if (!isset($attributes['rel'])) {
                        $attributes['rel'] = $rel;
                    }

                    $html = '<link';
                    foreach ($attributes as $name => $value) {
                        $html .= sprintf(' %s="%s"', $name, htmlspecialchars($value));
                    }
                    $result[] = $html.'>';
                }
            }

            return implode(PHP_EOL, $result);
        });
    }
}
