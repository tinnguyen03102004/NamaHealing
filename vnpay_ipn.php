<?php
require 'config.php';

use NamaHealing\Models\OrderModel;
use NamaHealing\Models\UserModel;
use NamaHealing\Helpers\Mailer;

$config = require __DIR__ . '/vnpay_config.php';
$vnp_HashSecret = $config['vnp_HashSecret'];

$inputData = [];
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == 'vnp_') {
        $inputData[$key] = $value;
    }
}

$secureHash = $inputData['vnp_SecureHash'] ?? '';
unset($inputData['vnp_SecureHash']);
unset($inputData['vnp_SecureHashType']);
ksort($inputData);
$hashData = urldecode(http_build_query($inputData));
$checkHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

$logFile = __DIR__ . '/data/vnpay.log';
file_put_contents($logFile, date('c') . ' ' . json_encode($_GET) . PHP_EOL, FILE_APPEND);

$returnData = ['RspCode' => '97', 'Message' => 'Invalid signature'];
if ($checkHash === $secureHash) {
    $orderId = $inputData['vnp_TxnRef'] ?? '';
    $responseCode = $inputData['vnp_ResponseCode'] ?? '';
    $returnData = ['RspCode' => '00', 'Message' => 'Confirm Success'];

    if ($responseCode === '00') {
        $orderModel = new OrderModel($db);
        $order = $orderModel->markPaid($orderId);
        if ($order && $order['status'] === 'paid') {
            $userModel = new UserModel($db);
            $user = $userModel->findByIdentifier($order['email']) ?? $userModel->findByIdentifier($order['phone']);
            $newPass = null;
            if (!$user) {
                $newPass = bin2hex(random_bytes(4));
                $userId = $userModel->createStudent(
                    $order['full_name'],
                    $order['email'],
                    $order['phone'],
                    $newPass,
                    (int)$order['sessions']
                );
            } else {
                $userModel->addSessions((int)$user['id'], (int)$order['sessions']);
            }

            // send email
            $body = 'Cảm ơn bạn đã đăng ký lớp học. Đăng nhập tại ' .
                ($_ENV['APP_URL'] ?? '') . '/login.php';
            if ($newPass !== null) {
                $body .= '\nMật khẩu của bạn: ' . $newPass;
            }
            Mailer::send($order['email'], 'Xác nhận đăng ký NamaHealing', $body);

            // Optional chatbot hook
            if (!empty($_ENV['CHATBOT_WEBHOOK'])) {
                @file_get_contents($_ENV['CHATBOT_WEBHOOK'] . '?email=' . urlencode($order['email']));
            }
        } else {
            $returnData = ['RspCode' => '02', 'Message' => 'Order already confirmed'];
        }
    } else {
        $returnData = ['RspCode' => '00', 'Message' => 'Payment Failed'];
    }
}

echo json_encode($returnData);

