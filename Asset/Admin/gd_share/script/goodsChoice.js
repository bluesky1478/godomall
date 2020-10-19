
var GoodsChoiceController = function () {
    var goodsChoiceIframeID = 'iframe_goodsChoiceList'; //상품선택 iframe ID
    var registeredTableID = 'tbl_add_goods_result';
    var searchedTableID = 'tbl_add_goods';
    var fixDataArr = new Array();

    /**
     * 선택 상품개수 노출
     * @author bumyul2000@godo.co.kr
     * @date 2015-07-30
     */
    this.registeredCheckedGoodsCountMsg = function()
    {
        $('#registeredCheckedGoodsCountMsg').html(this.getRegisteredCheckRow().length);
    }

    /**
     * 등록 상품개수 노출
     * @author bumyul2000@godo.co.kr
     * @date 2015-07-30
     */
    this.registeredGoodsCountMsg = function()
    {
        $('#registeredGoodsCountMsg').html(this.getregisteredGoodsno().length);
    }

    /**
     * 등록할 상품 갯수체크
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.checkboxLength_choice = function () {
        var msg = '';
        var goodsChoiceRow = this.getGoodsChoiceCheckRow();
        if (goodsChoiceRow.length < 1) {
            msg = '상품을 선택해 주세요.';
        }

        return msg;
    }

    /**
     * '상품선택' 의 체크된 체크박스
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.getGoodsChoiceCheckRow = function () {
        return $('#' + goodsChoiceIframeID).contents().find('input[name="itemGoodsNo[]"]:checked');
    }

    /**
     * '상품선택' 의 체크된 체크박스
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.getGoodsChoiceHtml = function () {

        var tblGoods = $("#" + goodsChoiceIframeID).contents();
        var duplicateCnt = 0;
        var addCnt = 0;
        var registerCount = $("#" + registeredTableID).find('input[name="itemGoodsNo[]"]').length;
        var $this = this;

        tblGoods.find('input[name="itemGoodsNo[]"]:checked').each(function () {

            var sel_id = $(this).attr('id');    // 상품코드
            var sel_row = $(this);
            var row = sel_row.closest("tr");
            var table = sel_row.closest("table");

            if ($("#" + registeredTableID).find('#' + sel_id).length == 0) {

                row.detach();
                if(registerCount > 0 ) $("#" + registeredTableID).prepend(row);
                else $("#" + registeredTableID).append(row);
                $('#'+sel_id).on('click', countCheckGoods);

                addCnt++;

            } else duplicateCnt++;


            $("#" + registeredTableID).find('#' + sel_id).prop('checked', false);

            //itemGoodsNo값을 체크하여 선택 리스트에 추가함
            if (typeof $('#selectedGoodsList').val() !== "undefined") {
                $this.setSelectedGoodsList($(this).val(), false);
            }

        });


        if (duplicateCnt > 0 && addCnt > 0) alert('중복된 데이터' + duplicateCnt + '건을 제외한 ' + addCnt + '건의 데이터가 추가되었습니다.');
        else if (duplicateCnt > 0 && addCnt == 0) alert('중복된 데이터' + duplicateCnt + '건이 있습니다.');

        this.registeredGoodsCountMsg();
        this.getGoodsReSort();
        this.backgroundShow();
    }

    /**
     * '상품선택' 의 체크된 체크박스
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.getGoodsDeleteHtml = function () {

        var tblGoods = $("#" + goodsChoiceIframeID).contents();
        var duplicateCnt = 0;
        var addCnt = 0;
        var $this = this;

        //전체 선택된 상품을 삭제하는 경우인지 체크한다.
        if (typeof $('#selectedGoodsList').val() !== "undefined") {
            var selectedGoodsLength     = $('tbl_add_goods_result input[name="itemGoodsNo[]"]:checked').length;
            var selectedAllGoodsLength  = $('tbl_add_goods_result input[name="itemGoodsNo[]"]').length;
            if (selectedGoodsLength == selectedAllGoodsLength) {
                $this.setSelectedGoodsList('none');
            }
        }

        $('input[name="itemGoodsNo[]"]:checked').each(function () {
            var sel_id = $(this).attr('id');
            var sel_row = $(this);
            var row = sel_row.closest("tr");
            var table = sel_row.closest("table");

            if (tblGoods.find('#' + sel_id).length == 0) {
                row.detach();
                tblGoods.find("#" + searchedTableID).append(row);
                addCnt++;

            } else {
                row.detach();
                duplicateCnt++;
            }

            //itemGoodsNo값을 체크하여 선택 리스트에 제거함
            if (typeof $('#selectedGoodsList').val() !== "undefined") {
                if ($('#selectedGoodsList').val() !== "none") $this.setSelectedGoodsList($(this).val(), true);
            }
        });




        tblGoods.find("#" + searchedTableID +" input[name='itemGoodsNo[]']").prop("checked",false);
        $("#allCheck").prop("checked",false);

        this.getGoodsReSort();
    }

    /**
     * '상품선택' 재정렬
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.getGoodsReSort = function () {

        var cnt = 1;

        $('#'+registeredTableID+' input[name="itemGoodsNo[]"]').each(function () {

            $("#"+registeredTableID+" .addGoodsNumber_"+$(this).val()).html(cnt);
            cnt++;
        });
    }

    /**
     * 등록된 상품 갯수체크
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.checkboxLength_regestered = function (checkType) {
        var msg = '';
        var moveLength = $("#" + registeredTableID).find('input[name="itemGoodsNo[]"]:checked').length;


        if (moveLength < 1) {
            msg = '상품을 선택해 주세요.';
        }
        if (checkType != 'delete') {
            if (moveLength > 100) {
                msg = '한 번에 이동할 수 있는 최대 상품개수는 100개 입니다.';
            }
        }
        return msg;
    }

    /**
     * 화살표 상품이동
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.moveRowArrow = function (mode) {


        var itemGoodsNoCnt = $("#" + registeredTableID).find('input:checkbox[name="itemGoodsNo[]"]').length;
        var itemGoodsNoCheckedCnt = $("#" + registeredTableID).find('input:checkbox[name="itemGoodsNo[]"]:checked').length;

        if(itemGoodsNoCnt == itemGoodsNoCheckedCnt) {
            this.reSort();
            return false;
        }

        switch (mode) {
            case 'downArrowMore' :
                this.moveRowArrow_downArrowMore();
                break;

            case 'downArrow' :
                this.moveRowArrow_downArrow();
                break;

            case 'upArrow' :
                this.moveRowArrow_upArrow();
                break;

            case 'upArrowMore' :
                this.moveRowArrow_upArrowMore();
                break;
        }


    }

    /**
     * 체크상품 한단계 아래로 이동
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.moveRowArrow_downArrow = function () {
        var checkRow = this.getRegisteredCheckRow();


        if ($("#" + registeredTableID).find('input:checkbox[name="itemGoodsNo[]"]').last().prop('checked') === true) {
            return false;
        }
        else {
            $("#" + registeredTableID + " .add_goods_fix").remove();
            for (var i = checkRow.length; i >= 0; i--) {
                $(checkRow.eq(i)).insertAfter(checkRow.eq(i).next());
            }
    }


        this.reSort();
    }

    /**
     * 체크상품 맨아래로 이동
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.moveRowArrow_downArrowMore = function () {
        $("#" + registeredTableID + " .add_goods_fix").remove();
        var lastRow;
        var checkRow = this.getRegisteredCheckRow();
        $(checkRow).insertBefore($('#' + registeredTableID + ' tr').last());

        lastRow = $('#' + registeredTableID + ' tr').last();
        if (lastRow.find('input:checkbox[name="itemGoodsNo[]"]').prop('checked') === false) {
            $(lastRow).insertBefore($("#" + registeredTableID).find('input[name="itemGoodsNo[]"]:checked').closest('tr').eq(0));
        }
        this.reSort();
    }

    /**
     * 체크상품 한단계 위로 이동
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.moveRowArrow_upArrow = function () {
        $("#" + registeredTableID + " .add_goods_fix").remove();

        var checkRow = this.getRegisteredCheckRow();

        $.each(checkRow, function (i) {
            if ($(this).index() == '0') {
                return false;
            }
            //alert($(this).prevAll().filter('.add_goods_free').attr('id'));
            $($(this)).insertBefore($(this).prev());
        });

        this.reSort();
    }

    /**
     * 체크상품 맨 위로 이동
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.moveRowArrow_upArrowMore = function () {

        $("#" + registeredTableID + " .add_goods_fix").remove();
        var checkRow = this.getRegisteredCheckRow();
        $(checkRow).insertBefore($('#' + registeredTableID + ' tbody tr').first());
        this.reSort();
    }


    /**
     * 체크되어있는 체크박스 row
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.getRegisteredCheckRow = function () {
        return $("#" + registeredTableID).find('input[name="itemGoodsNo[]"]:checked').closest('tr');
    }

    /**
     * 순서변경 텍스트박스의 정수형 숫자 체크
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.checkInteager = function (obj) {
        var intPattern = /^[0-9]+$/;

        if (!intPattern.test(obj.val())) {
            alert("정수형 숫자를 입력해 주세요.");
            obj.val('');
            return false;
        }

        return true;
    }

    /**
     * 이동가능한 위치 체크
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.checkRowLength = function (obj) {

        var goodsNoArray = this.getregisteredGoodsno();
        var checkRow = this.getRegisteredCheckRow();
        var checkRowCount = checkRow.length;
        var totalRowCount = $("#" + registeredTableID + " tr").length - 1;  // 마지막 row index 값
        var remainCount = totalRowCount - checkRowCount;          // 체크되지 않은 마지막 row index 값

        if($(obj).data("page") == true) {
            return true;
        }

        if (goodsNoArray.length < 1 || goodsNoArray.length < parseInt(obj.val()) || parseInt(obj.val()) == 0 || parseInt(obj.val()) > (remainCount + 2)) {
            alert("이동할 수 없는 위치입니다.");
            obj.val('');
            return false;
        }

        return true;
    }

    /**
     * 등록된 상품
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.getregisteredGoodsno = function () {
        return $('#tbl_add_goods_result tbody tr');
        // return $('input[name="itemGoodsNo[]"]');
    }

    /**
     * 이벤트 정지
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.eventStop = function (e) {
        var event = e || window.event;
        if (event.preventDefault) {
            event.preventDefault();
        }
        else {
            event.returnValue = false;
        }
    }

    /**
     * 텍스트 순서변경 값 공유 (위,아래)
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.exchangeText = function (objValue) {
        $('input[name="goodsChoice_sortText"]').each(function () {
            $(this).val(objValue);
        });
    }

    /**
     * 텍스트 순서변경 빈값 확인
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.checkSortText = function () {
        if ($('input[name="goodsChoice_sortText"]').val() != '') {
            return true;
        }

        return false;
    }

    /**
     * 텍스트 순서변경
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.moveRowText = function (moveRowState) {

        // $("#" + registeredTableID + " .add_goods_fix").remove();
        var itemGoodsNoCnt = $("#" + registeredTableID).find('input:checkbox[name="itemGoodsNo[]"]').length;
        var itemGoodsNoCheckedCnt = $("#" + registeredTableID).find('input:checkbox[name="itemGoodsNo[]"]:checked').length;

        if(itemGoodsNoCnt == itemGoodsNoCheckedCnt) {
            this.reSort();
            return false;
        }

        var startRow = moveRowState - 1;
        var checkRow = this.getRegisteredCheckRow();
        var checkRowCount = checkRow.length;
        var totalRowCount = $("#" + registeredTableID + " tr").length - 1;  // 마지막 row index
        var remainCount = totalRowCount - checkRowCount;          // 체크되지 않은 마지막 row index
        var lastFl = false;

        if (moveRowState > remainCount + 1) {
            lastFl = true;
            startRow = remainCount;
        }

        checkRow.remove();

        $(checkRow).each(function () {
            if(lastFl) {
                $($(this)).insertAfter($("#" + registeredTableID + " tr").eq(startRow));
            } else  {
                $($(this)).insertBefore($("#" + registeredTableID + " tr").eq(startRow));
            }
            startRow++;

        });

        this.reSort();
        this.backgroundShow();

        $('.goodsChoice_sortText').val('');
    }


    /**
     * 텍스트 순서변경 값 공유 (위,아래)
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.exchangeText = function(objValue)
    {
        $('input[name="goodsChoice_sortText"]').each(function(){
            $(this).val(objValue);
        });
    }

    /**
     * 텍스트 고정
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.fixRow = function () {

        var sortFl = false;
        var fixDataIndex = 0;
        var startNum = parseInt($('input[name="startNum"]').val());

        $('#' + registeredTableID + ' input[name="itemGoodsNo[]"]:checked').each(function () {

            //레이어로 변경
            //고정박스 선택인 경우 드랍
            if($("#"+registeredTableID+" .layer_sort_fix_"+$(this).val()).is(":checked")) {

                var row = $(this).closest("tr");
                row.removeClass('add_goods_fix');
                row.addClass('add_goods_free');
                row.css("background", "#fff");


                $("#"+registeredTableID+" .layer_sort_fix_"+$(this).val()).prop('checked', false);
                $("#"+registeredTableID+" #goodsSort_"+$(this).val()).val(startNum);


                $(this).prop('checked', false);

                //체크해제필요
                fixDataArr[row.index()-fixDataIndex] = '';
                fixDataArr.splice(row.index()-fixDataIndex, 1);

                sortFl = true;
                fixDataIndex++;

            } else {

                var sortCheck = $("#"+registeredTableID+" input[name='sortFix[]']:checked").last().val();
                if(sortCheck){
                    $("#goodsSort_"+$(this).val()).val(parseInt($("#goodsSort_"+sortCheck).val())+1);
                }
                var row = $(this).closest("tr");
                row.removeClass('add_goods_free');
                row.addClass('add_goods_fix');
                row.css("background", "#E8E8E8");

                $("#"+registeredTableID+" .layer_sort_fix_"+$(this).val()).prop('checked', true);
                $("#addGoodsNumber_"+$(this).val()).html("");

                $(this).prop('checked', false);
                fixDataArr[row.index()] = row;
            }

        });

        if(sortFl)  {

            if(parseInt($('input[name="pageNow"]').val()) > 1) {
                var sortNum =  parseInt($('input[name="fixCount"]').val())+1
            } else {
                var sortNum = 1;
            }

            $('#' + registeredTableID + ' input[name="sortFix[]"]:checked').each(function () {
                $("#goodsSort_"+$(this).val()).val(sortNum);
                sortNum++;
            });

            // $("#" + registeredTableID + " .add_goods_fix").remove();
            this.reSort();
        }
    }



    /**
     * 텍스트 고정
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.reSort = function () {

        $.each(fixDataArr, function (key, value) {
            if (value) {

                if(key) $("#" + registeredTableID + " tbody tr").eq(key - 1).after(value);
                else $(value).insertBefore($('#' + registeredTableID + ' tbody tr').first());
            }
        });

        var movesort =  parseInt($('.goodsChoice_sortText').eq(0).val());

        var chk = $('#'+registeredTableID +' input[name="itemGoodsNo[]"]:checked').length;

        var pagePnum = parseInt($('input[name="pagePnum"]').val());
        var startNum = parseInt($('input[name="startNum"]').val());

        $('#'+registeredTableID +' input[name="itemGoodsNo[]"]').each(function (num) {

            if($("#"+registeredTableID +" .layer_sort_fix_"+$(this).val()).prop('checked') == false && $("#goodsSort_"+$(this).val()).val() >= startNum && $("#goodsSort_"+$(this).val()).val() < startNum+pagePnum  ) {

                var sort = $("#addGoodsNumber_"+$(this).val()).data('sort-num');
                if(movesort+chk >= pagePnum  && $(this).prop('checked') ){
                    $("#addGoodsNumber_"+$(this).val()).html(sort +" → <span class='text-danger'>"+ (movesort) +"</span>");
                    $("#goodsSort_"+$(this).val()).val(movesort);
                    movesort++;
                } else if(movesort+chk <= pagePnum  && $(this).prop('checked') ){
                    $("#addGoodsNumber_"+$(this).val()).html(sort +" → <span class='text-danger'>"+ (movesort) +"</span>");
                    $("#goodsSort_"+$(this).val()).val(movesort);
                    movesort++;
                } else {
                    var newSort = startNum+num;
                    if(sort != newSort) {
                        $("#addGoodsNumber_"+$(this).val()).html(sort +" → <span class='text-danger'>"+newSort +"</span>");
                        $("#goodsSort_"+$(this).val()).val(newSort);
                    } else {
                        $("#addGoodsNumber_"+$(this).val()).text(sort);
                        $("#goodsSort_"+$(this).val()).val(sort);
                    }

                }
            }
        });


    }

    this.moveTextSort = function () {
        var textSort = 1;
        var checkSort  = parseInt($('.goodsChoice_sortText').val());
        $('#'+registeredTableID+' input[name="itemGoodsNo[]"]').not(':checked').each(function (num) {

            if($('.goodsChoice_sortText').val() && ($('.goodsChoice_sortText').val()  ==  $("#goodsSort_"+$(this).val()).val() || $("#goodsSort_"+$(this).val()).val() == checkSort ) && $("#"+registeredTableID+" .layer_sort_fix_"+$(this).val()).prop('checked') == false ) {

                var sort = $("#addGoodsNumber_"+$(this).val()).data('sort-num');
                var newTextSort = parseInt($('.goodsChoice_sortText').val())+textSort;
                $("#goodsSort_"+$(this).val()).val(newTextSort);
                $("#addGoodsNumber_"+$(this).val()).html(sort +" → <span class='text-danger'>"+newTextSort+"</span>");
                textSort++;
                checkSort++;
            }
        });
    }

    /**
     * 고정데이터 확인
     * @author bumyul2000@godo.co.kr
     * @date 2015-06-25
     */
    this.addFixDataArr = function (key,value) {

        fixDataArr[key] = value;
    }

    /**
     * 고정데이터 개수 확인
     * @author dlwoen9@godo.co.kr
     * @date 2017-09-04
     */
    this.countFixDataArr = function () {

        return fixDataArr.length;
    }

    /**
     * 선택 BG처리
     * @author cjb3333@godo.co.kr
     * @date 2016-07-11
     */
    this.backgroundShow = function () {

        $("#tbl_add_goods_result tbody").find('input[name="itemGoodsNo[]"]').each(function () {
            $(this).click(function (e) {
                if($(this).is(":checked")) {
                    $(this).parent().parent().css('background-color','#f7f7f7');
                }else{
                    if(e.target.parentElement.parentElement.className == 'add_goods_fix')   $(this).parent().parent().css('background-color', '#E8E8E8');
                    else    $(this).parent().parent().css('background-color','');
                }
            });

            if(!$("#tbl_add_goods_result tbody .layer_sort_fix_"+$(this).val()).is(":checked")) {
                if($(this).is(":checked")) {
                    $(this).parent().parent().css('background-color','#f7f7f7');
                }else{
                    $(this).parent().parent().css('background-color','');
                }
            }

        });
    }

    /**
     * 선택된 리스트 관리
     * @author parkjs@godo.co.kr
     * @date 2018-10-01
     */
    this.setSelectedGoodsList = function(itemGoodsNo, removeFl) {
        if (itemGoodsNo == 'none') {    //전체 삭제되었을 경우를 구분하기 위한 값 (추후 로직변경 시, 삭제됨)
            $('#selectedGoodsList').val('none');
            return;
        }

        $('#selectedGoodsList').val($('#selectedGoodsList').val().replace('none',''));
        var checkGoodsNo = $('#selectedGoodsList').val().indexOf(itemGoodsNo + ','); //itemGoodsNo 중복 체크용 변수
        if (removeFl === true) { //itemGoodsNo를 삭제할 경우
            $('#selectedGoodsList').val($('#selectedGoodsList').val().replace(itemGoodsNo + ',', ""));
        } else if (checkGoodsNo == -1) {
            $('#selectedGoodsList').val($('#selectedGoodsList').val() + itemGoodsNo + ',');
        }
        return;
    }

}

$(document).ready(function () {

    var goodsChange = false;
    var goodsChoice = new GoodsChoiceController();
    var doubleClick = false;
    var tableAddID = ($('input[name="relationFl"]').val() == 'm') ? 'relationGoodsInfo' : 'tbl_add_goods_set'; // 상품 등록수정 공급사 선택 추가

    goodsChoiceFunc = goodsChoice;

    //기존에 등록된 내용이 있는지 확인
    if($("#tbl_add_goods_result tbody tr").length == 0 && $("#tbl_add_goods_result").data("result") !='self') {

        if ($("#" + tableAddID +" tbody", opener.document).length) {

            if ($("#" + tableAddID + " tbody.active", opener.document).length)  var targetAddGoods = "#" + tableAddID + " tbody.active";
            else  var targetAddGoods = "#" + tableAddID + " tbody";

            var addGoodsList = $(targetAddGoods, opener.document).html();

            if (addGoodsList.length > 0) {

                $("#tbl_add_goods_result tbody").append(addGoodsList);
                $("#tbl_add_goods_result tbody .js-goodschoice-hide").hide();
                $("#tbl_add_goods_tr_none").remove();
                //opener창 필드추가 감춤처리
                $("#tbl_add_goods_result tbody").find(".displayFl").hide();

                $("#tbl_add_goods_result tbody input[name='sortFix[]']").each(function () {
                    if ($(this).is(":checked")) {
                        $("#tbl_add_goods_result .layer_sort_fix_" + $(this).val()).prop('checked', true);
                        var row = $("#tbl_add_goods_result .layer_sort_fix_" + $(this).val()).closest("tr");

                        goodsChoice.addFixDataArr(row.index(), row);
                    }
                    else $(".layer_sort_fix_" + $(this).val()).prop('checked', false);

                    //itemGoodsNo값을 체크하여 선택 리스트에 추가함.
                    //(선택 리스트의 값이 none일 경우 전체 삭제된 리스트로 간주)
                    if (typeof $('#selectedGoodsList').val() !== "undefined") {
                        if ($('#selectedGoodsList').val() !== "none") {
                            goodsChoice.setSelectedGoodsList($(this).val(), false);
                        }
                    }
                });
                $( ".js-goods-popup" ).click(function() {
                    goods_register_popup($(this).data('goodsno'));
                });
            }

            $(".addGoodsDisplayNone").remove();

        }

        goodsChoiceFunc.backgroundShow();

    } else {

        if ($("#tbl_add_goods_result tbody").length) {

            var addGoodsList = $("#tbl_add_goods_result tbody").html();

            if (addGoodsList.length > 0) {

                $('input[name="sortFix[]"]').each(function () {

                    if ($(this).is(":checked")) {
                        $(".layer_sort_fix_" + $(this).val()).prop('checked', true);
                        var row = $(".layer_sort_fix_" + $(this).val()).closest("tr");
                        goodsChoice.addFixDataArr(row.index(), row);
                    }
                    else $(".layer_sort_fix_" + $(this).val()).prop('checked', false);

                });
            }

        }

        goodsChoiceFunc.backgroundShow();
    }



    $('#addGoods').click(function () {

        goodsChange = true;
        var errorMsg = goodsChoice.checkboxLength_choice();
        if (errorMsg) {
            alert(errorMsg);
            return false;
        }
        goodsChoice.getGoodsChoiceHtml();

    });


    $('#delGoods').click(function () {

        if ($('input[name="itemGoodsNo[]"]:checked').length < 1) {
            alert('상품을 선택해 주세요.');
            return false;
        }
        goodsChoice.getGoodsDeleteHtml();
        goodsChoice.registeredCheckedGoodsCountMsg();

    });

    $('.goodsChoice_downArrowMore').click(function () {
        goodsChange = true;
        var errorMsg = goodsChoice.checkboxLength_regestered('move');
        if (errorMsg) {
            alert(errorMsg);
            return false;
        }
        goodsChoice.moveRowArrow('downArrowMore');
    });

    $('.goodsChoice_downArrow').click(function () {

        goodsChange = true;
        var errorMsg = goodsChoice.checkboxLength_regestered('move');
        if (errorMsg) {
            alert(errorMsg);
            return false;
        }
        goodsChoice.moveRowArrow('downArrow');

    });

    //위로
    $('.goodsChoice_upArrow').click(function () {
        goodsChange = true;
        var errorMsg = goodsChoice.checkboxLength_regestered('move');
        if (errorMsg) {
            alert(errorMsg);
            return false;
        }
        goodsChoice.moveRowArrow('upArrow');
    });

    //맨위로
    $('.goodsChoice_upArrowMore').click(function () {
        goodsChange = true;
        var errorMsg = goodsChoice.checkboxLength_regestered('move');
        if (errorMsg) {
            alert(errorMsg);
            return false;
        }
        goodsChoice.moveRowArrow('upArrowMore');
    });

    //수기이동 text
    $('.goodsChoice_sortText').bind('change', function (e) {
        var thisObj = $(this);

        if (thisObj.val() != '') {
            if (goodsChoice.checkInteager(thisObj) == false || goodsChoice.checkRowLength(thisObj) == false) {
                goodsChoice.eventStop(e);
                return false;
            }
            goodsChoice.exchangeText(thisObj.val());
        }
    });

    $('.goodsChoice_moveBtn').click(function (e) {
        goodsChange = true;

        if (goodsChoice.checkSortText() == false) {
            alert("이동할 위치를 입력하여 주세요");
            return false;
        }
        var errorMsg = goodsChoice.checkboxLength_regestered('move');
        if (errorMsg) {
            alert(errorMsg);
            return false;
        }

        var cnt  = parseInt($('.goodsChoice_sortText').eq(0).val());

        if($('input[name="startNum"]').length == 0) {
            var startNum = 1;
        } else {
            var startNum = parseInt($('input[name="startNum"]').val());
        }

        if(cnt <= startNum ) {
            goodsChoice.moveRowArrow('upArrowMore');
        } else {
            goodsChoice.moveRowText(parseInt($('.goodsChoice_sortText').eq(0).val())-(startNum-1));
        }

        goodsChoice.moveTextSort();

        $('.goodsChoice_sortText').val('');

        goodsChoice.backgroundShow();
    });

    $('.goodsChoice_cate_moveBtn').click(function (e) {
        goodsChange = true;

        if (goodsChoice.checkSortText() == false) {
            alert("이동할 위치를 입력하여 주세요");
            return false;
        }
        var errorMsg = goodsChoice.checkboxLength_regestered('move');
        if (errorMsg) {
            alert(errorMsg);
            return false;
        }

        var cnt  = parseInt($('.goodsChoice_sortText').eq(0).val());

        if(cnt <= goodsChoice.countFixDataArr())    return false;

        if($('input[name="startNum"]').length == 0) {
            var startNum = 1;
        } else {
            var startNum = parseInt($('input[name="startNum"]').val());
        }

        if(cnt <= startNum ) {
            goodsChoice.moveRowArrow('upArrowMore');
        } else {
            goodsChoice.moveRowText(parseInt($('.goodsChoice_sortText').eq(0).val())-(startNum-1));
        }

        goodsChoice.moveTextSort();

        $('.goodsChoice_sortText').val('');

        goodsChoice.backgroundShow();
    });

    $('.goodsChoice_fixBtn').click(function (e) {
        goodsChange = true;
        if (goodsChoice.checkSortText() == false) {
            goodsChoice.fixRow();
            return false;
        }
        var errorMsg = goodsChoice.checkboxLength_regestered('move');
        if (errorMsg) {
            alert(errorMsg);
            return false;
        }

        //goodsChoice.moveRowText(parseInt($('.goodsChoice_sortText').eq(0).val()));
        goodsChoice.fixRow();

    });


    $('.goodsChoice_fixUpBtn').click(function () {
        var idx = 0;
        $("#tbl_add_goods_result tbody tr input[name='itemGoodsNo[]']").each(function(){
            if($(this).is(':checked')) idx++;
        })
        if(idx == 0)    return false;
        goodsChange = true;
        if (goodsChoice.checkSortText() == false) {
            $('.goodsChoice_sortText').val(parseInt($('input[name="fixCount"]').val())+1);
            goodsChoice.moveRowArrow('upArrowMore');
            goodsChoice.fixRow();
            $('.goodsChoice_sortText').val('');
            goodsChoice.backgroundShow();
            return false;
        }
        var errorMsg = goodsChoice.checkboxLength_regestered('move');
        if (errorMsg) {
            alert(errorMsg);
            return false;
        }

        //goodsChoice.moveRowText(parseInt($('.goodsChoice_sortText').eq(0).val()));
        //goodsChoice.moveRowArrow('upArrowMore');
        goodsChoice.fixRow();

        $('.goodsChoice_sortText').val('');
    });

    $('.goodsChoiceConfirm').click(function (e) {

        $("#tbl_add_goods_tr_none",opener.document).remove();


        var resultJson = {
            "info": []
        };

        var tblGoods = $("#tbl_add_goods_result").contents();


        tblGoods.find('input[name="itemGoodsNo[]"]').each(function(key, val) {


            var goodsNo		=$(this).val();
            var goodsNm		=tblGoods.find('input[name="itemGoodsNm[]"]').eq(key).val();
            var goodsPrice		=tblGoods.find('input[name="itemGoodsPrice[]"]').eq(key).val();
            var scmNm		=tblGoods.find('input[name="itemScmNm[]"]').eq(key).val();
            var totalStock		=tblGoods.find('input[name="itemTotalStock[]"]').eq(key).val();
            var image		=tblGoods.find('input[name="itemImage[]"]').eq(key).val();
            var brandNm		=tblGoods.find('input[name="itemBrandNm[]"]').eq(key).val();
            var makerNm		=tblGoods.find('input[name="itemMakerNm[]"]').eq(key).val();
            var optionNm		=tblGoods.find('input[name="itemOptionNm[]"]').eq(key).val();
            var soldOutFl		=tblGoods.find('input[name="itemSoldOutFl[]"]').eq(key).val();
            var stockFl		=tblGoods.find('input[name="itemStockFl[]"]').eq(key).val();

            var goodsDisplayFl		=tblGoods.find('input[name="itemGoodsDisplayFl[]"]').eq(key).val();
            var goodsDisplayMobileFl		=tblGoods.find('input[name="itemGoodsDisplayMobileFl[]"]').eq(key).val();
            var goodsSellFl		=tblGoods.find('input[name="itemGoodsSellFl[]"]').eq(key).val();
            var goodsSellMobileFl		=tblGoods.find('input[name="itemGoodsSellMobileFl[]"]').eq(key).val();

            var goodsIcon		=tblGoods.find('input[name="itemIcon[]"]').eq(key).val();

            var sortFix		=tblGoods.find('.layer_sort_fix_'+goodsNo).prop('checked');

            //등록일 및 관련상품 노출기간 추가
            var regDt       = tblGoods.find('input[name="regDt[]"]').eq(key).val();
            var relationGoodsNoStartYmd = $('#relationGoodsStartYmd_'+goodsNo).val();
            var relationGoodsNoEndYmd = $('#relationGoodsEndYmd_'+goodsNo).val();
            var relationGoodsEach       = $('#relationGoodsEach_'+goodsNo).val();

            resultJson.info.push({"goodsNo": goodsNo, "goodsNm": goodsNm, "goodsPrice": goodsPrice, "scmNm": scmNm, "totalStock": totalStock, "image": image,"sortFix": sortFix,"brandNm": brandNm,"makerNm": makerNm,"optionNm": optionNm,"soldOutFl": soldOutFl,"stockFl": stockFl,"goodsDisplayFl": goodsDisplayFl,"goodsDisplayMobileFl": goodsDisplayMobileFl,"goodsSellFl": goodsSellFl,"goodsSellMobileFl": goodsSellMobileFl,"goodsIcon": goodsIcon, "regDt": regDt, "relationGoodsNoStartYmd": relationGoodsNoStartYmd, "relationGoodsNoEndYmd" : relationGoodsNoEndYmd, "relationGoodsEach" : relationGoodsEach});

        });

        if ($('input[name="relationFl"]').val() == 'm') {
            opener.parent.setRelationGoods(resultJson); // 상품등록 수정 관련상품 상품선택 추가
        } else {
            opener.parent.setAddGoods(resultJson);
        }
        self.close();

    });

    goodsChoiceFunc.backgroundShow();
});

function countCheckGoods() {

    $('#registeredCheckedGoodsCountMsg').html($("#tbl_add_goods_result").find('input[name="itemGoodsNo[]"]:checked').closest('tr').length);
}

function all_checkbox(checkbox,tbl) {
    if($(checkbox).is(":checked ")) {
        //$("#"+tbl+" input[name='itemGoodsNo[]']").prop("checked",true);

        $("#"+tbl+" input[name='itemGoodsNo[]']").each(function () {
            if($(this).prop('disabled') != true) {
                $(this).prop("checked",true);
            }
        });

    } else {
        $("#"+tbl+" input[name='itemGoodsNo[]']").prop("checked",false);
    }

    goodsChoiceFunc.backgroundShow();

}
