<?php
$user_file = "users.json";
$msg = "";
$error = "";

if(isset($_POST['register'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if($password !== $confirm_password) {
        $error = "পাসওয়ার্ড দুটি মিলছে না!";
    } else {
        $users = file_exists($user_file) ? json_decode(file_get_contents($user_file), true) : [];
        
        // ইমেইল বা ফোন আগে থেকেই আছে কিনা চেক
        $exists = false;
        foreach($users as $u) {
            if($u['email'] === $email || $u['phone'] === $phone) {
                $exists = true;
                break;
            }
        }

        if($exists) {
            $error = "এই ইমেইল বা ফোন নম্বর দিয়ে ইতিমধ্যে অ্যাকাউন্ট রয়েছে!";
        } else {
            $new_user = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'password' => $password, // প্রোজেক্টের সিকিউরিটির জন্য plaintext বা hash রাখা যায়
                'time' => date('Y-m-d H:i:s')
            ];
            $users[] = $new_user;
            file_put_contents($user_file, json_encode($users, JSON_PRETTY_PRINT));
            $msg = "রেজিস্ট্রেশন সফল হয়েছে! এখন আপনি লগইন করতে পারেন।";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - Sign Up</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #0f172a; color: #f8fafc; }
        .container { padding: 30px; max-width: 420px; margin: 40px auto; background: #1e293b; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); border: 1px solid #334155; }
        h2 { text-align: center; color: #38bdf8; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #cbd5e1; }
        input { width: 100%; padding: 11px; border: 1px solid #475569; border-radius: 8px; box-sizing: border-box; font-size: 14px; background: #0f172a; color: white; }
        input:focus { border-color: #3b82f6; outline: none; }
        .checkbox-group { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #cbd5e1; margin-top: 10px; }
        .checkbox-group input { width: auto; }
        .submit-btn { display: block; width: 100%; padding: 13px; background: #10b981; color: white; border: none; border-radius: 8px; text-align: center; font-size: 16px; font-weight: bold; margin-top: 20px; cursor: pointer; transition: 0.3s; }
        .submit-btn:hover { background: #059669; }
        .login-link { text-align: center; margin-top: 15px; font-size: 13px; color: #94a3b8; }
        .login-link a { color: #38bdf8; text-decoration: none; font-weight: bold; }
        .success-msg { color: #34d399; background: rgba(52,211,153,0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; border: 1px solid #34d399; }
        .error-msg { color: #f87171; background: rgba(248,113,113,0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; border: 1px solid #f87171; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <div class="login-link" style="margin-bottom: 20px;">
            Already a Member? <a href="login.php">Log in</a>
        </div>
        <?php if($msg != "") { echo "<div class='success-msg'>$msg</div>"; } ?>
        <?php if($error != "") { echo "<div class='error-msg'>$error</div>"; } ?>

        <form method="POST">
            <div class="form-group"><label>First Name *</label><input type="text" name="first_name" required></div>
            <div class="form-group"><label>Last Name *</label><input type="text" name="last_name" required></div>
            <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password *</label><input type="password" name="password" required></div>
            <div class="form-group"><label>Confirm Password *</label><input type="password" name="confirm_password" required></div>
            <div class="form-group"><label>Phone *</label><input type="text" name="phone" required></div>
            <div class="checkbox-group">
                <input type="checkbox" name="newsletter" id="news" checked>
                <label for="news" style="margin-bottom:0; cursor:pointer;">I'd like to receive newsletter</label>
            </div>
            <button type="submit" name="register" class="submit-btn">Sign up</button>
        </form>
        <div style="text-align: center; margin-top: 20px;"><a href="index.php" style="color: #94a3b8; font-size: 13px; text-decoration: none;">← হোম পেজে ফিরে যান</a></div>
    </div>
</body>
</html>
