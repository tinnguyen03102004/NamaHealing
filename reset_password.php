<?php
require 'config.php';

use NamaHealing\Controllers\ResetPasswordController;

$controller = new ResetPasswordController($db);
$controller->handle();
