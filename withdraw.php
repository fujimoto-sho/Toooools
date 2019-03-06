<?php
//*************************************
// 退会機能
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('退会ページ');

// ログイン認証
require_once('auth.php');

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  try {
    // DB処理
    $dbh = dbConnect();
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :uid';
    $sql2 = 'UPDATE tools SET delete_flg = 1 WHERE user_id = :uid';
    $sql3 = 'UPDATE likes SET delete_flg = 1 WHERE user_id = :uid';
    $data = array(
      ':uid' => $_SESSION['user_id'],
    );

    // トランザクション開始
    $dbh->beginTransaction();

    // コミットorロールバックを必ず行うため、try-catchで囲む
    try {
      // 更新処理を実行
      $stmt1 = queryPost($dbh, $sql1, $data);
      $stmt2 = queryPost($dbh, $sql2, $data);
      $stmt3 = queryPost($dbh, $sql3, $data);

      // 全て成功したらコミット
      $dbh->commit();
    } catch (PDOException $e) {
      // どれか1つでもエラーが発生したらロールバック
      $dbh->rollback();

      // エラー処理は上位で行う
      throw $e;
    }

    // usersテーブルが処理されていたら成功とする
    if (!empty($stmt1->rowCount())) {
      debugLog('退会完了');

      debugLog('ログイン画面に遷移します。');
      header("Location:login.php");
    } else {
      debugLog('退会失敗');
      $err_msg['common'] = ERRMSG['DEFAULT'];
    }

  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = ERRMSG['DEFAULT'];
  }

}

// 終了ログ
debugLogEnd();
$pageTitle = '退会';
// ヘッダー;
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- フォーム -->
  <div class="form-container">
    <form class="form" method="post">
      <h1 class="form-title">退会</h1>

      <!-- 共通メッセージ -->
      <div class="input-msg">
        <?php echo getErrMsg('common'); ?>
      </div>

      <p class="form-p">
        本当に退会しますか？
      </p>

      <input type="submit" name="withdraw" class="form-btn" value="退会">
    </form>
  </div>
</main>

<!-- フッター -->
<?php require_once('footer.php'); ?>
