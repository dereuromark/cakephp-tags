# Tags

[![Build Status](https://img.shields.io/travis/UseMuffin/Tags/master.svg?style=flat-square)](https://travis-ci.org/UseMuffin/Tags)
[![Coverage](https://img.shields.io/codecov/c/github/UseMuffin/Tags.svg?style=flat-square)](https://codecov.io/github/UseMuffin/Tags)
[![Total Downloads](https://img.shields.io/packagist/dt/muffin/tags.svg?style=flat-square)](https://packagist.org/packages/muffin/tags)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

{{@TODO description}}

## Install

Using [Composer][composer]:

```
composer require dereuromark/cakephp-tags:dev-master
```

You then need to load the plugin. In `boostrap.php`, something like:

```php
\Cake\Core\Plugin::load('Tags');
```

Also don't forget to run migration:
```
bin/cake migrations migrate -p Tags
```
