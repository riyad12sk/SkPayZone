<?php
session_start();
$user_file = "users.json";
$error = "";

if(isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $users = file_exists($user_file) ? json_decode(file_get_contents($user_file), true) : [];
    $logged = false; $name = "";
    foreach($users as $u) {
        if(($u['email'] === $email || $u['phone'] === $email) && $u['password'] === $password) {
            $logged = true;
            $name = $u['first_name'];
            break;
        }
    }

    if($logged) {
        $_SESSION['user_logged'] = true;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
        header("Location: index.php");
        exit();
    } else {
        $error = "ভুল ইমেইল/ফোন অথবা পাসওয়ার্ড!";
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkPayZone - Log In</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #0f172a; color: #f8fafc; }
        .container { padding: 30px; max-width: 400px; margin: 60px auto; background: #1e293b; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); border: 1px solid #334155; }
        h2 { text-align: center; color: #38bdf8; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 13px; color: #cbd5e1; }
        input { width: 100%; padding: 12px; border: 1px solid #475569; border-radius: 8px; box-sizing: border-box; font-size: 15px; background: #0f172a; color: white; }
        .submit-btn { display: block; width: 100%; padding: 13px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; margin-top: 20px; cursor: pointer; }
        .signup-link, .forgot-link { text-align: center; margin-top: 12px; font-size: 13px; color: #94a3b8; }
        .signup-link a, .forgot-link a { color: #38bdf8; text-decoration: none; font-weight: bold; }
        .error-msg { color: #f87171; background: rgba(248,113,113,0.1); padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; border: 1px solid #f87171; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Log In</h2>
        <?php if($error != "") { echo "<div class='error-msg'>$error</div>"; } ?>
        <form method="POST">
            <div class="form-group"><label>Email or Phone</label><input type="text" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <button type="submit" name="login" class="submit-btn">Log In</button>
        </form>
        <div class="forgot-link"><a href="forgot.php">পাসওয়ার্ড ভুলে গেছেন?</a></div>
        <div class="signup-link">Don't have an account? <a href="signup.php">Sign up</a></div>
        <div style="text-align: center; margin-top: 20px;"><a href="index.php" style="color: #94a3b8; font-size: 13px; text-decoration: none;">← হোম পেজে ফিরে যান</a></div>
    </div>
</body>
</html>
