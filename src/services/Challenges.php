<?php

declare(strict_types=1);
/** @noinspection ALL */

/**
 * AWS S3 Signed URLs plugin for Craft CMS 3.x
 *
 * Signed URLs for AWS S3 Craft Asset Filesystems, including the ability to limit access.
 *
 * @link      https://zaengle.com
 * @copyright Copyright (c) 2022 Zaengle Corp
 */

namespace zaengle\awss3signedurls\services;

use Craft;

use craft\base\Component;
use craft\elements\Asset;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use yii\base\ExitException;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use zaengle\awss3signedurls\AwsS3SignedUrls;
use zaengle\awss3signedurls\models\FsSettings;

/**
 * @author    Zaengle Corp
 * @package   AwsS3SignedUrls
 * @since     1.0.0
 */
class Challenges extends Component
{
    /**
     * Test if an Asset should be visible
     *
     * @param Asset $asset
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function challenge(Asset $asset): bool
    {
        /**
         * @var FsSettings $fsSettings
         */
        $fsSettings = AwsS3SignedUrls::getInstance()->fs->getSettings($asset->getFs());

        $challenge = $fsSettings->challenge ?? [$this, 'defaultChallenge'];

        return $challenge($asset);
    }

    /**
     * Handle failure to pass the challenge
     * @param Asset $asset
     * @throws InvalidConfigException
     */
    public function handleFailure(Asset $asset, Controller $controller): mixed
    {
        /**
         * @var FsSettings $fsSettings
         */
        $fsSettings = AwsS3SignedUrls::getInstance()->fs->getSettings($asset->getFs());

        $handler = $fsSettings->failureHandler ?? [$this, 'defaultFailureHandler'];

        return $handler($asset, $controller);
    }

    public function getChallengeUrl(Asset $asset): ?string
    {
        return UrlHelper::url(AwsS3SignedUrls::ROUTE_PROTECTED_ASSET . $asset->uid);
    }

    /**
     * By default, just check that there is a logged in user
     * @return bool
     * @throws \Throwable
     */
    public function defaultChallenge(): bool
    {
        return (bool) Craft::$app->getUser()->getIdentity();
    }

    /**
     * By default, redirect to login
     * @param Asset $asset
     * @param Controller $controller
     * @return void
     * @throws ExitException
     * @throws ForbiddenHttpException
     */
    public function defaultFailureHandler(Asset $asset, Controller $controller): void
    {
        $userSession = Craft::$app->getUser();

        $userSession->loginRequired();
        Craft::$app->end();
    }
}
