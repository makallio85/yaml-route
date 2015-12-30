Not stable version. Do not use yet :D

```composer require makallio85/cake-yaml "dev-master"```

Replace all content in ```config/routes.php``` with ```CakeYaml\Generator::getInstance()->run();```

In bootstrap, all plugins should be loaded by calling ```CakeYaml\Plugin::load()``` method instead of ```Cake\Core\Plugin::load()```.

Add routes.yml to your project or any plugin loaded in order to use Yaml style configuration for routes:

```javascript
animals:
  path: /animals
  config:
    plugin: PluginAnimals
    controller: Animals
    action: index
    extensions:
      - json
    routes:
      cats:
        path: /cats
        controller: Cats
      dogs:
        path: /dogs
        controller: Dogs
```