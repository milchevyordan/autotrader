<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\QuoteInvitationStatus;
use App\Enums\QuoteStatus;
use App\Exceptions\QuoteAlreadyAcceptedException;
use App\Http\Requests\StoreQuoteInvitationRequest;
use App\Models\File;
use App\Models\Quote;
use App\Models\QuoteInvitation;
use App\Models\User;
use App\Models\UserGroup;
use App\Notifications\PdfEmailNotification;
use App\Services\DataTable\DataTable;
use App\Services\Files\FileManager;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

class QuoteInvitationService extends Service
{
    /**
     * Quote invitation model.
     *
     * @var QuoteInvitation
     */
    private QuoteInvitation $quoteInvitation;

    /**
     * Get the value of quote invitation.
     *
     * @return QuoteInvitation
     */
    public function getQuoteInvitation(): QuoteInvitation
    {
        return $this->quoteInvitation;
    }

    /**
     * Set the value of quoteInvitation.
     *
     * @param  QuoteInvitation $quoteInvitation
     * @return self
     */
    public function setQuoteInvitation(QuoteInvitation $quoteInvitation): self
    {
        $this->quoteInvitation = $quoteInvitation;

        return $this;
    }

    /**
     * Collection of DataTable<QuoteInvitation>.
     *
     * @return DataTable<QuoteInvitation>
     */
    public function getIndexMethodTable(): DataTable
    {
        return (new DataTable(
            QuoteInvitation::forRole(auth()->user()->roles[0]->name)->select(QuoteInvitation::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setRelation('customer', ['id', 'full_name'])
            ->setRelation('quote', ['id', 'total_quote_price_exclude_vat', 'total_quote_price'])
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('customer.full_name', __('Invited'), true)
            ->setColumn('quote.total_quote_price_exclude_vat', __('Total quote price exclude VAT'), true)
            ->setColumn('quote.total_quote_price', __('Total Quote Price'), true)
            ->setTimestamps()
            ->setEnumColumn('status', QuoteInvitationStatus::class);
    }

    /**
     * Creates the quote invitation.
     *
     * @param StoreQuoteInvitationRequest $request
     * @return self
     * @throws Exception
     */
    public function createQuoteInvitation(StoreQuoteInvitationRequest $request): self
    {
        $validatedRequest = $request->validated();

        $quoteService = new QuoteService();
        $allCustomerIds = $this->getCustomerIdsByRequest($validatedRequest);
        $invitationData = $this->getInvitationData($allCustomerIds, $validatedRequest['quote_id']);
        $quote = Quote::findOrFail($validatedRequest['quote_id']);
        $vehiclesCount = $quote->vehicles->count();

        QuoteInvitation::insert($invitationData);

        $quote->load([
            'customer:id,company_id,full_name',
            'customer.company:id,name',
            'customer.company' => function ($query) {
                $query->select('id', 'name')->withTrashed();
            },
            'vehicles' => function ($query) use ($vehiclesCount) {
                $query->withTrashed()->withPivot('delivery_week')
                    ->with(['images' => function ($imagesQuery) use ($vehiclesCount) {
                        $imagesQuery->where('section', 'externalImages');

                        return $vehiclesCount < 2 ? $imagesQuery : $imagesQuery->take(2);
                    },
                    ]);
            },
            'vehicles.calculation',
            'vehicles.engine:id,name',
            'vehicles.vehicleModel:id,name',
            'vehicles.make:id,name',
            'vehicles.variant:id,name',
            'vehicles.creator',
            'serviceLevel:id,name',
            'orderItems',
            'orderServices',
        ]);

        $quoteService->setQuote($quote)->updateStatus(QuoteStatus::Sent->value);

        $localeService = (new LocaleService())->checkChangeToLocale($validatedRequest['locale']);

        $customerName = count($allCustomerIds) == 1 ? User::find($allCustomerIds[0])->full_name : null;
        $generatedPdf = $quoteService->generatePdf($vehiclesCount, $customerName);
        $localeService->setOriginalLocale();

        $this->sendPdf($generatedPdf, $quote, $allCustomerIds);

        return $this;
    }

    /**
     * Generate quote invitation pdf, save it to system and send it via mail.
     *
     * @param  File  $generatedPdf
     * @param  Quote $quote
     * @param  array $allCustomerIds
     * @return void
     */
    private function sendPdf(File $generatedPdf, Quote $quote, array $allCustomerIds): void
    {
        $subject = 'Quote Invitation';
        $path = (new FileManager())->getLocalFilePath($generatedPdf['path']);

        $quoteInvitationNotification = new PdfEmailNotification(
            $subject,
            $generatedPdf['original_name'],
            $path,
            $quote->email_text,
            [
                'name'  => __('View quote invitation'),
                'route' => route('quote-invitations.invitation', $quote->id),
            ]
        );

        $users = User::whereIn('id', $allCustomerIds)->get();
        Notification::send($users, $quoteInvitationNotification);

        (new MailService())->saveMailToSystem(
            $quoteInvitationNotification->toMail($users[0])->render(),
            $users,
            $quote,
            $subject,
            $generatedPdf['unique_name']
        );
    }

    /**
     * Change the status of the quote invitation.
     *
     * @param  QuoteInvitationStatus $status
     * @return self
     */
    public function changeStatus(QuoteInvitationStatus $status): self
    {
        $quoteInvitation = $this->getQuoteInvitation();
        $quoteInvitation->status = $status;
        $quoteInvitation->save();

        $this->setQuoteInvitation($quoteInvitation);

        return $this;
    }

    /**
     * Accept the quote invitation.
     *
     * @return void
     * @throws QuoteAlreadyAcceptedException
     */
    public function accept(): void
    {
        $quoteInvitation = $this->getQuoteInvitation();
        $quote = $quoteInvitation->quote;

        if (QuoteService::hasAcceptedInvitation($quote)) {
            throw new QuoteAlreadyAcceptedException('This quote is already accepted');
        }

        self::closeQuoteInvitations($quote);

        $this->changeStatus(QuoteInvitationStatus::Accepted);
        (new QuoteService())->setQuote($quote)->updateStatus(QuoteStatus::Accepted_by_client->value);
    }

    /**
     * Reject the quote invitation.
     *
     * @return void
     */
    public function reject(): void
    {
        $this->changeStatus(QuoteInvitationStatus::Rejected);
    }

    /**
     * Close all quote invitations when invitation is accepted.
     *
     * @param  Quote $quote
     * @return void
     */
    public static function closeQuoteInvitations(Quote $quote): void
    {
        QuoteInvitation::where('quote_id', $quote->id)
            ->where('status', QuoteInvitationStatus::Concept->value)
            ->update(['status' => QuoteInvitationStatus::Closed]);
    }

    /**
     * Collection of DataTable<QuoteInvitation>.
     *
     * @param  Builder                    $builder
     * @return DataTable<QuoteInvitation>
     */
    public static function getQuoteInvitationsDataTableByBuilder(Builder $builder): DataTable
    {
        return (new DataTable(
            $builder->select(QuoteInvitation::$defaultSelectFields)
        ))
            ->setRelation('creator')
            ->setRelation('customer', ['id', 'full_name'])
            ->setColumn('action', __('Action'), exportable: false)
            ->setColumn('id', '#', true, true)
            ->setColumn('status', __('Status'), true, true)
            ->setColumn('creator.full_name', __('Creator'), true)
            ->setColumn('customer.full_name', __('Invited'), true)
            ->setTimestamps()
            ->setEnumColumn('status', QuoteInvitationStatus::class);
    }

    /**
     * Plucks the customer ids from the request.
     *
     * @param array $validatedRequest
     * @return array
     */
    private function getCustomerIdsByRequest(array $validatedRequest): array
    {
        $allCustomerIds = [];
        if (isset($validatedRequest['user_group_id'])) {
            $allCustomerIds = UserGroup::with('users:id')->where('id', $validatedRequest['user_group_id'])
                ->first()->users->pluck('id')->toArray();
        }

        if (isset($validatedRequest['customer_ids'])) {
            $allCustomerIds = array_merge($allCustomerIds, $validatedRequest['customer_ids']);
        }

        return array_unique($allCustomerIds);
    }

    /**
     * Get the invitation data.
     *
     * @param  array $allCustomerIds
     * @param  int   $quoteId
     * @return array
     */
    private function getInvitationData(array $allCustomerIds, int $quoteId): array
    {
        $invitationData = [];
        foreach ($allCustomerIds as $customerId) {
            $invitationData[] = [
                'quote_id'    => $quoteId,
                'customer_id' => $customerId,
                'creator_id'  => auth()->id(),
            ];
        }

        return $invitationData;
    }
}
