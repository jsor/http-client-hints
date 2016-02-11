<?php

namespace Jsor\HttpClientHints;

final class Resolver
{
    private $mapping   = array();
    private $whitelist = array(
        'dpr',
        'width',
        'viewport-width',
        'downlink',
        'save-data',
    );

    public function __construct(array $config = array())
    {
        if (isset($config['mapping'])) {
            $this->setMapping($config['mapping']);
        }

        if (isset($config['whitelist'])) {
            $this->setWhitelist($config['whitelist']);
        }
    }

    public function withMapping(array $mapping)
    {
        $instance = clone $this;

        $instance->setMapping($mapping);

        return $instance;
    }

    public function withWhitelist($whitelist)
    {
        $instance = clone $this;

        $instance->setWhitelist($whitelist);

        return $instance;
    }

    public function resolve(array $headers, array $query = array())
    {
        $headers = $this->normalizeHeaders($headers);

        $widthKey   = $this->resolveKey('width');
        $queryWidth = 0;

        if (isset($query[$widthKey]) && is_numeric($query[$widthKey])) {
            $queryWidth = (int) $query[$widthKey];
        }

        $resolved = array();

        foreach ($this->whitelist as $header) {
            if (!isset($headers[$header])) {
                continue;
            }

            $resolved[$this->resolveKey($header)] = $headers[$header];
        }

        if ($queryWidth > 0 && isset($resolved[$widthKey])) {
            $heightKey = $this->resolveKey('height');

            if (isset($query[$heightKey]) && is_numeric($query[$heightKey])) {
                $resolved[$heightKey] = $query[$heightKey] * ($resolved[$widthKey] / $queryWidth);
            }
        }

        return $resolved;
    }

    private function normalizeHeaders(array $headers)
    {
        $normalized = array();

        foreach ($headers as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $key = substr($key, 5);
            }

            $key = str_replace('_', '-', $key);
            $key = strtolower($key);

            if (is_array($value)) {
                $value = reset($value);
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function resolveKey($key)
    {
        if (isset($this->mapping[$key])) {
            $key = $this->mapping[$key];
        }

        return $key;
    }

    private function setMapping(array $mapping)
    {
        $this->mapping = array_map('strtolower', $mapping);
    }

    private function setWhitelist($whitelist)
    {
        if (!is_array($whitelist)) {
            $whitelist = array_map(
                'trim',
                explode(',', (string) $whitelist)
            );
        }

        $this->whitelist = array_map('strtolower', $whitelist);
    }
}
