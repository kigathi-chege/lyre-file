<?php

namespace Lyre\File\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Lyre\File\Models\File;

/**
 * Host apps bind this to protect files attached to paid or private content. Without a
 * binding every file is public, as before.
 */
interface FileDeliveryAuthorizer
{
    /**
     * Whether the viewer may receive the file's bytes.
     */
    public function allows(?Authenticatable $viewer, File $file): bool;

    /**
     * Public files may be cached by browsers and CDNs; protected ones are served privately.
     */
    public function isPublic(File $file): bool;
}
