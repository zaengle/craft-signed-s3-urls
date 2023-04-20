<?php

declare(strict_types=1);

namespace zaengle\awss3signedurls\models;

use Closure;
use craft\base\Model;
use DateTimeInterface;

class FsSettings extends Model
{
    /**
     * @var bool Should this route be protected
     */
    public bool $protect = false;

    /**
     * @var DateTimeInterface|int|string How long should the signed URL be valid for
     */
    public DateTimeInterface|int|string $expires = "+1 minutes";
    /**
     * @var Closure optionally customise the challenge to apply to requests for assets in this fs, receives the
     * asset its only argument
     */
    public Closure $challenge;
    /**
     * @var Closure optionally customise the handler for failures to pass the challenge, receives the asset and the
     * controller instance as arguments
     */
    public Closure $failureHandler;
}
