<?php

declare(strict_types=1);
/** @noinspection ALL */

/**
 * AWS S3 Signed URLs plugin for Craft CMS 4.x
 *
 * Signed URLs for AWS S3 Craft Asset Filesystems, including the ability to limit access.
 *
 * @link      https://zaengle.com
 * @copyright Copyright (c) 2023 Zaengle Corp
 */

namespace zaengle\awss3signedurls\services;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;

use craft\awss3\Fs as AwsS3Fs;
use craft\base\Component;
use craft\elements\Asset;
use craft\helpers\App as AppHelper;

use yii\base\InvalidConfigException;
use zaengle\awss3signedurls\AwsS3SignedUrls;
use zaengle\awss3signedurls\errors\UnsupportedFsException;

/**
 * @author    Zaengle Corp
 * @package   AwsS3SignedUrls
 * @since     1.0.0
 */
class Signer extends Component
{
    /**
     * Get a Signed S3 URL for a GET request to an S3 asset
     *
     * @param Asset $asset
     * @return string
     * @throws InvalidConfigException|UnsupportedFsException
     */
    public function getSignedUrl(Asset $asset): string
    {
        $fs = $asset->getFs();

        if (! $fs instanceof AwsS3Fs) {
            throw new UnsupportedFsException('Asset is not an AWS S3 asset');
        }

        $s3Settings = self::getFsS3Settings($fs);
        $pluginSettings = AwsS3SignedUrls::getInstance()->fs->getSettings($fs);

        $s3config = AwsS3Fs::buildConfigArray($s3Settings['keyId'], $s3Settings['secret'], $s3Settings['region']);
        $s3Client = self::client($s3config);

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $s3Settings['bucket'],
            'Key' => AppHelper::parseEnv($fs->subfolder) . $asset->path,
        ]);

        $request = $s3Client->createPresignedRequest($cmd, $pluginSettings->expires);

        return (string) $request->getUri();
    }

    /**
     * Get an Amazon S3 client.
     *
     * @param array $config client config
     * @param array $credentials credentials to use when generating a new token
     * @return S3Client
     */
    protected static function client(array $config = [], array $credentials = []): S3Client
    {
        if (! empty($config['credentials']) && $config['credentials'] instanceof Credentials) {
            $config['generateNewConfig'] = static function () use ($credentials) {
                $args = [
                    $credentials['keyId'],
                    $credentials['secret'],
                    $credentials['region'],
                    true,
                ];

                return call_user_func_array(AwsS3Fs::class . '::buildConfigArray', $args);
            };
        }

        return new S3Client($config);
    }

    /**
     * Return the relevant S3 settings from the Volume as an array
     *
     * @param AwsS3Fs $fs
     * @return array
     */
    protected static function getFsS3Settings(AwsS3Fs $fs): array
    {
        return [
            'bucket' => AppHelper::parseEnv($fs->bucket),
            'keyId' => AppHelper::parseEnv($fs->keyId),
            'region' => AppHelper::parseEnv($fs->region),
            'secret' => AppHelper::parseEnv($fs->secret),
        ];
    }
}
