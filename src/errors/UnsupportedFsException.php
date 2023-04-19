<?php

declare(strict_types=1);

namespace zaengle\awss3signedurls\errors;

use yii\base\Exception;

class UnsupportedFsException extends Exception
{
    public function getName()
    {
        return 'Only AWS S3 Asset Filesystems are supported';
    }
}
