<?php

declare(strict_types=1);

namespace Roave\Zf1Migration\Controller\Container;

use Psr\Container\ContainerInterface;
use Roave\Zf1Migration\Controller\ContainerAwareDispatcher;
use Roave\Zf1Migration\Controller\ControllerManager;

class ContainerAwareDispatcherFactory
{
    public function __invoke(ContainerInterface $container) : ContainerAwareDispatcher
    {
        return new ContainerAwareDispatcher($container->get(ControllerManager::class));
    }
}
