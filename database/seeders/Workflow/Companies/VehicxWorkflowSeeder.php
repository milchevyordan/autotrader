<?php

declare(strict_types=1);

namespace Database\Seeders\Workflow\Companies;

use Database\Seeders\Workflow\Base;

class VehicxWorkflowSeeder extends Base
{
    /**
     * Company's place when seeding this was first so id 1.
     *
     * @var int
     */
    private int $companyId = 1;

    /**
     * Main process flows.
     *
     * @var array|array[]
     */
    public array $processes = [
        [
            'name'              => 'Trade - Import',
            'variable_map_name' => 'isImportFlow',
        ],

        [
            'name'              => 'Trade - Domestic',
            'variable_map_name' => 'isDomesticFlow',
        ],

        [
            'name'              => 'Trade - Export',
            'variable_map_name' => 'isExportFlow',
        ],

        [
            'name'              => 'Trade - Transit',
            'variable_map_name' => 'isTransitFlow',
        ],

        [
            'name'              => 'Trade - Pre-order',
            'variable_map_name' => 'isPreOrderFlow',
        ],

        [
            'name'              => 'Service - Import',
            'variable_map_name' => 'isServiceImportFlow',
        ],

        [
            'name'              => 'Service - Export',
            'variable_map_name' => 'isServiceExportFlow',
        ],
    ];

    /**
     * Subprocesses in detail.
     *
     * @var array|array[]
     */
    public array $subprocesses = [
        [
            'name'                => 'Ready for pick up',
            'icon_component_name' => 'CalendarCheck',

            'statuses' => [
                [
                    'name' => 'Latest Expected date Ready',

                    'steps' => [
                        [
                            'name'                       => 'Expected date Ready when PO issued',
                            'variable_map_name'          => 'hasExpectedDateReadyWhenPurchaseOrderIssued',
                            'additional_modal_component' => null,
                        ],

                        [
                            'name'                       => 'Latest + Updated Expected date Ready',
                            'variable_map_name'          => 'latestAndUpdatedExpectedDateReady',
                            'additional_modal_component' => 'LatestAndUpdatedExpectedDateReady',
                        ],
                    ],
                ],

                [
                    'name' => 'Definitive Date Ready',

                    'steps' => [
                        [
                            'name'                       => 'Pick Up Location/ Address',
                            'variable_map_name'          => 'hasDefinedPickupLocation',
                            'additional_modal_component' => null,
                        ],

                        [
                            'name'                       => 'Send Email to Supplier',
                            'variable_map_name'          => 'hasDefinedPickupDate',
                            'additional_modal_component' => null,
                        ],

                        [
                            'name'                       => 'Agreed date Ready with Supplier',
                            'variable_map_name'          => 'hasAgreedDateReadyWithSupplier',
                            'additional_modal_component' => null,
                        ],
                    ],
                ],

                [
                    'name' => 'Payment Supplier',

                    'steps' => [
                        [
                            'name'                       => '(Proforma) Invoice',
                            'variable_map_name'          => 'hasDefinedPickupLocation',
                            'additional_modal_component' => null,
                        ],

                        [
                            'name'                       => 'Vehicle Payment in Bank',
                            'variable_map_name'          => 'hasVehiclePaymentInBank',
                            'additional_modal_component' => null,
                        ],

                        [
                            'name'                       => 'Vehicle Payment Completed',
                            'variable_map_name'          => 'hasPaymentCompleted',
                            'additional_modal_component' => null,
                        ],

                        [
                            'name'                       => 'Proof of Payment Send to supplier',
                            'variable_map_name'          => 'hasProofOfPaymentSentToSupplier',
                            'additional_modal_component' => null,
                        ],
                    ],
                ],
            ],
        ],
        [
            'name'                => 'Payment',
            'icon_component_name' => 'Payment',

            'statuses' => [
                [
                    'name' => 'Waiting for payment instructions',

                    'steps' => [
                        [
                            'name'                       => 'Received ProForm',
                            'variable_map_name'          => 'hasReceivedProForm',
                            'additional_modal_component' => 'ReceivedProForm',
                        ],
                    ],
                ],
                [
                    'name' => 'Ready for payment',

                    'steps' => [
                        [
                            'name'                       => 'Prepared Bank Payment',
                            'variable_map_name'          => 'hasPreparedBankPayment',
                            'additional_modal_component' => 'PreparedBankPayment',
                        ],
                    ],
                ],
                [
                    'name' => 'Paid',

                    'steps' => [
                        [
                            'name'                       => 'Sent Payment Confirmation',
                            'variable_map_name'          => 'hasSentPaymentConfirmation',
                            'additional_modal_component' => 'SentPaymentConfirmation',
                        ],
                    ],
                ],
            ],
        ],
        [
            'name'                => 'Transport Inbound',
            'icon_component_name' => 'TruckWithArrow',

            'statuses' => [
                [
                    'name' => 'Plan transport',

                    'steps' => [
                        [
                            'name'                       => 'Transport Order Inbound',
                            'variable_map_name'          => 'hasTransportOrderInbound',
                            'additional_modal_component' => null,
                        ],
                        [
                            'name'                       => 'Sent Pick up Authorization',
                            'variable_map_name'          => 'hasSentPickupAuth',
                            'additional_modal_component' => 'SentPickupAuth',
                        ],
                        [
                            'name'                       => 'Sent Transport And Authorization',
                            'variable_map_name'          => 'hasSentTransportAndAuth',
                            'additional_modal_component' => 'SentTransportAndAuth',
                        ],
                        [
                            'name'                       => 'Transport Company Confirmation',
                            'variable_map_name'          => 'hasTransportCompanyConfirmation',
                            'additional_modal_component' => 'TransportCompanyConfirmation',
                        ],
                        [
                            'name'                       => 'Estimated Delivery Date',
                            'variable_map_name'          => 'hasEstimatedDeliveryDate',
                            'additional_modal_component' => 'EstimatedDeliveryDate',
                        ],
                    ],
                ],
                [
                    'name' => 'Waiting for delivery',

                    'steps' => [
                        [
                            'name'                       => 'Informed Supplier For PickupDate',
                            'variable_map_name'          => 'hasInformedSupplierForPickupDate',
                            'additional_modal_component' => 'InformedSupplierForPickupDate',
                        ],
                    ],
                ],
                [
                    'name' => 'Picked up',

                    'steps' => [
                        [
                            'name'                       => 'Notified By Supplier For PickupDate',
                            'variable_map_name'          => 'hasBeenNotifiedBySupplierForPickupDate',
                            'additional_modal_component' => 'NotifiedBySupplierForPickupDate',
                        ],
                    ],
                ],
                [
                    'name' => 'Received',

                    'steps' => [
                        [
                            'name'                       => 'Received Vehicle',
                            'variable_map_name'          => 'hasReceivedVehicle',
                            'additional_modal_component' => null,
                        ],
                        [
                            'name'                       => 'Uploaded Vehicle Intake Form',
                            'variable_map_name'          => 'hasUploadedVehicleIntakeForm',
                            'additional_modal_component' => 'UploadVehicleIntakeForm',
                        ],
                        [
                            'name'                       => 'Sent Cmr',
                            'variable_map_name'          => 'hasSentCmr',
                            'additional_modal_component' => 'SentCmr',
                        ],
                    ],
                ],
            ],
        ],
        [
            'name'                => 'Documents Inbound',
            'icon_component_name' => 'Mail',

            'statuses' => [
                [
                    'name' => 'Waiting for documents',

                    'steps' => [
                        [
                            'name'                       => 'Received Foreign Vehicle Documents',
                            'variable_map_name'          => 'hasReceivedForeignVehicleDocuments',
                            'additional_modal_component' => 'ReceivedForeignVehicleDocuments',
                        ],
                        [
                            'name'                       => 'Received Nl Vehicle Documents',
                            'variable_map_name'          => 'hasReceivedNlVehicleDocuments',
                            'additional_modal_component' => 'ReceivedNlVehicleDocuments',
                        ],
                    ],
                ],
                [
                    'name' => 'Documents Received',

                    'steps' => [
                        [
                            'name'                       => 'Received Original Documents',
                            'variable_map_name'          => 'hasReceivedOriginalDocuments',
                            'additional_modal_component' => 'ReceivedOriginalDocuments',
                        ],
                    ],
                ],
            ],
        ],
        [
            'name'                => 'Import',
            'icon_component_name' => 'ImportMarketing',

            'statuses' => [
                [
                    'name' => 'Execute RDW Identification',

                    'steps' => [
                        [
                            'name'                       => 'Rdw Identification',
                            'variable_map_name'          => 'hasRdwIdentification',
                            'additional_modal_component' => 'RdwIdentification',
                        ],
                    ],
                ],
                [
                    'name' => 'RDW',

                    'steps' => [
                        [
                            'name'                       => 'RdwApproval',
                            'variable_map_name'          => 'hasRdwApproval',
                            'additional_modal_component' => 'RdwApproval',
                        ],
                    ],
                ],
                [
                    'name' => 'Sample Inspection',

                    'steps' => [
                        [
                            'name'                       => 'Sample Inspection Approval',
                            'variable_map_name'          => 'hasSampleInspectionApproval',
                            'additional_modal_component' => 'SampleInspectionApproval',
                        ],
                        [
                            'name'                       => 'Planned Sample Inspection',
                            'variable_map_name'          => 'hasPlannedSampleInspection',
                            'additional_modal_component' => 'PlannedSampleInspection',
                        ],
                        [
                            'name'                       => 'Passed Sample Inspection',
                            'variable_map_name'          => 'hasPassedSampleInspection',
                            'additional_modal_component' => 'PassedSampleInspection',
                        ],
                    ],
                ],
                [
                    'name' => 'BPM',

                    'steps' => [
                        [
                            'name'                       => 'Chosen Valuation Or Bpm',
                            'variable_map_name'          => 'hasChosenValuationOrBpm',
                            'additional_modal_component' => 'ChooseValuationOrBpm',
                        ],
                        [
                            'name'                       => 'Uploaded Bpm Declaration',
                            'variable_map_name'          => 'hasUploadedBpmDeclaration',
                            'additional_modal_component' => 'UploadBpmDeclaration',
                        ],
                    ],
                ],
                [
                    'name' => 'Execute Valuation',

                    'steps' => [
                        [
                            'name'                       => 'Planned Valuation',
                            'variable_map_name'          => 'hasPlannedValuation',
                            'additional_modal_component' => 'PlannedValuation',
                        ],
                        [
                            'name'                       => 'Executed Valuation',
                            'variable_map_name'          => 'hasExecutedValuation',
                            'additional_modal_component' => 'ExecutedValuation',
                        ],
                    ],
                ],
                [
                    'name' => 'NL Registration',

                    'steps' => [
                        [
                            'name'                       => 'Uploaded Bpm Approval',
                            'variable_map_name'          => 'hasUploadedBpmApprovalEmail',
                            'additional_modal_component' => 'UploadedBpmApprovalEmail',
                        ],
                        [
                            'name'                       => 'Uploaded Nl Registration',
                            'variable_map_name'          => 'hasUploadedNlRegistration',
                            'additional_modal_component' => null,
                        ],
                        [
                            'name'                       => 'Filled Registration Number And Codes',
                            'variable_map_name'          => 'hasFilledRegistrationNumberAndCodes',
                            'additional_modal_component' => null,
                        ],
                    ],
                ],
            ],
        ],

        [
            'name'                => 'Work Order',
            'icon_component_name' => 'CheckList',

            'statuses' => [
                [
                    'name' => 'Added tasks',

                    'steps' => [
                        [
                            'name'                       => 'Added Work Order Tasks',
                            'variable_map_name'          => 'hasAddedWorkOrderTasks',
                            'additional_modal_component' => null,
                        ],
                    ],
                ],
                [
                    'name' => 'Completed',

                    'steps' => [
                        [
                            'name'                       => 'Marked Completed Work Order Tasks',
                            'variable_map_name'          => 'hasMarkedCompletedWorkOrderTasks',
                            'additional_modal_component' => null,
                        ],
                        [
                            'name'                       => 'Marked Completed Work Order',
                            'variable_map_name'          => 'hasMarkedCompletedWorkOrder',
                            'additional_modal_component' => null,
                        ],
                    ],
                ],
            ],
        ],

        [
            'name'                => 'Completing',
            'icon_component_name' => 'CheckListIncompleted',

            'statuses' => [
                [
                    'name' => 'Completed',

                    'steps' => [
                        [
                            'name'                       => 'Vat Deposit Document',
                            'variable_map_name'          => 'hasVatDepositDocument',
                            'additional_modal_component' => 'VatDepositDocument',
                        ],
                        [
                            'name'                       => 'Received Vat Deposit',
                            'variable_map_name'          => 'hasReceivedVatDeposit',
                            'additional_modal_component' => 'ReceivedVatDeposit',
                        ],
                        [
                            'name'                       => 'Process Bpm License',
                            'variable_map_name'          => 'hasProcessBpmLicense',
                            'additional_modal_component' => 'ProcessBpmLicense',
                        ],
                        [
                            'name'                       => 'Deliver License Number And Codes Or Papers',
                            'variable_map_name'          => 'hasDeliverLicenseNumberAndCodesOrPapers',
                            'additional_modal_component' => 'DeliverLicenseNumberAndCodesOrPapers',
                        ],
                        [
                            'name'                       => 'Calculated And Processed Provision',
                            'variable_map_name'          => 'hasCalculatedAndProcessedProvision',
                            'additional_modal_component' => 'CalculatedAndProcessedProvision',
                        ],
                        [
                            'name'                       => 'Registered Client',
                            'variable_map_name'          => 'hasRegisteredClient',
                            'additional_modal_component' => 'RegisteredClient',
                        ],
                    ],
                ],
            ],
        ],

        [
            'name'                => 'Export',
            'icon_component_name' => 'PlanetAirplane',

            'statuses' => [
                [
                    'name' => 'Execute RDW Export',

                    'steps' => [
                        [
                            'name'                       => 'Received Licence Plate And Registration Card',
                            'variable_map_name'          => 'hasReceivedLicencePlateAndRegistrationCard',
                            'additional_modal_component' => 'ReceivedLicencePlateAndRegistrationCard',
                        ],
                        [
                            'name'                       => 'Photos Taken',
                            'variable_map_name'          => 'hasPhotosTaken',
                            'additional_modal_component' => 'PhotosTaken',
                        ],
                        [
                            'name'                       => 'Arranged Export Rdw',
                            'variable_map_name'          => 'hasArrangedExportRdw',
                            'additional_modal_component' => 'ArrangedExportRdw',
                        ],
                    ],
                ],
                [
                    'name' => 'Apply for BPM Return',

                    'steps' => [
                        [
                            'name'                       => 'Arranged Foreign Registration',
                            'variable_map_name'          => 'hasArrangedForeignRegistration',
                            'additional_modal_component' => 'ArrangedForeignRegistration',
                        ],
                        [
                            'name'                       => 'Sent All Documents And Bpm Return Form',
                            'variable_map_name'          => 'hasSentAllDocumentsAndBpmReturnForm',
                            'additional_modal_component' => 'SentAllDocumentsAndBpmReturnForm',
                        ],
                    ],
                ],
                [
                    'name' => 'Completed',

                    'steps' => [
                        [
                            'name'                       => 'Arranged Foreign Registration',
                            'variable_map_name'          => 'hasArrangedForeignRegistration',
                            'additional_modal_component' => 'ArrangedForeignRegistration',
                        ],
                        [
                            'name'                       => 'Bpm Returned',
                            'variable_map_name'          => 'hasBpmReturned',
                            'additional_modal_component' => 'BpmReturned',
                        ],
                    ],
                ],
            ],
        ],

        [
            'name'                => 'Transport Outbound',
            'icon_component_name' => 'Truck',

            'statuses' => [
                [
                    'name' => 'Planned',

                    'steps' => [
                        [
                            'name'                       => 'Transport Order Outbound',
                            'variable_map_name'          => 'hasTransportOrderOutbound',
                            'additional_modal_component' => null,
                        ],
                        [
                            'name'                       => 'Decided Internal Or External Transport',
                            'variable_map_name'          => 'hasDecidedInternalOrExternalTransport',
                            'additional_modal_component' => null,
                        ],
                    ],
                ],
                [
                    'name' => 'Delivered',

                    'steps' => [
                        [
                            'name'                       => 'Transport Outbound Picked up',
                            'variable_map_name'          => 'hasTransportOutboundPickedUp',
                            'additional_modal_component' => 'TransportOutboundPickedUp',
                        ],
                        [
                            'name'                       => 'Transport Outbound Delivered',
                            'variable_map_name'          => 'hasTransportOutboundDelivered',
                            'additional_modal_component' => 'TransportOutboundDelivered',
                        ],
                    ],
                ],
            ],
        ],

        [
            'name'                => 'Invoicing',
            'icon_component_name' => 'Invoice',

            'statuses' => [
                [
                    'name' => 'Ready For Invoice',

                    'steps' => [
                        [
                            'name'                       => 'Ready For Invoice',
                            'variable_map_name'          => 'hasReadyForInvoice',
                            'additional_modal_component' => 'ReadyForInvoice',
                        ],
                    ],
                ],
                [
                    'name' => 'Ready For Service Invoice',

                    'steps' => [
                        [
                            'name'                       => 'Service Invoice',
                            'variable_map_name'          => 'hasServiceInvoice',
                            'additional_modal_component' => 'ServiceInvoice',
                        ],
                    ],
                ],
                [
                    'name' => 'Sent Invoice',

                    'steps' => [
                        [
                            'name'                       => 'Sent Invoice To Customer',
                            'variable_map_name'          => 'hasSentInvoiceToCustomer',
                            'additional_modal_component' => 'SentInvoiceToCustomer',
                        ],
                    ],
                ],
                [
                    'name' => 'Determined Bpm For Invoicing',

                    'steps' => [
                        [
                            'name'                       => 'Determined Bpm And Uploaded Bpm Declaration',
                            'variable_map_name'          => 'hasDeterminedBpmAndUploadedBpmDeclaration',
                            'additional_modal_component' => 'UploadBpmDeclaration',
                        ],
                    ],
                ],
                [
                    'name' => 'Ready For Vehicle Invoicing',

                    'steps' => [
                        [
                            'name'                       => 'Created Vehicle Invoice',
                            'variable_map_name'          => 'hasCreatedVehicleInvoice',
                            'additional_modal_component' => 'CreatedVehicleInvoice',
                        ],
                    ],
                ],
                [
                    'name' => 'Vehicle Invoiced',

                    'steps' => [
                        [
                            'name'                       => 'Sent Vehicle Invoice',
                            'variable_map_name'          => 'hasSentVehicleInvoice',
                            'additional_modal_component' => 'SentVehicleInvoice',
                        ],
                    ],
                ],
                [
                    'name' => 'BPM Invoiced',

                    'steps' => [
                        [
                            'name'                       => 'Sent Bpm Invoice',
                            'variable_map_name'          => 'hasSentBpmInvoice',
                            'additional_modal_component' => 'SentBpmInvoice',
                        ],
                    ],
                ],
                [
                    'name' => 'Payment Received',

                    'steps' => [
                        [
                            'name'                       => 'Received Service Payment',
                            'variable_map_name'          => 'hasReceivedServicePayment',
                            'additional_modal_component' => 'ReceivedServicePayment',
                        ],
                        [
                            'name'                       => 'Received Invoice Payment',
                            'variable_map_name'          => 'hasReceivedInvoicePayment',
                            'additional_modal_component' => 'ReceivedInvoicePayment',
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * Subprocesses in every process flow.
     *
     * @var array|array[]
     */
    private array $processSubprocess = [
        'Trade - Import' => [
            'Ready for pick up',
            'Invoicing',
            'Payment',
            'Transport Inbound',
            'Documents Inbound',
            'Work Order',
            'Import',
            'Completing',
            'Transport Outbound',
        ],

        'Trade - Domestic' => [
            'Ready for pick up',
            'Invoicing',
            'Payment',
            'Transport Inbound',
            'Documents Inbound',
            'Work Order',
            'Completing',
            'Transport Outbound',
        ],

        'Trade - Export' => [
            'Ready for pick up',
            'Invoicing',
            'Payment',
            'Transport Inbound',
            'Documents Inbound',
            'Work Order',
            'Export',
            'Invoicing',
            'Completing',
            'Transport Outbound',
        ],

        'Trade - Transit' => [
            'Ready for pick up',
            'Invoicing',
            'Payment',
            'Transport Inbound',
            'Documents Inbound',
            'Work Order',
            'Completing',
            'Transport Outbound',
        ],

        'Trade - Pre-order' => [
            'Ready for pick up',
            'Invoicing',
            'Payment',
            'Transport Inbound',
            'Documents Inbound',
            'Work Order',
            'Completing',
            'Transport Outbound',
        ],

        'Service - Import' => [
            //            'Service Order',
            'Transport Inbound',
            'Documents Inbound',
            'Work Order',
            'Import',
            'Completing',
            'Transport Outbound',
            'Invoicing',
        ],

        'Service - Export' => [
            //            'Service Order',
            'Documents Inbound',
            'Export',
            'Invoicing',
        ],
    ];

    /**
     * Create a new VehicxWorkflowSeeder instance.
     */
    public function __construct()
    {
        parent::__construct($this->companyId);

        $this->seedData = $this->generateSeedData($this);
        $this->processSubprocessData = $this->processSubprocess;
    }
}
