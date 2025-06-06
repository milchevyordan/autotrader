<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

trait HasFiles
{
    public ?Collection $uploadedFiles = null;

    /**
     * Inverse of fileable relationship.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable', 'fileable_type', 'fileable_id', 'id')
            ->orderBy('order');
    }

    /**
     * Get grouped files by sections provided in param.
     *
     * @param  array        $sections
     * @return Collection[] an array where each key represents a section, and the corresponding
     *                      value is a Collection of files associated with that section
     */
    public function getGroupedFiles(array $sections): array
    {
        $this->load(['files' => function ($query) {
            $query->orderBy('order');
        }]);

        $files = $this->files;
        $fileGroups = [];

        foreach ($sections as $section) {
            $fileGroups[$section] = $files->filter(function ($file) use ($section) {
                return $section == $file->section;
            })->sortBy('order')->values();
        }

        return $fileGroups;
    }

    /**
     * Get the model's first file.
     */
    public function firstFile(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable', 'fileable_type', 'fileable_id', 'id')->ofMany('order', 'min');
    }

    /**
     * Get the model's last file.
     */
    public function lastFile(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable', 'fileable_type', 'fileable_id', 'id')->ofMany('order', 'max');
    }

    /**
     * Get the model's most recent file.
     */
    public function latestFile(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable', 'fileable_type', 'fileable_id', 'id')->latestOfMany();
    }

    /**
     * Get the model's oldest file.
     */
    public function oldestFile(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable', 'fileable_type', 'fileable_id', 'id')->oldestOfMany();
    }

    /**
     * Save resource and attach file given as parameter to it.
     *
     * @param File $file
     * @param null|string $section
     * @return void
     */
    public function saveWithFile(File $file, ?string $section = null): void
    {
        if (! $this->id) {
            $this->save();
        }

        $lastOrder = $this->files()
            ->where('section', $section)
            ->max('order') ?? 0;

        $file->fileable_id = $this->id;
        $file->fileable_type = $this->getMorphClass();
        $file->section = $section;
        $file->order = $lastOrder + 1;

        $this->files()->save($file);
    }

    /**
     * Saves the record with files. If there are no files, it only saves the record.
     *
     * @param  ?Collection<File> $uploadedFiles
     * @param  ?string           $section       - Separates the images by section if needed. This param allows us to have different sections in one model.
     * @return self
     */
    public function saveWithFiles(?Collection $uploadedFiles, ?string $section = null): self
    {
        if (! $this->id) {
            $this->save();
        }

        if (! $uploadedFiles || $uploadedFiles->isEmpty()) {
            return $this;
        }

        $maxFileOrder = $this->files()->max('order');

        $files = new Collection();

        foreach ($uploadedFiles as $file) {
            $file->fileable_id = $this->id;
            $file->fileable_type = $this->getMorphClass();
            $file->section = $section;
            $file->order = $maxFileOrder + 1;

            $files->push($file);

            $maxFileOrder++;
        }

        $this->files()->saveMany($files);

        $this->uploadedFiles = $files;

        return $this;
    }

    /**
     * Delete the all entity images.
     *
     * @return self
     */
    public function deleteEntityFiles(): self
    {
        $this->files()->whereIn('id', $this->id)->delete();

        return $this;
    }

    /**
     * Delete images By array of paths.
     *
     * @param  array $paths
     * @return self
     */
    public function deleteFilesByPaths(array $paths): self
    {
        $this->files()->whereIn('path', $paths)->delete();

        return $this;
    }

    /**
     * Get the value of uploadedFiles.
     *
     * @return ?Collection
     */
    public function getUploadedFiles(): ?Collection
    {
        return $this->uploadedFiles;
    }
}
