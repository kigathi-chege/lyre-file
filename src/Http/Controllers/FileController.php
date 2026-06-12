<?php

namespace Lyre\File\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Lyre\File\Models\File;
use Lyre\File\Repositories\Contracts\FileRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Lyre\Controller;

class FileController extends Controller
{
    public function __construct(
        FileRepositoryInterface $modelRepository
    ) {
        $model = new File();
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }

    public function stream($slug, $extension)
    {
        $attachment = File::where("slug", $slug)
            ->select([
                "id",
                "name",
                "path",
                "viewed_at",
                "storage",
            ])
            ->firstOrFail();

        $disk = Storage::disk($attachment->storage);

        if (! $disk->exists($attachment->path)) {
            Log::warning('file.stream_missing_path', [
                'file_id' => $attachment->id,
                'slug' => $slug,
                'path' => $attachment->path,
                'storage' => $attachment->storage,
            ]);

            throw new NotFoundHttpException('The requested file could not be found.');
        }

        $attachment->viewed_at = now();
        $attachment->save();

        $stream = $disk->readStream($attachment->path);
        if (! is_resource($stream)) {
            Log::warning('file.stream_unreadable', [
                'file_id' => $attachment->id,
                'slug' => $slug,
                'path' => $attachment->path,
                'storage' => $attachment->storage,
            ]);

            throw new NotFoundHttpException('The requested file could not be read.');
        }

        $cacheDuration = 6 * 30 * 24 * 60 * 60;
        return response()->stream(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
        }, 200, [
            'Content-Type' => $disk->mimeType($attachment->path) ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=' . $cacheDuration,
            'Expires' => gmdate("D, d M Y H:i:s", time() + $cacheDuration),
        ]);
    }

    public function download($slug, $extension)
    {
        $attachment = File::where("slug", $slug)
            ->select([
                "id",
                "name",
                "path",
                "viewed_at",
                "storage",
            ])
            ->firstOrFail();

        $disk = Storage::disk($attachment->storage);

        if (! $disk->exists($attachment->path)) {
            Log::warning('file.download_missing_path', [
                'file_id' => $attachment->id,
                'slug' => $slug,
                'path' => $attachment->path,
                'storage' => $attachment->storage,
            ]);

            throw new NotFoundHttpException('The requested file could not be found.');
        }

        $attachment->viewed_at = now();
        $attachment->save();
        $filePath = $disk->url($attachment->path);
        header("Cache-Control: public, max-age=31536000");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + 31536000));
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename={$attachment->name}");
        header("Content-Type: " . ($disk->mimeType($attachment->path) ?: 'application/octet-stream'));
        return readfile($filePath);
    }
}
