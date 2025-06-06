<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmailTemplateController extends Controller
{
    /**
     * Handle policy authorization.
     */
    public function __construct()
    {
        $this->authorizeResource(EmailTemplate::class);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  EmailTemplate $emailTemplate
     * @return Response
     */
    public function edit(EmailTemplate $emailTemplate): Response
    {
        return Inertia::render('email-templates/Edit', compact('emailTemplate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateEmailTemplateRequest $request
     * @param  EmailTemplate              $emailTemplate
     * @return RedirectResponse
     */
    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $emailTemplate): RedirectResponse
    {
        $emailTemplate->fill($request->validated());
        $emailTemplate->creator_id = auth()->id();
        if ($emailTemplate->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }
}
