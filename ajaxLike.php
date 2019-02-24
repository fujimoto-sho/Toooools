<?php
//*************************************
// お気に入り登録・削除
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('お気に入り登録・削除');

$t_id = (!empty($_POST['tool_id'])) ? $_POST['tool_id'] : '';

if (isLogin() && !empty($t_id)) {

  try {
    debugLog('お気に入りの登録をします。');
    $dbh = dbConnect();
    $sql = 'SELECT COUNT(*) cnt FROM likes WHERE tools_id = :tid AND user_id = :uid';
    $data = array(
      ':tid' => $t_id,
      ':uid' => $_SESSION['user_id'],
    );
    $stmt = queryPost($dbh, $sql, $data);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $likeCnt = (int)$result['cnt'];
    if($likeCnt === 0) {
      debugLog('お気に入り登録します');
      $sql = 'INSERT INTO likes (tools_id, user_id, created_at) VALUES (:tid, :uid, :date)';
      $data = array(
        ':tid' => $t_id,
        ':uid' => $_SESSION['user_id'],
        ':date' => date('Y-m-d H:i:s'),
      );
      $likeCnt += 1;
    } else {
      debugLog('お気に入り削除します');
      $sql = 'DELETE FROM likes WHERE tools_id = :tid AND user_id = :uid';
      $data = array(
        ':tid' => $t_id,
        ':uid' => $_SESSION['user_id'],
      );
      $likeCnt -= 1;
    }
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt->rowCount() > 0) {
      echo $likeCnt;
    }

  } catch (Exception $e) {
    debugLog('エラー発生：' . $e->getMessage());
  }
}