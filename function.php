<?php
//*************************************
// 共通変数・関数
//*************************************

//-------------------------------------
// iniファイル設定
//-------------------------------------
// ログの出力設定
ini_set('log_error', 'On');
// ログの出力ファイルを指定
ini_set('error_log', 'log/php_' . date('Ymd') . '.log');


//-------------------------------------
// グローバル変数
//-------------------------------------
// エラーメッセージ
$err_msg = array();
// 本番環境判定
$isProduction = (getenv('PHP_ENV') === 'heroku') ? true : false;

//-------------------------------------
// 定数
//-------------------------------------
// ログイン有効期限のデフォルト（1時間）
define('LOGIN_TIME_DEFAULT', 60 * 60);
// ログイン有効期限の最大（30日）
define('LOGIN_TIME_LONG', 60 * 60 * 24 * 30);
// 一覧ページで1ページに表示する最大件数
define('ONE_PAGE_COUNT', 10);
// 送信用メールアドレス
define('MAIL_FROM', 'fujisho344test@gmail.com');

// エラーメッセージ
define('ERRMSG', array(
  'EMPTY'                 => '必須入力です。',
  'DEFAULT'               => 'エラーが発生しました。しばらく経ってからやり直してください。',
  'EMAIL_DUP'             => '既に登録されているメールアドレスです。',
  'PASS_RE_NOT_EQUAL'     => 'パスワード（再入力）が合っていません。',
  'HALF'                  => '半角英数字のみ入力可能です。',
  'MINLEN'                => '文字以上で入力してください。',
  'MAXLEN'                => '文字以下で入力してください。',
  'EMAIL_FORMAT'          => 'Emailの形式が違います。',
  'LOGIN'                 => 'メールアドレス、またはパスワードが違います。',
  'PASS_NOW_NOT_EQUAL'    => '現在のパスワードが違います。',
  'PASS_NOW_NEW_EQUAL'    => '現在のパスワードと新しいパスワードが同じです。',
  'PASS_NEW_RE_NOT_EQUAL' => '新しいパスワード（再入力）が合っていません。',
  'EMAIL_NOT_EXISTS'      => '登録されていないメールアドレスです。',
));

// サクセスメッセージ
define('SUCMSG', array(
  'PROF_EDIT'   => 'プロフィールを編集しました。',
  'PASS_CHANGE' => 'パスワードを変更しました。',
  'PASS_REMIND' => 'パスワードの再設定が完了しました。',
  'POST_INSERT' => '投稿が完了しました。',
  'POST_UPDATE' => '投稿を編集しました。',
  'REPLY_SEND'  => 'リプライを送信しました。',
  'POST_DELETE' => '投稿を削除しました。',
));


//-------------------------------------
// セッション
//-------------------------------------
// セッションファイルを保存する。（/var/tmp/ 以下に保存すると30日保持される。）
session_save_path('/var/tmp/');
// ガーベージコレクションで回収される有効期限を伸ばす（デフォルト24分）
ini_set('session.gc_maxlifetime', LOGIN_TIME_DEFAULT);
// ブラウザを閉じても削除されないよう、クッキーの有効期限を伸ばす
ini_set('session.cookie_lifetime ', LOGIN_TIME_LONG);
// セッション開始
session_start();
// セッションを再生成（なりすまし対策）
session_regenerate_id();


//-------------------------------------
// デバッグログ
//-------------------------------------
// デバッグの出力判定
// 本番時はfalseにしてログを出さないようにする
// $debugLogWrite = ($isProduction) ? false : true;
$debugLogWrite = true;


// デバッグログ出力
function debugLog($msg)
{
  global $debugLogWrite;
  if ($debugLogWrite) error_log('デバッグ：' . $msg);
}

// デバッグログ（画面表示開始）
function debugLogStart($title)
{
  debugLog('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
  debugLog('「 ' . $title);
  debugLog('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
  debugLog('画面表示開始 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
  debugLog('セッションID：' . session_id());
  debugLog('セッション変数：' . print_r($_SESSION, true));
  debugLog('現在日時のタイムスタンプ：' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debugLog('ログイン有効期限タイムスタンプ：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
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
    $err_msg[$key] = ERRMSG['EMPTY'];
  }
}

// 最小文字数チェック
function validMinLen($str, $key, $min = 6)
{
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = $min . ERRMSG['MINLEN'];
  }
}

// 最大文字数チェック
function validMaxLen($str, $key, $max = 255)
{
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = $max . ERRMSG['MAXLEN'];
  }
}

// メールの重複チェック
function validEmailDup($email, $key)
{
  try {
    // DB処理
    $dbh = dbConnect();
    $sql = 'SELECT id FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(
      ':email' => $email
    );
    $stmt = queryPost($dbh, $sql, $data);

    // 取得件数が1件以上の場合は重複
    if (!empty($stmt->rowCount())) {
      debugLog('メールアドレス重複あり');
      global $err_msg;
      $err_msg[$key] = ERRMSG['EMAIL_DUP'];
    } else {
      debugLog('メールアドレス重複なし');
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = ERRMSG['DEFAULT'];
  }
}

// メールのフォーマットチェック
function validEmailFormat($email, $key)
{
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    global $err_msg;
    $err_msg[$key] = ERRMSG['EMAIL_FORMAT'];
  }
}

// 半角英数字チェック
function validHalf($str, $key)
{
  $regax = "/^[0-9a-zA-Z]+$/";
  if (!preg_match($regax, $str)) {
    global $err_msg;
    $err_msg[$key] = ERRMSG['HALF'];
  }
}

// 同値チェック
function validEqual($str1, $str2, $key)
{
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = ERRMSG['PASS_RE_NOT_EQUAL'];
  }
}

// パスワードのバリデーション
function validPass($pass, $key)
{
  // 最小文字数チェック
  validMinLen($pass, $key);
  // 最大文字数チェック
  validMaxLen($pass, $key);
  // 半角英数字チェック
  validHalf($pass, $key);
}

// Emailのバリデーション
function validEmail($email, $key)
{
  // フォーマットチェック
  validEmailFormat($email, $key);
  // 最大文字数チェック
  validMaxLen($email, $key);
}


//-------------------------------------
// データベース
//-------------------------------------
// データベース接続
function dbConnect()
{
  // 本番環境と開発環境でつなぐDBを切り替える
  global $isProduction;
  if ($isProduction) {
    $url = parse_url(getenv('CLEARDB_DATABASE_URL'));
    $dsn = sprintf('mysql:dbname=' . substr($url['path'], 1) . ';host=' . $url['host']) . ';charset=utf8';
    $user = $url['user'];
    $pass = $url['pass'];
  } else {
    $dsn = 'mysql:dbname=toooools;host=localhost;charset=utf8';
    $user = 'root';
    $pass = 'root';
  }

  $options = array(
    // SQL失敗時、PDOExceptionをスロー
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
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
  if (!$stmt->execute($data)) {
    debugLog('クエリ失敗');
    debugLog('失敗したSQL：' . print_r($stmt, true));
    $err_msg['common'] = ERRMSG['DEFAULT'];
    return '';
  }
  debugLog('クエリ成功');
  return $stmt;
}

// ユーザー情報取得
function getUser($u_id)
{
  debugLog('ユーザーデータを取得します');
  debugLog('ユーザID：' . $u_id);

  try {
    // DB処理
    $dbh = dbConnect();
    $sql = 'SELECT id, name, email, password, bio, like_tool, img, mime, created_at FROM users WHERE id = :uid AND delete_flg = 0';
    $data = array(
      ":uid" => $u_id,
    );
    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('ユーザーデータ取得失敗');
      return '';
    }

    return $stmt->fetch();
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return '';
  }
}

// ツール情報取得
function getTool($t_id)
{
  debugLog('ツールデータ取得');
  debugLog('ツールID' . $t_id);

  try {
    // DB処理
    $dbh = dbConnect();
    $sql = 'SELECT id, name, introduction, img, mime, user_id, created_at FROM tools WHERE id = :tid AND user_id = :uid AND delete_flg = 0';
    $data = array(
      ":tid" => $t_id,
      ":uid" => $_SESSION['user_id'],
    );
    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('ツールデータ取得失敗');
      return '';
    }

    return $stmt->fetch();
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return '';
  }
}

// 投稿データ取得
function getToolDetail($t_id)
{
  debugLog('投稿データ取得処理');
  debugLog('ツールID：' . $t_id);

  try {
    $dbh = dbConnect();
    $sql = 'SELECT
    u.id user_id
    , u.name user_name
    , u.img avatar_img
    , u.mime avatar_img_mime
    , t.id tool_id
    , t.name tool_name
    , t.introduction tool_introduction
    , t.img tool_img
    , t.mime tool_img_mime
    , t.created_at
    , IFNULL(l.like_cnt, 0) like_cnt
    , IFNULL(r.reply_cnt, 0) reply_cnt
    FROM tools t
    LEFT JOIN users u
    ON u.id = t.user_id
    LEFT JOIN (SELECT tool_id, COUNT(*) like_cnt FROM likes GROUP BY tool_id) l
    ON l.tool_id = t.id
    LEFT JOIN (SELECT tool_id, COUNT(*) reply_cnt FROM replies WHERE delete_flg = 0 GROUP BY tool_id) r
    ON r.tool_id = t.id
    WHERE t.id = :tid
    AND t.delete_flg = 0
    AND u.delete_flg = 0';
    $data = array(
      ':tid' => $t_id,
    );

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('投稿データを取得できませんでした');
      return '';
    }

    return $stmt->fetch();
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return '';
  }
}

// 投稿データ取得
function getPost($order, $searchTarget, $searchWord, $nowPage)
{
  debugLog('全ての投稿データ取得処理');

  try {
    $dbh = dbConnect();
    $data = array();
    $sql = 'SELECT
      u.id user_id
    , u.name user_name
    , u.img avatar_img
    , u.mime avatar_img_mime
    , t.id tool_id
    , t.name tool_name
    , t.introduction tool_introduction
    , t.img tool_img
    , t.mime tool_img_mime
    , t.created_at
    , IFNULL(l.like_cnt, 0) like_cnt
    , IFNULL(r.reply_cnt, 0) reply_cnt
    FROM tools t
    LEFT JOIN users u
    ON u.id = t.user_id
    LEFT JOIN (SELECT tool_id, COUNT(*) like_cnt FROM likes GROUP BY tool_id) l
    ON l.tool_id = t.id
    LEFT JOIN (SELECT tool_id, COUNT(*) reply_cnt FROM replies WHERE delete_flg = 0 GROUP BY tool_id) r
    ON r.tool_id = t.id
    WHERE t.delete_flg = 0
    AND u.delete_flg = 0';

    if ($searchTarget === 'tool_name') $target = 't.name';
    if ($searchTarget === 'tool_introduction') $target = 't.introduction';
    if (!empty($searchWord) && !empty($target)) {
      $sql .= " AND " . $target . " LIKE :searchWord";
      $data = array(
        ':searchWord' => '%' . $searchWord . '%',
      );
    }
    if ($order === 'create_asc') {
      $sql .= ' ORDER BY t.created_at ASC';
    } elseif ($order === 'like_desc') {
      $sql .= ' ORDER BY IFNULL(l.like_cnt, 0) DESC';
    } else {
      $sql .= ' ORDER BY t.created_at DESC';
    }
    if ($nowPage !== 0) {
      $sql .= ' LIMIT 10 OFFSET ' . (($nowPage - 1) * 10);
    }

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('投稿データを取得できませんでした');
      return '';
    }

    // 全行返す
    return $stmt->fetchAll();
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return '';
  }
}

// 投稿データ取得
function getPostInProfile($u_id, $isLikeShow)
{
  debugLog('全ての投稿データ取得処理');
  debugLog('ユーザーID：' . $u_id);
  try {
    $dbh = dbConnect();
    $sql = 'SELECT
      u.id user_id
    , u.name user_name
    , u.img avatar_img
    , u.mime avatar_img_mime
    , t.id tool_id
    , t.name tool_name
    , t.introduction tool_introduction
    , t.img tool_img
    , t.mime tool_img_mime
    , t.created_at
    , IFNULL(l.like_cnt, 0) like_cnt
    , IFNULL(r.reply_cnt, 0) reply_cnt
    FROM tools t
    LEFT JOIN users u
    ON u.id = t.user_id
    LEFT JOIN (SELECT tool_id, COUNT(*) like_cnt FROM likes GROUP BY tool_id) l
    ON l.tool_id = t.id
    LEFT JOIN (SELECT tool_id, COUNT(*) reply_cnt FROM replies GROUP BY tool_id) r
    ON r.tool_id = t.id
    WHERE t.delete_flg = 0
    AND u.delete_flg = 0';
    if ($isLikeShow) {
      $sql .= ' AND t.id IN (SELECT tool_id FROM likes WHERE user_id = :uid)';
    } else {
      $sql .= ' AND t.user_id = :uid';
    }
    $sql .= ' ORDER BY t.created_at DESC';
    $data = array(
      ':uid' => $u_id
    );

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('投稿データを取得できませんでした');
      return '';
    }

    // 全行返す
    return $stmt->fetchAll();
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return '';
  }
}

// リプライデータ取得
function getReplies($t_id)
{
  debugLog('リプライデータ取得処理');
  debugLog('ツールID：' . $t_id);

  try {
    $dbh = dbConnect();
    $sql = 'SELECT r.message, r.created_at, u.name user_name, u.img avatar_img, u.mime avatar_img_mime FROM replies r LEFT JOIN users u ON u.id = r.tool_id WHERE r.tool_id = :tid AND r.delete_flg = 0';
    $data = array(
      ':tid' => $t_id,
    );

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('リプライデータを取得できませんでした');
      return '';
    }

    // 全行返す
    return $stmt->fetchAll();
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return '';
  }
}

// いいねの数を取得
function getLikeCount($t_id, $u_id)
{
  debugLog('いいねデータ取得処理');
  debugLog('ユーザーID：' . $u_id);
  debugLog('ツールID：' . $t_id);

  try {
    $dbh = dbConnect();
    $data = array();
    $sql = 'SELECT COUNT(*) cnt FROM likes';
    if (!empty($t_id)) {
      $sql .= ' WHERE tool_id = :tid';
      $data += array(':tid' => $t_id);
    }
    if (!empty($u_id)) {
      $sql .= (empty($t_id)) ? ' WHERE' : ' AND';
      $sql .= ' user_id = :uid';
      $data += array(':uid' => $u_id);
    }

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('いいね情報を取得できませんでした');
      return '';
    }

    $result = $stmt->fetch();
    return (int)$result['cnt'];
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return '';
  }
}

// パスワード変更
function changePassword($pass, $u_id)
{
  debugLog('いいねデータ取得処理');
  debugLog('ユーザーID：' . $u_id);

  try {
    // データベース処理
    $dbh = dbConnect();
    $sql = 'UPDATE users SET password = :pass WHERE id = :id';
    $data = array(
      ':pass' => $pass,
      ':id' => $u_id,
    );
    $stmt = queryPost($dbh, $sql, $data);
    
    if (!empty($stmt->rowCount())) {
      debugLog('パスワード変更失敗');
      return false;
    }
    
    debugLog('パスワード変更成功');
    return true;
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    global $err_msg;
    $err_msg['common'] = ERRMSG['DEFAULT'];
  }
}


//-------------------------------------
// メール送信
//-------------------------------------
// メール送信
function sendMail($to, $subject, $comment)
{
  debugLog('メール送信処理開始');

  // 空の項目が存在したら処理しない
  if (empty($to)) return;
  if (empty($subject)) return;
  if (empty($comment)) return;

  // 本番環境と開発環境でメール送信方法を変える
  global $isProduction;
  if ($isProduction) {
    sendMailProduction($to, $subject, $comment);
  } else {
    sendMailDevelopment($to, $subject, $comment);
  }
}

// 本番環境でメール送信
function sendMailProduction($to, $subject, $comment)
{
  require('vendor/autoload.php');

  $email = new \SendGrid\Mail\Mail();
  $email->setFrom(MAIL_FROM);
  $email->addTo($to);
  $email->setSubject($subject);
  $email->addContent("text/plain", $comment);
  $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
  try {
    $response = $sendgrid->send($email);
    debugLog($response->statusCode());
    debugLog(print_r($response->headers(), true));
    debugLog($response->body());
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

// ローカル環境でメール送信
function sendMailDevelopment($to, $subject, $comment)
{
  // 文字化け回避
  mb_language('Japanese');
  mb_internal_encoding('UTF-8');

  // メール送信
  $isSend = mb_send_mail($to, $subject, $comment, 'From: ' . MAIL_FROM);
  if ($isSend) {
    debugLog('メール送信成功');
  } else {
    debugLog('メール送信失敗');
  }
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

  return sanitize($err_msg[$key]);
}

// エラーのクラス名取得
function getErrClassName($key)
{
  // エラーメッセージが存在したらエラークラス名を返す
  if (!empty(getErrMsg($key))) return 'err';

  return '';
}

// フォームに出力する
function getFormData($key)
{
  global $dbFormData;

  // POSTされていたら返す
  if (isset($_POST[$key])) return sanitize($_POST[$key]);

  // SELECT結果があったら返す
  if (!empty($dbFormData[$key])) return sanitize($dbFormData[$key]);

  // 両方存在しなかったら空白を返す
  return '';
}

// 画像アップロード時処理
function getUploadImage($dbFormData, $isImg)
{
  if ($isImg) {
    $key = 'img';
    $return = (!empty($_FILES['img'])) ? imageToBlob($_FILES['img'], 'img') : '';
  } else {
    $key = 'mime';
    $return = (!empty($_FILES['img']['type'])) ? $_FILES['img']['type'] : '';
  }

  if (empty($return)) {
    if (!empty($_SESSION[$key])) {
      $return = $_SESSION[$key];
    } else {
      $return = (!empty($dbFormData)) ? $dbFormData[$key] : '';
    }
  } else {
    $_SESSION[$key] = $return;
  }
  
  return $return;
}

// 画像をバイナリデータに変換
function imageToBlob($file, $key)
{
  try {
    // エラーチェック
    switch ($file['error']) {
      case UPLOAD_ERR_OK: // エラーなし
        break;
      case UPLOAD_ERR_NO_FILE: // 未選択
        return '';
        break;
        // throw new RuntimeException('ファイルが選択されていません');
      case UPLOAD_ERR_INI_SIZE: // iniで指定したサイズオーバー
      case UPLOAD_ERR_FORM_SIZE: // フォームで指定したサイズオーバー
        throw new RuntimeException('ファイルサイズが大きすぎます');
      default:
        throw new RuntimeException('その他エラー');
    }

    // MIMEタイプチェック
    $mimeList = array(
      'image/jpeg',
      'image/gif',
      'image/png'
    );
    $fileMime = $file['type'];
    if (!in_array($fileMime, $mimeList)) {
      throw new RuntimeException('画像を選択してください');
    }

    // バイナリデータに変換する
    $raw_data = file_get_contents($file['tmp_name']);

    return $raw_data;
  } catch (RuntimeException $e) {
    debugLog($e->getMessage());
    global $err_msg;
    $err_msg[$key] = $e->getMessage();
    return '';
  }
}

// imgタグのsrcに指定
function showImage($img, $mime, $key)
{
  // 画像が存在した場合
  if (!empty($img)) {
    $content = base64_encode($img);
    return sanitize('data:' . $mime . ';base64,' . $content);
  }

  // 画像が存在しなかった場合、それぞれのデフォルト画像を表示
  if ($key === 'avatar') {
    $content = base64_encode(file_get_contents('img/avatar_default.png'));
    return sanitize('data:img/png;base64,' . $content);
  }
  if ($key === 'tool') {
    $content = base64_encode(file_get_contents('img/tool_default.png'));
    return sanitize('data:img/png;base64,' . $content);
  }

  return '';
}

// ランダムな文字列を生成する
function makeRandStr($length = 10)
{
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';

  $str = '';
  for ($i = 0; $i < $length; $i++) {
    $str .= $chars[mt_rand(0, 61)];
  }

  return $str;
}

// ページネーション
function pagenation($nowPage, $pageCount)
{
  // 最小ページ、最大ページの設定
  if ($nowPage === 1) {
    $minPage = 1;
    $maxPage = $nowPage + 4;
  } elseif ($nowPage === 2) {
    $minPage = 1;
    $maxPage = $nowPage + 3;
  } elseif ($nowPage === $pageCount) {
    $minPage = $nowPage - 4;
    $maxPage = $pageCount;
  } elseif ($nowPage === $pageCount - 1) {
    $minPage = $nowPage - 3;
    $maxPage = $pageCount;
  } else {
    $minPage = $nowPage - 2;
    $maxPage = $nowPage + 2;
  }

  // 最小ページが0以下にならないようにする
  if ($minPage < 1) $minPage = 1;
  // 最大ページがページ数以上にならないようにする
  if ($maxPage > $pageCount) $maxPage = $pageCount;

  echo '<div class="pagenation">';
  echo '<ul class="pagenation-list">';

  // 遷移先URL作成
  $url = 'index.php?';
  if (!empty($_GET['order'])) $url .= 'order=' . $_GET['order'] . '&';
  if (!empty($_GET['search_target'])) $url .= 'search_target=' . $_GET['search_target'] . '&';
  if (!empty($_GET['search_word'])) $url .= 'search_word=' . $_GET['search_word'] . '&';
  $url .= 'p=';

  // 最初のページに移動するためのリンク作成
  if ($nowPage !== $minPage) {
    echo '<li class="pagenation-item"><a href="' . $url . $minPage . '">&lt;</a></li>';
  }

  // クリックされたページ数に移動するためのリンク作成
  for ($i = $minPage; $i <= $maxPage; $i++) {
    $classNowPage = ($i === $nowPage) ? ' pagination-item-now' : '';
    echo '<li class="pagenation-item' . $classNowPage . '"><a href="' . $url . $i . '">' . $i . '</a></li>';
  }

  // 最後のページに移動するためのリンク作成
  if ($nowPage !== $maxPage) {
    echo '<li class="pagenation-item"><a href="' . $url . $maxPage . '">&gt;</a></li>';
  }

  echo '</ul>';
  echo '</div>';
}

// ログイン認証
function isLogin()
{
  debugLog('ログイン認証を行います。');

  // セッションにuser_idが存在したらログイン済と判断する
  if (!empty($_SESSION['user_id'])) {
    debugLog('ログイン済ユーザーです。');

    // 有効期限の検証
    $maxLoginTime = $_SESSION['login_date'] + $_SESSION['login_limit'];
    if ($maxLoginTime < time()) {
      debugLog('ログイン有効期限切れです');
      if (!empty($_SESSION)) session_destroy();
      return false;
    } else {
      return true;
    }
  } else {
    debugLog('未ログインユーザーです');
    return false;
  }
}

// サニタイズ
function sanitize($str)
{
  if ($str === '') return '';
  return htmlspecialchars($str);
}
