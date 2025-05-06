<?php

namespace Lyre\File\Concerns;

use Lyre\File\Models\Attachment;
use Lyre\File\Models\File;

trait HasFile
{
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function files()
    {
        return $this->hasManyThrough(File::class, Attachment::class, 'attachable_id', 'id', 'id', 'file_id')
            ->where('attachments.attachable_type', self::class);
    }

    /**
     * @param int[] $fileIds
     * @return Attachment[]
     * 
     * This function deletes all attachments and creates new ones from fileIds
     */
    public function attachFile($fileIds)
    {
        $this->attachments()->delete();
        return $this->attachments()->createMany(array_map(fn($fileId) => ['file_id' => $fileId], $fileIds));
    }
}
