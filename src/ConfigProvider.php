<?php

namespace Roave\Zf1Migration;

use Roave\Zf1Migration\Container\ControllerManagerFactory;
use Roave\Zf1Migration\Controller\ControllerManager;

use const Roave\Zf1Migration\ZF1_CONTROLLERS_CONFIG;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            ZF1_CONTROLLERS_CONFIG => [],
        ];
    }

    public function getDependencies() : array
    {
        return [
            'factories' => [
                ControllerManager::class => ControllerManagerFactory::class,
            ],
        ];
    }
}
