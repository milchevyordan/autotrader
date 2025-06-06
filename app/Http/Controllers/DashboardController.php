<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Dashboard\Boxes\DashboardBoxService;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $user = Auth::user();
        $user->load('company:id,name,country');
        if (! $user->hasRole(RoleService::getMainCompanyRoleNames())) {
            return Inertia::render('Dashboard', compact('user'));
        }

        $dashboardBoxes = DashboardBoxService::getAllBoxes();

        return Inertia::render('Dashboard', compact('user', 'dashboardBoxes'));
    }

    /**
     * Refresh red flags and send notifications about them.
     *
     * @return RedirectResponse
     */
    public function refresh(): RedirectResponse
    {
        $dashboardBoxService = new DashboardBoxService();
        try {

            $dashboardBoxService->recacheAllBoxes();

            return redirect()->back()->with('success', __('Dashboard successfully refreshed.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error refreshing data.')]);
        }
    }
}
