# PSR Container

This package defines `Psr\Container\ContainerInterface` resource for use with
`Zend_Application`. As the name implies it is an instance of
[PSR-11 Container Interface](https://www.php-fig.org/psr/psr-11/)

## Enable the resource

`Psr\Container\ContainerInterface` resource is provided by resource plugin
`Roave\Zf1Migration\Application\Resource\ServiceManagerContainer`,
which uses [zend-servicemanager](https://docs.zendframework.com/zend-servicemanager/)
as a PSR Container implementation.

It can be enabled in your application with:

```ini
; application.ini
resources.Roave\Zf1Migration\Application\Resource\ServiceManagerContainer =
```

Alternatively, it will be registered by default if you use
`Roave\Zf1Migration\Application\Bootstrap\Bootstrap`.

## Usage

Once resource plugin is registered, it can be bootstrapped and used with:

```php
use Psr\Container\ContainerInterface;

// explicit bootstrap:
$bootstrap->bootstrap(ContainerInterface::class);

// accessing container:
$container = $bootstrap->getResource(ContainerInterface::class);
// or
$container = Zend_Registry::get(ContainerInterface::class);
```

Note: static usage with `Zend_Registry` is not recommended and should only be
used transiently while codebase is refactored from static state to dependency
injection.

### Configuration

`ServiceManagerContainer` resource plugin looks for the main configuration
in application options under `configuration` key.

Container configuration is provided within the `dependencies` key of
configuration. That key is structured with `zend-servicemanager` configuration format:

```php
return [
    'dependencies' => [
        'services' => [
            // name => instance pairs
            'config' => $configuration,
        ],
        'aliases' => [
            // alias => target pairs
            'page-handler' => SomePageHandler::class,
        ],
        'factories' => [
            // service => factory pairs
            SomePageHandler::class => SomePageHandlerFactory::class,
        ],
        'invokables' => [
            // service => instantiable class pairs
            SomeInstantiableClass::class => SomeInstantiableClass::class,
            'an-alias-for' => SomeInstantiableClass::class,
        ],
        'delegators' => [
            // service => array of delegator factory pairs
            SomeInstantiableClass::class => [
                InjectListenersDelegator::class,
                InjectLoggerDelegator::class,
            ],
        ],
    ],
];
```

For more information please refer to
[Configuring the service manager](https://docs.zendframework.com/zend-servicemanager/configuring-the-service-manager/)

Following Zend Framework convention, configuration array is stored within container
under `config` key. Additionally, ZF1 bootstrap object is stored in container under
`zf1_bootstrap` key, alternatively constant `Roave\Zf1Migration\SERVICE_ZF1_BOOTSTRAP` can be
used for IDE completion.

