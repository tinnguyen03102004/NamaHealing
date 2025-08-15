<?php
require 'config.php';

use NamaHealing\Controllers\ForgotPasswordController;

$controller = new ForgotPasswordController($db);
$controller->handle();
