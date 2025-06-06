<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Locale;

class LocaleService extends Service
{
    /**
     * The locale service is started with.
     *
     * @var string
     */
    private string $originalLocale;

    public function __construct()
    {
        $this->originalLocale = app()->getLocale();
    }

    /**
     * Check if the locale in the request is different from the original.
     *
     * @param int $requestLocale
     * @param bool $storeInSession
     * @return $this
     */
    public function checkChangeToLocale(int $requestLocale, bool $storeInSession = false): static
    {
        $requestLocaleString = Locale::getCaseByValue($requestLocale)?->name ?? app()->getLocale();
        if ($requestLocaleString == $this->originalLocale) {
            return $this;
        }

        app()->setLocale($requestLocaleString);

        if (! $storeInSession) {
            return $this;
        }

        session()->put('locale', $requestLocaleString);

        return $this;
    }

    /**
     * Set app locale to original.
     *
     * @return $this
     */
    public function setOriginalLocale(): static
    {
        if (app()->getLocale() != $this->originalLocale) {
            app()->setLocale($this->originalLocale);
        }

        return $this;
    }
}
