var gd_goods_view = function () {
    var setOptionFl = "n";
    var setOptionTextFl = "n";
    var setOptionDisplayFl = "s";
    var setAddGoodsFl = "n";
    var setIntDivision = "";
    var setStrDivision = "";
    var setMileageUseFl = "n";
    var setCouponUseFl = "n";
    var setMinOrderCnt = 1;
    var setMaxOrderCnt = 0;
    var setStockFl = "n";
    var setSalesUnit = 1;
    var setDecimal= 0;
    var setGoodsPrice = 0;
    var setGoodsNo = '';
    var setMileageFl = '';
    var setControllerName = "";
    var setCartTabFl = "n";
    var setTemplate = "";
    var setFixedSales = "option";
    var setFixedOrderCnt = "option";


    /**
     * 최소수량 체크
     *
     * @param string keyNo 상품 배열 키값
     */
    this.input_count_change = function(inputName,goodsFl)
    {

        var frmId = "#"+$(inputName).closest("form").attr('id');
        if($(inputName).val()=='') {
            $(inputName).val('0');
        }

        var itemNo = $(inputName).data('key');
        if(itemNo) {
            var optionDisplay =itemNo.split(setIntDivision);
            var optionDisplay = optionDisplay[0];
        } else {
            var optionDisplay = "0";
        }

        if($("#option_display_item_"+optionDisplay+" input[name='couponApplyNo[]']").val()) {
            alert('쿠폰이 적용된 상품은 수량변경을 할 수 없습니다.\n쿠폰취소 후 수량을 변경해주세요.');
            $(inputName).val($(inputName).data('value'));
            return false;
        }

        if(goodsFl) {

            var nowCnt	= parseInt($('input.goodsCnt_'+itemNo).val());

            var minCnt = parseInt(setMinOrderCnt);
            var maxCnt = parseInt(setMaxOrderCnt);

            var stockFl		= setStockFl;
            var setStock	= parseInt($(inputName).data('stock'));
            if (((setStock > 0 &&  maxCnt ==0) || (setStock <= maxCnt)) && stockFl == 'y') {
                maxCnt = parseInt(setStock);
            }

            var salesUnit = parseInt(setSalesUnit);

        } else {

            var nowCnt	= parseInt($(inputName).val());
            var minCnt = 1;
            var salesUnit = 1;

            if($(inputName).data('stock-fl') =='1') var maxCnt =  parseInt($(inputName).data('stock'));
            else var maxCnt = 0 ;

        }

        if(parseInt( nowCnt % salesUnit) > 0 ) {
            alert(salesUnit+"개 단위로 묶음 주문 상품입니다.");
            $(inputName).val(minCnt);
        }

        if (nowCnt < minCnt && minCnt != 0 && minCnt != '' && typeof minCnt != 'undefined') {
            $(inputName).val(minCnt);
        }

        if (nowCnt > maxCnt && maxCnt != 0 && maxCnt != '' && typeof maxCnt != 'undefined') {

            if(parseInt( maxCnt % salesUnit) > 0 ) {
                alert("최대 주문 가능 수량을 확인해주세요.");
                $(inputName).val(minCnt);
            } else {
                $(inputName).val(maxCnt);
            }
        }

        $(inputName).data('value', $(inputName).val());

        if(setCartTabFl =='y' ) {
            $("#frmCartTabViewLayer input[class='"+$(inputName).attr('class')+"']").val($(inputName).val());
        }

        this.goods_calculate(frmId,goodsFl,itemNo, $(inputName).val());
    }


    /**
     * 수량 변경하기
     *
     * @param string inputName input box name
     * @param string modeStr up or dn
     * @param integer minCnt 최소수량
     * @param integer maxCnt 최대수량
     */
    this.count_change = function(inputName,goodsFl)
    {
        var frmId = "#"+$(inputName).closest("form").attr('id');

        var tmpStr =  $(inputName).val().split(setStrDivision);
        var modeStr =  tmpStr[0];
        var itemNo =  tmpStr[1];

        var optionDisplay =itemNo.split(setIntDivision);

        if($("#option_display_item_"+optionDisplay[0]+" input[name='couponApplyNo[]']").val()) {
            alert('쿠폰이 적용된 상품은 수량변경을 할 수 없습니다.\n쿠폰취소 후 수량을 변경해주세요.');
            return false;
        }

        var minCnt =parseInt(setMinOrderCnt);
        var maxCnt = parseInt(setMaxOrderCnt);

        var nowCnt	= parseInt($(inputName).closest('span.count').find('input').val());

        if(goodsFl) {

            var salesUnit = parseInt(setSalesUnit);

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
            } else {
                var maxCntChk = true;
                maxCnt = parseInt(maxCnt);
            }

            var stockFl		= setStockFl;
            var setStock	= parseInt($(inputName).closest('span.count').find('input').data('stock'));
            if (((setStock > 0 &&  maxCnt ==0) || (setStock <= maxCnt)) && stockFl == 'y') {
                maxCnt = setStock;
                var maxCntChk = true;
            }

        }
        else {
            var salesUnit = 1;
            minCnt = 1;

            if($(inputName).closest('span.count').find('input').data('stock-fl') =='1') {
                var maxCnt =  parseInt($(inputName).closest('span.count').find('input').data('stock'));
                var maxCntChk = true;
            }
            else var maxCnt = 0 ;

        }

        if (isNaN(nowCnt) === true) {
            var thisCnt	= minCnt;
        } else {
            if (modeStr == 'up') {
                if (maxCntChk === true && (nowCnt+salesUnit) > maxCnt) {
                    var thisCnt	= nowCnt;
                } else {
                    var thisCnt	= nowCnt + salesUnit;
                }
            } else if (modeStr == 'dn') {
                if (nowCnt > minCnt) {
                    var thisCnt	= nowCnt - salesUnit;
                }
                else{
                    var thisCnt = nowCnt;
                }
            }
        }

        var goodsCntInput =  $(inputName).closest('span.count').find('input');
        $(goodsCntInput).val(thisCnt);
        $(goodsCntInput).focus();
        $(goodsCntInput).data('value',thisCnt);

        if(setCartTabFl =='y' ) {
            $("#frmCartTabViewLayer input[class='"+$(goodsCntInput).attr('class')+"']").val(thisCnt);
        }

        this.goods_calculate(frmId,goodsFl,itemNo,thisCnt);
    }


    /**
     * 옵션에 따른 가격 출력
     *
     * @param integer optionNo 상품 배열 키값 (기본 0)
     */
    this.option_price_display = function(inputName)
    {
        var frmId = "#"+$(inputName).closest("form").attr('id');

        if(setOptionTextFl =='y') {
            if(!this.option_text_valid(frmId))
            {
                if(setOptionDisplayFl =='s') {
                    $(frmId+' select[name="optionSnoInput"]').val('');
                } else {
                    $(frmId+' select[name*="optionNo_"]').val('');
                    $(frmId+' select[name*="optionNo_"]').trigger('chosen:updated');
                }

                alert('선택한 옵션의 필수 텍스트 옵션 내용을 먼저 입력해주세요.');

                return false;
            }
            $(frmId+' input[name*="optionTextInput"]').val('');
        }


        if(setAddGoodsFl =='y') {
            if(!this.add_goods_valid(frmId))
            {
                if(setOptionDisplayFl =='s') {
                    $(frmId+' select[name="optionSnoInput"]').val('');
                } else {
                    $(frmId+' select[name*="optionNo_"]').val('');
                    $(frmId+' select[name*="optionNo_"]').trigger('chosen:updated');
                }

                alert('선택한 옵션의 필수 추가 상품 먼저 선택해주세요.');
                return false;
            }
        }


        if($(frmId+" input[name='selectGoodsFl']").length && $(frmId+" input[name='selectGoodsFl']").val()) {
            $(frmId+" div.option_display_area").html('');
        }

        if(setOptionDisplayFl =='s') {
            if ($(frmId+' select[name="optionSnoInput"] option:selected').val() != '') {
                var valTmp	= $(frmId+' select[name="optionSnoInput"] option:selected').val();
                $(frmId+' select[name="optionSnoInput"]').val('');
            }
        } else if(setOptionDisplayFl =='d') {
            var valTmp		= $(frmId+' input[name="optionSnoInput"]').val();
            $(frmId+' select[name*="optionNo_"]').val('');
            $(frmId+' select[name*="optionNo_"]').not(':eq(0)').attr('disabled',true);
            $(frmId+' select[name*="optionNo_"]').trigger('chosen:updated');
        }

        if (typeof valTmp == 'undefined') return false;

        var arrTmp	= new Array();
        var arrTmp	= valTmp.split(setStrDivision);
        var optionName = arrTmp[1].trim();
        var optionInput = arrTmp[0];

        var arrTmp	= optionInput.split(setIntDivision);

        if(setMileageUseFl =='y' && arrTmp[2]) {
            $(frmId+' input[name="set_goods_mileage"]').val(parseFloat(arrTmp[2].trim()));
        }


        if(arrTmp[3]) $(frmId+' input[name="set_goods_stock"]').val(parseFloat(arrTmp[3].trim()));

        var optionPrice = arrTmp[1].trim();
        var optionStock = parseFloat(arrTmp[3].trim());

        var displayOptionkey = arrTmp[0]+'_'+$.now();

        if($(frmId+" div.optionKey_"+arrTmp[0]).length > 0) {
            alert("이미 선택된 옵션입니다");
            return false;
        }
        else {
            var addHtml = "";
            var complied = _.template($('#optionTemplate'+setTemplate).html());
            addHtml += complied({
                displayOptionkey: displayOptionkey,
                optionSno: arrTmp[0],
                optionName:optionName,
                optionPrice: optionPrice,
                optionStock : optionStock
            });

            $(frmId+" div.option_display_area").append(addHtml);

            $(frmId+' div.optionKey_'+arrTmp[0] + '  .goods-cnt').on('click', function(e){
                setControllerName.count_change(this,1);
            });

            $(frmId+' div.optionKey_'+arrTmp[0] + '  button.delete-goods').on('click', function(e){
                setControllerName.remove_option($(this).data('key'));
            });

            if(setCartTabFl =='y') {
                var addHtml = "";
                var complied = _.template($('#optionTemplateCartTab').html());
                addHtml += complied({
                    displayOptionkey: displayOptionkey,
                    optionSno: arrTmp[0],
                    optionName:optionName,
                    optionPrice: optionPrice,
                    optionStock: optionStock
                });

                $("#frmCartTabViewLayer div.option_display_area").append(addHtml);

                $('#frmCartTabViewLayer div.optionKey_'+arrTmp[0] + '  .goods-cnt').on('click', function(e){
                    var datakey = $(this).val().split(setStrDivision);
                    $('#option_display_item_'+datakey[1]).find('button[class="'+$(this).attr('class')+'"]').click();
                });

                $('#frmCartTabViewLayer div.optionKey_'+arrTmp[0] + '  button.delete-goods').on('click', function(e){
                    $('#frmView #'+$(this).data('key')+'  button.delete-goods').click();
                });

                $("#frmCartTabViewLayer div.option_total_display_area").show();
            }

            this.goods_calculate(frmId,1, displayOptionkey, setMinOrderCnt);

            if(setCouponUseFl =='y') {
                if (typeof bindBtnOpenLayer !== 'undefined' && $.isFunction(bindBtnOpenLayer)) bindBtnOpenLayer();
            }

            $(frmId+" div.option_total_display_area").show();
            $(frmId+" div.end-price").show();
        }

    }

    this.option_select = function(inputName,thisKey, nextVal,stockViewFl)
    {
        var frmId = "#"+$(inputName).closest("form").attr('id');

        // 무한정 판매 여부
        var stockFl				= setStockFl;

        // 옵션의 갯수
        var optionCnt			= $(frmId+ ' input[name="optionCntInput"]').val();

        // 옵션 가격 출력 여부
        var optionPriceFl		= 'y';

        // 옵션 가격이 다른 경우 출력 여부
        var optionPriceDiffFl	= 'y';

        // 기본 상품 가격
        var defaultGoodsPrice	= parseFloat(setGoodsPrice);

        // 선택된 옵션
        var optionVal	= new Array();
        for (var i = 0; i <= thisKey; i++) {
            optionVal[i]	= $(frmId+' select[name="optionNo_'+i+'"]').val();
            // 선택값이 없는경우 disabled 처리
            if (optionVal[i] == '') {
                for (var j = (i+1); j <= optionCnt; j++) {
                    if (j != 0) {
                        $(frmId+' select[name="optionNo_'+j+'"]').attr('disabled',true);
                    }
                }

                return true;
            }
        }

        $.post('../goods/goods_ps.php',{'mode' : 'option_select', 'optionVal' : optionVal, 'optionKey' : thisKey, 'goodsNo' : setGoodsNo, 'mileageFl' :setMileageFl}, function(data){


            if (typeof data.optionSno == 'string') {
                $(frmId+' input[name=\'optionSnoInput\']').val(data.optionSno+setStrDivision+optionVal.join('/'));

                if($(frmId).data("form") =='cart') carttab_option_price_display();
                else setControllerName.option_price_display($(frmId+' input[name=\'optionSnoInput\']'));

                return true;
            } else {
                $(frmId+' input[name=\'optionSnoInput\']').val('');
            }

            for (var i = 0; i <= optionCnt; i++) {
                if (i <= data.nextKey) {
                    $(frmId+' select[name="optionNo_'+i+'"]').attr('disabled',false);
                    if (i == data.nextKey) {
                        $(frmId+' select[name="optionNo_'+i+'"]').html('');
                        var addSelectOption		= '';
                        for (var j = 0; j < data.nextOption.length; j++) {
                            if(data.optionViewFl[j] =='y') {
                                if(j == 0)
                                {
                                    // 옵션 선택명
                                    addSelectOption	+= '<option value="">= '+nextVal+' ' +'선택';

                                    // 마지막 옵션의 경우 가격, 재고 출력
                                    if ((parseInt(data.nextKey) + 1) == parseInt(optionCnt)) {
                                        if (optionPriceFl == 'y' && optionPriceDiffFl == 'y') {
                                            addSelectOption	+= ' : ' + '가격';
                                        }
                                        if (stockFl == 'y' && stockViewFl == 'y') {
                                            addSelectOption	+= ' : ' + '재고';
                                        }
                                    }

                                    addSelectOption	+= ' =</option>';
                                }

                                // 옵션값
                                addSelectOption	+= '<option value="'+data.nextOption[j]+'"';

                                // 마지막 옵션의 경우 재고 체크 (품절이면 disabled 처리)
                                if ((parseInt(data.nextKey) + 1) == parseInt(optionCnt) && ((stockFl == 'y' && data.stockCnt[j] <  setMinOrderCnt)  || data.optionSellFl[j] == 'n')) {
                                    addSelectOption	+= ' disabled="disabled"';
                                }

                                // 옵션값
                                addSelectOption	+= '>'+data.nextOption[j];

                                // 마지막 옵션의 경우 재고 체크 및 가격
                                if ((parseInt(data.nextKey) + 1) == parseInt(optionCnt)) {
                                    // 가격 출력여부

                                    if(parseInt(data.optionPrice[j]) !='0') {
                                        if(data.optionPrice[j] > 0 ) var addPlus = "+";
                                        else var addPlus = "";
                                        if (optionPriceFl == 'y' && optionPriceDiffFl == 'y') {
                                            addSelectOption	+= ' : '+addPlus+data.optionPrice[j].toString()+'';
                                        } else if (optionPriceFl == 'y' && optionPriceDiffFl == 'n' && defaultGoodsPrice != parseFloat(data.optionPrice[j])) {
                                            addSelectOption	+= ' ('+addPlus+data.optionPrice[j].toString()+')';
                                        }
                                    }

                                    // 재고 체크
                                    if ((stockFl == 'y' && data.stockCnt[j] < 1) || data.optionSellFl[j] == 'n') {
                                        addSelectOption	+= ' [' + '품절' + ']';
                                    } else if (stockFl == 'y' && stockViewFl == 'y') {
                                        addSelectOption	+= ' : '+numeral(data.stockCnt[j]).format()+'개';
                                    }
                                }

                                // 옵션값
                                addSelectOption	+= '</option>';
                            }
                        }

                        $(frmId+' select[name="optionNo_'+i+'"]').html(addSelectOption);
                    }
                } else {
                    if (i != 0) {
                        $(frmId+' select[name="optionNo_'+i+'"]').attr('disabled',true);
                    }
                }

                $(frmId+' select[name="optionNo_'+i+'"]').trigger('chosen:updated');
            }


        }, 'json');
    }


    /**
     * 옵션 삭제
     *
     * @param optionId 삭제 옵션 아이디
     */
    this.remove_option = function(optionId) {

        var frmId = "#"+$("#"+optionId).closest("form").attr('id');

        $("#"+optionId).remove();
        if (typeof total_calculate !== 'undefined' && $.isFunction(total_calculate)) total_calculate();

        var ontionCnt = $('div[id*=\'option_display_item_\']').length;
        if(ontionCnt == 0) {
            $(frmId+" div.option_total_display_area").hide();
            $(frmId+" div.end-price").hide();
        }


        if(setCartTabFl =='y') {
            $("#frmCartTabViewLayer ."+optionId).remove();
        }
    }


    /**
     * 텍스트 옵션 선택
     */
    this.option_text_select = function(inputName) {

        var frmId = "#"+$(inputName).closest("form").attr('id');

        var optionText = '';
        var optionTextPrice = 0;
        var optionTextTotalPrice = 0;
        var optionTextSno = '';
        var optionTextKey = "";

        var displayOptionDisplay = $(frmId+' div[id*=\'option_display_item_\']').last().attr('id');

        if(displayOptionDisplay)
        {
            var checkKey = $(frmId+' #'+displayOptionDisplay+' div.check').attr('class').replace("check","").trim();
            var displayOptionkey = displayOptionDisplay.replace("option_display_item_", "");


            if(setOptionFl =='y') {
                var optionItemNo = $(frmId+' div[id*=\'option_display_item_\']').length-1;
            } else {
                var optionItemNo = 0;
            }

            $(frmId+' input[name*=\'optionTextInput\']').each(function (key) {
                if ($(this).val()) {

                    var optionTextLimit =  $(frmId+' input[name="optionTextLimit_'+key+'"]').val();
                    if($(this).val().length > optionTextLimit) {
                        $(this).val($(this).val().substring(0,optionTextLimit));
                    }

                    var optionValue = $(this).val();

                    optionTextPrice =parseFloat($(this).next('input').val());
                    optionTextSno += '<input type="hidden"  value="' + optionValue + '" name="optionText[' + optionItemNo + '][' + $(this).prev('input').val() + ']">';


                    var optionTextNm = $(frmId+' span.optionTextNm_'+key).text();
                    var optionDisplayText = optionTextNm+' : '+optionValue+' <b>('+gdCurrencySymbol+optionTextPrice+gdCurrencyString+')</b>';
                    optionTextSno += '<em>'+optionDisplayText+'</em>';


                    for (var i = 0, m = optionValue.length; i < m; i++) {
                        optionTextKey += optionValue.charCodeAt(i);
                    }

                } else {
                    optionTextSno +='';
                    optionTextPrice = 0;
                }

                optionTextTotalPrice += optionTextPrice;

            });


            var tmpStr = displayOptionkey.split('_');


            if($(frmId+' div.optionKey_'+tmpStr[0]+optionTextKey).length) {
                alert("이미 선택된 옵션입니다");
                return false;
            } else {
                if(optionTextKey)   $('#'+displayOptionDisplay+' div.'+checkKey +' .name strong').addClass('btm-txt');
                else  $('#'+displayOptionDisplay+' div.'+checkKey +' .name strong').removeClass('btm-txt');

                optionText = optionTextSno + '<input type="hidden" value="' + optionTextTotalPrice + '" name="optionTextPriceSum[]" ><input type="hidden" value="' + optionTextTotalPrice + '" name="option_text_price_' + displayOptionkey + '"></div>';

                $('#option_text_display_' + displayOptionkey).html(optionText);
                $('#'+displayOptionDisplay+' div.'+checkKey).attr('class', 'check optionKey_' + tmpStr[0] + optionTextKey);

                if(setCartTabFl =='y') {
                    $('#frmCartTabViewLayer .option_text_display_' + displayOptionkey).html(optionText);
                    $('#frmCartTabViewLayer .'+displayOptionDisplay+' div.'+checkKey).attr('class', 'this optionKey_' + tmpStr[0] + optionTextKey);
                }

                var goodsCnt = $(frmId+" input.goodsCnt_"+displayOptionkey).val();
                this.goods_calculate(frmId,1, displayOptionkey, goodsCnt);
            }

        }
        else {
            alert("옵션을 먼저 선택해주세요");
            $(frmId+' input[name*=\'optionTextInput\']').val('');
            return false;
        }

    }

    /**
     * 텍스트 옵션 유효성체크
     */
    this.option_text_valid = function(frmId) {

        if($(frmId+' input[name="optionSno[]"]').length) {
            var returnFl = true;

            $(frmId+' input[name="optionSno[]"]').each(function (key) {

                $(frmId +' input[name*=\'optionTextInput\']').each(function (textKey) {

                    var optionTextSno = $(frmId +' input[name="optionTextSno_'+textKey+'"]').val();
                    var optionText = $(frmId+' input[name="optionText[' + key + '][' + optionTextSno + ']"]');


                    if ( $(frmId +' input[name="optionTextMust_'+textKey+'"]').val() == 'y') {

                        if (optionText.length == '0') {
                            $(frmId +' input[name="optionTextInput_'+textKey+'"]').focus();
                            returnFl =  false;
                        }
                    }
                });
            });
            return returnFl;

        }
        else return true;

    }

    this.add_goods_select = function(inputName) {

        var frmId = "#"+$(inputName).closest("form").attr('id');
        var selAddGoods = $(inputName).data('key');

        if ($(frmId+" select[name='addGoodsInput"+selAddGoods+"']").val() != '') {


            var displayOptionDisplay = $(frmId+' div[id*=\'option_display_item_\']').last().attr('id');

            if(displayOptionDisplay)
            {

                var displayOptionkey = displayOptionDisplay.replace("option_display_item_", "");

                if ($('#'+displayOptionDisplay + ' .add').length == 0) {
                    $('#'+displayOptionDisplay).append('<div class="add"></div>');
                }


                var addGoods = $(frmId+" select[name='addGoodsInput"+selAddGoods+"']");

                if(!addGoods.val()){
                    return;
                }
                var arrTmp = new Array();
                var arrTmp = addGoods.val().split(setStrDivision);
                var addGoodsName = arrTmp[1].trim();
                var addGoodsimge = decodeURIComponent(arrTmp[2].trim());
                var addGoodsGroup = arrTmp[3].trim();
                var addGoodsValue = arrTmp[0];
                var addGoodsStockFl = arrTmp[4];
                var addGoodsStock = arrTmp[5];

                var arrTmp = addGoodsValue.split(setIntDivision);


                var displayAddGoodsKey = arrTmp[0];
                var optionIndex = $(frmId+ ' #'+displayOptionDisplay).index();
                if($(frmId+ ' #add_goods_display_item_' + displayOptionkey + '_' + displayAddGoodsKey).length) {
                    alert("이미 선택된 추가상품 입니다.");
                    return false;
                } else {

                    var addHtml='';
                    var complied = _.template($('#addGoodsTemplate'+setTemplate).html());
                    addHtml += complied({
                        displayOptionkey: displayOptionkey,
                        displayAddGoodsKey: displayAddGoodsKey,
                        addGoodsimge:addGoodsimge,
                        addGoodsGroup:addGoodsGroup,
                        optionIndex: optionIndex,
                        optionSno: arrTmp[0],
                        addGoodsName: addGoodsName,
                        addGoodsStockFl: addGoodsStockFl,
                        addGoodsStock: addGoodsStock,
                        addGoodsPrice:  parseFloat(arrTmp[1].trim())
                    });


                    $(frmId+ ' #option_display_item_' + displayOptionkey + ' .add').append(addHtml);


                    $(frmId+ ' #add_goods_display_item_'+displayOptionkey + '_' + displayAddGoodsKey+' .add-goods-cnt').on('click', function(e){
                        setControllerName.count_change(this,0);
                    });

                    $(frmId+ ' #add_goods_display_item_'+displayOptionkey + '_' + displayAddGoodsKey+'  button.delete-add-goods').on('click', function(e){
                        setControllerName.remove_add_goods(displayOptionkey,displayAddGoodsKey);
                    });

                    if(setCartTabFl =='y') {


                        var addHtml = "";
                        var complied = _.template($('#addGoodsTemplateCartTab').html());
                        addHtml += complied({
                            displayOptionkey: displayOptionkey,
                            displayAddGoodsKey: displayAddGoodsKey,
                            addGoodsimge:addGoodsimge,
                            addGoodsGroup:addGoodsGroup,
                            optionIndex: optionIndex,
                            optionSno: arrTmp[0],
                            addGoodsName: addGoodsName,
                            addGoodsPrice:  parseFloat(arrTmp[1].trim())
                        });


                        $('#frmCartTabViewLayer .option_display_item_' + displayOptionkey).append(addHtml);

                        $('#frmCartTabViewLayer  .add_goods_display_item_'+displayOptionkey + '_' + displayAddGoodsKey+' .add-goods-cnt').on('click', function(e){
                            $('#add_goods_display_item_'+displayOptionkey + '_' + displayAddGoodsKey).find('button[class="'+$(this).attr('class')+'"]').click();
                        });

                        $('#frmCartTabViewLayer .add_goods_display_item_'+displayOptionkey + '_' + displayAddGoodsKey+'  button.delete-add-goods').on('click', function(e){
                            $('#add_goods_display_item_'+displayOptionkey + '_' + displayAddGoodsKey+'  button.delete-add-goods').click();

                        });
                    }


                    var itemNo = displayOptionkey + setIntDivision + displayAddGoodsKey;

                    this.goods_calculate(frmId,0, itemNo, 1);

                    if(setCouponUseFl =='y') {
                        if (typeof bindBtnOpenLayer !== 'undefined' && $.isFunction(bindBtnOpenLayer)) bindBtnOpenLayer();
                    }

                    addGoods.val('');
                }
            }
            else
            {
                alert("옵션을 먼저 선택해주세요.");
                $(frmId+' select[name*=\'addGoodsInput\']').val('');
                return false;
            }
        }
    }


    /**
     * 추가상품 유효성검사
     */
    this.add_goods_valid = function(frmId) {
        if($(frmId+' input[name="addGoodsInputMustFl[]"]').length) {

            if($(frmId+' input[name="optionSno[]"]').length) {

                var returnCnt = 0;
                $(frmId+" div[id*='option_display_item_']").each(function (key) {

                    var mustFl = false;
                    $("#"+$(this).attr('id')+" input[name='addGoodsNo["+key+"][]']").each(function (textKey) {
                        var group = $(this).data('group');

                        $(frmId+' input[name="addGoodsInputMustFl[]"]').each(function () {
                            if($(this).val() == group) mustFl = true;
                        });
                    });

                    if(mustFl) returnCnt++;

                });

                if(returnCnt == $(frmId+" div[id*='option_display_item_']").length) return true;
                else return false;
            }
            else return true;

        } else return true;

    }

    /**
     * 추가상품 삭제
     */
    this.remove_add_goods= function(optionId,addGoodsId) {
        $("#add_goods_display_item_"+optionId+"_"+addGoodsId).remove();
        if (typeof total_calculate !== 'undefined' && $.isFunction(total_calculate))   total_calculate();

        var addGoodsCnt = $('div[id=\'option_display_item_'+optionId+'\']').find('div[id*=\'add_goods_display_item_\']').length;

        if(addGoodsCnt == 0) $('div[id=\'option_display_item_'+optionId+'\']').find('.add').remove();

        var setAddGoodsPrice =  0;

        $("#option_display_item_"+optionId+" input[name*='add_goods_total_price']").each(function () {
            setAddGoodsPrice += parseFloat($(this).val());
        });

        $("#option_display_item_"+optionId+" input[name='addGoodsPriceSum[]']").val(setAddGoodsPrice);


        if(setCartTabFl =='y') {
            $("#frmCartTabViewLayer .add_goods_display_item_"+optionId+"_"+addGoodsId).remove();
        }
    }


    /**
     * 상품 가격 계산
     * @param integer goodsFl 1: 상품 0:추가상품
     * @param integer itemNo 선택상품명
     * @param integer goodsCnt 상품 갯수
     */
    this.goods_calculate = function(frmId,goodsFl,itemNo,goodsCnt) {

        var goodsPrice = parseFloat($(frmId+' input[name="set_goods_price"]').val());

        if(goodsFl)
        {
            var optionTextPrice = 0;
            if(setOptionTextFl =='y') {
                if($(frmId+' input[name="option_text_price_'+itemNo+'"]').length) optionTextPrice = parseFloat($(frmId+' input[name="option_text_price_'+itemNo+'"]').val());
            }

            var optionPrice = parseFloat($(frmId+' input[name="option_price_'+itemNo+'"]').val());

            var optionTotalPrice = (optionTextPrice)+(optionPrice);

            $(frmId+' .option_price_display_' + itemNo).html( ((optionTotalPrice+goodsPrice)*goodsCnt).toFixed(setDecimal));

            if(setCartTabFl =='y') {
                $('#frmCartTabViewLayer .option_price_display_' + itemNo).html( ((optionTotalPrice+goodsPrice)*goodsCnt).toFixed(setDecimal));
            }

            $('#option_display_item_' + itemNo + ' input[name="optionPriceSum[]"]').val((optionPrice*goodsCnt).toFixed(setDecimal));
            $('#option_display_item_' + itemNo + ' input[name="optionTextPriceSum[]"]').val((optionTextPrice*goodsCnt).toFixed(setDecimal));

            //var goodsPrice = parseFloat($('#set_goods_price').val());
            $("#option_display_item_"+itemNo+" input[name='goodsPriceSum[]']").val((goodsPrice*goodsCnt).toFixed(setDecimal));

        }
        else
        {
            var tmpStr = itemNo.split(setIntDivision);
            itemNo = tmpStr[0];
            var addGoodsItemNo =  tmpStr[1];
            var addGoodsPrice = parseFloat($(frmId+' input[name="add_goods_price_'+ itemNo+'_'+addGoodsItemNo+'"]').val());
            var addGoodsTotalPrice = parseFloat(addGoodsPrice*goodsCnt);

            $(frmId+' .add_goods_price_display_' + itemNo+'_'+addGoodsItemNo).html(addGoodsTotalPrice.toFixed(setDecimal));

            if(setCartTabFl =='y') {
                $('#frmCartTabViewLayer .add_goods_price_display_' + itemNo+'_'+addGoodsItemNo).html(addGoodsTotalPrice.toFixed(setDecimal));
            }


            $('#add_goods_display_item_'+ itemNo+'_'+addGoodsItemNo+' input[name*="add_goods_total_price"]').val(addGoodsTotalPrice);

            var setAddGoodsPrice =  0;

            $("#option_display_item_"+itemNo+" input[name*='add_goods_total_price']").each(function () {
                setAddGoodsPrice += parseFloat($(this).val());
            });

            $("#option_display_item_"+itemNo+" input[name='addGoodsPriceSum[]']").val(setAddGoodsPrice);
        }

        if (typeof total_calculate !== 'undefined' && $.isFunction(total_calculate)) total_calculate();
    }


    this.init = function(param)
    {
        setOptionTextFl = param.setOptionTextFl;
        setOptionDisplayFl = param.setOptionDisplayFl;
        setAddGoodsFl =param.setAddGoodsFl;
        setIntDivision =param.setIntDivision;
        setStrDivision =param.setStrDivision;
        setMileageUseFl =param.setMileageUseFl;
        setCouponUseFl = param.setCouponUseFl;
        setStockFl = param.setStockFl;
        setDecimal = param.setDecimal;
        setOptionFl = param.setOptionFl;
        setGoodsPrice = param.setGoodsPrice;
        setGoodsNo = param.setGoodsNo;
        setMileageFl = param.setMileageFl;
        setControllerName = param.setControllerName;
        setFixedSales = param.setFixedSales;
        setFixedOrderCnt = param.setFixedOrderCnt;
        if(param.setTemplate) {
            setTemplate = param.setTemplate;
        }
        if (setFixedSales != 'goods') {
            setSalesUnit = parseInt(param.setSalesUnit);
            if (setSalesUnit > 1) {
                setMinOrderCnt = parseInt(param.setSalesUnit);
            } else {
                if (setFixedOrderCnt != 'goods') {
                    setMinOrderCnt = parseInt(param.setMinOrderCnt);
                }
            }
        }
        if (setFixedOrderCnt != 'goods') {
            setMaxOrderCnt = parseInt(param.setMaxOrderCnt);
        }


    }
    this.initCartTab = function(cartTabFl)
    {
        setCartTabFl = cartTabFl;
    }

}
