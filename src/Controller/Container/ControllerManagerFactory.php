<?php

declare(strict_types=1);

namespace Roave\Zf1Migration\Container;

use Psr\Container\ContainerInterface;
use Roave\Zf1Migration\Controller\ControllerManager;

use const Roave\Zf1Migration\ZF1_CONTROLLERS_CONFIG;

class ControllerManagerFactory
{
    /**
     * Create the controller manager service
     *
     * Creates and returns an instance of ControllerManager. The
     * only controllers this manager will allow are those defined in the
     * application configuration's "zf1_controllers" array.
     *
     */
    public function __invoke(ContainerInterface $container, string $name, array $options = null) : ControllerManager
    {
        if (null !== $options) {
            return new ControllerManager($container, $options);
        }
        return new ControllerManager($container, static::getControllersConfig($container));
    }

    public static function getControllersConfig(ContainerInterface $container) : array
    {
        $config = $container->has('config') ? $container->get('config') : [];

        return $config[ZF1_CONTROLLERS_CONFIG] ?? [];
    }
}
