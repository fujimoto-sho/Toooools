<?php
//*************************************
// ログイン認証
//*************************************

debugLog('ログイン認証');

// セッションにlogin_dateが存在したらログイン済と判断する
if (!empty($_SESSION['login_date'])) {
  debugLog('ログイン済ユーザーです。');
  
  // 有効期限の検証
  $maxLoginTime = $_SESSION['login_date'] + $_SESSION['login_limit'];
  if ($maxLoginTime < time()) {
    debugLog('ログイン有効期限切れです');
    session_destroy();
    if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
      header("Location:login.php");
    }
  } else {
    if (basename($_SERVER['PHP_SELF']) !== 'profile.php') {
      header("Location:profile.php");
    }
  }
} else {
  debugLog('未ログインユーザーです');
  if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location:login.php");
  }
}