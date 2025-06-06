<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternalRemarkReplyRequest;
use App\Models\InternalRemarkReply;
use Illuminate\Http\RedirectResponse;

class InternalRemarkReplyController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreInternalRemarkReplyRequest $request
     * @return RedirectResponse
     */
    public function store(StoreInternalRemarkReplyRequest $request): RedirectResponse
    {
        $internalRemarkReply = new InternalRemarkReply();
        $internalRemarkReply->fill($request->validated());
        $internalRemarkReply->creator_id = auth()->id();

        if ($internalRemarkReply->save()) {
            return redirect()->back()->with('success', __('The record has been successfully created.'));
        }

        return redirect()->back()->withErrors([__('Error creating record.')]);
    }
}
