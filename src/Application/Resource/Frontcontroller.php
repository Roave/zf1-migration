<?php

namespace Roave\Zf1Migration\Application\Resource;

use Psr\Container\ContainerInterface;
use Zend_Application_Bootstrap_BootstrapAbstract;
use Zend_Application_Resource_Exception;
use Zend_Application_Resource_Frontcontroller;
use Zend_Controller_Action_HelperBroker;

use function is_array;
use function is_string;
use function strtolower;

class Frontcontroller extends Zend_Application_Resource_Frontcontroller
{
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        if (! $bootstrap instanceof Zend_Application_Bootstrap_BootstrapAbstract) {
            throw new Zend_Application_Resource_Exception(sprintf(
                '%s requires Bootstrap to be available and to be instance of %s',
                self::class,
                Zend_Application_Bootstrap_BootstrapAbstract::class
            ));
        }
        $bootstrap->bootstrap(ContainerInterface::class);
        $container = $bootstrap->getResource(ContainerInterface::class);

        $front = $this->getFrontController();

        foreach ($this->getOptions() as $key => $value) {
            switch (strtolower($key)) {
                case 'controllerdirectory':
                    if (is_string($value)) {
                        $front->setControllerDirectory($value);
                    } elseif (is_array($value)) {
                        foreach ($value as $module => $directory) {
                            $front->addControllerDirectory($directory, $module);
                        }
                    }
                    break;

                case 'modulecontrollerdirectoryname':
                    $front->setModuleControllerDirectoryName($value);
                    break;

                case 'moduledirectory':
                    if (is_string($value)) {
                        $front->addModuleDirectory($value);
                    } elseif (is_array($value)) {
                        foreach ($value as $moduleDir) {
                            $front->addModuleDirectory($moduleDir);
                        }
                    }
                    break;

                case 'defaultcontrollername':
                    $front->setDefaultControllerName($value);
                    break;

                case 'defaultaction':
                    $front->setDefaultAction($value);
                    break;

                case 'defaultmodule':
                    $front->setDefaultModule($value);
                    break;

                case 'baseurl':
                    if (! empty($value)) {
                        $front->setBaseUrl($value);
                    }
                    break;

                case 'params':
                    $front->setParams($value);
                    break;

                case 'plugins':
                    foreach ((array) $value as $pluginClass) {
                        $stackIndex = null;
                        if (is_array($pluginClass)) {
                            $pluginClass = array_change_key_case($pluginClass, CASE_LOWER);
                            if (isset($pluginClass['class'])) {
                                if (isset($pluginClass['stackindex'])) {
                                    $stackIndex = $pluginClass['stackindex'];
                                }

                                $pluginClass = $pluginClass['class'];
                            }
                        }
                        if ($container->has($pluginClass)) {
                            $plugin = $container->get($pluginClass);
                        } else {
                            $plugin = new $pluginClass();
                        }
                        $front->registerPlugin($plugin, $stackIndex);
                    }
                    break;

                case 'returnresponse':
                    $front->returnResponse((bool) $value);
                    break;

                case 'throwexceptions':
                    $front->throwExceptions((bool) $value);
                    break;

                case 'actionhelperpaths':
                    if (is_array($value)) {
                        foreach ($value as $helperPrefix => $helperPath) {
                            Zend_Controller_Action_HelperBroker::addPath($helperPath, $helperPrefix);
                        }
                    }
                    break;

                case 'dispatcher':
                    if (!isset($value['class'])) {
                        throw new Zend_Application_Resource_Exception('You must specify dispatcher class');
                    }

                    $dispatcherClass = $value['class'];
                    if (! $container->has($dispatcherClass)) {
                        throw new Zend_Application_Resource_Exception(sprintf(
                            'You must define custom dispatcher in a PSR Container. Requested dispatcher was "%s"',
                            $dispatcherClass
                        ));
                    }

                    $front->setDispatcher($container->get($dispatcherClass));
                    break;
                default:
                    $front->setParam($key, $value);
                    break;
            }
        }

        return $front;
    }
}
