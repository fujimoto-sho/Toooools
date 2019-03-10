<?php
//*************************************
// 投稿作成
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('投稿作成ページ');

// ログイン認証
require_once('auth.php');

// ツールID設定
$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
// ツールデータ取得
$dbFormData = (!empty($t_id)) ? getTool($t_id) : '';
// 該当するツールのデータが存在したら編集、しなかったら新規
$isCreate = (empty($dbFormData)) ? true : false;

// ツールデータが存在するが、ユーザIDが違う場合、投稿詳細ページへ遷移させる
if (!$isCreate && $dbFormData['user_id'] === $_SESSION['user_id']) {
  debugLog('投稿詳細に遷移します');
  header("Location:postDetail.php?t_id=" . $t_id);
}

// POSTされていなくても画像を表示する
$img = (!empty($dbFormData['img'])) ? $dbFormData['img'] : '';
$mime = (!empty($dbFormData['mime'])) ? $dbFormData['mime'] : '';

// POSTされていなかったら画像保持用のセッションを削除する
if (empty($_POST)) {
  unset($_SESSION['img']);
  unset($_SESSION['mime']);
}

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $name = $_POST['name'];
  $introduction = $_POST['introduction'];
  $img = getUploadImage($dbFormData, true);
  $mime = getUploadImage($dbFormData, false);

  // 未入力チェック
  validEmpty($name, 'name');
  validEmpty($introduction, 'introduction');

  if (empty($err_msg)) {
    // ツール名
    // 最大文字数チェック
    validMaxLen($name, 'name', 30);

    // ツール紹介文
    // 最大文字数チェック
    validMaxLen($introduction, 'introduction', 500);

    if (empty($err_msg)) {
      debugLog('バリデーションOK');

      try {
        $dbh = dbConnect();
        if ($isCreate) {
          debugLog('ツール登録');
          $sql = 'INSERT INTO tools (name, introduction, img, mime, user_id, created_at)';
          $sql .= 'VALUES (:name, :introduction, :img, :mime, :uid, :date)';
          $data = array(
            ':name' => $name,
            ':introduction' => $introduction,
            ':img' => $img,
            ':mime' => $mime,
            ':uid' => $_SESSION['user_id'],
            ':date' => date('Y-m-d H:i:s'),
          );
        } else {
          if (!empty($img)) {
            debugLog('ツール更新');
            $sql = 'UPDATE tools SET name = :name, introduction = :introduction, img = :img, mime = :mime  WHERE id = :tid';
            $data = array(
              ':name' => $name,
              ':introduction' => $introduction,
              ':img' => $img,
              ':mime' => $mime,
              ':tid' => $t_id,
            );
          } else {
            $sql = 'UPDATE tools SET name = :name, introduction = :introduction WHERE id = :tid';
            $data = array(
              ':name' => $name,
              ':introduction' => $introduction,
              ':tid' => $t_id,
            );
          }
        }

        $stmt = queryPost($dbh, $sql, $data);

        if (!empty($stmt)) {
          debugLog('ツール情報更新成功');

          // フラッシュメッセージセット
          $_SESSION['flash_msg'] = ($isCreate) ? SUCMSG['POST_INSERT'] : SUCMSG['POST_UPDATE'];
          debugLog('投稿詳細に遷移します。');

          $t_id = ($isCreate) ? $dbh->lastInsertId() : $t_id;

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

// 終了ログ
debugLogEnd();
$pageTitle = ($isCreate) ? '新規投稿' : '投稿編集';
// ヘッダー;
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- フォーム -->
  <div class="form-container">
    <form class="form" method="post" enctype="multipart/form-data">
      <h1 class="form-title">
        <?php echo $pageTitle; ?>
      </h1>

      <!-- 共通メッセージ -->
      <div class="input-msg">
        <?php echo getErrMsg('common'); ?>
      </div>

      <!-- ツール名 -->
      <div class="input-msg">
        <?php echo getErrMsg('name'); ?>
      </div>
      <label class="form-label <?php echo getErrClassName('name'); ?>">
        ツール名
        <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
      </label>

      <!-- ツール紹介 -->
      <div class="input-msg">
        <?php echo getErrMsg('introduction'); ?>
      </div>
      <label class="form-label <?php echo getErrClassName('introduction'); ?>">
        ツール紹介
        <textarea name="introduction" cols="30" rows="5"><?php echo getFormData('introduction'); ?></textarea>
      </label>

      <!-- ツール画像 -->
      <div class="input-msg">
        <?php echo getErrMsg('img'); ?>
      </div>
      ツール画像
      <div class="form-input-container">
        <label class="form-label form-label-file">
          <input type="hidden" name="MAX_FILE_SIZE" value="3000000">
          <input type="file" name="img" id="js-img-input" hidden>
          <img src="<?php echo showImage($img, $mime, 'tool'); ?>" id="js-img-show" class="form-input-file-img">
        </label>
      </div>

      <input type="submit" class="form-btn" value="投稿">
    </form>
  </div>
</main>

<!-- フッター -->
<?php require_once('footer.php'); ?>
