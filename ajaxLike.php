<?php
//*************************************
// いいね登録・削除
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('いいね登録・削除');

// ツールID
$t_id = (!empty($_POST['tool_id'])) ? $_POST['tool_id'] : '';
// ユーザーID
$u_id = (!empty($_SESSION['user_id'])) ? $_SESSION['user_id'] : '';

// ログインしている場合のみ処理を実行
if (isLogin() && !empty($t_id)) {
  try {
    // いいねの数を取得
    $likeCnt = getLikeCount($t_id, $u_id);

    //DB処理
    $dbh = dbConnect();

    // いいねをしていなかったら登録、していたら削除
    if ($likeCnt === 0) {
      debugLog('いいねを登録します');
      $sql = 'INSERT INTO likes (tool_id, user_id, created_at) VALUES (:tid, :uid, :date)';
      $data = array(
        ':tid' => $t_id,
        ':uid' => $u_id,
        ':date' => date('Y-m-d H:i:s'),
      );
    } else {
      debugLog('いいねを削除します');
      $sql = 'DELETE FROM likes WHERE tool_id = :tid AND user_id = :uid';
      $data = array(
        ':tid' => $t_id,
        ':uid' => $u_id,
      );
    }

    $stmt = queryPost($dbh, $sql, $data);

    // ツールのいいね数を返却
    echo getLikeCount($t_id, '');
  } catch (Exception $e) {
    debugLog('エラー発生：' . $e->getMessage());
  }
}
