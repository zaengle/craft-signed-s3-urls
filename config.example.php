<?php
/**
 * AWS S3 Signed URLs plugin for Craft CMS 3.x
 *
 * @link      https://zaengle.com
 * @copyright Copyright (c) 2022 Zaengle Corp
 */

use Craft;
use craft\elements\Asset;
use craft\web\Controller;

return [
    'filesystems' => [
        '<fsHandle>' => [
            'protect' => true,
            // Time in seconds that the signed URL is valid for
            'expires'  => "+1 minutes",
//            Optionally customise the challenge callback
//            By default the plugin just requires a logged-in user
//            Callbacks receive the Asset as their only argument
//            'challenge' => static function (Asset $asset): bool
//            {
//                return true;
//            },
//            Optionally customise the failure handler callback
//            By default the plugin just redirects to the login page
//            Callbacks receive the Asset + Controller instance as their arguments
//            'failureHandler' => static function (Asset $asset, Controller $controller): mixed
//            {
//                $userSession = Craft::$app->getUser();
//
//                $userSession->loginRequired();
//                Craft::$app->end();
//            },
        ],
    ],
];
