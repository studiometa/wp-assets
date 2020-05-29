# Assets

> Manage and autoload your WordPress assets based on a YAML configuration file.

## Installation

Install the package via Composer : 

```bash
composer require studiometa/wp-assets
```

## Usage

Add an `assets.yaml` file in your theme folder, and instantiate the `Studiometa\WP\Assets` class.

```php
// functions.php

use Studiometa\WP\Assets;
$assets = new Assets();
```

### The YAML configuration

The YAML configuration lets you define which assets will be loaded on each template used by your theme, based on the template resolution of WordPress (see [wphierarchy.com](https://wphierarchy.com/)).

```yaml
<template>: # The template name, based on https://wphierarchy.com/
  <type>: # Type of the asset: 'js' or 'css'
    # <handle> is the name of the asset used by WordPress
    # <path> is the path of the asset, relative to the theme folder
    <handle>: <path> 
    # Define the asset as an object to specify the `$media` parameter 
    # for styles and the `$in_footer` for scripts.
    <handle>:
      path: <path>
      media: <media type>
      footer: <boolean>
```

An example of a simple configuration:

```yaml
all: # The `all` key will autoload the defined assets on all pages
  js:
    app: dist/js/app.js
    instant:
      path: /dist/js/components/instant-click.js
      footer: false
  css:
    app: dist/css/app.css
    print:
      path: dist/css/print.css
      media: print

front-page:
  js:
    home: dist/js/pages/home.js
  css:
    home: dist/css/pages/home.css

single:
  js:
    single: dist/js/pages/single.js
  css:
    single: dist/css/pages/single.css
```

The `Assets` class uses the [`template_include` WordPress filter](https://developer.wordpress.org/reference/hooks/template_include/) to load assets based on the template resolution.
