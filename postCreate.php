<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>TOP | Toooools</title>
  <!-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous"> -->
  <link rel="stylesheet" href="tool/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <!-- ヘッダー -->
  <header class="header">
    <h1 class="logo">Toooools</h1>
    <nav class="top-nav">
      <ul>
        <li><a class="top-nav-link top-nav-login" href="#">ログイン</a></li>
        <li><a class="top-nav-link top-nav-signup" href="#">ユーザー登録</a></li>
      </ul>
    </nav>
  </header>

  <!-- メイン -->
  <main class="main site-width one-column">
    <!-- フォーム -->
    <div class="form-container">
      <form class="form" method="post">
        <h1 class="form-title">新規投稿</h1>

        <!-- ツール名 -->
        <div class="input-msg">
        </div>
        <label class="form-label">
          ツール名
          <input type="password" name="email" id="" placeholder="">
        </label>

        <!-- ツール紹介 -->
        <div class="input-msg">
        </div>
        <label class="form-label">
          ツール紹介
          <textarea name="" id="" cols="30" rows="5"></textarea>
        </label>

        <!-- ツール画像 -->
        <div class="input-msg">
        </div>
        <label class="form-label form-label-file">
          ツール画像
          <div class="form-input-file">
            <input type="file" name="" id="" hidden>
          </div>
        </label>

        <input type="submit" class="form-btn" value="投稿">
      </form>
    </div>
  </main>

  <!-- フッター -->
  <footer class="footer">
    Copyright fujisho All Rights Reserved.
  </footer>

  <script src="js/jquery-3.3.1.min.js"></script>
  <script>
    $(function() {

    });
  </script>
</body>
</html>