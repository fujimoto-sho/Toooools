<?php
//*************************************
// 投稿一覧
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('投稿一覧ページ');

// 並び順を取得
$order = (!empty($_GET['order'])) ? $_GET['order'] : '';
// 検索対象を取得
$search_target = (!empty($_GET['search_target'])) ? $_GET['search_target'] : '';
// 検索ワードを取得
$search_word = (!empty($_GET['search_word'])) ? $_GET['search_word'] : '';

// 投稿データ（全件）の取得
$postData = getPost($order, $search_target, $search_word, 0);
// 投稿件数の取得
$postCount = (!empty($postData)) ? count($postData) : 0;

// 現在ページの取得
$nowPage = (!empty($_GET['p'])) ? (int)$_GET['p'] : 1;
// 全ページ数
$pageCount = (int)ceil($postCount / ONE_PAGE_COUNT);

// 表示中の最小件数
$minPostNum = (($nowPage - 1) * ONE_PAGE_COUNT) + 1;
if ($minPostNum > $postCount) $minPostNum = $postCount;
// 表示中の最大件数
$maxPostNum = (($nowPage) * ONE_PAGE_COUNT);
if ($maxPostNum > $postCount) $maxPostNum = $postCount;

// 表示用に再取得
$postData = getPost($order, $search_target, $search_word, $nowPage);

// 終了ログ
debugLogEnd();
$pageTitle = '投稿一覧';
// ヘッダー
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
          <option value="create_desc" <?php if ($order === 'create_desc') echo 'selected'; ?>>新着順</option>
          <option value="create_asc" <?php if ($order === 'create_asc') echo 'selected'; ?>>古い順</option>
          <option value="like_desc" <?php if ($order === 'like_desc') echo 'selected'; ?>>いいねが多い順</option>
        </select>
      </label>
      <label>
        検索対象
        <select name="search_target" id="">
          <option value="tool_name" <?php if ($search_target === 'tool_name') echo 'selected'; ?>>ツール名</option>
          <option value="tool_introduction" <?php if ($search_target === 'tool_introduction') echo 'selected'; ?>>ツール紹介</option>
        </select>
      </label>
      <label>
        検索ワード
        <input type="text" name="search_word" value="<?php if (!empty($_GET['search_word'])) echo sanitize($_GET['search_word']); ?>">
      </label>
      <input type="submit" value="検索">
    </form>
    <div class="sideber-line"></div>
    <p class="sidebar-page-count">
      <?php echo $minPostNum; ?> -
      <?php echo $maxPostNum; ?> 件（全
      <?php echo $postCount; ?>件）
    </p>
  </div>

  <!-- 投稿 -->
  <div class="post">
    <?php
    if ($postCount > 0) {
      foreach ($postData as $post) {
        // 投稿表示
        require('postItem.php');
      }
      pagenation($nowPage, $pageCount);
    } else {
      echo '検索結果がありません。';
    }
    ?>
  </div>

</main>

<!-- フッター -->
<?php require_once('footer.php'); ?>
