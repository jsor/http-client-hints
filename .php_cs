<?php

$config = PhpCsFixer\Config::create()
    ->setRules(array(
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'long'],
    ));

$config->getFinder()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

$config
    ->setCacheFile(__DIR__ . '/.php_cs.cache');

return $config;
