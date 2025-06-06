<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bpm;

class BpmService extends Service
{
    /**
     * Collection of bpmValues.
     *
     * @var null|Bpm
     */
    private Bpm|null $bpmValues;

    /**
     * Get the value of bpmValues.
     *
     * @return null|Bpm
     */
    public static function getValues(): Bpm|null
    {
        $self = new self();

        if (! isset($self->suppliers)) {
            $self->setValues();
        }

        return $self->bpmValues;
    }

    /**
     * Set the value of bpmValues.
     *
     * @return void
     */
    private function setValues(): void
    {
        $this->bpmValues = Bpm::getValues(request(null)->input('year'), request(null)->input('fuel'), request(null)->input('co2'))->first();
    }
}
