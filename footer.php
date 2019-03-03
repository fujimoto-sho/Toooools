<!-- フッター -->
<footer class="footer">
    Copyright fujisho All Rights Reserved.
</footer>

<script src="js/jquery-3.3.1.min.js"></script>
<script>
$(function() {

  // フラッシュメッセージ
  var $flash = $('.flash-msg');
  if ($flash.length > 0) {
    $flash.slideToggle();
    setTimeout(function() {
      $flash.slideToggle();
    }, 5000);
  };

  // 画像ライブプレビュー
  $imgInput = $('.form-label-file');
  $imgInput.on('dragover', function(e) {
    e.stopPropagation();
    e.preventDefault();
    $imgInput.css('border', '5px solid red');
  });
  $imgInput.on('dragleave', function(e) {
    e.stopPropagation();
    e.preventDefault();
    $imgInput.css('border', 'none');
  });
  $('#js-img-input').on('change', function() {
    var file = this.files[0];
    $img = $('#js-img-show');
    reader = new FileReader();

    reader.onload = function(e) {
      $img.attr('src', reader.result);
    };
    reader.readAsDataURL(file);
  });

  // いいね送信
  $('.js-like-icon').on('click', function() {
    $.ajax({
      type: "POST",
      url: "ajaxLike.php",
      data: {
        'tool_id': $(this).data('tool_id'),
      },
      dataType: "text",
    })
    .done((data) => {
      if (data !== undefined && data !== null && data !== '') {
        $(this).toggleClass('fa-heart-active');
        $(this).siblings('.post-like-count').text(data);
      }
    })
    .fail((data) => {});
  });

  // 投稿削除
  $('.js-delete-icon').on('click', function() {
    $('#js-dlt-form').submit();
  });
});
</script>
</body>

</html>