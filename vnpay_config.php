<?php
// VNPay configuration is kept separate for easy switching between sandbox and production.
// Values can be overridden by environment variables when deploying.
return [
    'vnp_TmnCode'   => $_ENV['VNP_TMN_CODE']   ?? 'VNPAYDEMO',
    'vnp_HashSecret'=> $_ENV['VNP_HASH_SECRET']?? 'SECRETKEY',
    'vnp_Url'       => $_ENV['VNP_URL']        ?? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    // URL VNPay redirects students to after payment; adjust in production
    'vnp_ReturnUrl' => $_ENV['VNP_RETURN_URL'] ?? 'http://localhost/vnpay_return.php',
];
