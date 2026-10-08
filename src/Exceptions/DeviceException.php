<?php

declare(strict_types=1);

namespace Ruvelo\Device\Exceptions;

use Throwable;

/**
 * Every exception the package throws implements this, so one catch covers them all.
 * (An interface rather than a base class, because UnknownMethod has to stay a
 * BadMethodCallException for code written against jenssegers/agent.)
 */
interface DeviceException extends Throwable {}
