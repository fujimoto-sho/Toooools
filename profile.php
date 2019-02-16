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

// 終了ログ
debugLogEnd();
$pageTitle = 'プロフィール';
require_once('header.php');
?>

  <!-- プロフトップ -->
  <div class="prof-top">
    <img src="img/user-icon-1.jpg" alt="" class="prof-top-img">
    <p class="prof-top-user-name">ユーザ名ユーザ名ユーザ名ユザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーーザ名ユーザ名ユーザ名ユーザ名ユ名ユーザーザ</p>
    <nav class="prof-top-nav">
      <ul>
        <li>
          <a href="" class="prof-top-link">
            投稿<br>
            9999
          </a>
        </li>
        <li>
          <a href="" class="prof-top-link">
            いいね<br>
            9999
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
        <button class="prof-side-btn"><a href="profileEdit.php">プロフィール編集</a></button>
        <button class="prof-side-btn">パスワード変更</button>
        <button class="prof-side-btn"><a href="withdraw.php">退会</a></button>

      </div>

      <!-- 投稿 -->
      <div class="post">
        <div class="post-list">
          <img src="img/user-icon-1.jpg" alt="" class="post-user-img">
          <p class="post-user-name">ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユ名ユーザーザ</p>
          <time class="post-time" datetime="2018-02-03 11:22:55">2018年02月03日 11時22分55秒</time>
          <h1 class="post-tool-name">ツール名ツール名ツール名ツール名ツール名ツ名ツール名名ツール名ール名</h1>
          <div class="post-wrap-center">
            <p class="post-tool-introduction">
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
            </p>
            <img src="img/javascript.jpg" alt="" class="post-tool-img">
          </div>
          <div class="post-wrap-icon">
            <i class="fas fa-reply"></i>
            <span class="post-reply-count">9</span>
            <i class="fas fa-heart"></i>
            <span class="post-like-count">9</span>
            <i class="fas fa-angle-down fa-lg"></i>
          </div>
        </div>
        
        <div class="post-list">
          <img src="img/user-icon-1.jpg" alt="" class="post-user-img">
          <p class="post-user-name">ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユ名ユーザーザ</p>
          <time class="post-time" datetime="2018-02-03 11:22:55">2018年02月03日 11時22分55秒</time>
          <h1 class="post-tool-name">ツール名ツール名ツール名ツール名ツール名ツ名ツール名名ツール名ール名</h1>
          <div class="post-wrap-center">
            <p class="post-tool-introduction">
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
            </p>
            <img src="img/javascript.jpg" alt="" class="post-tool-img">
          </div>
          <div class="post-wrap-icon">
            <i class="fas fa-reply"></i>
            <span class="post-reply-count">9</span>
            <i class="fas fa-heart"></i>
            <span class="post-like-count">9</span>
            <i class="fas fa-angle-down fa-lg"></i>
          </div>
        </div>
        
        <div class="post-list">
          <img src="img/user-icon-1.jpg" alt="" class="post-user-img">
          <p class="post-user-name">ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユーザ名ユ名ユーザーザ</p>
          <time class="post-time" datetime="2018-02-03 11:22:55">2018年02月03日 11時22分55秒</time>>
          <h1 class="post-tool-name">ツール名ツール名ツール名ツール名ツール名ツ名ツール名名ツール名ール名</h1>
          <div class="post-wrap-center">
            <p class="post-tool-introduction">
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
              ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介ツール紹介
            </p>
            <img src="img/javascript.jpg" alt="" class="post-tool-img">
          </div>
          <div class="post-wrap-icon">
            <i class="fas fa-reply"></i>
            <span class="post-reply-count">9</span>
            <i class="fas fa-heart"></i>
            <span class="post-like-count">9</span>
            <i class="fas fa-angle-down fa-lg"></i>
          </div>
        </div>

        <div class="pagenation">
          <ul class="pagenation-list">
            <li class="pagenation-item"><a href="">&lt;</a></li>
            <li class="pagenation-item pagination-item-now"><a href="">1</a></li>
            <li class="pagenation-item"><a href="">2</a></li>
            <li class="pagenation-item"><a href="">3</a></li>
            <li class="pagenation-item"><a href="">4</a></li>
            <li class="pagenation-item"><a href="">5</a></li>
            <li class="pagenation-item"><a href="">&gt;</a></li>
          </ul>
        </div>
      </div>
    </div>
  </main>

  <?php require_once('footer.php'); ?>