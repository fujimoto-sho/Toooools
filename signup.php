<?php
//*************************************
// ユーザ登録
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('ユーザ登録ページ');

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $email = $_POST['email'];
  $name = $_POST['name'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  // 未入力チェック
  validEmpty($email, 'email');
  validEmpty($name, 'name');
  validEmpty($pass, 'pass');
  validEmpty($pass_re, 'pass_re');

  if (empty($err_msg)) {
    // Email
    // Emailのバリデーション
    validEmail($email, 'email');
    // 重複チェック
    validEmailDup($email, 'email');

    // ユーザー名
    // 最大文字数チェック
    validMaxLen($name, 'name', 30);

    // パスワードのバリデーション
    validPass($pass, 'pass');

    if (empty($err_msg)) {
      // 再入力と同じかチェック
      validEqual($pass, $pass_re, 'pass_re');

      if (empty($err_msg)) {
        debugLog('バリデーションOK');

        try {
          $dbh = dbConnect();
          $sql = 'INSERT INTO users (name, email, password, created_at) VALUES (:name, :email, :pass, :date)';
          $data = array(
            ':name' => $name,
            ':email' => $email,
            ':pass' => password_hash($pass, PASSWORD_DEFAULT),
            ':date' => date('Y-m-d H:i:s'),
          );
  
          $stmt = queryPost($dbh, $sql, $data);
  
          if (!empty($stmt->rowCount())) {
            debugLog('ユーザー登録成功');
            debugLog('プロフィールに遷移します。');

            // ログイン日時
            $_SESSION['login_date'] = time();
            // ログイン有効期限
            $_SESSION['login_limit'] = LOGIN_TIME_DEFAULT;
            // ユーザーID
            $_SESSION['user_id'] = $dbh->lastInsertId();

            header("Location:profile.php");
          } else {
            debugLog('ユーザー登録失敗');
            $err_msg['common'] = ERRMSG['DEFAULT'];
          }
  
        } catch (Exception $e) {
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = ERRMSG['DEFAULT'];
        }
      }

    }
  }
}

// 終了ログ
debugLogEnd();
$pageTitle = 'ユーザ登録';
// ヘッダー;
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- フォーム -->
  <div class="form-container">
    <form class="form" method="post">
      <h1 class="form-title">ユーザ登録</h1>

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

      <!-- パスワード -->
      <div class="input-msg">
        <?php echo getErrMsg('pass'); ?>
      </div>
      <label class="form-label <?php echo getErrClassName('pass'); ?>">
        パスワード
        <input type="password" name="pass" placeholder="英数字6文字以上" value="<?php echo getFormData('pass'); ?>">
      </label>

      <!-- パスワード（再入力）-->
      <div class="input-msg">
        <?php echo getErrMsg('pass_re'); ?>
      </div>
      <label class="form-label <?php echo getErrClassName('pass_re'); ?>">
        パスワード（再入力）
        <input type="password" name="pass_re" placeholder="英数字6文字以上" value="<?php echo getFormData('pass_re'); ?>">
      </label>

      <input type="submit" class="form-btn" value="登録">
    </form>
  </div>
</main>

<!-- フッター -->
<?php require_once('footer.php'); ?>
