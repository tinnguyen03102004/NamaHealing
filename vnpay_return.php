<?php
require 'config.php';

$status = ($_GET['vnp_ResponseCode'] ?? '') === '00' ? 'success' : 'fail';

include __DIR__ . '/views/payment_result.php';

