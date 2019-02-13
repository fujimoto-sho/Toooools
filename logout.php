<?php
//*************************************
// ログアウト
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

debugLogStart('ログアウト');

debugLog('ログアウトします。');
// セッション削除（ログアウト）
session_destroy();

debugLogEnd();

debugLog('ログインページに遷移します。');
header("Location:login.php");