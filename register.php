<?php
require 'config.php';

use NamaHealing\Controllers\SelfRegisterController;

$controller = new SelfRegisterController($db);
$controller->handle();
