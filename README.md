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

The key will be loaded automatically by `config.php` when using `chatbot.php` or `chatbot_api.php`.

## Database Configuration

The application expects a MySQL database named `zoom_class` running on
`localhost`. Database connection details are defined at the top of
`config.php`:

```php
try {
    $db = new PDO('mysql:host=localhost;dbname=zoom_class;charset=utf8', 'root', '');
    // Nếu bạn đặt mật khẩu cho tài khoản `root` hãy điền vào tham số cuối cùng
} catch (PDOException $e) {
    die("Kết nối DB lỗi: " . $e->getMessage());
}
```

Update the username, password or database name here to match your local
environment before running the project.
