<?php

namespace EventEspresso\Square\domain;

/**
 * Class Domain
 * Holds constants for the Square Add-on.
 *
 * @package        Event Espresso
 * @subpackage     eea-square-gateway
 * @author         Nazar Kolivoshka
 */
class Domain
{
    /**
     * Holds the Square API version used in this integration.
     */
    public const SQUARE_API_VERSION = '2024-01-18';

    public const CORE_VERSION_REQUIRED = '5.0.0.rc.002';

    public const LICENSE_PLUGIN_ID = 0;
    public const LICENSE_PLUGIN_NAME = 'Square Integration';
    public const LICENSE_PLUGIN_SLUG = 'eep-square';

    /*
     * Holds the name of the health check fail admin notice.
     */
    public const ADMIN_NOTICE_HEALTH_CHECK = 'eea_square_health_check_fail_notice';

    /*
     * Holds the name of the cron hook that does a health check.
     */
    public const CRON_EVENT_HEALTH_CHECK = 'AHEEA_Square_oauth_health_check';

    /**
     * Name of Extra Meta that stores the Square Application ID used in API calls.
     */
    public const META_KEY_APPLICATION_ID = 'application_id';

    /**
     * Name of the Extra Meta that stores the Square Access Token that is used to process payments.
     */
    public const META_KEY_ACCESS_TOKEN = 'access_token';

    /**
     * Name of the Extra Meta that stores the Square Refresh Token that is used to refresh the access token.
     */
    public const META_KEY_REFRESH_TOKEN = 'refresh_token';

    /**
     * Name of the Extra Meta that stores the expiration date of the access token.
     */
    public const META_KEY_EXPIRES_AT = 'expires_at';

    /**
     * Name of the Extra Meta that stores whether or not the merchant is authenticated.
     */
    public const META_KEY_USING_OAUTH = 'using_square_oauth';

    /**
     * Name of the Extra Meta that stores whether the credentials were for the Square sandbox or live mode.
     */
    public const META_KEY_LIVE_MODE = 'livemode';

    /**
     * Name of the Extra Meta that stores the Event Espresso Square Account's merchant id.
     */
    public const META_KEY_MERCHANT_ID = 'merchant_id';

    /**
     * Name of the Extra Meta that stores the Square account location ID.
     */
    public const META_KEY_LOCATION_ID = 'location_id';

    /**
     * Name of the Extra Meta key that stores the merchant locations list.
     */
    public const META_KEY_LOCATIONS_LIST = 'locations_list';

    /**
     * Name of the Extra Meta that stores the option for enabling the Square Digital Wallet (Google Pay and Apple Pay).
     */
    public const META_KEY_USE_DIGITAL_WALLET = 'use_dwallet';

    /**
     * Name of the Extra Meta that stores the option for the refresh token requests throttle window.
     */
    public const META_KEY_THROTTLE_TIME = 'throttle_time';

    /**
     * Name of the Extra Meta key that stores a few options as one meta (combined).
     * These are saved under this key:
     * refresh_token, expires_at, merchant_id, using_square_oauth, throttle_time, locations_list, permissions_scope.
     */
    public const META_KEY_SQUARE_DATA = 'square_data';

    /**
     * Name of the Extra Meta that stores the domain verification status.
     */
    public const META_KEY_DOMAIN_VERIFY = 'domain_verified';

    /**
     * Holds the name of the Register Domain button.
     */
    public const META_KEY_REG_DOMAIN_BUTTON = 'register_domain';

    /**
     * Name of the Extra Meta that stores the authenticated permissions scope.
     */
    public const META_KEY_PERMISSIONS = 'permissions_scope';

    /**
     * The default required permissions scope to be able to use all the API methods needed by this add-on.
     */
    public const PERMISSIONS_SCOPE_MINIMAL = 'PAYMENTS_WRITE PAYMENTS_READ ORDERS_WRITE ORDERS_READ MERCHANT_PROFILE_READ';

    /**
     * Customers API required permissions.
     */
    public const PERMISSIONS_SCOPE_CUSTOMERS = 'CUSTOMERS_READ CUSTOMERS_WRITE';

    /**
     * The default required permissions scope to be able to use all the API methods needed by this add-on.
     */
    public const PERMISSIONS_SCOPE_ALL = self::PERMISSIONS_SCOPE_MINIMAL . ' ' . self::PERMISSIONS_SCOPE_CUSTOMERS;
}
