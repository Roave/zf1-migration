<?php

namespace Roave\Zf1Migration\Application\Resource;

use Psr\Container\ContainerInterface;
use Zend\ServiceManager\ServiceManager;
use Zend_Application_Resource_Exception;
use Zend_Application_Resource_ResourceAbstract;
use Zend_Config;
use Zend_Registry;

use function sprintf;

use const Roave\Zf1Migration\SERVICE_ZF1_BOOTSTRAP;

class ServiceManagerContainer extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var string Register resource under this name, used by Zend_Application_Bootstrap_Bootstrap
     */
    public $_explicitType = ContainerInterface::class; // @codingStandardsIgnoreLine

    public function init()
    {
        $configuration = $this->getBootstrap()->getOption('configuration') ?? [];
        if ($configuration instanceof Zend_Config) {
            $configuration = $configuration->toArray();
        }
        if (! isset($configuration['dependencies'])) {
            throw new Zend_Application_Resource_Exception(sprintf(
                '%s looks for "dependencies" key under "configuration" in application options, none found',
                self::class
            ));
        }

        $deps = $configuration['dependencies'];
        $deps['services'][SERVICE_ZF1_BOOTSTRAP] = $this->getBootstrap();
        $deps['services']['config'] = $configuration;

        $container = new ServiceManager($deps);

        Zend_Registry::getInstance()->set(ContainerInterface::class, $container);

        return $container;
    }
}
