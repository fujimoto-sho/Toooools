<?php
//*************************************
// パスワード再発行
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('パスワード再設定');

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $email = $_POST['email'];

  // 未入力チェック
  validEmpty($email, 'email');

    if (empty($err_msg)) {
      try {
        $dbh = dbConnect();
        $sql = 'SELECT COUNT(*) cnt FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(
          ':email' => $email
        );
        $stmt = queryPost($dbh, $sql, $data);
        $dbCount = $stmt->fetchAll();
        if ($dbCount[0]['cnt'] === 0) {
          $err_msg['email'] = MSG13;
        }

      } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG02;
      }

      if (empty($err_msg)) {
        debugLog('バリデーションOK');

        debugLog('認証用セッションを設定します');
        // 認証用メールアドレスを保持
        $_SESSION['auth_email'] = $email;
        // 認証キーを保持
        $auth_key = makeRandStr();
        $_SESSION['auth_key'] = $auth_key;
        // 認証可能時間を設定（30分）
        $_SESSION['auth_limit'] = time() + (60 * 30);
        
        debugLog('セッション：' . print_r($_SESSION, true));

        $from = 'fujisho344@gmail.com';
        $to = 'fujisho344@gmail.com';
        $subject = 'パスワード再設定';
        $message = <<<EOF
パスワード再設定します。
http://localhost:8888/toooools/passRemindChange.php?k=$auth_key

ご確認ください。
EOF;

        sendMail($from, $to, $subject, $message);

        $isSendMail = true;
    }
  }
}

// 終了ログ
debugLogEnd();
$pageTitle = 'パスワード再発行';
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- フォーム -->
  <div class="form-container">
    <form class="form" method="post">
      <h1 class="form-title">パスワード再発行</h1>
      <?php if (empty($isSendMail)) { ?>
        <p class="form-p">
            登録したメールアドレスを下記フォームに入力し、送信ボタンを押してください。<br>
            入力したメールアドレスにパスワード再設定メールが通知されます。
        </p>
        <!-- Email -->
        <div class="input-msg">
          <?php echo getErrMsg('email'); ?>
        </div>
        <label class="form-label <?php if (!empty(getErrMsg('email'))) echo 'err'; ?>">
          Email
          <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
        </label>

        <input type="submit" class="form-btn" value="送信">
      <?php } else { ?>
        <p class="form-p">
            再発行用のメールを送信しました。
        </p>
      <?php } ?>
    </form>
  </div>

</main>

<?php require_once('footer.php'); ?>
