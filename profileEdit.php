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

$dbFormData = getUser();

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $email = $_POST['email'];
  $name = $_POST['name'];
  $like_tool = $_POST['like_tool'];
  $bio = $_POST['bio'];

  // 未入力チェック
  validEmpty($email, 'email');
  validEmpty($name, 'name');

  if (empty($err_msg)) {
    // Email
    if ($_POST['email'] !== $dbFormData['email']) {
      // フォーマットチェック
      validEmailFormat($email, 'email');
      // 最大文字数チェック
      validMaxLen($email, 'email');
      // 重複チェック
      validEmailDup($email, 'email');
    }

    // ユーザー名
    if ($_POST['name'] !== $dbFormData['name']) {
      // 最大文字数チェック
      validMaxLen($name, 'name');
    }


    // 一番好きなツール
    if ($_POST['like_tool'] !== $dbFormData['like_tool']) { 
      // 最大文字数チェック
      validMaxLen($like_tool, 'like_tool');
    }

    // 自己紹介
    if ($_POST['bio'] !== $dbFormData['bio']) {
      // 最大文字数チェック
      validMaxLen($bio, 'bio', 500);
    }

    if (empty($err_msg)) {
      debugLog('バリデーションOK');

      try {
        debugLog('ユーザー情報更新');
        $dbh = dbConnect();
        $sql = 'UPDATE users SET name = :name, email = :email, like_tool = :like_tool, bio = :bio WHERE id = :id';
        $data = array(
          ':id' => $_SESSION['user_id'],
          ':name' => $name,
          ':email' => $email,
          ':like_tool' => $like_tool,
          ':bio' => $bio,
        );

        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
          debugLog('ユーザー情報更新成功');

          // フラッシュメッセージセット
          $_SESSION['flash_msg'] = SUC01;
          debugLog('プロフィールに遷移します。');

          header("Location:profile.php");
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
$pageTitle = 'プロフィール編集';
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
      <label class="form-label <?php if (!empty(getErrMsg('email'))) echo 'err'; ?>">
        Email
        <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
      </label>

      <!-- ユーザー名 -->
      <div class="input-msg">
        <?php echo getErrMsg('name'); ?>
      </div>
      <label class="form-label <?php if (!empty(getErrMsg('name'))) echo 'err'; ?>">
        ユーザー名
        <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
      </label>

      <!-- 一番お気に入りのツール -->
      <div class="input-msg">
        <?php echo getErrMsg('like_tool'); ?>
      </div>
      <label class="form-label <?php if (!empty(getErrMsg('like_tool'))) echo 'err'; ?>">
        一番お気に入りのツール
        <input type="text" name="like_tool" value="<?php echo getFormData('like_tool'); ?>">
      </label>

      <!-- 自己紹介 -->
      <div class="input-msg">
        <?php echo getErrMsg('bio'); ?>
      </div>
      <label class="form-label <?php if (!empty(getErrMsg('bio'))) echo 'err'; ?>">
        自己紹介
        <textarea name="bio" cols="30" rows="5"><?php echo getFormData('bio'); ?></textarea>
      </label>

      <!-- プロフィール画像 -->
      <div class="input-msg">
        <?php echo getErrMsg('pic'); ?>
      </div>
      <label class="form-label form-label-file">
        プロフィール画像
        <div class="form-input-file">
          <input type="file" name="pic" hidden>
        </div>
      </label>

      <input type="submit" class="form-btn" value="変更">
    </form>
  </div>
</main>

<?php require_once('footer.php'); ?>