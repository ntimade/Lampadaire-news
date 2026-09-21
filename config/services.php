<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'gemini' => [
        // Default/fallback key — used for any category without its own
        // dedicated key below, and while you're still creating the others.
        'key' => env('GEMINI_API_KEY'),

        // One free-tier API key per rubrique (each from a separate Google
        // Cloud project) so conversions spread across independent quotas
        // instead of sharing a single one. Add keys to .env as you create
        // them — a category left blank here just uses the default key
        // above, nothing breaks.
        'keys_by_category' => [
            'culture' => env('GEMINI_API_KEY_CULTURE'),
            'finance-et-bourse' => env('GEMINI_API_KEY_FINANCE_ET_BOURSE'),
            'business' => env('GEMINI_API_KEY_BUSINESS'),
            'sante-prevention' => env('GEMINI_API_KEY_SANTE_PREVENTION'),
            'politique-faits-divers' => env('GEMINI_API_KEY_POLITIQUE_FAITS_DIVERS'),
            'international' => env('GEMINI_API_KEY_INTERNATIONAL'),
            'sport' => env('GEMINI_API_KEY_SPORT'),
            'religion-spiritualite' => env('GEMINI_API_KEY_RELIGION_SPIRITUALITE'),
        ],

        // Dedicated key for cleaning up article text for the printable PDF
        // newspaper (see GeminiArticleWriter::writeForPrint) — kept separate
        // from the per-category keys above so it always has its own 20/day
        // quota, independent of the RSS conversion pipeline.
        'key_journal' => env('GEMINI_API_KEY_JOURNAL'),
    ],

    'facebook' => [
        'page_id' => env('FACEBOOK_PAGE_ID'),
        // A Page access token derived from a short-lived user token expires
        // with it (a couple of hours) — see PostDailyFacebookUpdates' class
        // doc for how to get a durable one (long-lived user token exchange
        // via the app secret, then re-derive the Page token from that).
        'page_access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
    ],

];
