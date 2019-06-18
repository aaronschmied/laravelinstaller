# laravelinstaller

Laravel installer tailored to **my workflow**.

Based on the original [laravel/installer](https://github.com/laravel/installer)

## Usage

Install it globally using composer:

```
composer global require aaronschmied/laravelinstaller
```

Create a new project:

```
laravelasm new MyProject
```

This command will create a new project called "MyProject" and:
- Install the composer dependencies
- Add the ci configuration for gitlab
- Add the codesniffer package
- Install and configure vessel
- Add the assets and the laravel-mix config
- Add the base model class and behaviours
- Add the laravel echo server
- Run yarn and load all node modules
