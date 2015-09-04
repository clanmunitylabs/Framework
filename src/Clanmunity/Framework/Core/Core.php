<?php

namespace Clanmunity\Framework\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;

use Clanmunity\Framework\Cache\Cache;
use Clanmunity\Framework\Container\Container;

class Core extends Container implements HttpKernelInterface
{
    /** @var  \Symfony\Component\HttpKernel\HttpKernel */
    protected $kernel;

    /** @var  \Symfony\Component\Routing\RouteCollection */
    protected $routes;

    protected $loader;

    /** @var  \Symfony\Component\HttpKernel\HttpCache\HttpCache */
    protected $useHttpCache;

    /**
     * @param bool $useHttpCache
     */
    public function __construct($useHttpCache = false)
    {
        $this->useHttpCache = $useHttpCache;
    }

    /**
     * @inheritdoc
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->routes = $this->getRouteCollection();

        // create a context using the current request
        $context = new RequestContext();
        $context->fromRequest($request);

        // matches URL based on a set of routes
        $matcher = new UrlMatcher($this->routes, $context);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher));

        // determine the controller to execute
        $resolver = new ControllerResolver();

        $kernel = new HttpKernel($dispatcher, $resolver);

        if ($this->useHttpCache) {
            $cache = new Cache();
            $kernel = new HttpCache($kernel, new Store($cache->getCacheDir()));
        }

        $response = $kernel->handle($request);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createFromGlobals();
        }

        $response = $this->handle($request);
        $response->send();

        return $response;
    }

    protected function getRouteCollection()
    {
        $routes = new RouteCollection();

        // for each route found in our route_env.yml
        // add it to the route collection
        /*$configDirectories = config.dir;
        $locator = new FileLocator($configDirectories);

        // Loads routes files
        $loader =  new YamlFileLoader($locator);
        return $loader;
        };*/

        $routes->add('hello', new Route('/', array('_controller' =>
           function (Request $request) {
               return new Response(sprintf("Hello %s", $request->get('name')));
           }
        )));

        return $routes;
    }

    public function setUseHttpCache($useHttpCache = false)
    {
        $this->useHttpCache = $useHttpCache;
    }
} 
