<?php

declare(strict_types=1);
/**
 * AWS S3 Signed URLs plugin for Craft CMS 3.x
 *
 * @link      https://zaengle.com
 * @copyright Copyright (c) 2022 Zaengle Corp
 */

namespace zaengle\awss3signedurls\models;

use craft\base\Model;

/**
 * @author    Zaengle Corp
 * @package   Zaengle
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public array $filesystems = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['filesystems', []],
        ];
    }
}
