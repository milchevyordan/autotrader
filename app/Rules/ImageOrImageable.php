<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

class ImageOrImageable implements ValidationRule
{
    /**
     * The list of allowed MIME types for image files.
     *
     * @var array
     */
    public array $allowedMimeTypes;

    /**
     * The min size for image files in MB.
     *
     * @var float
     */
    public float $minImageSizeMb;

    /**
     * The maximum allowed size for image files in MB.
     *
     * @var float
     */
    public float $maxImageSizeMb;

    /**
     * The errors returned from the validations.
     *
     * @var array
     */
    private array $errors = [];

    /**
     * ImageOrImageable constructor.
     *
     * @param null|array $validations an array of validation settings
     */
    public function __construct()
    {
        $imageDefaultValidations = config('app.validation.image');

        $this
            ->setAllowedMimeTypes($imageDefaultValidations['mimeTypes'])
            ->setminImageSizeMb($imageDefaultValidations['minImageSizeMb'])
            ->setmaxImageSizeMb($imageDefaultValidations['maxImageSizeMb']);
    }

    /**
     * Run the validation rule.
     *
     * @param  string                                       $attribute
     * @param  mixed                                        $value
     * @param  Closure(string): PotentiallyTranslatedString $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value instanceof UploadedFile) {
            if (! $value->isValid()) {
                $fail($value->getErrorMessage());

                return;
            }

            if (! $this->passesUploadedFileRules($value)) {
                $fail(implode("\n", $this->getErrors()));

                return;
            }

            return;
        }

        if (! $this->isImageableInDatabase($value)) {
            $fail(implode("\n", $this->getErrors()));
        }
    }

    /**
     * Validate the uploaded file.
     *
     * @param  UploadedFile $file
     * @return bool
     */
    protected function passesUploadedFileRules(UploadedFile $file): bool
    {
        $errors = [];

        $maxImageSizeMbMb = $this->getmaxImageSizeMb();
        $minImageSizeMbMb = $this->getminImageSizeMb();
        $allowedMimeTypes = $this->getAllowedMimeTypes();

        $fileSizeInMb = $this->convertBytesToMb($file->getSize());

        // Check MIME type
        if (! in_array($file->getMimeType(), $allowedMimeTypes, true)) {
            $allowedTypes = implode(', ', $allowedMimeTypes);
            $errors[] = __("Image '{$file->getClientOriginalName()}' type not allowed. Allowed types: {$allowedTypes}.");
        }

        if ($fileSizeInMb < $minImageSizeMbMb) {
            $errors[] = __("Image '{$file->getClientOriginalName()}' size is less than minimum required size of {$minImageSizeMbMb} MB.");
        }

        if ($fileSizeInMb > $maxImageSizeMbMb) {
            $errors[] = __("Image '{$file->getClientOriginalName()}' size exceeds maximum allowed size of {$maxImageSizeMbMb} MB.");
        }

        $this->setErrors($errors);

        return count($errors) === 0;
    }

    /**
     * Validate file from DB.
     *
     * @param  array $value
     * @return bool
     */
    protected function isImageableInDatabase(array $value): bool
    {
        // Implement your logic to check if the file is imageable in the database
        // Return true if it's imageable, otherwise return false
        $this->setErrors([__('Not an object in the Database')]);

        return class_exists($value['imageable_type']) && is_subclass_of($value['imageable_type'], 'Illuminate\Database\Eloquent\Model');
    }

    /**
     * Get the value of allowedMimeTypes.
     *
     * @return array
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Set the value of allowedMimeTypes.
     *
     * @param  array $allowedMimeTypes
     * @return self
     */
    public function setAllowedMimeTypes(array $allowedMimeTypes): self
    {
        $this->allowedMimeTypes = $allowedMimeTypes;

        return $this;
    }

    /**
     * Get the value of maxImageSizeMb.
     *
     * @return float
     */
    public function getmaxImageSizeMb(): float
    {
        return $this->maxImageSizeMb;
    }

    /**
     * Set the value of maxImageSizeMb.
     *
     * @param  int  $maxImageSizeMb
     * @return self
     */
    public function setmaxImageSizeMb(float $maxImageSizeMb): self
    {
        $this->maxImageSizeMb = $maxImageSizeMb;

        return $this;
    }

    public function getmaxImageSizeMbInMb()
    {
        return $this->getmaxImageSizeMb() / (1024 * 1024);
    }

    private function convertBytesToMb(int $bytes)
    {
        return $bytes / (1024 * 1024);
    }

    /**
     * Get the value of minImageSizeMb
     *
     * @return float
     */
    public function getminImageSizeMb(): float
    {
        return $this->minImageSizeMb;
    }

    /**
     * Set the value of minImageSizeMb
     *
     * @param float $minImageSizeMb
     *
     * @return self
     */
    public function setminImageSizeMb(float $minImageSizeMb): self
    {
        $this->minImageSizeMb = $minImageSizeMb;

        return $this;
    }

    /**
     * Get the errors returned from the validations.
     *
     * @return array
     */
    private function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Set the errors returned from the validations.
     *
     * @param  array $errors the errors returned from the validations
     * @return void
     */
    private function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }
}
