# NamaHealing

## Chatbot Feature

Before running the project for the first time, install the PHP dependencies:

```
composer install
```

This installs packages such as `vlucas/phpdotenv` so that `config.php` can load
the `.env` file and read the `OPENAI_API_KEY` variable.

The chatbot uses the OpenAI API. You can place your API key in a `.env` file:

```
OPENAI_API_KEY=sk-proj-...
```

The key will be loaded automatically by `config.php` when using `chatbot.php` or `chatgptapi.php`.
