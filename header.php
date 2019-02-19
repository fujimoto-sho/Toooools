<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title><?php echo $pageTitle; ?> | Toooools</title>
  <link rel="stylesheet" href="tool/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <!-- ヘッダー -->
  <header class="header">
    <h1 class="logo">Toooools</h1>
    <nav class="top-nav">
      <ul>
        <?php if (empty($_SESSION['login_date'])) { ?>
          <li><a class="top-nav-link top-nav-login" href="login.php">ログイン</a></li>
          <li><a class="top-nav-link top-nav-signup" href="signup.php">ユーザー登録</a></li>
        <?php } else { ?>
          <li><a class="top-nav-link top-nav-login" href="profile.php">プロフィール</a></li>
          <li><a class="top-nav-link top-nav-signup" href="logout.php">ログアウト</a></li>
        <?php } ?>
      </ul>
    </nav>
  </header>

  <!-- フラッシュメッセージの表示 -->
  <?php if (!empty($_SESSION['flash_msg']) && empty($_POST)) : ?>
    <div class="flash-msg" hidden>
      <?php
        echo $_SESSION['flash_msg'];
        unset($_SESSION['flash_msg']);
      ?>
    </div>
  <?php endif; ?>