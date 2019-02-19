<?php
//*************************************
// パスワード再設定
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('パスワード再設定');

if (empty($_GET)) {
  exit;
}
if ($_GET['k'] !== $_SESSION['auth_key']) {
  exit;
}

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];
  
  // 未入力チェック
  validEmpty($pass_new, 'pass_new');
  validEmpty($pass_new_re, 'pass_new_re');
  
  if (empty($err_msg)) {
    // 新しいパスワードのバリデーション
    validPass($pass_new, 'pass_new');

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
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(
            ':email' => $_SESSION['auth_email'],
            ':pass' => password_hash($pass_new, PASSWORD_DEFAULT),
          );

          $stmt = queryPost($dbh, $sql, $data);

          if ($stmt) {
            debugLog('パスワード変更成功');
            
            $from = 'fujisho344@gmail.com';
            $to = 'fujisho344@gmail.com';
            $subject = 'パスワード再設定完了';
            $message = <<<EOF
パスワードの再設定が完了されました。

ご確認ください。
EOF;

            sendMail($from, $to, $subject, $message);

            // フラッシュメッセージセット
            $_SESSION['flash_msg'] = SUC03;

            debugLog('ログインページに遷移します。');
            header("Location:login.php");

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
$pageTitle = 'パスワード再設定';
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- フォーム -->
  <div class="form-container">
    <form class="form" method="post">
      <h1 class="form-title">パスワード再設定</h1>

      <!-- 共通メッセージ -->
      <div class="input-msg">
        <?php echo getErrMsg('common'); ?>
      </div>

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

      <input type="submit" class="form-btn" value="登録">
    </form>
  </div>
</main>

<?php require_once('footer.php'); ?>
