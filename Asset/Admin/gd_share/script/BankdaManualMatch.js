/**
 * 뱅크다 수동매칭 관련 스크립트
 *
 * @copyright Copyright (c), Godosoft
 */

//임금내역 주문서 수동매칭
function account_unmaching_list(query) {
    listEle = $('#list_form');
    pageRtotalEle = $('#page_rtotal');
    pageRecodeEle = $('#page_recode');
    pageNaviEle = $('#page_navi');

    // Create Query
    if(query == undefined) {
        var query = $('#frmBankSearchBase').serialize();
    } else if(query != '') {
        $('#frmBankSearchBase input[name="query"]').val(query);
        query = $.param(query.parse_str());
    }

    var func_list_init = function () {
        if(listEle != null) {
            while(listEle.get(0).rows.length > 1) {
                listEle.get(0).deleteRow(listEle.get(0).rows.length - 1); // 결과 rows 초기화
            }
        }

        pageRtotalEle.text(0);
        pageRecodeEle.text(0);
        pageNaviEle.text('');
    }

    //입금내역 주문서 리스트 생성
    var func_listing = function (lists) {

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
        for(n = 0; n < len; n++) {
            l_row = lists[n];

            newTr = listEle.get(0).insertRow(-1);
            newTr.height = '25';
            newTr.align = 'center';
            newTr.bgcolor = '#ffffff';
            newTr.bg = '#ffffff';
            newTr.setAttribute('updateItems', '');

            //선택박스
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            if(l_row.gdstatus == 'F' || l_row.gdstatus == 'S' || l_row.gdstatus == 'U') {
                newTd.innerHTML = '<input type="checkbox" name="bkcode" value="' + l_row.bkcode + '" subject="' + l_row.bkjukyo + ' (' + l_row.bkinput.number_format() + ')"/>';
            }

            //번호
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            newTd.innerHTML = l_row.no;

            //입금일
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('date');
            newTd.innerHTML = l_row.bkdate.substr(2, 2) + '-' + l_row.bkdate.substr(4, 2) + '-' + l_row.bkdate.substr(6, 2);

            //계좌번호
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            newTd.innerHTML = l_row.bkname + '<br/>';
            newTd.innerHTML += '<span style="color:#0074BA">' + l_row.bkacctno + '</span>';

            //입금금액
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            newTd.innerHTML = '<b>' + l_row.bkinput.number_format() + '</b>';

            //임금자명
            newTd = newTr.insertCell(-1);
            newTd.innerHTML = l_row.bkjukyo;

            //현재상태
            newTd = newTr.insertCell(-1);
            if(l_row.gdstatus == 'M') {
                newTd.innerHTML = (gdstatusNm[l_row.gdstatus] != undefined ? gdstatusNm[l_row.gdstatus] + (manualControllerManageId[l_row.bkcode] != undefined ? ' (by ' + manualControllerManageId[l_row.bkcode] + ' ) ' : '') : '확인전') + '<br>';
            } else if(l_row.gdstatus != undefined) {
                newTd.innerHTML = (gdstatusNm[l_row.gdstatus] != undefined ? gdstatusNm[l_row.gdstatus] : '확인전') + '<br>';
            } else {
                //newTd.innerHTML += '-';
            }

            //관리자 메모
            if(l_row.gdstatus == 'N') {
                newTd.innerHTML += '-';
            } else {
                if(adminMemoData[l_row.bkcode] != 'undefined' && adminMemoData[l_row.bkcode]) {
                    var btnColor = 'btn-gray';
                    var dataContents = String(adminMemoData[l_row.bkcode]).replace(/\r\n/g, '<br>').replace(/\n/g, '<br>');
                    var dataTitle = '입금내역메모';
                } else {
                    var btnColor = 'btn-white';
                    var dataContents = '';
                    var dataTitle = '';
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
    var func_list_msg = function (msg) {

        if(listEle == null) return;

        newTr = listEle.get(0).insertRow(-1);
        newTr.align = 'center';

        newTd = newTr.insertCell(-1);
        newTd.style.padding = '20px 0 20px 0';
        newTd.colSpan = 12;
        newTd.innerHTML = msg;
    }


    // 뱅크다 매뉴얼 메칭 상태 가져오기
    $.ajax({
        type: 'GET'
        , url: './bankda_match_ps.php'
        , data: 'mode=accountList&' + query + '&dummy=' + new Date().getTime()
        , async: false
        , success: function (response) {

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
                if(pageRtotalEle != null) {
                    pageRtotalEle.text(jsonData.page['rtotal']);
                }
                if(pageRecodeEle != null) {
                    pageRecodeEle.text(jsonData.page['recode']);
                }
                if(pageNaviEle != null) {
                    var navi = jsonData.page['navi'];
                    var len = navi[0].length;
                    for(i = 0; i < len; i++) {
                        if(navi[0][i] == '') { // 현재 페이지번호
                            pageNaviEle.append('<li class="active"><a href="#">' + navi[1][i] + '</a></li>');
                        } else {  // 이동할 페이지번호

                            navi[1][i] = navi[1][i].replace("[", "");
                            navi[1][i] = navi[1][i].replace("]", "");

                            $('<li ref="' + navi[0][i] + '"><a href="#">' + navi[1][i] + '</a></li>').click(function () {
                                account_unmaching_list($(this).attr('ref'));
                            }).appendTo(pageNaviEle);
                            pageNaviEle.append('&nbsp;');
                        }
                    }

                    $('.js-bankda-admin-memo').popover({
                        trigger: 'hover',
                        container: '#content',
                        html: true
                    });

                    var popover = $('.js-bankda-admin-memo').attr('data-content', $('textarea[name=adminMemo]').val().replace(/\r\n/g, '<br>').replace(/\n/g, '<br>'));
                    popover.data('bs.popover').setContent();
                }
            }
            catch(err) {
                return;
            }
        }
        , error: function (XMLHttpRequest, textStatus, errorThrown) {
            //alert(XMLHttpRequest.responseText);
            func_list_init();
            func_list_msg('<span style="color:#FF6600; font-weight:bold; font-size:13px;">검색된 정보가 없습니다.</span>');
            return;
        }
        , complete: function () {
        }
    });

}

//입금대기 주문 - 솔루션
function manualMatchOrderList(query) {
    listEle = $('#ord_list_form');
    pageRtotalEle = $('#ord_page_rtotal');
    pageRecodeEle = $('#ord_page_recode');
    pageNaviEle = $('#ord_page_navi');

    // Create Query
    if(query == undefined) {
        var query = $('#frmOrdSearchBase').serialize();
    }
    var func_list_init = function () {
        if(listEle != null) {
            while(listEle.get(0).rows.length > 1) {
                listEle.get(0).deleteRow(listEle.get(0).rows.length - 1); // 결과 rows 초기화
            }
        }

        pageRtotalEle.text(0);
        pageRecodeEle.text(0);
        pageNaviEle.text('');
    }

    //입금내역 주문서 리스트 생성
    var func_listing = function (lists, page) {

        var len = lists.length;
        for(n = 0; n < len; n++) {
            l_row = lists[n];

            newTr = listEle.get(0).insertRow(-1);
            newTr.style.height = '57px';
            newTr.align = 'center';

            //선택박스
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            if(l_row.manualMatchLimit == 'limit')  {
                checkLimit = 'disabled';
            } else {
                checkLimit = '';
            }
            newTd.innerHTML = '<input type="checkbox" name="statusCheck[]" value="' + l_row.orderNo + '" ' + checkLimit + '>';

            //번호
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            newTd.innerHTML = l_row.pageIdx;

            //주문일시
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('date');
            newTd.innerHTML = l_row.regDt;

            //주문번호
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');
            newTd.innerHTML = '<a onclick="order_view_popup(\'' + l_row.orderNo + '\');"><span style="color:#0074BA" class="hand">' + l_row.orderNo + '</span></a>';

            //실결제금액
            newTd = newTr.insertCell(-1);
            $(newTd).addClass('number');

            newTd.innerHTML = '<b>' + l_row.settlePrice.number_format().replace('.00', '') + '원</b>';

            //임금자명
            newTd = newTr.insertCell(-1);
            newTd.innerHTML = l_row.bankSender;

            //현재상태
            newTd = newTr.insertCell(-1);
            if(l_row.manualMatchLimit == 'limit') {
                checkString = '<br>(부분취소)';
            } else {
                checkString = '';
            }
            newTd.innerHTML = l_row.orderStatusStr + checkString;
        }
        if(!len) func_list_msg('<span class="no-data" style="line-height: 0 !important;">검색된 주문이 없습니다.</span>');
    }

    /**
     * 리스팅 메시지출력
     * @param string msg 메시지
     */
    var func_list_msg = function (msg) {

        if(listEle == null) return;

        newTr = listEle.get(0).insertRow(-1);
        newTr.align = 'center';

        newTd = newTr.insertCell(-1);
        newTd.style.padding = '20px 0 20px 0';
        newTd.colSpan = 12;
        newTd.innerHTML = msg;
    }


    // 뱅크다 매뉴얼 메칭 상태 가져오기
    $.ajax({
        type: 'GET'
        , url: './bankda_match_ps.php'
        , data: 'mode=bankManualOrderList&' + query
        , async: false
        , success: function (response) {

            var jsonData = eval('(' + response + ')');
            func_list_init();

            // 리스팅 실행
            try {
                func_listing(jsonData.lists, jsonData.page);
            }
            catch(err) {
                if(pageRtotalEle != null) {
                    pageRtotalEle.text(jsonData.page['rtotal']);
                }
                func_list_msg('<span class="no-data" style="line-height: 0 !important;">검색된 주문이 없습니다.</span>');
                return;
            }

            // 페이징정보 출력
            try {
                if(pageRtotalEle != null) {
                    pageRtotalEle.text(jsonData.page['rtotal']);
                }
                if(pageRecodeEle != null) {
                    pageRecodeEle.text(jsonData.page['recode']);
                }
                if(pageNaviEle != null) {
                    var navi = jsonData.page['navi'];
                    var len = navi[0].length;
                    pageNaviEle.html(navi);
                }
            }
            catch(err) {
                return;
            }
        }
        , error: function (XMLHttpRequest, textStatus, errorThrown) {
            //alert(XMLHttpRequest.responseText);
            func_list_init();
            func_list_msg('<span class="no-data" style="line-height: 0 !important;">검색된 주문이 없습니다.</span>');
            return;
        }
        , complete: function () {
        }
    });

}

/*** 수동매칭 일괄수정 클래스 ***/
manualBatchUpdate = {
    /**
     * 일괄수정
     */
    begin: function () {
        AGM.act({'onStart': this.startCallback, 'onRequest': this.requestCallback, 'onCloseBtn': 0, 'onErrorCallback': 0});
    }

    /**
     * 그래프시작 콜백(정의)
     * @param object grp 그래프 Object
     */
    , startCallback: function (grp) {
        grp.layoutTitle = '수동매칭 일괄수정중 ...';
        grp.bMsg['chkEmpty'] = '수정할 입금내역이 없습니다.';
        grp.bMsg['chkCount'] = '총 __count__개의 수동매칭 수정을 요청하셨습니다.';
        grp.bMsg['start'] = '수동매칭 수정을 시작합니다.';
        grp.bMsg['end'] = '수동매칭 수정이 종료되었습니다.';

        grp.articles = new Array();
        grp.iobj = $('input[name=bkcode]:checked');
        grp.iobj.each(function (idx) {
            $(this).parents('tr').first().find(':input:not(input[name=bkcode])').each(function () {
                if($(this).val() != $(this).attr('valued')) {
                    grp.articles.push(idx);
                    return false;
                }
            });
        });
    }

    /**
     * 처리요청 콜백
     */
    , requestCallback: function (grp, idx) {
        // Create Query
        var query = grp.iobj.eq(idx).parents('tr').first().find(':input').serialize();

        // AJAX 실행
        $.ajax({
            type: 'GET'
            , url: './bankda_match_ps.php'
            , data: 'mode=bankUpdate&' + query + '&dummy=' + new Date().getTime()
            , async: false
            , global: false
            , success: function (response) {
                grp.iobj.eq(idx).parents('tr').css('background', '#ffffff');
                grp.complete(response);
            }
            , error: function (XMLHttpRequest, textStatus, errorThrown) {
                grp.error(XMLHttpRequest);
            }
        });
    }
}

/**
 * 최종 매칭일 형식 검증
 */
function chkDateFormat(tObj) {
    if(tObj.value == '') return;
    if(tObj.value.match(/^([0-9]{2})-[0-9]{2}-[0-9]{2} [0-9]{2}:([0-9]{2})$/) != null) return;
    alert('날짜는 YY-MM-DD HH:SS 형식으로 입력하셔야 합니다.');
    tObj.value = tObj.getAttribute('valued');
}