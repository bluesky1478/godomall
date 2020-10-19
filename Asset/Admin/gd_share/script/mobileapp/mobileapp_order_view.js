/**
* 모바일앱 회원상세
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

    // 주문내역 목록
    $("[id$='List_display']").on('click', function(){
        var tempId = $(this).attr('id');
        var sId = tempId.replace('_display', '');

        // 기본적으로 건수가 0이면 ajax체크도 하지말자
        if ($('#' + sId + '_num').val() > 0) {
            if ($('#' + sId + '_div').css('display') == 'none') {
                var sId2 = '';
                $("[id$='List_display']").each(function() {
                    sId2 = $(this).attr('id').replace('_display', '');
                    if ($(this).attr('id') != tempId) {
                        $('#' + sId2 + '_div').css('display', 'none');
                        $('#' + sId2).empty();
                    }
                });

                var html = '';

                var $ajax = $.ajax('mobileapp_order_ajax.php', {
                    method: "POST",
                    data: {
                    mode : 'get_order_list_part',
                    part : sId,
                    orderNo : $('#orderNo').val(),
                    }
                });

                $ajax.done(function (responseData) {
                    if (responseData.resultList.count == 0) {
                        alert('데이터를 가져오지 못했습니다.');
                        location.reload();
                    }
                    else {
                        $('#' + sId).empty();
                        $.each(responseData.resultList.list, function (sKey, sVal) {
                            html += '<tr>';
                            html += '    <td>';
                            html += '        <div class="pd-wrap overflow-h" style="margin-top:5px; margin-left:5px; margin-right:5px;">';
                            html += '            <div style="width:100%">';
                            html += '            <span class="pd-info" >';
                            html += '              ' + sVal.image;
                            html += '            </span>';
                            html += '            <span class="pd-info" style="max-width: 75%; word-break: break-all !important;">';
                            html += '                <p>' + sVal.name + '</p>';
                            $.each(sVal.option, function (sKey2, sVal2) {
                                html += '                <p>' + sVal2 + '</p>';
                            });
                            html += '                <p>' + sVal.price + '<p>';
                            html += '            </span>';
                            html += '            </div>';
                            html += '        </div>';
                            html += '        <hr style="width:92%; margin-top:5px; margin-bottom:5px;">';
                            html += '        <div style="margin-left:10px; margin-right:5px;">';
                            html += '            <span class="pull-left mgt5"><strong style="color:#fa2828;" id="stringState' + sVal.sno + '">' + sVal.state + '</strong>';
                            if (sVal.invoiceNo != '') {
                                html += ' (' + sVal.delivery_company + ' ' + sVal.invoiceNo + ')';
                            }
                            html += '</span>';
                            html += '            <input type="hidden" name="bundle[statusMode][' + sVal.sno + ']" value="' + sVal.orderStatus + '">';
                            html += '            <input type="hidden" name="bundle[goods][sno][' + sVal.sno + ']" value="' + sVal.sno + '">';
                            html += '            <input type="hidden" name="bundle[goods][invoiceCompanySno][' + sVal.sno + ']" value="' + sVal.invoiceCompanySno + '">';
                            html += '            <input type="hidden" name="bundle[goods][invoiceNo][' + sVal.sno + ']" value="' + sVal.invoiceNo + '">';
                            if (sVal.orderStatus.substr(0, 1) != 'c') {
                                html += '            <button type="button" id="sno_bundle' + sVal.sno + '" class="btn btn-sm btn-info pull-right border-r-n" style="color:black !important; margin-right:7px; margin-bottom:3px;" data-toggle="modal" data-target="#myModal">수정</button>';
                            }
                            html += '        </div>';
                            html += '    </td>';
                            html += '</tr>';
                        });
                    }

                    $('#' + sId ).append(html);
                    $('#selectBoxOrderStatusHtmlLi').html('<label for="select-opt">= 상품상태 =</label>' + responseData.selectBoxOrderStatusHtml);
                    $('#selectBoxDeliveryHtmlLi').html('<label for="select-opt">= 배송업체 =</label>' + responseData.selectBoxDeliveryHtml);
                    var select = $('.select-opt');
                    select.change( function () {
                        var select_name = $(this).children('option:selected').text();
                        $(this).siblings('label').text(select_name);
                    });
                    $('#' + sId + '_div').css('display', 'block');
                    $('#' + sId + '_icon').html('<img src="/admin/gd_share/img/mobileapp/icon/arrow_close.png" width="15">');

                });
            } else {
                $('#' + sId + '_div').css('display', 'none');
                $('#' + sId + '_icon').html('<img src="/admin/gd_share/img/mobileapp/icon/arrow_open.png" width="15">');
            }
        }
    });

    // 품목별 수정 클릭시
    $(document).on('click', '[id^="sno_bundle"]', function(){
        var bundleId = $(this).attr('id').replace('sno_bundle', '');
        /*
         if ($('select[name="bundle[orderStatus]"] option[value="' + $('input[name="bundle[statusMode][' + bundleId + ']"]').val() + '"]').length > 0) {
         $('select[name="bundle[orderStatus]"]').val($('input[name="bundle[statusMode][' + bundleId + ']"]').val()).prop('selected', 'true');
         $('select[name="bundle[orderStatus]"]').siblings('label').text($('select[name="bundle[orderStatus]"] option:selected').text());
         } else {
         $('#bundleOrderStatus option:eq(0)').prop('selected', true);
         $('select[name="bundle[orderStatus]"]').siblings('label').text($('#stringState' + bundleId).text());
         }
         */

        $('#bundleOrderStatus option:eq(0)').prop('selected', true);
        $('select[name="bundle[orderStatus]"]').siblings('label').text($('#stringState' + bundleId).text());

        if ($('input[name="bundle[goods][invoiceNo][' + bundleId + ']"]').val() == '') {
            $("#applyDeliverySno option:eq(0)").prop('selected', true);
            $('#applyDeliverySno').siblings('label').text('= 배송업체 =');
            $('#invoiceNo').val('');
        }
        else {
            $('#applyDeliverySno').val($('input[name="bundle[goods][invoiceCompanySno][' + bundleId + ']"]').val()).prop('selected', 'true');
            $('#applyDeliverySno').siblings('label').text($('#applyDeliverySno option:selected').text());
            $('#invoiceNo').val($('input[name="bundle[goods][invoiceNo][' + bundleId + ']"]').val());
        }
        $('#modify_type').val(bundleId);
    });

    // 주문내역 목록
    $("[id$='ListModify']").on('click', function(){
        $("#bundleOrderStatus option:eq(0)").prop('selected', true);
        $('#bundleOrderStatus').siblings('label').text('= 상품상태 =');
        $("#applyDeliverySno option:eq(0)").prop('selected', true);
        $('#applyDeliverySno').siblings('label').text('= 배송업체 =');
        $('#invoiceNo').val('');
        $('#modify_type').val('all');
    });

    // 상품상태/송장등록 레이어에서 상품상태 변경시
    $(document).on('change', '#bundleOrderStatus', function(){
        var thisText = $('#bundleOrderStatus option:selected').text();
        var tempStatusCheck = 0;

        if ($('#modify_type').val() == 'all') {
            $.each($("[name^='bundle[goods][sno]']"), function () {
                if ($('input[name="bundle[statusMode][' + $(this).val() + ']"]').val() == $('#bundleOrderStatus').val()) {
                    tempStatusCheck++;
                }
            });
            if (tempStatusCheck > 0) {
                alert(thisText + ' 상태와 상태가 동일한 주문이 있습니다. 개별 수정으로 처리해주세요.');
            }
        } else {
            if ($('input[name="bundle[statusMode][' + $('#modify_type').val() + ']"]').val() == $('#bundleOrderStatus').val()) {
                tempStatusCheck++;
            }
            if (tempStatusCheck > 0) {
                alert(thisText + ' 상태와 상태가 동일합니다. 다른 주문 상태를 선택해주세요.');
            }
        }

        if (tempStatusCheck > 0) {
            $('#bundleOrderStatus').val('');
            var select_name = $('#bundleOrderStatus').children('option:selected').text();
            $('#bundleOrderStatus').siblings('label').text(select_name);
        }
    });

    // 주문내역 목록
    $('#state_delivery_modify').click(function(){
        if ($('#bundleOrderStatus').val() == '' && $('#applyDeliverySno').val() == 0 && $('#invoiceNo').val() == '') {
            alert('상품상태 혹은 배송업체와 송장번호를 입력해주세요.');
            return false;
        }
        if ($('#applyDeliverySno').val() != 0 && $('#invoiceNo').val() == '') {
            alert('송장번호를 입력해주세요.');
            return false;
        }
        if ($('#applyDeliverySno').val() == 0 && $('#invoiceNo').val() != '') {
            alert('배송업체를 선택해주세요.');
            return false;
        }

        var bundle = {};
        var statusMode = {};
        var statusCheck = {};
        var changeStatus = $('#bundleOrderStatus').val();

        bundle['statusCheck'] = {};
        bundle['goods'] = {};
        bundle['goods']['sno'] = {};
        bundle['goods']['invoiceCompanySno'] = {};
        bundle['goods']['invoiceNo'] = {};
        if ($('#modify_type').val() == 'all') {
            var tempCount = 0;
            var tempStatus = '';
            var tempStatusCheck = 0;
            $.each($("[name^='bundle[goods][sno]']"), function () {
                if ($('input[name="bundle[statusMode][' + $(this).val() + ']"]').val() == $('#bundleOrderStatus').val()) {
                    tempStatusCheck++;
                }
                statusMode[$('input[name="bundle[statusMode][' + $(this).val() + ']"]').val().substr(0, 1)] = [];
                statusCheck[$('input[name="bundle[statusMode][' + $(this).val() + ']"]').val().substr(0, 1)] = [];
            });
            $.each($("[name^='bundle[goods][sno]']"), function () {
                bundle['statusCheck'][$(this).val()] = $(this).val();
                bundle['goods']['sno'][$(this).val()] = $(this).val();
                bundle['goods']['invoiceCompanySno'][$(this).val()] = $('#applyDeliverySno').val();
                bundle['goods']['invoiceNo'][$(this).val()] =  $('#invoiceNo').val();

                if (tempStatus != $('input[name="bundle[statusMode][' + $(this).val() + ']"]').val().substr(0, 1)) {
                    tempCount = 0;
                    tempStatus = $('input[name="bundle[statusMode][' + $(this).val() + ']"]').val().substr(0, 1);
                }
                statusMode[$('input[name="bundle[statusMode][' + $(this).val() + ']"]').val().substr(0, 1)].push($('input[name="bundle[statusMode][' + $(this).val() + ']"]').val().substr(0, 1));
                statusCheck[$('input[name="bundle[statusMode][' + $(this).val() + ']"]').val().substr(0, 1)].push($('#orderNo').val() + '||' + $(this).val());
                tempCount++;
            });

            if (tempStatusCheck > 0) {
                alert($('#bundleOrderStatus option:selected').text() + ' 상태와 상태가 동일한 주문이 있습니다. 개별 수정으로 처리해주세요.');
                return false;
            }
        } else {
            var tempStatusCheck = 0;

            bundle['statusCheck'][$('#modify_type').val()] = $('#modify_type').val();
            bundle['goods']['sno'][$('#modify_type').val()] = $('#modify_type').val();
            bundle['goods']['invoiceCompanySno'][$('#modify_type').val()] = $('#applyDeliverySno').val();
            bundle['goods']['invoiceNo'][$('#modify_type').val()] = $('#invoiceNo').val();

            statusMode[$('input[name="bundle[statusMode][' + $('#modify_type').val() + ']"]').val().substr(0, 1)] = [];
            statusMode[$('input[name="bundle[statusMode][' + $('#modify_type').val() + ']"]').val().substr(0, 1)].push($('input[name="bundle[statusMode][' + $('#modify_type').val() + ']"]').val().substr(0, 1));

            statusCheck[$('input[name="bundle[statusMode][' + $('#modify_type').val() + ']"]').val().substr(0, 1)] = [];
            statusCheck[$('input[name="bundle[statusMode][' + $('#modify_type').val() + ']"]').val().substr(0, 1)].push($('#orderNo').val() + '||' + $('#modify_type').val());

            if ($('input[name="bundle[statusMode][' + $('#modify_type').val() + ']"]').val() == $('#bundleOrderStatus').val()) {
                tempStatusCheck++;
            }

            if (tempStatusCheck > 0) {
                alert($('#bundleOrderStatus option:selected').text() + ' 상태와 상태가 동일합니다. 다른 주문 상태를 선택해주세요.');
                return false;
            }
        }

        var $ajax = $.ajax('mobileapp_order_ajax.php', {
            method: "POST",

            dataType: "json",
            data: {
                mode : 'modify_order_info',
                orderNo : $('#orderNo').val(),
                statusMode : statusMode,
                statusCheck : statusCheck,
                changeStatus : changeStatus,
                bundle : bundle,
            }
        });
        $ajax.done(function (responseData) {
            if(responseData === 'ok'){
                alert('저장되었습니다.');
                location.reload();
            }
            else {
                alert(responseData);
            }
        });
    });

    // 배송정보/요청사항/상담메모 수정
    $("#mobileapp_orderViewModifyBtn").click(function(){
        if ($.trim($('[name="info[receiverName]"]').val()).length < 1) {
            alert('수령자명을 입력해주세요');
            $('[name="info[receiverName]"]').focus();
            return false;
        }
        if ($.trim($('[name="info[receiverCellPhone]"]').val()).length < 1) {
            alert('휴대폰 번호를 입력해주세요');
            $('[name="info[receiverCellPhone]"]').focus();
            return false;
        }
        if ($.trim($('[name="info[receiverZonecode]"]').val()).length < 1) {
            alert('우편번호를 입력해주세요');
            $('[name="info[receiverZonecode]"]').focus();
            return false;
        }
        if ($.trim($('[name="info[receiverAddress]"]').val()).length < 1) {
            alert('주소를 입력해주세요');
            $('[name="info[receiverAddress]"]').focus();
            return false;
        }
        if ($.trim($('[name="info[receiverAddressSub]"]').val()).length < 1) {
            alert('주소를 입력해주세요');
            $('[name="info[receiverAddressSub]"]').focus();
            return false;
        }

        var info = {};
        info['sno'] = $('input[name="info[sno]"]').val();
        info['receiverName'] = $('input[name="info[receiverName]"]').val();
        info['receiverCellPhone'] = $('input[name="info[receiverCellPhone]"]').val();
        info['receiverZonecode'] = $('input[name="info[receiverZonecode]"]').val();
        info['receiverZipcode'] = $('input[name="info[receiverZipcode]"]').val();
        info['receiverAddress'] = $('textarea[name="info[receiverAddress]"]').val();
        info['receiverAddressSub'] = $('textarea[name="info[receiverAddressSub]"]').val();
        info['orderMemo'] = $('textarea[name="info[orderMemo]"]').val();

        var consult = {};
        consult['sno'] = $('input[name="consult[sno]"]').val();
        consult['orderNo'] = $('#orderNo').val();
        consult['requestMemo'] = $('textarea[name="consult[requestMemo]"]').val();
        consult['consultMemo'] = $('textarea[name="consult[consultMemo]"]').val();

        var $ajax = $.ajax('mobileapp_order_ajax.php', {
            method: "POST",
            data: {
                mode : 'modify_order_receiver_info',
                orderNo : $('#orderNo').val(),
                info : info,
                consult : consult,
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
    });

    //목록
    $("#mobileapp_orderViewListBtn").click(function(){
        window.history.back(-1);
    });

    // 주소 우편번호 길이제한
    $('input[name="info[receiverZonecode]"]').keyup(function(){
        if ($(this).val().length > 5) {
            alert('우편번호는 5자리까지 입력가능합니다.');
            $(this).val($(this).val().substr(0, 5));
        }
    });

    // 요청사항 길이제한
    $('textarea[name="consult[requestMemo]"]').keyup(function(){
        if ($(this).val().length > 1000) {
            alert('요청사항은 1000자까지 입력가능합니다.');
            $(this).val($(this).val().substr(0, 5));
        }
    });

    // 상담메모 길이제한
    $('textarea[name="consult[consultMemo]"]').keyup(function(){
        if ($(this).val().length > 1000) {
            alert('상담메모는 1000자까지 입력가능합니다.');
            $(this).val($(this).val().substr(0, 5));
        }
    });

    // 주문내역 취소내역등 항목별로 내역이있는 부분들 자동으로 열리게
    /*
    $("[id$='List_num']").each(function() {
        if ($(this).val() > 0) {
            $( "#" + $(this).attr('id')).trigger( "click" );
        }
    });
    */
    // 처음에는 주문내역만 열리게 변경
    $('#mobileapp_orderList_display').trigger( "click" );

    $("#memberLink").click(function(){
        $("[id$='List_div']").each(function() {
            var tempId = $(this).attr('id');
            var sId = tempId.replace('List_div', '');
            $( "#" + $(this).attr('id')).css('display', 'none');
            $('#' + sId + 'List_icon').html('<img src="/admin/gd_share/img/mobileapp/icon/arrow_open.png" width="15">');
        });

        if ($(this).attr('data-memNo') != '0') {
            location.href = './mobileapp_member_view.php?memNo=' + $(this).attr('data-memNo');
        }
    });
});
