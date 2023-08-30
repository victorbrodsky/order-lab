<?php

// src/Asset/VersionStrategy/GulpBusterVersionStrategy.php
namespace App\Asset\VersionStrategy;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

class GulpBusterVersionStrategy implements VersionStrategyInterface
{

    private $manifestPath = "busters.json";
    private $format = "%%s?version=%%s";

    /**
     * @var string[]
     */
    private $hashes;

    #public function __construct(
        //$manifestPath,
        //$format = null
    #) {
    #    $this->format = $this->format ?: '%s?%s';
    #}

    public function getVersion(string $path): string
    {
        if (!is_array($this->hashes)) {
            $this->hashes = $this->loadManifest();
        }

        return $this->hashes[$path] ?? '';
    }

    public function applyVersion(string $path): string
    {
        $version = $this->getVersion($path);

        if ('' === $version) {
            return $path;
        }

        return sprintf($this->format, $path, $version);
    }

    private function loadManifest(): array
    {
        return json_decode(file_get_contents($this->manifestPath), true);
    }
}
