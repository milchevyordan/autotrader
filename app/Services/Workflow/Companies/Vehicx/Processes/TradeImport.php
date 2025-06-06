<?php

declare(strict_types=1);

namespace App\Services\Workflow\Companies\Vehicx\Processes;

use App\Models\Workflow as ModelsWorkflow;
use App\Services\Workflow\Companies\Vehicx\Subprocesses\CompletingFile;
use App\Services\Workflow\Companies\Vehicx\Subprocesses\DocumentsInbound;
use App\Services\Workflow\Companies\Vehicx\Subprocesses\Import;
use App\Services\Workflow\Companies\Vehicx\Subprocesses\Invoicing;
use App\Services\Workflow\Companies\Vehicx\Subprocesses\ReadyForPickup;
use App\Services\Workflow\Companies\Vehicx\Subprocesses\TransportInbound;
use App\Services\Workflow\Companies\Vehicx\Subprocesses\TransportOutbound;
use App\Services\Workflow\Companies\Vehicx\Subprocesses\WorkOrder;
use App\Services\Workflow\Components\Processes\Process;
use App\Services\Workflow\Subprocesses\BaseSubprocess;
use Illuminate\Support\Collection;

class TradeImport extends Process
{
    public const NAME = 'Trade - Import';

    private const SUBPROCESS_CLASSES = [
        ReadyForPickup::class,
        TransportInbound::class,
        DocumentsInbound::class,
        Import::class,
        WorkOrder::class,
        Invoicing::class,
        TransportOutbound::class,
        CompletingFile::class,
    ];

    public function __construct(ModelsWorkflow $modelsWorkflow)
    {
        parent::__construct($modelsWorkflow);
    }

    public function getName(): string
    {
        return __(self::NAME);
    }

    /**
     * @return Collection<BaseSubprocess>
     */
    protected function getSubprocessClasses(): Collection
    {
        return new Collection(self::SUBPROCESS_CLASSES);
    }
}
