# CakePHP Tags Plugin

[![Build Status](https://img.shields.io/travis/dereuromark/cakephp-tags/master.svg?style=flat-square)](https://travis-ci.org/dereuromark/cakephp-tags)
[![Total Downloads](https://img.shields.io/packagist/dt/dereuromark/cakephp-tags.svg?style=flat-square)](https://packagist.org/packages/dereuromark/cakephp-tags)
[![License](https://poser.pugx.org/dereuromark/cakephp-tags/license)](https://packagist.org/packages/dereuromark/cakephp-tags)

Make tagging of entities a piece of cake.

## Install

Using Composer:

```
composer require dereuromark/cakephp-tags
```

You then need to load the plugin. In `boostrap.php`, something like:

```php
use Cake\Core\Plugin:

Plugin::load('Tags');
```

Also don't forget to run migration (e.g. using Migrations plugin):
```
bin/cake migrations migrate -p Tags
```

## Demo
See Sandbox @ https://sandbox.dereuromark.de/sandbox/tags

## Documentation

For documentation, as well as tutorials, see the [docs](docs/) directory of this repository.

## Support

For bugs and feature requests, please use the [issues](https://github.com/dereuromark/cakephp-tags/issues) section of this repository.
