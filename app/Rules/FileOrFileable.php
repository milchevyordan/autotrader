<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

class FileOrFileable implements ValidationRule
{
    /**
     * The list of allowed MIME types for image files.
     *
     * @var array
     */
    public array $allowedMimeTypes;

    /**
     * The maximum allowed size for image files in bytes.
     *
     * @var float
     */
    public float $minFileSizeMb;

    /**
     * The maximum allowed size for image files in bytes.
     *
     * @var float
     */
    public float $maxFileSizeMb;

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
     * @param mixed      $value
     */
    public function __construct()
    {
        $fileDefaultValidations = config('app.validation.file');

        $this
            ->setAllowedMimeTypes($fileDefaultValidations['mimeTypes'])
            ->setMinFileSizeMb($fileDefaultValidations['minFileSizeMb'])
            ->setMaxFileSizeMb($fileDefaultValidations['maxFileSizeMb']);
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

        if (! $this->isFileableInDatabase($value)) {
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

        $allowedMimeTypes = $this->getAllowedMimeTypes();
        $minFileSizeMbMb = $this->getMinFileSizeMb();
        $maxFileSizeMbMb = $this->getMaxFileSizeMb();

        $fileSizeInMb = $this->convertBytesToMb($file->getSize());

        if (! in_array($file->getMimeType(), $allowedMimeTypes, true)) {
            $allowedTypes = implode(', ', $allowedMimeTypes);
            $errors[] = __("File '{$file->getClientOriginalName()}' type not allowed. Allowed types: {$allowedTypes}.");
        }

        if ($fileSizeInMb < $minFileSizeMbMb) {
            $errors[] = __("Image '{$file->getClientOriginalName()}' size is less than minimum required size of {$minFileSizeMbMb} MB.");
        }

        if ($fileSizeInMb > $maxFileSizeMbMb) {
            $errors[] = __("File '{$file->getClientOriginalName()}' size exceeds maximum allowed size of {$maxFileSizeMbMb} MB.");
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
    protected function isFileableInDatabase(array $value): bool
    {
        // Implement your logic to check if the file is imageable in the database
        // Return true if it's imageable, otherwise return false
        $this->setErrors([__('Not an object in the Database')]);

        return class_exists($value['fileable_type']) && is_subclass_of($value['fileable_type'], 'Illuminate\Database\Eloquent\Model');
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
     * Get the value of minFileSizeMb
     *
     * @return float
     */
    public function getMinFileSizeMb(): float
    {
        return $this->minFileSizeMb;
    }

    /**
     * Set the value of minFileSizeMb
     *
     * @param float $minFileSizeMb
     *
     * @return self
     */
    public function setMinFileSizeMb(float $minFileSizeMb): self
    {
        $this->minFileSizeMb = $minFileSizeMb;

        return $this;
    }

    /**
     * Get the value of maxFileSizeMb
     *
     * @return float
     */
    public function getMaxFileSizeMb(): float
    {
        return $this->maxFileSizeMb;
    }

    /**
     * Set the value of maxFileSizeMb
     *
     * @param float $maxFileSizeMb
     *
     * @return self
     */
    public function setMaxFileSizeMb(float $maxFileSizeMb): self
    {
        $this->maxFileSizeMb = $maxFileSizeMb;

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
     * @return self
     */
    private function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Return the converted bytes to MB
     *
     * @param  int $bytes
     * @return float
     */
    private function convertBytesToMb(int $bytes): float
    {
        return $bytes / (1024 * 1024);
    }
}
