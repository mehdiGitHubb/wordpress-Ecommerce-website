# Dhii - WordPress Interop
[![Continuous Integration](https://github.com/wp-oop/wordpress-interface/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/wp-oop/wordpress-interface/actions/workflows/continuous-integration.yml)
[![Latest Stable Version](https://poser.pugx.org/wp-oop/wordpress-interface/v)](http://packagist.org/packages/wp-oop/wordpress-interface)
[![Latest Unstable Version](https://poser.pugx.org/wp-oop/wordpress-interface/v/unstable)](http://packagist.org/packages/wp-oop/wordpress-interface)

Interfaces for interop within WordPress.

## Details
Often, multiple packages need to operate on the various aspects of WordPress,
while centralizing them. Unfortunately, many of these aspects are not represented
by any type in WordPress. An example of this is a modular plugin, where each
module needs to independently interface with such a part of WordPress that is centralized,
like a Plugin or Post entity.

Also, it can be useful to write code that addresses a specific aspect of WordPress,
and type-safety is desirable, but WordPress feels too bulky to include. While WordPress
can be added as a dev-dependency, the dependency graph will not reflect these hidden requirements.
For example, a Post type would be useful to represents a post.

In addition, some modules can be developed in a way to be usable in a variety of platforms.
In these cases it would be necessary to rely on a proprietary standard.

This interop standard aims to address the above concerns by providing types for common
aspects of WordPress. 
