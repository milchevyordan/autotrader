<?php

declare(strict_types=1);

namespace App\Services\Dashboard\Boxes;

use App\Services\Dashboard\Boxes\Builders\CompletedInboundTransportOrdersNoPapers;
use App\Services\Dashboard\Boxes\Builders\CompletedInvoiceOpenWorkOrder;
use App\Services\Dashboard\Boxes\Builders\CompletedInvoicesWithoutOutboundTransport;
use App\Services\Dashboard\Boxes\Builders\CompletedPurchaseOrderLastWeek;
use App\Services\Dashboard\Boxes\Builders\CompletedPurchaseOrderThisWeek;
use App\Services\Dashboard\Boxes\Builders\CompletedSalesOrderLastWeek;
use App\Services\Dashboard\Boxes\Builders\CompletedSalesOrderThisWeek;
use App\Services\Dashboard\Boxes\Builders\CompletedTransportOrderInboundOpenWorkOrder;
use App\Services\Dashboard\Boxes\Builders\CompletedTransportOrderInboundThisWeek;
use App\Services\Dashboard\Boxes\Builders\CompletedTransportOrderInboundToday;
use App\Services\Dashboard\Boxes\Builders\CompletedTransportOrderInboundYesterday;
use App\Services\Dashboard\Boxes\Builders\ConceptQuoteInvitations;
use App\Services\Dashboard\Boxes\Builders\DeliveryNextSixWeeks;
use App\Services\Dashboard\Boxes\Builders\NlRegistrationOpenWorkOrders;
use App\Services\Dashboard\Boxes\Builders\NlRegistrationWithoutInvoice;
use App\Services\Dashboard\Boxes\Builders\NoNlRegistrationWithBpmDeclaration;
use App\Services\Dashboard\Boxes\Builders\OpenInboundTransportOrders;
use App\Services\Dashboard\Boxes\Builders\OpenInvoices;
use App\Services\Dashboard\Boxes\Builders\OpenPurchaseOrders;
use App\Services\Dashboard\Boxes\Builders\OpenSalesOrders;
use App\Services\Dashboard\Boxes\Builders\OpenServiceOrders;
use App\Services\Dashboard\Boxes\Builders\RdwApprovedWithoutBpmDeclaration;
use App\Services\Dashboard\Boxes\Builders\RdwNotApproved;
use App\Services\Dashboard\Boxes\Builders\ReceivedWithPapersNoRdw;
use App\Services\Dashboard\Boxes\Builders\ReservedQuotes;
use App\Services\Dashboard\Boxes\Builders\SalesOrderCompletedWithoutInvoice;
use App\Services\Dashboard\Boxes\Builders\ServiceVehiclesCompletedTransportOrderInboundOpenWorkOrder;
use App\Services\Dashboard\Boxes\Builders\ServiceVehiclesWithoutServiceOrders;
use App\Services\Dashboard\Boxes\Builders\VehiclesInStock;
use App\Services\Dashboard\Boxes\Builders\VehiclesToPurchase;
use App\Services\Dashboard\Boxes\Builders\VehiclesWithoutOrders;
use App\Services\Dashboard\Boxes\Builders\VehiclesWithoutSalesOrder;
use App\Services\Dashboard\Boxes\Components\Box;
use App\Services\Service;

class DashboardBoxService extends Service
{
    /**
     * @return array<Box>
     */
    public static function getAllBoxes(): array
    {
        return [
            new Box(
                __('Vehicles without orders'),
                __('Vehicles without Pre-order /Purchase Order/ Sales Order/'),
                new VehiclesWithoutOrders()
            ),

            new Box(
                __('Vehicles without Sales order'),
                __('Vehicles without Sales order'),
                new VehiclesWithoutSalesOrder()
            ),

            new Box(
                __('Vehicles to be purchased'),
                __('Vehicles to be purchased'),
                new VehiclesToPurchase()
            ),

            new Box(
                __('Outstanding offers'),
                __('Outstanding offers'),
                new ConceptQuoteInvitations()
            ),

            new Box(
                __('Reserved offers'),
                __('Reserved offers'),
                new ReservedQuotes()
            ),

            new Box(
                __('Open Purchase Orders'),
                __('Open Purchase Orders'),
                new OpenPurchaseOrders()
            ),

            new Box(
                __('Open Sales Orders'),
                __('The Sales Orders that are not completed'),
                new OpenSalesOrders()
            ),

            new Box(
                __('Delivery in six weeks'),
                __('All vehicles with delivery dates agreed within the next six weeks'),
                new DeliveryNextSixWeeks()
            ),

            new Box(
                __('Open Inbound Transport Orders'),
                __('All vehicles with transport order but not yet received by us'),
                new OpenInboundTransportOrders()
            ),

            new Box(
                __('Received, but without papers'),
                __('All vehicles received by us but without original papers or quality color copies'),
                new CompletedInboundTransportOrdersNoPapers()
            ),

            new Box(
                __('Received, with papers, but without RDW'),
                __('All vehicles received by us with papers or quality copies, but no planned RDW inspection yet'),
                new ReceivedWithPapersNoRdw()
            ),

            new Box(
                __('Vehicles received this week'),
                __('All vehicles with Transport Order Inbound completed this week'),
                new CompletedTransportOrderInboundThisWeek()
            ),

            new Box(
                __('Vehicles received yesterday'),
                __('All vehicles with Transport Order Inbound completed yesterday'),
                new CompletedTransportOrderInboundYesterday()
            ),

            new Box(
                __('Vehicles received today'),
                __('All vehicles with Transport Order Inbound completed today'),
                new CompletedTransportOrderInboundToday()
            ),

            new Box(
                __('Vehicles without RDW approval'),
                __('All vehicles with RDW approval'),
                new RdwNotApproved()
            ),

            new Box(
                __('Vehicles with RDW approval without BPM declaration'),
                __('All vehicles with RDW approval, but no BPM declaration has been uploaded'),
                new RdwApprovedWithoutBpmDeclaration()
            ),

            new Box(
                __('Vehicles w/t NL registration number with BPM declaration'),
                __('All vehicles without NL registration number, but have Uploaded BPM declaration'),
                new NoNlRegistrationWithBpmDeclaration()
            ),

            new Box(
                __('Vehicles with NL registration number but no invoice to buyer'),
                __('All vehicles without NL registration number, but have Uploaded BPM declaration'),
                new NlRegistrationWithoutInvoice()
            ),

            new Box(
                __('All vehicles may/must be invoiced now'),
                __('Аll vehicles may/must be invoiced now as they have sales order completed, but DON`T have invoice at least at status Submitted'),
                new SalesOrderCompletedWithoutInvoice()
            ),

            new Box(
                __('All vehicles with open invoices'),
                __('All vehicles with open invoices - Invoices that are not completed'),
                new OpenInvoices()
            ),

            new Box(
                __('All vehicles w/Completed invoices and w/o outbound transport'),
                __('All vehicles with completed invoices and without outbound transport'),
                new CompletedInvoicesWithoutOutboundTransport()
            ),

            new Box(
                __('Vehicles at our location w/Open Work Order'),
                __('All vehicles with Transport Order Inbound completed and Work Order open'),
                new CompletedTransportOrderInboundOpenWorkOrder()
            ),

            new Box(
                __('Service vehicles at our location w/Open Work Orders'),
                __('All service vehicles with Transport Order Inbound completed and Work Order open'),
                new ServiceVehiclesCompletedTransportOrderInboundOpenWorkOrder()
            ),

            new Box(
                __('Service Vehicles w/o Service Orders'),
                __('All service vehicles without Service Orders'),
                new ServiceVehiclesWithoutServiceOrders()
            ),

            new Box(
                __('Open Service Orders'),
                __('All Service Orders that are not completed'),
                new OpenServiceOrders()
            ),

            new Box(
                __('All vehicles invoiced by us with open work orders'),
                __('All vehicles with completed invoice and open work order'),
                new CompletedInvoiceOpenWorkOrder()
            ),

            new Box(
                __('All vehicles with BVI and open work orders'),
                __('All vehicles with NL registration number and open work orders'),
                new NlRegistrationOpenWorkOrders()
            ),

            new Box(
                __('Vehicles in stock'),
                __('All vehicles with Purchase Order completed, but Sales Order status before Signed Contract Uploaded'),
                new VehiclesInStock()
            ),

            new Box(
                __('Vehicles sold this week'),
                __('All vehicles with Sales Order completed this week'),
                new CompletedSalesOrderThisWeek()
            ),

            new Box(
                __('Vehicles sold last week'),
                __('All vehicles with Sales Order completed last week'),
                new CompletedSalesOrderLastWeek()
            ),

            new Box(
                __('Vehicles bought this week'),
                __('All vehicles with Purchase Order completed this week'),
                new CompletedPurchaseOrderThisWeek()
            ),

            new Box(
                __('Vehicles bought last week'),
                __('All vehicles with Purchase Order completed last week'),
                new CompletedPurchaseOrderLastWeek()
            ),
        ];
    }

    public function recacheAllBoxes()
    {

        foreach (self::getAllBoxes() as $box) {
            $box
            ->getBoxBuilder()
            ->initBuilderValues()
            ->cacheValues();
        }
    }
}
