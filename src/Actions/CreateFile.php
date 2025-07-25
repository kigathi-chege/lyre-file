<?php

namespace Lyre\File\Actions;

use Illuminate\Http\UploadedFile;

class CreateFile
{
    public static function make(array $data)
    {
        $absolutePath = storage_path('app/public/' . $data['file']);

        $uploadedFile = new UploadedFile(
            $absolutePath,
            basename($absolutePath),
            mime_content_type($absolutePath),
            null,
            true
        );

        // $fileRepository = app(\Lyre\File\Repositories\Contracts\FileRepositoryInterface::class);
        // $record = $fileRepository->uploadFile($uploadedFile, $data['name'] ?? null, $data['description'] ?? null, $data['attachment_file_names'] ?? null);

        $record = fileRepository()
            ->uploadFile(
                $uploadedFile,
                $data['name'] ?? null,
                $data['description'] ?? null,
                $data['attachment_file_names'] ?? null
            );

        unlink($absolutePath);

        // TODO: Kigathi - July 15 2025 - Implement tenant association
        // if (
        //     static::getResource()::isScopedToTenant() &&
        //     ($tenant = Filament::getTenant())
        // ) {
        //     return $this->associateRecordWithTenant($record, $tenant);
        // }

        $record->save();

        return $record;
    }
}
