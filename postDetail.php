<?php
//*************************************
// 投稿詳細
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('投稿詳細ページ');

$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
$postData = (!empty($t_id)) ? getToolDetail($t_id) : '';
$repliesData = (!empty($t_id)) ? getReplies($t_id) : '';
$likeCnt = (!empty($t_id)) ? getLikes($t_id) : 0;

if (empty($postData)) {
  debugLog('データが取得できなかったため、トップページに遷移します。');
  header("Location:index.php");
}

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $reply = (!empty($_POST['reply'])) ? $_POST['reply'] : '';

  // 未入力チェック
  validEmpty($reply, 'reply');

  if (empty($err_msg)) {
    // ツール名
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

        if ($stmt) {
          debugLog('リプライ投稿更新成功');

          // フラッシュメッセージセット
          $_SESSION['flash_msg'] = SUC06;
          debugLog('投稿詳細に遷移します。');

          header("Location:postDetail.php?t_id=" . $t_id);
        } else {
          debugLog('ユーザー情報更新失敗');
          $err_msg['common'] = MSG02;
        }

      } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG02;
      }
    }
  }
}

// 終了ログ
debugLogEnd();
$pageTitle = '投稿詳細';
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- 投稿 -->
  <div class="post-detail">
    <div class="post-list">
      <img src="<?php echo showImage($postData['avatar_img'], $postData['avatar_img_mime']); ?>" alt="" class="post-user-img">
      <p class="post-user-name"><?php echo $postData['user_name']; ?></p>
      <time class="post-time" datetime="<?php echo $postData['created_at'] ?>"><?php echo $postData['created_at']; ?></time>
      <h1 class="post-tool-name"><?php echo $postData['tool_name']; ?></h1>
      <div class="post-wrap-center">
        <p class="post-tool-introduction">
          <?php echo $postData['tool_introduction']; ?>
        </p>
        <img src="<?php echo showImage($postData['tool_img'], $postData['tool_img_mime']); ?>" alt="" class="post-tool-img">
      </div>
      <div class="post-wrap-icon">
        <i class="fas fa-reply"></i>
        <span class="post-reply-count"><?php echo count($repliesData) ?></span>
        <i class="fas fa-heart js-like-icon <?php if ($likeCnt > 0) echo 'fa-heart-active' ?>" data-tool_id="<?php echo $postData['tool_id']; ?>"></i>
        <span class="post-like-count"><?php echo $likeCnt; ?></span>
        <i class="fas fa-angle-down fa-lg"></i>
      </div>
    </div>

    <div class="post-detail-reply">
      <form action="" method="post">
        <!-- 共通メッセージ -->
        <div class="input-msg">
          <?php echo getErrMsg('common'); ?>
        </div>
        <div class="input-msg">
          <?php echo getErrMsg('reply'); ?>
        </div>
        <input type="text" name="reply" value="<?php echo getFormData('reply'); ?>">
        <input type="submit" value="送信">
      </form>
    </div>

    <?php
      if(!empty($repliesData)) :
        foreach($repliesData as $row) :
    ?>
          <div class="post-list">
            <div class="post-detail-reply-icon">
              <i class="fas fa-reply"></i>
              reply
            </div>
            <img src="<?php echo showImage($postData['avatar_img'], $postData['avatar_img_mime']); ?>" alt="" class="post-user-img">
            <p class="post-user-name"><?php echo $row['user_name']; ?></p>
            <time class="post-time" datetime="<?php echo $row['created_at']; ?>"><?php echo $row['created_at']; ?></time>
            <p class="post-reply-text">
              <?php echo $row['message']; ?>
            </p>
          </div>
    <?php
        endforeach;
      endif;
    ?>

  </div>

</main>

<?php require_once('footer.php'); ?>