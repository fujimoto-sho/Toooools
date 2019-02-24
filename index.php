<?php
//*************************************
// 投稿一覧
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('投稿一覧ページ');

$order = (!empty($_GET['order'])) ? $_GET['order'] : '';
$search_target = (!empty($_GET['search_target'])) ? $_GET['search_target'] : '';
$search_word = (!empty($_GET['search_word'])) ? $_GET['search_word'] : '';

$postData = getPost($order, $search_target, $search_word, 0);
$postCount = (!empty($postData)) ? count($postData) : 0;

$onePageCount = 10;
$nowPage = (!empty($_GET['p'])) ? (int)$_GET['p'] : 1;
$pageCount = (int)ceil($postCount / $onePageCount);

$minPostNum = (($nowPage - 1) * $onePageCount) + 1;
if ($minPostNum > $postCount) $minPostNum = $postCount;
$maxPostNum = (($nowPage) * $onePageCount);
if ($maxPostNum > $postCount) $maxPostNum = $postCount;

// 再取得
$postData = getPost($order, $search_target, $search_word, $nowPage);

// 終了ログ
debugLogEnd();
$pageTitle = '投稿一覧';
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width two-column">
  <!-- サイドバー -->
  <div class="sidebar">
    <form method="get" class="search-form">
      <label>
        並び順
        <select name="order">
          <option value="create_desc" <?php if($order === 'create_desc') echo 'selected'; ?>>新着順</option>
          <option value="create_asc" <?php if($order === 'create_asc') echo 'selected'; ?>>古い順</option>
          <option value="like_desc" <?php if($order === 'like_desc') echo 'selected'; ?>>いいねが多い順</option>
        </select>
      </label>
      <label>
        検索対象
        <select name="search_target" id="">
          <option value="tool_name" <?php if($search_target === 'tool_name') echo 'selected'; ?>>ツール名</option>
          <option value="tool_introduction" <?php if($search_target === 'tool_introduction') echo 'selected'; ?>>ツール紹介文</option>
          <option value="user_name" <?php if($search_target === 'user_name') echo 'selected'; ?>>ユーザ名</option>
        </select>
      </label>
      <label>
        検索ワード
        <input type="text" name="search_word" value="<?php if(!empty($_GET['search_word'])) echo $_GET['search_word']; ?>">
      </label>
      <input type="submit" value="検索">
    </form>
    <?php echo $minPostNum; ?> - <?php echo $maxPostNum; ?> 件（全<?php echo $postCount; ?>件）
  </div>

  <!-- 投稿 -->
  <div class="post">
    <?php
      if ($postCount > 0) {
        foreach($postData as $row) {
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
        }
        pagenation($nowPage, $pageCount);
      } else {
        echo '検索結果がありません。';
      }
    ?>

  </div>

</main>

<?php require_once('footer.php'); ?>
