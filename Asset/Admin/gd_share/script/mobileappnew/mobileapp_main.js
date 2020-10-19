/**
* 모바일앱 메인
**/
$(document).ready(function(){
    $(document).on({
        ajaxStart: function() { $("#ajax_loading").show();  },
        ajaxError: function() { $("#ajax_loading").show();  },
        ajaxStop: function() { $("#ajax_loading").hide(); }
    });

    window.onerror = function() {
        $("#ajax_loading").hide();    // global error
    };

    var $ajax = $.ajax('mobileapp_notice_ajax.php', {
        method: "POST"
    });
    $ajax.done(function (responseData) {
        $('#mobileapp_mainNoticeArea').empty();

        var html = '';

        //리스트 출력
        $.each(responseData, function(Key, Val) {
            if (Key > 2 || Key == 'disk') {
                return false;
            }
            html += '<tr data-idx="' + Val.idx + '">';
            html += '    <td style="height:36px;"><span style="margin-left: 10px;">' + Val.title + '</span><span class="pull-right text-graylighter" style="margin-right: 10px;">' + Val.writeday + '</span></td>';
            html += '</tr>';
        });

        $('#mobileapp_mainNoticeArea').append(html);

        if (responseData.disk.usedPer == '100') {
            alert('쇼핑몰 제공용량이 초과되었습니다.\n\n용량 초과 상태로 3일 이내 용량 확보가 되지 않을 시 전체 관리자 로그인이 차단됩니다.\n\n초과시점 : ' + responseData.disk.fullDate);
        }
    });

    $(document).on('click', '#mobileapp_mainNoticeArea tr', function() {
        var sId = $(this).attr('data-idx');
        location.href = 'https://mobileapp.godo.co.kr/new2/app/notice.php#' + sId;
    });

    $(document).on('click', '[id^="mobileapp_alarmTd"]', function(){
        var tempId = $(this).attr('id');
        var sId = tempId.replace('mobileapp_alarmTd', '');

        var now = new Date();
        var year= now.getFullYear();
        var mon = (now.getMonth() + 1) > 9 ? '' + (now.getMonth() + 1) : '0' + (now.getMonth() + 1);
        var day = now.getDate() > 9 ? '' + now.getDate() : '0' + now.getDate();
        var nowTime = year + '-' + mon + '-' + day + ' ' + ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2) + ':' + ('0' + now.getSeconds()).slice(-2);

        if (sId == 'order') {
            $.cookie('lastCheckOrder', nowTime, {expires : 7, path : '/'});
            location.href = '/mobileappnew/mobileapp_order_list.php?treatDate[]=' + $(this).attr('data-sdate') + '&treatDate[]=' + $(this).attr('data-edate');
        } else {
            $.cookie('lastCheckBoard' + sId, nowTime, {expires : 7, path : '/'});
            location.href = '/mobileappnew/mobileapp_board_article.php?sno=' + sId;
        }
    });

});
