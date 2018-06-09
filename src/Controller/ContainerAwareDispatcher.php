<?php

declare(strict_types=1);

namespace Roave\Zf1Migration\Controller;

use Psr\Container\ContainerInterface;
use Throwable;
use Zend_Controller_Dispatcher_Exception;
use Zend_Controller_Dispatcher_Standard;
use Zend_Controller_Request_Abstract;
use Zend_Controller_Response_Abstract;

use function ob_get_clean;
use function ob_get_level;
use function ob_start;
use function sprintf;

class ContainerAwareDispatcher extends Zend_Controller_Dispatcher_Standard
{
    private $container;
    private $controllers;

    public function __construct(ContainerInterface $container, ControllerManager $controllers, array $params = [])
    {
        $this->container = $container;
        $this->controllers = $controllers;
        parent::__construct($params);
    }

    public function isDispatchable(Zend_Controller_Request_Abstract $request)
    {
        $controllerName = $this->getControllerClass($request);
        if (! $controllerName) {
            return false;
        }

        $finalName  = $controllerName;
        if (($this->_defaultModule != $this->_curModule)
            || $this->getParam('prefixDefaultModule')) {
            $finalName = $this->formatClassName($this->_curModule, $controllerName);
        }
        return $this->controllers->has($finalName);
    }

    public function dispatch(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response)
    {
        $this->setResponse($response);

        /**
         * Get controller class
         */
        if (! $this->isDispatchable($request)) {
            $controller = $request->getControllerName();
            if (! $this->getParam('useDefaultControllerAlways') && ! empty($controller)) {
                throw new Zend_Controller_Dispatcher_Exception(
                    sprintf('Invalid controller specified (%s)', $request->getControllerName())
                );
            }

            $className = $this->getDefaultControllerClass($request);
        } else {
            $className = $this->getControllerClass($request);
            if (! $className) {
                $className = $this->getDefaultControllerClass($request);
            }
        }

        /**
         * If we're in a module or prefixDefaultModule is on, we must add the module name
         * prefix to the contents of $className, as getControllerClass does not do that automatically.
         * We must keep a separate variable because modules are not strictly PSR-0: We need the no-module-prefix
         * class name to do the class->file mapping, but the full class name to insantiate the controller
         */
        $controllerName = $className;
        if (($this->_defaultModule != $this->_curModule)
            || $this->getParam('prefixDefaultModule')) {
            $controllerName = $this->formatClassName($this->_curModule, $className);
        }

        try {
            $controller = $this->controllers->build($controllerName, [
                'request' => $request,
                'response' => $this->getResponse(),
                'params' => $this->getParams()
            ]);
        } catch (Throwable $exception) {
            throw new Zend_Controller_Dispatcher_Exception(
                sprintf('Failed to create controller "%s": %s', $controllerName, $exception->getMessage()),
                0,
                $exception
            );
        }

        /**
         * Retrieve the action name
         */
        $action = $this->getActionMethod($request);

        /**
         * Dispatch the method call
         */
        $request->setDispatched(true);

        // by default, buffer output
        $disableOb = $this->getParam('disableOutputBuffering');
        $obLevel   = ob_get_level();
        if (empty($disableOb)) {
            ob_start();
        }

        try {
            $controller->dispatch($action);
        } catch (Throwable $e) {
            // Clean output buffer on error
            $curObLevel = ob_get_level();
            if ($curObLevel > $obLevel) {
                do {
                    ob_get_clean();
                    $curObLevel = ob_get_level();
                } while ($curObLevel > $obLevel);
            }
            throw $e;
        }

        if (empty($disableOb)) {
            $content = ob_get_clean();
            $response->appendBody($content);
        }

        // Destroy the page controller instance and reflection objects
        $controller = null;
    }
}
