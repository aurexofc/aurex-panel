<?php

namespace Pterodactyl\Exceptions;

class ManifestDoesNotExistException extends \Exception
{
    // NOTE: This exception intentionally does NOT implement
    // Spatie\Ignition\Contracts\ProvidesSolution. Ignition is a dev-only
    // package; referencing its interface here causes a fatal
    // "Interface not found" error in production (--no-dev installs)
    // at the exact moment this exception is thrown (missing manifest).
}
