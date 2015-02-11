<?php

use Pagekit\View\Asset\AssetInterface;
use Pagekit\View\Asset\AssetManager;
use Pagekit\View\Asset\FileAsset;
use Pagekit\View\Event\ViewListener;
use Pagekit\View\Section\SectionManager;
use Pagekit\View\View;

return [

    'name' => 'framework/view',

    'main' => function ($app) {

        $app['view'] = function($app) {

            $view = new View($app['sections']);
            $view->set('app', $app);

            return $view;
        };

        $app['sections'] = function() {
            return new SectionManager;
        };

        $app['styles'] = function($app) {
            return new AssetManager($app['version']);
        };

        $app['scripts'] = function($app) {
            return new AssetManager($app['version']);
        };

        $app->on('kernel.boot', function() use ($app) {

            $app->subscribe(new ViewListener($app['view']));

            $app['sections']->prepend('head', function() use ($app) {

                $result = [];

                if ($title = $app['view']->get('head.title')) {
                    $result[] = sprintf('        <title>%s</title>', $title);
                }

                if ($links = $app['view']->get('head.link', [])) {
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

            $app['sections']->append('head', function() use ($app) {

                $result = [];

                $getDataAttibutes = function (AssetInterface $asset) {
                    $attributes = '';

                    foreach ($asset->getOptions() as $name => $value) {
                        if ('data-' == substr($name, 0, 5)) {
                            $attributes .= sprintf(' %s="%s"', $name, htmlspecialchars($value));
                        }
                    }

                    return $attributes;
                };

                foreach ($app['styles'] as $style) {

                    $attributes = $getDataAttibutes($style);

                    if ($style instanceof FileAsset) {
                        $result[] = sprintf('        <link href="%s" rel="stylesheet"%s>', $app['url']->getStatic($style), $attributes);
                    } else {
                        $result[] = sprintf('        <style%s>%s</style>', $attributes, $style);
                    }
                }

                foreach ($app['scripts'] as $script) {

                    $attributes = $getDataAttibutes($script);

                    if ($script instanceof FileAsset) {
                        $result[] = sprintf('        <script src="%s"%s></script>', $app['url']->getStatic($script), $attributes);
                    } else {
                        $result[] = sprintf('        <script%s>%s</script>', $attributes, $script);
                    }
                }

                return implode(PHP_EOL, $result);

            });
        });

        $app->on('kernel.controller', function() use ($app) {
            foreach ($app['module'] as $module) {

                if (!isset($module->renderer)) {
                    continue;
                }

                foreach ($module->renderer as $name => $template) {
                    $app['sections']->addRenderer($name, function ($name, $value, $options = []) use ($app, $template) {
                        return $app['view']->render($template, compact('name', 'value', 'options'));
                    });
                }
            }
        });
    }

];
