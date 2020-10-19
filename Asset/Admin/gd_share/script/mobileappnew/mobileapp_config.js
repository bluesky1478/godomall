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

    var device_uid = $.cookie('device_uid');
    var params = {
        'device_uid': $('#device_uid').val(),
        'shop_domain': $('#shop_domain').val(),
        'godo5': 'T'
    };
    var $ajax = $.ajax('https://mobilepush.godo.co.kr/new/api/v1/schedule/show', {
        method: "POST",
        dataType: 'json',
        data: params
    });
    $ajax.done(function (responseData) {
        if (responseData.resultCode != '000') {
            // 초기화
            $('input:radio[name=order_alim]:input[value="0"]').attr("checked", true);
            $('input:radio[name=stop_run_type]:input[value="0"]').attr("checked", true);
            $('#send_term optio:eq(0)').attr('selected', 'selected');
            $('#alarm_stop_area').css('display', 'none');
            return false;
        } else {
            if (responseData.data.stop_run_type == '1') {
                $('#alarm_stop_area').css('display', '');
            } else {
                $('#alarm_stop_area').css('display', 'none');
            }
            $('input:radio[name=order_alim]:input[value="' + responseData.data.order_alim + '"]').attr("checked", true);
            $('#send_term option[value="' + responseData.data.send_term + '"]').attr('selected', 'selected');
            $('#send_term').siblings('label').text($('#send_term option:selected').text());
            $('input:radio[name=stop_run_type]:input[value="' + responseData.data.stop_run_type + '"]').attr("checked", true);
            $('#stop_stime option[value="' + responseData.data.stop_stime + '"]').attr('selected', 'selected');
            $('#stop_stime').siblings('label').text($('#stop_stime option:selected').text());
            $('#stop_etime option[value="' + responseData.data.stop_etime + '"]').attr('selected', 'selected');
            $('#stop_etime').siblings('label').text($('#stop_etime option:selected').text());
        }
    });

    $('input[id^="stop_run_type"]').click(function() {
        if ($(this).attr('id') == 'stop_run_type1') {
            $('#alarm_stop_area').css('display', '');
        } else {
            $('#alarm_stop_area').css('display', 'none');
        }
    });

    $('#updateConfig').click(function() {
        var $ajax = $.ajax('https://mobilepush.godo.co.kr/new/api/v1/schedule/update', {
            method: "POST",
            dataType: 'json',
            data: {
                shop_domain: $('#shop_domain').val(),
                app_id: 'com.godo.com',
                device_uid: $('#device_uid').val(),
                order_alim: $('input:radio[name="order_alim"]:checked').val(),
                send_term: $('#send_term option:selected').val(),
                stop_run_type: $('input:radio[name="stop_run_type"]:checked').val(),
                stop_sdate: '2013-09-09',
                stop_edate: '2013-09-10',
                stop_stime: $('#stop_stime option:selected').val(),
                stop_etime: $('#stop_etime option:selected').val(),
                schedule_alim: '0',
                godo5: 'T',
            }
        });
        $ajax.done(function (responseData) {
            alert(responseData.resultMsg);
        });
    });
});
