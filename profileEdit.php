<?php
//*************************************
// プロフィール編集
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('プロフィール編集ページ');

// ログイン認証
require_once('auth.php');

// フォーム表示用のデータ取得
$dbFormData = getUser($_SESSION['user_id']);

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

  $email = $_POST['email'];
  $name = $_POST['name'];
  $like_tool = $_POST['like_tool'];
  $bio = $_POST['bio'];
  $img = getUploadImage($dbFormData, true);
  $mime = getUploadImage($dbFormData, false);

  // 未入力チェック
  validEmpty($email, 'email');
  validEmpty($name, 'name');

  if (empty($err_msg)) {
    // Email
    if ($_POST['email'] !== $dbFormData['email']) {
      // Emailのバリデーション
      validEmail($email, 'email');
      // 重複チェック
      validEmailDup($email, 'email');
    }

    // ユーザー名
    if ($_POST['name'] !== $dbFormData['name']) {
      // 最大文字数チェック
      validMaxLen($name, 'name', 30);
    }

    // 一番好きなツール
    if ($_POST['like_tool'] !== $dbFormData['like_tool']) {
      // 最大文字数チェック
      validMaxLen($like_tool, 'like_tool', 30);
    }

    // 自己紹介
    if ($_POST['bio'] !== $dbFormData['bio']) {
      // 最大文字数チェック
      validMaxLen($bio, 'bio', 150);
    }

    if (empty($err_msg)) {
      debugLog('バリデーションOK');

      try {
        debugLog('ユーザー情報更新');
        $dbh = dbConnect();
        if (empty($img)) {
          // 画像が選択されていない場合
          $sql = 'UPDATE users SET name = :name, email = :email, like_tool = :like_tool, bio = :bio WHERE id = :id';
          $data = array(
            ':id' => $_SESSION['user_id'],
            ':name' => $name,
            ':email' => $email,
            ':like_tool' => $like_tool,
            ':bio' => $bio,
          );
        } else {
          // 画像が選択されている場合
          $sql = 'UPDATE users SET name = :name, email = :email, like_tool = :like_tool, bio = :bio, img = :img, mime = :mime WHERE id = :id';
          $data = array(
            ':id' => $_SESSION['user_id'],
            ':name' => $name,
            ':email' => $email,
            ':like_tool' => $like_tool,
            ':bio' => $bio,
            ':img' => $img,
            ':mime' => $mime,
          );
        }

        $stmt = queryPost($dbh, $sql, $data);

        if (!empty($stmt->rowCount())) {
          debugLog('ユーザー情報更新成功');

          // フラッシュメッセージセット
          $_SESSION['flash_msg'] = SUCMSG['PROF_EDIT'];
          debugLog('プロフィールに遷移します。');

          header("Location:profile.php");
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
$pageTitle = 'プロフィール編集';
// ヘッダー;
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- フォーム -->
  <div class="form-container">
    <form class="form" method="post" enctype="multipart/form-data">
      <h1 class="form-title">プロフィール編集</h1>

      <!-- 共通メッセージ -->
      <div class="input-msg">
        <?php echo getErrMsg('common'); ?>
      </div>

      <!-- Email -->
      <div class="input-msg">
        <?php echo getErrMsg('email'); ?>
      </div>
      <label class="form-label <?php echo getErrClassName('email'); ?>">
        Email
        <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
      </label>

      <!-- ユーザー名 -->
      <div class="input-msg">
        <?php echo getErrMsg('name'); ?>
      </div>
      <label class="form-label <?php echo getErrClassName('name'); ?>">
        ユーザー名
        <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
      </label>

      <!-- 一番好きなツール -->
      <div class="input-msg">
        <?php echo getErrMsg('like_tool'); ?>
      </div>
      <label class="form-label <?php echo getErrClassName('like_tool'); ?>">
        一番好きなツール
        <input type="text" name="like_tool" value="<?php echo getFormData('like_tool'); ?>">
      </label>

      <!-- 自己紹介 -->
      <div class="input-msg">
        <?php echo getErrMsg('bio'); ?>
      </div>
      <label class="form-label <?php echo getErrClassName('bio'); ?>">
        自己紹介
        <textarea name="bio" cols="30" rows="5"><?php echo getFormData('bio'); ?></textarea>
      </label>

      <!-- プロフィール画像 -->
      <div class="input-msg">
        <?php echo getErrMsg('img'); ?>
      </div>
      <div class="form-input-container">
      <label class="form-label form-label-file">
        ツール画像
          <input type="hidden" name="MAX_FILE_SIZE" value="1500000">
          <input type="file" name="img" id="js-img-input" hidden>
          <img src="<?php echo showImage($img, $mime, 'avatar'); ?>" id="js-img-show" class="form-input-file-img">
        </label>
      </div>

      <input type="submit" class="form-btn" value="変更">
    </form>
  </div>
</main>

<!-- フッター -->
<?php require_once('footer.php'); ?>