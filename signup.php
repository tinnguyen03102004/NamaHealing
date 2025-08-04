<?php
require 'config.php';

use NamaHealing\Models\OrderModel;

$order = $_SESSION['order'] ?? null;

// Step 2: create VNPay payment URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_vnpay'])) {
    csrf_check($_POST['csrf_token'] ?? null);
    $order = $_SESSION['order'] ?? null;
    if (!$order) {
        header('Location: signup.php');
        exit;
    }

    $config = require __DIR__ . '/vnpay_config.php';
    $vnp_Url = $config['vnp_Url'];
    $vnp_Returnurl = $config['vnp_ReturnUrl'];
    $vnp_TmnCode = $config['vnp_TmnCode'];
    $vnp_HashSecret = $config['vnp_HashSecret'];

    $inputData = [
        'vnp_Version'   => '2.1.0',
        'vnp_TmnCode'   => $vnp_TmnCode,
        'vnp_Amount'    => $order['amount'] * 100,
        'vnp_Command'   => 'pay',
        'vnp_CreateDate'=> date('YmdHis'),
        'vnp_CurrCode'  => 'VND',
        'vnp_IpAddr'    => $_SERVER['REMOTE_ADDR'],
        'vnp_Locale'    => 'vn',
        'vnp_OrderInfo' => 'Thanh toan khoa thien NamaHealing',
        'vnp_OrderType' => 'billpayment',
        'vnp_ReturnUrl' => $vnp_Returnurl,
        'vnp_TxnRef'    => $order['txnRef'],
    ];

    ksort($inputData);
    $query = http_build_query($inputData);
    $hashdata = urldecode($query);
    $secureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $paymentUrl = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $secureHash;
    header('Location: ' . $paymentUrl);
    exit;
}

// Step 1: handle form submission and show confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    csrf_check($_POST['csrf_token'] ?? null);
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $sessions = max(1, (int)($_POST['sessions'] ?? 0));
    $pricePer = (int)($_ENV['SESSION_PRICE'] ?? 100000); // VND per session
    $amount   = $sessions * $pricePer;
    $txnRef   = (string)time();

    $model = new OrderModel($db);
    $model->create($txnRef, $name, $email, $sessions, $amount);

    $_SESSION['order'] = $order = [
        'txnRef'   => $txnRef,
        'full_name'=> $name,
        'email'    => $email,
        'sessions' => $sessions,
        'amount'   => $amount,
    ];
    include __DIR__ . '/views/confirm_order.php';
    exit;
}

include __DIR__ . '/views/signup_form.php';

