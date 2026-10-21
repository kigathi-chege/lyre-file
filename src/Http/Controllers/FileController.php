<?php

namespace Lyre\File\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Lyre\Controller;
use Lyre\File\Contracts\FileDeliveryAuthorizer;
use Lyre\File\Events\FileDelivered;
use Lyre\File\Models\File;
use Lyre\File\Repositories\Contracts\FileRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends Controller
{
    public function __construct(
        FileRepositoryInterface $modelRepository
    ) {
        $model = new File;
        $modelConfig = $model->generateConfig();
        parent::__construct($modelConfig, $modelRepository);
    }

    public function stream($slug, $extension)
    {
        $attachment = File::where('slug', $slug)
            ->select([
                'id',
                'name',
                'path',
                'viewed_at',
                'storage',
            ])
            ->firstOrFail();

        $this->authorizeDelivery($attachment);

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
        event(new FileDelivered($attachment, $this->viewer(), 'inline'));

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
            ...$this->cacheHeaders($attachment, $cacheDuration),
        ]);
    }

    public function download($slug, $extension)
    {
        $attachment = File::where('slug', $slug)
            ->select([
                'id',
                'name',
                'path',
                'viewed_at',
                'storage',
            ])
            ->firstOrFail();

        $this->authorizeDelivery($attachment);

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
        event(new FileDelivered($attachment, $this->viewer(), 'attachment'));
        foreach ($this->cacheHeaders($attachment, 31536000) as $name => $value) {
            header("{$name}: {$value}");
        }
        header('Content-Description: File Transfer');
        header("Content-Disposition: attachment; filename={$attachment->name}");
        header('Content-Type: '.($disk->mimeType($attachment->path) ?: 'application/octet-stream'));

        return readfile($filePath);
    }

    /**
     * Ask the host app's FileDeliveryAuthorizer (when bound) whether the viewer may receive
     * the file; files attached to paid content are refused to viewers without access.
     */
    protected function authorizeDelivery(File $file): void
    {
        $authorizer = $this->deliveryAuthorizer();

        if ($authorizer && ! $authorizer->allows($this->viewer(), $file)) {
            throw new AccessDeniedHttpException('You do not have access to this file.');
        }
    }

    /**
     * @return array<string, string>
     */
    protected function cacheHeaders(File $file, int $maxAge): array
    {
        $authorizer = $this->deliveryAuthorizer();

        if ($authorizer && ! $authorizer->isPublic($file)) {
            return ['Cache-Control' => 'private, no-store'];
        }

        return [
            'Cache-Control' => 'public, max-age='.$maxAge,
            'Expires' => gmdate('D, d M Y H:i:s', time() + $maxAge),
        ];
    }

    /**
     * File routes are not behind auth middleware; resolve a bearer token through Sanctum.
     */
    protected function viewer(): ?Authenticatable
    {
        return request()->user() ?? (config('auth.guards.sanctum') ? auth('sanctum')->user() : null);
    }

    protected function deliveryAuthorizer(): ?FileDeliveryAuthorizer
    {
        return app()->bound(FileDeliveryAuthorizer::class) ? app(FileDeliveryAuthorizer::class) : null;
    }
}
