<?php
session_start();

// বটের আক্রমণ রুখতে শক্তিশালী ইউনিক টোকেন জেনারেট করা
if (empty($_SESSION['bot_token'])) {
    $_SESSION['bot_token'] = bin2hex(random_bytes(32));
}

$rate_file = "rates.json";
$wallet_file = "wallets.json";
$order_file = "orders.json";

$default_rates = ['buy_rate' => 122.00, 'sell_rate' => 125.00];
$current_rates = file_exists($rate_file) ? json_decode(file_get_contents($rate_file), true) : $default_rates;

$whatsapp_number = "8801700000000";
$notice_text = "স্বাগতম SkPayZone-এ! নিরাপদে ২৪ ঘণ্টা অটোমেটিক কারেন্সি এক্সচেঞ্জ করুন।";

$ticker_names_json = json_encode(["রহিম", "করিম", "ফাহিম", "সাকিব", "রিয়াদ", "তানভীর", "ইমরান"]);
$ticker_actions_json = json_encode(["Epay USD বিক্রি করে বিকাশ নিলেন", "Epay USD এক্সচেঞ্জ করলেন", "নগদ দিয়ে Epay নিলেন"]);
$ticker_interval_ms = 60000;

if (file_exists($wallet_file)) {
    $wallets = json_decode(file_get_contents($wallet_file), true);
    if(isset($wallets['whatsapp'])) { $whatsapp_number = $wallets['whatsapp']; }
    if(isset($wallets['notice'])) { $notice_text = $wallets['notice']; }
    
    if(!empty($wallets['ticker_names'])) {
        $names_arr = array_map('trim', explode(',', $wallets['ticker_names']));
        $ticker_names_json = json_encode($names_arr);
    }
    if(!empty($wallets['ticker_actions'])) {
        $actions_arr = array_map('trim', explode(',', $wallets['ticker_actions']));
        $ticker_actions_json = json_encode($actions_arr);
    }
    if(isset($wallets['ticker_interval']) && (int)$wallets['ticker_interval'] > 0) {
        $ticker_interval_ms = (int)$wallets['ticker_interval'] * 60000;
    }
}

$orders = file_exists($order_file) ? json_decode(file_get_contents($order_file), true) : [];
$latest_orders = array_slice($orders, 0, 3);
$is_logged_in = isset($_SESSION['user_logged']) ? $_SESSION['user_logged'] : false;
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - Digital Currency Exchange</title>
    <style>
        :root {
            --bg-color: #0f172a;
            --text-color: #f8fafc;
            --header-bg: rgba(30, 41, 59, 0.9);
            --card-bg: rgba(30, 41, 59, 0.85);
            --border-color: #334155;
            --input-bg: rgba(15, 23, 42, 0.9);
        }
        [data-theme="light"] {
            --bg-color: #f1f5f9;
            --text-color: #0f172a;
            --header-bg: rgba(255, 255, 255, 0.9);
            --card-bg: rgba(255, 255, 255, 0.95);
            --border-color: #cbd5e1;
            --input-bg: #f8fafc;
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: var(--bg-color); color: var(--text-color); position: relative; overflow-x: hidden; transition: 0.3s; }
        
        @keyframes waterWave { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, var(--bg-color), #1e1b4b, var(--bg-color), #022c22); background-size: 400% 400%; animation: waterWave 12s ease infinite; z-index: -1; opacity: 0.8; }

        header { background: var(--header-bg); backdrop-filter: blur(10px); padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(0,0,0,0.2); border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 1000; }
        @keyframes magicLight { 0% { color: #ff0055; text-shadow: 0 0 12px rgba(255,0,85,0.8); } 33% { color: #00ffcc; text-shadow: 0 0 12px rgba(0,255,204,0.8); } 66% { color: #ffff00; text-shadow: 0 0 12px rgba(255,255,0,0.8); } 100% { color: #ff0055; text-shadow: 0 0 12px rgba(255,0,85,0.8); } }
        .logo h2 { margin: 0; font-size: 22px; animation: magicLight 4s infinite; font-weight: 800; letter-spacing: 1px; }
        .logo p { margin: 0; font-size: 11px; color: #94a3b8; }
        
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .nav-links a { color: #fff; text-decoration: none; font-size: 12px; background: #3b82f6; padding: 6px 10px; border-radius: 6px; font-weight: 600; }
        .nav-links a.register { background: #10b981; }
        .lang-select { background: var(--input-bg); color: var(--text-color); border: 1px solid var(--border-color); padding: 5px; border-radius: 6px; font-size: 11px; cursor: pointer; }

        .menu-container { position: relative; display: flex; align-items: center; gap: 8px; }
        .hamburger { background: none; border: none; color: var(--text-color); font-size: 22px; cursor: pointer; padding: 0; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: 45px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); width: 190px; overflow: hidden; z-index: 1100; }
        .dropdown-menu a, .dropdown-menu div { display: block; padding: 10px 15px; color: var(--text-color); text-decoration: none; font-size: 13px; border-bottom: 1px solid var(--border-color); cursor: pointer; }
        .dropdown-menu a:hover { background: #3b82f6; color: #fff; }
        .dropdown-menu.active { display: block; }

        .notice-bar { background: var(--card-bg); border-bottom: 1px solid var(--border-color); color: #f59e0b; padding: 8px 0; font-size: 13px; font-weight: bold; overflow: hidden; white-space: nowrap; }
        .hero-banner { background: linear-gradient(135deg, rgba(30,58,138,0.8), rgba(30,41,59,0.8)); color: white; text-align: center; padding: 20px; border-bottom: 2px solid rgba(59,130,246,0.5); }
        .whatsapp-btn { display: inline-block; background: #22c55e; color: white; padding: 6px 15px; text-decoration: none; border-radius: 30px; font-size: 13px; margin-top: 10px; font-weight: bold; }

        .rates-table-box { max-width: 420px; margin: 15px auto; background: var(--card-bg); padding: 12px 15px; border-radius: 10px; border: 1px solid var(--border-color); font-size: 12px; display: flex; justify-content: space-around; text-align: center; }
        .rate-item span { display: block; color: #38bdf8; font-weight: bold; font-size: 14px; margin-top: 3px; }

        .container { padding: 20px; max-width: 420px; margin: 15px auto; background: var(--card-bg); backdrop-filter: blur(10px); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border: 1px solid var(--border-color); }
        .container h3 { margin-top: 0; text-align: center; font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid var(--border-color); padding-bottom: 8px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 12px; color: var(--text-color); }
        select, input { width: 100%; padding: 11px; border: 1px solid var(--border-color); border-radius: 8px; box-sizing: border-box; font-size: 14px; background: var(--input-bg); color: var(--text-color); }
        .swap-container { text-align: center; margin: -5px 0 10px 0; }
        .swap-btn { background: var(--border-color); color: var(--text-color); border: none; width: 35px; height: 35px; border-radius: 50%; font-size: 15px; cursor: pointer; }

        @keyframes magicBorder { 0% { border-color: #ff0055; box-shadow: 0 0 15px #ff0055; } 33% { border-color: #00ffcc; box-shadow: 0 0 15px #00ffcc; } 66% { border-color: #ffff00; box-shadow: 0 0 15px #ffff00; } 100% { border-color: #ff0055; box-shadow: 0 0 15px #ff0055; } }
        .exchange-btn { display: block; width: 100%; padding: 13px; background: var(--bg-color); color: var(--text-color); border: 3px solid #ff0055; border-radius: 8px; text-align: center; font-size: 16px; font-weight: bold; margin-top: 15px; cursor: pointer; animation: magicBorder 3s infinite; }

        .latest-box { max-width: 420px; margin: 15px auto; background: var(--card-bg); padding: 15px; border-radius: 12px; border: 1px solid var(--border-color); font-size: 12px; }
        .latest-item { display: flex; justify-content: space-between; background: var(--input-bg); padding: 8px 10px; border-radius: 6px; margin-bottom: 6px; border: 1px solid var(--border-color); }

        .trust-badges { max-width: 420px; margin: 15px auto; text-align: center; font-size: 11px; color: #94a3b8; background: var(--card-bg); padding: 10px; border-radius: 10px; border: 1px solid var(--border-color); }

        #liveToast { position: fixed; bottom: 20px; left: 20px; background: rgba(15, 23, 42, 0.95); border: 1px solid #38bdf8; color: #fff; padding: 10px 15px; border-radius: 8px; font-size: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.4); z-index: 9999; display: none; transition: 0.5s; }

        .water-ripple { position: fixed; border-radius: 50%; border: 2px solid rgba(0, 255, 204, 0.8); background: rgba(255, 0, 85, 0.3); transform: scale(0); animation: waterRippleAnim 0.8s cubic-bezier(0, 0.2, 0.8, 1) forwards; pointer-events: none; z-index: 9999; }
        @keyframes waterRippleAnim { 0% { width: 0px; height: 0px; opacity: 1; transform: translate(-50%, -50%) scale(0); border-color: #00ffcc; background: rgba(255, 0, 85, 0.4); } 50% { border-color: #ffff00; background: rgba(0, 255, 204, 0.2); } 100% { width: 180px; height: 180px; opacity: 0; transform: translate(-50%, -50%) scale(1); border-color: #ff0055; background: rgba(255, 255, 0, 0); } }
        footer { text-align: center; padding: 15px; font-size: 11px; color: #94a3b8; margin-top: 20px; border-top: 1px solid var(--border-color); }
    </style>
</head>
<body data-theme="dark">

    <header>
        <div class="logo">
            <h2>SkPayZone</h2>
            <p>Secure Exchange</p>
        </div>
        <div class="menu-container">
            <select class="lang-select" onchange="changeLanguage(this.value)">
                <option value="bn">বাংলা</option>
                <option value="en">English</option>
            </select>

            <?php if (!$is_logged_in): ?>
                <div class="nav-links">
                    <a href="login.php">লগইন</a>
                    <a href="signup.php" class="register">সাইন আপ</a>
                </div>
            <?php else: ?>
                <button class="hamburger" onclick="toggleMenu()">☰</button>
                <div id="dropdownMenu" class="dropdown-menu">
                    <div style="font-weight:bold; color:#38bdf8; border-bottom:1px solid var(--border-color);">👤 <?php echo htmlspecialchars($user_name); ?></div>
                    <a href="dashboard.php">⚙️ ইউজার ড্যাশবোর্ড</a>
                    <a href="history.php">📜 এক্সচেঞ্জ হিস্ট্রি</a>
                    <div onclick="toggleTheme()" id="themeToggleText">☀️ Light Mode</div>
                    <a href="logout.php" style="color: #f87171;">🚪 লগআউট</a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="notice-bar">
        <marquee scrollamount="5">📢 <b>বিশেষ নোটিশ:</b> <?php echo htmlspecialchars($notice_text); ?></marquee>
    </div>

    <div class="hero-banner">
        <h3 style="margin:0 0 5px 0;">দ্রুত ও নিরাপদ এক্সচেঞ্জ সেবা</h3>
        <p style="margin:0; font-size:12px; color:#cbd5e1;">নিরাপদে আপনার কারেন্সি এক্সচেঞ্জ করুন মুহূর্তের মধ্যে</p>
        <a href="https://wa.me/<?php echo $whatsapp_number; ?>" target="_blank" class="whatsapp-btn">💬 WhatsApp সাপোর্ট</a>
    </div>

    <div class="rates-table-box">
        <div class="rate-item">ডলার কিনবেন (Buy) <span><?php echo $current_rates['buy_rate']; ?> ৳</span></div>
        <div style="border-left: 1px solid var(--border-color);"></div>
        <div class="rate-item">ডলার বিক্রি করবেন (Sell) <span><?php echo $current_rates['sell_rate']; ?> ৳</span></div>
    </div>

    <div class="container">
        <h3>এক্সচেঞ্জ ক্যালকুলেটর</h3>
        
        <form action="process.php" method="POST">
            <!-- বটের আক্রমণ ঠেকাতে সিকিউরিটি টোকেন -->
            <input type="hidden" name="bot_token" value="<?php echo $_SESSION['bot_token']; ?>">

            <!-- হানিপট ফিল্ড: সাধারণ মানুষ এটি দেখতে পাবে না, কিন্তু বট দেখলে ফিলাপ করবে এবং ধরা খেয়ে ব্লক হবে -->
            <div style="display:none;">
                <label>Website URL (Leave blank):</label>
                <input type="text" name="website_url" value="">
            </div>

            <div class="form-group">
                <label>আপনি পাঠাবেন (Send):</label>
                <select id="sendCurrency" name="send_currency" onchange="updateCalculation()">
                    <option value="Epay USD" selected>Epay USD</option>
                    <option value="Binance USDT">Binance USDT</option>
                    <option value="Payeer USD">Payeer USD</option>
                    <option value="Tron (TRX)">Tron (TRX)</option>
                </select>
            </div>
            
            <div class="swap-container">
                <button type="button" class="swap-btn" onclick="swapCurrencies()" title="Swap">⇅</button>
            </div>
            
            <div class="form-group">
                <label>আপনি পাবেন (Receive):</label>
                <select id="receiveCurrency" name="receive_currency" onchange="updateCalculation()">
                    <option value="Bkash Personal" selected>Bkash Personal</option>
                    <option value="Nagad Personal">Nagad Personal</option>
                    <option value="Rocket Personal">Rocket Personal</option>
                    <option value="Upay Personal">Upay Personal</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>পরিমাণ (Amount):</label>
                <input type="number" id="amount" name="amount" placeholder="পরিমাণ লিখুন" required oninput="updateCalculation()">
            </div>

            <div id="resultText" style="font-weight: bold; color: #34d399; text-align: center; margin-bottom: 10px; font-size: 14px;"></div>
            <button type="submit" class="exchange-btn">এক্সচেঞ্জ করুন</button>
        </form>
    </div>

    <div class="trust-badges">
        🛡️ <b>100% সুরক্ষিত ও স্বয়ংক্রিয় লেনদেন সিস্টেম</b><br>
        বিকাশ • নগদ • উপায় পেমেন্ট গেটওয়ে সমর্থিত
    </div>

    <div class="latest-box">
        <h4 style="margin:0 0 8px 0; color:#38bdf8; text-align:center;">🔄 Latest Exchanges (সাম্প্রতিক লেনদেন)</h4>
        <?php if(empty($latest_orders)): ?>
            <p style="text-align:center; color:#94a3b8; margin:0;">কোনো সাম্প্রতিক লেনদেন নেই</p>
        <?php else: ?>
            <?php foreach($latest_orders as $lo): ?>
                <div class="latest-item">
                    <span><b><?php echo htmlspecialchars($lo['user_name']); ?></b>: <?php echo $lo['send_currency']; ?> ➔ <?php echo $lo['receive_currency']; ?></span>
                    <span style="color:<?php echo (strpos($lo['status'],'Completed')!==false)?'#34d399':((strpos($lo['status'],'Cancelled')!==false)?'#ef4444':'#f59e0b'); ?>; font-weight:bold;"><?php echo $lo['status']; ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div style="text-align:center; margin-top:8px;">
            <a href="track.php" style="color:#38bdf8; text-decoration:none; font-size:11px;">🔍 আপনার অর্ডার ট্র্যাক করুন</a>
        </div>
    </div>

    <div id="liveToast"></div>

    <footer>&copy; 2026 SkPayZone. All Rights Reserved.</footer>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let savedTheme = localStorage.getItem("sk_theme") || "dark";
            document.body.setAttribute("data-theme", savedTheme);
            let themeText = document.getElementById("themeToggleText");
            if(themeText) {
                themeText.innerText = savedTheme === "light" ? "🌙 Dark Mode" : "☀️ Light Mode";
            }
        });

        function toggleMenu() { document.getElementById("dropdownMenu").classList.toggle("active"); }
        
        function toggleTheme() {
            let body = document.body;
            let themeText = document.getElementById("themeToggleText");
            if(body.getAttribute("data-theme") === "dark") {
                body.setAttribute("data-theme", "light");
                localStorage.setItem("sk_theme", "light");
                themeText.innerText = "🌙 Dark Mode";
            } else {
                body.setAttribute("data-theme", "dark");
                localStorage.setItem("sk_theme", "dark");
                themeText.innerText = "☀️ Light Mode";
            }
        }

        document.addEventListener("click", function (e) {
            let ripple = document.createElement("div");
            ripple.className = "water-ripple";
            ripple.style.left = e.clientX + "px";
            ripple.style.top = e.clientY + "px";
            document.body.appendChild(ripple);
            setTimeout(() => { ripple.remove(); }, 800);
        });

        const fakeUsers = <?php echo $ticker_names_json; ?>;
        const fakeActions = <?php echo $ticker_actions_json; ?>;
        const tickerInterval = <?php echo $ticker_interval_ms; ?>;

        function showLiveToast() {
            const toast = document.getElementById("liveToast");
            if(!fakeUsers.length || !fakeActions.length) return;
            let randomUser = fakeUsers[Math.floor(Math.random() * fakeUsers.length)];
            let randomAction = fakeActions[Math.floor(Math.random() * fakeActions.length)];
            toast.innerHTML = `🔔 <b>${randomUser}</b> এই মাত্র ${randomAction}!`;
            toast.style.display = "block";
            setTimeout(() => { toast.style.display = "none"; }, 5000);
        }
        
        setInterval(showLiveToast, tickerInterval);

        const buyRate = <?php echo $current_rates['buy_rate']; ?>;
        const sellRate = <?php echo $current_rates['sell_rate']; ?>;
        function swapCurrencies() {
            var s = document.getElementById("sendCurrency"), r = document.getElementById("receiveCurrency");
            var t = s.value; s.value = r.value; r.value = t;
            updateCalculation();
        }
        function updateCalculation() {
            var amount = document.getElementById("amount").value;
            var resultDiv = document.getElementById("resultText");
            if(amount === "" || amount <= 0) { resultDiv.innerHTML = ""; return; }
            resultDiv.innerHTML = "আপনি পাবেন আনুমানিক: " + (amount * sellRate).toFixed(2) + " ৳";
        }
    </script>
</body>
</html>
