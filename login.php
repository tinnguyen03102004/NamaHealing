<?php

use NamaHealing\Controllers\LoginController;

require __DIR__ . '/config.php';

$controller = new LoginController($db);
$controller->handle();

