<?php

namespace Roave\Zf1Migration\Controller;

use Zend_Controller_Action_Interface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;

use function get_class;
use function gettype;
use function is_object;
use function sprintf;

/**
 * Manager for loading controllers
 *
 * Does not define any controllers by default, but does add a validator.
 */
class ControllerManager extends AbstractPluginManager
{
    /**
     * We do not want arbitrary classes instantiated as controllers.
     *
     * @var bool
     */
    protected $autoAddInvokableClass = false;

    /**
     * Controllers must be of this type.
     *
     * @var string
     */
    protected $instanceOf = Zend_Controller_Action_Interface::class;

    /**
     * Validate a plugin
     *
     * {@inheritDoc}
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Controller of type "%s" is invalid; must implement "%s"',
                (is_object($instance) ? get_class($instance) : gettype($instance)),
                $this->instanceOf
            ));
        }
    }
}
