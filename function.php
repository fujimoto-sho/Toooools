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
// 定数
//-------------------------------------
// ログイン有効期限のデフォルト（1時間）
define('LOGIN_TIME_DEFAULT', 60 * 60);
// ログイン有効期限の最大（30日）
define('LOGIN_TIME_LONG', 60 * 60 * 24 * 30);

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
define('MSG09', 'メールアドレス、またはパスワードが違います。');
define('MSG10', '現在のパスワードが違います。');
define('MSG11', '現在のパスワードと新しいパスワードが同じです。');
define('MSG12', '新しいパスワード（再入力）が合っていません。');
define('MSG13', '登録されていないメールアドレスです。');
define('SUC01', 'プロフィールを変更しました。');
define('SUC02', 'パスワードを変更しました。');
define('SUC03', 'パスワードの再設定が完了しました。');
define('SUC04', '投稿が完了しました。');
define('SUC05', '投稿を編集しました。');
define('SUC06', 'リプライを送信しました。');
define('SUC07', '投稿を削除しました。');

//-------------------------------------
// セッション
//-------------------------------------
// セッションファイルを保存する。/var/tmp/ 以下に保存すると30日保持される。
session_save_path('/var/tmp/');
// ガーベージコレクションで回収される有効期限を伸ばす（デフォルト24分）
ini_set('session.gc_maxlifetime', LOGIN_TIME_DEFAULT);
// セッション開始
session_start();
// セッションを再生成（なりすまし対策）
session_regenerate_id();

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
// デバッグログ（画面表示開始）
function debugLogStart($title)
{
  debugLog('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
  debugLog('「 ' . $title);
  debugLog('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
  debugLog('画面表示開始 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
  debugLog('セッションID：' . session_id());
  debugLog('セッション：' . print_r($_SESSION, true));
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
    $sql = 'SELECT * FROM users WHERE email = :email AND delete_flg = 0';
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
  $stmt->execute($data);
  if ($stmt) {
    debugLog('クエリ成功');
  } else {
    debugLog('クエリ失敗');
    debugLog('失敗したクエリ：' . print_r($stmt, true));
    $err_msg['common'] = MSG02;
    return 0;
  }
  return $stmt;
}
// usersテーブル取得
function getUser($u_id)
{
  global $err_msg;
  debugLog('ユーザーデータ取得');

  try {
    // DB処理
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE id = :uid AND delete_flg = 0';
    $data = array(
      ":uid" => $u_id,
    );
    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('ユーザーデータ取得失敗');
      return 0;
    }

    return $stmt->fetch(PDO::FETCH_ASSOC);

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG02;
  }
}
// toolsテーブル取得
function getTool($id)
{
  global $err_msg;
  debugLog('ツールデータ取得');

  try {
    // DB処理
    $dbh = dbConnect();
    $sql = 'SELECT * FROM tools WHERE id = :id AND user_id = :uid AND delete_flg = 0';
    $data = array(
      ":id" => $id,
      ":uid" => $_SESSION['user_id'],
    );
    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('ユーザーデータ取得失敗');
      return 0;
    }

    return $stmt->fetch(PDO::FETCH_ASSOC);

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG02;
  }
}
// パスワード変更
function changePassword($pass, $userId)
{
  global $err_msg;
  try {
    // データベース処理
    $dbh = dbConnect();
    $sql = 'UPDATE users SET password = :pass WHERE id = :id';
    $data = array(
      ':pass' => $pass,
      ':id' => $userId,
    );
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      debugLog('パスワード変更失敗');
      return false;
    }

    debugLog('パスワード変更成功');
    return true;

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG02;
  }

}
// 投稿データ取得
function getToolDetail($t_id)
{

  debugLog('投稿データ取得処理');
  
  try {
    $dbh = dbConnect();
    $sql = 'SELECT u.id user_id, u.name user_name, u.avatar_img, u.avatar_img_mime, t.id tool_id, t.tool_name, t.tool_introduction, t.tool_img, t.tool_img_mime , t.created_at FROM tools t LEFT JOIN users u ON u.id = t.user_id WHERE t.id = :tid AND t.delete_flg = 0 AND u.delete_flg = 0';
    $data = array(
      ':tid' => $t_id,
    );

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('投稿データを取得できませんでした');
      return 0;
    }

    debugLog('投稿データを取得できました');
    return $stmt->fetch(PDO::FETCH_ASSOC);

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return 0;
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
    , u.avatar_img
    , u.avatar_img_mime
    , t.id tool_id
    , t.tool_name
    , t.tool_introduction
    , t.tool_img
    , t.tool_img_mime
    , t.created_at
    , IFNULL(l.like_cnt, 0) like_cnt
    , IFNULL(r.reply_cnt, 0) reply_cnt
    FROM tools t
    LEFT JOIN users u
    ON u.id = t.user_id
    LEFT JOIN (SELECT tools_id, COUNT(*) like_cnt FROM likes GROUP BY tools_id) l
    ON l.tools_id = t.id
    LEFT JOIN (SELECT tool_id, COUNT(*) reply_cnt FROM replies GROUP BY tool_id) r
    ON r.tool_id = t.id
    WHERE t.delete_flg = 0zz
    AND u.delete_flg = 0';

    if ($searchTarget === 'user_name') $target = 'u.name';
    if ($searchTarget === 'tool_name') $target = 't.tool_name';
    if ($searchTarget === 'tool_introduction') $target = 't.tool_introduction';
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
      return 0;
    }

    debugLog('投稿データを取得できました');
    return $stmt->fetchAll();

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return 0;
  }
}
// 投稿データ取得
function getPostInProfile($u_id, $isLikeShow)
{

  debugLog('全ての投稿データ取得処理');

  try {
    $dbh = dbConnect();
    $sql = 'SELECT
      u.id user_id
    , u.name user_name
    , u.avatar_img
    , u.avatar_img_mime
    , t.id tool_id
    , t.tool_name
    , t.tool_introduction
    , t.tool_img
    , t.tool_img_mime
    , t.created_at
    , IFNULL(l.like_cnt, 0) like_cnt
    , IFNULL(r.reply_cnt, 0) reply_cnt
    FROM tools t
    LEFT JOIN users u
    ON u.id = t.user_id
    LEFT JOIN (SELECT tools_id, COUNT(*) like_cnt FROM likes GROUP BY tools_id) l
    ON l.tools_id = t.id
    LEFT JOIN (SELECT tool_id, COUNT(*) reply_cnt FROM replies GROUP BY tool_id) r
    ON r.tool_id = t.id
    WHERE t.delete_flg = 0
    AND u.delete_flg = 0
    AND u.id = :uid';
    if ($isLikeShow) {
      $sql .= ' AND t.id IN (SELECT tools_id FROM likes WHERE user_id = :uid)';
    }
    $sql .= ' ORDER BY t.created_at';
    $data = array(
      ':uid' => $u_id
    );

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('投稿データを取得できませんでした');
      return 0;
    }

    debugLog('投稿データを取得できました');
    return $stmt->fetchAll();

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return 0;
  }
}
// リプライデータ取得
function getReplies($t_id)
{
  debugLog('リプライデータ取得処理');
  
  try {
    $dbh = dbConnect();
    $sql = 'SELECT r.message, r.created_at, u.name user_name, u.avatar_img, u.avatar_img_mime FROM replies r LEFT JOIN users u ON u.id = r.tool_id WHERE r.tool_id = :tid AND r.delete_flg = 0';
    $data = array(
      ':tid' => $t_id,
    );

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('リプライデータを取得できませんでした');
      return 0;
    }

    debugLog('リプライデータを取得できました');
    return $stmt->fetchAll();

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return 0;
  } 
}
// お気に入りデータ取得
function getLikes($t_id)
{
  debugLog('お気に入りデータ取得処理');
  
  try {
    $dbh = dbConnect();
    $sql = 'SELECT COUNT(*) cnt FROM likes WHERE tools_id = :tid AND user_id = :uid';
    $data = array(
      ':tid' => $t_id,
      ':uid' => $_SESSION['user_id'],
    );

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('お気に入りデータを取得できませんでした');
      return 0;
    }

    debugLog('お気に入りデータを取得できました');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$result['cnt'];

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return 0;
  }
}
function getUserLikes($u_id)
{
  debugLog('お気に入りデータ取得処理');
  
  try {
    $dbh = dbConnect();
    $sql = 'SELECT COUNT(*) cnt FROM likes WHERE user_id = :uid';
    $data = array(
      ':uid' => $u_id,
    );

    $stmt = queryPost($dbh, $sql, $data);

    if (!$stmt) {
      debugLog('お気に入りデータを取得できませんでした');
      return 0;
    }

    debugLog('お気に入りデータを取得できました');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)$result['cnt'];

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    return 0;
  }
}

//-------------------------------------
// メール送信
//-------------------------------------
// メール送信
function sendMail($from, $to, $subject, $comment)
{
  debugLog('メール送信処理開始');

  // 空が存在したら処理しない
  if (empty($from)) return;
  if (empty($to)) return;
  if (empty($subject)) return;
  if (empty($comment)) return;

  // 文字化け回避
  mb_language('Japanese');
  mb_internal_encoding('UTF-8');

  // メール送信
  $isSend = mb_send_mail($to, $subject, $comment, 'From: ' . $from);
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

  return $err_msg[$key];
}
// POSTされていたらその文字列を返す
function getFormData($key)
{
  global $dbFormData;

  // POSTされていたら返す
  if (isset($_POST[$key])) return $_POST[$key];

  // SELECT結果があったら返す
  if (!empty($dbFormData[$key])) return $dbFormData[$key];

  // 両方存在しなかったら空白を返す
  return '';
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
    // $fileMime = mime_content_type($file['name']);
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
function getImage($isCreate, $t_id, $key)
{
  global $tool_img;
  global $tool_img_mime;
  if (!empty($tool_img)) {
    $content = base64_encode($tool_img);
    return 'data:' . $tool_img_mime . ';base64,' . $content;
  }

  // if (!$isCreate && empty($_FILES[$key])) return 'imageImport.php?t_id=' . $t_id;

  return '';

}
// imgタグのsrcに指定
function getImageAvatar()
{
  global $avatar_img;
  global $avatar_img_mime;
  if (!empty($avatar_img)) {
    $content = base64_encode($avatar_img);
    return 'data:' . $avatar_img_mime . ';base64,' . $content;
  }

  return '';

}
// imgタグのsrcに指定
function showImage($tool_img, $tool_img_mime)
{
  if (!empty($tool_img)) {
    $content = base64_encode($tool_img);
    return 'data:' . $tool_img_mime . ';base64,' . $content;
  }

  // if (!$isCreate && empty($_FILES[$key])) return 'imageImport.php?t_id=' . $t_id;

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

  echo '<div class="pagenation">';
  echo '<ul class="pagenation-list">';

  // 最小ページが0以下にならないようにする
  if ($minPage < 1) $minPage = 1;
  // 最大ページがページ数以上にならないようにする
  if ($maxPage > $pageCount) $maxPage = $pageCount;

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

  // セッションにlogin_dateが存在したらログイン済と判断する
  if (!empty($_SESSION['login_date'])) {
    debugLog('ログイン済ユーザーです。');

    // 有効期限の検証
    $maxLoginTime = $_SESSION['login_date'] + $_SESSION['login_limit'];
    if ($maxLoginTime < time()) {
      debugLog('ログイン有効期限切れです');
      session_destroy();
      return false;

    } else {
      return true;
    }

  } else {
    debugLog('未ログインユーザーです');
      return false;
  }
}