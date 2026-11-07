<?php
session_start();
$rate_file = "rates.json";
$wallet_file = "wallets.json";
$order_file = "orders.json";
$user_file = "users.json";

$admin_email = "skriyad01967@gmail.com";
$admin_pass = "Skriyad@5566";
$msg = ""; $error = "";

if (isset($_POST['login'])) {
    if ($_POST['email'] === $admin_email && $_POST['password'] === $admin_pass) {
        $_SESSION['admin_logged'] = true;
    } else {
        $error = "ভুল ইমেইল অথবা পাসওয়ার্ড!";
    }
}
if (isset($_GET['logout'])) { unset($_SESSION['admin_logged']); header("Location: sk-bot-pay.php"); exit(); }

if (isset($_SESSION['admin_logged']) && isset($_GET['action']) && isset($_GET['id'])) {
    $orders = file_exists($order_file) ? json_decode(file_get_contents($order_file), true) : [];
    foreach ($orders as &$ord) {
        if ($ord['id'] === $_GET['id']) {
            if ($_GET['action'] === 'complete') $ord['status'] = 'Completed (সম্পন্ন)';
            if ($_GET['action'] === 'cancel') $ord['status'] = 'Cancelled (বাতিল)';
        }
    }
    file_put_contents($order_file, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    header("Location: sk-bot-pay.php"); exit();
}

$current_rates = file_exists($rate_file) ? json_decode(file_get_contents($rate_file), true) : ['buy_rate' => 122.00, 'sell_rate' => 125.00];
$current_wallets = file_exists($wallet_file) ? json_decode(file_get_contents($wallet_file), true) : ['notice' => 'স্বাগতম!'];

if (isset($_SESSION['admin_logged']) && isset($_POST['update_admin'])) {
    $current_rates['buy_rate'] = floatval($_POST['buy_rate']);
    $current_rates['sell_rate'] = floatval($_POST['sell_rate']);
    file_put_contents($rate_file, json_encode($current_rates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $current_wallets['notice'] = $_POST['notice'];
    $current_wallets['whatsapp'] = $_POST['whatsapp'];
    $current_wallets['Binance USDT'] = $_POST['binance_usdt'];
    $current_wallets['Payeer USD'] = $_POST['payeer_usd'];
    $current_wallets['Epay USD'] = $_POST['epay_usd'];
    $current_wallets['Tron (TRX)'] = $_POST['tron_trx'];
    $current_wallets['Bkash Personal'] = $_POST['bkash'];
    $current_wallets['Nagad Personal'] = $_POST['nagad'];
    $current_wallets['Rocket Personal'] = $_POST['rocket'];
    
    // ফেক পপ-আপ টিকার কন্ট্রোল ডাটা সেভ
    $current_wallets['ticker_names'] = $_POST['ticker_names'];
    $current_wallets['ticker_actions'] = $_POST['ticker_actions'];
    $current_wallets['ticker_interval'] = (int)$_POST['ticker_interval'];

    file_put_contents($wallet_file, json_encode($current_wallets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $msg = "সফলভাবে আপডেট করা হয়েছে!";
}

$orders = file_exists($order_file) ? json_decode(file_get_contents($order_file), true) : [];
$users = file_exists($user_file) ? json_decode(file_get_contents($user_file), true) : [];

// লাইভ USD মার্কেট রেট (API থেকে ফেচ করা)
$live_usd_bdt = "N/A";
$api_data = @file_get_contents("https://open.er-api.com/v6/latest/USD");
if ($api_data) {
    $json_res = json_decode($api_data, true);
    if (isset($json_res['rates']['BDT'])) {
        $live_usd_bdt = number_format($json_res['rates']['BDT'], 2) . " ৳";
    }
}

$total_users = count($users);
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sk Bot Pay - Secure Admin Panel</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f172a; color: #f8fafc; margin: 0; padding: 20px; }
        .admin-container { max-width: 950px; margin: 30px auto; background: #1e293b; padding: 25px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); border: 1px solid #334155; }
        h2, h3 { text-align: center; color: #38bdf8; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; font-size: 13px; color: #cbd5e1; font-weight: 600; }
        input, textarea { width: 100%; padding: 12px; border: 1px solid #475569; border-radius: 8px; box-sizing: border-box; font-size: 14px; background: #0f172a; color: white; }
        .btn { display: block; width: 100%; padding: 13px; background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        .success-msg { color: #34d399; background: rgba(52,211,153,0.1); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px; border: 1px solid #34d399; }
        .error-msg { color: #f87171; background: rgba(248,113,113,0.1); padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px; border: 1px solid #f87171; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 12px; background: #0f172a; border-radius: 8px; overflow: hidden; }
        th, td { padding: 8px; border: 1px solid #334155; text-align: center; }
        th { background: #334155; color: #38bdf8; }
        .action-btn { padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: bold; margin: 2px; display: inline-block; }
        .complete-btn { background: #10b981; color: white; }
        .cancel-btn { background: #ef4444; color: white; }
        .copy-btn { background: #3b82f6; color: white; border: none; padding: 4px 6px; border-radius: 4px; cursor: pointer; font-size: 11px; margin-top: 4px; }
        .search-box { margin-bottom: 15px; }
        .live-rate-box { background: linear-gradient(135deg, #064e3b, #0f172a); border: 1px solid #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .stats-badge { background: #334155; color: #38bdf8; padding: 8px 15px; border-radius: 6px; display: inline-block; font-weight: bold; font-size: 14px; margin-bottom: 15px; }
        hr { border: 0; border-top: 1px solid #334155; margin: 25px 0; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php if (!isset($_SESSION['admin_logged'])): ?>
            <h2>🔐 Sk Bot Pay</h2>
            <?php if($error != "") { echo "<div class='error-msg'>$error</div>"; } ?>
            <form method="POST">
                <div class="form-group"><label>ইমেইল:</label><input type="email" name="email" required></div>
                <div class="form-group"><label>পাসওয়ার্ড:</label><input type="password" name="password" required></div>
                <button type="submit" name="login" class="btn" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">লগইন করুন</button>
            </form>
            <a href="index.php" style="display:block; text-align:center; margin-top:15px; color:#38bdf8; text-decoration:none;">← হোম পেজে ফিরে যান</a>
        <?php else: ?>
            <h2>⚙️ Sk Bot Pay Control Panel</h2>
            <div style="text-align: right; margin-bottom: 15px;"><a href="sk-bot-pay.php?logout=true" style="color: #f87171; font-size: 12px; text-decoration: none; font-weight: bold;">লগআউট 🚪</a></div>
            
            <!-- আন্তর্জাতিক লাইভ ডলার রেট বক্স -->
            <div class="live-rate-box">
                <h4 style="margin:0; color:#34d399; font-size:13px;">🌐 আন্তর্জাতিক মার্কেট লাইভ USD ➔ BDT রেট</h4>
                <p style="margin:5px 0 0; font-size:22px; font-weight:bold; color:#fff;"><?php echo $live_usd_bdt; ?></p>
                <small style="color:#94a3b8; font-size:11px;">(এটি শুধু এডমিন প্যানেলেই দেখা যাচ্ছে)</small>
            </div>

            <?php if($msg != "") { echo "<div class='success-msg'>$msg</div>"; } ?>
            
            <form method="POST">
                <h3>ডলারের রেট, নোটিশ ও ফেক পপ-আপ কন্ট্রোল</h3>
                <div class="form-group"><label>ডলার কেনার রেট:</label><input type="number" step="0.01" name="buy_rate" value="<?php echo $current_rates['buy_rate']; ?>" required></div>
                <div class="form-group"><label>ডলার বিক্রির রেট:</label><input type="number" step="0.01" name="sell_rate" value="<?php echo $current_rates['sell_rate']; ?>" required></div>
                <div class="form-group"><label>রানিং নোটিশ:</label><textarea name="notice" rows="2"><?php echo htmlspecialchars($current_wallets['notice'] ?? ''); ?></textarea></div>

                <hr>
                <h3>🔔 ফেক লাইভ পপ-আপ টিকার কন্ট্রোল</h3>
                <div class="form-group">
                    <label>ইউজারদের নাম (কমা দিয়ে লিখুন):</label>
                    <input type="text" name="ticker_names" value="<?php echo htmlspecialchars($current_wallets['ticker_names'] ?? 'রহিম, করিম, ফাহিম, সাকিব, রিয়াদ'); ?>">
                </div>
                <div class="form-group">
                    <label>অ্যাকশন বা কাজসমূহ (কমা দিয়ে লিখুন):</label>
                    <input type="text" name="ticker_actions" value="<?php echo htmlspecialchars($current_wallets['ticker_actions'] ?? 'Binance USDT বিক্রি করে বিকাশ নিলেন, Payeer USD এক্সচেঞ্জ করলেন, নগদ দিয়ে USDT কিনলেন'); ?>">
                </div>
                <div class="form-group">
                    <label>কত মিনিট পরপর পপ-আপ ভেসে উঠবে (Interval in Minutes):</label>
                    <input type="number" name="ticker_interval" value="<?php echo $current_wallets['ticker_interval'] ?? 1; ?>" min="1" required>
                </div>

                <hr>
                <h3>ওয়ালেট ও নম্বর</h3>
                <div class="form-group"><label>WhatsApp:</label><input type="text" name="whatsapp" value="<?php echo htmlspecialchars($current_wallets['whatsapp'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Binance USDT:</label><input type="text" name="binance_usdt" value="<?php echo htmlspecialchars($current_wallets['Binance USDT'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Payeer USD:</label><input type="text" name="payeer_usd" value="<?php echo htmlspecialchars($current_wallets['Payeer USD'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Epay USD:</label><input type="text" name="epay_usd" value="<?php echo htmlspecialchars($current_wallets['Epay USD'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Tron (TRX):</label><input type="text" name="tron_trx" value="<?php echo htmlspecialchars($current_wallets['Tron (TRX)'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Bkash:</label><input type="text" name="bkash" value="<?php echo htmlspecialchars($current_wallets['Bkash Personal'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Nagad:</label><input type="text" name="nagad" value="<?php echo htmlspecialchars($current_wallets['Nagad Personal'] ?? ''); ?>" required></div>
                <div class="form-group"><label>Rocket:</label><input type="text" name="rocket" value="<?php echo htmlspecialchars($current_wallets['Rocket Personal'] ?? ''); ?>" required></div>
                <button type="submit" name="update_admin" class="btn">সব তথ্য আপডেট করুন</button>
            </form>

            <hr>
            <h3>👥 রেজিস্টার্ড ইউজার তালিকা</h3>
            <div class="stats-badge">📌 মোট রেজিস্টার্ড ইউজার: <?php echo $total_users; ?> জন</div>
            <div style="overflow-x:auto;">
                <table>
                    <tr><th>নাম</th><th>ইমেইল</th><th>ফোন</th><th>পাসওয়ার্ড</th><th>সময়</th></tr>
                    <?php foreach($users as $usr): ?>
                    <tr>
                        <td><?php echo htmlspecialchars(($usr['first_name'] ?? '').' '.($usr['last_name'] ?? '')); ?></td>
                        <td><?php echo htmlspecialchars($usr['email'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($usr['phone'] ?? ''); ?></td>
                        <td style="color:#f59e0b;"><?php echo htmlspecialchars($usr['password'] ?? ''); ?></td>
                        <td><?php echo $usr['time'] ?? ''; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <hr>
            <h3>📥 লাইভ অর্ডার লিস্ট ও সার্চ সিস্টেম</h3>
            
            <!-- রিয়েল-টাইম সার্চ বক্স -->
            <div class="search-box">
                <input type="text" id="orderSearchInput" onkeyup="searchOrders()" placeholder="🔍 অর্ডার আইডি বা TrxID দিয়ে সার্চ করুন...">
            </div>

            <div style="overflow-x:auto;">
                <table id="orderTable">
                    <tr><th>অর্ডার আইডি</th><th>সময়</th><th>ইউজার নাম</th><th>লেনদেন</th><th>পরিমাণ</th><th>অ্যাকাউন্ট/Address</th><th>TrxID</th><th>স্ট্যাটাস</th><th>অ্যাকশন</th></tr>
                    <?php foreach($orders as $ord): ?>
                    <tr>
                        <td style="color:#f59e0b; font-weight:bold;"><?php echo $ord['id'] ?? ''; ?></td>
                        <td><?php echo $ord['time'] ?? ''; ?></td>
                        <td><?php echo htmlspecialchars($ord['user_name'] ?? ''); ?></td>
                        <td><?php echo ($ord['send_currency'] ?? '').' ➔ '.($ord['receive_currency'] ?? ''); ?></td>
                        <td><?php echo $ord['amount'] ?? ''; ?></td>
                        <td>
                            <span id="acc-<?php echo $ord['id'] ?? ''; ?>"><?php echo htmlspecialchars($ord['user_account'] ?? ''); ?></span>
                            <br><button class="copy-btn" onclick="copyText('acc-<?php echo $ord['id'] ?? ''; ?>')">📋 কপি</button>
                        </td>
                        <td style="color:#f59e0b;">
                            <span id="trx-<?php echo $ord['id'] ?? ''; ?>"><?php echo htmlspecialchars($ord['trx_id'] ?? ''); ?></span>
                            <br><button class="copy-btn" onclick="copyText('trx-<?php echo $ord['id'] ?? ''; ?>')">📋 কপি</button>
                        </td>
                        <td><b style="color:#38bdf8;"><?php echo $ord['status'] ?? ''; ?></b></td>
                        <td>
                            <a href="sk-bot-pay.php?action=complete&id=<?php echo $ord['id'] ?? ''; ?>" class="action-btn complete-btn">Complete</a>
                            <a href="sk-bot-pay.php?action=cancel&id=<?php echo $ord['id'] ?? ''; ?>" class="action-btn cancel-btn">Cancel</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <a href="index.php" style="display:block; text-align:center; margin-top:25px; color:#38bdf8; text-decoration:none;">← হোম পেজে ফিরে যান</a>
        <?php endif; ?>
    </div>
    <script>
        function copyText(containerId) {
            var text = document.getElementById(containerId).innerText;
            navigator.clipboard.writeText(text).then(function() {
                alert('সফলভাবে কপি হয়েছে!');
            });
        }

        // লাইভ সার্চ ফাংশন
        function searchOrders() {
            var input = document.getElementById("orderSearchInput");
            var filter = input.value.toUpperCase();
            var table = document.getElementById("orderTable");
            var tr = table.getElementsByTagName("tr");

            for (var i = 1; i < tr.length; i++) {
                var tdId = tr[i].getElementsByTagName("td")[0]; // অর্ডার আইডি কলাম
                var tdTrx = tr[i].getElementsByTagName("td")[6]; // TrxID কলাম
                if (tdId || tdTrx) {
                    var idValue = tdId.textContent || tdId.innerText;
                    var trxValue = tdTrx.textContent || tdTrx.innerText;
                    if (idValue.toUpperCase().indexOf(filter) > -1 || trxValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>
