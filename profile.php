<?php
 //*************************************
// プロフィール
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('プロフィール');

// いいねを表示するか
$isLikeShow = (!empty($_GET['show']) && $_GET['show'] === 'like') ? true : false;
// ユーザーID取得
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id'] : '';
if (empty($u_id) && !empty($_SESSION['user_id'])) $u_id = $_SESSION['user_id'];
// ユーザーIDがあったら、ユーザー情報を取得
if (!empty($u_id)) {
  $dbUser = getUser($u_id);
}

// ユーザーデータを取得できなかったら一覧ページへ遷移
if (empty($dbUser)) {
  debugLog('トップページに遷移');
  header("Location:index.php");
} else {
  $postData = getPostInProfile($u_id, $isLikeShow);
}

// 終了ログ
debugLogEnd();
$pageTitle = 'プロフィール';
// ヘッダー;
require_once('header.php');
?>

<!-- プロフトップ -->
<div class="prof-top">
  <img src="<?php echo showImage($dbUser['img'], $dbUser['mime'], 'avatar'); ?>" alt="" class="prof-top-img">
  <p class="prof-top-user-name">
    <?php if (!empty($dbUser['name'])) echo $dbUser['name']; ?>
  </p>
  <nav class="prof-top-nav">
    <ul>
      <li>
        <a href="profile.php<?php if (!empty($u_id)) echo '?u_id=' . $u_id; ?>" class="prof-top-link">
          投稿<br>
          <?php echo (!empty(getPostInProfile($u_id, false))) ? count(getPostInProfile($u_id, false)) : 0; ?>
        </a>
      </li>
      <li>
        <a href="profile.php?show=like<?php if (!empty($u_id)) echo '&u_id=' . $u_id; ?>" class="prof-top-link">
          いいね<br>
          <?php echo getLikeCount('', $u_id); ?>
        </a>
      </li>
    </ul>
  </nav>
</div>

<!-- メイン -->
<main class="main site-width two-column">

  <div class="content-wrap">

    <!-- サイドバー -->
    <div class="sidebar">
      <div class="prof-side-bio">
        <p class="prof-side-bio-title">自己紹介</p>
        <p class="prof-side-bio-content">
          <?php if (!empty($dbUser['name'])) echo $dbUser['bio']; ?>
        </p>
      </div>
      <div class="sideber-line"></div>
      <div class="prof-side-bio">
        <p class="prof-side-bio-title">一番好きなツール</p>
        <p class="prof-side-bio-content">
          <?php if (!empty($dbUser['like_tool'])) echo $dbUser['like_tool']; ?>
        </p>
      </div>
      <?php if ($u_id === $_SESSION['user_id']): ?>
        <div class="sideber-line"></div>
      <?php endif; ?>
      <?php if (isLogin() && $u_id === $_SESSION['user_id']): ?>
        <button class="prof-side-btn"><a href="profileEdit.php">プロフィール編集</a></button>
        <button class="prof-side-btn"><a href="passChange.php">パスワード変更</a></button>
        <button class="prof-side-btn"><a href="withdraw.php">退会</a></button>
      <?php endif; ?>
    </div>

    <!-- 投稿 -->
    <div class="post">
      <?php
        if (!empty($postData)):
          foreach ($postData as $post):
            // 投稿表示
            require('postItem.php');
          endforeach;

        else:
          if ($isLikeShow):
            echo 'いいねをした投稿がありません。';
          else:
            echo 'まだ投稿をしていません。';
          endif;
        endif;
      ?>
    </div>

  </div>

</main>

<!-- フッター -->
<?php require_once('footer.php'); ?> 