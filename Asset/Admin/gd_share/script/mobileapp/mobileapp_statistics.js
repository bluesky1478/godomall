/**
* 모바일앱 통계 : 방문 분석, 주문 분석, 판매순위 분석
**/
$(document).ready(function(){
    var listData = [];

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

        setComma : function(str)
        {
            return String(str).replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
        },

        //총 검색 건수
        mobileapp_displayTotalCount : function()
        {
            if($.mobileapp_getMode() === 'sale'){
                $('#mobileapp_totalCount').html($.setComma(listData.total_count));
            }
        },

        mobileapp_getAjaxMode : function()
        {
            var mode = '';
            switch($.mobileapp_getMode()){
                case 'visit' :
                    mode = 'get_statistics_visit';
                    break;

                case 'order' :
                    mode = 'get_statistics_order';
                    break;

                case 'sale' :
                    mode = 'get_statistics_sale';
                    break;
            }

            return mode;
        },

        mobileapp_getMode : function()
        {
            return $("#mobileapp_mode").val();
        },
        //더보기 버튼 노출 확인
        mobileapp_checkDisplayMore : function()
        {
            if($.mobileapp_getMode() === 'sale'){
                var trLength = Number($('#mobileapp_listArea > tr').length);
            }
            else {
                var trLength = Number($('#mobileapp_listArea > tr').length) - 1;
            }

            if(Number(listData.total_count) <= Number(trLength)){
                $("#mobileapp_moreBtn").css('display', 'none');
            }
            else{
                $("#mobileapp_moreBtn").css('display', '');
            }
        },

        //기간 validation check
        mobileapp_checkDate : function(firstDate, lastDate)
        {
            if(firstDate === true){
                var searchDate = $('#mobileapp_searchDate1').val();

                if($.isNumeric(searchDate) === false){
                    alert("숫자를 입력해 주세요.");
                    $('#mobileapp_searchDate1').focus();
                    return false;
                }
                if(searchDate.length != 8){
                    alert("YYYYMMDD 형식으로 입력해 주세요.");
                    $('#mobileapp_searchDate1').focus();
                    return false;
                }
            }
            if(lastDate === true){
                var searchDate = $('#mobileapp_searchDate2').val();

                if($.isNumeric(searchDate) === false){
                    alert("숫자를 입력해 주세요.");
                    $('#mobileapp_searchDate2').focus();
                    return false;
                }
                if(searchDate.length != 8){
                    alert("YYYYMMDD 형식으로 입력해 주세요.");
                    $('#mobileapp_searchDate2').focus();
                    return false;
                }
            }

            return true;
        },

        //합계노출
        mobileapp_writeHtml_sumRow : function()
        {
            var html = '';
            switch($.mobileapp_getMode()){
                //방문분석
                case 'visit':
                    html += '<tr class="lists text-center" style="background-color: #f5f9fc;">';
                    html += '<td>합계</td>';
                    html += '<td>'+listData.total_visitCount+'</td>';
                    html += '<td>'+listData.total_pv+'</td>';
                    html += '</tr>';
                    break;

                //주문분석
                case 'order':
                    html += '<tr class="lists text-center" style="background-color: #f5f9fc;">';
                    html += '<td>합계</td>';
                    html += '<td>'+listData.total.memberCnt+'</td>';
                    html += '<td>'+listData.total.orderCnt+'</td>';
                    html += '<td>'+listData.total.goodsCnt+'</td>';
                    html += '<td>'+listData.total.goodsPrice+'</td>';
                    html += '</tr>';
                    break;

                //판매순위 분석
                default :
                    html = '';
                    break;
            }

            $('#mobileapp_listArea').append(html);
        },

        //리스트노출
        mobileapp_writeHtml_list : function()
        {
            var html = '';
            if($.mobileapp_getMode() === 'sale'){
                var startPageNum = Number($('#mobileapp_listArea > tr').length);
            }
            else {
                var startPageNum = Number($('#mobileapp_listArea > tr').length) - 1;
            }
            var endPageNum = Number($('#pageNum').val()) + Number(startPageNum);
            var idx = 0;
            switch($.mobileapp_getMode()){
                //방문분석
                case 'visit':
                    $.each(listData.list, function (index, row) {
                        idx++;
                        if (startPageNum >= idx) {
                            return true;
                        }

                        html += '<tr class="lists text-center">';
                        html += '<td>' + index + '</td>';
                        html += '<td>' + row.visitCount + '</td>';
                        html += '<td>' + row.pv + '</td>';
                        html += '</tr>';

                        if (idx >= endPageNum) {
                            return false;
                        }
                    });
                    break;

                //주문분석
                case 'order':
                    $.each(listData.list, function (index, row) {
                        idx++;
                        if (startPageNum >= idx) {
                            return true;
                        }

                        html += '<tr class="lists text-center">';
                        html += '<td>' + index + '</td>';
                        html += '<td>' + row.memberCnt + '</td>';
                        html += '<td>' + row.orderCnt + '</td>';
                        html += '<td>' + row.goodsCnt + '</td>';
                        html += '<td style="background-color: #f5f9fc;">' + row.goodsPrice + '</td>';
                        html += '</tr>';

                        if (idx >= endPageNum) {
                            return false;
                        }
                    });
                    break;

                //판매순위분석
                case 'sale':
                    if(listData.list.length > 0){
                        $.each(listData.list, function(index, row){
                            idx++;
                            if(startPageNum >= idx){
                                return true;
                            }

                            html += '<tr class="lists text-center">';
                            html += '<td>' + idx + '</td>';
                            html += '<td>'+row.name+'</td>';
                            html += '<td>'+row.price+'</td>';
                            html += '<td>'+row.count+'</td>';
                            html += '</tr>';

                            if(idx >= endPageNum){
                                return false;
                            }
                        });
                    }
                    break;
            }

            $('#mobileapp_listArea').append(html);
        },

        mobileapp_listPut : function(resetStart)
        {
            if(resetStart === true){
                $('#mobileapp_listArea').empty();

                //합계 노출
                $.mobileapp_writeHtml_sumRow();
            }

            //리스트 출력
            $.mobileapp_writeHtml_list();

            //더보기 버튼 노출 여부
            $.mobileapp_checkDisplayMore();

            //총 갯수 표시
            $.mobileapp_displayTotalCount();
        },

        //ajax
        mobileapp_getList : function()
        {
            var requestParameter = {
                mode : $.mobileapp_getAjaxMode(),
                searchDate : [$('#mobileapp_searchDate1').val(), $('#mobileapp_searchDate2').val()]
            };
            var $ajax = $.ajax('mobileapp_ps.php', {
                method: "POST",
                data: requestParameter,
                beforeSend : function (){
                    $.showProgressBar();
                }
            });
            $ajax.done(function (responseData) {
                if(responseData.result !== 'ok'){
                    alert(responseData);
                    return;
                }

                listData = responseData;

                $.mobileapp_listPut(true);
            });
            $ajax.always(function() {
                $.hideProgressBar();
            });
        },
    });

    //검색기간
    $(".mobileappDateSelector").click(function(){
        $('#mobileapp_searchDate1').val($(this).attr('data-interval'));
        $('#mobileapp_searchDate2').val($('#mobileapp_standardDate').val());
    });

    //검색기간 자릿수 체크
    $("#mobileapp_searchDate1, #mobileapp_searchDate2").keypress(function(e){
        if($(this).val().length >= 8){
            e.preventDefault();
            return false;
        }
    });

    //검색
    $("#mobileapp_search").click(function(){
        //검색 기간 체크
        if($.mobileapp_checkDate(true, true) === false){
            return;
        }

        $.mobileapp_getList();
    });

    //노출개수
    $("#pageNum").change(function(){
        $.mobileapp_listPut(true);
    });

    //더보기
    $("#mobileapp_moreBtn").click(function(){
        $.mobileapp_listPut(false);
    });

    $( "#mobileapp_search" ).trigger( "click" );
});
