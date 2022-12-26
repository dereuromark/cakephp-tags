# CakePHP Tags Plugin

[![CI](https://github.com/dereuromark/cakephp-tags/workflows/CI/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-tags/actions?query=workflow%3ACI+branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-tags/master.svg)](https://codecov.io/github/dereuromark/cakephp-tags/branch/master)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-tags/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-tags)
[![Total Downloads](https://img.shields.io/packagist/dt/dereuromark/cakephp-tags.svg?style=flat-square)](https://packagist.org/packages/dereuromark/cakephp-tags)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://packagist.org/packages/dereuromark/cakephp-tags)

Make tagging of entities a piece of cake.

This branch is for **CakePHP 4.2+**. For details see [version map](https://github.com/dereuromark/cakephp-tags/wiki#cakephp-version-map).

## Install

Using Composer:

```
composer require dereuromark/cakephp-tags
```

You then need to load the plugin. In `src/Application.php`, something like:

```php
public function bootstrap() {
    parent::bootstrap();
    $this->addPlugin('Tags');
}

```

Also don't forget to run migration (e.g. using Migrations plugin):
```
bin/cake migrations migrate -p Tags
```

## Demo
See Sandbox @ https://sandbox.dereuromark.de/sandbox/tags

Tutorial and Blog Post: https://www.dereuromark.de/2018/07/12/tutorial-cakephp-tagging/

## Documentation

For documentation, as well as tutorials, see the [docs](docs/) directory of this repository.
