# AWS S3 Signed URLs plugin for Craft CMS 4.x

Generate signed URLs for AWS S3 Craft Asset Volumes, including the ability to limit access.

## Requirements

This plugin requires:
- Craft CMS 4.3.0+
- "craftcms/aws-s3" 2.0+
- PHP 8.0+

## Installation

```shell
composer require zaengle/craft-awss3signedurls
craft plugin/install awss3signedurls
```
or with ddev:

```shell
ddev composer require zaengle/craft-awss3signedurls
ddev craft plugin/install awss3signedurls
```


## Overview

The plugin is configured entirely via the config file at `config/aws-s3-signed-urls.php`.  The config file is 
automatically generated when the plugin is installed. There is no Control Panel interface.

## Configuration

The config file must return an array with a `filesystems` key.  The `filesystems` key should be an associative array that
maps a Filesystem handle to an array of `FsSettings`.

### Per FS Configuration

The supported keys for the `FsSettings` array are:

`protect` - (bool) Whether to protect the FS with signed URLs.  Defaults to `false`.
`expires` - (DateTimeInterface|string|int) Duration that the signed URL is valid for, can be a `\DateInterval` parsable string ,or a 
number of seconds.  Defaults to `+1 minutes`.
`challenge` - (Closure) Optionally customise the challenge callback. By default, the plugin just requires a logged-in user.
Callbacks receive the Asset as their only argument.
`failureHandler` - (Closure) Optionally customise the failure handler callback.  By default, the plugin just redirects to
the login page.  Callbacks receive the Asset + Controller instance as their arguments.

You only need add keys to `filesystems` for the Filesystems you want to protect.

### Example Configuration

```php
<?php
use Craft;
use craft\elements\Asset;
use craft\web\Controller;
return [
    'filesystems' => [
        'myS3Fs' => [
            'protect' => true,
            // Duration that the signed URL is valid for, can be a \DateInterval parsable string ,or a number of seconds
            'expires'  => "+1 minutes",
//            Require user to be logged in and have a specific permission
            'challenge' => static function (Asset $asset): bool
            {
                $userSession = Craft::$app->getUser();
                
                if ($userSession->checkPermission('myCustomPermission'))
                {
                    return true;
                }
                return false;
            },
//            Optionally customise the failure handler callback
//            By default the plugin just redirects to the login page
//            Callbacks receive the Asset + Controller instance as their arguments
            'failureHandler' => static function (Asset $asset, Controller $controller): mixed
            {
                $userSession = Craft::$app->getUser();

                $userSession->loginRequired();
                Craft::$app->end();
            },
        ],
    ],
];
```

## Using Protected Assets in Templates / Elsewhere

No changes to your templates are required. The plugin will automatically override the URLs for Assets in protected 
Filesystem with a challenge URL. Requests to the challenge URL will redirect to a signed URL if the challenge is 
successful for the user that makes the request. 

## Credits

Brought to you by [Zaengle Corp](https://zaengle.com)

Icon is guard by Rflor from [The Noun Project](https://thenounproject.com/browse/icons/term/guard/) (CCBY3.0)