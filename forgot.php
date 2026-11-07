<?php
$user_file = "users.json";
$msg = "";
$error = "";
$step = 1;

if(isset($_POST['send_code'])) {
    $identifier = trim($_POST['identifier']);
    $users = file_exists($user_file) ? json_decode(file_get_contents($user_file), true) : [];
    $found = false;
    foreach($users as $u) {
        if($u['email'] === $identifier || $u['phone'] === $identifier) {
            $found = true;
            break;
        }
    }
    if($found) {
        session_start();
        $_SESSION['reset_id'] = $identifier;
        $_SESSION['otp'] = rand(1000, 9999); // ডেমো ওটিপি কোড
        $step = 2;
        $msg = "আপনার কোডটি হলো: " . $_SESSION['otp'] . " (ডেমো সার্ভার সিমুলেশন)";
    } else {
        $error = "এই ইমেইল বা ফোন নম্বর দিয়ে কোনো অ্যাকাউন্ট পাওয়া যায়নি!";
    }
}

if(isset($_POST['verify_reset'])) {
    session_start();
    $entered_otp = $_POST['otp'];
    $new_pass = $_POST['new_password'];
    
    if($entered_otp == $_SESSION['otp']) {
        $users = file_exists($user_file) ? json_decode(file_get_contents($user_file), true) : [];
        foreach($users as &$u) {
            if($u['email'] === $_SESSION['reset_id'] || $u['phone'] === $_SESSION['reset_id']) {
                $u['password'] = $new_pass;
            }
        }
        file_put_contents($user_file, json_encode($users, JSON_PRETTY_PRINT));
        unset($_SESSION['otp']);
        unset($_SESSION['reset_id']);
        $step = 3;
        $msg = "পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!";
    } else {
        session_start();
        $step = 2;
        $error = "ভুল ভেরিফিকেশন কোড!";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - Forgot Password</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #0f172a; color: #f8fafc; }
        .container { padding: 30px; max-width: 400px; margin: 60px auto; background: #1e293b; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); border: 1px solid #334155; }
        h2 { text-align: center; color: #38bdf8; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #cbd5e1; }
        input { width: 100%; padding: 12px; border: 1px solid #475569; border-radius: 8px; box-sizing: border-box; font-size: 15px; background: #0f172a; color: white; }
        input:focus { border-color: #3b82f6; outline: none; }
        .submit-btn { display: block; width: 100%; padding: 13px; background: #3b82f6; color: white; border: none; border-radius: 8px; text-align: center; font-size: 16px; font-weight: bold; margin-top: 20px; cursor: pointer; transition: 0.3s; }
        .submit-btn:hover { background: #2563eb; }
        .success-msg { color: #34d399; background: rgba(52,211,153,0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; border: 1px solid #34d399; }
        .error-msg { color: #f87171; background: rgba(248,113,113,0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; border: 1px solid #f87171; }
        .back-link { text-align: center; margin-top: 15px; font-size: 13px; }
        .back-link a { color: #38bdf8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <?php if($msg != "") { echo "<div class='success-msg'>$msg</div>"; } ?>
        <?php if($error != "") { echo "<div class='error-msg'>$error</div>"; } ?>

        <?php if(!isset($step) || $step == 1): ?>
        <form method="POST">
            <div class="form-group">
                <label>আপনার রেজিস্টার্ড ইমেইল বা ফোন নম্বর দিন</label>
                <input type="text" name="identifier" placeholder="Email or Phone" required>
            </div>
            <button type="submit" name="send_code" class="submit-btn">কোড পাঠান</button>
        </form>
        <?php elseif($step == 2): ?>
        <form method="POST">
            <div class="form-group">
                <label>৪ ডিজিটের কোডটি লিখুন</label>
                <input type="text" name="otp" placeholder="Enter OTP Code" required>
            </div>
            <div class="form-group">
                <label>নতুন পাসওয়ার্ড দিন</label>
                <input type="password" name="new_password" placeholder="New Password" required>
            </div>
            <button type="submit" name="verify_reset" class="submit-btn">পাসওয়ার্ড পরিবর্তন করুন</button>
        </form>
        <?php else: ?>
            <p style="text-align:center; color:#34d399;">আপনার পাসওয়ার্ড রিসেট সম্পন্ন হয়েছে!</p>
        <?php endif; ?>

        <div class="back-link"><a href="login.php">← লগইন পেজে ফিরে যান</a></div>
    </div>
</body>
</html>
