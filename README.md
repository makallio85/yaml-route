Cake-yaml routing
-------------------------

Cake-yaml routing provides possibility to configure CakePHP 3 routes with simple yaml files. This is basically just wrapper for CakePHP core routing that parses yaml files and makes proper calls to Cake\Core\Router.

### Installation ###

```composer require makallio85/cake-yaml "dev-master"```

### Usage ###

1. Replace all contents in ```config/routes.php``` file with single method call ```CakeYaml\Generator::getInstance()->run()```
2. Load all plugins by calling ```CakeYaml\Plugin::getInstance()->load($plugin, $options)```  Method is basically just wrapper for ```Cake\Core\Plugin::load()``` method. Note that ```Cake\Core\Plugin::loadAll()``` method is not supported and all plugins should be loaded one at time.
3. Add your own route to ```routes.yml``` files to your project and desired plugins.

### About route configuration ###

Every route are automatically named with its key. Root route should be named as root by convention.
Route can contain path and config keys. Path is always string but config can be string that references to another yaml file that contains configuration. Syntax for external path is "PluginName.RouteFileName". All route configurations should be placed in config folder of project or plugin.

Possible keys for config are listed below:

| Key        | Type   | Description         | Notes               |
|:-----------|:-------|:--------------------|:--------------------|
| controller | string | Route controller    |                     |
| action     | string | Route action        |                     |
| plugin     | string | Route plugin        |                     |
| extensions | array  | Allowed extensions  | Not implemented yet |
| routes     | array  | Subroutes           |                     |

Note that ```routes``` key can contain all keys above except routes.

### Examples ###

##### Basic routing #####
```config/routes.yml``` like this
```
root:
  path: /
```

Turns into this

```
\Cake\Routing\Router::scope('/', [], function ($routes) {
	$routes->fallbacks('DashedRoute');
});

\Cake\Core\Plugin::routes();
```

##### Plugin Routing #####

```PluginCars/config/routes.yml``` like this

```
cars:
  path: /cars
  config:
    plugin: PluginCars
    controller: Cars
    action: index
    routes:
      bmws:
        path: /bmws
        controller: Bmws
      ladas:
        path: /ladas
        controller: Ladas
```

Turns into this

```
\Cake\Routing\Router::plugin('PluginCars', ['path' => '/cars'], function ($routes) {
	$routes->connect('/', ['controller' => 'Cars', 'action' => 'index'], ['_name' => 'cars']);
	$routes->connect('/bmws', ['path' => '/bmws', 'controller' => 'Bmws'], ['_name' => 'bmws']);
	$routes->connect('/ladas', ['path' => '/ladas', 'controller' => 'Ladas'], ['_name' => 'ladas']);
	$routes->fallbacks('DashedRoute');
});

\Cake\Core\Plugin::routes();
```

### Debugging ###

If you want to debug generated routes, you can set debug parameter to true when calling ```CakeYaml\Generator::getInstance()->run(true)```.
After that, you are able to get executed calls by calling ```CakeYaml\Generator::getInstance()->getDump()```.
