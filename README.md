# NamaHealing Contributor Guide

## Dev Environment Tips
- Run `composer install` to pull in PHP dependencies like `vlucas/phpdotenv`.
- Create a `.env` file and add `OPENAI_API_KEY=sk-proj-...` so `config.php` can load it.
- Launch a local server from the project root with `php -S localhost:8000` to preview pages.
- `chatbot.php` and `chatgptapi.php` will read the API key automatically for OpenAI requests.

## Testing Instructions
- Start the local server (`php -S localhost:8000`) in another terminal; route tests expect it.
- Run all tests with `./vendor/bin/phpunit`.
- Fix any test failures and extend the suite for any code you modify.

## PR instructions
Title format: [NamaHealing] <Title>
