<?php
$rate_file = "rates.json";
$wallet_file = "wallets.json";

// ডিফল্ট রেট
$default_rates = ['buy_rate' => 122.00, 'sell_rate' => 125.00];
if (!file_exists($rate_file)) {
    file_put_contents($rate_file, json_encode($default_rates));
}
$current_rates = json_decode(file_get_contents($rate_file), true);

// ডিফল্ট ওয়ালেট বা নম্বর
$default_wallets = [
    'Binance USDT' => 'TYz8... (Default USDT Address)',
    'Payeer USD' => 'P1092837456 (Default Payeer)',
    'Tron (TRX)' => 'TReceiverTronAddress99...',
    'Bkash Personal' => '01700000000',
    'Nagad Personal' => '01800000000',
    'Rocket Personal' => '01900000000'
];
if (!file_exists($wallet_file)) {
    file_put_contents($wallet_file, json_encode($default_wallets));
}
$current_wallets = json_decode(file_get_contents($wallet_file), true);

$msg = "";

// ফর্ম সাবমিট হলে আপডেট করার কোড
if (isset($_POST['update_admin'])) {
    // রেট আপডেট
    $current_rates['buy_rate'] = floatval($_POST['buy_rate']);
    $current_rates['sell_rate'] = floatval($_POST['sell_rate']);
    file_put_contents($rate_file, json_encode($current_rates));

    // ওয়ালেট ও নম্বর আপডেট
    $current_wallets['Binance USDT'] = $_POST['binance_usdt'];
    $current_wallets['Payeer USD'] = $_POST['payeer_usd'];
    $current_wallets['Tron (TRX)'] = $_POST['tron_trx'];
    $current_wallets['Bkash Personal'] = $_POST['bkash'];
    $current_wallets['Nagad Personal'] = $_POST['nagad'];
    $current_wallets['Rocket Personal'] = $_POST['rocket'];
    file_put_contents($wallet_file, json_encode($current_wallets));

    $msg = "সফলভাবে সমস্ত তথ্য ও রেট আপডেট করা হয়েছে!";
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - Admin Panel</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #2c3e50; color: #fff; margin: 0; padding: 20px; }
        .admin-container { max-width: 450px; margin: 30px auto; background: #34495e; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        h2, h3 { text-align: center; color: #f39c12; margin-top: 0; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; font-size: 13px; color: #ecf0f1; }
        input { width: 100%; padding: 10px; border: 1px solid #4e6d8d; border-radius: 6px; box-sizing: border-box; font-size: 14px; background: #2c3e50; color: white; }
        input:focus { border-color: #f39c12; outline: none; }
        .btn { display: block; width: 100%; padding: 12px; background: #e74c3c; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 15px; }
        .btn:hover { background: #c0392b; }
        .success-msg { color: #2ecc71; background: #27ae6033; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 14px; border: 1px solid #27ae60; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #3498db; text-decoration: none; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
        hr { border: 0; border-top: 1px solid #4e6d8d; margin: 20px 0; }
    </style>
</head>
<body>

    <div class="admin-container">
        <h2>⚙️ Admin Control Panel</h2>
        <?php if($msg != "") { echo "<div class='success-msg'>$msg</div>"; } ?>
        
        <form method="POST">
            <h3>ডলারের রেট</h3>
            <div class="form-group">
                <label>ডলার কেনার রেট (Buy Rate):</label>
                <input type="number" step="0.01" name="buy_rate" value="<?php echo $current_rates['buy_rate']; ?>" required>
            </div>
            <div class="form-group">
                <label>ডলার বিক্রির রেট (Sell Rate):</label>
                <input type="number" step="0.01" name="sell_rate" value="<?php echo $current_rates['sell_rate']; ?>" required>
            </div>

            <hr>
            <h3>অফিশিয়াল ওয়ালেট ও নম্বর</h3>
            
            <div class="form-group">
                <label>Binance USDT Address:</label>
                <input type="text" name="binance_usdt" value="<?php echo htmlspecialchars($current_wallets['Binance USDT']); ?>" required>
            </div>
            <div class="form-group">
                <label>Payeer USD Account:</label>
                <input type="text" name="payeer_usd" value="<?php echo htmlspecialchars($current_wallets['Payeer USD']); ?>" required>
            </div>
            <div class="form-group">
                <label>Tron (TRX) Address:</label>
                <input type="text" name="tron_trx" value="<?php echo htmlspecialchars($current_wallets['Tron (TRX)']); ?>" required>
            </div>
            <div class="form-group">
                <label>Bkash Personal Number:</label>
                <input type="text" name="bkash" value="<?php echo htmlspecialchars($current_wallets['Bkash Personal']); ?>" required>
            </div>
            <div class="form-group">
                <label>Nagad Personal Number:</label>
                <input type="text" name="nagad" value="<?php echo htmlspecialchars($current_wallets['Nagad Personal']); ?>" required>
            </div>
            <div class="form-group">
                <label>Rocket Personal Number:</label>
                <input type="text" name="rocket" value="<?php echo htmlspecialchars($current_wallets['Rocket Personal']); ?>" required>
            </div>

            <button type="submit" name="update_admin" class="btn">সব তথ্য আপডেট করুন</button>
        </form>
        
        <a href="index.php" class="back-link">← হোম পেজে ফিরে যান</a>
    </div>

</body>
</html>
