<?php

namespace Roave\Zf1Migration;

use function strtolower;

/**
 * Translates resource name used with Zend_Application_Bootstrap_Bootstrap to
 * service name in container.
 *
 * To be used together with Roave\Zf1Migration\Application\Bootstrap\Bootstrap
 * for transparent resource proxy to Psr Container
 */
function mapResourceName(string $name) : string
{
    return 'zf1_resource_' . strtolower($name);
}
