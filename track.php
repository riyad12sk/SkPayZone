<?php
$search_result = null;
$searched = false;

if (isset($_POST['search_trx'])) {
    $searched = true;
    $trx = trim($_POST['trx_id']);
    $order_file = "orders.json";
    if (file_exists($order_file)) {
        $orders = json_decode(file_get_contents($order_file), true);
        foreach ($orders as $ord) {
            if ($ord['trx_id'] === $trx) {
                $search_result = $ord;
                break;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - Track Order</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #0f172a; color: #f8fafc; position: relative; overflow-x: hidden; }
        @keyframes waterWave { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        body::before { content: ""; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #0f172a, #1e1b4b, #0f172a, #022c22); background-size: 400% 400%; animation: waterWave 12s ease infinite; z-index: -1; opacity: 0.8; }
        header { background: rgba(30, 41, 59, 0.9); backdrop-filter: blur(10px); color: #fff; padding: 15px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .container { padding: 25px; max-width: 450px; margin: 30px auto; background: rgba(30, 41, 59, 0.85); backdrop-filter: blur(10px); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); }
        h3 { text-align: center; color: #38bdf8; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #cbd5e1; }
        input { width: 100%; padding: 12px; border: 1px solid #475569; border-radius: 8px; box-sizing: border-box; font-size: 15px; background: rgba(15, 23, 42, 0.9); color: white; }
        input:focus { border-color: #3b82f6; outline: none; }
        @keyframes magicBorder { 0% { border-color: #ff0055; box-shadow: 0 0 15px #ff0055; } 33% { border-color: #00ffcc; box-shadow: 0 0 15px #00ffcc; } 66% { border-color: #ffff00; box-shadow: 0 0 15px #ffff00; } 100% { border-color: #ff0055; box-shadow: 0 0 15px #ff0055; } }
        .submit-btn { display: block; width: 100%; padding: 14px; background: #0f172a; color: #fff; border: 3px solid #ff0055; border-radius: 8px; text-align: center; font-size: 17px; font-weight: bold; margin-top: 20px; cursor: pointer; animation: magicBorder 3s infinite; transition: 0.3s; }
        .submit-btn:hover { opacity: 0.9; }
        .result-box { background: rgba(15, 23, 42, 0.9); padding: 15px; border-radius: 8px; margin-top: 20px; border: 1px solid #334155; }
        .back-link { display: block; text-align: center; margin-top: 15px; color: #94a3b8; text-decoration: none; font-size: 14px; }
        .water-ripple { position: fixed; border-radius: 50%; border: 2px solid rgba(0, 255, 204, 0.8); background: rgba(255, 0, 85, 0.3); transform: scale(0); animation: waterRippleAnim 0.8s cubic-bezier(0, 0.2, 0.8, 1) forwards; pointer-events: none; z-index: 9999; }
        @keyframes waterRippleAnim { 0% { width: 0px; height: 0px; opacity: 1; transform: translate(-50%, -50%) scale(0); border-color: #00ffcc; background: rgba(255, 0, 85, 0.4); } 50% { border-color: #ffff00; background: rgba(0, 255, 204, 0.2); } 100% { width: 180px; height: 180px; opacity: 0; transform: translate(-50%, -50%) scale(1); border-color: #ff0055; background: rgba(255, 255, 0, 0); } }
    </style>
</head>
<body>
    <header>
        <h2>SkPayZone</h2>
        <p style="margin:0; font-size:12px; color:#94a3b8;">অর্ডার ট্র্যাকিং</p>
    </header>
    <div class="container">
        <h3>🔍 ট্রানজেকশন ট্র্যাক করুন</h3>
        <form method="POST">
            <div class="form-group">
                <label>আপনার TrxID বা Hash দিন:</label>
                <input type="text" name="trx_id" placeholder="TrxID লিখুন" required>
            </div>
            <button type="submit" name="search_trx" class="submit-btn">স্ট্যাটাস চেক করুন</button>
        </form>

        <?php if($searched): ?>
            <div class="result-box">
                <?php if($search_result): ?>
                    <p><b>নাম:</b> <?php echo htmlspecialchars($search_result['user_name']); ?></p>
                    <p><b>লেনদেন:</b> <?php echo $search_result['send_currency']; ?> ➔ <?php echo $search_result['receive_currency']; ?></p>
                    <p><b>পরিমাণ:</b> <?php echo $search_result['amount']; ?></p>
                    <p><b>স্ট্যাটাস:</b> <span style="color:#34d399; font-weight:bold;"><?php echo $search_result['status']; ?></span></p>
                <?php else: ?>
                    <p style="color:#f87171; text-align:center; margin:0;">দুঃখিত, এই TrxID দিয়ে কোনো অর্ডার পাওয়া যায়নি!</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

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
    </script>
</body>
</html>
