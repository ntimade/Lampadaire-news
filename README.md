# Le Lampadaire

AI-assisted online news platform, built on top of a Laravel news portal base and extended with automation.

## Features

- News site with categories, tags, comments, ads and newsletter subscribers
- Admin panel with roles and permissions
- Breaking (urgent) news and a printable newspaper edition
- Multi-language support
- Automated news collection from configured sources
- AI article writing with Google Gemini and automatic publishing
- Daily publishing to a Facebook page through the Graph API

## Tech stack

Laravel, PHP, Blade templates, MySQL, Google Gemini API, Facebook Graph API.

## Getting started

    composer install
    npm install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate --seed

Set your Gemini and Facebook credentials in .env. Never commit them.