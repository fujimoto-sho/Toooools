<?php
//*************************************
// パスワード再設定
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('パスワード再設定');

// GETパラメタ存在チェック
if (empty($_GET)) {
  debugLog('GETパラメータがありません。');
  debugLog('パスワード再発行ページに遷移します。');
  header("Location:passRemindSend.php");
}

// 認証キーチェック
if ($_GET['k'] !== $_SESSION['auth_key']) {
  debugLog('認証キーが違います。');
  debugLog('パスワード再発行ページに遷移します。');
  header("Location:passRemindSend.php");
}

if (!empty($_POST)) {
  // debugLog('POST：' . print_r($_POST, true));
  debugLog('POST OK');

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
        $err_msg['pass_new_re'] = ERRMSG['PASS_NEW_RE_NOT_EQUAL'];
      }

      if (empty($err_msg)) {
        debugLog('バリデーションOK');

        try {
          debugLog('パスワード変更');
          // DB処理
          $dbh = dbConnect();
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(
            ':email' => $_SESSION['auth_email'],
            ':pass' => password_hash($pass_new, PASSWORD_DEFAULT),
          );

          $stmt = queryPost($dbh, $sql, $data);

          if (!empty($stmt)) {
            debugLog('パスワード変更成功');

            $from = 'fujisho344@gmail.com';
            $to = $_SESSION['auth_email'];
            $subject = 'パスワード再設定完了';
            $message = <<<EOF
お世話になっております。
「Toooools」をご利用頂き誠にありがとうございます。

パスワードの再設定が完了しました。

今後とも、Tooooolsをよろしくお願いいたします。

---------------------------------------------

Toooools
{$_SERVER['SERVER_NAME']}

---------------------------------------------
EOF;

            // メール送信
            sendMail($from, $to, $subject, $message);

            // フラッシュメッセージセット
            $_SESSION['flash_msg'] = SUCMSG['PASS_REMIND'];

            debugLog('ログインページに遷移します。');
            header("Location:login.php");
          } else {
            debugLog('パスワード変更失敗');
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
$pageTitle = 'パスワード再設定';
// ヘッダー;
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
      <label class="form-label <?php echo getErrClassName('pass_new'); ?>">
        新しいパスワード
        <input type="password" name="pass_new" placeholder="英数字6文字以上" value="<?php echo getFormData('pass_new'); ?>">
      </label>

      <!-- 新しいパスワード（再入力）-->
      <div class="input-msg">
        <?php echo getErrMsg('pass_new_re'); ?>
      </div>
      <label class="form-label <?php echo getErrClassName('pass_new_re'); ?>">
        新しいパスワード（再入力）
        <input type="password" name="pass_new_re" id="" placeholder="英数字6文字以上" value="<?php echo getFormData('pass_new_re'); ?>">
      </label>

      <input type="submit" class="form-btn" value="登録">
    </form>
  </div>
</main>

<!-- フッター -->
<?php require_once('footer.php'); ?>
