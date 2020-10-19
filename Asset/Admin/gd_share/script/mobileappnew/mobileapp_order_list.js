/**
* 관리자앱 for 고도몰5 주문 리스트
**/
$(document).ready(function(){
    $(document).on({
        ajaxStart: function() { $('#ajax_loading').show();  },
        ajaxError: function() { $('#ajax_loading').show();  },
        ajaxStop: function() {
            $('#ajax_loading').hide();

            setTimeout(function() {
                var orderNo = localStorage.getItem("mobileapp-page-parameter-orderNo");
                $(".lists").each(function() {
                    if ($(this).attr('data-orderNo') == orderNo) {
                        $(window).scrollTop($(this).offset().top - 45);
                        localStorage.setItem("mobileapp-page-parameter-orderNo", '');
                        return false;
                    }
                });
            }, 300);
        }
    });

    window.onerror = function() {
        $('#ajax_loading').hide();    // global error
    };

    var page = 1;
    var pageNum = $('#pageNum').val();
    var requestParameter = [];

    $.extend({
        mobileapp_setRequestParameter : function(pageNum, moreStatus)
        {
            //더보기 클릭시
            if(moreStatus === true){
                var requestParameter = JSON.parse(sessionStorage.getItem("orderList_request"));
                page += 1;
                requestParameter['page'] = page;
                requestParameter['pageNum'] = pageNum;
            }
            else {
                page = 1;
                var requestParameter = {
                    mode : 'get_order_list',
                    page : page,
                    pageNum : pageNum,
                    statusMode : $('#statusMode').val(),
                    key : $('#key').val(),
                    keyword : $('#keyword').val(),
                    treatDate : [$('#mobileapp_treatDate1').val(), $('#mobileapp_treatDate2').val()],
                    settleKind : $('#settleKind').val(),
                };
                sessionStorage.setItem("orderList_request", JSON.stringify(requestParameter));
                localStorage.setItem("mobileapp-order-list-nowCount", 0);
            }

            return requestParameter;
        },

        mobileapp_getOrderList : function(requestParameter, listReset)
        {
            var $ajax = $.ajax('mobileapp_order_ajax.php', {
                method: "POST",
                data: requestParameter
            });
            $ajax.done(function (responseData) {
                if (typeof responseData != 'object') {
                    alert(responseData);
                    return false;
                }
                //회원리스트 리셋
                if(listReset === true){
                    $('#mobileapp_orderListArea').empty();
                    $('#nowCount').val();
                }

                var totalCount = responseData.totalCount;
                var nowCount = responseData.nowCount;
                var listCount = $('#nowCount').val();
                var html = '';
                var bgColor = '';

                $('#mobileapp_totalCount').html(totalCount);
                if(nowCount > 0){
                    //리스트 출력
                    $.each(responseData.data.order, function(orderNo, orderData) {
                        $.each(orderData.goods, function(sKey, sVal) {
                            $.each(sVal, function(dKey, dVal) {
                                $.each(dVal, function(Key, Val) {
                                    if (key > 0) {
                                        return true;
                                    }
                                    listCount++;

                                    html += '<tr class="lists text-left" data-orderNo="' + orderNo + '" data-state="' + Val.state + '">';
                                    html += '<td colspan="2" style="border-top: 2px solid #ccc;"><div style="margin: 5px 0 5px 10px;">';
                                    if (Val.orderChannelFl == 'payco') {
                                        html += '<img src="/admin/gd_share/img/channel_icon/payco.gif"> ';
                                    }
                                    if (Val.orderChannelFl == 'naverpay') {
                                        html += '<img src="/admin/gd_share/img/channel_icon/naverpay.gif"> ';
                                    }
                                    html += '<span style=" font-weight: bolder; color:#4f81bd;">' + orderNo + '</span>&nbsp;(' + Val.regDt.substring(0, 10) + ')';
                                    html += '</div></td>';
                                    html += '</tr>';
                                    html += '<tr class="lists" data-orderNo="' + orderNo + '" data-state="' + Val.state + '" style="background:' + bgColor + '">';
                                    html += '<td class="text-left" style="border-bottom: 2px solid #ccc; margin: 10px 50px; width:60%; !important;">';
                                    html += '<div style="margin: 5px 0 0 10px;">' + Val.orderName + '</div>';
                                    html += '<div style="margin: 0 0 5px 10px;">';
                                    if (Val.memNo == 0) {
                                        if (Val.memNoCheck == 0) {
                                            html += '비회원</div>';
                                        } else {
                                            html += '탈퇴회원</div>';
                                        }
                                    } else {
                                        html += Val.memId + '</div>';
                                    }
                                    html += '</td>';
                                    html += '<td class="text-center" style="border-bottom: 2px solid #ccc; width:40%; !important;">';
                                    html += '<div style="margin: 5px 0 0 0;">' + Val.icon + '</div>';
                                    html += '<div style="margin: 0 5px 0 0;">' + Val.state + '</div>';
                                    html += '</td>';
                                    html += '</tr>';
                                });
                            });
                        });
                    });

                    $('#nowCount').val(listCount);

                    if ($('#nowCount').val() == totalCount) {
                        $('#moreDisplay').css('display', 'none');
                    }
                    else {
                        $('#moreDisplay').css('display', 'block');
                    }
                }
                else {
                    html = '<tr class="no-list"><td colspan="3" class="text-center text-muted">주문이 존재하지 않습니다.</td></tr>';
                    $('#moreDisplay').css('display', 'none');
                }

                $('#mobileapp_orderListArea').append(html);

                //화면위치 조정
                var orderNo = localStorage.getItem("mobileapp-page-parameter-orderNo");
                if($.trim(orderNo) !== ''){
                    //페이지
                    page = Number(localStorage.getItem("mobileapp-order-list-page"));
                }

                //localStorage.setItem("mobileapp-order-list", $('#mobileapp_orderListArea').html());
                //localStorage.setItem("mobileapp-order-list-totalCount", totalCount);
                localStorage.setItem("mobileapp-order-list-nowCount", Number(localStorage.getItem("mobileapp-order-list-nowCount")) + Number(nowCount));
                localStorage.setItem("mobileapp-order-list-page", page);
            });
        },

        mobileapp_storageLoad : function()
        {
            var mobileapp_page = localStorage.getItem("mobileapp-page");

            if(mobileapp_page === 'mobileapp_order_view.php'){
                //리스트 컨텐츠 복구
                //$('#mobileapp_orderListArea').html(localStorage.getItem("mobileapp-order-list"));
                //카운트
                //$('#mobileapp_totalCount').html(localStorage.getItem("mobileapp-order-list-totalCount"));
                requestParameter = $.mobileapp_setRequestParameter(Number(localStorage.getItem("mobileapp-order-list-nowCount")), false);
                $.mobileapp_getOrderList(requestParameter, true);

                return true;
            }

            return false;
        },
    });

    // 검색
    $('#mobileapp_search').click(function(){
        if ($('#mobileapp_treatDate1').val().length < 8) {
            $('#mobileapp_treatDate1').focus();
            alert('검색기간을 입력해주세요');
            return false;
        }
        if ($('#mobileapp_treatDate2').val().length < 8) {
            $('#mobileapp_treatDate2').focus();
            alert('검색기간을 입력해주세요');
            return false;
        }
        if ($('#mobileapp_treatDate1').val() > $('#mobileapp_treatDate2').val()) {
            $('#mobileapp_treatDate1').focus();
            alert('검색 시작일이 종료일보다 늦은날짜입니다.');
            return false;
        }
        $('#nowCount').val(0);
        requestParameter = $.mobileapp_setRequestParameter($('#pageNum').val(), false);
        $.mobileapp_getOrderList(requestParameter, true);
    });

    // 페이지 리스트 개수 변경시
    $('#pageNum').change(function(){
        $('#nowCount').val(0);
        requestParameter = $.mobileapp_setRequestParameter($(this).val(), false);
        $.mobileapp_getOrderList(requestParameter, true);
    });

    // 더보기
    $('#mobileapp_moreOrderList').click(function(){
        requestParameter = $.mobileapp_setRequestParameter($('#pageNum').val(), true);
        $.mobileapp_getOrderList(requestParameter, false);
    });

    // 주문일자 지정일자 클릭시
    $(".mobileappDateSelector").click(function(){
        $('#mobileapp_treatDate1').val($(this).attr('data-interval'));
        $('#mobileapp_treatDate2').val($(".mobileappDateSelector").eq(0).attr('data-interval'));
    });

    // 주문일자 삭제 체크
    $('#mobileapp_treatDate1, #mobileapp_treatDate2').keyup(function(e) {
        if ($(this).val().length == 0) {
            //$(this).val($(".mobileappDateSelector").eq(0).attr('data-interval'));
        }
    });
    // 주문일자 변경 이벤트
    $('#mobileapp_treatDate1, #mobileapp_treatDate2').change(function(e) {
        if ($(this).val().indexOf('.') != -1) {
            alert('검색기간 항목에는 숫자만 입력해주세요');
            $(this).val($(this).val().replace('.', ''));
        }
    });
    // 주문일자 자릿수 체크
    $('#mobileapp_treatDate1, #mobileapp_treatDate2').keypress(function(e){
        if($(this).val().length >= 8){
            e.preventDefault();
            return false;
        }
    });

    //주문상세페이지 이동
    $('#mobileapp_orderListArea').delegate("tr", "click", function(){
        if ($(this).attr('data-state') == '결제중단/실패') {
            alert('결제중단/실패주문은 PC버전에서 관리가능합니다.');
        } else {
            if ($(this).attr('data-orderNo') != undefined) {
                localStorage.setItem("mobileapp-page-parameter-orderNo", $(this).attr('data-orderNo'));
                location.href = './mobileapp_order_view.php?orderNo=' + $(this).attr('data-orderNo');
            }
        }
    });

    //초기화
    $('#mobileapp_resetBtn').click(function(){
        $('#keyword').val('');
        $('#statusMode, #key, #settleKind').val('all');
        $('#mobileapp_treatDate1, #mobileapp_treatDate2').val($(".mobileappDateSelector").eq(0).attr('data-interval'));
        $('#statusMode').siblings('label').text($('#statusMode option:first').text());
        $('#key').siblings('label').text($('#key option:first').text());
        $('#settleKind').siblings('label').text($('#settleKind option:first').text());
    });

    $('#statusMode').siblings('label').text($('#statusMode option:selected').text());
    $('#key').siblings('label').text($('#key option:selected').text());
    $('#settleKind').siblings('label').text($('#settleKind option:selected').text());

    if($.mobileapp_storageLoad() === false){
        $( "#mobileapp_search" ).trigger( "click" );
    }

});
