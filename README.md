HTTP Client Hints
=================

[![Build Status](https://travis-ci.org/jsor/http-client-hints.svg?branch=master)](https://travis-ci.org/jsor/http-client-hints?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/jsor/http-client-hints/badge.svg?branch=master)](https://coveralls.io/github/jsor/http-client-hints?branch=master)

Utilities for working with
[HTTP Client Hints](https://httpwg.org/http-extensions/client-hints.html).

Installation
------------

Install the latest version with [Composer](https://getcomposer.org).

```bash
composer require jsor/http-client-hints
```

Check the [Packagist page](https://packagist.org/packages/jsor/http-client-hints)
for all available versions.

Example
-------

```php
$resolved = (new Jsor\HttpClientHints\Resolver())
    ->withAllowedHeaders([
        // Process only Width and DPR headers
        'Width',
        'DPR',
    ])
    ->withMapping([
        // Map Width header to w key
        'width'  => 'w',
        // Needed to extract the height from the query params
        // for recalculation depending on Width if present
        'height' => 'h',
    ])
    ->resolve($_SERVER, $_GET)
;

if (isset($resolved['dpr'])) {
    header('Content-DPR: ' . $resolved['dpr']);
    header('Vary: DPR', false);
}

if (isset($resolved['w'])) {
    header('Vary: Width', false);
}

// Use $resolved to generate thumbnails.
// If you use Glide (https://github.com/thephpleague/glide), this could look
// something like:

$server = League\Glide\ServerFactory::create([
    'source' => 'path/to/source/folder',
    'cache' => 'path/to/cache/folder',
]);
$server->outputImage($path, array_merge($_GET, $resolved));
```

License
-------

Copyright (c) 2016-2018 Jan Sorgalla. 
Released under the [MIT License](LICENSE).
