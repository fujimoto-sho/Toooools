<?php
//*************************************
// ログイン機能
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogTitle('ユーザ登録ページ');
debugLogStart();

if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $email = $_POST['email'];
  $pass = $_POST['pass'];

  // 未入力チェック
  validEmpty($email, 'email');
  validEmpty($pass, 'pass');

  if (empty($err_msg)) {
    // Email
    // フォーマットチェック
    validEmailFormat($email, 'email');
    // 最大文字数チェック
    validMaxLen($email, 'email');

    // パスワード
    // 最小文字数チェック
    validMinLen($pass, 'pass');
    // 最大文字数チェック
    validMaxLen($pass, 'pass');
    // 半角英数字チェック
    validHalf($pass, 'pass');

    if (empty($err_msg)) {
      debugLog('バリデーションOK');

      try {
        // DB処理
        $dbh = dbConnect();
        $sql = 'SELECT id, password FROM users WHERE email = :email';
        $data = array(
          ':email' => $email,
        );
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        debugLog('クエリの取得結果：' . print_r($result, true));

        $isPassMatch = false;
        if (!empty($result)) $isPassMatch = password_verify($pass, $result['password']);

        if ($isPassMatch) {
          debugLog('パスワードOK');
          debugLog('プロフィールへ遷移します。');
          header("Location:profile.html");
        } else {
          debugLog('該当データなし');
          $err_msg['pass'] = MSG09;
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
$pageTitle = 'ログイン';
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
      <label class="form-label <?php if (empty(getErrMsg('email'))) echo 'err'; ?>">
        Email
        <input type="text" name="email" value="<?php echo getPost('email'); ?>">
      </label>

      <!-- パスワード -->
      <div class="input-msg">
        <?php echo getErrMsg('pass'); ?>
      </div>
      <label class="form-label <?php if (empty(getErrMsg('pass'))) echo 'err'; ?>">
        パスワード
        <input type="password" name="pass" id="" placeholder="英数字6文字以上" value="<?php echo getPost('pass'); ?>">
      </label>

      <label class="form-label-checkbox">
        <input type="checkbox" name="save">
        <span>ログイン情報を保存する</span>
      </label>

      <input type="submit" class="form-btn" value="ログイン">

      <div class="form-link-list">
        <a href="signup.php">新規登録</a> | <a href="">パスワードを忘れてしまった方はこちら</a> 
      </div>
    </form>
  </div>
</main>

<?php require_once('footer.php'); ?>