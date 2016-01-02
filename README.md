Cake Yaml routing library
-------------------------

Cake-yaml provides possibility to configure CakePHP 3 routes with yaml files. This is basically just wrapper for CakePHP core routing that parses yaml files and makes proper calls to Cake\Core\Router.

### Installation ###

```composer require makallio85/cake-yaml "dev-master"```

### Usage ###

1. Replace all contents in ```config/routes.php``` file with single method call ```CakeYaml\Generator::getInstance()->run()```
2. Load all plugins by calling ```CakeYaml\Plugin::getInstance()->load($plugin, $options)```  Method is basically just wrapper for ```Cake\Core\Plugin::load()``` method. Note that ```Cake\Core\Plugin::loadAll()``` method is not supported and all plugins should be loaded one at time.
3. Add your own route to ```routes.yml``` files to your project and desired plugins.

### About route configuration ###

About to be...

### Examples ###

Add examples here...

### Debugging ###

If you want to debug generated routes, you can set debug parameter to true when calling ```CakeYaml\Generator::getInstance()->run(true)```.
After that, you are able to get executed calls by calling ```CakeYaml\Generator::getInstance()->getDump()```.