<?php
session_start();
if (!isset($_SESSION['user_logged']) || $_SESSION['user_logged'] !== true) {
    echo "<script>alert('দয়া করে প্রথমে লগইন বা সাইন আপ করুন!'); window.location.href='login.php';</script>";
    exit();
}

$send_currency = $_POST['send_currency'] ?? 'Binance USDT';
$receive_currency = $_POST['receive_currency'] ?? 'Bkash Personal';
$amount = $_POST['amount'] ?? 0;
$user_email = $_SESSION['user_email'] ?? 'unknown';

$wallet_file = "wallets.json";
$company_wallets = file_exists($wallet_file) ? json_decode(file_get_contents($wallet_file), true) : [];
$target_wallet = $company_wallets[$send_currency] ?? 'অফিশিয়াল নম্বর বা অ্যাড্রেস পাওয়া যায়নি';

// ইউনিক অর্ডার আইডি জেনারেট (যেমন: SK-839201)
$order_id = "SK-" . strtoupper(substr(uniqid(), -6));
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - Order Details</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #0f172a; color: #f8fafc; position: relative; overflow-x: hidden; }
        @keyframes waterWave { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #0f172a, #1e1b4b, #0f172a, #022c22); background-size: 400% 400%; animation: waterWave 12s ease infinite; z-index: -1; opacity: 0.8; }
        header { background: rgba(30, 41, 59, 0.9); backdrop-filter: blur(10px); color: #fff; padding: 15px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .container { padding: 25px; max-width: 420px; margin: 25px auto; background: rgba(30, 41, 59, 0.85); backdrop-filter: blur(10px); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); }
        h3 { text-align: center; color: #38bdf8; margin-top: 0; }
        .info-box { background: rgba(15, 23, 42, 0.9); padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; border: 1px solid #334155; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px; color: #cbd5e1; }
        input { width: 100%; padding: 11px; border: 1px solid #475569; border-radius: 8px; box-sizing: border-box; font-size: 14px; background: rgba(15, 23, 42, 0.9); color: white; }
        
        .wallet-box-flex { display: flex; align-items: center; justify-content: space-between; background: rgba(15, 23, 42, 0.9); border: 1px solid #475569; padding: 10px 12px; border-radius: 8px; margin-top: 5px; }
        .wallet-text { font-size: 13px; font-weight: bold; color: #f87171; word-break: break-all; margin-right: 10px; }
        .copy-btn { background: #3b82f6; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold; white-space: nowrap; }

        @keyframes magicBorder { 0% { border-color: #ff0055; box-shadow: 0 0 15px #ff0055; } 33% { border-color: #00ffcc; box-shadow: 0 0 15px #00ffcc; } 66% { border-color: #ffff00; box-shadow: 0 0 15px #ffff00; } 100% { border-color: #ff0055; box-shadow: 0 0 15px #ff0055; } }
        .submit-btn { display: block; width: 100%; padding: 13px; background: #0f172a; color: #fff; border: 3px solid #ff0055; border-radius: 8px; text-align: center; font-size: 16px; font-weight: bold; margin-top: 20px; cursor: pointer; animation: magicBorder 3s infinite; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #94a3b8; text-decoration: none; font-size: 13px; }

        .water-ripple { position: fixed; border-radius: 50%; border: 2px solid rgba(0, 255, 204, 0.8); background: rgba(255, 0, 85, 0.3); transform: scale(0); animation: waterRippleAnim 0.8s cubic-bezier(0, 0.2, 0.8, 1) forwards; pointer-events: none; z-index: 9999; }
        @keyframes waterRippleAnim { 0% { width: 0px; height: 0px; opacity: 1; transform: translate(-50%, -50%) scale(0); border-color: #00ffcc; background: rgba(255, 0, 85, 0.4); } 50% { border-color: #ffff00; background: rgba(0, 255, 204, 0.2); } 100% { width: 180px; height: 180px; opacity: 0; transform: translate(-50%, -50%) scale(1); border-color: #ff0055; background: rgba(255, 255, 0, 0); } }
    </style>
</head>
<body>
    <header>
        <h2>SkPayZone</h2>
        <p style="margin:0; font-size:11px; color:#94a3b8;">অর্ডার কনফার্মেশন পেজ</p>
    </header>
    <div class="container">
        <h3>এক্সচেঞ্জ বিবরণী</h3>
        <div class="info-box">
            <p style="margin:4px 0;">অর্ডার আইডি: <b style="color:#f59e0b;"><?php echo $order_id; ?></b></p>
            <p style="margin:4px 0;">পাঠাবেন: <b style="color:#38bdf8;"><?php echo htmlspecialchars($send_currency); ?></b></p>
            <p style="margin:4px 0;">পাবেন: <b style="color:#34d399;"><?php echo htmlspecialchars($receive_currency); ?></b></p>
            <p style="margin:4px 0;">পরিমাণ: <b style="color:#f59e0b;"><?php echo htmlspecialchars($amount); ?></b></p>
        </div>

        <div style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 12px; color: #fbbf24;">
            <b>নির্দেশনা:</b> নিচের অ্যাকাউন্টে আপনার পরিমাণ টাকা বা ডলার পাঠিয়ে দিন।<br><br>
            <b>আমাদের ওয়ালেট/নম্বর (কপি করুন):</b>
            <div class="wallet-box-flex">
                <span id="walletAddress" class="wallet-text"><?php echo htmlspecialchars($target_wallet); ?></span>
                <button type="button" class="copy-btn" onclick="copyWallet()">📋 কপি</button>
            </div>
        </div>

        <form action="success.php" method="POST">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            <input type="hidden" name="send_currency" value="<?php echo htmlspecialchars($send_currency); ?>">
            <input type="hidden" name="receive_currency" value="<?php echo htmlspecialchars($receive_currency); ?>">
            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">

            <div class="form-group"><label>আপনার নাম:</label><input type="text" name="user_name" placeholder="সম্পূর্ণ নাম" required></div>
            <div class="form-group"><label>আপনার অ্যাকাউন্ট নম্বর / ওয়ালেট:</label><input type="text" name="user_account" placeholder="যে মাধ্যমে গ্রহণ করবেন" required></div>
            <div class="form-group"><label>ট্রানজেকশন আইডি / TrxID:</label><input type="text" name="trx_id" placeholder="TrxID অথবা Hash" required></div>

            <button type="submit" class="submit-btn">অর্ডার কনফার্ম করুন</button>
        </form>
        <a href="index.php" class="back-link">← হোম পেজে ফিরে যান</a>
    </div>

    <script>
        document.addEventListener("click", function (e) {
            let ripple = document.createElement("div");
            ripple.className = "water-ripple";
            ripple.style.left = e.clientX + "px";
            ripple.style.top = e.clientY + "px";
            document.body.appendChild(ripple);
            setTimeout(() => { ripple.remove(); }, 800);
        });
        function copyWallet() {
            var textToCopy = document.getElementById("walletAddress").innerText;
            navigator.clipboard.writeText(textToCopy).then(function() {
                alert('অ্যাডার্স সফলভাবে কপি হয়েছে!');
            });
        }
    </script>
</body>
</html>
