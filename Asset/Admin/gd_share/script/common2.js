/**
 * common js
 *
 * @author artherot, sunny
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */

$(document).ready(function()
{
    /**
     * 움직이는 스크롤배너
     *
     * @author sunny
     */
    $(window).load(function()
    {
        if (self.name != 'easy') {
            if ($('.layout_leftscroll').length == 1 || $('.layout_rightscroll').length == 1) {
                var saLeftTop = saRightTop = 0;
                if ($('.layout_leftscroll').css('top') != null && $('.layout_leftscroll').css('top') != 'auto') {
                    saLeftTop = eval($('.layout_leftscroll').css('top').replace(/px/, ''));
                }
                if ($('.layout_rightscroll').css('top') != null && $('.layout_rightscroll').css('top') != 'auto') {
                    saRightTop = eval($('.layout_rightscroll').css('top').replace(/px/, ''));
                }
                $(window).scroll(function()
                {
                    // 좌측스크롤
                    var scrolledLeftTop = (Math.max(document.documentElement.scrollTop, document.body.scrollTop));
                    scrolledLeftTop += (scrolledLeftTop < 10 ? saLeftTop : 10);
                    $('.layout_leftscroll').animate({
                        'top': scrolledLeftTop
                    }, {
                        duration: 'slow',
                        easing: 'swing',
                        queue: false
                    });
                    // 우측스크롤
                    var scrolledRightTop = (Math.max(document.documentElement.scrollTop, document.body.scrollTop));
                    scrolledRightTop += (scrolledRightTop < 10 ? saRightTop : 10);
                    $('.layout_rightscroll').animate({
                        'top': scrolledRightTop
                    }, {
                        duration: 'slow',
                        easing: 'swing',
                        queue: false
                    });
                });
            }
        }
    });
});

/**
 * 관리자에서 모든 페이지의 input 에서  enter 로 submit 실행 되지 않게 처리
 */
$(document).on("keypress","input", function(event) {
    if ($(this).closest("form").attr('id') != 'frmLogin' && $(this).closest("form").hasClass('js-form-enter-submit') != true) { // 관리자 로그인 폼은 제외 or 검색 등 submit 를 위한 js-form-enter-submit 는 제외
        if (event.which == 13) { // enter 키
            event.preventDefault();
            return false;
        }
    }
});

/**
 * 자바스크립트 Trim함수
 *
 * @return string 좌우 공백 제거한 문자열
 */
String.prototype.trim = function()
{
    return this.replace(/^\s+|\s+$/, '');
};

/**
 * 자바스크립트 number_format 함수
 *
 * @return string 문자열을 세자리 단위로 쉼표 찍기
 */
String.prototype.number_format = function()
{
    return this.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
};

/**
 * 스트링 camel 케이스로 변경
 * @returns {*}
 */
String.prototype.toCamelCase = function () {
    switch (this.length) {
        case 0:
        {
            return '';
            break;
        }
        case 1:
        {
            return this.toUpperCase();
            break;
        }
        default:
        {
            return (this.substring(0, 1).toUpperCase() + this.substring(1));
            break;
        }
    }
}
/**
 * 문자열 Parse
 *
 * @author sunny
 */
String.prototype.parse_str = function()
{
    var arr = {};
    var tmp = this.split('&');
    $.each(tmp, function()
    {
        var tmp2 = this.split('=');
        arr[tmp2[0]] = tmp2[1];
    });
    return arr;
};

/**
 * 문자열 자르기
 */
String.prototype.str_cut = function(max_length, isDot)
{
    var pattern = /([^가-힣\x20])/;
    var count = 0;
    var tmp;
    var dot= '';
    if(typeof isDot == 'undefined') {
        isDot = false;
    }

    for (var i = 0; i < this.length; i++){
        tmp = this.charAt(i);
        if(tmp == ' '){
            count++;
        }
        else if (!pattern.test(tmp)){
            count += 2;
        } else {
            count++;
        }

        if(count > max_length) {
            if(isDot) {
                dot = '...';
            }
            break;
        }
    }
    return this.substring(0, i)+dot;
}

/**
 * 리스트 이동
 *
 * @param integer step 이동 갯수 (1, -1)
 */
jQuery.fn.moveRow = function (step) {
    var thisTable = this.parent();
    var tableHeight = thisTable.parent().height();
    var selectZone = thisTable.find('>tr');
    var selectIndex = thisTable.data('index');
    if(selectIndex== undefined) selectIndex = ".move-row";
    var startIndex = 0;
    var endIndex = 0;
    var idx = 0;
    if (step < 0) {
        var useBefore = true;
    }

    $(selectZone).each(function () {
        var obj = $(this);
        if (obj.hasClass('warning')) {
            if (startIndex == 0) {
                startIndex = (obj.index(selectIndex) + 1);
            }
            endIndex = (obj.index(selectIndex) + 1);
        }
    });

    // 위로 이동시
    if (step < 0) {
        if (startIndex != 1) {
            var targetZone = (step < -1 ? 0 : startIndex - 2);
            for (var i = startIndex; i <= endIndex; i++) {
                idx = i - 1;
                if(selectZone.eq(idx).hasClass('warning')) {
                    if(step=='-100') targetZone = 0;
                    selectZone.eq(idx)['insert' + (useBefore && 'Before' || 'After')](selectZone.eq(targetZone));
                } else {
                    targetZone = idx;
                }
            }
        }
        // 아래로 이동시
    } else {
        var targetZone = (step > 1 ? selectZone.length - 1 : endIndex);
        for (var i = endIndex; i >= startIndex; i--) {
            idx = i - 1;
            if(selectZone.eq(idx).hasClass('warning')) {
                if(step=='100') targetZone = selectZone.length-1;
                selectZone.eq(idx)['insert' + (useBefore && 'Before' || 'After')](selectZone.eq(targetZone));
            } else {
                targetZone = idx;
            }
        }
    }

    thisTable.parent().height(tableHeight);
}

/**
 * show(block)
 *
 * @author sunny
 */
$.fn.showup = function()
{
    var docSize = parseInt($(document).width());
    if ($(this).css('position') == 'absolute') {
        $(this).css({
            'left': '10px',
            'right': 'auto'
        });
    }
    $(this).show();
    if ($(this).css('position') == 'absolute') {
        var offset = $(this).offset();
        var boxSize = parseInt(offset.left) + parseInt($(this).width());
        if (docSize <= boxSize) {
            $(this).css({
                'left': 'auto',
                'right': '10px'
            });
        }
    }
};

/**
 * 경고 UI
 * @param title
 * @param message
 * @param timeout
 * @param onClose
 */
$.warnUI = function(title, message, timeout, onClose)
{
    if (timeout == undefined) timeout = 2000;

    BootstrapDialog.show({
        type: BootstrapDialog.TYPE_WARNING,
        title: title,
        message: message,
        onshown: function(dialogRef){
            setTimeout(function(){
                onClose();
                dialogRef.close()
            }, timeout);
        }
    });
};

/**
 * 이미지뷰어창 호출
 *
 * @author sunny
 * @param string theURL 이미지경로
 * @return object Window 개체
 */
function image_viewer(theURL, width, height)
{
    var imageSize = '';
    if (typeof width != 'undefined') imageSize += ' width="' + width + '"';
    if (typeof height != 'undefined') imageSize += ' height="' + height + '"';
    var data = "<div style='text-align:center;width:970px;height:500px;overflow:auto;border:1px solid #efefef;'><img src='"+theURL+"' " + imageSize + "></div>";
    BootstrapDialog.show({
        title: '이미지 미리 보기',
        size: get_layer_size('wide-lg'),
        message: $(data)

    });
};

/**
 * PG 관련 영수증 보기 show_receipt.php는 전역에서 사용할 수 있는 PG 관련 영수증 정보 화일입니다.(고객용)
 *
 * @author artherot
 * @param string modeStr 카드, 현금영수증 종류 (card, cash)
 * @param string orderNo 주문 번호
 */
function pg_receipt_view(modeStr, orderNo)
{
    // 사이즈를 미리 설정 - 자동으로 창이 커지지 않아 미리 설정함
    var preWidth = 430;
    var preHeight = 700;

    // 미리 팝업창을 띄우기
    var prePopupData = {
        'url': 'about:blank',
        'target': 'show_receipt',
        'width': preWidth,
        'height': preHeight
    };
    var show_receipt = popup(prePopupData);

    // 각 PG별 영수증 팝업창
    $.post('/share/show_receipt.php', {
        mode: modeStr,
        orderNo: orderNo
    }, function(data)
    {
        var infoData = data;
        if (typeof infoData['error'] == 'undefined') {
            popup(infoData);
        }
        else {
            alert(infoData['error']);
            show_receipt.close();
        }
    }, 'json');
}

/**
 * 에스크로 구매확인 - 올더게이트용
 *
 * @author artherot
 */
function pg_escrow_confirm(escrowUrl, orderNo)
{
    // 사이즈를 미리 설정 - 자동으로 창이 커지지 않아 미리 설정함
    var preWidth = 430;
    var preHeight = 300;

    // 미리 팝업창을 띄우기
    var popupData = {
        'url': escrowUrl + '?orderNo=' + orderNo,
        'target': 'escrow_confirm',
        'width': preWidth,
        'height': preHeight
    };
    var show_confirm = popup(popupData);
}

/**
 * 세금계산서 보기
 *
 * @author artherot
 * @param string orderNo 주문 번호
 * @param string modeStr 공급자 공급받는자 구분
 */
function tax_invoice_view(orderNo, modeStr)
{
    var path = get_script_dirpath('module/script/common.js');

    win = popup({
        url: path + 'share/show_tax_invoice.php?orderNo=' + orderNo + '&modeStr=' + modeStr,
        target: 'tax_invoice',
        width: 750,
        height: 600,
        resizable: 'yes',
        scrollbars: 'yes'
    });
    win.focus();
    return win;
};

/**
 * 고도빌(전자 세금계산서) 보기
 *
 * @author artherot
 * @param string orderNo 주문 번호
 * @param string modeStr 공급자 공급받는자 구분
 */
function tax_godobill_view(godobillUrl)
{
    win = popup({
        url: godobillUrl,
        target: 'godobill_view',
        width: 830,
        height: 450,
        resizable: 'yes',
        scrollbars: 'yes'
    });
    win.focus();
    return win;
};

/**
 * 도로명 주소 찾기 (팝업)
 *
 * @author artherot
 * @param string zoneCodeID zonecode input ID
 * @param string addrID address input ID
 * @param string zipCodeID zipcode input ID
 */
function postcode_search(zoneCodeID, addrID, zipCodeID)
{
    win = popup({
        url: '/share/postcode_search.php?zoneCodeID=' + zoneCodeID + '&addrID=' + addrID + '&zipCodeID=' + zipCodeID,
        target: 'postcode',
        width: 500,
        height: 450,
        resizable: 'yes',
        scrollbars: 'yes'
    });
    win.focus();
    return win;
};

/**
 * 배송추적
 *
 * @author artherot
 * @param string invoiceCompanySno 택배사 코드
 * @param string invoiceNo 송장번호
 */
function delivery_trace(invoiceCompanySno, invoiceNo)
{
    win = popup({
        url: '/share/delivery_trace.php?invoiceCompanySno=' + invoiceCompanySno + '&invoiceNo=' + invoiceNo,
        target: 'trace',
        width: 650,
        height: 660,
        resizable: 'yes',
        scrollbars: 'yes'
    });
    win.focus();
    return win;
};

/**
 * 쿠폰 적용 - 상품 쿠폰
 *
 * @author artherot
 * @param integer goodsNo 상품 번호
 * @param integer cartSno 장바구니 sno
 * @param integer goodsPrice 상품 가격
 * @param integer optionPrice 옵션 가격
 */
function coupon_apply_goods(goodsNo, cartSno, goodsPrice, optionPrice)
{
    var path = get_script_dirpath('module/script/common.js');

    win = popup({
        url: path + 'share/coupon_apply_goods.php?goodsNo=' + goodsNo + '&cartSno=' + cartSno + '&goodsPrice=' + goodsPrice + '&optionPrice=' + optionPrice,
        target: 'coupon_goods',
        width: 700,
        height: 400,
        resizable: 'yes',
        scrollbars: 'yes'
    });
    win.focus();
    return win;
};

/**
 * 쿠폰 적용 - 주문 쿠폰
 *
 * @author artherot
 */
function coupon_apply_order()
{
    var path = get_script_dirpath('module/script/common.js');

    win = popup({
        url: path + 'share/coupon_apply_order.php',
        target: 'coupon_order',
        width: 700,
        height: 400,
        resizable: 'yes',
        scrollbars: 'yes'
    });
    win.focus();
    return win;
};

/**
 * 윈도우팝업 호출
 *
 * @author sunny
 * @param array options 창정보
 * @return object Window 개체
 */
function popup(options)
{
    if (!options.width) options.width = 500;
    if (!options.height) options.height = 415;
    var status = new Array();
    $.each(options, function(i, v)
    {
        if ($.inArray(i, ['url', 'target']) != 0) {
            status.push(i + '=' + v);
        }
    });
    var status = status.join(',');
    var win = window.open(options.url, options.target, status);
    return win;
}



/**
 * 스크립트 디렉토리경로 리턴
 *
 * @author qnibus
 * @param string fileName 스크립트파일명
 * @return string 디렉토리경로
 */
function get_script_dirpath(fileName)
{
    var e = document.getElementsByTagName('script');
    var regexp = new RegExp('(^|\/)' + fileName.replace('.', '\.') + '([?#].*)?$', 'i');
    for (var i = 0; i < e.length; i++) {
        if (e[i].src && regexp.test(e[i].src)) {
            var thisScriptDir = e[i].src.replace(regexp, '');

            return thisScriptDir + '/';
        }
    }
}

/**
 * 이메일 도메인 대입
 *
 * @author artherot, sunny
 * @param string emailID email input ID
 * @param boolean overlap overlap_email
 */
function put_email_domain(emailID, overlap)
{
    if ($('#email_domain').val() != '' && $('#email_domain').val() != 'self') {
        $('#' + emailID).attr('disbled', false);
        $('#' + emailID).css('display', 'none');
        $('#' + emailID).val($('#email_domain').val());
    }
    else if ($('#email_domain').val() == 'self' || $('#email_domain').val() == '') {
        $('#'+emailID).val('');
        $('#' + emailID).css('display', 'inline');
    }

    if (overlap == 'y') {
        eval(emailID).prepare();
    }
}

/**
 * 개체에 맞춰 이미지 크기 변환
 *
 * @deprecated
 * @param contents 개체
 * @param objimg 이미지
 */
function miniSelfResize(contents, objimg)
{
    var objCon = document.getElementById(contents);
    objCon.style.display = 'block';
    objimg.style.display = 'none';
    fix_w = objCon.clientWidth;
    objimg.style.display = 'block';
    if (objimg.width > fix_w) {
        objimg.width = fix_w;
        objimg.title = "popup original size Image";
    }
    else
        objimg.title = "popup original Image";
    objimg.style.cursor = "pointer";
    objimg.onclick = popup_image;
}

/**
 * 이미지 팝업 (삭제예정!!)
 *
 * @param src 이미지 url
 * @param base path
 */
function popup_image(src, base)
{
    if (!src) src = this.src;
    if (!src) return;
    if (!base) base = "";
    window.open(base + '../board/viewImg.php?src=' + escape(src), '', 'width=1,height=1');
}

/**
 * 수량 변경하기
 *
 * @param string inputName input box name
 * @param string modeStr up or dn
 * @param integer minCnt 최소수량
 * @param integer maxCnt 최대수량
 */
function count_change(inputName, modeStr, minCnt, maxCnt)
{
    var nowCnt = parseInt($('input[name=\'' + inputName + '\']').val());

    // 최소 수량 체크
    if (minCnt == 0 || minCnt == '' || typeof minCnt == 'undefined') {
        minCnt = 1;
    }
    if (nowCnt < minCnt) {
        nowCnt = parseInt(minCnt);
    }

    // 최대 수량 체크
    if (maxCnt == 0 || maxCnt == '' || typeof maxCnt == 'undefined') {
        var maxCntChk = false;
    }
    else {
        var maxCntChk = true;
        maxCnt = parseInt(maxCnt);
    }

    if (isNaN(nowCnt) === true) {
        var thisCnt = minCnt;
    }
    else {
        if (modeStr == 'up') {
            if (maxCntChk === true && nowCnt == maxCnt) {
                var thisCnt = nowCnt;
            }
            else {
                var thisCnt = nowCnt + 1;
            }
        }
        else if (modeStr == 'dn') {
            if (nowCnt > minCnt) {
                var thisCnt = nowCnt - 1;
            }
        }
    }
    $('input[name=\'' + inputName + '\']').val(thisCnt);
    $('input[name=\'' + inputName + '\']').focus();
}

/**
 * 이벤트 무료화
 *
 * @author sunny
 */
function event_hold(e)
{
    if (window.netscape) {
        e.preventDefault();
    }
    else {
        event.returnValue = false;
    }
}

/**
 * 기간차
 *
 * @author sunny
 * @param string $sdate Start date
 * @param string $edate End date
 * @return int
 */
function between_date(sdate, edate)
{
    // 시작일
    sdate = sdate.replace(/-/g, '');
    var year = sdate.substr(0, 4);
    var month = sdate.substr(4, 2) - 1;
    var day = sdate.substr(6, 2);
    var sDay = new Date(year, month, day);
    // 종료일
    edate = edate.replace(/-/g, '');
    var year = edate.substr(0, 4);
    var month = edate.substr(4, 2) - 1;
    var day = edate.substr(6, 2);
    var eDay = new Date(year, month, day);
    // 차이
    var gap = sDay.getTime() - eDay.getTime();
    gap = Math.floor(gap / (1000 * 60 * 60 * 24));
    return Math.abs(gap);
}

/**
 * 숫자의 올림 반올림 내림 처리
 *
 * @author artherot
 * @param integer numberInt 계산할 숫자
 * @param integer unit 자리수
 * @param string methodStr 올림 반올림 내림 방법 (up, half, down)
 * @return integer 계산된 숫자
 */
function gd_number_figure(numberInt, unit, methodStr)
{
    if (unit == '' || unit == 0) {
        return numberInt;
    }

    // 올림
    if (methodStr == 'up') {
        return Math.ceil(numberInt / (unit * 10)) * (unit * 10);

        // 반올림
    }
    else if (methodStr == 'half') {
        return Math.round(numberInt / (unit * 10)) * (unit * 10);

        // 내림
    }
    else if (methodStr == 'down') {
        return Math.floor(numberInt / (unit * 10)) * (unit * 10);

        // 설정이 없는 경우
    }
    else {
        return numberInt;
    }
}

/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
/**
 * 해당되는 CheckBox 체크 여부
 *
 * @author artherot
 * @param string chkID 체크 기준 checkbox ID
 * @param string targetName 타겟 checkbox의 공통적인 name 부분
 * @deprecated
 */
function check_toggle(chkID, targetName)
{
    var checked = false;
    if ($('input[id=\'' + chkID + '\']:checked').length == 1) {
        checked = true;
    }
    $('input[name*=\'' + targetName + '\']').prop('checked', checked);

    if(targetName == 'itemGoodsNo'){
        if(checked == true)    $('#tbl_add_goods_result tr').css('background-color','#f7f7f7');
        else $('#tbl_add_goods_result tr').css('background-color','');
    }
}


/**
 * Image Resize
 *
 * @deprecated 사용되지 않음
 * @author sunny
 */
set_image_resize = function(img, reWidth, reHeight)
{
    if (reWidth == null) reWidth = 0;
    if (reHeight == null) reHeight = 0;
    // Original SIze
    viewImageOWidth = img.context.width;
    viewImageOHeight = img.context.height;
    // 비율
    var ratioHeight = reHeight / viewImageOHeight;
    var ratioWidth = reWidth / viewImageOWidth;
    var ratio = (ratioHeight > ratioWidth ? ratioWidth : ratioHeight);
    if (ratio > 1) {
        ratio = 1;
    }

    // 화면에 표시할 이미지사이즈를 구한다.
    var displayImgWidth = Math.round(viewImageOWidth * ratio);
    var displayImgHeight = Math.round(viewImageOHeight * ratio);

    // 이미지사이즈를 설정하고 css를 설정한다
    img.width(displayImgWidth).height(displayImgHeight);
};


function layerCouponAuth(couponNo) {
    if (couponNo > 0) {
        $.ajax({
            method: "GET",
            cache: false,
            url: "../share/layer_coupon_auth.php",
            data: "couponNo=" + couponNo,
        }).success(function (data) {
            data = '<div id="layer_coupon_auth">' + data + '</div>';

            BootstrapDialog.show({
                title: '인증번호 등록/관리',
                size: get_layer_size('wide'),
                message: $(data),
                closable: true
            });
        }).error(function (e) {
            alert(e.responseText);
        });
    }
}

/**
 * 숫자형태의 값에 3자리 마다 comma 를 추가하는 함수
 * @param str
 * @returns {*}
 */
function number_with_comma(str){
    return str.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
