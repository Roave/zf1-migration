<?php

namespace Roave\Zf1Migration\Application\Bootstrap;

use Psr\Container\ContainerInterface;
use Zend_Application_Bootstrap_Bootstrap;

use function dirname;
use function Roave\Zf1Migration\mapResourceName;

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    public function __construct($application)
    {
        // override default resources but allow them to be overridden in userland
        $pluginLoader = $this->getPluginLoader();
        $resourcePath = dirname(__DIR__ ) . '/Resource/';
        $pluginLoader->addPrefixPath('Roave\Zf1Migration\Application\Resource\\',$resourcePath);

        parent::__construct($application);
        if (! $this->hasPluginResource(ContainerInterface::class)) {
            $this->registerPluginResource('ServiceManagerContainer');
        }
    }

    public function hasResource($name)
    {
        if ($name === ContainerInterface::class) {
            // never try to fetch container from itself
            return parent::hasResource($name);
        }

        $container = $this->getResource(ContainerInterface::class);

        $serviceName = mapResourceName($name);
        return $container->has($serviceName) || parent::hasResource($name);
    }

    public function getResource($name)
    {
        if ($name === ContainerInterface::class) {
            // ensure container is bootstrapped
            $this->bootstrap(ContainerInterface::class);
            // never try to fetch container from itself
            return parent::getResource($name);
        }

        $container = $this->getResource(ContainerInterface::class);

        $serviceName = mapResourceName($name);
        if ($container->has($serviceName)) {
            return $container->get($serviceName);
        }

        return parent::getResource($name);
    }
}
