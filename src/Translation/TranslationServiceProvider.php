<?php

namespace Pagekit\Translation;

use Pagekit\Application;
use Pagekit\Application\ServiceProviderInterface;
use Pagekit\Translation\Loader\MoFileLoader;
use Pagekit\Translation\Loader\PhpFileLoader;
use Pagekit\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

class TranslationServiceProvider implements ServiceProviderInterface
{
    /**
     * @var TranslatorInterface
     */
    public static $translator;

    public function register(Application $app)
    {
        $app['translator'] = function($app) {

            $translator = new Translator($app['config']['app.locale']);
            $translator->addLoader('php', new PhpFileLoader);
            $translator->addLoader('mo', new MoFileLoader);
            $translator->addLoader('po', new PoFileLoader);
            $translator->addLoader('array', new ArrayLoader);

            return $translator;
        };
    }

    public function boot(Application $app)
    {
        require __DIR__.'/functions.php';

        self::$translator = $app['translator'];
    }
}
