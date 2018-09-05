<?php

namespace Jsor\HttpClientHints;

final class Resolver
{
    private $mapping = [];
    private $allowedHeaders = [
        'dpr',
        'width',
        'viewport-width',
        'downlink',
        'save-data',
    ];

    public function __construct(array $config = [])
    {
        if (isset($config['mapping'])) {
            $this->setMapping($config['mapping']);
        }

        if (isset($config['allowed_headers'])) {
            $this->setAllowedHeaders($config['allowed_headers']);
        }
    }

    public function withMapping(array $mapping)
    {
        $instance = clone $this;

        $instance->setMapping($mapping);

        return $instance;
    }

    public function withAllowedHeaders($allowedHeaders)
    {
        $instance = clone $this;

        $instance->setAllowedHeaders($allowedHeaders);

        return $instance;
    }

    public function resolve(array $headers, array $query = [])
    {
        $headers = $this->normalizeHeaders($headers);

        $widthKey   = $this->resolveKey('width');
        $queryWidth = 0;

        if (isset($query[$widthKey]) && \is_numeric($query[$widthKey])) {
            $queryWidth = (int) $query[$widthKey];
        }

        $resolved = [];

        foreach ($this->allowedHeaders as $header) {
            if (!isset($headers[$header])) {
                continue;
            }

            $resolved[$this->resolveKey($header)] = $headers[$header];
        }

        if ($queryWidth > 0 && isset($resolved[$widthKey])) {
            $heightKey = $this->resolveKey('height');

            if (isset($query[$heightKey]) && \is_numeric($query[$heightKey])) {
                $resolved[$heightKey] = $query[$heightKey] * ($resolved[$widthKey] / $queryWidth);
            }
        }

        return $resolved;
    }

    private function normalizeHeaders(array $headers)
    {
        $normalized = [];

        foreach ($headers as $key => $value) {
            if (0 === \strpos($key, 'HTTP_')) {
                $key = \substr($key, 5);
            }

            $key = \str_replace('_', '-', $key);
            $key = \strtolower($key);

            if (\is_array($value)) {
                $value = \reset($value);
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
        $this->mapping = \array_change_key_case($mapping, \CASE_LOWER);
    }

    private function setAllowedHeaders($allowedHeaders)
    {
        if (!\is_array($allowedHeaders)) {
            $allowedHeaders = \array_map(
                'trim',
                \explode(',', (string) $allowedHeaders)
            );
        }

        $this->allowedHeaders = \array_map('strtolower', $allowedHeaders);
    }
}
