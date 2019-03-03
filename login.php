<?php
 //*************************************
// ログイン機能
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('ユーザ登録ページ');

// ログイン認証
require_once('auth.php');

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $email = $_POST['email'];
  $pass = $_POST['pass'];

  // 未入力チェック
  validEmpty($email, 'email');
  validEmpty($pass, 'pass');

  if (empty($err_msg)) {
    // Emailのバリデーション
    validEmail($email, 'email');

    // パスワードのバリデーション
    validPass($pass, 'pass');

    if (empty($err_msg)) {
      debugLog('バリデーションOK');

      try {
        // DB処理
        $dbh = dbConnect();
        $sql = 'SELECT id, password FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(
          ':email' => $email,
        );
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch();

        debugLog('クエリの取得結果：' . print_r($result, true));

        // パスワードの照合
        $isPassMatch = (!empty($result)) ? password_verify($pass, $result['password']) : false;

        if ($isPassMatch) {
          debugLog('パスワードOK');

          // ログイン日時設定
          $_SESSION['login_date'] = time();
          // ログイン有効期限設定（チェックがついていたら長くする）
          $loginLimit = (empty($_POST['limit'])) ? LOGIN_TIME_DEFAULT : LOGIN_TIME_LONG;
          $_SESSION['login_limit'] = $loginLimit;
          // ユーザーID設定
          $_SESSION['user_id'] = $result['id'];

          debugLog('プロフィールへ遷移します。');
          header("Location:profile.php");
        } else {
          debugLog('パスワードNG');
          $err_msg['pass'] = ERRMSG['LOGIN'];
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
$pageTitle = 'ログイン';
// ヘッダー;
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
    <!-- フォーム -->
    <div class="form-container">
        <form class="form" method="post">
            <h1 class="form-title">ログイン</h1>

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

            <!-- パスワード -->
            <div class="input-msg">
                <?php echo getErrMsg('pass'); ?>
            </div>
            <label class="form-label <?php echo getErrClassName('pass'); ?>">
                パスワード
                <input type="password" name="pass" id="" placeholder="英数字6文字以上" value="<?php echo getFormData('pass'); ?>">
            </label>

            <label class="form-label-checkbox">
                <input type="checkbox" name="limit">
                <span>ログイン情報を保持する</span>
            </label>

            <input type="submit" class="form-btn" value="ログイン">

            <div class="form-link-list">
                <a href="signup.php">新規登録</a> | <a href="passRemindSend.php">パスワードを忘れてしまった方はこちら</a>
            </div>
        </form>
    </div>
</main>

<!-- フッター -->
<?php require_once('footer.php'); ?> 