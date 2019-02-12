<?php
//*************************************
// 共通変数・関数
//*************************************

//-------------------------------------
// iniファイル設定
//-------------------------------------
ini_set('log_error', 'On');
ini_set('error_log', 'log/php_' . date('Ymd') . '.log');

//-------------------------------------
// 共通変数
//-------------------------------------
// エラーメッセージ
$err_msg = array();

//-------------------------------------
// メッセージ
//-------------------------------------
define('MSG01', '必須入力です。');
define('MSG02', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG03', '既に登録されているメールアドレスです。');
define('MSG04', 'パスワード（再入力）が合っていません。');
define('MSG05', '半角英数字のみ入力可能です。');
define('MSG06', '文字以上で入力してください。');
define('MSG07', '文字以下で入力してください。');
define('MSG08', 'Emailの形式が違います。');

//-------------------------------------
// デバッグログ
//-------------------------------------
$debugLogWrite = true;
// デバッグログ出力
function debugLog($msg)
{
  global $debugLogWrite;
  if ($debugLogWrite) error_log('デバッグ：' . $msg);
}
// デバッグログ（ページタイトル）
function debugLogTitle($title)
{
  debugLog('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
  debugLog('「 ' . $title);
  debugLog('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
}
// デバッグログ（画面表示開始）
function debugLogStart()
{
  debugLog('画面表示開始 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
}
// デバッグログ（画面表示終了）
function debugLogEnd()
{
  debugLog('画面表示終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
}

//-------------------------------------
// バリデーション
//-------------------------------------
// 未入力チェック
function validEmpty($str, $key)
{
  if ($str === '') {
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
// 最小文字数チェック
function validMinLen($str, $key, $min = 6)
{
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = $min . MSG06;
  }
}
// 最大文字数チェック
function validMaxLen($str, $key, $max = 255)
{
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = $max . MSG07;
  }
}
// メールの重複チェック
function validEmailDup($email, $key)
{
  try {
    // DB処理
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE email = :email';
    $data = array(
      ':email' => $email
    );
    $stmt = queryPost($dbh, $sql, $data);

    // 取得件数が1件以上の場合は重複
    if ($stmt->rowCount() > 0) {
      debugLog('メールアドレス重複あり');
      global $err_msg;
      $err_msg[$key] = MSG03;
    } else {
      debugLog('メールアドレス重複なし');
    }

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG02;
  }
}
// メールのフォーマットチェック
function validEmailFormat($email, $key)
{
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    global $err_msg;
    $err_msg[$key] = MSG08;
  }
}
// 半角英数字チェック
function validHalf($str, $key)
{
  $regax = "/^[0-9a-zA-Z]+$/";
  if (!preg_match($regax, $str)) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
// 同値チェック
function validEqual($str1, $str2, $key)
{
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}

//-------------------------------------
// データベース
//-------------------------------------
// データベース接続
function dbConnect()
{
  $dsn = 'mysql:dbname=toooools;host=localhost;charset=utf8';
  $user = 'root';
  $pass = 'root';
  $options = array(
    // SQL失敗時、エラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    // デフォルトフェッチモードを連想配列に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // SELECTの結果に対してrowCountを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  debugLog('データベースに接続');
  $dbh = new PDO($dsn, $user, $pass, $options);
  return $dbh;
}

// クエリ実行
function queryPost($dbh, $sql, $data)
{
  // SQLインジェクションを防ぐため、prepareを使用する
  $stmt = $dbh->prepare($sql);
  $result = $stmt->execute($data);
  if (!$result) {
    debugLog('クエリ失敗');
    debugLog('失敗したクエリ：' . print_r($stmt, true));
    $err_msg['common'] = MSG02;
    return 0;
  }
  debugLog('クエリ成功');
  return $stmt;
}

//-------------------------------------
// その他
//-------------------------------------
// エラーメッセージが存在したら返す
function getErrMsg($key)
{
  global $err_msg;
  // 存在しなかったら空白を返す
  if (empty($err_msg[$key])) return '';

  return $err_msg[$key];
}
// POSTされていたらその文字列を返す
function getPost($key)
{
  // 存在しなかったら空白を返す
  if (empty($_POST[$key])) return '';

  return $_POST[$key];
}