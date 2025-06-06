<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Models\Document;
use App\Services\CompanyService;
use App\Services\DocumentService;
use App\Services\MultiSelectService;
use App\Services\OwnershipService;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\Workflow\ProcessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DocumentController extends Controller
{
    public DocumentService $service;

    /**
     * Handle policy authorization and service initialization.
     */
    public function __construct()
    {
        $this->authorizeResource(Document::class);
        $this->service = new DocumentService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $dataTable = $this->service->getIndexMethodDataTable()->run();

        return Inertia::render('documents/Index', compact('dataTable'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('documents/Create', [
            'companies'         => fn () => CompanyService::getCrmCompanies(),
            'customers'         => fn () => UserService::getCustomers(),
            'dataTable'         => fn () => $this->service->getDocumentableDataTable(),
            'tableVehicles'     => fn () => $this->service->getTableVehicles(),
            'queryId'           => fn () => $this->service->getQueryId(),
            'workflowProcesses' => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
            'mainCompanyRoles'  => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
            'mainCompanyUsers'  => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreDocumentRequest $request
     * @return RedirectResponse
     */
    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service->createDocument($request);

            DB::commit();

            return redirect()->route('documents.edit', ['document' => $this->service->getDocument()->id])->with('success', __('The record has been successfully created.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error creating record.')]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Document $document
     * @return Response
     */
    public function edit(Document $document): Response
    {
        $document = $this->service->setDocument($document)->loadRelations();

        return Inertia::render('documents/Edit', [
            'document'                => fn () => $document,
            'companies'               => fn () => CompanyService::getCrmCompanies(),
            'mainCompanyUsers'        => fn () => (new MultiSelectService(RoleService::getCompanyMainRolesUsers()))->setTextColumnName('full_name')->dataForSelect(),
            'pendingOwnerships'       => fn () => OwnershipService::getPending($document),
            'acceptedOwnership'       => fn () => OwnershipService::getAccepted($document),
            'customers'               => fn () => UserService::getCustomers($document->customer_company_id),
            'dataTable'               => fn () => $this->service->getDocumentableDataTable(),
            'tableVehicles'           => fn () => $this->service->getTableVehicles(),
            'selectedDocumentables'   => fn () => $this->service->getSelectedDocumentables(),
            'selectedDocumentableIds' => fn () => $this->service->getSelectedDocumentables()->pluck('id'),
            'workflowProcesses'       => Inertia::lazy(fn () => MultiSelectService::dataForSelectFromArray(ProcessService::getCompanyProcesses()->toArray(), 'className')),
            'mainCompanyRoles'        => Inertia::lazy(fn () => (new MultiSelectService(RoleService::getMainCompanyRoles()))->dataForSelect()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Document              $document
     * @param  UpdateDocumentRequest $request
     * @return RedirectResponse
     */
    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->service
                ->setDocument($document)
                ->updateDocument($request);

            DB::commit();

            return redirect()->route('documents.edit', ['document' => $document->id])->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollBack();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Document         $document
     * @return RedirectResponse
     */
    public function destroy(Document $document): RedirectResponse
    {
        try {
            $document->delete();

            return redirect()->back()->with('success', __('The record has been successfully deleted.'));
        } catch (Throwable $th) {
            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error deleting record.')]);
        }
    }

    /**
     * Updates the status of the Document.
     *
     * @param  UpdateStatusRequest $request
     * @return RedirectResponse
     */
    public function updateStatus(UpdateStatusRequest $request): RedirectResponse
    {
        try {
            $validatedRequest = $request->validated();
            $document = Document::findOrFail($validatedRequest['id']);
            $this->authorize('updateStatus', [$document, $validatedRequest['status']]);

            $this->service
                ->setDocument($document)
                ->updateDocumentStatus($request);

            return redirect()->back()->with('success', __('The record has been successfully updated.'));
        } catch (Throwable $th) {
            DB::rollback();

            Log::error($th->getMessage(), ['exception' => $th]);

            return redirect()->back()->withErrors([__('Error updating record.')]);
        }
    }
}
