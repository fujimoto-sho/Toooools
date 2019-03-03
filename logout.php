<?php
 //*************************************
// ログアウト
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('ログアウト');

debugLog('ログアウトします。');
// セッション削除（ログアウト）
session_destroy();

// 終了ログ
debugLogEnd();

debugLog('ログインページに遷移します。');
header("Location:login.php");
