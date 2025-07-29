<?php
require 'config.php';

use NamaHealing\Controllers\LoginController;

$controller = new LoginController($db);
$controller->handle();
