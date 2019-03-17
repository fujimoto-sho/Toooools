<?php
//*************************************
// 投稿詳細
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('投稿詳細ページ');

// ツールID取得
$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
// 投稿データ取得
$postData = (!empty($t_id)) ? getToolDetail($t_id) : '';
// リプライデータ取得
$repliesData = (!empty($t_id)) ? getReplies($t_id) : '';
// いいね数取得
$likeCnt = (!empty($t_id)) ? getLikeCount($t_id, '') : 0;

if (empty($postData)) {
  debugLog('データが取得できなかったため、トップページに遷移します。');
  header("Location:index.php");
}

// リプライ投稿処理
if (!empty($_POST) && empty($_POST['delete_tool_id'])) {
  debugLog('POST：' . print_r($_POST, true));

  $reply = (!empty($_POST['reply'])) ? $_POST['reply'] : '';

  // 未入力チェック
  validEmpty($reply, 'reply');

  if (empty($err_msg)) {
    // リプライ
    // 最大文字数チェック
    validMaxLen($reply, 'reply', 140);

    if (empty($err_msg)) {
      debugLog('バリデーションOK');

      try {
        $dbh = dbConnect();
        debugLog('リプライ投稿');

        $sql = 'INSERT INTO replies (message, tool_id, user_id, created_at)';
        $sql .= 'VALUES (:message, :tid, :uid, :date)';
        $data = array(
          ':message' => $reply,
          ':tid' => $t_id,
          ':uid' => $_SESSION['user_id'],
          ':date' => date('Y-m-d H:i:s'),
        );

        $stmt = queryPost($dbh, $sql, $data);

        if (!empty($stmt->rowCount())) {
          debugLog('リプライ投稿更新成功');

          // フラッシュメッセージセット
          $_SESSION['flash_msg'] = SUCMSG['REPLY_SEND'];
          debugLog('投稿詳細に遷移します。');

          header("Location:postDetail.php?t_id=" . $t_id);
        } else {
          debugLog('ユーザー情報更新失敗');
          $err_msg['common'] = ERRMSG['DEFAULT'];
        }
      } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = ERRMSG['DEFAULT'];
      }
    }
  }
}

// 削除処理
if (!empty($_POST['delete_tool_id']) && $_GET['t_id'] === $_POST['delete_tool_id']) {
  debugLog('POST：' . print_r($_POST, true));

  $dlt_t_id = $_POST['delete_tool_id'];
  $postData = (!empty($dlt_t_id)) ? getToolDetail($dlt_t_id) : '';
  if ($postData['user_id'] === $_SESSION['user_id']) {
    try {
      $dbh = dbConnect();
      debugLog('投稿削除');

      $sql1 = 'UPDATE tools SET delete_flg = 1 WHERE id = :tid';
      $sql2 = 'UPDATE replies SET delete_flg = 1 WHERE tool_id = :tid';
      $sql3 = 'DELETE FROM likes WHERE tool_id = :tid';

      $data = array(
        ':tid' => $dlt_t_id,
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

      if (!empty($stmt1->rowCount())) {
        debugLog('投稿削除成功');

        // フラッシュメッセージセット
        $_SESSION['flash_msg'] = SUCMSG['POST_DELETE'];
        debugLog('プロフィールページに遷移します。');

        header("Location:profile.php");
      } else {
        debugLog('投稿削除失敗');
        $err_msg['common'] = ERRMSG['DEFAULT'];
      }
    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = ERRMSG['DEFAULT'];
    }
  }
}

// 終了ログ
debugLogEnd();
$pageTitle = '投稿詳細';
// ヘッダー;
require_once('header.php');
?>

<form id="js-dlt-form" method="post">
    <input type="hidden" name="delete_tool_id" value="<?php echo $t_id; ?>">
</form>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- 投稿 -->
  <div class="post-detail">
    <?php
      $post = $postData;
      require_once('postItem.php');
    ?>

    <?php if (isLogin()): ?>
      <div class="post-detail-reply">
        <form action="" method="post">

          <!-- 共通メッセージ -->
          <div class="input-msg">
            <?php echo getErrMsg('common'); ?>
          </div>

          <!-- リプライ -->
          <div class="input-msg">
            <?php echo getErrMsg('reply'); ?>
          </div>
          <input type="text" name="reply" value="<?php echo getFormData('reply'); ?>">

          <input type="submit" value="送信">
        </form>
      </div>
    <?php endif; ?>
    <?php
      if (!empty($repliesData)):
        foreach ($repliesData as $row):
    ?>
          <div class="post-list">
            <div class="post-detail-reply-icon">
              <i class="fas fa-reply"></i>
              reply
            </div>
            <img src="<?php echo showImage($row['avatar_img'], $row['avatar_img_mime'], 'avatar'); ?>" alt="" class="post-user-img">
            <p class="post-user-name">
              <?php echo sanitize($row['user_name']); ?>
            </p>
            <time class="post-time" datetime="<?php echo sanitize($row['created_at']); ?>">
              <?php echo sanitize($row['created_at']); ?></time>
            <p class="post-reply-text">
              <?php echo sanitize($row['message']); ?>
            </p>
          </div>
    <?php
        endforeach;
      endif;
    ?>

  </div>

</main>

<!-- フッター -->
<?php require_once('footer.php'); ?>
