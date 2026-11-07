<?php
session_start();
if (!isset($_SESSION['user_logged']) || $_SESSION['user_logged'] !== true) {
    header("Location: login.php");
    exit();
}

$user_file = "users.json";
$users = file_exists($user_file) ? json_decode(file_get_contents($user_file), true) : [];
$current_email = $_SESSION['user_email'];

$msg = ""; $error = "";
$current_user = null;

foreach($users as $u) {
    if($u['email'] === $current_email) {
        $current_user = $u;
        break;
    }
}

// পাসওয়ার্ড আপডেট হ্যান্ডেল
if(isset($_POST['update_pass'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];

    if($current_user['password'] !== $old_pass) {
        $error = "বর্তমান পাসওয়ার্ড সঠিক নয়!";
    } else {
        foreach($users as &$usr) {
            if($usr['email'] === $current_email) {
                $usr['password'] = $new_pass;
                $current_user['password'] = $new_pass;
            }
        }
        file_put_contents($user_file, json_encode($users, JSON_PRETTY_PRINT));
        $msg = "পাসওয়ার্ড সফলভাবে পরিবর্তন করা হয়েছে!";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - User Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; background: #0f172a; color: #f8fafc; }
        .container { max-width: 500px; margin: 30px auto; background: #1e293b; padding: 25px; border-radius: 12px; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
        h2, h3 { text-align: center; color: #38bdf8; margin-top: 0; }
        .info-box { background: #0f172a; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #334155; font-size: 14px; }
        .info-box p { margin: 8px 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #cbd5e1; }
        input { width: 100%; padding: 11px; border: 1px solid #475569; border-radius: 8px; box-sizing: border-box; font-size: 14px; background: #0f172a; color: white; }
        .submit-btn { display: block; width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-size: 15px; font-weight: bold; cursor: pointer; margin-top: 15px; }
        .submit-btn:hover { background: #2563eb; }
        .success-msg { color: #34d399; background: rgba(52,211,153,0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; border: 1px solid #34d399; }
        .error-msg { color: #f87171; background: rgba(248,113,113,0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; border: 1px solid #f87171; }
        .links { display: flex; justify-content: space-between; margin-top: 20px; font-size: 13px; }
        .links a { color: #38bdf8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>👤 ইউজার ড্যাশবোর্ড</h2>
        
        <div class="info-box">
            <p><b>নাম:</b> <?php echo htmlspecialchars($current_user['first_name'].' '.$current_user['last_name']); ?></p>
            <p><b>ইমেইল:</b> <?php echo htmlspecialchars($current_user['email']); ?></p>
            <p><b>ফোন:</b> <?php echo htmlspecialchars($current_user['phone']); ?></p>
        </div>

        <h3>🔒 পাসওয়ার্ড পরিবর্তন করুন</h3>
        <?php if($msg != "") { echo "<div class='success-msg'>$msg</div>"; } ?>
        <?php if($error != "") { echo "<div class='error-msg'>$error</div>"; } ?>

        <form method="POST">
            <div class="form-group"><label>বর্তমান পাসওয়ার্ড</label><input type="password" name="old_password" required></div>
            <div class="form-group"><label>নতুন পাসওয়ার্ড</label><input type="password" name="new_password" required></div>
            <button type="submit" name="update_pass" class="submit-btn">পাসওয়ার্ড আপডেট করুন</button>
        </form>

        <div class="links">
            <a href="history.php">📜 এক্সচেঞ্জ হিস্ট্রি</a>
            <a href="index.php">🏠 হোম পেজ</a>
        </div>
    </div>
</body>
</html>
