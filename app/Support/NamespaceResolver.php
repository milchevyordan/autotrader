<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class NamespaceResolver
{
    /**
     * Get all class names in a given namespace directory.
     *
     * @param  string     $namespace
     * @param  bool       $excludeAbstract
     * @return Collection
     */
    public static function getClassesInNamespace(string $namespace, bool $excludeAbstract = true): Collection
    {
        $directory = self::getNamespaceDirectory($namespace);
        if (! is_dir($directory)) {
            return new Collection();
        }

        $files = File::allFiles($directory);
        $classNames = new Collection();

        foreach ($files as $file) {
            $class = $namespace.'\\'.basename($file->getRealPath(), '.php');
            $class = Str::replace('/', '\\', $class);

            if (class_exists($class)) {
                $reflection = new ReflectionClass($class);
                if (! $excludeAbstract || ! $reflection->isAbstract()) {
                    $classNames->push($class);
                }
            }
        }

        return $classNames;
    }

    /**
     * Get the directory for a given namespace.
     *
     * @param  string $namespace
     * @return string
     */
    private static function getNamespaceDirectory(string $namespace): string
    {
        return base_path(Str::replaceFirst('App', 'app', $namespace));
    }
}
