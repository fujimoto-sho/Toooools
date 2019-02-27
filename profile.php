<?php
//*************************************
// プロフィール
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('プロフィール');

// ログイン認証
require_once('auth.php');

$isLikeShow = (!empty($_GET['show']) && $_GET['show'] === 'like') ? true: false;
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id'] : '';
if (empty($u_id) && !empty($_SESSION['user_id'])) $u_id = $_SESSION['user_id'];
if (!empty($u_id)) {
  $dbUser = getUser($u_id);
}

if (empty($dbUser)) {
  header("Location:index.php");

} else {
  $postData = getPostInProfile($u_id, $isLikeShow);
  $postCount = (!empty($postData)) ? count($postData) : 0;
}

// 終了ログ
debugLogEnd();
$pageTitle = 'プロフィール';
require_once('header.php');
?>

  <!-- プロフトップ -->
  <div class="prof-top">
    <img src="<?php echo showImage($dbUser['avatar_img'], $dbUser['avatar_img_mime']); ?>" alt="" class="prof-top-img">
    <p class="prof-top-user-name"><?php if(!empty($dbUser['name'])) echo $dbUser['name']; ?></p>
    <nav class="prof-top-nav">
      <ul>
        <li>
          <a href="profile.php" class="prof-top-link">
            投稿<br>
            <?php echo  (!empty(getPostInProfile($u_id, false))) ? count(getPostInProfile($u_id, false)) : 0; ?>
          </a>
        </li>
        <li>
          <a href="profile.php?show=like" class="prof-top-link">
            いいね<br>
            <?php echo getUserLikes($u_id); ?>
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
          <p class="prof-side-bio-content"><?php if(!empty($dbUser['name'])) echo $dbUser['bio']; ?></p>
        </div>
        <?php if ($u_id === $_SESSION['user_id']): ?>
          <button class="prof-side-btn"><a href="profileEdit.php">プロフィール編集</a></button>
          <button class="prof-side-btn"><a href="passChange.php">パスワード変更</a></button>
          <button class="prof-side-btn"><a href="withdraw.php">退会</a></button>
        <?php endif; ?>
      </div>

  <!-- 投稿 -->
  <div class="post">
    <?php
      if ($postCount > 0):
        foreach($postData as $row):
    ?>
          <div class="post-list">
            <img src="<?php echo showImage($row['avatar_img'], $row['avatar_img_mime']); ?>" alt="" class="post-user-img">
            <p class="post-user-name"><a href="profile.php?u_id=<?php echo $row['user_id']; ?>"><?php echo $row['user_name']; ?></a></p>
            <time class="post-time" datetime="<?php echo $row['created_at']; ?>"><?php echo $row['created_at']; ?></time>
            <h1 class="post-tool-name"><a href="postDetail.php?t_id=<?php echo $row['tool_id']; ?>"><?php echo $row['tool_name']; ?></a></h1>
            <div class="post-wrap-center">
              <p class="post-tool-introduction">
                <?php echo $row['tool_introduction']; ?>
              </p>
              <img src="<?php echo showImage($row['tool_img'], $row['tool_img_mime']); ?>" alt="" class="post-tool-img">
            </div>
            <div class="post-wrap-icon">
              <i class="fas fa-reply"></i>
              <span class="post-reply-count"><?php echo $row['reply_cnt']; ?></span>
              <i class="fas fa-heart js-like-icon <?php if ($row['like_cnt'] > 0) echo 'fa-heart-active' ?>" data-tool_id="<?php echo $row['tool_id']; ?>"></i>
              <span class="post-like-count"><?php echo $row['like_cnt']; ?></span>
              <i class="fas fa-angle-down fa-lg"></i>
            </div>
          </div>
    <?php
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

  <?php require_once('footer.php'); ?>