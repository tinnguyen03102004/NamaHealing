<?php
require 'config.php';

use NamaHealing\Controllers\ForgotPasswordController;

// Hiển thị form quên mật khẩu sử dụng controller mới
$controller = new ForgotPasswordController();
$controller->forgotForm();
