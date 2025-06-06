<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MailType;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends BaseSeeder
{
    /**
     * Create a new EmailTemplateSeeder instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    private array $templates = [
        [
            'name'        => 'Exception email template',
            'mail_type'   => MailType::Red_flag->value,
            'text_top'    => null,
            'text_bottom' => 'Thank you for using our application!',
        ],
        [
            'name'        => 'Pdf mail template',
            'mail_type'   => MailType::Pdf->value,
            'text_top'    => null,
            'text_bottom' => 'Thank you for using our application!',
        ],
        [
            'name'        => 'Internal remarks email template',
            'mail_type'   => MailType::Internal_remarks->value,
            'text_top'    => 'Internal remarks top',
            'text_bottom' => 'Internal remarks email bottom!',
        ],
        [
            'name'        => 'Owner changed email template',
            'mail_type'   => MailType::Owner_changed->value,
            'text_top'    => 'Owner changed email top',
            'text_bottom' => 'Owner changed email bottom!',
        ],
        [
            'name'        => 'Assigned to work order task mail template',
            'mail_type'   => MailType::Work_order_task->value,
            'text_top'    => 'Assigned to work order email top',
            'text_bottom' => 'Assigned to work order email bottom!',
        ],
        [
            'name'        => 'Vehicle transfer link',
            'mail_type'   => MailType::Transfer_vehicle->value,
            'text_top'    => 'Vehicle transfer link email top',
            'text_bottom' => 'Vehicle transfer link email bottom!',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templatesInsertData = [];
        foreach ($this->getCompanyCompanyAdministratorIdsMap() as $companyAdministratorIds) {
            foreach ($this->templates as $template) {
                $template['creator_id'] = $companyAdministratorIds;

                $templatesInsertData[] = $template;
            }
        }

        EmailTemplate::insert($templatesInsertData);
    }
}
