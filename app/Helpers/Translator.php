<?php

declare(strict_types=1);

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class Translator
{
    /**
     * The default dir of the language files.
     *
     * @var string
     */
    private string $localesDir = 'resources/lang';

    private string $locale = 'en';

    private string $phpLangLocalePath = '';

    /**
     * @var array<string>
     */
    private array $allLocales = [];

    /**
     * Create a new Translator instance.
     *
     * @param $locale
     */
    public function __construct($locale = null)
    {
        $localesDir = $this->getLocalesDir();
        $this->setLocale($locale);
        $this->setPhpLangLocalePath("{$localesDir}/{$this->locale}");
    }

    /**
     * Returns an array of all existing translations for the given locale.
     *
     * @param  null|string $locale
     * @return array
     */
    public static function translations(?string $locale = null): array
    {
        $instance = new self($locale);
        $locale = $instance->getLocale() ?: app()->getLocale();

        return Cache::rememberForever("translations_{$locale}", function () use ($locale) {
            $jsonTranslations = [];
            $jsonLangLocalePath = resource_path("lang/{$locale}.json");

            if (File::exists($jsonLangLocalePath)) {
                $jsonTranslations = json_decode(File::get($jsonLangLocalePath), true);
            }

            return $jsonTranslations;
        });
    }

    /**
     * Creates / edits a json file with all missing translations from English to the appropriate locale.
     *
     * @param  null|string $locale
     * @return array
     */
    public static function translate(?string $locale = null): array
    {
        Cache::forget("translations_{$locale}");
        $basePath = base_path();
        $oldTranslations = self::translations($locale);
        $jsonLangLocalePath = resource_path("lang/{$locale}.json");

        // Load existing translations from the JSON file (if it exists)
        $existingTranslations = [];
        if (File::exists($jsonLangLocalePath)) {
            $existingTranslations = json_decode(File::get($jsonLangLocalePath), true) ?? [];
        }

        Cache::forget("translations_{$locale}");
        exec("cd {$basePath} && php artisan langscanner {$locale} 2>&1");
        $newTranslations = array_merge(self::translations('en'), self::translations($locale));

        // Preserve old translations if they exist
        $mergedTranslations = array_merge($existingTranslations, $newTranslations);

        if ($locale === 'en') {
            $mergedTranslationsWithValues = [];
            foreach ($mergedTranslations as $key => $translation) {
                $mergedTranslationsWithValues[$key] = $key;
            }
        }

        $finalTranslations = array_merge(
            $mergedTranslationsWithValues ?? $mergedTranslations,
            $oldTranslations
        );

        ksort($finalTranslations, \SORT_NATURAL | \SORT_FLAG_CASE);

        File::replace(
            resource_path("lang/{$locale}.json"),
            json_encode($finalTranslations, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES)
        );

        return $finalTranslations;
    }

    /**
     * Clear cache of all translations.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $instance = new self();

        Cache::forget('translations_nl');
        Cache::forget('translations_en');

        foreach ($instance->getAllLocales() as $lang) {
            Cache::forget("translations_{$lang}");
        }
    }

    /**
     * Get the PHP language locale path.
     *
     * @return string
     */
    public function getPhpLangLocalePath(): string
    {
        return $this->phpLangLocalePath;
    }

    /**
     * Get the PHP language locale path.
     *
     * @param  mixed $path
     * @return self
     */
    public function setPhpLangLocalePath($path): self
    {
        $this->phpLangLocalePath = base_path($path);

        return $this;
    }

    /**
     * Get the PHP language locale.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get the PHP language locale.
     *
     * @param  mixed $locale
     * @return self
     */
    public function setLocale($locale): self
    {
        $this->locale = $locale ?? app()->getLocale();

        return $this;
    }

    /**
     * Get the value of locales dir.
     */
    public function getLocalesDir()
    {
        return $this->localesDir;
    }

    /**
     * Set the value of defaultLocalesDir.
     *
     * @param  mixed $localesDir
     * @return self
     */
    public function setLocalesDir(mixed $localesDir): self
    {
        $this->localesDir = resource_path($localesDir);

        return $this;
    }

    /**
     * Get the value of languages.
     */
    public function getAllLocales()
    {
        $localesDir = $this->getLocalesDir();

        $directories = File::directories($localesDir);

        foreach ($directories as $dir) {
            $lang = basename($dir);
            $this->addLocale($lang);
        }

        return $this->allLocales;
    }

    /**
     * Set the value of languages.
     *
     * @param  string $language
     * @return self
     */
    public function addLocale(string $locale): self
    {
        $this->allLocales[] = $locale;

        return $this;
    }
}
