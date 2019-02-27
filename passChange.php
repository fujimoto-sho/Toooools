<?php
//*************************************
// パスワード変更ページ
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('パスワード変更ページ');

// ログイン認証
require_once('auth.php');

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];
  
  // 未入力チェック
  validEmpty($pass_old, 'pass_old');
  validEmpty($pass_new, 'pass_new');
  validEmpty($pass_new_re, 'pass_new_re');
  
  if (empty($err_msg)) {
    $dbUser = getUser($_SESSION['user_id']);

    // 現在のパスワードが合っているかチェック
    if (empty($dbUser) || !password_verify($pass_old, $dbUser['password'])) {
      $err_msg['pass_old'] = MSG10;
    }

    // 新しいパスワードのバリデーション
    validPass($pass_new, 'pass_new');

    // 現在のパスワードと新しいパスワードが違うか
    if ($pass_old === $pass_new) {
      $err_msg['pass_new'] = MSG11;
    }


    if (empty($err_msg)) {
      // 新しいパスワードと新しいパスワード（再入力）が同じか
      if ($pass_new !== $pass_new_re) {
        $err_msg['pass_new_re'] = MSG12;
      }

      if (empty($err_msg)) {
        debugLog('バリデーションOK');

        try {
          debugLog('パスワード変更');
          $dbh = dbConnect();
          $sql = 'UPDATE users SET password = :pass WHERE id = :id';
          $data = array(
            ':id' => $_SESSION['user_id'],
            ':pass' => password_hash($pass_new, PASSWORD_DEFAULT),
          );

          $stmt = queryPost($dbh, $sql, $data);

          if ($stmt) {
            debugLog('パスワード変更成功');
            
            $from = 'fujisho344@gmail.com';
            $to = 'fujisho344@gmail.com';
            $subject = 'パスワード変更';
            $message = <<<EOF
パスワード変更されました。

ご確認ください。
EOF;

            sendMail($from, $to, $subject, $message);

            // フラッシュメッセージセット
            $_SESSION['flash_msg'] = SUC02;

            debugLog('プロフィールに遷移します。');
            header("Location:profile.php");

          } else {
            debugLog('パスワード変更失敗');
            $err_msg['common'] = MSG02;
          }

        } catch (Exception $e) {
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = MSG02;
        }
      }
    }
  }
}

// 終了ログ
debugLogEnd();
$pageTitle = 'パスワード変更';
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- フォーム -->
  <div class="form-container">
    <form class="form" method="post">
      <h1 class="form-title">パスワード変更</h1>

      <!-- 共通メッセージ -->
      <div class="input-msg">
        <?php echo getErrMsg('common'); ?>
      </div>

      <!-- 現在のパスワード -->
      <div class="input-msg">
      <?php echo getErrMsg('pass_old'); ?>
      </div>
      <label class="form-label <?php if (!empty(getErrMsg('pass_old'))) echo 'err'; ?>">
        現在のパスワード
        <input type="password" name="pass_old" value="<?php echo getFormData('pass_old'); ?>">
      </label>

      <!-- 新しいパスワード -->
      <div class="input-msg">
        <?php echo getErrMsg('pass_new'); ?>
      </div>
      <label class="form-label <?php if (!empty(getErrMsg('pass_new'))) echo 'err'; ?>">
        新しいパスワード
        <input type="password" name="pass_new" placeholder="英数字6文字以上" value="<?php echo getFormData('pass_new'); ?>">
      </label>

      <!-- 新しいパスワード（再入力）-->
      <div class="input-msg">
        <?php echo getErrMsg('pass_new_re'); ?>
      </div>
      <label class="form-label <?php if (!empty(getErrMsg('pass_new_re'))) echo 'err'; ?>">
        新しいパスワード（再入力）
        <input type="password" name="pass_new_re" id="" placeholder="英数字6文字以上" value="<?php echo getFormData('pass_new_re'); ?>">
      </label>

      <input type="submit" class="form-btn" value="変更">
    </form>
  </div>
</main>

<?php require_once('footer.php'); ?>