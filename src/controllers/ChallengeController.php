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

namespace zaengle\awss3signedurls\controllers;

use Craft;
use craft\elements\Asset;
use craft\web\Controller;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;

use yii\web\NotFoundHttpException;
use zaengle\awss3signedurls\AwsS3SignedUrls;
use zaengle\awss3signedurls\errors\UnsupportedFsException;

/**
 * @author    Zaengle Corp
 * @package   AwsS3SignedUrls
 * @since     1.0.0
 */
class ChallengeController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected array|bool|int $allowAnonymous = ['index'];
    protected AwsS3SignedUrls $plugin;

    public function init(): void
    {
        parent::init();
        $this->plugin = AwsS3SignedUrls::getInstance();
    }

    // Public Methods
    // =========================================================================
    public function beforeAction($action): bool
    {
        if (! parent::beforeAction($action)) {
            return false;
        }

        $request = Craft::$app->getRequest();

        if (! $request->isGet) {
            throw new BadRequestHttpException();
        }

        return true; // or false to not run the action
    }

    /**
     * Challenge an asset request
     *
     * @param string|null $assetUid
     * @return mixed
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     * @throws UnsupportedFsException
     */
    public function actionIndex(string $assetUid = null): mixed
    {
        if (! $assetUid) {
            throw new BadRequestHttpException();
        }

        $asset = Asset::findOne(['uid' => $assetUid]);

        if (! $asset) {
            throw new NotFoundHttpException();
        }

        if ($this->plugin->challenges->challenge($asset)) {
            return $this->redirect($this->plugin->signer->getSignedUrl($asset));
        }

        return $this->plugin->challenges->handleFailure($asset, $this);
    }
}
