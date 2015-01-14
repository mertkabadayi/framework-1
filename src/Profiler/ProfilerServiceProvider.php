<?php

namespace Pagekit\Profiler;

use Pagekit\Database\DataCollector\DatabaseDataCollector;
use Pagekit\Profiler\Event\ToolbarListener;
use Pagekit\Routing\DataCollector\RoutesDataCollector;
use Pagekit\Application;
use Pagekit\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\EventDataCollector;
use Symfony\Component\HttpKernel\DataCollector\MemoryDataCollector;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector;
use Symfony\Component\HttpKernel\DataCollector\TimeDataCollector;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\EventListener\ProfilerListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\SqliteProfilerStorage;
use Symfony\Component\Stopwatch\Stopwatch;

class ProfilerServiceProvider implements ServiceProviderInterface, EventSubscriberInterface
{
    protected $app;

    public function register(Application $app)
    {
        $this->app = $app;

        if (!$app['config']['profiler.enabled']) {
            return;
        }

        if (!(class_exists('SQLite3') || (class_exists('PDO') && in_array('sqlite', \PDO::getAvailableDrivers(), true)))) {
            return;
        }

        $app['profiler'] = function($app) {

            $profiler = new Profiler($app['profiler.storage']);

            if ($app['events'] instanceof TraceableEventDispatcherInterface) {
                $app['events']->setProfiler($profiler);
            }

            return $profiler;
        };

        $app['profiler.storage'] = function($app) {
            return new SqliteProfilerStorage('sqlite:'.$app['config']['profiler.file'], '', '', 86400);
        };

        $app['profiler.stopwatch'] = function() {
            return new Stopwatch;
        };

        $app->extend('events', function($dispatcher, $app) {
            return new TraceableEventDispatcher($dispatcher, $app['profiler.stopwatch']);
        });
    }

    public function boot(Application $app)
    {
        if (!isset($app['profiler'])) {
            return;
        }

        if (isset($app['view'])) {
            $view = $app['view'];
            unset($app['view']);
            $app['view'] = new TraceableView($view, $app['profiler.stopwatch']);
        }

        $toolbar = __DIR__ . '/views/toolbar/';
        $panel   = __DIR__ . '/views/panel/';

        $app['profiler']->add($request = new RequestDataCollector, "$toolbar/request.php", "$panel/request.php", 40);
        $app['profiler']->add(new RoutesDataCollector($app['router'], $app['path.cache']), "$toolbar/routes.php", "$panel/routes.php", 35);
        $app['profiler']->add(new TimeDataCollector, "$toolbar/time.php", "$panel/time.php", 20);
        $app['profiler']->add(new MemoryDataCollector, "$toolbar/memory.php");
        $app['profiler']->add(new EventDataCollector, "$toolbar/events.php", "$panel/events.php", 30);

        if (isset($app['db']) && isset($app['db.debug_stack'])) {
            $app['profiler']->add(new DatabaseDataCollector($app['db'], $app['db.debug_stack']), "$toolbar/db.php", "$panel/db.php", -10);
            $app['db']->getConfiguration()->setSQLLogger($app['db.debug_stack']);
        }

        $app['events']->addSubscriber($request);
        $app['events']->addSubscriber(new ProfilerListener($app['profiler']));
        $app['events']->addSubscriber($this);
    }

    public function onKernelRequest()
    {
        $this->app['callbacks']->get('_profiler/{token}', '_profiler', function ($token) {

            if (!$profile = $this->app['profiler']->loadProfile($token)) {
                return new Response;
            }

            return new Response($this->app['view']->render(__DIR__.'/views/toolbar.php', ['profiler' => $this->app['profiler'], 'profile' => $profile, 'token' => $token]));

        })->setDefault('_maintenance', true);
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request  = $event->getRequest();

        if ($event->isMasterRequest()
            && !$request->isXmlHttpRequest()
            && !$request->attributes->get('_disable_profiler_toolbar')
            && $response->headers->has('X-Debug-Token')
            && !$response->isRedirection()
            && !($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
            && 'html' === $request->getRequestFormat()
        ) {
            $this->injectToolbar($response);
        }
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param Response $response A Response instance
     */
    protected function injectToolbar(Response $response)
    {
        $content  = $response->getContent();

        if (false === $pos = strripos($content, '</body>')) {
            return;
        }

        $token    = $response->headers->get('X-Debug-Token');
        $route    = $this->app['url']->route('_profiler', compact('token'));
        $url      = $this->app['file']->getUrl(__DIR__.'/assets');
        $markup[] = "<div id=\"profiler\" data-url=\"{$url}\" data-route=\"{$route}\" style=\"display: none;\"></div>";
        $markup[] = "<script src=\"{$url}/js/profiler.js\"></script>";

        $response->setContent(substr_replace($content, implode("\n", $markup), $pos));
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST  => ['onKernelRequest', 100],
            KernelEvents::RESPONSE => ['onKernelResponse', -100]
        ];
    }
}
