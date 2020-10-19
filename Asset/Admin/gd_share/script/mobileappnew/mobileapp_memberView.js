/**
* 모바일앱 회원상세
**/
$(document).ready(function(){
    $.extend({
        showProgressBar : function()
        {
            var progressImgMarginTop = Math.round(($(window).height() - 128) / 2) + $(window).scrollTop();

            $("body").append('<div id="mobileapp_progressBar" style="position:absolute;top:0;left:0;background:#44515b;filter:alpha(opacity=80);opacity:0.8;width:100%;height:'+$('body').height()+'px;cursor:progress;z-index:100000;margin:0 auto;text-align: center;"><img src="/admin/gd_share/img/mobileapp/common/page-loader-bk.gif" border="0" style="margin-top:'+progressImgMarginTop+'px;" /></div>');
        },

        hideProgressBar : function()
        {
            $("#mobileapp_progressBar").remove();
        },
    });

    //저장
    $("#mobileapp_memberViewModifyBtn").click(function(){
        var $ajax = $.ajax('mobileapp_ps.php', {
            method: "POST",
            data: {
                mode : 'modify_member_view',
                memNo : $('#mobileapp_memNo').val(),
                appFl : $(':radio[name="appFl"]:checked').val()
            },
            beforeSend : function (){
                $.showProgressBar();
            }
        });
        $ajax.done(function (responseData) {
            if(responseData === 'ok'){
                alert('저장되었습니다.');
            }
            else {
                alert(responseData);
            }
        });
        $ajax.always(function() {
            $.hideProgressBar();
        });
    });

    //목록
    $("#mobileapp_memberViewListBtn").click(function(){
        window.history.go(-1);
    });
    //더보기
    $("#mobileapp_moreMemberViewOrder").click(function(){
        alert('최근 3개월 동안의 주문을 불러옵니다');

        var memId = $(this).attr('data-memId');
        var treatDate1 = $(this).attr('data-treatDate1');
        var treatDate2 = $(this).attr('data-treatDate2');
        window.location.href = './mobileapp_order_list.php?treatDate[]='+treatDate1+'&treatDate[]='+treatDate2+'&key=m.memId&keyword=' + memId;
    });
    //주문상세페이지 이동
    $('#mobileapp_memberViewOrderArea').delegate("tr", "click", function(){
        window.location.href = './mobileapp_order_view.php?orderNo='+$(this).attr('data-orderNo');
    });
});
