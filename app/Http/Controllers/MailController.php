<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Mail;
use App\Services\MailService;
use Inertia\Inertia;
use Inertia\Response;

class MailController extends Controller
{
    private MailService $service;

    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->service = new MailService();
        $this->authorizeResource(Mail::class);
    }

    /**
     * Show mails sent or received via the system.
     *
     * @return Response
     */
    public function index(): Response
    {
        return Inertia::render('mails/Index', [
            'inBox'  => fn () => $this->service->getInBoxMails(),
            'outBox' => fn () => $this->service->getOutBoxMails(),
        ]);
    }
}
