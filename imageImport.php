<?php
//*************************************
// 画像取得
//*************************************
// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('画像を取得します');


$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';

$mimeList = array(
  'image/jpeg',
  'image/gif',
  'image/png'
);

try {
  $dbh = dbConnect();
  $sql = 'SELECT tool_img, tool_img_mime FROM tools WHERE id = :tid AND delete_flg = 0';
  $data = array(
    ':tid' => $t_id,
  );
  $stmt = queryPost($dbh, $sql, $data);

  if ($stmt) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    header("Content-type: " . $result['tool_img_mime']);
    echo $result['tool_img'];
  }
  
} catch (Exception $e) {
  error_log('エラー発生：' . $e->getMessage());
}
