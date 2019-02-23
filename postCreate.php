<?php
//*************************************
// 投稿作成
//*************************************

// 共通変数・関数読み込み
require_once('function.php');

// 開始ログ
debugLogStart('投稿作成ページ');

// ログイン認証
require_once('auth.php');

$t_id = (!empty($_GET['t_id'])) ? $_GET['t_id'] : '';
$dbFormData = (!empty($t_id)) ? getTool($t_id) : '';
$isCreate = (empty($dbFormData)) ? true : false;

if (!$isCreate) {
  $tool_img = $dbFormData['tool_img'];
  $tool_img_mime = $dbFormData['tool_img_mime'];
}

if (empty($_POST)) {
  unset($_SESSION['tool_img']);
  unset($_SESSION['tool_img_mime']);
}
if (!empty($_POST)) {
  debugLog('POST：' . print_r($_POST, true));

  $tool_name = $_POST['tool_name'];
  $tool_introduction = $_POST['tool_introduction'];
  $tool_img = (!empty($_FILES['tool_img'])) ? imageToBlob($_FILES['tool_img'], 'tool_img') : '';
  if (empty($tool_img)) {
    if (!empty($_SESSION['tool_img'])) {
      $tool_img = $_SESSION['tool_img'];
    } else {
      $tool_img = (!empty($dbFormData)) ? $dbFormData['tool_img'] : '';
    }
  } else {
    $_SESSION['tool_img'] = $tool_img;
  }
  $tool_img_mime = (!empty($_FILES['tool_img']['type'])) ? $_FILES['tool_img']['type'] : '';
  if (empty($tool_img_mime)) {
    if (!empty($_SESSION['tool_img_mime'])) {
      $tool_img_mime = $_SESSION['tool_img_mime'];
    } else {
      $tool_img_mime = (!empty($dbFormData)) ? $dbFormData['tool_img_mime'] : '';
    }
  } else {
    $_SESSION['tool_img_mime'] = $tool_img_mime;
  }
  // 未入力チェック
  validEmpty($tool_name, 'tool_name');
  validEmpty($tool_introduction, 'tool_introduction');

  if (empty($err_msg)) {
    // ツール名
    // 最大文字数チェック
    validMaxLen($tool_name, 'tool_name');

    // ツール紹介文
    // 最大文字数チェック
    validMaxLen($tool_introduction, 'tool_introduction', 500);

    if (empty($err_msg)) {
      debugLog('バリデーションOK');

      try {
        $dbh = dbConnect();
        if ($isCreate) {
          debugLog('ツール登録');
          $sql = 'INSERT INTO tools (tool_name, tool_introduction, tool_img, tool_img_mime, user_id, created_at)';
          $sql .= 'VALUES (:tool_name, :tool_introduction, :tool_img, :tool_img_mime, :uid, :date)';
          $data = array(
            ':tool_name' => $tool_name,
            ':tool_introduction' => $tool_introduction,
            ':tool_img' => $tool_img,
            ':tool_img_mime' => $tool_img_mime,
            ':uid' => $_SESSION['user_id'],
            ':date' => date('Y-m-d H:i:s'),
          );
        } else {
          if (!empty($tool_img)) {
            debugLog('ツール更新');
            $sql = 'UPDATE tools SET tool_name = :tool_name, tool_introduction = :tool_introduction, tool_img = :tool_img, tool_img_mime = :tool_img_mime  WHERE id = :tid';
            $data = array(
              ':tool_name' => $tool_name,
              ':tool_introduction' => $tool_introduction,
              ':tool_img' => $tool_img,
              ':tool_img_mime' => $tool_img_mime,
              ':tid' => $t_id,
            );
          } else {
            $sql = 'UPDATE tools SET tool_name = :tool_name, tool_introduction = :tool_introduction WHERE id = :tid';
            $data = array(
              ':tool_name' => $tool_name,
              ':tool_introduction' => $tool_introduction,
              ':tid' => $t_id,
            );
          }
        }

        $stmt = queryPost($dbh, $sql, $data);

        if ($stmt) {
          debugLog('ツール情報更新成功');

          // フラッシュメッセージセット
          $_SESSION['flash_msg'] = ($isCreate) ? SUC04 : SUC05;
          debugLog('投稿詳細に遷移します。');

          $t_id = ($isCreate) ? $dbh->lastInsertId() : $t_id;

          header("Location:postDetail.php?t_id=" . $t_id);
        } else {
          debugLog('ユーザー情報更新失敗');
          $err_msg['common'] = MSG02;
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
$pageTitle = ($isCreate) ? '新規投稿' : '投稿編集';
require_once('header.php');
?>

<!-- メイン -->
<main class="main site-width one-column">
  <!-- フォーム -->
  <div class="form-container">
    <form class="form" method="post" enctype="multipart/form-data">
      <h1 class="form-title"><?php echo ($isCreate) ? '新規投稿' : '投稿編集'; ?></h1>

      <!-- 共通メッセージ -->
      <div class="input-msg">
        <?php echo getErrMsg('common'); ?>
      </div>

      <!-- ツール名 -->
      <div class="input-msg">
        <?php echo getErrMsg('tool_name'); ?>
      </div>
      <label class="form-label <?php if (!empty(getErrMsg('tool_name'))) echo 'err'; ?>">
        ツール名
        <input type="text" name="tool_name" value="<?php echo getFormData('tool_name'); ?>">
      </label>

      <!-- ツール紹介 -->
      <div class="input-msg">
        <?php echo getErrMsg('tool_introduction'); ?>
      </div>
      <label class="form-label <?php if (!empty(getErrMsg('tool_introduction'))) echo 'err'; ?>">
        ツール紹介
        <textarea name="tool_introduction" cols="30" rows="5"><?php echo getFormData('tool_introduction'); ?></textarea>
      </label>

      <!-- ツール画像 -->
      <div class="input-msg">
        <?php echo getErrMsg('tool_img'); ?>
      </div>
      <div class="form-input-container">
      <label class="form-label form-label-file">
        ツール画像
          <input type="hidden" name="MAX_FILE_SIZE" value="1500000">
          <input type="file" name="tool_img" id="js-img-input" hidden>
          <img src="<?php echo getImage($isCreate, $t_id, 'tool_img'); ?>" id="js-img-show" class="form-input-file-img">
        </label>
      </div>

      <input type="submit" class="form-btn" value="投稿">
    </form>
  </div>
</main>

<?php require_once('footer.php'); ?>