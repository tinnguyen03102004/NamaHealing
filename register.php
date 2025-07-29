<?php
require 'config.php';

use NamaHealing\Controllers\RegisterController;

$controller = new RegisterController($db);
$controller->handle();
