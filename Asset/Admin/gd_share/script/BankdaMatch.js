/**
 * 뱅크다 관련 스크립트
 *
 * @copyright Copyright (c), Godosoft
 */

function account_list( query ){

    listEle = $('#list_form');
    pageRtotalEle = $('#page_rtotal');
    pageRecodeEle = $('#page_recode');
    pageNowEle = $('#page_now');
    pageTotalEle = $('#page_total');
    pageNaviEle = $('#page_navi');

    if($('input[name="sword"]').val() !='') {
        $('input[name="page"]').val('');
        query ='';
    }

    // Create Query
    if (query == undefined || !query) {
        var query = $('#frmSearchBase').serialize();
    } else if (query != '') {
        $('input[name="query"]').val(query);
        query = $.param(query.parse_str());
    }

    var func_list_init = function()
    {
        if (listEle != null) {
            while (listEle.get(0).rows.length > 1) {
                listEle.get(0).deleteRow(listEle.get(0).rows.length - 1); // 결과 rows 초기화
            }
        }

        pageRtotalEle.text(0);
        pageRecodeEle.text(0);
        pageNowEle.text(0);
        pageTotalEle.text(0);
        pageNaviEle.text('');
    }

    var func_listing = function(lists) {

        var gdstatusNm = new Array();
        gdstatusNm['T'] = '매칭성공 (by시스템)';
        gdstatusNm['B'] = '매칭성공 (by관리자)';
        gdstatusNm['F'] = '매칭실패 (불일치)';
        gdstatusNm['S'] = '매칭실패 (동명이인)';
        gdstatusNm['A'] = '관리자입금확인완료';
        gdstatusNm['U'] = '관리자미확인';
        gdstatusNm['M'] = '수동매칭';

        /*
         * 수동 처리 관리자 Id  Ajax 배열 호출
         * 리턴 값 존재할 경우 관리자 아이디데이터 변수 삽입
         */
        var managerInfo = bankManualManagerInfo(lists);
        if(managerInfo.responseText) {
            var manualControllerManageId = JSON.parse(managerInfo.responseText);
        } else {
            var manualControllerManageId ='';
        }

        /*
         * 관리자 입금내역 메모 Ajax 배열 호출
         * 리턴 값 존재할 경우 메모데이터 변수 삽입
         */
        var managerAdminMemo = bankAdminMemoArraySelect(lists);
        if(managerAdminMemo.responseText) {
            var adminMemoData = JSON.parse(managerAdminMemo.responseText);
        } else {
            var adminMemoData ='';
        }

        var len = lists.length;
        for (n = 0; n < len; n++) {
            l_row = lists[n];

            newTr = listEle.get(0).insertRow(-1);
            newTr.height='25';
            newTr.align='center';
            newTr.bgcolor='#ffffff';
            newTr.bg='#ffffff';
            newTr.setAttribute('updateItems', '');

            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            newTd.innerHTML = l_row.no;
            if (l_row.gdstatus == 'F' || l_row.gdstatus == 'S' || l_row.gdstatus == 'A' || l_row.gdstatus == 'U') {
                newTd.innerHTML += '<input type="hidden" name="bkcode" value="' + l_row.bkcode + '" subject="' + l_row.bkjukyo + ' (' + l_row.bkinput.number_format() + ')"/>';
            }

            newTd = newTr.insertCell(-1);
            $(newTd).addClass('date');
            newTd.innerHTML = l_row.bkdate.substr(2,2) + '-' + l_row.bkdate.substr(4,2) + '-' + l_row.bkdate.substr(6,2);

            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            newTd.innerHTML = '<span style="color:#0074BA">' + l_row.bkacctno + '</span>';

            newTd = newTr.insertCell(-1);
            newTd.innerHTML = l_row.bkname;

            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            newTd.innerHTML = '<b>' + l_row.bkinput.number_format() + '</b>';

            newTd = newTr.insertCell(-1);
            newTd.innerHTML = l_row.bkjukyo;

            newTd = newTr.insertCell(-1);
            if (l_row.gdstatus == 'F' || l_row.gdstatus == 'S' || l_row.gdstatus == 'A' || l_row.gdstatus == 'U') {
                newTd.innerHTML = '\
                <select name="gdstatus" valued="' + l_row.gdstatus + '" class="input_select">\
                <option value="F"' + ( l_row.gdstatus == 'F' ? ' selected' : '' ) + '>매칭실패 (불일치)</option>\
                <option value="S"' + ( l_row.gdstatus == 'S' ? ' selected' : '' ) +'>매칭실패 (동명이인)</option>\
                <option value="A"' + ( l_row.gdstatus == 'A' ? ' selected' : '' ) +'>관리자입금확인완료</option>\
                <option value="U"' + ( l_row.gdstatus == 'U' ? ' selected' : '' ) +'>관리자미확인</option>\
                </select>';
                $('select', $(newTd)).change(function(){
                    barColorChg(this);
                });
            } else if( l_row.gdstatus == 'M') {
                newTd.innerHTML = '<span><b>' + (gdstatusNm[l_row.gdstatus] != undefined ? gdstatusNm[l_row.gdstatus] + (manualControllerManageId[l_row.bkcode] !=undefined ? ' (by ' + manualControllerManageId[l_row.bkcode] + ')' : '')  : '확인전') + '</b></span>';
                newTd.innerHTML += '<input type="hidden" name="gdstatus" value="' + l_row.gdstatus + '">';
            } else {
                newTd.innerHTML = '<span style="color:#EA0095"><b>' + (gdstatusNm[l_row.gdstatus] != undefined ? gdstatusNm[l_row.gdstatus] : '확인전') + '</b></span>';
            }

            newTd = newTr.insertCell(-1);
            $(newTd).addClass('date');
            if ( l_row.gddatetime.substr(0,8) != '' ) {
                if ( l_row.gdstatus == 'M') {
                    $(newTd).addClass('date_' + l_row.bkcode);
                }
                dt = l_row.gddatetime.substr(2,2) + '-' + l_row.gddatetime.substr(4,2) + '-' + l_row.gddatetime.substr(6,2) + ' ' + l_row.gddatetime.substr(8,2) + ':' + l_row.gddatetime.substr(10,2);
            } else {
                dt = '';
            }

            if ( (l_row.gdstatus == 'T' || l_row.gdstatus == 'M') && l_row.gddatetime.substr(0,8) != '' ) {
                newTd.innerHTML = dt;
            } else if ( l_row.gdstatus == 'F' || l_row.gdstatus == 'S' || l_row.gdstatus == 'A' || l_row.gdstatus == 'U' ) {
                newTd.innerHTML = '<input type="text" name="gddatetime" value="' + dt + '" valued="' + dt + '"  class="form-control width90p"/>';
                $('input', $(newTd)).blur(function(){
                    chkDateFormat(this);
                    barColorChg(this);
                }).dblclick(function(){
                    setToday(this);
                });
            } else {
                newTd.innerHTML = '―';
            }

            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            if (( l_row.gdstatus == 'T' || l_row.gdstatus == 'B' ) && l_row.bkmemo4 !='') {
                var orderNoTag = '<a onclick="order_view_popup(\'' + l_row.bkmemo4 + '\');"><span style="color:#0074BA"><b>' + l_row.bkmemo4 + '</b></span></a>';
                if (l_row.gdstatus == 'B') {
                    orderNoTag += '&nbsp;<button type="button" name="btnEditOrderNo" class="btn btn-black btn-xs" data-code="' + l_row.bkcode +'" data-name="' + l_row.bkjukyo + '" data-price="' + l_row.bkinput.number_format() +'" data-order-no="' + l_row.bkmemo4  + '" data-date="' + dt +'">수정</button>';
                }
                newTd.innerHTML = orderNoTag;
                $('button', $(newTd)).click(function(){
                    modify_bankda_orderno($(this), l_row.gdstatus);
                });
            } else if ( l_row.gdstatus == 'F' || l_row.gdstatus == 'S' || l_row.gdstatus == 'A' || l_row.gdstatus == 'U' ) {
                var orderMemoCnt = l_row.bkmemo4.split('||').length;
                if(orderMemoCnt > 1) {
                    var orderMemoSplit = l_row.bkmemo4.split('||').join('<br>');
                    orderMemoSplit = orderMemoSplit.replace(/<br>/g, '\n');
                    var orderNoTag = '<textarea name="bkmemo4" class="form-control width98p" rows="' + orderMemoCnt + '" valued="' + orderMemoSplit + '">' + orderMemoSplit + '</textarea>';
                    newTd.innerHTML = orderNoTag;
                } else {
                    newTd.innerHTML = '<input type="text" name="bkmemo4" value="' + l_row.bkmemo4 + '" valued="' + l_row.bkmemo4 + '" class="form-control width90p"/>';
                }
                $('input', $(newTd)).blur(function(){
                    barColorChg(this);
                });
            } else if ( l_row.gdstatus == 'M') { // 수동매칭
                var orderMemoSplit	= l_row.bkmemo4.split('||');
                var orderMemoSplitMerge = '';
                for(i=0; i < orderMemoSplit.length; i++) {
                    orderMemoSplitMerge += '<a onclick="order_view_popup(\'' + orderMemoSplit[i] + '\');"><span style="color:#0074BA;cursor:pointer;"><b>' + orderMemoSplit[i] + '</b></span></a><br>';
                }
                var orderNoTag = '<div style="display:table;height:100%;"><div class="left">' + orderMemoSplitMerge + '</div>';
                orderNoTag += '<div style="display:table-cell;vertical-align:middle;padding-left: 10px;" class="right"><button type="button" name="btnEditOrderNo" class="btn btn-black btn-xs" data-code="' + l_row.bkcode +'" data-name="' + l_row.bkjukyo + '" data-price="' + l_row.bkinput.number_format() +'" data-order-no="' + l_row.bkmemo4 + '" data-date="' + dt +'">수정</button></div></div>';
                newTd.innerHTML = orderNoTag;
                $('button', $(newTd)).click(function(){
                    modify_bankda_orderno($(this), 'M');
                });
            } else {
                newTd.innerHTML = '―';
            }
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            if (l_row.gdstatus == 'N') {
                newTd.innerHTML += '-';
            } else {
                if(adminMemoData[l_row.bkcode] != 'undefined' && adminMemoData[l_row.bkcode]) {
                    var btnColor = 'btn-gray';
                    var dataContents = String(adminMemoData[l_row.bkcode]).replace(/\r\n/g, '<br>').replace(/\n/g, '<br>');
                    var dataTitle = '입금내역메모';
                } else {
                    var btnColor = 'btn-white';
                    var dataContents = '';
                    var dataTitle ='';
                }
                newTd.innerHTML += '<input type="button" value="메모" class="btn btn-sm ' + btnColor + ' js-html-popover js-bankda-admin-memo" data-bankda-no="' + l_row.bkcode + '" data-original-title="' + dataTitle + '" data-placement="left" data-content="' + dataContents + '" onclick="bankdaAdminMemoLayer(' + l_row.bkcode + ');">';
            }
        }

        if(!len) func_list_msg('<span style="color:#FF6600; font-weight:bold; font-size:13px;">검색된 정보가 없습니다.</span>');
    }
    /**
     * 리스팅 메시지출력
     * @param string msg 메시지
     */
    var func_list_msg = function(msg) {

        if (listEle == null) return;

        newTr = listEle.get(0).insertRow(-1);
        newTr.align = 'center';

        newTd = newTr.insertCell(-1);
        newTd.style.padding='20px 0 20px 0';
        newTd.colSpan = 12;
        newTd.innerHTML = msg;
    }

    // AJAX 실행
    $.ajax({
        type: 'GET'
        , url: './bankda_match_ps.php'
        , data: 'mode=accountList&'+query+'&dummy='+new Date().getTime()
        , async: false
        , success: function(response) {

            var jsonData = eval('(' + response + ')');
            func_list_init();

            // 리스팅 실행
            try {
                func_listing(jsonData.lists);
            }
            catch(err) {
                func_list_msg('<span style="color:#FF6600; font-weight:bold; font-size:13px;">검색된 정보가 없습니다.</span>');
                return;
            }

            // 페이징정보 출력
            try {
                if (pageRtotalEle != null) {pageRtotalEle.text(jsonData.page['rtotal']);}
                if (pageRecodeEle != null) {pageRecodeEle.text(jsonData.page['recode']);}
                if (pageNowEle != null) {pageNowEle.text(jsonData.page['now']);}
                if (pageTotalEle != null) {pageTotalEle.text(jsonData.page['total']);}
                if (pageNaviEle != null) {
                    var navi = jsonData.page['navi'];
                    var len = navi[0].length;
                    for (i = 0; i < len; i++) {
                        if (navi[0][i] == '') { // 현재 페이지번호
                            pageNaviEle.append('<li class="active"><a href="#">' + navi[1][i] + '</a></li>');
                        } else {  // 이동할 페이지번호

                            navi[1][i] = navi[1][i].replace("[","");
                            navi[1][i] = navi[1][i].replace("]","");

                            $('<li ref="' + navi[0][i] + '"><a href="#">' + navi[1][i] + '</a></li>').click(function() {
                                account_list($(this).attr('ref'));
                            }).appendTo(pageNaviEle);
                            pageNaviEle.append('&nbsp;');
                        }
                    }
                    if($('input[name="page"]').val() > jsonData.page['total']) { // 기존 이동된 페이지보다 검색토탈이 작은 경우 1페이지로 강제 이동
                        $('input[name="page"]').val('1');
                        $('#page_navi li:first').click();
                    }
                    $('input[name="page"]').val(jsonData.page['now']); //선택 페이지 값 삽입

                    $('.js-bankda-admin-memo').popover({
                        trigger: 'hover',
                        container: '#content',
                        html: true
                    });

                    var popover = $('.js-bankda-admin-memo').attr('data-content', $('textarea[name=adminMemo]').val().replace(/\r\n/g,'<br>').replace(/\n/g,'<br>'));
                    popover.data('bs.popover').setContent();
                }
            }
            catch(err){
                return;
            }
        }
        , error: function(XMLHttpRequest, textStatus, errorThrown) {
            //alert(XMLHttpRequest.responseText);
            func_list_init();
            func_list_msg('<span style="color:#FF6600; font-weight:bold; font-size:13px;">검색된 정보가 없습니다.</span>');
            return;
        }
        , complete: function() {}
    });
}

function bank_matching() {

    /**
     * 브리핑 출력
     * @param string str 브리핑내용
     * @param bool emtpy ?
     * @param string color 색상
     */
    var briefing = function (str, emtpy, color) {
        if (emtpy == true) {
            $('.process .report .briefing ul').empty();
        }
        var li = $('<li>' + str + '</li>').appendTo($('.process .report .briefing ul'));

        if (color != '') {
            li.css({'color': color});
        }
    }

    // 실행시작 메시지
    briefing('실시간입금확인을 시작합니다.', true);
    briefing('비교(Matching) 작업 진행 중입니다. <span style="color:#DF6600;">(진행 중에는 창을 닫지 마세요.)</span>');

    // Create Query
    var query = $('input[name=\'bkdate[]\']').serialize();

    // AJAX 실행
    $.ajax({
        type: 'GET'
        , url: './bankda_match_ps.php'
        , data: 'mode=bankMatching&' + query + '&dummy=' + new Date().getTime()
        , async: false
        , global: false
        , success: function (response) {

            // 결과 메시지 브리핑
            var msg = response.split('^'); // [0] 결과 메시지, [1] 데이터
            var remsg = msg[0];

            try {

                var datas = msg[1].split('|'); // [..] 매칭된 주문번호들

                remsg += '<div class="sub">총 ' + datas.length + ' 건의 주문이 입금확인 되었습니다.</div>';

                for (i = 0; i < datas.length; i++) {
                    if (i == 0) remsg += '<ol type="1" class="sub">';
                    remsg += '<li>주문번호 : ' + datas[i] + '</li>';
                    if (i > 0 && (i + 1) == datas.length) remsg += '</ol>';
                }
            }
            catch (err) {
                remsg += '<div class="sub" style="color:red;">입금확인된 주문건이 없습니다.</div>';
            }
            briefing(remsg);

            $('.process .report .report_btn #btnReport').click(function () {
                account_list();
                layer_close();
            });
        }
        , error: function (XMLHttpRequest, textStatus, errorThrown) {
            if (XMLHttpRequest.status == '404' && XMLHttpRequest.statusText == 'Not Found') {
                briefing('[Not Found]\nThe requested URL was not found.');
            } else  {
                // 결과 메시지 브리핑
                var remsg = '';
                var tmp = XMLHttpRequest.responseText.split('^'); // [0] 결과 메시지, [..] 오류 메시지
                for (i = 0; i < tmp.length; i++) {
                    if (i == 1) {
                        remsg += '<ol type="1" class="sub">';
                    }
                    if (i == 0) {
                        remsg += tmp[i];
                    } else {
                        remsg += '<li>' + tmp[i] + '</li>';
                    }
                    if (i > 0 && (i + 1) == tmp.length) {
                        remsg += '</ol>';
                    }
                }
                briefing(remsg, true, 'red');
                $('.process .report .report_btn #btnReport').click(function () {
                    layer_close();
                });
            }
        }
        , complete: function () {
            // 실행종료 메시지
            briefing('실시간입금확인이 종료되었습니다.');
            $('.process .report .report_btn').show();
        }
    });
}

/**
 * 오늘날짜 입력(yy-mm-dd hh:ii)
 */
function setToday(tObj) {
    var now = new Date();
    var year = now.getFullYear().toString().substring(2,4);

    var month = now.getMonth() + 1;
    if (month.toString().length == 1) month = '0' + month.toString();

    var day = now.getDate();
    if (day.toString().length == 1) day = '0' + day.toString();

    var hours = now.getHours();
    if (hours.toString().length == 1) hours = '0' + hours.toString();

    var minutes = now.getMinutes();
    if (minutes.toString().length == 1) minutes = '0' + minutes.toString();

    tObj.value = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes;
}

/**
 * 최종 매칭일 형식 검증
 */
function chkDateFormat(tObj) {
    if (tObj.value == '') return;
    if (tObj.value.match(/^([0-9]{2})-[0-9]{2}-[0-9]{2} [0-9]{2}:([0-9]{2})$/) != null) return;
    alert('날짜는 YY-MM-DD HH:SS 형식으로 입력하셔야 합니다.');
    tObj.value = tObj.getAttribute('valued');
}

/**
 * 입금내역 변경된 레코드 배경색상 변경
 */
function barColorChg(Obj) {
    var trObj = Obj.parentNode.parentNode;
    if (Obj.value != Obj.getAttribute('valued')) {
        trObj.setAttribute('updateItems', trObj.getAttribute('updateItems') + Obj.name);
    } else {
        trObj.setAttribute('updateItems', trObj.getAttribute('updateItems').replace(Obj.name, '') );
    }
    if (trObj.getAttribute('updateItems') != '') trObj.style.backgroundColor = '#dddddd';
    else trObj.style.backgroundColor = '#ffffff';
}

/**
 *실시간 입금 확인 실행
 */
function layer_open_bank_matching() {

    $.ajax({
        url: './layer_bankda_matching.php',
        type: 'get',
        async: false,
        success: function (data) {
            BootstrapDialog.show({
                nl2br: false,
                title: '실시간 입금확인',
                size: BootstrapDialog.SIZE_NORMAL,
                message: data,
                closable: false,

            });
        }
    });
}

/**
 *입금내역 일괄 수정 실행
 */
function layer_open_batch_update(titleType) {
    if(titleType !='manualBatchUpdate')  {
        var titleName = '입금내역 일괄수정';
    } else {
        var titleName = '입금내역 수동매칭';
    }
    $.ajax({
        url: './layer_bankda_batch_update.php?mode=' + titleType,
        type: 'get',
        async: false,
        success: function (data) {
            BootstrapDialog.show({
                nl2br: false,
                title: titleName,
                size: BootstrapDialog.SIZE_NORMAL,
                message: data,
                closable: false,

            });
        }
    });
}

/*** 입금내역 일괄수정 클래스 ***/
batchUpdate =  {
    /**
     * 일괄수정
     */
    begin: function() {
        AGM.act({'onStart' : this.startCallback, 'onRequest' : this.requestCallback, 'onCloseBtn' : 0, 'onErrorCallback' : 0});
    }

    /**
     * 그래프시작 콜백(정의)
     * @param object grp 그래프 Object
     */
    , startCallback: function(grp) {
        grp.layoutTitle = '입금내역 일괄수정중 ...';
        grp.bMsg['chkEmpty'] = '수정할 입금내역이 없습니다.';
        grp.bMsg['chkCount'] = '총 __count__개의 입금내역 수정을 요청하셨습니다.';
        grp.bMsg['start'] = '입금내역 수정을 시작합니다.';
        grp.bMsg['end'] = '입금내역 수정이 종료되었습니다.';

        grp.articles = new Array();
        grp.iobj = $('input[name=bkcode]');
        grp.iobj.each(function(idx) {
            $(this).parents('tr').first().find(':input:not(input[name=bkcode])').each(function() {
                if ($(this).val() != $(this).attr('valued')) {
                    grp.articles.push(idx);
                    return false;
                }
            });
        });
    }

    /**
     * 처리요청 콜백
     */
    , requestCallback: function(grp, idx) {
        // Create Query
        var query = grp.iobj.eq(idx).parents('tr').first().find(':input').serialize();

        // AJAX 실행
        $.ajax({
            type: 'GET'
            , url: './bankda_match_ps.php'
            , data: 'mode=bankUpdate&'+query+'&dummy='+new Date().getTime()
            , async: false
            , global: false
            , success: function(response) {
                grp.iobj.eq(idx).parents('tr').css('background','#ffffff');
                grp.complete(response);
            }
            , error: function(XMLHttpRequest, textStatus, errorThrown) {
                grp.error(XMLHttpRequest);
            }
        });
    }
}

/**
 * 주문번호 수정
 */
function modify_bankda_orderno(obj, status) {
    var addHtml = '';

    addHtml += '<input type="hidden" name="gdstatus" value="A">';
    addHtml += '<input type="hidden" name="bkcode" value="' + $(obj).data('code') + '" subject="' + $(obj).data('name') + ' (' + $(obj).data('price') + ')"/>';
    if(status == 'M') {
        var orderSplit = $(obj).data('order-no').toString().split('||');
        if(orderSplit.length > 1) {
            var orderMemo = $(obj).data('order-no').split('||').join('<br>');
        } else {
            var orderMemo = $(obj).data('order-no').toString();
        }
        orderMemo = orderMemo.replace(/<br>/g, '\n');
        addHtml += '<textarea name="bkmemo4" class="form-control width98p" rows="6" valued="' + orderMemo + '">'+ orderMemo + '</textarea>';
        var dateInput = '<input type="text" name="gddatetime" value="' + $(obj).data('date') + '" valued="' + $(obj).data('date') + '" class="form-control width90p">';
        $('.date_' + $(obj).data('code')).html(dateInput);
    } else {
        addHtml += '<input type="text" name="bkmemo4" value="' + $(obj).data('order-no') + '" class="form-control width90p"/>'
    }

    $(obj).closest('td').html(addHtml);
}

/*
 * 뱅크다수동 매칭 관리자 정보 배열 추출
 */
function bankManualManagerInfo(bankdaData) {
    // AJAX 실행
    var params = {
        mode: 'bankManualManagerInfo',
        bankdaData : bankdaData
    };
    return $.ajax({
        type: 'POST'
        , url: './bankda_match_ps.php'
        , data: params
        , async: false
        , global: false
        , success: function(response) {
        }
        , error: function(XMLHttpRequest, textStatus, errorThrown) {
        }
    });
}

/*
 * 뱅크다입금내역 메모 보기 - 리스팅 좌측 레이어
 */
function bankAdminMemoArraySelect(bankdaData) {
    // AJAX 실행
    var params = {
        mode: 'bankAdminMemoArraySelect',
        bankdaData : bankdaData
    };
    return $.ajax({
        type: 'POST'
        , url: './bankda_match_ps.php'
        , data: params
        , async: false
        , global: false
        , success: function(response) {
        }
        , error: function(XMLHttpRequest, textStatus, errorThrown) {
        }
    });
}
/*
 * 뱅크다입금내역 메모 보기 - 클릭 오픈 레이어
 */
function bankdaAdminMemoLayer(bankdaNo) {
    var params = {
        bankdaNo: bankdaNo
    };
    $.post('layer_bankda_admin_memo.php', params, function (data) {
        layer_popup(data, '입금내역 메모');
    });
}