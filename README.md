# CakePHP Tags Plugin

This branch targets CakePHP 6 and PHP 8.5 or later. Use the `cake6` branches of companion CakePHP plugins during development.

Composer applications consuming this development branch must set `minimum-stability` to `dev` and `prefer-stable` to `true`, then require this plugin as `dev-cake6`. Companion plugins also use their `cake6` branches. The published documentation website covers stable CakePHP 5 releases; use this branch's `docs/` directory for the upgrade documentation.

[![CI](https://github.com/dereuromark/cakephp-tags/actions/workflows/ci.yml/badge.svg?branch=cake6)](https://github.com/dereuromark/cakephp-tags/actions/workflows/ci.yml?query=branch%3Acake6)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-tags/master.svg)](https://codecov.io/github/dereuromark/cakephp-tags/branch/master)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-tags/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-tags)
[![Total Downloads](https://img.shields.io/packagist/dt/dereuromark/cakephp-tags.svg?style=flat-square)](https://packagist.org/packages/dereuromark/cakephp-tags)
[![License](https://poser.pugx.org/dereuromark/cakephp-tags/license.svg)](LICENSE)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-purple.svg?style=flat-square)](https://github.com/php-fig-rectified/fig-rectified-standards)

Make tagging of entities a piece of cake.

This branch targets **CakePHP 6**. Composer declares the supported PHP and CakePHP ranges. For details see [version map](https://github.com/dereuromark/cakephp-tags/wiki#cakephp-version-map).

Upgrading from 2.x? See [UPGRADE.md](UPGRADE.md) for the v3 migration notes.

## Documentation

Read the [documentation website](https://dereuromark.github.io/cakephp-tags/). Start with [installation](https://dereuromark.github.io/cakephp-tags/guide/installation), then follow the [beginner tagging example](https://dereuromark.github.io/cakephp-tags/guide/getting-started).

## Demo
See Sandbox @ https://sandbox.dereuromark.de/sandbox/tags

## Admin Backend

The plugin ships with an admin backend under the `Admin` prefix at `/admin/tags`.

![Tags Admin Dashboard](docs/screenshots/tags-admin-dashboard.png)

![Tags Admin Index](docs/screenshots/tags-admin-index.png)

Tutorial and Blog Post: https://www.dereuromark.de/2018/07/12/tutorial-cakephp-tagging/

