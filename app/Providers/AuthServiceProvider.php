<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Company;
use App\Models\CompanyGroup;
use App\Models\Configuration;
use App\Models\Document;
use App\Models\EmailTemplate;
use App\Models\Engine;
use App\Models\File;
use App\Models\Image;
use App\Models\Item;
use App\Models\Mail;
use App\Models\Make;
use App\Models\PreOrder;
use App\Models\PreOrderVehicle;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\QuoteInvitation;
use App\Models\SalesOrder;
use App\Models\ServiceLevel;
use App\Models\ServiceOrder;
use App\Models\Setting;
use App\Models\TransportOrder;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\Variant;
use App\Models\Vehicle;
use App\Models\VehicleGroup;
use App\Models\VehicleModel;
use App\Models\Workflow;
use App\Models\WorkOrder;
use App\Models\WorkOrderTask;
use App\Policies\CompanyGroupPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\ConfigurationPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\EmailTemplatePolicy;
use App\Policies\EnginePolicy;
use App\Policies\FilePolicy;
use App\Policies\ImagePolicy;
use App\Policies\ItemPolicy;
use App\Policies\MailPolicy;
use App\Policies\MakePolicy;
use App\Policies\PreOrderPolicy;
use App\Policies\PreOrderVehiclePolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\QuoteInvitationPolicy;
use App\Policies\QuotePolicy;
use App\Policies\RolePolicy;
use App\Policies\SalesOrderPolicy;
use App\Policies\ServiceLevelPolicy;
use App\Policies\ServiceOrderPolicy;
use App\Policies\SettingPolicy;
use App\Policies\TransportOrderPolicy;
use App\Policies\UserGroupPolicy;
use App\Policies\UserPolicy;
use App\Policies\VariantPolicy;
use App\Policies\VehicleGroupPolicy;
use App\Policies\VehicleModelPolicy;
use App\Policies\VehiclePolicy;
use App\Policies\WorkflowPolicy;
use App\Policies\WorkOrderPolicy;
use App\Policies\WorkOrderTaskPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class         => CompanyPolicy::class,
        CompanyGroup::class    => CompanyGroupPolicy::class,
        Engine::class          => EnginePolicy::class,
        Document::class        => DocumentPolicy::class,
        Item::class            => ItemPolicy::class,
        Make::class            => MakePolicy::class,
        PreOrder::class        => PreOrderPolicy::class,
        PurchaseOrder::class   => PurchaseOrderPolicy::class,
        SalesOrder::class      => SalesOrderPolicy::class,
        ServiceLevel::class    => ServiceLevelPolicy::class,
        ServiceOrder::class    => ServiceOrderPolicy::class,
        Vehicle::class         => VehiclePolicy::class,
        PreOrderVehicle::class => PreOrderVehiclePolicy::class,
        VehicleModel::class    => VehicleModelPolicy::class,
        VehicleGroup::class    => VehicleGroupPolicy::class,
        Variant::class         => VariantPolicy::class,
        TransportOrder::class  => TransportOrderPolicy::class,
        User::class            => UserPolicy::class,
        UserGroup::class       => UserGroupPolicy::class,
        Setting::class         => SettingPolicy::class,
        Workflow::class        => WorkflowPolicy::class,
        WorkOrder::class       => WorkOrderPolicy::class,
        WorkOrderTask::class   => WorkOrderTaskPolicy::class,
        Mail::class            => MailPolicy::class,
        Configuration::class   => ConfigurationPolicy::class,
        Quote::class           => QuotePolicy::class,
        QuoteInvitation::class => QuoteInvitationPolicy::class,
        EmailTemplate::class   => EmailTemplatePolicy::class,
        Image::class           => ImagePolicy::class,
        File::class            => FilePolicy::class,
        Role::class            => RolePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
    }
}
