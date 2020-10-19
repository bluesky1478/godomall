/**
 * 모바일앱 상품
 **/
$(document).ready(function(){
    var page = 1;
    var pageNum = $("#pageNum").val();
    var requestParameter = [];
    var totalCount = 0;

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

        /*
         *  상품리스트
         */
        setComma : function(str)
        {
            return String(str).replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
        },

        //초기화
        mobileapp_resetSearch : function()
        {
            $('#mobileapp_keyword, input[name="searchDate[]"]').val('');
            $('#mobileapp_key').val('all').attr("selected", "selected");
            $('#mobileapp_key').siblings('label').text($('#mobileapp_key option:first').text());
            $("select[name='cateGoods[]']").val('').attr("selected", "selected");
            $('#cateGoods2 option, #cateGoods3 option, #cateGoods4 option').not("[value='']").remove();
            $('input:radio[name="goodsDisplayFl"][value=""]').prop('checked', true);
        },

        //더보기 버튼 노출 여부
        mobileapp_checkDisplayMore : function()
        {
            var trLength = Number($('#mobileapp_goodsListArea > tr').length);
            if(Number(totalCount) <= Number(trLength)){
                $("#mobileapp_moreGoodsList").css('display', 'none');
            }
            else{
                $("#mobileapp_moreGoodsList").css('display', '');
            }
        },

        //ajax parameter 정리
        mobileapp_setRequestParameter : function(pageNum, moreStatus)
        {
            //더보기 클릭시
            if(moreStatus === true){
                var requestParameter = JSON.parse(sessionStorage.getItem("goodsList_request"));
                page += 1;
                requestParameter['page'] = page;
                requestParameter['pageNum'] = pageNum;
            }
            else {
                var dateVal1 = $('#mobileapp_searchDate1').val();
                var dateVal2 = $('#mobileapp_searchDate2').val();
                if($.trim(dateVal1) !== ''){
                    var searchDate1 = dateVal1.substring(0, 4) + '-' + dateVal1.substring(4, 6) + '-' + dateVal1.substring(6, 8);
                }
                if($.trim(dateVal2) !== ''){
                    var searchDate2 = dateVal2.substring(0, 4) + '-' + dateVal2.substring(4, 6) + '-' + dateVal2.substring(6, 8);
                }

                page = 1;
                var requestParameter = {
                    mode : 'get_goods_list',
                    page : page,
                    pageNum : pageNum,
                    key : $('#mobileapp_key').val(),
                    keyword : $('#mobileapp_keyword').val(),
                    searchDate : [searchDate1, searchDate2],
                    cateGoods : [
                        $("#cateGoods1").val(),
                        $("#cateGoods2").val(),
                        $("#cateGoods3").val(),
                        $("#cateGoods4").val()
                    ],
                    goodsDisplayFl : $(':radio[name="goodsDisplayFl"]:checked').val()
                };
                sessionStorage.setItem("goodsList_request", JSON.stringify(requestParameter));
            }

            return requestParameter;
        },

        //get 상품리스트
        mobileapp_getGoodsList : function(requestParameter, listReset)
        {
            var $ajax = $.ajax('mobileapp_goods_ps.php', {
                method: "GET",
                data: requestParameter,
                beforeSend : function (){
                    $.showProgressBar();
                }
            });
            $ajax.done(function (responseData) {
                if(responseData.result !== 'ok'){
                    alert("로딩을 실패하였습니다.\n고객센터에 문의해 주세요.");
                    return;
                }
                //회원리스트 리셋
                if(listReset === true){
                    $('#mobileapp_goodsListArea').empty();
                }

                totalCount = responseData.total_count;
                var html = '';

                $('#mobileapp_totalCount').html($.setComma(totalCount));
                if(totalCount > 0){
                    //리스트 출력
                    $.each(responseData.list, function(index, row){
                        if($.trim(row.soldoutImage) !== ''){
                            var soldoutRow = '<tr><td>'+row.soldoutImage+'</td></tr>';
                            var rowSpan = 5;
                        }
                        else {
                            var soldoutRow = '';
                            var rowSpan = 4;
                        }

                        html += '<tr class="lists text-left gList-contents-row" data-goodsNo="'+row.goodsNo+'" style="border-bottom: 1px #ebebeb solid; padding-bottom: 10px !important;">';
                        html += '<td>';
                        html += '   <table width="100%" border="0" class="gList-list-area">';
                        html += '   <colgroup><col style="width: 30%;"/><col /><col style="width: 10%;"/></colgroup>';
                        html += '   <tr>';
                        html += '       <td rowspan="'+rowSpan+'" class="imageArea">'+row.image+'</td>';
                        html += '       <td><span class="goodsNameArea">'+row.goodsNm+'</span></td>';
                        html += '       <td rowspan="'+rowSpan+'" class="locationBtnArea"><img src="/admin/gd_share/img/mobileapp/icon/ico_blank.png" border="0" class="gList-locationMobileGoodsView" style="width: 27px; height: 23px;" /></td>';
                        html += '   </tr>';
                        html += '   <tr>';
                        html += '       <td><span class="goodsPriceArea">'+row.goodsPrice+'</span>';
                        html += '   </tr>';
                        html += '   <tr>';
                        html += '       <td>'+row.goodsDisplayFl+' | '+row.totalStock+'</td>';
                        html += '   </tr>';
                        html += '   <tr>';
                        html += '       <td>'+row.regDt+'</td>';
                        html += '   </tr>';
                        html += soldoutRow;
                        html += '   </table>';
                        html += '</td>';
                        html += '</tr>';
                    });
                }
                else {
                    html = '<tr class="no-list"><td class="text-center gList-noItem-area">상품이 존재하지 않습니다.</td></tr>';
                }

                $('#mobileapp_goodsListArea').append(html);

                $.mobileapp_checkDisplayMore();

                $.mobileapp_storageSave();

            });
            $ajax.always(function() {
                $.hideProgressBar();
            });
        },

        mobileapp_storageSave : function()
        {
            localStorage.setItem("mobileapp-goods-list", $('#mobileapp_goodsListArea').html());
            localStorage.setItem("mobileapp-goods-list-totalCount", totalCount);
            localStorage.setItem("mobileapp-goods-list-page", page);
            localStorage.setItem("mobileapp-goods-list-searchKeyText", $("#mobileapp_key").siblings('label').text());
            localStorage.setItem("mobileapp-goods-list-category", JSON.stringify([$("#cateGoods1").val(), $("#cateGoods2").val(), $("#cateGoods3").val(), $("#cateGoods4").val()]));
            localStorage.setItem("mobileapp-goods-list-category-option", JSON.stringify([$("#cateGoods1").html(), $("#cateGoods2").html(), $("#cateGoods3").html(), $("#cateGoods4").html()]));
        },

        mobileapp_storageLoad : function()
        {
            var mobileapp_page = localStorage.getItem("mobileapp-page");
            var mobileapp_page_parameter_mode =  localStorage.getItem("mobileapp-page-parameter-mode");
            if(mobileapp_page === 'mobileapp_goods_register.php' && mobileapp_page_parameter_mode === 'modify'){
                //리스트 컨텐츠 복구
                $('#mobileapp_goodsListArea').html(localStorage.getItem("mobileapp-goods-list"));
                //카운트
                totalCount = localStorage.getItem("mobileapp-goods-list-totalCount");
                $('#mobileapp_totalCount').html(totalCount);

                //페이지
                page = Number(localStorage.getItem("mobileapp-goods-list-page"));
                //화면위치 조정
                var goodsNo = localStorage.getItem("mobileapp-page-parameter-goodsNo");
                if($.trim(goodsNo) !== '') {
                    var moveTargetEl = $(".gList-contents-row[data-goodsNo='" + goodsNo + "']");
                    if (moveTargetEl.length > 0) {
                        $(window).scrollTop(moveTargetEl.offset().top - 45);
                    }
                }
                //검색유형
                $("#mobileapp_key").siblings('label').text(localStorage.getItem("mobileapp-goods-list-searchKeyText"));

                //카테고리 정보 복원
                $.mobileapp_setCategoryHistory();

                $.mobileapp_checkDisplayMore();

                return true;
            }

            return false;
        },

        //카테고리 정보 복원
        mobileapp_setCategoryHistory : function()
        {
            $(window).load(function() {
                var categoryArray = JSON.parse(localStorage.getItem("mobileapp-goods-list-category"));
                var categoryOptionArray = JSON.parse(localStorage.getItem("mobileapp-goods-list-category-option"));
                $("#cateGoods1").html(categoryOptionArray[0]);
                $("#cateGoods2").html(categoryOptionArray[1]);
                $("#cateGoods3").html(categoryOptionArray[2]);
                $("#cateGoods4").html(categoryOptionArray[3]);

                if(categoryArray[0]){
                    $("#cateGoods1").val(categoryArray[0]);
                }
                if(categoryArray[1]){
                    $("#cateGoods2").val(categoryArray[1]);
                }
                if(categoryArray[2]){
                    $("#cateGoods3").val(categoryArray[2]);
                }
                if(categoryArray[3]){
                    $("#cateGoods4").val(categoryArray[3]);
                }
            });
        },

        mobileapp_checkSearchDate : function()
        {
            var mobileapp_searchDate1 = $("#mobileapp_searchDate1");
            var mobileapp_searchDate2 = $("#mobileapp_searchDate2");

            if(mobileapp_searchDate1.val().length > 0) {
                if (mobileapp_searchDate1.val().length !== 8) {
                    alert("YYYYMMDD 형식으로 입력해 주세요.");
                    mobileapp_searchDate1.focus();
                    return false;
                }
            }
            if(mobileapp_searchDate2.val().length > 0){
                if(mobileapp_searchDate2.val().length !== 8){
                    alert("YYYYMMDD 형식으로 입력해 주세요.");
                    mobileapp_searchDate2.focus();
                    return false;
                }
            }

            return true;
        },
        /*
         *  상품리스트
         */


        /*
         *  상품 등록/수정
         */
        //레이어 노출
        mobileapp_showLayer : function(url, title, param)
        {
            $.get(url, param, function (data) {
                var dataHTML = '<div>' + data + '</div>';

                BootstrapDialog.show({
                    title: title,
                    size: BootstrapDialog.SIZE_NORMAL,
                    message: $(dataHTML),
                    closable: true
                });
            });
        },

        mobileapp_checkFormValidation : function()
        {
            if($.trim($("input[name='goodsNm']").val()) == ''){
                alert('상품명을 입력해 주세요.');
                $("input[name='goodsNm']").focus();
                return false;
            }

            if(mobileapp_option_adjust === false){
                alert("옵션 설정이 변경되었습니다.\n옵션가격 설정적용을 클릭해 주세요.");
                return false;
            }

            return true;
        },

        mobileapp_displayTab : function(el)
        {
            //2개 이상의 옵션정보 사용 불가
            if(el.attr('id') === 'mobileapp_tab_option' && $(".mobileapp_part_option").attr('data-realOptionCnt') > 2){
                alert('모바일에서는 옵션 설정은 옵션 개수가 2개 이하인 상품만 가능합니다.');
                return;
            }
            el.siblings('div').fadeToggle(300, function() {
                if ($(this).is(":hidden")) {
                    el.find('.gRegister-tab-arrow').html('<img src="/admin/gd_share/img/mobileapp/icon/icon_arrow02.png" border="0" />');
                }
                else {
                    el.find('.gRegister-tab-arrow').html('<img src="/admin/gd_share/img/mobileapp/icon/icon_arrow02_up.png" border="0" />');
                }
            });
        },

        //옵션 생성
        mobileapp_changeOption : function(mode, optionCount, dataArray)
        {
            if(optionCount > 0){
                $('.gRegister-display-add').show();
            }
            else {
                $('.gRegister-display-add').hide();
            }

            var fieldCnt = $('input[id*=\'option_optionName_\']').length;
            var optionHtml = '';

            $("#mobileapp_optionArea").empty();

            var optionName = '';
            var optionValue = '';
            for (var i = fieldCnt; i < optionCount; i++) {

                if(mode === 'auto'){
                    if(dataArray['optionName'][i]){
                        optionName = dataArray['optionName'][i];
                    }
                    else {
                        optionName = '';
                    }
                    if(dataArray['optionValue'][i]){
                        optionValue = dataArray['optionValue'][i];
                    }
                    else {
                        optionValue = '';
                    }
                }

                optionHtml = "<div class='gRegister-option-input-area'>";
                optionHtml += "<input type='text' id='mobileapp_optionName_"+i+"' name='optionY[optionName][]' class='form-control input-sm gRegister-optionName' value='"+optionName+"' placeholder='옵션명을 입력해주세요. ex)사이즈' input-type='optionName' />";
                optionHtml += "<input type='text' id='mobileapp_optionValue_"+i+"' name='optionY[optionValue]["+i+"][]' class='form-control input-sm gRegister-option-selector' value='"+optionValue+"' placeholder='옵션값을 입력해주세요. ex)XL' style='margin-top: 2px !important;' />";
                optionHtml += "</div>";

                $("#mobileapp_optionArea").append(optionHtml);
                $('#mobileapp_optionValue_' + i).tagsinput();
            }
        },

        mobileapp_setHtmlOption : function(optionName, optionValue)
        {
            var optionRow = [];
            var html = "";
            var optionNameCount = optionName.length;
            if(optionNameCount === 2){
                var optionColspan = 5;
            }
            else if(optionNameCount === 1){
                var optionColspan = 4;
            }
            else {
                var optionColspan = 3;
            }

            html += "<table class='table table-bordered table1 gRegister-option-detail'><colgroup><col /><col /><col /><col /><col /></colgroup>";
            html += "<thead><tr><th class='gRegister-option-detail-title' colspan='"+optionColspan+"'>옵션 가격/재고 설정";
            html += "<div><button type='button' class='btn btn-default-gray btn-sm gRegister-optionDetail-all-btn'>일괄적용</button></div>";
            html += "</th></tr>";

            for (var idx in optionName) {
                if(optionValue[idx] && typeof(optionValue[idx]) !== 'object'){
                    optionValue[idx] = optionValue[idx].split(','); // 옵션의 조합 개수
                }
                html += '<th>' + optionName[idx] + '</th>';
            }
            html += '<th>노출</th><th>품절</th><th>상세</th></thead>';
            html += '<tbody>';
            var index=0;
            for (var idx in optionValue[0]) {
                if(optionValue[1]){
                    for (var subIdx in optionValue[1]) {
                        optionRow[index] = [];
                        optionRow[index][0] = optionValue[0][idx];
                        optionRow[index][1] = optionValue[1][subIdx];
                        index++;
                    }
                }
                else {
                    optionRow[index] = [];
                    optionRow[index][0] = optionValue[0][idx];
                    index++;
                }
            }

            for (var idx in optionRow) {
                optionPriceID = "mobileapp_optionPrice_" + idx;
                stockCntID = "mobileapp_stockCnt_" + idx;
                insertBtnID = "mobileapp_insertBtn_" + idx;

                html += '<tr>';
                html += '<td>'+optionRow[idx][0]+'</td>';
                if(optionRow[idx][1]){
                    html += '<td>'+optionRow[idx][1]+'</td>';
                }
                html += '<td>';
                html += '<input type="hidden" name="optionY[optionValueText][]" value="'+optionRow[idx].join($(".mobileapp_part_option").attr('data-strDivision'))+'" />';
                html += '<input id="mobileapp_option_optionViewFlApply_'+idx+'" type="checkbox" value="y" name="optionY[optionViewFl]['+idx+']" checked="checked" />';
                html += '</td>';
                html += '<td>';
                html += '<input id="mobileapp_option_optionSellFl_'+idx+'" type="checkbox" value="n" name="optionY[optionSellFl]['+idx+']" />';
                html += '</td>';
                html += '<td>'
                html += '<button type="button" class="btn btn-danger btn-sm gRegister-optionDetail-btn" id="'+insertBtnID+'" data-optionID="'+optionPriceID+'" data-stockID="'+stockCntID+'">입력</button>';
                html += '<input type="hidden" id="'+optionPriceID+'" name="optionY[optionPrice][]" />'; //옵션가
                html += '<input type="hidden" id="'+stockCntID+'" name="optionY[stockCnt][]" />'; //재고량
                html += '</td>';
                html += '</tr>';
            }
            html += '</tbody>';
            html += '</table>';

            $('#mobileapp_detail_option_area').html(html);
            $('#mobileapp_detail_option_area').show();

            if($('.bootstrap-dialog-close-button').length > 0){
                $('.bootstrap-dialog-close-button').trigger('click');
            }
        },

        //옵션 설정 - 수기
        mobileapp_settingOption_self : function()
        {
            var error = '';
            var optionName = [];
            var optionValue = [];
            var inputArray = $("#mobileapp_optionArea > .gRegister-option-input-area > input[type='text']");

            inputArray.each(function() {
                if($.trim($(this).val()) === ''){
                    error = '옵션명 or 옵션값을 입력해주세요.';
                    $(this).focus();
                    return false;
                }

                if($(this).attr('input-type') === 'optionName'){
                    optionName.push($(this).val());
                }
                else {
                    optionValue.push($(this).val());
                }
            });

            if (error) {
                alert(error);
                return false;
            }

            $.mobileapp_setHtmlOption(optionName, optionValue);
        },

        //옵션 설정 - 자주쓰는옵션
        mobileapp_settingOption_auto : function(dataArray)
        {
            //옵션사용 : '사용함' 설정
            $('input:radio[name="optionFl"][value="y"]').prop('checked', true);
            //옵션 노출 방식 설정
            $('input:radio[name="optionY[optionDisplayFl]"][value="'+dataArray['displayFl']+'"]').prop('checked', true);
            //옵션 개수 설정
            $('#mobileapp_optionY_optionCnt').val(dataArray['optionCount']);

            $.mobileapp_changeOption('auto', dataArray['optionCount'], dataArray);
            $.mobileapp_setHtmlOption(dataArray['optionName'], dataArray['optionValue']);

            mobileapp_option_adjust = true;
        },

        //옵션 가격/재고 상세 설정 저장 - 일괄적용
        mobileapp_saveDetailOption_all : function()
        {
            var valuePrice = $("input[name='mobileapp_layer_optionPrice']").val();
            var valueStock = $("input[name='mobileapp_layer_stockCnt']").val();
            var buttonEl = $(".gRegister-optionDetail-btn");

            $("input[name='optionY[optionPrice][]']").val(valuePrice);
            if (valueStock !== '' && valueStock > 0) {
                $("input[name='optionY[stockCnt][]']").val(valueStock);
            }

            buttonEl.removeClass('btn-danger');
            buttonEl.addClass('btn-default-gray');
            buttonEl.html('수정');
        },

        //옵션 가격/재고 상세 설정 저장
        mobileapp_saveDetailOption : function()
        {
            if($("#mobileapp_layer_detailMode").val() === 'all'){
                $.mobileapp_saveDetailOption_all();
                return;
            }
            var valuePrice = $("input[name='mobileapp_layer_optionPrice']").val();
            var valueStock = $("input[name='mobileapp_layer_stockCnt']").val();
            var buttonEl = $("#" + $("#mobileapp_layer_thisID").val());

            if ($.trim(valuePrice) || $.trim(valueStock)) {
                buttonEl.removeClass('btn-danger');
                buttonEl.addClass('btn-default-gray');
                buttonEl.html('수정');
            }
            else {
                buttonEl.html('입력');
                buttonEl.removeClass('btn-default-gray');
                buttonEl.addClass('btn-danger');
            }

            $("#" + $("#mobileapp_layer_optionID").val()).val(valuePrice);
            if (valueStock !== '' && valueStock > 0) {
                $("#" + $("#mobileapp_layer_stockID").val()).val(valueStock);
            }
        },

        mobileapp_getOptionData : function(sno)
        {
            var $ajax = $.ajax('mobileapp_ps.php', {
                method: "POST",
                data: {
                    mode : 'get_option_detail_contents',
                    sno : sno
                }
            });
            $ajax.done(function (responseData) {
                if(responseData['data']['result'] === 'fail'){
                    alert('옵션 개수 2개 이상의 등록/수정은 PC로 가능합니다.');
                    return;
                }
                $.mobileapp_settingOption_auto(responseData['data']);

                $('.gRegister-display-add').show();
            });
        },

        //옵션 사용여부에 따른  - 옵션 설정 노출여부, 상품재고 입력 방지 체크
        mobileapp_option_execute : function()
        {
            if($(":radio[name='optionFl']:checked").val() === 'y'){
                //옵션 설정 노출
                $("#mobileapp_option_display_area").show();
                //옵션 사용여부에 따른 상품재고 수정방지
                $("input[name='stockCnt']").attr("disabled", "disabled");
            }
            else {
                //옵선 설정 노출 방지
                $("#mobileapp_option_display_area").hide();
                //옵션 사용여부에 따른 상품재고 수정 허용
                if ($("#mobileapp_mode").val() === 'modify' && $("input[name='stockCnt']").hasClass('disabled_stockCnt')) {
                    $("input[name='stockCnt']").attr("disabled", "disabled");
                } else {
                    $("input[name='stockCnt']").attr("disabled", false);
                }
            }
        }
        /*
         *  상품 등록/수정
         */
    });
});
