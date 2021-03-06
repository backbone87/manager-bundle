<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ManagerBundle\Routing;

use Contao\ManagerPlugin\PluginLoader;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

class RouteLoader
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var PluginLoader
     */
    private $pluginLoader;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader
     * @param PluginLoader    $pluginLoader
     * @param KernelInterface $kernel
     */
    public function __construct(LoaderInterface $loader, PluginLoader $pluginLoader, KernelInterface $kernel)
    {
        $this->loader = $loader;
        $this->pluginLoader = $pluginLoader;
        $this->kernel = $kernel;
    }

    /**
     * Returns route collection build from all plugins.
     *
     * @return RouteCollection
     */
    public function loadFromPlugins()
    {
        /** @var RouteCollection $collection */
        $collection = array_reduce(
            $this->pluginLoader->getInstancesOf(PluginLoader::ROUTING_PLUGINS, true),
            function (RouteCollection $collection, RoutingPluginInterface $plugin) {
                $routes = $plugin->getRouteCollection($this->loader->getResolver(), $this->kernel);

                if ($routes instanceof RouteCollection) {
                    $collection->addCollection($routes);
                }

                return $collection;
            },
            new RouteCollection()
        );

        if ($catchAll = $collection->get('contao_catch_all')) {
            $collection->add('contao_catch_all', $catchAll);
        }

        return $collection;
    }
}
