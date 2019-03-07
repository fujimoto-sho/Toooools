<!-- 投稿表示 -->
<div class="post-list">
  <img src="<?php echo showImage($post['avatar_img'], $post['avatar_img_mime'], 'avatar'); ?>" alt="アバター" class="post-user-img">
  <p class="post-user-name">
    <a href="profile.php?u_id=<?php echo sanitize($post['user_id']); ?>">
      <?php echo sanitize($post['user_name']); ?>
    </a>
  </p>
  <time class="post-time" datetime="<?php echo sanitize($post['created_at']); ?>">
      <?php echo sanitize($post['created_at']); ?></time>
  <h1 class="post-tool-name">
    <a href="postDetail.php?t_id=<?php echo sanitize($post['tool_id']); ?>">
      <?php echo sanitize($post['tool_name']); ?>
    </a>
  </h1>
  <div class="post-wrap-center">
    <p class="post-tool-introduction">
      <?php echo sanitize($post['tool_introduction']); ?>
    </p>
    <img src="<?php echo showImage($post['tool_img'], $post['tool_img_mime'], 'tool'); ?>" alt="ツール" class="post-tool-img">
  </div>
  <div class="post-wrap-icon">
    <i class="fas fa-reply icon-pointer"></i>
    <span class="post-reply-count">
      <?php echo sanitize($post['reply_cnt']); ?>
    </span>
    <i class="fas fa-heart js-like-icon icon-pointer <?php if (isLogin() && !empty(getLikeCount($post['tool_id'], $_SESSION['user_id']))) echo 'fa-heart-active' ?>" data-tool_id="<?php echo sanitize($post['tool_id']); ?>"></i>
    <span class="post-like-count">
      <?php echo sanitize($post['like_cnt']); ?>
    </span>
    <?php if (isLogin() && $post['user_id'] === $_SESSION['user_id'] && basename($_SERVER['PHP_SELF']) === 'postDetail.php'): ?>
      <a href="postEdit.php?t_id=<?php echo sanitize($post['tool_id']); ?>"><i class="fas fa-edit"></i></a>
      <i class="fas fa-trash-alt js-delete-icon icon-pointer"></i>
    <?php endif; ?>
  </div>
</div>
