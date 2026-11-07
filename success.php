<?php
session_start();
$order_id = $_POST['order_id'] ?? 'SK-000000';
$send_currency = $_POST['send_currency'] ?? '';
$receive_currency = $_POST['receive_currency'] ?? '';
$amount = $_POST['amount'] ?? '';
$user_name = $_POST['user_name'] ?? '';
$user_account = $_POST['user_account'] ?? '';
$trx_id = $_POST['trx_id'] ?? '';
$user_email = $_SESSION['user_email'] ?? 'guest';
$order_time = date('Y-m-d H:i:s');

$order_file = "orders.json";
$orders = file_exists($order_file) ? json_decode(file_get_contents($order_file), true) : [];

$new_order = [
    'id' => $order_id,
    'user_email' => $user_email,
    'send_currency' => $send_currency,
    'receive_currency' => $receive_currency,
    'amount' => $amount,
    'user_name' => $user_name,
    'user_account' => $user_account,
    'trx_id' => $trx_id,
    'time' => $order_time,
    'status' => 'Pending'
];

array_unshift($orders, $new_order);
file_put_contents($order_file, json_encode($orders, JSON_PRETTY_PRINT));
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - Order Success</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #0f172a; color: #f8fafc; }
        .container { padding: 30px; max-width: 420px; margin: 40px auto; background: rgba(30, 41, 59, 0.9); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); border: 1px solid #334155; text-align: center; }
        .success-icon { font-size: 45px; color: #34d399; margin-bottom: 10px; }
        h3 { color: #34d399; margin-top: 0; font-size: 20px; }
        p { color: #cbd5e1; font-size: 13px; line-height: 1.5; }
        .details-box { background: rgba(15, 23, 42, 0.9); padding: 12px; border-radius: 8px; text-align: left; margin: 15px 0; font-size: 13px; border: 1px solid #334155; }
        .details-box p { margin: 5px 0; color: #f8fafc; }
        .home-btn { display: inline-block; width: 100%; padding: 12px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: bold; margin-top: 10px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✅</div>
        <h3>অর্ডার সফলভাবে জমা হয়েছে!</h3>
        <p>আপনার এক্সচেঞ্জ অনুরোধটি গ্রহণ করা হয়েছে।</p>
        <div class="details-box">
            <p><b>অর্ডার আইডি:</b> <span style="color:#f59e0b;"><?php echo $order_id; ?></span></p>
            <p><b>সময় ও তারিখ:</b> <?php echo $order_time; ?></p>
            <p><b>নাম:</b> <?php echo htmlspecialchars($user_name); ?></p>
            <p><b>পাঠাবেন:</b> <?php echo htmlspecialchars($send_currency); ?></p>
            <p><b>পাবেন:</b> <?php echo htmlspecialchars($receive_currency); ?></p>
            <p><b>পরিমাণ:</b> <?php echo htmlspecialchars($amount); ?></p>
            <p><b>TrxID:</b> <span style="color:#f59e0b;"><?php echo htmlspecialchars($trx_id); ?></span></p>
            <p><b>স্ট্যাটাস:</b> <span style="color:#38bdf8;">Pending</span></p>
        </div>
        <a href="index.php" class="home-btn">হোম পেজে ফিরে যান</a>
    </div>
</body>
</html>
