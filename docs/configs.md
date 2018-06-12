# Configuration management

Recommended way to manage configuration is with [zend-config-aggregator](https://docs.zendframework.com/zend-config-aggregator/)
and [zend-component-installer](https://docs.zendframework.com/zend-component-installer/)

The `zend-config-aggregator` library collects and merges configuration from different
sources. It also supports configuration caching.

One such source is [Config Providers](https://docs.zendframework.com/zend-config-aggregator/config-providers/)
of various Zend Framework components. Listing them for config aggregator
allows component features to become immediately available in our Container:

```php
<?php // config/config.php

use Zend\ConfigAggregator\ConfigAggregator;
use Zend\ConfigAggregator\PhpFileProvider;

$aggregator = new ConfigAggregator([
    Zend\Validator\ConfigProvider::class,

    Application\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider('config/autoload/{{,*.}global,{,*.}local}.php'),

    // Load development config if it exists
    new PhpFileProvider('config/development.config.php'),
], 'data/config-cache.php');

return $aggregator->getMergedConfig();
```

```php
<?php // config/application.php - zf1 specific config

use Zend\ConfigAggregator\ConfigAggregator;

$aggregator = new ConfigAggregator([
    // load zf1 specific configuration
    function () {
        // Note: you would want to change the way environment specific configs
        // are handled because of config caching
        return (new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production'))
            ->toArray();
    },
    // Load main configuration
    function() {
        return ['configuration' => require 'config/config.php'];
    },
], 'data/application-config-cache.php');

return $aggregator->getMergedConfig();
```

```php
<?php // public/index.php

// ...
chdir(dirname(__DIR__));
require 'vendor/autoload.php';

// ...

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    require 'config/application.php'
);
$application->bootstrap()
            ->run();
```

---

Example of config aggregator usage can be found in
[zend-expressive skeleton application](https://github.com/zendframework/zend-expressive-skeleton)
