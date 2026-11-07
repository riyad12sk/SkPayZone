<?php
session_start();
if (!isset($_SESSION['user_logged'])) { header("Location: login.php"); exit(); }
$user_email = $_SESSION['user_email'];
$order_file = "orders.json";
$orders = file_exists($order_file) ? json_decode(file_get_contents($order_file), true) : [];
$my_orders = array_filter($orders, function($o) use ($user_email) {
    return isset($o['user_email']) && $o['user_email'] === $user_email;
});
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - Exchange History</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #0f172a; color: #f8fafc; }
        .container { max-width: 600px; margin: 30px auto; background: #1e293b; padding: 20px; border-radius: 12px; border: 1px solid #334155; }
        h2 { text-align: center; color: #38bdf8; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 12px; background: #0f172a; border-radius: 8px; overflow: hidden; }
        th, td { padding: 10px; border: 1px solid #334155; text-align: center; }
        th { background: #334155; color: #38bdf8; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #38bdf8; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>📜 আপনার এক্সচেঞ্জ হিস্ট্রি</h2>
        <table>
            <tr><th>অর্ডার আইডি</th><th>সময়</th><th>লেনদেন</th><th>পরিমাণ</th><th>স্ট্যাটাস</th></tr>
            <?php if(empty($my_orders)): ?>
                <tr><td colspan="5" style="color:#94a3b8;">আপনার কোনো লেনদেন নেই</td></tr>
            <?php else: ?>
                <?php foreach($my_orders as $mo): ?>
                <tr>
                    <td style="color:#f59e0b;"><?php echo $mo['id']; ?></td>
                    <td><?php echo $mo['time']; ?></td>
                    <td><?php echo $mo['send_currency']; ?> ➔ <?php echo $mo['receive_currency']; ?></td>
                    <td><?php echo $mo['amount']; ?></td>
                    <td><b style="color:#34d399;"><?php echo $mo['status']; ?></b></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
        <a href="index.php" class="back-link">← হোম পেজে ফিরে যান</a>
    </div>
</body>
</html>
