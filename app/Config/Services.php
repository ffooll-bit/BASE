<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * Neo Feeder API Service
     *
     * Returns a singleton instance of the Neo Feeder API service
     * with its configuration and HTTP client dependencies injected.
     *
     * @param bool $getShared
     *
     * @return \App\Libraries\NeoFeeder
     */
    public static function neoFeeder($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('neoFeeder');
        }

        $config = config('NeoFeeder');
        $curl   = service('curlrequest');

        return new \App\Libraries\NeoFeeder($config, $curl);
    }

    public static function wizardProgress($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('wizardProgress');
        }

        return new \App\Libraries\WizardProgress(service('cache'));
    }

    public static function pisn($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('pisn');
        }

        return new \App\Libraries\PisnService();
    }

    /**
     * Authentication Service
     *
     * Returns a singleton instance of the Authentication service
     * with its NeoFeeder, Session, and Encryption dependencies injected.
     *
     * @param bool $getShared
     *
     * @return \App\Libraries\Auth
     */
    public static function auth($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('auth');
        }

        $neoFeeder  = service('neoFeeder');
        $session    = service('session');
        $encryption = service('encrypter');

        return new \App\Libraries\Auth($neoFeeder, $session, $encryption);
    }
}
