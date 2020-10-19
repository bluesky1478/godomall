/**
* 모바일앱 게시판
**/
$(document).ready(function(){
    // 글내용불러오기
    $('#boardList').delegate("tr", "click", function(){
        if ($(this).attr('data-sno') != undefined) {
            var sId = $(this).attr('data-sno');
            location.href = '/mobileapp/mobileapp_board_article.php?sno=' + sId;
        }
    });

});
