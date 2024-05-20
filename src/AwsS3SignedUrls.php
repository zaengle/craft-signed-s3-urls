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

namespace zaengle\awss3signedurls;

use Craft;

use craft\base\Plugin;
use craft\elements\Asset;
use craft\events\DefineAssetUrlEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;

use yii\base\Event;

use zaengle\awss3signedurls\models\Settings as SettingsModel;
use zaengle\awss3signedurls\services\Challenges as ChallengesService;
use zaengle\awss3signedurls\services\Fs as FsService;
use zaengle\awss3signedurls\services\Signer as SignerService;

/**
 * Class AwsS3SignedUrls
 *
 * @author    Zaengle Corp
 * @package   AwsS3SignedUrls
 * @since     1.0.0
 *
 * @property  SignerService $signer
 * @property  ChallengesService $challenges
 * @property  FsService $fs
 */
class AwsS3SignedUrls extends Plugin
{
    public const ROUTE_PROTECTED_ASSET = 'protected-asset/';
    // Static Properties
    // =========================================================================

    public static AwsS3SignedUrls $plugin;

    // Public Properties
    // =========================================================================

    public $controllerNamespace = 'zaengle\awss3signedurls\controllers';
    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        $this->installEventHandlers();

        $this->setComponents([
            'challenges' => ChallengesService::class,
            'fs' => FsService::class,
            'signer' => SignerService::class,
        ]);

        Craft::info('AwsS3SignedUrls plugin loaded', __METHOD__);
    }

    // Protected Methods
    // =========================================================================
    protected function installEventHandlers(): void
    {
        Event::on(
            UrlManager::class,
            name: UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            handler: function(RegisterUrlRulesEvent $event) {
                $event->rules[self::ROUTE_PROTECTED_ASSET . '<assetUid:{uid}>'] = "$this->handle/challenge";
            }
        );

        Event::on(
            UrlManager::class,
            name: UrlManager::EVENT_REGISTER_CP_URL_RULES,
            handler: function(RegisterUrlRulesEvent $event) {
                $event->rules[self::ROUTE_PROTECTED_ASSET . '<assetUid:{uid}>'] = "$this->handle/challenge";
            }
        );

        /**
         * Override protected Asset URLs
         */
        Event::on(
            Asset::class,
            Asset::EVENT_DEFINE_URL,
            function(DefineAssetUrlEvent $event) {
                if ($this->fs->isProtected($event->sender->getFs())) {
                    $event->url = $this->challenges->getChallengeUrl($event->sender);
                }
            }
        );
    }

    /**
     * Copy example config to project's config folder
     */
    protected function afterInstall(): void
    {
        $configSource = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.example.php';
        $configTarget = \Craft::$app->getConfig()->configDir . DIRECTORY_SEPARATOR . 'awss3signedurls.php';

        if (!file_exists($configTarget)) {
            copy($configSource, $configTarget);
        }
    }

    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new SettingsModel();
    }
}
