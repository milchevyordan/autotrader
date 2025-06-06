<?php

declare(strict_types=1);

namespace App\Services\Workflow;

use App\Http\Requests\StoreWorkflowFinishedStepRequest;
use App\Http\Requests\UpdateWorkflowFinishedStepRequest;
use App\Models\Company;
use App\Models\User;
use App\Models\Workflow;
use App\Models\WorkflowFinishedStep;
use App\Notifications\WorkflowEmail;
use App\Services\Files\FileManager;
use App\Services\Files\UploadHelper;
use App\Services\MailService;
use App\Services\Service;
use App\Services\Workflow\Components\Steps\Interfaces\EmailStepInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithDateInputInterface;
use App\Services\Workflow\Components\Steps\Interfaces\StepWithWeekInputInterface;
use App\Support\JsonHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use RuntimeException;

class WorkflowFinishedStepService extends Service
{
    public function createWorkflowStep()
    {
    }

    public function upsertModelWorkflowStep(Model $model, string $stepClass, ?string $additionalValue = null, ?Carbon $finishedDate = null): void
    {
        $workflow = $model->workflow;

        if (! $workflow) {
            return;
        }

        $cast = (new WorkflowFinishedStep())->getCasts()['finished_at'] ?? null;

        if (! $cast) {
            throw new RuntimeException('Invalid datetime format in WorkflowFinishedStep model');
        }

        $data = [
            'workflow_id'      => $workflow->id,
            'class_name'       => $stepClass,
            'additional_value' => $additionalValue,
            'finished_at'      => $finishedDate,
        ];

        $this->upsertWorkflowStep($data, $stepClass);
    }

    /**
     * Handles the upsert operation for a WorkflowFinishedStep.
     *
     * @param  Request|array       $data
     * @param  string              $stepNamespace
     * @return "updated"|"created" Returns a string indicating whether the record was updated or created
     */
    public function upsertWorkflowStep(Request|array $data, string $stepNamespace): string
    {
        $inputData = $data instanceof Request ? $data->all() : $data;

        $validatedData = $this->validateUpsertData($inputData);
        $workflowModel = Workflow::where('id', $validatedData['workflow_id'])->first();
        $step = new $stepNamespace($workflowModel);

        $workflowStepModel = WorkflowFinishedStep::firstOrNew(
            [
                'workflow_id'             => $validatedData['workflow_id'] ?? null,
                'workflow_step_namespace' => $stepNamespace,
            ]
        );

        $workflowStepModel->fill($validatedData);
        $workflowStepModel->creator_id = Auth::id();
        $workflowStepModel->save();

        $workflowStepModel
            ->saveWithFiles(UploadHelper::uploadMultipleFiles($validatedData, 'files'), 'files')
            ->saveWithImages(UploadHelper::uploadMultipleImages($validatedData, 'images'), 'images');

        if ($step instanceof EmailStepInterface) {
            $this->handleEmailSending($inputData, $step, $workflowStepModel, $workflowStepModel->getUploadedFiles());
        }

        return isset($validatedData['id']) ? 'updated' : 'created';
    }

    /**
     * @param  Request                  $request
     * @return null|bool
     * @throws InvalidArgumentException
     */
    public function deleteWorkflowStep(Request $request): ?bool
    {
        $stepNamespace = WorkflowService::getWorkflowCompanyNameSpace(Auth::user()?->company).'\\Steps\\'.$request->class_name;

        $finishedWorkflowStep = WorkflowFinishedStep::where('workflow_id', $request->workflow_id)
            ->where('workflow_step_namespace', $stepNamespace)->first();

        if (! $finishedWorkflowStep) {
            throw new InvalidArgumentException('Could not find step with this name in the company namespace');
        }

        return $finishedWorkflowStep->delete();
    }

    private function validateUpsertData(Request|array $data): array
    {
        $inputData = $data instanceof Request ? $data->all() : $data;

        // Validate the input based on the rules in the request
        $rules = (new UpdateWorkflowFinishedStepRequest())->rules();

        $validatedData = $data instanceof Request
            ? $data->validate($rules)
            : validator($inputData, $rules)->validate();

        // Now it's safe to access 'class_name'
        $stepNamespace = WorkflowService::getWorkflowCompanyNameSpace(Auth::user()?->company).'\\Steps\\'.$validatedData['class_name'];

        $finishedWorkflowStepExists = WorkflowFinishedStep::where('workflow_id', $validatedData['workflow_id'])
            ->where('workflow_step_namespace', $stepNamespace)
            ->exists();

        $rules = $finishedWorkflowStepExists
            ? (new UpdateWorkflowFinishedStepRequest())->rules()
            : (new StoreWorkflowFinishedStepRequest())->rules();

        return $data instanceof Request
            ? $data->validate($rules)
            : validator($inputData, $rules)->validate();
    }

    /**
     * Handle email sending logic.
     *
     * @param array                $validatedData
     * @param ?array               $weeksInputArray
     * @param WorkflowFinishedStep $workflowStep
     * @param ?Collection          $attachments
     */
    private function handleEmailSending(array $inputData, EmailStepInterface $step, WorkflowFinishedStep $workflowStep): void
    {
        $validatedData = $this->validateUpsertData($inputData);

        $recipient = $validatedData['email_recipient'];
        $subject = $validatedData['email_subject'];
        $uploadedFiles = $workflowStep->getUploadedFiles();

        if ($step instanceof StepWithWeekInputInterface) {
            $weeksInputArray = null;
            $weeksInputArray = JsonHelper::convertJsonToArray($validatedData['additional_value']);

            $firstExpectedDate = Carbon::create($weeksInputArray['from'][0])->addDay()->format('d.m.Y');
            $lastExpectedDate = Carbon::create($weeksInputArray['to'][1])->format('d.m.Y');
            $deliveryDatesEmailText = $step->getEmailTemplateText().$firstExpectedDate.'-'.$lastExpectedDate;

            $this->sendEmail($recipient, $subject, $deliveryDatesEmailText);
        } elseif ($step instanceof StepWithDateInputInterface) {
            $additionalValue = $step->getEmailTemplateText().$validatedData['finished_at'];
            $this->sendEmail($recipient, $subject, $additionalValue, $uploadedFiles);

        } else {
            $additionalValue = $validatedData['additional_value'];
            $this->sendEmail($recipient, $subject, $additionalValue, $uploadedFiles);
        }
    }

    private function sendEmail(string $recipient, string $subject, string $text, ?Collection $attachments = null): void
    {
        $recipient = User::where('email', $recipient)->first() ?? Company::where('email', $recipient)->first();
        $workflowEmail = new WorkflowEmail($subject, $text, $attachments);
        Notification::send($recipient, $workflowEmail);

        // TODO: #13 Change this when add email support for multiple attachments
        $attachmentName = null;

        if ($attachments) {
            $firstAttachmentFile = (new FileManager())->getFileNameAndPath($attachments->first()->path);
            $attachmentName = $firstAttachmentFile['fileOriginalName'];
        }

        (new MailService())->saveMailToSystem(
            $workflowEmail->toMail($recipient)->render(),
            collect([$recipient]),
            $recipient,
            $subject,
            $attachmentName
        );
    }
}
