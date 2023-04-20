<?php

declare(strict_types=1);
/**
 * AWS S3 Signed URLs plugin for Craft CMS 4.x
 *
 * Signed URLs for AWS S3 Craft Asset Filesystems, including the ability to limit access.
 *
 * @link      https://zaengle.com
 * @copyright Copyright (c) 2023 Zaengle Corp
 */

namespace zaengle\awss3signedurls\services;

use craft\base\Component;
use craft\base\FsInterface;

use zaengle\awss3signedurls\AwsS3SignedUrls;
use zaengle\awss3signedurls\models\FsSettings as FsSettingsModel;
use zaengle\awss3signedurls\models\Settings;

/**
 * @author    Zaengle Corp
 * @package   AwsS3SignedUrls
 * @since     1.0.0
 */
class Fs extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get the settings for a FS
     *
     * @param FsInterface $fs
     * @return FsSettingsModel|null
     */
    public function getSettings(FsInterface $fs): ?FsSettingsModel
    {
        /**
         * @var Settings $pluginSettings
         */
        $pluginSettings = AwsS3SignedUrls::getInstance()->getSettings();

        /**
         * @var ?array $fsSettings
         */
        $fsSettings = $pluginSettings['filesystems'][$fs->handle ?? null] ?? false;

        if ($fsSettings) {
            return new FsSettingsModel($fsSettings);
        }

        return null;
    }

    /**
     * Is a FS protected by the plugin?
     *
     * @param FsInterface $fs
     * @return bool
     */
    public function isProtected(FsInterface $fs): bool
    {
        /**
         * @var ?FsSettingsModel $fsSettings
         */
        $fsSettings = $this->getSettings($fs);

        return $fsSettings && $fsSettings->protect;
    }
}
