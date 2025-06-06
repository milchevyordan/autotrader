<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\CacheTag;
use App\Enums\Locale;
use App\Helpers\Translator;
use App\Services\CacheService;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  Request     $request
     * @return null|string
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  Request              $request
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $firstLoadOnlyProps = $request->hasHeader('X-Inertia') || $request->inertia() ? [] : [
            '$translations' => function () {
                return Translator::translations();
            },
        ];

        $user = $request->user() ?? null;
        $userId = $user->id ?? null;

        $roles = Cache::tags(CacheTag::User_roles->value)->remember($userId, now()->addHours(), function () use ($user) {
            return $user?->roles->pluck('name')->unique()->toArray() ?? [];
        });

        $permissions = Cache::tags(CacheTag::User_permissions->value)->remember($userId, now()->addHours(), function () use ($user) {
            $user?->loadMissing('roles.permissions');

            return $user?->roles->flatMap->permissions->pluck('name')->unique()->toArray() ?? [];
        });

        $notificationsCount = Cache::tags(CacheTag::User_notifications->value)->remember($userId, now()->addHours(), function () use ($user) {
            return $user?->unreadNotifications->count() ?? 0;
        });

        $pendingOwnershipsCount = Cache::tags(CacheTag::Pending_ownerships->value)->remember($userId, now()->addHours(), function () use ($user) {
            return $user?->pendingOwnerships->count() ?? 0;
        });

        $userCompanyLogo = Cache::tags(CacheTag::Company_logo->value)->remember($userId, now()->addHours(), function () use ($user) {
            return CompanyService::getCompanyLogoForUser($user);
        });

        $userCompanyVatPercentage = (int) Cache::tags(CacheTag::Vat_percentage->value)->remember($user?->company_id, now()->addHours(), function () use ($user) {
            return $user?->company?->vat_percentage ?? null;
        });

        $cacheService = new CacheService();

        return array_merge(parent::share($request), $firstLoadOnlyProps, [
            // session
            'session' => [
                // Session variables here
            ],
            'auth' => [
                'user' => [
                    'id'                     => $userId,
                    'roles'                  => $roles,
                    'permissions'            => $permissions,
                    'notificationsCount'     => $notificationsCount,
                    'pendingOwnershipsCount' => $pendingOwnershipsCount,
                    'last_name'              => $user?->last_name,
                    'image_path'             => $user?->image_path,
                ],
                'company' => [
                    'logo'             => $userCompanyLogo,
                    'default_currency' => $cacheService->getUserCompanyCurrency($user),
                    'vat_percentage'   => $userCompanyVatPercentage,
                ],
            ],
            'config' => [
                // Set them separately in case we put some sensitive information in config/app.php
                'validation' => [
                    'rule' => [
                        'maxIntegerValue' => config('app.validation.rules.maxIntegerValue'),
                        'maxStringLength' => config('app.validation.rules.maxStringLength'),
                    ],

                    'image' => [
                        'mimeTypes'      => config('app.validation.image.mimeTypes'),
                        'minImageSizeMb' => config('app.validation.image.minImageSizeMb'),
                        'maxImageSizeMb' => config('app.validation.image.maxImageSizeMb'),
                    ],

                    'file' => [
                        'mimeTypes'     => config('app.validation.file.mimeTypes'),
                        'minFileSizeMb' => config('app.validation.file.minFileSizeMb'),
                        'maxFileSizeMb' => config('app.validation.file.maxFileSizeMb'),
                    ],
                ],
                'app_env' => config('app.app_env'),
            ],
            'flash' => [
                // Flash session variables -> !!! Vue plugin doesn't update the values, so we can check them in the components via {{ }} !!!
                'status'  => fn () => session('status'),
                'success' => fn () => session('success'),
                'errors'  => function () {
                    $errorBag = session('errors');

                    if ($errorBag) {
                        return $errorBag->toArray();
                    }

                    return [];
                },
                'error' => fn () => session('error'),
            ],

            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy())->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'lang'   => app()->getLocale(),
            'locale' => Locale::getCaseByName(app()->getLocale()),
        ]);
    }
}
