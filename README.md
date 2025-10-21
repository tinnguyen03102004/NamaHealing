# NamaHealing Contributor Guide

## Dev Environment Tips
- Requires PHP 8.0 or higher.
- Run `composer install` to pull in PHP dependencies like `vlucas/phpdotenv`.
- Create a `.env` file and add `OPENAI_API_KEY=sk-proj-...` so `config.php` can load it.
- Add your Zoom Meeting SDK credentials to `.env` as `ZOOM_SDK_KEY` and `ZOOM_SDK_SECRET` (legacy names `ZOOM_SDK_CLIENT_ID` / `ZOOM_SDK_CLIENT_SECRET` are also supported) to enable the embedded classroom experience.
- Ensure your production domain is added to the allow list of the Zoom Meeting SDK app so the embedded client can load.
- Launch a local server from the project root with `php -S localhost:8000` to preview pages.
- `chatbot.php` and `chatgptapi.php` will read the API key automatically for OpenAI requests.

## Testing Instructions
- Start the local server (`php -S localhost:8000`) in another terminal; route tests expect it.
- Run all tests with `./vendor/bin/phpunit`.
- Fix any test failures and extend the suite for any code you modify.

## PR instructions
Title format: [NamaHealing] <Title>
