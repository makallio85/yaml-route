Yaml-route
----------
[![Latest Stable Version](https://poser.pugx.org/makallio85/yaml-route/v/stable)](https://packagist.org/packages/makallio85/yaml-route) [![Total Downloads](https://poser.pugx.org/makallio85/yaml-route/downloads)](https://packagist.org/packages/makallio85/yaml-route) [![Latest Unstable Version](https://poser.pugx.org/makallio85/yaml-route/v/unstable)](https://packagist.org/packages/makallio85/yaml-route) [![License](https://poser.pugx.org/makallio85/yaml-route/license)](https://packagist.org/packages/makallio85/yaml-route) [![Build Status](https://travis-ci.org/makallio85/yaml-route.svg?branch=master)](https://travis-ci.org/makallio85/yaml-route) [![Coverage Status](https://coveralls.io/repos/makallio85/yaml-route/badge.svg?branch=master&service=github)](https://coveralls.io/github/makallio85/yaml-route?branch=master)

Yaml-route provides possibility to configure CakePHP 3 routes with simple YAML files. This is basically just wrapper for CakePHP core routing that parses YAML files and makes proper calls to ```Cake\Core\Router```.

Main goal is not to implement all fancy features that CakePHP 3 routing provides, only those ones that are really needed. Of course this is relative to developer, so feel free to fork and commit your own code.

### Installation ###

```composer require makallio85/yaml-route```

### Usage ###

1. Replace all contents in ```config/routes.php``` file with single method call ```makallio85\YamlRoute\Generator::getInstance()->run()```
2. Load all plugins by calling ```makallio85\YamlRoute\Plugin::getInstance()->load($plugin, $options)```  Method is basically just wrapper for ```Cake\Core\Plugin::load()``` method. Note that ```Cake\Core\Plugin::loadAll()``` method is not supported and all plugins should be loaded one at time.
3. Add your own route to ```routes.yml``` files to your project and desired plugins.

### About route configuration ###

Every route is automatically named with its key. Root route should be named as root by convention.
Route can contain path and config keys. Path is always string but config can be string that references to another YAML file that contains routes configuration. Syntax for external path is "PluginName.RouteFileNameWithoutExtension". All route configurations should be placed in config folder of project or plugin.

Route can also contain subroutes and they are defined inside ```config.routes``` key

```config``` key can contain keys listed below

| Key        | Type            | Description                   |
|:-----------|:----------------|:------------------------------|
| controller | string          | Route controller              |
| action     | string          | Route action                  |
| plugin     | string          | Route plugin                  |
| _method    | array or string | Route method                  |
| extensions | array           | Allowed extensions            |
| routes     | array           | Subroutes                     |
| validate   | array           | List of variables to validate |

Note that subroutes can't contain routes so ```config.routes``` for subroutes is not available.

### Examples ###

##### Basic routing #####
```config/routes.yml``` like this
```
root:
  path: /
```

Turns into this

```php
\Cake\Routing\Router::scope('/', [], function ($routes) {
	$routes->fallbacks('DashedRoute');
});

\Cake\Core\Plugin::routes();
```

##### Plugin Routing #####

```plugins/PluginCars/config/routes.yml``` like this

```
cars:
  path: /cars
  config:
    plugin: PluginCars
    controller: Cars
    action: index
    extensions:
      - json
      - xml
    routes:
      bmws_list:
        path: /bmws
        config:
          controller: Bmws
      bmws_view:
        path: /bmws/{id}
        config:
          _method: GET
          controller: Bmws
          action: view
          validate:
            id: '[0-9]+'
      bmws_add:
        path: /bmws/add
        config:
          _method: POST
          controller: Bmws
          action: add
      ladas:
        path: /ladas
        config:
          controller: Ladas
```

Turns into this

```php
\Cake\Routing\Router::plugin('PluginCars', ['path' => '/cars'], function ($routes) {
  $routes->extensions(['0' => 'json', '1' => 'xml']);
  $routes->connect('/', ['plugin' => 'PluginCars', 'controller' => 'Cars', 'action' => 'index'], ['_name' => 'cars']);
  $routes->connect('/bmws', ['controller' => 'Bmws'], ['_name' => 'bmws_list']);
  $routes->connect('/bmws/:id', ['_method' => 'GET', 'controller' => 'Bmws', 'action' => 'view'], ['_name' => 'bmws_view', 'pass' => ['0' => 'id'], 'id' => '[0-9]+']);
  $routes->connect('/bmws/add', ['_method' => 'POST', 'controller' => 'Bmws', 'action' => 'add'], ['_name' => 'bmws_add']);
  $routes->connect('/ladas', ['controller' => 'Ladas'], ['_name' => 'ladas']);
  $routes->fallbacks('DashedRoute');
});

\Cake\Core\Plugin::routes();
```

### Debugging ###

If you want to debug generated routes, you can set debug parameter to true when calling ```makallio85\YamlRoute\Generator::getInstance()->run(true)```.
After that, you are able to get executed calls by calling ```makallio85\YamlRoute\Generator::getInstance()->getDump()```.

### toDo ###

- Add support for true inheritance by allowing subroute to contain subroute
- ~~Add tests~~ Add more tests
- Refactor classes
- ~~Add support for extensions~~
- ~~Improve exception handling~~

### License ###

The MIT License (MIT)

Copyright (c) 2016 makallio85

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
