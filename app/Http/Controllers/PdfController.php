<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Notifications\PdfEmailNotification;
use App\Services\Files\FileManager;
use App\Services\MailService;
use App\Support\ModelHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use ReflectionException;

class PdfController extends Controller
{
    /**
     * Send mail based on the request provided.
     *
     * @param  Request             $request
     * @return void
     * @throws ReflectionException
     */
    public function sendMail(Request $request): void
    {
        $validatedRequest = $request->validate([
            'resource'               => 'required|array',
            'resource.mailable_type' => 'required|string',
            'resource.mailable_id'   => 'required|integer',
            'receiver'               => 'required|string',
            'file'                   => 'required|string',
            'options'                => 'nullable|array',
        ]);

        $fileData = (new FileManager())->getFileNameAndPath($validatedRequest['file']);

        $model = $validatedRequest['resource']['mailable_type']::find($validatedRequest['resource']['mailable_id']);
        if (! $model) {
            return;
        }

        $resourceNameWithId = ModelHelper::getModelNameWithId($model);

        $pdfMail = new PdfEmailNotification(
            $resourceNameWithId,
            $fileData['fileOriginalName'],
            $fileData['filePath'],
            null,
            [
                'route' => ModelHelper::getEditRoute($model),
                'name'  => 'View '.$resourceNameWithId,
            ]
        );

        $receivers = User::where('email', $request['receiver'])->get()
            ->whenEmpty(fn () => Company::where('email', $request['receiver'])->get());

        Notification::send($receivers, $pdfMail);

        (new MailService())->saveMailToSystem(
            $pdfMail->toMail($receivers->first())->render(),
            $receivers,
            $model,
            $resourceNameWithId,
            $validatedRequest['file']
        );
    }
}
