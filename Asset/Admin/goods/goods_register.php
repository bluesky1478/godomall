<?php
//펼침,닫힘 정보
$toggle = gd_policy('display.toggle');
$SessScmNo = Session::get('manager.scmNo');

// --- 상품 설정 config 불러오기
if ($data['mode'] == 'modify') {
    $goodsConfig = (gd_policy('goods.display'));
    $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');

    // 상품 수정일 업데이트 설정
    if (gd_is_provider() === false) {
        $goodsModDtTypeName = gd_isset($goodsConfig['goodsModDtTypeUp'], 'y');
    } else {
        $goodsModDtTypeName = gd_isset($goodsConfig['goodsModDtTypeScm'], 'y');
    }
}
?>
<script type="text/javascript" xmlns="http://www.w3.org/1999/html">
    <!--
    $(document).ready(function () {
        // 상품 등록 / 수정 처리
        $("#frmGoods").validate({
            submitHandler: function (form) {
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                oEditors.getById["editor2"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.

                if (typeof oEditors.getById["detailInfoDeliveryDirectInput"] != 'undefined') oEditors.getById["detailInfoDeliveryDirectInput"].exec("UPDATE_CONTENTS_FIELD", []);
                if (typeof oEditors.getById["detailInfoASDirectInput"] != 'undefined') oEditors.getById["detailInfoASDirectInput"].exec("UPDATE_CONTENTS_FIELD", []);
                if (typeof oEditors.getById["detailInfoRefundDirectInput"] != 'undefined') oEditors.getById["detailInfoRefundDirectInput"].exec("UPDATE_CONTENTS_FIELD", []);
                if (typeof oEditors.getById["detailInfoExchangeDirectInput"] != 'undefined') oEditors.getById["detailInfoExchangeDirectInput"].exec("UPDATE_CONTENTS_FIELD", []);

                if ($('input[name=goodsPermission]:checked').val() != 'all' && $('input[name=goodsPermissionPriceStringFl]:checked').val() =='y' && $('input[name=goodsPermissionPriceString]').val() =='' ) {
                    alert("구매불가 고객 가격 대체문구를 입력하세요");
                    return false;
                }

                if ($('input[name=payLimitFl]:checked').val() === 'y' && $('input[name*=payLimit]:checkbox:checked').length == 0) {
                    alert("결제수단 > 개별설정을 확인해주세요.");
                    return false;
                }

                if ($("input[name='salesDateFl']:checked").val() == 'y' && $("input[name='salesDate[]']").eq(0).val() && $("input[name='salesDate[]']").eq(1).val() && $("input[name='salesDate[]']").eq(0).val() > $("input[name='salesDate[]']").eq(1).val()) {
                    alert("판매기간의 시작일/종료일을 확인해주세요.");
                    return false;
                }

                if ($("input[name='effectiveStartYmd']").val() && $("input[name='effectiveEndYmd']").val() && $("input[name='effectiveStartYmd']").val() > $("input[name='effectiveEndYmd']").val()) {
                    alert("유효일자의 종료일은 시작일 이후로 설정해 주세요.");
                    return false;
                }

                if ($("input[name='goodsIconStartYmd']").val() && $("input[name='goodsIconEndYmd']").val() && $("input[name='goodsIconStartYmd']").val() > $("input[name='goodsIconEndYmd']").val()) {
                    alert("기간제한용 아이콘의 종료일은 시작일 이후로 설정해 주세요.");
                    return false;
                }

                if ($("input[name='salesUnit']").val() < 1) {
                    alert("묶음주문단위는 1개 이하로 설정할수 없습니다.");
                    return false;
                }

                var infoTitleFl = true,
                    infoValueFl = true;
                $.each($('#addMustInfo tbody > tr[id^="addMustInfo"]'), function(index) {
                    for (var i = 0; i <= $('input[name="addMustInfo[infoTitle][' + index + '][]"]').length; i++) {
                        var infoTitle = $('input[name="addMustInfo[infoTitle][' + index + '][]"]:eq(' + i + ')').val();
                        if (infoTitle && infoTitle.length > 60) {
                            infoTitleFl = false;
                            return false;
                        }
                    }
                    for (var i = 0; i <= $('input[name="addMustInfo[infoValue][' + index + '][]"]').length; i++) {
                        var infoValue = $('input[name="addMustInfo[infoValue][' + index + '][]"]:eq(' + i + ')').val();
                        if (infoValue && infoValue.length > 500) {
                            infoValueFl = false;
                            return false;
                        }
                    }
                });
                if (infoTitleFl === false) {
                    alert('필수정보 항목명은 60자를 넘을 수 없습니다.');
                    return false;
                }
                if (infoValueFl === false) {
                    alert('필수정보 항목내용은 500자를 넘을 수 없습니다.');
                    return false;
                }

                if ($("input[name='optionFl']:checked").val() == 'y') {

                    if($('#optionY_optionCnt').val()) {
                        var fieldCnt = $('#option').find('input[id*=\'option_optionName_\']').length;

                        for (var i = 0; i < fieldCnt; i++) {
                            if ($('#option_optionName_'+i).val() == '') {
                                alert('옵션을 등록해 주세요!');
                                return false;
                            }

                            var optionValueCnt = $("input[id*='option_optionValue_"+i+"']").length;

                            if (optionValueCnt > 0 || optionValueCnt != '') {
                                for (var j = 0; j < optionValueCnt; j++) {
                                    if ($('#option_optionValue_'+i+'_'+j).val() == '') {
                                        alert('옵션값을 넣어주세요!');
                                        return false;
                                    }
                                }
                            }
                        }
                    } else {
                        alert('옵션을 등록해 주세요!');
                        return false;
                    }
                }
                if ($('input[name="mileageFl"]:checked').val() == 'g') {
                    if ($('input[name="mileageGroup"]:checked').val() == 'group') {
                        var mileageFl = true;
                        var mileageDiscountFl = true;
                        $('input[name="mileageGroupMemberInfo[\'mileageGoods\'][]"]').each(function(index){
                            if ($('select[name="mileageGroupMemberInfo[\'groupSno\'][]"]').eq(index).val() == '' || $(this).val() == '' || parseFloat($(this).val()) <= 0) {
                                mileageFl = false;
                                return false;
                            }
                            if ($('select[name="mileageGroupMemberInfo[\'mileageGoodsUnit\'][]"]').eq(index).val() == 'percent' && parseFloat($(this).val()) > 100) {
                                mileageDiscountFl = false;
                                return false;
                            }
                        });
                        if (mileageFl === false) {
                            alert('마일리지 설정의 금액설정 항목을 입력하세요.');
                            return false;
                        }
                        if (mileageDiscountFl === false) {
                            alert('마일리지 지급금액은 100%를 초과할 수 없습니다.');
                            return false;
                        }
                    } else {
                        if ($('select[name="mileageGoodsUnit"]').val() == 'percent' && parseFloat($('input[name="mileageGoods"]').val()) > 100) {
                            alert('마일리지 지급금액은 100%를 초과할 수 없습니다.');
                            return false;
                        }
                    }
                } else if ($('input[name="mileageFl"]:checked').val() == 'c' && $('input[name="mileageGroup"]:checked').val() == 'group') {
                    if (!$('input[name="mileageGroupInfo[]"]').length) {
                        alert('마일리지 지급 대상 회원등급을 선택해주세요');
                        return false;
                    }
                }

                if ($('input[name="goodsBenefitSetFl"]:checked').val() == 'y') {
                    var benefitSno = $('input[name="benefitSno"]').val();
                    if ( typeof benefitSno == "undefined" || benefitSno == "" ){
                        alert('상품 혜택을 선택해주세요');
                        return false;
                    }
                }


                if ($('input[name="goodsDiscountFl"]:checked').val() == 'y') {
                    if ($('input[name="goodsDiscountGroup"]:checked').val() == 'group') {
                        var discountFl = true;
                        var goodsDiscountFl = true;
                        $('input[name="goodsDiscountGroupMemberInfo[\'goodsDiscount\'][]"]').each(function(index){
                            if ($('select[name="goodsDiscountGroupMemberInfo[\'groupSno\'][]"]').eq(index).val() == '' || $(this).val() == '' || parseFloat($(this).val()) <= 0) {
                                discountFl = false;
                                return false;
                            }
                            if ($('select[name="goodsDiscountGroupMemberInfo[\'goodsDiscountUnit\'][]"]').eq(index).val() == 'percent' && parseFloat($(this).val()) > 100) {
                                goodsDiscountFl = false;
                                return false;
                            }
                        });
                        if (discountFl === false) {
                            alert('상품 할인 설정의 금액설정 항목을 입력하세요.');
                            return false;
                        }
                        if (goodsDiscountFl === false) {
                            alert('상품 할인 금액은 100%를 초과할 수 없습니다.');
                            return false;
                        }
                    } else {
                        if ($('select[name="goodsDiscountUnit"]').val() == 'percent' && parseFloat($('input[name="goodsDiscount"]').val()) > 100) {
                            alert('상품 할인 금액은 100%를 초과할 수 없습니다.');
                            return false;
                        }
                    }
                }
                if ($('input[name="exceptBenefit[]"]:checked').length > 0 && $('input[name="exceptBenefitGroup"]:checked').val() == 'group') {
                    if (!$('input[name="exceptBenefitGroupInfo[]"]').length) {
                        alert('상품 할인/적립 혜택 제외 회원등급을 선택해주세요');
                        return false;
                    }
                }

                if ($('input[name="benefitUseType"]:checked').val() == 'newGoodsDiscount' && $('input[name="goodsBenefitSetFl"]:checked').val() != 'y') {
                    if ($('input[name="newGoodsDate"]') == '' || $('input[name="newGoodsDate"]').val() < 1) {
                        alert('신상품 할인 기간을 입력해주세요');
                        return false;
                    }
                }

                if ($('input[name="benefitUseType"]:checked').val() == 'periodDiscount' && $('input[name="goodsBenefitSetFl"]:checked').val() != 'y') {

                    var discountStart = $('input[name="periodDiscountStart"]').val();
                    var discountEnd = $('input[name="periodDiscountEnd"]').val()
                    if(discountStart == '' || discountEnd == ''){
                        alert('특정기간 할인의 시작일/종료일을 확인해 주세요');
                        return false;
                    }
                    var sdt = dateGetTime(discountStart);
                    var edt = dateGetTime(discountEnd);
                    if (sdt >= edt) {
                        alert('특정기간 할인의 시작일/종료일을 확인해 주세요');
                        return false;
                    }
                }

                $("input[name='goodsPermissionPriceStringFl']").prop("disabled",false);
                $("input[name='goodsAccessDisplayFl']").prop("disabled",false);
                $("input[name='onlyAdultDisplayFl']").prop("disabled",false);
                $("input[name='onlyAdultImageFl']").prop("disabled",false);

                if($("input[name='stockCnt']").val() > 0 && $("input[name='stockFl']:checked").val() =='n') {

                    dialog_confirm('상품재고가 등록되었습니다.\n 판매재고를 "재고량에 따름"으로 변경 후 상품 정보를 저장하시겠습니까?', function (result) {
                        if (result) {
                            $("input[name='stockFl'][value='y']").prop("checked",true);
                        }

                        //상품수정일 변경 확인 팝업
                        <?php if ($data['mode'] == 'modify') { ?>
                        <?php if ($goodsModDtTypeName == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                        dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result) {
                            if (result) {
                                $("[name='modDtUse']").val('y');
                            } else {
                                $("[name='modDtUse']").val('n');
                            }
                            form.target = 'ifrmProcess';
                            form.submit();
                        }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                        <?php } else { ?>
                        //상품 수정일 변경 범위설정 체크
                        <?php if ($goodsModDtTypeName == 'y') { ?>
                        $("[name='modDtUse']").val('y');
                        <?php } else { ?>
                        $("[name='modDtUse']").val('n');
                        <?php } ?>
                        form.target = 'ifrmProcess';
                        form.submit();
                        <?php } ?>
                        <?php } else { ?>
                        form.target = 'ifrmProcess';
                        form.submit();
                        <?php } ?>
                    },'확인',{"cancelLabel":'아니요',"confirmLabel":'예'});
                    return false;
                } else {
                    //상품수정일 변경 확인 팝업
                    <?php if ($data['mode'] == 'modify') { ?>
                    <?php if ($goodsModDtTypeName == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                    dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result) {
                        if (result) {
                            $("[name='modDtUse']").val('y');
                        } else {
                            $("[name='modDtUse']").val('n');
                        }
                        form.target = 'ifrmProcess';
                        form.submit();
                    }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                    return false;
                    <?php } else { ?>
                    //상품 수정일 변경 범위설정 체크
                    <?php if ($goodsModDtTypeName == 'y') { ?>
                    $("[name='modDtUse']").val('y');
                    <?php } else { ?>
                    $("[name='modDtUse']").val('n');
                    <?php } ?>
                    form.target = 'ifrmProcess';
                    form.submit();
                    <?php } ?>
                    <?php } else { ?>
                    form.target = 'ifrmProcess';
                    form.submit();
                    <?php } ?>
                }

            },
            // onclick: false, // <-- add this option
            rules: {
                goodsNm: 'required',
                deliverySno: 'required',
            },
            messages: {
                goodsNm: {
                    required: '상품명을 입력하세요.'
                },
                deliverySno: {
                    required: '배송비를 선택해주세요.'
                },
            }
        });


        $('#imageStorage').trigger('click');
        image_storage_selector('<?=$data['imageStorage'];?>');
        <?php
        if ($data['goodsNmFl'] == 'e') {
            echo '	display_toggle(\'goodsNmExt\',\'show\');' . chr(10);
        }
        if ($data['optionFl'] == 'y') {
            echo '	display_toggle(\'optionExist\',\'show\');' . chr(10);
            if ($data['optionCnt'] > 0) {
                echo '	fill_option();' . chr(10);
            }
            echo '	disabled_switch(\'stockCnt\',true);' . chr(10);
        }
        if ($data['optionTextFl'] == 'y') {
            echo '	display_toggle(\'optionTextDiv\',\'show\');' . chr(10);
        }
        if ($data['taxFreeFl'] == 'f') {
            echo '	disabled_switch(\'taxPercent\',true);' . chr(10);
        }
        if ($data['maxOrderChk'] == 'n') {
            echo '	disabled_switch(\'fixedOrderCnt\',true);' . chr(10);
            echo '	disabled_switch(\'minOrderCnt\',true);' . chr(10);
            echo '	disabled_switch(\'maxOrderCnt\',true);' . chr(10);
        }

        if ($data['salesDateFl'] == 'n') {
            echo '	disabled_switch(\'salesDate[]\',true);' . chr(10);
        }

        if ($data['mileageFl'] == 'c') {
            echo '	display_toggle(\'mileageBasic\',\'show\');' . chr(10);
            echo '	display_toggle(\'mileageGoodsConfig\',\'hide\');' . chr(10);
        } else if ($data['mileageFl'] == 'g') {
            echo '	display_toggle(\'mileageBasic\',\'hide\');' . chr(10);
            echo '	display_toggle(\'mileageGoodsConfig\',\'show\');' . chr(10);
        }

        if ($data['goodsDiscountFl'] == 'y') {
            echo '	display_toggle(\'goodsDiscountConfig\',\'show\');' . chr(10);
        }

        if ($data['payLimitFl'] == 'n') {
            echo '	display_toggle(\'payBasic\',\'show\');' . chr(10);
            echo '	display_toggle(\'payLimitConfig\',\'hide\');' . chr(10);
        } else if ($data['payLimitFl'] == 'y') {
            echo '	display_toggle(\'payBasic\',\'hide\');' . chr(10);
            echo '	display_toggle(\'payLimitConfig\',\'show\');' . chr(10);
        }

        if ($data['addGoodsFl'] == 'y') {
            echo '	$(\'#addGoodsGroupTitleInfo\').show();' . chr(10);
            echo '	select_add_goods_group(0);' . chr(10);
        }

        if ($data['imgDetailViewFl'] == 'y') {
            echo '	display_toggle(\'imgDetailViewDesc\',\'show\');' . chr(10);
        }

        if ($data['externalVideoFl'] == 'y') {
            echo '	display_toggle(\'useExternalVideoInfo\',\'show\');' . chr(10);
        }

        if ($data['scmNo']) {
            echo '	setScmInfo();' . chr(10);
        }
        ?>

        <?php  if ($data['mode'] == "register" && empty($data['image']) === true) {?>
        $("input[name='imageResize[original]']").prop("checked",true);
        image_resize_check_all('imageResize[original]');
        <?php } ?>

        relation_switch('<?=$data['relationFl'];?>');
        setDsiabledRelatedGoods('<?=$data['relationSameFl']?>');
        //add_data_sortable('relationGoodsInfo');		// 관련 상품 이동 소트
        setCommissionPrice();

        $('input[name*=\'optionCnt\']').number_only();
        $('input[name=\'goodsPrice\']').number_only();
        $('input[name=\'fixedPrice\']').number_only();
        $('input[name=\'costPrice\']').number_only();
        $('input[name*=\'mileageGoods\']').number_only();
        $('input[name*=\'stockCnt\']').number_only();
        $('input[name*=\'addPrice\']').number_only();
        $('input[name*=\'inputLimit\']').number_only(4, 100, 100);

        $('#option_optionPriceApply').number_only();
        $('#option_stockCntApply').number_only();

        $('input[name=\'minOrderCnt\']').number_only();
        $('input[name=\'maxOrderCnt\']').number_only();
        $('input[name=\'goodsWeight\']').number_only(8, 99999.99, 99999.99, 2);
        $('input[name=\'goodsVolume\']').number_only(8, 99999.99, 99999.99, 2);
        $('input[name=\'taxPercent\']').number_only(4, 100, 100);
        $('input[name=\'relationCnt\']').number_only(4, 100, 100);

        <?php if (gd_isset($conf['mobile']['mobileShopFl']) == 'y') {?>
        // 상세 설명 전환
        $("#btnDescriptionShop, #btnDescriptionMobile").click(function () {

            if (this.id == 'btnDescriptionShop') {
                $('#btnDescriptionShop').addClass('active');
                $('#btnDescriptionMobile').removeClass('active');
                $("#textareaDescriptionShop").show();
                $("#textareaDescriptionMobile").hide();
            } else {
                if($("input[name='goodsDescriptionSameFl']").prop('checked') == false) {
                    $('#btnDescriptionShop').removeClass('active');
                    $('#btnDescriptionMobile').addClass('active');
                    $("#textareaDescriptionShop").hide();
                    $("#textareaDescriptionMobile").show();
                }
            }
            return false;
        });


        $("input[name='goodsDescriptionSameFl']").click(function () {
            if($("input[name='goodsDescriptionSameFl']").prop('checked')) {
                $("#btnDescriptionMobile").addClass("nav-none");
                $("#btnDescriptionMobile a").css("background","#F6F6F6");
                $("#btnDescriptionShop").click();
            } else {
                $("#btnDescriptionMobile").removeClass("nav-none");
                $("#btnDescriptionMobile a").css("background","");
            }
        });

        <?php }?>



        //카테고리 선택
        $('#btn_category_select').click(function () {

            var cateGoods = '';
            var cateName = new Array();

            $("#cateGoodsInfo thead, #cateGoodsInfo tbody").show();

            for (var i = 0; i <= <?=DEFAULT_DEPTH_CATE;?>; i++) {
                if ($('#cateGoods' + i).val()) {
                    var cate = $('#cateGoods' + i + " option:selected");
                    cateName[i] = cate.text();
                    if ($("#cateGoodsInfo" + cate.val()).length == 0) {
                        addHtml = "<tr id='cateGoodsInfo" + cate.val() + "'>";
                        <?php if ($gGlobal['isUse'] === true) { ?>
                        var flagHtml = [];
                        var tmpFlag = (cate.data('flag')).split(',');
                        var tmpMallName = (cate.data('mall-name')).split(',');
                        for(var f = 0 ; f < tmpFlag.length; f++) {
                            flagHtml.push('<span class="js-popover flag flag-16 flag-'+tmpFlag[f]+'" data-content="'+tmpMallName[f]+'"></span>');
                        }
                        addHtml +="<td>"+flagHtml.join("&nbsp;")+"</td>";
                        <?php } ?>
                        addHtml += "<td class='center'><input type='hidden' name='link[cateCd][]' value='" + cate.val() + "'><input type='hidden' name='link[cateLinkFl][]' value='y' id='cateLink_" + cate.val() + "'><input type='radio' name='cateCd' value='" + cate.val() + "'></td>";
                        addHtml += "<td>" + (cateName.join(' &gt; ')).replace('&gt;', '') + "</td>";
                        addHtml += "<td class='center'>" + cate.val() + "</td>";
                        addHtml += "<td class='center'><input type='button' class='btn btn-sm btn-white btn-icon-minus' onclick='field_remove(\"cateGoodsInfo" + cate.val() + "\")' value='삭제'></td>";


                        $("#cateGoodsInfo tbody").append(addHtml);
                    }

                }
            }

            if ($('input[name="cateCd"]:checked').length == 0) {
                $('input[name="cateCd"]:first').prop('checked', true);
            }
        });

        $(".js-set-sales-date").click(function () {

            if($("input[name='salesDate[]'").eq(0).val() !='') {
                $("input[name='salesDateFl']").eq(1).click();
            }
        });

        var mode = $('#frmGoods [name="mode"]').val();
        // maxlength의 경우 display none으로 되어있으면 정상작동 하지 않는다 따라서 페이지 로딩 후 maxlength가 적용된 후 display none으로 강제 처리 (임시방편 처리)
        setTimeout(function(){
            if(mode == 'register') {
                //$('#goodsNmExt').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '1px', left: '1103px'});
                var left = $('input[name="goodsNm"]').next('span.bootstrap-maxlength').css('left');
                $('#goodsNmExt').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '1px', left: left});
            }
            if(mode == 'modify'){
                $("input:radio[name=goodsNmFl]").click(function(){
                    var chkVal = $(":input:radio[name=goodsNmFl]:checked").val();
                    if (chkVal == 'e') $('.js-maxlength').trigger('maxlength.reposition');
                });
            }
        }, 1000);


        $(document).on('change', 'input[name="optionY[stockCnt][]"]', function() {
            var totalStock = 0;
            $('input[name="optionY[stockCnt][]"]').each(function () {
                totalStock += parseInt($(this).val());
            });
            $("input[name='stockCnt']").val(totalStock);
        });


        $(document).on('change', 'input[name="optionY[optionPrice][]"]', function() {
            var goodsPrice =   $("input[name='goodsPrice']").val();
            if($(this).val() < (goodsPrice*-1) ) {
                alert("상품 판매가와 옵션가의 합이 마이너스인 경우 결제가 되지 않습니다. 확인 후 다시 입력해주세요.");
                $(this).val('0');
                return false;
            }
        });


        initDepthToggle(<?=$SessScmNo?>);//4depth 메뉴 보임안보임처리

        <?php if ($gGlobal['isUse'] === true) { ?>
        $(".js-global-name  input:checkbox").click(function () {
            var globalName = $(this).closest("tr").find("input[type='text']");
            if($(this).is(":checked")) {
                var gloablNameText = $(globalName).val();
                if(gloablNameText) $(globalName).data('global-name',gloablNameText);
                $(globalName).val('');
                $(globalName).prop('disabled',true);
            } else {
                var gloablNameOriText = $(globalName).data('global-name');
                if(gloablNameOriText) $(globalName).val(gloablNameOriText);
                $(globalName).prop('disabled',false);
            }
        });

        <?php } ?>

        <?php if($data['hscode']) {
        foreach($data['hscode'] as $k => $v) { ?>
        add_hscode();
        $("select[name='hscodeNation[]']:last").val('<?=$k?>');
        $("input[name='hscode[]']:last").val('<?=$v?>');
        <?php
        }
        } ?>

        // 상품명 검색 키워드 추가
        $('input[name="addGoodsKeyword"]').on('click', function() {
            var goodsNm = $.trim($('input[name="goodsNm"]').val());
            var target = $('input[name="goodsSearchWord"]');
            if (goodsNm.length > 0) {
                var maxLength = parseInt(target.attr('maxlength'));
                var oldKeyword = $.trim(target.val());
                var newKeyword = goodsNm + ',' + oldKeyword;

                if (newKeyword.length > maxLength) {
                    newKeyword = newKeyword.substr(0, maxLength);
                }

                if ($(this).prop('checked')) {
                    if (oldKeyword) {
                        target.val(newKeyword);
                    } else {
                        target.val(goodsNm);
                    }
                    target.trigger('input');
                }
            }
        });

        <?php if($data['onlyAdultFl'] =='n') { ?>
        $("input[name='onlyAdultDisplayFl']").prop("disabled",true);
        $("input[name='onlyAdultImageFl']").prop("disabled",true);
        $("input[name='onlyAdultDisplayFl']").closest("label").css("color","#999999");
        $("input[name='onlyAdultImageFl']").closest("label").css("color","#999999");
        <?php } ?>

        <?php if($data['goodsAccess'] =='all') { ?>
        $("input[name='goodsAccessDisplayFl']").prop("disabled",true);
        $("input[name='goodsAccessDisplayFl']").closest("label").css("color","#999999");
        <?php } ?>

        <?php if($data['goodsPermission'] =='all') { ?>
        $("input[name='goodsPermissionPriceStringFl']").prop("disabled",true);
        $("input[name='goodsPermissionPriceStringFl']").closest("label").css("color","#999999");
        $(".js-goods-permission-price-string").hide();
        <?php } else { ?>
        <?php if($data['goodsPermissionPriceStringFl'] =='y') { ?>
        $(".js-goods-permission-price-string").show();
        <?php } else { ?>
        $(".js-goods-permission-price-string").hide();
        <?php } ?>
        <?php } ?>

        $('input[name="onlyAdultFl"]').click(function(e){
            if($(this).val() =='n') {
                $("input[name='onlyAdultDisplayFl']").prop("disabled",true);
                $("input[name='onlyAdultDisplayFl']").closest("label").css("color","#999999");
                $("input[name='onlyAdultImageFl']").prop("disabled",true);
                $("input[name='onlyAdultImageFl']").closest("label").css("color","#999999");
            } else {
                $("input[name='onlyAdultDisplayFl']").prop("disabled",false);
                $("input[name='onlyAdultDisplayFl']").closest("label").css("color","#333333");
                $("input[name='onlyAdultImageFl']").prop("disabled",false);
                $("input[name='onlyAdultImageFl']").closest("label").css("color","#333333");
            }
        });


        $('input[name="goodsPermissionPriceStringFl"]').click(function(e){
            if($(this).prop("checked")) {
                $(".js-goods-permission-price-string").show();
                $('.js-maxlength').trigger('maxlength.reposition');
            } else {
                $(".js-goods-permission-price-string").hide();
            }
        });

        $('.js-goods-benefit-select').bind('click', function () {
            $(this).closest('td').find('input[name="goodsBenefitSetFl"][value="y"]').trigger('click');
        });

        $(document).on('click', '.btn-remove', function(){
            var loc = $(this).data('loc');
            var locNo = $(this).data('loc-no');console.log(loc, locNo);
            var fieldID = 'optionValue' + loc;
            var optionCnt = $('#' + fieldID).find('input[id*=\'option_optionValue_' + loc + '\']').length;
            $('#option_optionCnt_' + loc).val(optionCnt);			// 옵션값 수 변경

            // 옵션값의 ID 변경 (순서데로)
            var targetID = '';
            var newID = '';
            var idArr = new Array('optVal_', 'option_optionValue_', 'optValCode_', 'optValImage_');
            var idArrFirst = new Array('optValDetail_', 'imageStorageModeOptionGoodsImage_', 'imageStorageModeOptionGoodsText_');
            for (var i = locNo; i < optionCnt; i++) {
                for (var j = 0; j < idArr.length; j++) {
                    targetID = $('#' + fieldID).find('input[id*=\'' + idArr[j] + loc + '\']').eq(i).attr('id');
                    newID = idArr[j] + loc + '_' + i;
                    $('#' + targetID).attr('id', newID);
                    if (j == 1) {
                        $('#' + newID).closest('div').find('.btn-remove').attr('data-loc-no', i);
                    }
                }
                if (loc == 0) {
                    for (var j = 0; j < idArrFirst.length; j++) {
                        targetID = $('#' + fieldID).find('input[id*=\'' + idArrFirst[j] + loc + '\']').eq(i).attr('id');
                        newID = idArrFirst[j] + loc + '_' + i;
                        $('#' + targetID).attr('id', newID);
                    }
                }
            }

            // 옵션값 삭제시
            optionValueChange = true;

            option_grid();
        });

        relatedGoodsTbl = $('#relationGoodsInfo').html();


    });

    var optionGridChange = false;			// 옵션 변경 여부
    var optionValueChange = false;			// 옵션값 변경 여부
    var optionValueFill = true;				// 옵션값 채울지의 여부

    /**
     * 간단 리스트 Ajax
     *
     * @param string modeStr 리스트, 검색 모드 설정 (list,search)
     * @param object parameters 페이징 및 검색 관련 내용을 object 처리
     */
    function goods_list_layer(modeStr, parameters) {
        var loadChk = $('#layerGoodsListForm').length;
        if (modeStr == 'list') {
            if (loadChk == 0) {
                $.get('layer_goods_list.php', {
                    goodsNo: '<?=$data['goodsNo'];?>',
                    popupMode: '<?=gd_isset($popupMode);?>'
                }, function (data) {
                    goods_list_toggle(modeStr, data);
                });
            } else {
                goods_list_toggle(modeStr);
            }
        } else if (modeStr == 'search') {
            $.get('layer_goods_list.php', parameters, function (data) {
                goods_list_toggle(modeStr, data);
            });
        }
    }

    /**
     * 간단 리스트 출력
     *
     * @param string modeStr 리스트, 검색 모드 설정 (list,search)
     * @param string dataHtml 리스트 내용
     */
    function goods_list_toggle(modeStr, dataHtml) {
        if (typeof dataHtml != 'undefined') {
            var listHtml = '<div id="layerGoodsListForm" style="border:solid 4px #7c8389; margin:0px 0px 20px 0px; padding:10px 10px 10px 10px;">' + dataHtml + '</div>';
            $('#layerGoodsList').html(listHtml);
        }
        if (modeStr == 'list') {
            $('#layerGoodsList').toggle();

            if ($('#layerGoodsList').is(':hidden')) $("#goodsListForCopy").val('기존상품 복사');
            else $("#goodsListForCopy").val('기존상품 복사 닫기');

        }
    }

    /**
     * 자주쓰는 옵션 Ajax layer
     */
    function manage_option_list() {
        var loadChk = $('#layerOptionListForm').length;
        var scmNo = $('input[name="scmNo"]').val();

        $.get('layer_goods_option_list.php', {'scmNo': scmNo}, function (data) {
            if (loadChk == 0) {
                data = '<div id="layerOptionListForm">' + data + '</div>';
            }
            var layerForm = data;
            layer_popup(layerForm, '자주쓰는 옵션 리스트');
        });
    }

    /**
     * 자주쓰는 옵션 등록 Ajax layer
     */
    function manage_option_register() {

        var optionCnt = $('#optionY_optionCnt').val();

        // 옵션 개수가 있는 지는 체크
        if (optionCnt == '' || optionCnt == 0) {
            alert('옵션을 먼저 기재해주세요.');
            return false;
        }

        var loadChk = $('#layerOptionRegisterForm').length;
        var scmNo = $('input[name="scmNo"]').val();


        $.post('layer_goods_option_register.php', {'scmNo': scmNo}, function (data) {
            if (loadChk == 0) {
                data = '<div id="layerOptionRegisterForm">' + data + '</div>';
            }
            var layerForm = data;
            layer_popup(layerForm, '자주쓰는 옵션 등록');
        });
    }

    /**
     * 출력 여부
     *
     * @param string arrayID 해당 ID
     * @param string modeStr 출력 여부 (show or hide)
     */
    function display_toggle(thisID, modeStr) {
        var mode = $('#frmGoods [name="mode"]').val();
        if(mode == 'modify') {
            if (modeStr == 'show') {
                $('#' + thisID).attr('class', '');
                if(thisID == 'goodsNmExt') $('#goodsNmExt').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '1px', left: '1103px'});
            } else if (modeStr == 'hide') {
                $('#' + thisID).attr('class', 'display-none');
            }
        }else{
            if (modeStr == 'show') {
                $('#' + thisID).attr('class', '');
                if ($('input[name="applyGoodsCopy"]').val() != '' && thisID == 'goodsNmExt') {
                    //$('#goodsNmExt').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '1px', left: '1103px'});
                    var left = $('input[name="goodsNm"]').next('span.bootstrap-maxlength').css('left');
                    $('#goodsNmExt').find('input[maxlength]').next('span.bootstrap-maxlength').css({top: '1px', left: left});
                } else {
                    if (thisID == 'goodsNmExt') $('.js-maxlength').trigger('maxlength.reposition');
                }
            } else if (modeStr == 'hide') {
                $('#' + thisID).attr('class', 'display-none');
            }
        }
    }

    /**
     * 체크박스 출력여부에 따라서 노출
     *
     * @param string checkbox 해당 ID
     * @param string display 출력 여부 (show or hide)
     */
    function checkbox_toggle(checkboxName, displayInput) {
        if($("input[name='"+checkboxName+"']").prop("checked")) {
            $("input[name='"+displayInput+"']").show();
        } else {
            $("input[name='"+displayInput+"']").hide();
        }
    }

    function display_toggle_class(thisName, thisClass) {
        var modeStr = $('input[name="' + thisName + '"]:checked').val();
        if (modeStr == 'y') {
            $('.' + thisClass).removeClass('display-none');
            // !중요! 숨겨진 엘리먼트를 보여지게 할 경우 maxlength 표시 부분의 위치가 어긋난다. 이에 아래 트리거를 사용해 위치를 재 설정한다.
        } else if (modeStr == 'n') {
            $('.' + thisClass).addClass('display-none');
        }
    }

    /**
     * 출력 토글
     *
     * @param string thisID 해당 ID
     */
    function view_switch(thisID) {
        $('#' + thisID).slideToggle('slow');
    }

    /**
     * disabled 여부
     *
     * @param string  inputName 해당 input Box의 name
     * @param boolean modeBool 출력 여부 (true or false)
     */
    function disabled_switch(inputName, modeBool) {
        if ($('input[name=\'' + inputName + '\']').length) {
            $('input[name=\'' + inputName + '\']').prop('disabled', modeBool);
        } else if ($('select[name=\'' + inputName + '\']').length) {
            $('select[name=\'' + inputName + '\']').prop('disabled', modeBool);
        }
    }

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function goods_categoty_add_layer() {
        var loadChk = $('#addCateGoodsForm').length;
        $.post('layer_goods_categoty_add.php', '', function (data) {
            if (loadChk == 0) {
                data = '<div id="addCateGoodsForm">' + data + '</div>';
            }
            var layerForm = data;
            layer_popup(layerForm, '카테고리 연결');
        });
    }

    /**
     * 추가항목 추가
     */
    function add_info() {
        var fieldID = 'addInfoItem';
        var fieldNoChk = $('#' + fieldID).find('tr:last').get(0).id.replace(fieldID, '');

        if (fieldNoChk == '') {
            var fieldNoChk = 0;
        }
        var fieldNo = parseInt(fieldNoChk) + 1;
        var addHtml = '';
        addHtml += '<tr id="' + fieldID + fieldNo + '">';
        addHtml += '<td class="center">' + fieldNo + '</td>';
        addHtml += '<td class="center"><input type="text" name="addInfo[infoTitle][]" value="" class="form-control width-lg" /></td>';
        addHtml += '<td class="center"><input type="text" name="addInfo[infoValue][]" value="" class="form-control" /></td>';
        addHtml += '<td class="center"><input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="field_remove(\'' + fieldID + fieldNo + '\');" value="삭제" /></td>';
        addHtml += '</tr>';
        $('#' + fieldID).append(addHtml);
    }

    /**
     * 상품 필수 정보 추가
     */
    function add_must_info(infoCnt) {
        var fieldID = 'addMustInfo';
        $('#' + fieldID).show();
        var fieldNoChk = $('#' + fieldID).find('tr:last').get(0).id.replace(fieldID, '');
        if (fieldNoChk == '') {
            var fieldNoChk = 0;
        }
        var fieldNo = parseInt(fieldNoChk) + 1;

        var colspanStr = '';
        if (infoCnt == 2) {
            colspanStr = ' colspan="3"';
        }

        var addHtml = '';
        addHtml += '<tr id="' + fieldID + fieldNo + '">';
        addHtml += '<td class="center"><input type="text" name="addMustInfo[infoTitle][' + fieldNo + '][0]" value="" class="form-control" maxlength="60" /></td>';
        addHtml += '<td class="center"' + colspanStr + '><input type="text" name="addMustInfo[infoValue][' + fieldNo + '][0]" value="" class="form-control" maxlength="500" /></td>';
        if (infoCnt == 4) {
            addHtml += '<td class="center"><input type="text" name="addMustInfo[infoTitle][' + fieldNo + '][1]" value="" class="form-control" maxlength="60" /></td>';
            addHtml += '<td class="center"><input type="text" name="addMustInfo[infoValue][' + fieldNo + '][1]" value="" class="form-control" maxlength="500" /></td>';
        }
        addHtml += '<td class="center"><input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="field_remove(\'' + fieldID + fieldNo + '\');" value="삭제" /></span></td>';
        addHtml += '</tr>';
        $('#' + fieldID).append(addHtml);
    }

    /**
     * 상품 필수 정보 추가 배송,설치비용 / 추가설치비용 관련
     */
    function add_must_info_install(val) {
        add_must_info(2);

        $('#addMustInfo').find('tr:last td:eq(0) input').val($(val).closest('tr').find('span').text());
        $('#addMustInfo').find('tr:last td:eq(1) input').attr('placeholder',$(val).parents('tr').find("td:eq(0)").text());
    }


    <?php if ($data['optionFl'] == 'y') {?>
    /**
     * 옵션 정보 채우기
     */
    function fill_option() {
        option_setting(<?=$data['optionCnt'];?>);
        <?php
        $optionImageAddUrlFl = "n";
        for ($i = 0; $i < $data['optionCnt']; $i++) {
            $optionCnt = count(gd_isset($data['option']['optVal'][$i + 1]));
            echo "	$('#option_optionName_" . $i . "').val('" . gd_htmlspecialchars_slashes($data['optionName'][$i], 'add') . "');" . chr(10);
            echo "	$('#option_optionCnt_" . $i . "').val('" . $optionCnt . "');" . chr(10);
            echo "	option_value_conf(" . $i . ", " . $optionCnt . ", true);" . chr(10);
            if (is_array($data['option']['optVal'][$i + 1])) {
                $j = 0;
                $optIcon = [];
                foreach ($data['option']['optVal'][$i + 1] as $key => $val) {
                    echo "	$('#option_optionValue_" . $i . "_" . $j . "').val('" . gd_htmlspecialchars_slashes($val, 'add') . "');" . chr(10);
                    $optIcon[json_encode($val)] = $j;    // 옵션 아이콘의 키값
                    $j++;
                }

                // 옵션 추가노출 여부
                if (!gd_isset($data['optionIcon'])) {
                    continue;
                }

                // 옵션 아이콘 값
                foreach ($data['optionIcon'] as $key => $val) {
                    // 번호가 맞지 않으면 패스
                    if ($val['optionNo'] != $i) {
                        continue;
                    }
                    // 옵션 아이콘의 키값
                    $k = $optIcon[json_encode($val['optionValue'])];


                    // 기존 상품 복사 등록/수정이 아닌경우
                    echo "	$('#option_Icon_sno_" . $i . "_" . $k . "').val('" . $val['sno'] . "');" . chr(10);
                    echo "	$('#option_Icon_optionNo_" . $i . "_" . $k . "').val('" . $val['optionNo'] . "');" . chr(10);
                    echo "	$('#option_Icon_colorCode_" . $i . "_" . $k . "').val('" . $val['colorCode'] . "');" . chr(10);
                    if ($data['imageStorage'] == 'url') {
                        echo "	$('#option_Icon_iconImageText_" . $i . "_" . $k . "').val('" . gd_htmlspecialchars_slashes($val['iconImage'], 'add') . "');" . chr(10);
                        echo "	$('#option_Icon_goodsImageText_" . $i . "_" . $k . "').val('" . gd_htmlspecialchars_slashes($val['goodsImage'], 'add') . "');" . chr(10);
                    } else {
                        if ($val['iconImage'] || $val['goodsImage']) {
                            if (strtolower(substr($val['goodsImage'],0,4)) =='http' ) {
                                $optionImageAddUrlFl = "y";
                                $preViewImg = gd_html_preview_image($val['goodsImage'], $data['imagePath'],'url', 20, 'goods', null, null, true);
                                echo "	$('#option_Icon_goodsImageText_" . $i . "_" . $k . "').val('" . gd_htmlspecialchars_slashes($val['goodsImage'], 'add') . "');" . chr(10);

                                if($preViewImg) $preViewImg .= " <input type='checkbox' name='optionYIcon[optionImageDeleteFl][".$i."][".$k."]' value='y'>삭제";
                                echo "	$('#option_Icon_goodsImageUrl_" . $i . "_" . $k . "').html('" . gd_htmlspecialchars_slashes($preViewImg, 'add') . "');" . chr(10);

                            } else {
                                $preViewImg = gd_html_preview_image($val['goodsImage'], $data['imagePath'], $data['imageStorage'], 20, 'goods', null, null, true);
                                echo "	$('#option_Icon_goodsImageName_" . $i . "_" . $k . "').val('" . gd_htmlspecialchars_slashes($val['goodsImage'], 'add') . "');" . chr(10);

                                if($preViewImg) $preViewImg .= " <input type='checkbox' name='optionY[optionImageDeleteFl][".$i."][".$k."]' value='y'>삭제";
                                echo "	$('#option_Icon_goodsImage_" . $i . "_" . $k . "').html('" . gd_htmlspecialchars_slashes($preViewImg, 'add') . "');" . chr(10);
                            }
                        }
                    }
                }
            }
        }

        // 사용된 옵션 값 제거
        if (isset($data['option']['optVal']) === true) {
            unset($data['option']['optVal']);
        }

        if($optionImageAddUrlFl =='y') {
            echo "$('input[name=optionImageAddUrl]').click();". chr(10);
        }
        ?>

        optionGridChange = true;
    }

    /**
     * 옵션값 채우기
     */
    function fill_value() {
        <?php
        if (gd_isset($data['option'])) {

        foreach($data['option'] as $k => $v) {
        $optionName = [];
        for ($i = 1; $i <= DEFAULT_LIMIT_OPTION; $i++) {
            if($v['optionValue'.$i]) $optionName[]= $v['optionValue'.$i];
        }
        ?>

        var optionItem = $('#optionGridTable input[value="<?=implode(STR_DIVISION,$optionName)?>"]').closest('tr').attr("id");
        $("#"+optionItem+ " input[name='optionY[sno][]']").val("<?=$v['sno']?>");
        $("#"+optionItem+ " input[name='optionY[optionPrice][]']").val("<?= gd_money_format(gd_isset($v['optionPrice']), false)?>");
        $("#"+optionItem+ " input[name='optionY[stockCnt][]']").val("<?=$v['stockCnt']?>");

        <?php }
        }
        ?>
    }
    <?php }?>

    /**
     * 옵션정보 리셋 - 전부 지우기
     */
    function option_reset() {
        $('#optionY_optionCnt').val('');
        $('#option').html('');
        $('#optionGrid').html('');
        optionGridChange = false;
    }

    /**
     * 옵션 세팅 - 옵션명 설정 및 추가 정보
     *
     * @param string thisCnt 옵션 개수
     */
    function option_setting(thisCnt) {
        var fieldID = 'option';
        var fieldCnt = $('#' + fieldID).find('input[id*=\'option_optionName_\']').length;
        var fieldChk = parseInt(thisCnt - fieldCnt);
        var addHtml = '';
        var templateHtml = '';

        var imageStorage = $('#imageStorage').val();

        if(imageStorage =='url') {
            var imageUploadView = "display-none";
            var imageUrlView = "display-inline";
        } else {
            var imageUploadView = "display-inline";
            var imageUrlView = "display-none";
        }


        if (fieldCnt == '0' && fieldChk > 0) {
            templateHtml += '<table class="table table-cols"  id="opation_add_tbody">';
            templateHtml += '<colgroup><col class="width-2xs" /><col  class="width-lg"/><col/></colgroup>';
            templateHtml += '<tr>';
            templateHtml += '<th class="left">옵션명</th>';
            templateHtml += '<th class="left " style="width:425px">옵션값</th>';
            templateHtml += '<th class="left">옵션 이미지 <span class="js-option-image-url '+imageUploadView+'"  >( <input type="checkbox" name="optionImageAddUrl" value="y" onclick="option_image_add_url();"/> URL 직접입력 추가사용 )</span></th>';
            templateHtml += '</tr>';
        }

        if (fieldChk > 0) {

            for (var i = fieldCnt; i < thisCnt; i++) {

                addHtml += '<tr class="option-items">';
                addHtml += '<td><input type="text" id="option_optionName_' + i + '" name="optionY[optionName][]" value="" class="form-control width-md" placeholder="ex)사이즈" onblur="option_grid();" /></td>';
                addHtml += '<td colspan="2" style="padding:0px;margin:0px;">';
                addHtml += '<table id="optionValue' + i + '" class="table table-cols table-cols-none">';
                addHtml += '<colgroup><col style="width:425px"/><col/></colgroup>';
                addHtml += '<tr id="optVal_' + i + '_0">';
                if (i == 0)  addHtml += '<td><div class="form-inline">';
                else addHtml += '<td colspan="2"><div class="form-inline">';
                addHtml += '<input type="text" id="option_optionValue_' + i + '_0" data-option-sno="'+i+'" name="optionY[optionValue][' + i + '][]" value="" class="form-control"  style="width:330px;"  placeholder="Enter키를 이용 옵션값을 연속적으로 입력하세요. ex)XL" onblur=" if(option_value_check(\'' + i + '\',\'0\') == true) { option_grid(); } " />';

                addHtml += ' <input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus" onclick="option_value_conf_add(' + i + ')" />';
                addHtml += '<input type="hidden" id="option_optionCnt_' + i + '" name="optionY[optionCnt][]" value="1" class="input_int" onblur="option_value_conf(' + i + ',this.value);" />';


                addHtml += '</div></td>';
                if (i == 0) {
                    addHtml += '<td id="optValDetail_' + i + '_0"><div class="form-inline">';
                    addHtml += '<span id="imageStorageModeOptionGoodsImage_' + i + '_0"  class="'+imageUploadView+'" >';
                    addHtml += '<input type="file" name="optionYIcon[goodsImage][' + i + '][]" value="" class="form-control" style="height:30px" />';
                    addHtml += '<input type="hidden" id="option_Icon_goodsImageName_' + i + '_0" name="optionYIcon[goodsImage][' + i + '][]" value="" />';
                    addHtml += ' <span id="option_Icon_goodsImage_' + i + '_0" ></span>';
                    addHtml += '</span>';
                    addHtml += '<span id="imageStorageModeOptionGoodsText_' + i + '_0" class="'+imageUrlView+'">';
                    addHtml += '<input type="text" id="option_Icon_goodsImageText_' + i + '_0" name="optionYIcon[goodsImageText][' + i + '][]" value="" class="form-control width90p" />';
                    addHtml += ' <span id="option_Icon_goodsImageUrl_' + i + '_0" ></span>';
                    addHtml += '</span>';
                    addHtml += '</div></td>';
                }
                addHtml += '</tr>';


                addHtml += '</table>';

                addHtml += '</td>';
                addHtml += '</tr>';


            }


        } else if (fieldChk < 0) {
            for (var j = thisCnt; j < fieldCnt; j++) {
                $('#opation_add_tbody').find('tr.option-items:last').remove();
            }
        }

        if (fieldCnt == '0' && fieldChk > 0) $('#' + fieldID).append(templateHtml + addHtml + '</html>');
        else $('#opation_add_tbody').append(addHtml);


        $('input[name*=\'optionCnt\']').number_only();
        $('.imageStorageText').html($('#imageStorage option:selected').text());
        option_grid();
        init_file_style();

        $("input[id*='option_optionValue_']").off('keypress');
        $("input[id*='option_optionValue_']").on('keypress', function (e) {
            if (e.which == 13) {

                var selOption = $(this).attr('id').split("_");
                if(option_value_check(selOption[2],selOption[3]) == true) {
                    option_value_conf_add($(this).data('option-sno'));
                }
                $("input[id*='option_optionValue_" + $(this).data('option-sno') + "']:last").focus();
                e.preventDefault();
                return false
            }
        });


    }

    /**
     * 옵션값 추가
     *
     * @param string loc 옵션 순서 (1-5)
     */
    function option_value_conf_add(loc) {



        var optionCnt = $('#option_optionCnt_' + loc).val();
        var addOptionCnt = 0;
        if (!optionCnt) {
            optionCnt = 0;
        }

        if(option_total_check(loc)) {
            addOptionCnt = parseInt(optionCnt) + 1;

            $('#option_optionCnt_' + loc).val(addOptionCnt);

            option_value_conf(loc, addOptionCnt, true);
        }

    }

    function option_total_check(loc) {

        var optionTotalCnt = $("#optionY_optionCnt").val();
        var totalOption = 1;

        for(var i = 0; i < optionTotalCnt; i++ ) {

            var tmp = $("input[id*='option_optionValue_"+i+"']").length;

            if(loc == i) tmp += 1;

            totalOption = totalOption*tmp;
        }

        if(totalOption > 1000) {
            alert("옵션의 조합은 1000개 이하로 가능합니다.");
            return false;
        } else {
            return true;
        }

    }

    /**
     * 옵션값 삭제 후 수량 및 ID 변경
     *
     * @param string loc 옵션 순서 (1-5)
     * @param string locNo 순서 번호
     */
    function option_value_conf_remove(loc, locNo) {
        var fieldID = 'optionValue' + loc;
        var optionCnt = $('#' + fieldID).find('input[id*=\'option_optionValue_' + loc + '\']').length;
        $('#option_optionCnt_' + loc).val(optionCnt);			// 옵션값 수 변경

        // 옵션값의 ID 변경 (순서데로)
        var targetID = '';
        var newID = '';
        var idArr = new Array('optVal_', 'option_optionValue_', 'optValCode_', 'optValImage_');
        var idArrFirst = new Array('optValDetail_', 'imageStorageModeOptionGoodsImage_', 'imageStorageModeOptionGoodsText_');
        for (var i = locNo; i < optionCnt; i++) {
            for (var j = 0; j < idArr.length; j++) {
                targetID = $('#' + fieldID).find('input[id*=\'' + idArr[j] + loc + '\']').eq(i).attr('id');
                newID = idArr[j] + loc + '_' + i;
                $('#' + targetID).attr('id', newID);
            }
            if (loc == 0) {
                for (var j = 0; j < idArrFirst.length; j++) {
                    targetID = $('#' + fieldID).find('input[id*=\'' + idArrFirst[j] + loc + '\']').eq(i).attr('id');
                    newID = idArrFirst[j] + loc + '_' + i;
                    $('#' + targetID).attr('id', newID);
                }
            }
        }

        // 옵션값 삭제시
        optionValueChange = true;

        option_grid();
    }

    /**
     * 옵션값 설정 - 옵션값 ,색상표, 아이콘 등
     *
     * @param string loc 옵션 순서 (1-5)
     * @param string thisCnt 옵션값 개수
     * @param string loadChk 옵션값 개수 제한 체크 여부 (기본 false)
     */
    function option_value_conf(loc, thisCnt, loadChk) {

        if (!loadChk) {
            // 옵션값 개수 제한
            var optionCnt = $('#optionY_optionCnt').val();
            var optTotVal = 1;
            for (var i = 0; i < optionCnt; i++) {
                if ($('#option_optionCnt_' + i).val() > 0) {
                    optTotVal = parseInt(optTotVal) * parseInt($('#option_optionCnt_' + i).val());
                }
            }
            if (optTotVal > <?=DEFAULT_LIMIT_OPTION_VALUE;?>) {
                dialog_confirm('옵션값 개수가 ' + optTotVal + '개 입니다.<br/>옵션이 <?=DEFAULT_LIMIT_OPTION_VALUE;?>개 이상이 되면<br/>너무 많아 작성이 힘들어 지거나 느려질수 있습니다.<br/>계속 옵션 작성 하시겠습니까?<br/>(확인-그대로 진행, 취소-해당 옵션을 재설정함)', function (result) {
                    if (!result) {
                        $('#option_optionCnt_' + loc).val('');
                        thisCnt = 0;
                        //return false;
                    }
                });
            }
        }

        var fieldID = 'optionValue' + loc;
        var fieldCnt = $('#' + fieldID).find('input[id*=\'option_optionValue_' + loc + '\']').length;
        var fieldChk = parseInt(thisCnt - fieldCnt);

        var imageStorage = $('#imageStorage').val();
        if(imageStorage =='url') {
            var imageUploadView = "display-none";
            var imageUrlView = "display-block";
        } else {
            var imageUploadView = "display-block";
            var imageUrlView = "display-none";
        }

        if($('input[name="optionImageAddUrl"]').is(":checked")) {
            imageUrlView  = "display-none display-block";
        }

        var addHtml = '';
        if (fieldChk > 0) {

            for (var i = fieldCnt; i < thisCnt; i++) {
                addHtml += '<tr id="optVal_' + loc + '_' + i + '">';
                if (loc == 0)  addHtml += '<td ><div class="form-inline">';
                else addHtml += '<td colspan="2"><div class="form-inline">';
                addHtml += '<input type="text" id="option_optionValue_' + loc + '_' + i + '" data-option-sno="'+loc+'" name="optionY[optionValue][' + loc + '][]" value="" class="form-control" style="width:330px;" placeholder="Enter키를 이용 옵션값을 연속적으로 입력하세요. ex)XL" onblur=" if(option_value_check(\'' + loc + '\',\'' + i + '\') == true) { option_grid(); } " />';
                addHtml += ' <input type="button" class="btn btn-sm btn-white btn-icon-minus btn-remove" id="remove_option_' + loc + '_' + i + '" data-loc="' + loc + '" data-loc-no="' + i + '" onclick="field_remove(\'optVal_' + loc + '_' + i + '\');" value="삭제" /> ';
                if (i == 0) {
                    addHtml += ' <span class="button black small"><input type="button" value="추가" onclick="option_value_conf_add(' + loc + ')" /></span>';
                    addHtml += '<input type="hidden" id="option_optionCnt_' + i + '" name="optionY[optionCnt][]" value="" class="input_int" onblur="option_value_conf(' + i + ',this.value);" />';
                }

                addHtml += '</div></td>';
                if (loc == 0) {
                    addHtml += '<td id="optValDetail_' + loc + '_' + i + '"><div class="form-inline">';
                    addHtml += '<span id="imageStorageModeOptionGoodsImage_' + loc + '_' + i + '"  class="'+imageUploadView+'">';
                    addHtml += '<input type="file" name="optionYIcon[goodsImage][' + loc + '][]" value="" class="form-control" style="height:30px" />';
                    addHtml += '<input type="hidden" id="option_Icon_goodsImageName_' + loc + '_' + i + '" name="optionYIcon[goodsImage][' + loc + '][]" value="" />';
                    addHtml += ' <span id="option_Icon_goodsImage_' + loc + '_' + i + '"></span>';
                    addHtml += '</span>';
                    addHtml += '<span id="imageStorageModeOptionGoodsText_' + loc + '_' + i + '" class="'+imageUrlView+'">';
                    addHtml += '<input type="text" id="option_Icon_goodsImageText_' + loc + '_' + i + '" name="optionYIcon[goodsImageText][' + loc + '][]" value="" class="form-control width90p" />';
                    addHtml += ' <span id="option_Icon_goodsImageUrl_' + loc + '_' + i + '"></span>';
                    addHtml += '</span>';
                    addHtml += '</div></td>';
                }
                addHtml += '</tr>';
            }
        } else if (fieldChk < 0) {
            for (var j = thisCnt; j < fieldCnt; j++) {
                $('#optVal_' + loc + '_' + j).remove();
            }
        }

        $('#' + fieldID).append(addHtml);
        init_file_style();

        $("input[id*='option_optionValue_']").off('keypress');
        $("input[id*='option_optionValue_']").on('keypress', function (e) {
            if (e.which == 13) {
                var selOption = $(this).attr('id').split("_");
                if(option_value_check(selOption[2],selOption[3]) == true) {
                    option_value_conf_add($(this).data('option-sno'));
                }
                $("input[id*='option_optionValue_" + $(this).data('option-sno') + "']:last").focus();
                e.preventDefault();
                return false
            }
        });

        if (thisCnt > 0) {
            /*
             for (var k = 0; k < thisCnt; k++) {
             if ($('#option_optionValue_' + loc + '_' + k).val() == '') {
             return false;
             }
             }
             */
            option_grid();
        }
    }


    /**
     * 동일한 옵션값 여부를 체크
     *
     * @param string loc 옵션 순서 (1-5)
     * @param string locNo 순서 번호
     */
    function option_value_check(loc, locNo) {
        var thisOptionValue = $('#option_optionValue_' + loc + '_' + locNo).val().trim();
        // 입력값이 없는경우
        if (thisOptionValue == '') {
            return true;
        }
        var chkOptionValue = '';
        var fieldID = 'optionValue' + loc;
        var fieldCnt = $('#' + fieldID).find('input[id*=\'option_optionValue_' + loc + '\']').length;

        for (var i = 0; i < fieldCnt; i++) {
            if (locNo != i) {
                chkOptionValue = $('#option_optionValue_' + loc + '_' + i).val().trim();
                if (thisOptionValue == chkOptionValue) {
                    alert('현재 입력한 옵션값과 동일한 옵션값이 존재합니다.\n다시 입력해 주세요!');
                    $('#option_optionValue_' + loc + '_' + locNo).val('');
                    $('#option_optionValue_' + loc + '_' + locNo).focus();

                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 옵션값 테이블 설정
     *
     * @param string manualFl 수동 체크 여부
     */
    /**
     * 옵션값 테이블 설정
     *
     * @param string manualFl 수동 체크 여부
     */
    function option_grid(manualFl) {
        // 수동 여부 체크
        if (typeof manualFl == 'undefined') {
            manualFl = 'n';
        }

        var fieldID = 'optionGrid';
        var fieldTable = fieldID + 'Table';
        var optionCnt = $('#optionY_optionCnt').val();
        var optTotCnt = 1;

        // 옵션 개수가 있는 지는 체크
        if (optionCnt == '' || optionCnt == 0) {
            if (manualFl == 'y') {
                alert('[옵션설정 오류]\n\n옵션 개수를 선택해 주세요!');
            }
            return false;
        }

        // 옵션값이 있는지를 체크하며, 전체 옵션값 개수를 계산을 함
        for (var i = 0; i < optionCnt; i++) {
            if ($('#option_optionName_' + i).val() == '') {
                if (manualFl == 'y') {
                    alert('옵션명을 입력해 주세요!');
                }
                return false;
            }
            if ($('#option_optionCnt_' + i).val() <= 0) {
                if (manualFl == 'y') {
                    alert('옵션값이 설정되있지 않습니다.\n\n추가를 눌러 수량에 맞게 옵션값을 넣어 주세요.!');
                }
                return false;
            } else {
                for (var j = 0; j < $('#option_optionCnt_' + i).val(); j++) {
                    if ($('#option_optionValue_' + i + '_' + j).val() == '') {
                        if (manualFl == 'y') {
                            alert('옵션값을 입력해 주세요!');
                        }
                        return false;
                    }
                }
            }
            var optTotCnt = optTotCnt * $('#option_optionCnt_' + i).val();
        }

        // 옵션값을 수정시 옵션Grid 를 다시 갱신 할지를 선택함
        if (optionGridChange == true) {
            if ($('#' + fieldTable).length) {
                $('#' + fieldTable).remove();
            }
        }

        // 옵션 값 개수 설정
        var valGab = new Array();
        var valCnt = new Array();
        for (var i = 0; i < optionCnt; i++) {
            if (i == 0) {
                valGab[i] = optTotCnt / $('#option_optionCnt_' + i).val();
            } else {
                valGab[i] = valGab[i - 1] / $('#option_optionCnt_' + i).val();
            }
            valCnt[i] = $('#option_optionCnt_' + i).val();
        }

        // 옵션 값 체크 설정
        var valChk = new Array();
        var valIdNo = new Array();
        <?php
        for ($i = 0; $i < DEFAULT_LIMIT_OPTION; $i++) {
            echo '	valChk[' . $i . ']		= 1;' . chr(10);
            echo '	valIdNo[' . $i . ']		= 0;' . chr(10);
        }
        ?>

        // 옵션 그리기
        var addHtml = '';
        addHtml += '<table id="' + fieldTable + '" class="table table-cols">';
        for (var j = 0; j <= optTotCnt; j++) {
            if (j == 0) {
                addHtml += '<thead>';
                addHtml += '<tr>';
                addHtml += '<th class="width2p"><input type="checkbox" id="allOptionCheck" value="y" onclick="check_toggle(this.id,\'optionY[optionNo][]\');"/></th>';
                addHtml += '<th class="width2p">번호</th>';
                for (var k = 0; k < optionCnt; k++) {
                    addHtml += '<th class="width10p">' + $('#option_optionName_' + k).val() + '</th>';
                }
                addHtml += '<th class="width10p">옵션 매입가</th>';
                addHtml += '<th class="width10p">옵션가</th>';
                addHtml += '<th class="width10p">재고량</th>';
                addHtml += '<th class="width10p">자체 옵션코드</th>';
                addHtml += '<th class="width10p">노출상태</th>';
                addHtml += '<th class="width10p">품절상태</th>';
                addHtml += '<th class="width10p">메모</th>';
                addHtml += '</tr>';
                addHtml += '</thead>';
                addHtml += '<tr>';
                addHtml += '<th class="center" colspan="' + (parseInt(optionCnt) + 2) + '"><input type="button" onclick="option_value_apply();" value="옵션 정보 일괄 적용" class="btn btn-xs btn-gray" /></th>';
                addHtml += '<th class="center"><div class="form-inline"><?=gd_currency_symbol();?><input type="text" id="option_optionCostPriceApply" class="form-control width-2xs" /><?=gd_currency_string();?></div></th>';
                addHtml += '<th class="center"><div class="form-inline"><?=gd_currency_symbol();?><input type="text" id="option_opotionPriceApply" class="form-control width-2xs" /><?=gd_currency_string();?></div></td>';
                addHtml += '<th class="center"><div class="form-inline"><input type="text" id="option_stockCntApply" class="form-control width-2xs" <?=$disabled['option_stockCntApply'];?>/>개</div></td>';
                addHtml += '<th class="center"><input type="text" id="option_optionCodeApply" class="form-control width-sm js-maxlength" maxlength="30" /></td>';
                addHtml += '<th class="center"><select class="form-control" id="option_optionViewFlApply" ><option value="y">노출함</optiton><option value="n">노출안함</optiton></select></td>';
                addHtml += '<th class="center"><select class="form-control" id="option_optionSellFlApply" ><option value="y">정상</optiton><option value="n">품절</optiton></select></td>';
                addHtml += '<th class="center"><div class="form-inline"><input type="text" id="option_optionMemoApply" class="form-control width-xs" /></div></th>';
                addHtml += '</tr>';
            } else {
                addHtml += '<tr id="tbl_option_info_' + j + '">';
                addHtml += '<td class="center"><input type="checkbox" name="optionY[optionNo][]" value="' + j + '"></td>';
                addHtml += '<td class="center">' + j + '</td>';
                var optKey = 0;
                var optKey2 = '';
                var optChkValue = '';
                var arrOption = [];
                for (var k = 0; k < optionCnt; k++) {
                    optKey = k + 1;
                    if ((valChk[k] - 1) == valGab[k]) {
                        if (valCnt[k] > (valIdNo[k] + 1)) {
                            valIdNo[k]++;
                        } else {
                            valIdNo[k] = 0;
                        }
                        valChk[k] = 1;
                    }
                    if($('#option_optionValue_' + k + '_' + valIdNo[k]).length) {
                        var optVal = $('#option_optionValue_' + k + '_' + valIdNo[k]).val().replace(/"/g, '&quot;');
                        arrOption.push(optVal);
                        addHtml += '<td class="center">' + optVal + '</td>';
                        optChkValue = optChkValue + optVal.trim();
                        optKey2 = optKey2 + valIdNo[k];
                        valChk[k]++;
                    }
                }
                addHtml += '<input type="hidden" id="option_sno_' + optKey2 + '" name="optionY[sno][]" value="" />';
                addHtml += '<input type="hidden" name="optionY[optionValueText][]" value="'+arrOption.join("<?=STR_DIVISION?>")+'" />';
                addHtml += '<td class="center"><div class="form-inline"><?=gd_currency_symbol();?><input type="text" id="option_optionCostPrice_' + optKey2 + '" name="optionY[optionCostPrice][]" value="" class="form-control width-2xs" /><?=gd_currency_string();?></div></td>';
                addHtml += '<td class="center"><div class="form-inline"><?=gd_currency_symbol();?><input type="text" id="option_optionPrice_' + optKey2 + '" name="optionY[optionPrice][]" value="" class="form-control width-2xs" /><?=gd_currency_string();?></div></td>';
                addHtml += '<td class="center"><div class="form-inline"><input type="text" id="option_stockCnt_' + optKey2 + '" name="optionY[stockCnt][]" value="" class="form-control width-2xs" <?=$disabled['optionY[stockCnt][]'];?>/>개</div></td>';
                addHtml += '<td class="center"><input type="text" id="option_optionCode_' + optKey2 + '" name="optionY[optionCode][]" value="" class="form-control width-sm js-maxlength" maxlength="30" /></td>';
                addHtml += '<td class="center"><select class="form-control" id="option_optionViewFl_' + optKey2 + '" name="optionY[optionViewFl][]"><option value="y">노출함</optiton><option value="n">노출안함</optiton></select></td>';
                addHtml += '<td class="center"><select  class="form-control" id="option_optionSellFl_' + optKey2 + '" name="optionY[optionSellFl][]"><option value="y">정상</optiton><option value="n">품절</optiton></select></td>';
                addHtml += '<td class="center"><div class="form-inline"><input type="text" id="option_optionMemo_' + optKey2 + '" name="optionY[optionMemo][]" value="" class="form-control width-xs" /></div></td>';
                addHtml += '</tr>';
            }
        }

        addHtml += '<tfoot><tr><td colspan="' + (parseInt(optionCnt) + 7) + '"><input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="delete_option(\'optionY[optionNo][]\',\'tbl_option_info_\');" value="선택 삭제" /></td></tr></tfoot>';
        addHtml += '</table>';
        $('#' + fieldID).append(addHtml);

        $('input[name*=\'optionPrice\']').number_only();
        $('input[name*=\'stockCnt\']').number_only();

        $("input.js-type-normal").bind('keyup', function () {
            $(this).val($(this).val().replace(/[^a-z0-9_]*/gi, ''));
        });

        <?php if ($data['optionFl'] == 'y') {?>    if (optionValueFill == true) {
            fill_value();
        }<?php }?>

        optionGridChange = true;
    }


    function delete_option(inputName, trName) {

        $('input[name="' + inputName + '"]:checked').each(function () {
            field_remove(trName + $(this).val());
        });
    }


    /**
     * 옵션값 일괄 적용
     */
    function option_value_apply() {


        if($('input[name="optionY[optionNo][]"]:checked').length) {

            var optionPrice = $('#option_opotionPriceApply').val();
            var stockCnt = $('#option_stockCntApply').val();
            var optionCode = $('#option_optionCodeApply').val();
            var optionViewFl = $('#option_optionViewFlApply').val();
            var optionSellFl = $('#option_optionSellFlApply').val();
            var optionCostPrice = $('#option_optionCostPriceApply').val();
            var optionMemo = $('#option_optionMemoApply').val();


            $('input[name="optionY[optionNo][]"]').each(function (i) {
                if (this.checked) {

                    if (optionCostPrice !='') {
                        $('input[name*=\'optionY\[optionCostPrice\]\']').eq(i).val(optionCostPrice);
                    }
                    if (optionPrice !='') {
                        $('input[name*=\'optionY\[optionPrice\]\']').eq(i).val(optionPrice);
                    }
                    if (stockCnt != '' && stockCnt >= 0) {
                        $('input[name*=\'optionY\[stockCnt\]\']').eq(i).val(stockCnt);
                        $("input[name='stockCnt']").val(stockCnt);
                    }
                    if (optionCode) {
                        $('input[name*=\'optionY\[optionCode\]\']').eq(i).val(optionCode);
                    }
                    if (optionMemo) {
                        $('input[name*=\'optionY\[optionMemo\]\']').eq(i).val(optionMemo);
                    }

                    $('select[name*=\'optionY\[optionViewFl\]\']').eq(i).val(optionViewFl);
                    $('select[name*=\'optionY\[optionSellFl\]\']').eq(i).val(optionSellFl);

                }
            });


        } else {
            alert("선택된 옵션이 없습니다.");
            return false;
        }
    }

    /**
     * 색상 ColorPicker
     */
    function option_color_picker() {
        $('.color-selector')
            .ColorPicker({
                onSubmit: function (hsb, hex, rgb, el) {
                    $(el).prev().val('#' + hex); // prev input element
                    $(el).ColorPickerHide();
                    $(el).css('backgroundColor', '#' + hex);
                }
                , onBeforeShow: function (cal) {
                    var color = $($(cal).data('colorpicker').el).prev().val(); // prev input element
                    var hex = $.ColorNameToHex(color);
                    if (hex != undefined) color = hex;
                    $(this).ColorPickerSetColor(color.replace(/#/, ''));
                }
            })
            .css('backgroundColor', function () {
                var self = this;
                var ipt = $(this).prev();
                ipt.change(function () {
                    $(self).css('backgroundColor', $(this).val());
                });
                return $(this).prev().val();
            });
    }

    /**
     * 텍스트 옵션 사용
     */
    function use_option_text() {
        if ($('input:radio[name="optionTextFl"][val="y"]').prop('checked', true) && $('#optionTextForm tbody').length > 0) {
            return;
        } else {
            add_option_text();
        }
    }

    /**
     * 텍스트 옵션 추가
     */
    function add_option_text() {
        var fieldID = 'optionTextForm';
        var fieldNoChk = $('#' + fieldID).find('tr:last').get(0).id.replace(fieldID, '');
        if (fieldNoChk == '') {
            var fieldNoChk = 0;
        }
        var fieldNo = parseInt(fieldNoChk) + 1;
        var fieldCnt = $('#' + fieldID).find('tr').length;
        if (fieldCnt >= <?php echo(DEFAULT_LIMIT_TEXTOPTION + 1);?>) {
            alert('텍스트 옵션은 <?=DEFAULT_LIMIT_TEXTOPTION;?>개가 제한 입니다.');
            return false;
        }
        var addHtml = '';
        addHtml += '<tr id="' + fieldID + fieldNo + '">';
        addHtml += '<td class="left"> <div class="form-inline">';
        addHtml += '<input type="text" name="optionText[optionName][]" value="" class="form-control width-lg" /> ';
        if (fieldNoChk != '0') addHtml += '<input type="button" onclick="field_remove(\'' + fieldID + fieldNo + '\');" value="-" class="btn btn-gray btn-xs" />';

        if (fieldNoChk == '0') addHtml += '<input type="button" onclick="add_option_text();" value="+" class="btn btn-black btn-xs"   />';


        addHtml += '</div></td>';
        addHtml += '<td><div class="form-inline"><?=gd_currency_symbol();?><input type="text" name="optionText[addPrice][]" value="" class="width-sm form-control" /><?=gd_currency_string();?></div></td>';
        addHtml += '<td class="center"><div class="form-inline"><input type="text" name="optionText[inputLimit][]" value="" class="width-sm form-control" /> 글자</div></td>';
        addHtml += '<td class="center"><input type="checkbox" name="optionText[mustFl][' + fieldNoChk + ']" value="y" /></td>';
        addHtml += '</tr>';
        $('#' + fieldID).append(addHtml);

        $('input[name*=\'addPrice\']').number_only();
        $('input[name*=\'inputLimit\']').number_only(4, 100, 100);
    }

    /**
     * 관련 상품 선택
     *
     * @param string thisID 종류 ID
     */
    function relation_switch(thisID) {
        if (thisID == 'n') {
            $('#relationGoodsConf').hide();
            $('.relationSet').hide();
        } else {
            $('#relationGoodsConf').show();
            if (thisID == 'a') {
                $('#relationGoodsConfText').html('자동 설정');
                display_toggle('relationGoodsConfAuto', 'show');
                display_toggle('relationGoodsConfManual', 'hide');
                $('.relationSet').hide();
            } else if (thisID == 'm') {
                $('#relationGoodsConfText').html('수동 설정');
                display_toggle('relationGoodsConfAuto', 'hide');
                display_toggle('relationGoodsConfManual', 'show');
                $('.relationSet').show();
            }
        }
    }

    /**
     * 이미지 저장소에 따른 상품 이미지 종류
     *
     * @param string modeType 이미지 저장소 종류
     */
    function image_storage_selector(storageName) {
        if (storageName == '') {
            return;
        }
        <?php  if ($data['mode'] == "register") {?>
        var addPath = '코드1/코드2/코드3/상품코드/';
        <?php }
        else {?>
        var addPath = '<?=$data['imagePath']?>';
        <?php }?>
        $('.imageStorageText').html($('#imageStorage option:selected').text());
        if (storageName !='url') {
            $.post("goods_ps.php", {mode: "getStorage", storage: storageName})
                .done(function (data) {
                    $("#imageStorageModeNm").html(data + addPath);
                });
        }
        if (storageName =='url') {
            $('#goodsImageImg').hide();
            $('#goodsImageUrl').show();
            $('#facebookImg').hide();
            $('#facebookUrl').show();
            $('#imageStorageMode_none').hide();
            $("#imageStorageModeNm").html('"URL 직접입력"은 따로 저장 경로가 없이 아래 작성한 URL로 대체 됩니다.');
        } else if (storageName == 'local') {
            $('#goodsImageImg').show();
            $('#goodsImageUrl').hide();
            $('#imageStorageMode_none').hide();
            $('#facebookImg').show();
            $('#facebookUrl').hide();
        } else if (storageName == '') {
            $('#goodsImageImg').show();
            $('#goodsImageUrl').hide();
            $('#imageStorageMode_none').show();
            $('#facebookImg').show();
            $('#facebookUrl').hide();
        } else {
            $('#goodsImageImg').show();
            $('#goodsImageUrl').hide();
            $('#imageStorageMode_none').hide();
            $('#facebookImg').show();
            $('#facebookUrl').hide();
        }

        image_storage_selector_option(storageName);
    }

    /**
     * 이미지 저장소에 따른 상품 옵션 추가노출 이미지 종류
     *
     * @param string modeType 이미지 저장소 종류
     */
    function image_storage_selector_option(storageName) {

        $('span[id*=\'imageStorageModeOption\']').removeClass();
        if (storageName == 'url') {
            $(".js-option-image-url").hide();
            $('span[id*=\'imageStorageModeOptionGoodsImage_\']').addClass('display-none');
            $('span[id*=\'imageStorageModeOptionGoodsText_\']').addClass('display-block');
        } else {
            $(".js-option-image-url").show();
            $('span[id*=\'imageStorageModeOptionGoodsImage_\']').addClass('display-block');
            $('span[id*=\'imageStorageModeOptionGoodsText_\']').addClass('display-none');
        }
    }

    /**
     * 이미지 저장소에 따른 상품 이미지 input 종류 (text or file)
     *
     * @param string fieldID 해당 ID
     * @param string addBtnYN 추가버튼 여부
     * @param string urlType URL 직접 입력 여부
     */
    function goods_image(fieldID, addBtnYN, urlType) {
        if ($('#' + fieldID).find('div:last').html()) {

            var fieldNoChk = $('#' + fieldID + ' > div').last().attr('id').replace(fieldID, '');
            if (fieldNoChk == '') {
                var fieldNoChk = 0;
            }
        } else {
            var fieldNoChk = 0
        }

        var fieldNo = parseInt(fieldNoChk) + 1;
        var addBtnFl = "n";
        if((addBtnYN =='r' || addBtnYN =='y') && urlType == 'y') {
            if($("#"+fieldID).find("input[id*='"+fieldID+"URL']").length == 0) {
                addBtnFl = "y";
            }
        }

        if(fieldID == 'imageFbGoods' || fieldID == 'imageFbGoodsURL') {
            var canAdd = $('#'+fieldID).find(".form-inline").length;
            if (canAdd > 9) {
                alert('페이스북 피드 이미지 업로드는 10개까지 가능합니다.');
                return;
            }
        }
        if (fieldNo == 1 || addBtnFl == 'y') {
            var addBtn = '<input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus" onclick="goods_image(\'' + fieldID + '\',\'y\',\'' + urlType + '\');" /> ';
        } else {
            if (addBtnYN == 'r') var addBtn = '';
            else var addBtn = '<input type="button" value="삭제" class="btn btn-sm btn-white btn-icon-minus"  onclick="field_remove(\'' + fieldID + fieldNo + '\');" /> ';
        }


        if(addBtnYN =='r')  addBtnYN ='y';

        var addHtml = '';
        addHtml += '<div id="' + fieldID + fieldNo + '" class="form-inline">';

        var imageInfo = '';
        if (urlType == 'y') {
            imageInfo = '<span id="' + fieldID + 'PreView' + fieldNoChk + '"></span>';
            if(fieldID == 'imageFbGoods' || fieldID == 'imageFbGoodsURL') { // 페이스북 피드 이미지 입력하는 경우
                addHtml += '<input type="text" id="' + fieldID + 'URL' + fieldNoChk + '" name="imageFb[' + fieldID + '][]" value="" class="form-control width60p" />' + imageInfo;
            } else {
                addHtml += '<input type="text" id="' + fieldID + 'URL' + fieldNoChk + '" name="image[' + fieldID + '][]" value="" class="form-control width60p" />' + imageInfo;
            }
        } else {
            var clickCheck = '';
            if (fieldID == 'imageOriginal') {
                clickCheck = 'onclick="image_resize_check_all(\'imageResize[original]\',\'y\');"';
            } else {
                imageInfo = '<span id="' + fieldID + 'PreView' + fieldNoChk + '"></span>';
            }
            if(fieldID == 'imageFbGoods'){ // 페이스북 피드 이미지 입력하는 경우
                addHtml += '<input type="file" name="imageFb[' + fieldID + '][]" class="form-control" ' + clickCheck + ' />' + imageInfo;
            }else {
                addHtml += '<input type="file" name="image[' + fieldID + '][]" class="form-control" ' + clickCheck + ' />' + imageInfo;
            }
        }
        if (addBtnYN == 'y') {
            addHtml += addBtn;
        }

        addHtml += '</div>';

        if (urlType == 'y') {
            $('#' + fieldID).append(addHtml);
        } else {
            if($("#"+fieldID).find("input[type='file']:last").closest('div').length == 0) {
                $('#' + fieldID).append(addHtml);
            } else {
                $("#"+fieldID).find("input[type='file']:last").closest('div').after(addHtml);
            }
        }

        init_file_style();
    }

    /**
     * 원본이미지의 리사이즈 체크
     *
     * @param string checkName 원본 이미지 체크박스 name
     * @param string addBtnYN 원본 이미지의 input file 를 클릭 여부
     */
    function image_resize_check_all(checkName, fileTypeChk) {

        if ($('input[name="imageAddUrl"]').is(":checked")) {
            $('input[name="imageAddUrl"]').prop("checked",false);
        }

        if (fileTypeChk == 'y') {
            $('input[name=\'' + checkName + '\']').prop('checked', true);
        }
        var checkboxCnt = $('input[name*=\'imageResize\']').length;
        var checkboxNm = '';
        for (var i = 1; i < checkboxCnt; i++) {
            checkboxNm = $('input[name*=\'imageResize\']:checkbox').eq(i).get(0).name;
            if ($('input[name=\'' + checkName + '\']:checked').length == 1) {
                $('input[name=\'' + checkboxNm + '\']').prop('checked', true);
            } else {
                $('input[name=\'' + checkboxNm + '\']').prop('checked', false);
            }
            image_resize_check(checkboxNm, 'y');
        }
    }

    /**
     * 상품이미지 URL직접입력 추가 사용
     *
     */
    function image_add_url() {
        if ($('input[name="imageResize[original]"]').is(":checked")) {
            alert("이미지 리사이즈 사용시 URL 직접입력 추가사용이 불가능합니다.");
            $('input[name="imageAddUrl"]').prop('checked', false);
            return false;
        }

        if($('input[name="imageAddUrl"]').is(":checked")) {
            <?php foreach($imageInfo as $k => $v) { ?>
            if($("#image<?=ucfirst($k)?>").find("input[id*='image<?=ucfirst($k)?>URL']").length == 0)  {
                goods_image('image<?=ucfirst($k); ?>','<?=$v['addKey']; ?>','y');
            } else {
                $("#image<?=ucfirst($k)?>").find("input[id*='image<?=ucfirst($k)?>URL']").closest('div').show();
            }
            <?php } ?>
        } else {
            <?php if($data['mode'] == 'modify') { ?>
            <?php foreach($imageInfo as $k => $v) { ?>
            $("#image<?=ucfirst($k)?>").find("input[id*='image<?=ucfirst($k)?>URL']").closest('div').hide();
            <?php } ?>
            <?php } else { ?>
            <?php foreach($imageInfo as $k => $v) { ?>
            $("#image<?=ucfirst($k)?>").find("input[id*='image<?=ucfirst($k)?>URL']").closest('div').remove();
            <?php } ?>
            <?php } ?>
        }
    }

    /**
     * 상품 옵션 이미지 이미지 URL직접입력 추가 사용
     *
     */
    function option_image_add_url() {

        $('span[id*=\'imageStorageModeOptionGoodsImage_\']').addClass('display-block');

        if($('input[name="optionImageAddUrl"]').is(":checked")) {
            $('span[id*=\'imageStorageModeOptionGoodsText_\']').addClass('display-block');
        } else {
            $('span[id*=\'imageStorageModeOptionGoodsText_\']').removeClass('display-block');
        }
    }

    /**
     * 페이스북 상품 피드 이미지 URL직접입력 추가 사용
     *
     */
    function facebook_image_add_url() {

        if($('input[name="imageFbAddUrl"]').is(":checked")) { // URL직접입력 박스 체크 이벤트 발생
            if($("#imageFbGoods").find("input[id*='imageFbGoodsURL']").length == 0)  {
                goods_image('imageFbGoods','y','y');
            } else {
                $("#imageFbGoods").find("input[id*='imageFbGoodsURL']").closest('div').show();
            }
        } else { // URL 직접입력 체크 해제 이벤트 발생
            <?php if($data['mode'] == 'modify') { ?> // 수정일 경우
            $("#imageFbGoods").find("input[id*='imageFbGoodsURL']").closest('div').hide(); // url 입력창 숨김
            <?php } else {?>
            $("#imageFbGoods").find("input[id*='imageFbGoodsURL']").closest('div').remove(); // url 입력창 삭제
            <?php } ?>
        }
    }

    /**
     * 각 이미지의 리사이즈 체크
     *
     * @param string checkName 해당 이미지 체크박스 name
     * @param string allCheck 전부 체크 되었는지의 여부
     */
    function image_resize_check(checkName, allCheck) {
        var tempID = checkName.replace(/Resize\[|\]/g, '');
        var checkID = tempID.substring(0, 5) + tempID.substr(5, 1).toUpperCase() + tempID.substring(6);

        if ($('input[name=\'' + checkName + '\']:checked').length == 1) {
            $('#' + checkID).hide('fast');
        } else {
            $('#' + checkID).show('fast', function () {
                $('#' + checkID + 'Text').remove();
            });
        }

        if (typeof allCheck == 'undefined') {
            if ($('input[name=\'imageResize\[original\]\']:checked').length == 1) {
                $('input[name=\'imageResize\[original\]\']').prop('checked', false);
            } else {
                var checkboxCnt = $('input[name*=\'imageResize\']').length;
                var checkedCnt = $('input[name*=\'imageResize\']:checked').length;
                if (checkboxCnt == parseInt(checkedCnt + 1)) {
                    $('input[name=\'imageResize\[original\]\']').prop('checked', true);
                }
            }
        }
    }

    /**
     *  이미지 원본 사이즈 구하기
     */
    function get_image_size(imgTag, url) {
        if(url == "http") {
            var imgWidth = ': 가로 ' + imgTag.naturalWidth + 'pixel(픽셀)';
            $(imgTag).siblings(".checkbox-inline").after(imgWidth);
        } else {
            //첫번째 호출인경우
            if(url == 'local') {
                var imgUrl = $(imgTag).attr('onclick').substring(14);
                var lastIndex = imgUrl.indexOf("'");
                var imgSrc = imgUrl.substring(0,lastIndex);
                var realImageTag = '<img src="'+imgSrc+'" style="display: none;" onload="get_image_size(this, null, name)"/>';
                $(imgTag).append(realImageTag);
            } else {
                var imgWidth = ' : 가로 ' + $(imgTag).get(0).naturalWidth + 'pixel(픽셀)&nbsp;';
                $(imgTag).parent().siblings(".checkbox-inline").after(imgWidth);
                return;
            }
        }
    }

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            addParam['callFunc'] = 'setScmSelect';
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
            $('select[name="add_must_info_sel"]').html("<option>= 상품 필수 정보 선택 =</option>");
            $('select[name="add_goods_info_sel"]').html("<option>= 추가 상품 그룹  선택 =</option>");
        }

        if (typeStr == 'memberGroup') {
            addParam['parentFormID'] = 'memberGroupLayer';
            addParam['dataFormID'] = 'infoMemberGroup';
            addParam['dataInputNm'] = 'memberGroupNo';
            typeStr = 'member_group';
        }

        if (typeStr == 'accessMemberGroup') {
            addParam['parentFormID'] = 'accessMemberGroupLayer';
            addParam['dataFormID'] = 'infoAccessMemberGroup';
            addParam['dataInputNm'] = 'accessMemberGroupNo';
            typeStr = 'member_group';
        }

        if (typeStr == 'relation') {
            addParam['layerFormID'] = 'layerRelationGoodsForm';
            addParam['parentFormID'] = 'relationGoodsInfo';
            addParam['dataFormID'] = 'relationGoods';
            addParam['dataInputNm'] = 'relationGoodsNo';
            typeStr = 'goods';
            addParam['callFunc'] = 'set_relation_display';

        }

        if (typeStr == 'delivery') {
            addParam['dataInputNm'] = 'deliverySno';
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
            addParam['callFunc'] = 'setDelivery';
        }

        if (typeStr == 'must_info') {
            addParam['dataInputNm'] = 'mustInFoSno';
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
            addParam['callFunc'] = 'set_add_must_info';
        }

        if (typeStr == 'detail_info_delivery') {
            addParam['detailInfoTitle'] = '배송안내 선택';
            addParam['groupCd'] = '002';
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
            addParam['key'] = 'informNm';
            addParam['callFunc'] = 'set_add_detail_info';
            typeStr = 'detail_info';
        }
        if (typeStr == 'detail_info_as') {
            addParam['detailInfoTitle'] = 'AS안내 선택';
            addParam['groupCd'] = '003';
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
            addParam['key'] = 'informNm';
            addParam['callFunc'] = 'set_add_detail_info';
            typeStr = 'detail_info';
        }
        if (typeStr == 'detail_info_refund') {
            addParam['detailInfoTitle'] = '환불안내 선택';
            addParam['groupCd'] = '004';
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
            addParam['key'] = 'informNm';
            addParam['callFunc'] = 'set_add_detail_info';
            typeStr = 'detail_info';
        }
        if (typeStr == 'detail_info_exchange') {
            addParam['detailInfoTitle'] = '교환안내 선택';
            addParam['groupCd'] = '005';
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
            addParam['key'] = 'informNm';
            addParam['callFunc'] = 'set_add_detail_info';
            typeStr = 'detail_info';
        }
        if (typeStr == 'hscode') {
            addParam['hscode'] = $("#js-hscode-add-"+mode+" select").val();
            addParam['hscodeIndex'] = mode;
            addParam['callFunc'] = 'set_hscode';
            addParam['detailInfoTitle'] =  $("#js-hscode-add-"+mode+" select option:selected").text() + ' HS코드 선택';
        }
        if (typeStr == 'category_batch') {
            addParam['noLimit'] = 'y';
        }
        if (typeStr == 'mileage_group') {
            if ($('input[name="mileageFl"]:checked').val() == 'g') {
                return;
            }
            addParam['layerFormID'] = 'mileageGroup';
            addParam['parentFormID'] = 'mileage_group';
            addParam['dataFormID'] = 'info_mileage_group';
            addParam['dataInputNm'] = 'mileageGroupInfo';
            typeStr = 'member_group';
        }
        if (typeStr == 'except_benefit_group') {
            addParam['layerFormID'] = 'exceptBenefitGroup';
            addParam['parentFormID'] = 'except_benefit_group';
            addParam['dataFormID'] = 'info_except_benefit_group';
            addParam['dataInputNm'] = 'exceptBenefitGroupInfo';
            typeStr = 'member_group';
        }
        if (typeStr == 'goods_benefit') {
            addParam['layerFormID'] = 'goodsBenefitGroup';
            addParam['parentFormID'] = 'goods_benefit_group';
            addParam['dataFormID'] = 'info_goods_benefit_group';
            addParam['dataInputNm'] = 'benefitSno';
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr, addParam);

    }

    /**
     * 관련상품 선택
     */
    function goods_search_popup()
    {
        window.open('../share/popup_goods.php?relationFl=m', 'popup_goods_search', 'width=1255, height=790, scrollbars=no');
    }

    /**
     * 관련상품 세팅
     *
     * @param  object frmData 관련상품 정보
     */
    function setRelationGoods(frmData) {
        var addHtml = "";

        $.each(frmData.info, function (key, val) {

            var stockText = "";
            // 상품 재고
            if (val.stockFl == 'n') {
                totalStock    = '∞';
            } else {
                totalStock    = val.totalStock ;
            }

            if(val.soldOutFl =='y' || totalStock== 0) stockText = "품절";
            else stockText = "정상";

            if(val.sortFix == true) {
                sortFix = "checked = 'checked'";
                tableCss = "style='background:#d3d3d3' class='add_goods_fix'";
            } else {
                sortFix = '';
                tableCss = "class='add_goods_free'";
            }

            addHtml += '<tr id="tbl_add_goods_'+val.goodsNo+'" '+tableCss+'>';
            addHtml += '<td class="center">';
            addHtml += '<input type="hidden" name="itemGoodsNm[]" value="'+val.goodsNm.replace(/<(\/)?([a-zA-Z]*)(\s[a-zA-Z]*=[^>]*)?(\s)*(\/)?>/ig, "")+'" />';
            addHtml += '<input type="hidden" name="itemGoodsPrice[]" value="'+val.goodsPrice+'" />';
            addHtml += '<input type="hidden" name="itemScmNm[]" value="'+val.scmNm+'" />';
            addHtml += '<input type="hidden" name="itemTotalStock[]" value="'+val.totalStock+'" />';
            addHtml += '<input type="hidden" name="itemBrandNm[]" value="'+val.brandNm+'" />';
            addHtml += '<input type="hidden" name="itemMakerNm[]" value="'+val.makerNm+'" />';
            addHtml += '<input type="hidden" name="itemImage[]" value="'+val.image+'" />';
            addHtml += '<input type="hidden" name="itemSoldOutFl[]" value="'+val.soldOutFl+'" />';
            addHtml += '<input type="hidden" name="itemStockFl[]" value="'+val.stockFl+'" />';
            addHtml += '<input type="hidden" name="itemGoodsDisplayFl[]" value="'+val.goodsDisplayFl+'" />';
            addHtml += '<input type="hidden" name="itemGoodsDisplayMobileFl[]" value="'+val.goodsDisplayMobileFl+'" />';
            addHtml += '<input type="hidden" name="itemIcon[]" value="'+val.goodsIcon+'" />';
            addHtml += '<input type="checkbox" name="itemGoodsNo[]" id="layer_goods_'+val.goodsNo+'"  value="'+val.goodsNo+'"/><input type="hidden" name="relationGoodsNo[]" value="' + val.goodsNo + '" /></td>';
            addHtml += '<td class="center number addGoodsNumber_'+val.goodsNo+'">'+(key + 1)+'</td>';
            addHtml += '<td class="center">'+decodeURIComponent(val.image)+'</td>';
            addHtml += '<td>'+val.goodsNm+'<input type="hidden" name="goodsNoData[]" value="'+val.goodsNo+'" />';
            addHtml += '<input type="checkbox" name="sortFix[]" class="layer_sort_fix_'+val.goodsNo+'"  value="'+val.goodsNo+'" '+sortFix+' style="display:none" >';
            addHtml += '<div>'+decodeURIComponent(val.goodsIcon) +'</div></td>';
            addHtml += '<td class="center">'+val.goodsPrice+'</td>';
            addHtml += '<td class="center">' + val.scmNm + '</td>';
            addHtml += '<td class="center">'+totalStock+'</td>';

            // 관련상품 노출기간
            val.relationGoodsNoStartYmd = (val.relationGoodsNoStartYmd == null || val.relationGoodsNoStartYmd == 'undefined') ? '' : val.relationGoodsNoStartYmd;
            val.relationGoodsNoEndYmd = (val.relationGoodsNoEndYmd == null || val.relationGoodsNoEndYmd == 'undefined') ? '' : val.relationGoodsNoEndYmd;

            addHtml += '<td class="center js-goodschoice-hide" id="relationGoodsDate_' + val.goodsNo + '">';
            addHtml += '<input type="hidden" name="relationGoodsNoStartYmd[]" id="relationGoodsStartYmd_' + val.goodsNo + '" value="' + val.relationGoodsNoStartYmd + '"><input type="hidden" name="relationGoodsNoEndYmd[]" id="relationGoodsEndYmd_' + val.goodsNo + '" value="' + val.relationGoodsNoEndYmd + '"><span id="relationGoodsDateText_' + val.goodsNo + '">';

            if (val.relationGoodsNoStartYmd && val.relationGoodsNoEndYmd) {
                addHtml += val.relationGoodsNoStartYmd + ' ~ <br />' + val.relationGoodsNoEndYmd;
            } else {
                addHtml += '지속노출';
            }
            addHtml += '</span></td>';

            addHtml += '<input type="hidden" name="regDt[]" value="'+val.regDt+'" />';
            addHtml += '<td class="center js-goodschoice-hide">' + val.regDt + '</td>';
            addHtml += '<td class="center">'+stockText+'</td>';

            // 서로등록
            val.relationGoodsEach = (val.relationGoodsEach) ? val.relationGoodsEach : 'y';
            addHtml += '<input type="hidden" id="relationGoodsEach_' + val.goodsNo + '" name="relationGoodsEach[]" value="'+ val.relationGoodsEach +'" />';
            addHtml += '<td class="center js-goodschoice-hide"><select class="form-control relationEach" name="relationEach[]" id="relationEach_' + val.goodsNo + '"  data-content="' + val.goodsNo + '">';

            if (val.relationGoodsEach == 'y') {
                addHtml += '<option value="y" selected>사용함</option>';
            } else {
                addHtml += '<option value="y" >사용함</option>';
            }

            if (val.relationGoodsEach == 'n') {
                addHtml += '<option value="n" selected>사용안함</option></td>';
            } else {
                addHtml += '<option value="n">사용안함</option></td>';
            }
            addHtml += '</tr>';
        });

        $("#relationGoodsList").html(addHtml);

        if($('input[name="relationSameFl"]')[0].checked){
            setDsiabledRelatedGoods('n');
        }else if($('input[name="relationSameFl"]')[1].checked){
            setDsiabledRelatedGoods('y');
        }else if($('input[name="relationSameFl"]')[0].checked){
            setDsiabledRelatedGoods('s');
        }

        var cnt = 1;

        $('#relationGoodsList input[name="itemGoodsNo[]"]').each(function () {
            $('#relationGoodsList .addGoodsNumber_'+$(this).val()).html(cnt);
            cnt++;
        });

        $("#relationGoodsCnt").html($('#relationGoodsInfo input[name*=\'itemGoodsNo\']').length);
    }

    /**
     * 관련상품 추가
     *
     * @param object data 관련 상품 내용
     */
    function set_relation_display(data) {
        data.dataInputNm = 'relationGoodsNo';


        $.each(data.info, function (key, val) {
            var addHtml = "";

            addHtml += '<tr id="' + data.dataFormID + '_' + val.goodsNo + '">';
            addHtml += '<td class="center"><input type="checkbox" name="' + data.dataInputNm + 'Chk[]" value="' + val.goodsNo + '"><input type="hidden" name="' + data.dataInputNm + '[]" value="' + val.goodsNo + '" /></td>';
            addHtml += '<td class="center"><img src="' + val.goodsImg + '" align="absmiddle" width="50" alt="' + val.goodsNm + '" title="' + val.goodsNm + '" /></td>';
            addHtml += '<td >' + val.goodsNm + '</td>';
            addHtml += '<td class="center">' + val.goodsPrice + '</td>';
            addHtml += '<td class="center">' + val.totalStock + '</td>';
            addHtml += '<td class="center"><input type="hidden" name="' + data.dataInputNm + 'StartYmd[]" id="relationGoodsStartYmd_' + val.goodsNo + '"><input type="hidden" name="' + data.dataInputNm + 'EndYmd[]"  id="relationGoodsEndYmd_' + val.goodsNo + '"><span id="relationGoodsDateText_' + val.goodsNo + '">지속노출</span></td>';
            addHtml += '<td class="center">' + val.regDt + '</td>';
            addHtml += '<td class="center">' + val.stockTxt + '</td>';
            addHtml += '<td class="center"><select class="form-control relationEach" name="relationEach[]"><option value="y">사용함</option><option value="n">사용안함</option></select></td>';
            addHtml += '</tr>';


            $("#" + data.parentFormID).append(addHtml);

            if($('input[name="relationSameFl"]')[0].checked){
                setDsiabledRelatedGoods('n');
            }else if($('input[name="relationSameFl"]')[1].checked){
                setDsiabledRelatedGoods('y');
            }else if($('input[name="relationSameFl"]')[0].checked){
                setDsiabledRelatedGoods('s');
            }

        });

        $("#relationGoodsCnt").html($('#relationGoodsInfo input[name*=\'relationGoodsNoChk\']').length);


    }

    function delete_relation(inputName, trName) {

        if (inputName && trName) {

            var chkCnt = $('input[name="itemGoodsNo[]"]:checked').length;
            if (chkCnt == 0) {
                alert('선택된 관련상품이 없습니다.');
                return;
            }

            delete_option(inputName, trName);
        } else {
            $('#relationGoodsInfo').html(relatedGoodsTbl);
        }

        $("#relationGoodsCnt").html($('#relationGoodsInfo input[name*=\'itemGoodsNo\']').length);
    }

    /**
     * 구매가능회원
     *
     * @param string group type 선택 구룹
     */
    function set_goods_permission(val,type,disabled) {
        if (val == 'group') {
            $("#"+type+"Btn").attr("disabled", false);
            layer_register(type);
        } else {
            $("#"+type+"Btn").attr("disabled", true);
            $("#"+type+"Layer").html('');
        }

        if (val != 'all') {
            $("input[name="+disabled+"]").attr("disabled", false);
            $("input[name="+disabled+"]").closest("label").css("color","#333333");
        } else {
            $("input[name="+disabled+"]").attr("disabled", true);
            $("input[name="+disabled+"]").closest("label").css("color","#999999");

            if(type=='memberGroup') {
                $("input[name='goodsPermissionPriceStringFl']").prop("checked",false);
                $(".js-goods-permission-price-string").hide();
            }
        }
    }


    /**
     * 공급사 선택 후 세팅
     *
     * @param object data 공급사 내용
     */
    function setScmSelect(data) {
        displayTemplate(data);

        //수수료 세팅
        $('input[name="commission"]').val(data.info[0].scmCommission);
        setCommissionPrice();

        //기존 추가 상품 삭제
        $("#tbl_add_goods_set tbody").html('');

        //기존 상품필수 정보 삭제
        $("#addMustInfo tbody").html('');

        //관련상품 삭제
        $("#relationGoodsInfo tbody").html('');
        $("#relationGoodsCnt").html(0);

        //기존 배송정책 삭제
        $('input[name="deliverySno"]').val('');

        setScmInfo();
    }


    /**
     * 공급사에 따른 내용 선택
     *
     */
    function setScmInfo(commissionFl) {

        var scmFl = $('input[name="scmFl"]:checked').val();
        var scmNo = $('input[name="scmNo"]').val();

        if (scmFl == 'n') {
            $("#scmLayer").html('');
            if(commissionFl =='y') $('input[name="commission"]').val('0');
            $('input[name="deliverySno"]').val('');
            scmNo = '';
        }

        //추가상품그룹
        $.post('./add_goods_ps.php', {'mode': 'search_scm', 'scmNo': scmNo}, function (data) {
            if(data) {
                var addGoodsGroup = $.parseJSON(data);
                if ($.type(addGoodsGroup) != 'array') var addGoodsGroup = {};

                var addHtml = "<option>= 추가 상품 그룹 선택 =</option>";

                if (addGoodsGroup) {
                    $.each(addGoodsGroup, function (key, val) {

                        addHtml += "<option value='" + val.sno + "'>" + val.groupNm + "</option>";

                    });
                }

                $('select[name="add_goods_info_sel"]').html(addHtml);
            }
        });

        //이용안내
        //배송안내 002
        //as안내 003
        //환불안내 004
        //교환안내 005

        var info_parameters = {
            'mode': 'search_scm_goods_info',
            'goods_mode': '<?=$data['mode'];?>',
            <?php if($data['mode'] == 'modify' || empty($applyGoodsCopy) === false) { ?>
            'detailInfoDelivery': "<?=$data['detailInfoDelivery']?>",
            'detailInfoAS': "<?=$data['detailInfoAS']?>",
            'detailInfoRefund': "<?=$data['detailInfoRefund']?>",
            'detailInfoExchange': "<?=$data['detailInfoExchange']?>",
            'defaultScmNo': '<?=$data['scmNo'];?>',
            <?php } ?>
            'scmNo': scmNo

        };

        $.post('../policy/goods_ps.php', info_parameters, function (data) {

            var goodsInfo = $.parseJSON(data);

            if ($.type(goodsInfo.default) == 'object') {

                $.each(goodsInfo.default, function (key, val) {

                    $('input[name=' + key + ']').val(val.informCd);
                    $('#' + key + 'InformNm').html(val.informNm);

                    try {
                        oEditors.getById[key + 'SelectionInput'].setIR(val.content);
                    }
                    catch(err) {
                        $('#' + key + 'SelectionInput').val(val.content);
                    }

                    <?php if($data['mode'] != 'modify') { ?>
                    if($('input[name=' + key + 'Fl]:checked').val() == 'selection' && val.informCd == 0){
                        $('input:radio[name=' + key + 'Fl]:input[value=no]').prop("checked", true);
                        infoToggleEditor($('input[name=' + key + 'Fl]:checked').val(),key);
                    }else if($('input[name=' + key + 'Fl]:checked').val() == 'no' && val.informCd != 0){
                        $('input:radio[name=' + key + 'Fl]:input[value=selection]').prop("checked", true);
                        infoToggleEditor($('input[name=' + key + 'Fl]:checked').val(),key);
                    }
                    <?php } ?>


                });
            }
        });

        //배송비 설정
        var delivery_parameters = {
            'mode': 'search_scm',
            <?php if($data['mode'] == 'modify' || empty($applyGoodsCopy) === false) { ?>
            'deliverySno': "<?=$data['deliverySno']?>",
            <?php } ?>
            'scmNo': scmNo
        };

        if (scmFl == 'y' && scmNo == '') delivery_parameters.deliverySno = '';


        $.post('../policy/delivery_ps.php', delivery_parameters, function (data) {

            var deliveryData = $.parseJSON(data);

            if (typeof deliveryData.sno == 'undefined') {
                $('input[name="deliverySno"]').val('');
                $('#deliveryLayer .deliverySnoNm').html('');
            }
            else {
                $('input[name="deliverySno"]').val(deliveryData.sno);
                $('#deliveryLayer .deliverySnoNm').html(deliveryData.method);

                $("#deliveryLayer span").attr("id", "info_delivery_" + deliveryData.sno);
            }

        });


    }

    /**
     * 배송비 선택 후 세팅
     *
     * @param object data 공급사 내용
     */
    function setDelivery(data) {
        //배송사 세팅
        $.each(data.info, function (key, val) {
            $('input[name="deliverySno"]').val(val.deliveryNo);
            $('#deliveryLayer .deliverySnoNm').html(val.deliveryNoNm);
            $("#deliveryLayer span").attr("id", "info_delivery_" + val.deliveryNo);
        });

    }

    /**
     * 색깔 추가
     *
     * @param string color 색깔
     */
    function selectColor(val) {

        var color = $(val).data('color');
        var title = $(val).data('content');

        if ($("#goodsColor_" + color).length == '0') {
            var addHtml = "<div id='goodsColor_" + color + "' class='btn-group btn-group-xs'>";
            addHtml += "<input type='hidden' name='goodsColor[]' value='" + color + "'>";
            addHtml += "<button type='button' class='btn btn-gray js-popover' data-html='true' data-content='"+title+"' data-placement='bottom' style='background:#" + color + ";border:1px solid #efefef;'>&nbsp;&nbsp;&nbsp;</button>";
            addHtml += "<button type='button' class='btn btn-icon-delete' data-toggle='delete' data-target='#goodsColor_" + color + "'>삭제</button></div>";
        }
        $("#selectColorLayer").append(addHtml);

        if (!$("#selectColorLayer").children().is('h5')) {
            $("#selectColorLayer").prepend('<h5>선택된 색상</h5>');
            $("#selectColorLayer").show();
        }

        $('.js-popover').popover({trigger: 'hover',container: '#content',});

    }


    /**
     * 필수 정보 세팅
     *
     * @param string val
     */
    function set_add_must_info(mustInfo) {
        $.each(mustInfo.info, function (k, v) {
            if(v.mustInfoSno) {
                //필수정보 세팅
                $.post('./goods_must_info_ps.php', {'mode': 'select_json', 'sno': v.mustInfoSno}, function (data) {
                    var mustinfo = $.parseJSON(data);
                    var addMustInfo = 'addMustInfo';
                    // 해당 필수정보 선택시 필드가 없는 경우 에러 발생해서 추가
                    if (_.isObject(mustinfo)) {
                        $.each(mustinfo, function (key, val) {
                            add_must_info(val.count);
                            var fieldNoChk = $('#' + addMustInfo).find('tr:last').get(0).id.replace(addMustInfo, '');
                            if (fieldNoChk == '') {
                                var fieldNoChk = 0;
                            }

                            var tdCnt = 0;
                            $.each(val.info, function (key1, val1) {
                                $('input[name="addMustInfo[infoTitle][' + fieldNoChk + '][' + tdCnt + ']"]').val(key1);
                                $('input[name="addMustInfo[infoValue][' + fieldNoChk + '][' + tdCnt + ']"]').val(val1);
                                tdCnt++;
                            });
                        });
                    }
                });
            }
        });
    }

    /**
     * 필수 정보 세팅
     *
     * @param string val
     */
    function set_add_detail_info(detailInfo) {

        $.each(detailInfo.info, function (k, v) {
            if(v.detailInfoInformCd) {
                //필수정보 세팅
                $.post('../policy/goods_ps.php', {'mode': 'search_detail_info', 'informCd': v.detailInfoInformCd}, function (data) {
                    var detailInfo = $.parseJSON(data);

                    if(detailInfo.groupCd == '002') var detailInfoId = 'detailInfoDelivery';
                    if(detailInfo.groupCd == '003') var detailInfoId = 'detailInfoAS';
                    if(detailInfo.groupCd == '004') var detailInfoId = 'detailInfoRefund';
                    if(detailInfo.groupCd == '005') var detailInfoId = 'detailInfoExchange';

                    $.each(detailInfo, function (key, val) {

                        if(key == 'informCd'){
                            $('input[name='+detailInfoId+']').val(val);
                        }else if(key == 'informNm'){
                            $('#'+detailInfoId+'InformNm').html(val);
                        }else if(key == 'content'){
                            oEditors.getById[detailInfoId+'SelectionInput'].setIR(val);
                        }

                    });

                });
            }
        });
    }
    /**
     * 추가상품
     *
     * @param string val
     */
    function set_add_goods_info(val) {
        var scmFl = $('input[name="scmFl"]:checked').val();
        if (scmFl == '') {
            alert('공급사를 먼저 선택해주세요2');
            return false;
        }

        $.post('./add_goods_ps.php', {'mode': 'select_json', 'sno': val}, function (data) {
            setAddGoods($.parseJSON(data));
        });
    }


    /**
     * 추가 상품 선택
     *
     * @author artherot
     * @param string orderNo 주문 번호
     */
    function add_goods_search_popup() {
        var scmFl = $('input[name="scmFl"]:checked').val();
        if (scmFl == '' || typeof scmFl == 'undefined') {
            alert('공급사를 먼저 선택해주세요3');
            return false;
        }

        var scmNo = '';
        var scmNoNm = '';
        if (scmFl == 'y') {
            scmNo = $('input[name="scmNo"]').val();
            scmNoNm = $('input[name="scmNoNm"]').val();
        }

        window.open('../share/popup_add_goods.php?scmFl=' + scmFl + '&scmNo=' + scmNo + '&scmNoNm=' + scmNoNm, 'member_crm', 'width=1210, height=705, scrollbars=no');

    }
    ;

    /**
     * 추가 상품 등록
     *
     * @author artherot
     * @param string orderNo 주문 번호
     */
    function add_goods_register_popup() {
        var scmFl = $('input[name="scmFl"]:checked').val();
        if (scmFl == '' || typeof scmFl == 'undefined') {
            alert('공급사를 먼저 선택해주세요');
            return false;
        }

        var scmNo = '';
        var scmNoNm = '';

        if (scmFl == 'y') {
            scmNo = $('input[name=scmNo]').val();
            scmNoNm = $('input[name=scmNoNm]').val();
        }

        window.open('../goods/add_goods_register.php?popupMode=yes&addGroup=true&scmFl=' + scmFl + '&scmNo=' + scmNo + '&scmNoNm=' + scmNoNm, 'member_crm', 'width=1210, height=700, scrollbars=yes');
    }
    ;


    /**
     * 수수료 계산
     *
     */
    function setCommissionPrice() {

        var goodsPrice = $('input[name="goodsPrice"]').val();
        var commission = $('input[name="commission"]').val();

        $('input[name*=\'commissionText\']').val(commission);

        var supplyPrice = goodsPrice - (goodsPrice * (commission / 100));
        var commissionPrice = goodsPrice - supplyPrice;

        $('input[name="supplyPrice"]').val(numeral(supplyPrice.toFixed(<?=$conf['currency']["decimal"]?>)).format());
        $('input[name="commissionPrice"]').val(numeral(commissionPrice.toFixed(<?=$conf['currency']["decimal"]?>)).format());
    }


    /**
     * 관련상품 기간 설정
     *
     */
    function setRelationGoodsDisplay() {


        var chkCnt = $('input[name="itemGoodsNo[]"]:checked').length;
        if (chkCnt == 0) {
            alert('선택된 관련상품이 없습니다.');
            return;
        }

        var $clone = $("#relationGoodsDisplay").clone();

        $clone.attr("id", "relationGoodsDisplayLayer");
        $clone.find("#relationGoodsDisplayDate").attr("id", "relationGoodsDisplayDateLayer");
        $clone.find('input[name="relationGoodsDisplayDate[]"]').attr("name", "relationGoodsDisplayDateLayer[]");
        $clone.find('div[data-target-name="relationGoodsDisplayDate"]').attr("data-target-name", "relationGoodsDisplayDateLayer");

        BootstrapDialog.show({
            title: '기간설정',
            message: $clone,
            closable: true,
            onshown: function () {
                init_datetimepicker();
                $("#relationGoodsDisplayLayer").removeClass('display-none');
            },
            buttons: [{
                label: '확인',
                cssClass: 'btn-red',
                action: function (dialogItself) {

                    var startYmd = "";
                    var endYmd = "";

                    var relationDataFl = $('#relationGoodsDisplayLayer input[name="relationDataFl"]:checked').val();
                    if (relationDataFl == 'y') relationFlText = "지속노출";
                    else {
                        var startYmd = $('#relationGoodsDisplayLayer input[name="relationGoodsDisplayDateLayer[]"]').eq(0).val();
                        var endYmd = $('#relationGoodsDisplayLayer input[name="relationGoodsDisplayDateLayer[]"]').eq(1).val();

                        if (startYmd =='' || endYmd =='') {
                            alert("관련상품 노출 기간을 입력해주세요.");
                            return false;
                        }

                        if (startYmd && endYmd && startYmd > endYmd) {
                            alert("종료일은 시작일 이후로 설정해 주세요.");
                            return false;
                        }

                        relationFlText = startYmd + " ~ " + endYmd;

                    }


                    $('input[name="itemGoodsNo[]"]:checked').each(function () {

                        $("#relationGoodsStartYmd_" + $(this).val()).val(startYmd);
                        $("#relationGoodsEndYmd_" + $(this).val()).val(endYmd);

                        $("#relationGoodsDateText_" + $(this).val()).html(relationFlText);
                    });

                    dialogItself.close();

                }
            }, {
                label: '취소',
                action: function (dialogItself) {
                    dialogItself.close();
                }
            }]
        });
    }

    /**
     * 관련상품 보이기 관련
     *
     * @param string modeStr 상태
     */
    function relationDisplayToggle(modeStr) {
        if (modeStr == 'show') {
            $(".bootstrap-dialog-message #relationGoodsDisplayDate").attr('class', 'display-block');
        } else if (modeStr == 'hide') {
            $(".bootstrap-dialog-message #relationGoodsDisplayDate").attr('class', 'display-none');
        }
    }


    var addGoodsGroupActive = "0";

    /**
     * 추가 상품 세팅
     *
     * @param  object frmData 추가상품 정보
     */
    function setAddGoods(frmData) {

        var addHtml = "";

        $.each(frmData.info, function (key, val) {

            // 상품 재고
            if (val.stockFl == '0') {
                totalStock = '∞';
            } else {
                totalStock = val.totalStock;
            }

            if (val.soldOutFl == 'y' || totalStock == '0') stockText = "품절";
            else stockText = "정상";


            if (val.sortFix == true) {
                sortFix = "checked = 'checked'";
                tableCss = "style='background:#d3d3d3' class='add_goods_fix'";
            }
            else {
                sortFix = '';
                tableCss = "class='add_goods_free'";
            }


            addHtml += '<tr id="tbl_add_goods_' + val.goodsNo + '" ' + tableCss + '>';
            addHtml += '<td class="center">';

            addHtml += '<input type="hidden" name="itemGoodsNm[]" value="' + val.goodsNm + '" />';
            addHtml += '<input type="hidden" name="itemGoodsPrice[]" value="' + val.goodsPrice + '" />';
            addHtml += '<input type="hidden" name="itemScmNm[]" value="' + val.scmNm + '" />';
            addHtml += '<input type="hidden" name="itemTotalStock[]" value="' + val.totalStock + '" />';
            addHtml += '<input type="hidden" name="itemSoldOutFl[]" value="' + val.soldOutFl + '"  />';
            addHtml += '<input type="hidden" name="itemStockFl[]" value="' + val.stockFl + '"  />   ';
            addHtml += '<input type="hidden" name="itemBrandNm[]" value="' + val.brandNm + '" />';
            addHtml += '<input type="hidden" name="itemMakerNm[]" value="' + val.makerNm + '" />';
            addHtml += '<input type="hidden" name="itemOptionNm[]" value="' + val.optionNm + '" />';
            addHtml += '<input type="hidden" name="itemImage[]" value="' + val.image + '" />';
            addHtml += '<input type="checkbox" name="itemGoodsNo[]" id="layer_goods_' + val.goodsNo + '"  value="' + val.goodsNo + '"/></td>';
            addHtml += '<td class="center number addGoodsNumber_' + val.goodsNo + '">' + (key) + '</td>';
            addHtml += '<td class="center">' + decodeURIComponent(val.image) + '</td>';
            addHtml += '<td>' + val.goodsNm + '<input type="hidden" name="goodsNoData[]" value="' + val.goodsNo + '" /><input type="checkbox" name="sortFix[]" class="layer_sort_fix_' + val.goodsNo + '"  value="' + val.goodsNo + '" ' + sortFix + ' style="display:none"></td>';
            addHtml += '<td class="center">' + val.optionNm + '</td>';
            addHtml += '<td class="center">' + val.goodsPrice + '</td>';
            addHtml += '<td class="center">' + val.scmNm + '</td>';
            addHtml += '<td class="center">' + totalStock + '</td>';
            addHtml += '<td class="center">' + stockText + '</td>';
            addHtml += '</tr>';

        });


        $("#addGoodsList" + addGoodsGroupActive).html(addHtml);


        var cnt = $('#addGoodsList' + addGoodsGroupActive + ' input[name="itemGoodsNo[]"]').length;

        if ($("#addGoodsGroupCnt" + addGoodsGroupActive).length) {
            $("#addGoodsGroupCnt" + addGoodsGroupActive).html(cnt);
            $("input[name='addGoodsGroupCnt[" + addGoodsGroupActive + "]']").val(cnt);
        }

        $('#addGoodsList' + addGoodsGroupActive + ' input[name="itemGoodsNo[]"]').each(function () {
            $('#addGoodsList' + addGoodsGroupActive + ' .addGoodsNumber_' + $(this).val()).html(cnt);
            cnt--;
        });

    }


    function delete_add_goods() {

        var chkCnt = $('#addGoodsList' + addGoodsGroupActive + ' input[name="itemGoodsNo[]"]:checked').length;
        if (chkCnt == 0) {
            alert('선택된 상품이 없습니다.');
            return;
        }

        dialog_confirm('선택한 ' + chkCnt + '개 상품을 삭제하시겠습니까?', function (result) {
            if (result) {
                $('#addGoodsList' + addGoodsGroupActive + ' input[name="itemGoodsNo[]"]:checked').each(function () {
                    //field_remove('tbl_add_goods_' + $(this).val());
                    $(this).closest("tr").remove();

                });

                var cnt = $('#addGoodsList' + addGoodsGroupActive + ' input[name="itemGoodsNo[]"]').length;
                if ($("#addGoodsGroupCnt" + addGoodsGroupActive).length) {
                    $("#addGoodsGroupCnt" + addGoodsGroupActive).html(cnt);
                    $("input[name='addGoodsGroupCnt[" + addGoodsGroupActive + "]']").val(cnt);
                }

                $('#addGoodsList' + addGoodsGroupActive + ' input[name="itemGoodsNo[]"]').each(function () {
                    $('#addGoodsList' + addGoodsGroupActive + ' .addGoodsNumber_' + $(this).val()).html(cnt);
                    cnt--;
                });
            }
        });
    }

    function select_add_goods_group(selGroup) {

        $("#addGoodsGroupInfo th").css("background-color", "#f6f6f6");
        $("#addGoodsGroup" + selGroup + " th").css("background-color", "#D5D5D5");

        $("#tbl_add_goods_set tbody").hide();
        $("#tbl_add_goods_set tbody").removeClass('active');
        $("#addGoodsList" + selGroup).show();
        $("#addGoodsList" + selGroup).addClass('active');

        addGoodsGroupActive = selGroup;
    }

    function remove_add_goods_group(selGroup) {

        $("#addGoodsGroup" + selGroup).remove();
        $("#addGoodsList" + selGroup).remove();

        if ($("#addGoodsGroupInfo tr").length) {
            select_add_goods_group($("#addGoodsGroupInfo tr:first").data("active"));
        } else {
            $(".add-goods-group-info").hide();
        }

    }

    function set_add_goods_group() {

        var addGoodsGroupTitle = $("input[name='addGoodsGroupTitle']").val();
        if (addGoodsGroupTitle) {
            if (/[:'"<>`]/.test(addGoodsGroupTitle)) {
                alert('추가상품 표시명에 사용할 수 없는 문자가 있습니다.');
                return false;
            }
            if (addGoodsGroupTitle.length > 30) {
                alert('추가상품 표시명은 30자를 넘을 수 없습니다.');
                return false;
            }

            var groupCnt = $("#addGoodsGroupInfo tr").length;
            if (!groupCnt) groupCnt = 0;
            else  groupCnt = $("#addGoodsGroupInfo tr:last").data("active") + 1;

            var addHtml = '';
            addHtml += '<tr id="addGoodsGroup' + groupCnt + '" data-active="' + groupCnt + '" >';
            addHtml += '<th  style="background-color:#f6f6f6">';
            addHtml += '<div class="form-inline hand " onclick="select_add_goods_group(\'' + groupCnt + '\')" > · ' + addGoodsGroupTitle + '<input type="hidden" name="addGoodsGroupTitle[' + groupCnt + ']" value="' + addGoodsGroupTitle + '">(<span id="addGoodsGroupCnt' + groupCnt + '">0</span><input type="hidden" name="addGoodsGroupCnt[' + groupCnt + ']" value="0">개)</div>';
            addHtml += '<div class="form-inline"><input type="checkbox" name="addGoodsGroupMustFl[' + groupCnt + ']" value="y">필수&nbsp;<span style="float:right"><input type="button" value="삭제" class="btn btn-icon-delete" onclick="remove_add_goods_group(' + groupCnt + ')"/></span></th></tr>';

            $("#addGoodsGroupInfo").append(addHtml);
            $("input[name='addGoodsGroupTitle']").val('');
            if (groupCnt > 0) $("#addGoodsGroup" + groupCnt + " th").css("background-color", "#f6f6f6");
            else $("#addGoodsGroup" + groupCnt + " th").css("background-color", "#D5D5D5");


            var addHtml = '<tbody  id="addGoodsList' + groupCnt + '" ';

            if (groupCnt == '0') addHtml += ' class="active" ';
            else addHtml += ' style="display:none;" ';

            addHtml += '><tr id="tbl_add_goods_tr_none"><td colspan="9" class="no-data">선택된 상품이 없습니다.</td></tr></tbody>';

            $("#tbl_add_goods_set").append(addHtml);

            $(".add-goods-group-info").show();

            select_add_goods_group($("#addGoodsGroupInfo tr:first").data("active"));

        } else {
            alert("추가상품 표시명을 입력해 주세요.");
        }
    }

    function displayAddGoodsInfo(act) {

        if (act == 'y') {
            $("#addGoodsGroupTitleInfo").show();
            if ($("#addGoodsGroupInfo tr").length) $(".add-goods-group-info").show();
        } else {
            $("#addGoodsGroupTitleInfo").hide();
            $(".add-goods-group-info").hide();
        }

    }

    function set_sales_date() {
        $("input[name='salesDateFl']").eq(1).click();
    }

    /*
     * HS CODE추가 관련
     */
    function add_hscode() {
        if($("div[id*='js-hscode-add-']").length) {
            var hscodeIndex = $("div[id*='js-hscode-add-']").last().data('index')+1;
        } else {
            var hscodeIndex = 1;
        }

        var hscodeCnt = $('select[name="hscodeNation[]"]').length;
        if($('select[name="hscodeNation[]"]').length < 4) {
            var nationArr = {};
            <?php foreach($hscode as $k => $v) { ?>
            nationArr['<?=$k?>'] = '<?=$v?>';
            <?php } ?>

            var selectNation = [];
            $('select[name="hscodeNation[]"]').each(function () {
                selectNation.push($(this).val());
            });

            var hscodeNationHtml = "";
            $.each(nationArr, function(key, value) {
                if($.inArray(key,selectNation) < 0) {
                    hscodeNationHtml +="<option value='"+key+"'>"+value+"</option>";
                }
            });

            var disabled ="";
            if(hscodeCnt ==0) disabled = "disabled='disabled'";

            var addHtml = '';
            addHtml += '<div id="js-hscode-add-'+hscodeIndex+'" data-index="'+hscodeIndex+'" class="form-inline"><select class="form-control width-xs"  '+disabled+' name="hscodeNation[]" onchange="add_hscode_nation(this,'+(hscodeCnt)+')" onclick="overlap_hscode_nation(this.value)">'+hscodeNationHtml+'</select>';
            if(disabled) addHtml += '<input type="hidden" name="hscodeNation[]" value="kr">';
            addHtml += ' <button type="button" class="btn btn-sm btn-gray" onclick="layer_register(\'hscode\',\''+hscodeIndex+'\')">HS코드 선택</button>';
            addHtml += ' <input type="text" name="hscode[]" value="" class="form-control width-md"/>';
            if(hscodeCnt > 0 ) {
                addHtml += ' <button type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="field_remove(\'js-hscode-add-'+hscodeIndex+'\');">삭제</button>';
            } else {
                addHtml += ' <button type="button" class="btn btn-sm btn-white btn-icon-plus" onclick="add_hscode();">추가</button>';
            }
            addHtml += '</div>';

            $(".js-hscode-info").append(addHtml);
        } else {
            alert('HS코드 삭제 후 추가해주세요.');
            return false;
        }
    }

    /*
     * HS CODE 중복관련
     */
    var sel_hscode_nation = "";
    function add_hscode_nation(hscode,selIndex) {
        var nation = $(hscode).val();
        $('select[name="hscodeNation[]"]').each(function (index) {
            if(selIndex != index && $(this).val() == nation) {
                alert("동일한 국가가 이미 추가되었습니다. 다른 국가를 선택해주세요.");
                $(hscode).val(sel_hscode_nation);
                return false;
            }
        });
    }

    /*
     * HS CODE 중복 선택 저장
     */
    function overlap_hscode_nation(nation) {
        sel_hscode_nation = nation;
    }

    /*
     * HS CODE 세팅
     */
    function set_hscode(selIndex,hscode) {
        $("#js-hscode-add-"+selIndex+" input[name='hscode[]']").val(hscode);
    }

    /*
     * 상품할인 혜택 세팅
     */
    function display_benefit_set(value, target){
        if (value == 'n') {
            $('#' + target).empty().removeClass('active');
            display_toggle('goodsBenefitConfig','show');
        } else {
            $('#' + target).addClass('active');
            display_toggle('goodsBenefitConfig','hide')
        }
    }
    /*
     * 상품할인 혜택 수정 팝업
     */
    function goods_benefit_popup(sno) {

        var url = '/goods/goods_benefit_register.php?popupMode=yes&sno=' + sno;

        win = popup({
            url: url,
            target: '',
            width: 900,
            height: 600,
            scrollbars: 'yes',
            resizable: 'yes'
        });
        win.focus();
        return win;
    };
    /**
     * 상품수정로그 보기
     *
     * @param string goodsNo 상품번호
     * @param string regDt 등록일자
     */
    function layer_goods_log_view(goodsNo, regDt)
    {
        var loadChk	= $('#viewInfoForm').length;
        var title = "변경 이력";
        $.post('./layer_goods_modify_log.php',{ goodsNo : goodsNo, regDt : regDt }, function(data){
            if (loadChk == 0) {
                data = '<div id="viewInfoForm">'+data+'</div>';
            }
            var layerForm = data;
            BootstrapDialog.show({
                title:title,
                size : BootstrapDialog.SIZE_WIDE,
                message: $(layerForm),
                closable: true
            });
        });
    }
    //-->
</script>
<form id="frmGoods" name="frmGoods" action="./goods_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?=$data['mode']; ?>"/>
    <input type="hidden" name="goodsNo" value="<?=$data['goodsNo']; ?>"/>
    <input type="hidden" name="applyGoodsCopy" value="<?=$applyGoodsCopy?>"/>
    <?php if ($applyGoodsCopy) { ?>
        <input type="hidden" name="applyGoodsimageStorage" value="<?=$data['applyGoodsImageStorage']?>"/>
        <input type="hidden" name="applyGoodsimagePath" value="<?=$data['imagePath']?>"/>
    <?php } ?>
    <?php if ($applyNo) { ?>
        <input type="hidden" name="applyNo" value="<?=$applyNo?>"/>
    <?php } ?>
    <?php if ($data['mode'] == 'modify') { ?>
        <input type="hidden" name="applyFl" value="<?=$data['applyFl']; ?>"/>
        <input type="hidden" name="modDtUse" value=""/>
    <?php } ?>
    <?php if($popupMode == 'yes') {?>
        <div id="gnb" class="gnb-mark page-header js-affix">
            <div class="mark-btn"><a href="#goodsDisplay" class="js-goodsDisplay font-off">노출설정</a></div>
            <div class="mark-btn"><a href="#defaultInfo" class="js-defaultInfo font-off">기본정보</a></div>
            <div class="mark-btn"><a href="#addInfo" class="js-addInfo font-off">추가정보</a></div>
            <div class="mark-btn"><a href="#sellInfo" class="js-sellInfo font-off">판매정보</a></div>
            <?php if(gd_is_provider() === false){?>
                <div class="mark-btn"><a href="#goodsDiscount" class="js-goodsDiscount font-off">할인/혜택</a></div>
            <?php }?>
            <div class="mark-btn"><a href="#stockOption" class="js-stockOption font-off">옵션설정</a></div>
            <div class="mark-btn"><a href="#goodsImage" class="js-goodsImage font-off">이미지</a></div>
            <div class="mark-btn"><a href="#goodsDetail" class="js-goodsDetail font-off">상세정보</a></div>
            <div class="mark-btn"><a href="#relation" class="js-relation font-off">관련상품</a></div>
            <div class="mark-btn"><a href="#icon" class="js-icon font-off">아이콘</a></div>
            <div class="mark-btn"><a href="#detailInfo" class="js-detailInfo font-off">이용안내</a></div>
            <div class="mark-btn-right"><a href="#seoTag" class="js-seoTag font-off">SEO 설정</a></div>
        </div>
    <?php }else{?>
        <div class="page-header js-affix">
            <h3><?=end($naviMenu->location); ?></h3>
            <div class="btn-group">
                <?php if (gd_is_provider() && $data['applyFl'] == 'a' && $applyGoodsCopy === false) { ?>
                    <input type="button" value="승인처리 진행 중" class="btn btn-red"/>
                <?php } else {
                    $req = Request::get()->toArray();
                    if(!$req['page']){
                        $req['page']=1;
                    }
                    ?>
                    <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./goods_list.php?page=<?= $req['page']?>');" />
                    <?php if ($data['mode'] == 'register') { ?>
                        <input type="button" value="기존상품 복사" class="btn btn-white" id="goodsListForCopy" onclick="goods_list_layer('list');"/>
                    <?php } ?>
                    <?php if ($data['mode'] == 'modify' && gd_is_provider() === false) { ?>
                        <input type="button" value="상품수정로그" class="btn btn-white" onclick="layer_goods_log_view('<?=$data['goodsNo']?>', '<?=$data['regDt']?>')" />
                    <?php } ?>
                    <input type="submit" value="저장" class="btn btn-red"/>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <div id="layerGoodsList" class="display-none"></div>
    <div class="table-title gd-help-manual">
        카테고리 연결
        <span class="notice-info">카테고리가 먼저 등록되어 있어야 카테고리 연결이 가능합니다. <?php if (gd_is_provider() === false) { ?><a href="./category_tree.php" class="btn btn-link-red" target="_blank">카테고리 등록하기 &gt;</a><?php } ?></span>
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="categoryLink"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-categoryLink" value="<?=$toggle['categoryLink_'.$SessScmNo]?>">
    <div id="depth-toggle-line-categoryLink" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-categoryLink" >
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>카테고리 선택<br/><button type="button" class="btn btn-sm btn-gray" onclick="layer_register('category_batch')">카테고리 일괄선택</button></th>
                <td>
                    <div class="form-inline">
                        <?=$cate->getMultiCategoryBox('cateGoods', '', 'size="5" style="width:23%;height:100px;"'); ?>
                    </div>
                </td>
                <td class="border-left text-center">
                    <input type="button" value="선택" class="btn btn-2xl btn-black" id="btn_category_select">
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        선택된 카테고리
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="category"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-category" value="<?=$toggle['category_'.$SessScmNo]?>">
    <div id="depth-toggle-line-category" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-category" >
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>선택된 카테고리</th>
                <td>
                    <p class="notice-info">
                        카테고리 등록 시 상위카테고리는 자동 등록되며, 등록된 카테고리에 상품이 노출됩니다.
                        <br/> 상품 노출을 원하지 않는 카테고리는 ‘삭제’버튼을 이용하여 삭제할 수 있습니다.<br/> 등록하신 카테고리들 중 체크된 카테고리가 대표 카테고리로 설정됩니다.
                    </p>

                    <table class="table table-rows table-fixed mgt10" id="cateGoodsInfo">
                        <thead <?php if (empty($data['link'])) {
                            echo "style='display:none'";
                        } ?>>
                        <tr>
                            <?php if ($gGlobal['isUse'] === true) { ?><th class="width12p">노출상점</th><?php } ?>
                            <th class="width10p">대표설정</th>
                            <th class="width62p">연결된 카테고리</th>
                            <th class="width20p">카테고리 코드</th>
                            <th class="width10p">연결해제</th>
                        </tr>
                        </thead>
                        <tbody <?php if (empty($data['link'])) {
                            echo "style='display:none'";
                        } ?>>
                        <?php
                        if (!empty($data['link'])) {
                            foreach ($data['link'] as $key => $val) {
                                if ($val['cateLinkFl'] == 'y') {
                                    ?>
                                    <tr id="cateGoodsInfo<?=$val['cateCd']; ?>">
                                        <?php if ($gGlobal['isUse'] === true) {
                                            $flagData = $cate->getCategoryFlag($val['cateCd']);
                                            ?><td>
                                            <?php foreach($flagData as $k1 => $v1) { ?>
                                                <span class="js-popover flag flag-16 flag-<?= $k1?>" data-content="<?=$v1?>"></span>
                                            <?php } ?>
                                            </td>
                                        <?php } ?>
                                        <?php if ($applyGoodsCopy === false) { ?>
                                            <input type="hidden" name="link[sno][]" value="<?=$val['sno']; ?>" /><?php } ?>
                                        <input type="hidden" name="link[cateCd][]" value="<?=$val['cateCd']; ?>"/>
                                        <input type="hidden" name="link[cateLinkFl][]" value="<?=$val['cateLinkFl']; ?>"/>
                                        <?php if ($applyGoodsCopy === false) { ?>
                                            <input type="hidden" name="link[goodsSort][]" value="<?=$val['goodsSort']; ?>" /><?php } ?>
                                        <td class="center">
                                            <input type="radio" name="cateCd" value="<?=$val['cateCd']; ?>" <?=gd_isset($checked['cateCd'][$val['cateCd']]); ?> />
                                        </td>
                                        <td><?=$cate->getCategoryPosition($val['cateCd'],0,' &gt; ',false,false); ?></td>
                                        <td class="center"><?=$val['cateCd']; ?></td>
                                        <td class="center">
                                            <input type="button" onclick="field_remove('cateGoodsInfo<?=$val['cateCd']; ?>');" value="삭제" class="btn btn-sm btn-white btn-icon-minus"/>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                    <p>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="goodsSortTop" value="y" /> <span class="bold">상단 고정진열 적용</span> (체크 시 선택된 모든 카테고리의 쇼핑몰 상품 페이지 최상단에 진열됩니다.)
                        </label>
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual" id="goodsDisplay">
        노출 및 판매상태 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="goodsDisplay"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-goodsDisplay" value="<?=$toggle['goodsDisplay_'.$SessScmNo]?>">
    <div id="depth-toggle-line-goodsDisplay" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-goodsDisplay">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col class="width-2xl"/>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>PC쇼핑몰 노출상태</th>
                <td>
                    <label class="radio-inline" title="상품을 출력시에는 &quot;출력&quot;을 선택하세요!">
                        <input type="radio" name="goodsDisplayFl" value="y" <?=gd_isset($checked['goodsDisplayFl']['y']); ?> />노출함
                    </label>
                    <label class="radio-inline" title="상품을 출력을 원하지 않을 시에는 &quot;미출력&quot;을 선택하세요!">
                        <input type="radio" name="goodsDisplayFl" value="n" <?=gd_isset($checked['goodsDisplayFl']['n']); ?> />노출안함
                    </label>
                </td>
                <th>PC쇼핑몰 판매상태</th>
                <td>
                    <label class="radio-inline" title="상품을 판매를 하는 경우에는 &quot;판매&quot;를 선택하세요!">
                        <input type="radio" name="goodsSellFl" value="y" <?=gd_isset($checked['goodsSellFl']['y']); ?> />판매함
                    </label>
                    <label class="radio-inline" title="상품을 판매를 원하지 않을 시에는 &quot;판매중지&quot;를 선택하세요!">
                        <input type="radio" name="goodsSellFl" value="n" <?=gd_isset($checked['goodsSellFl']['n']); ?> />판매안함
                    </label>
                </td>
            </tr>
            <?php if (gd_isset($conf['mobile']['mobileShopFl']) == 'y') { ?>
                <tr>
                    <th>모바일쇼핑몰 노출상태</th>
                    <td>
                        <label class="radio-inline" title="상품을 출력시에는 &quot;출력&quot;을 선택하세요!">
                            <input type="radio" name="goodsDisplayMobileFl" value="y" <?=gd_isset($checked['goodsDisplayMobileFl']['y']); ?> />노출함
                        </label>
                        <label class="radio-inline" title="상품을 출력을 원하지 않을 시에는 &quot;미출력&quot;을 선택하세요!">
                            <input type="radio" name="goodsDisplayMobileFl" value="n" <?=gd_isset($checked['goodsDisplayMobileFl']['n']); ?> />노출안함
                        </label>

                    </td>
                    <th>모바일쇼핑몰 판매상태</th>
                    <td>
                        <label class="radio-inline" title="상품을 판매를 하는 경우에는 &quot;판매&quot;를 선택하세요!">
                            <input type="radio" name="goodsSellMobileFl" value="y" <?=gd_isset($checked['goodsSellMobileFl']['y']); ?> />판매함
                        </label>
                        <label class="radio-inline" title="상품을 판매를 원하지 않을 시에는 &quot;판매중지&quot;를 선택하세요!">
                            <input type="radio" name="goodsSellMobileFl" value="n" <?=gd_isset($checked['goodsSellMobileFl']['n']); ?> />판매안함
                        </label>
                    </td>
                </tr>
            <?php } else { ?>
                <input type="hidden" name="goodsDisplayMobileFl" value="<?=$data['goodsDisplayMobileFl']; ?>"/>
            <?php } ?>
            <?php
            if(gd_is_provider() === false) {
                ?>
                <tr>
                    <th>메인상품 진열 상태</th>
                    <td colspan="4">
                        <div style="height:auto; overflow:hidden; border-bottom: 1px solid #e6e6e6;">
                            <div class="display-box-left">
                                <div class="display-title">PC쇼핑몰</div>
                                <?php
                                foreach($displayTheme as $k => $v) {
                                    if ($v['mobileFl'] == 'n') {
                                        ?>
                                        <div class="display-list-left">
                                            <input type="checkbox" name="displayThemeSno[]" value="<?= $v['sno'] ?>"
                                                <?php
                                                if($data['mode'] == 'modify') {
                                                    if ($v['sno'] == $v['checkSno']){ ?> checked="checked"
                                                        <?php
                                                    }
                                                } else {
                                                    if($applyNo){
                                                        if($v['sno'] == $v['checkSno']) { ?> checked="checked"
                                                            <?php
                                                        }
                                                    }else{
                                                        if($v['sortAutoFl'] == 'y') { ?> checked="checked"
                                                            <?php
                                                        }
                                                    }
                                                } ?>/><?= $v['themeNm'] ?>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <div class="display-box-right">
                                <div class="display-title">모바일쇼핑몰</div>
                                <?php
                                foreach($displayTheme as $k => $v) {
                                    if ($v['mobileFl'] == 'y') {
                                        ?>
                                        <div class="display-list-right">
                                            <input type="checkbox" name="displayThemeSno[]" value="<?= $v['sno'] ?>"
                                                <?php
                                                if($data['mode'] == 'modify') {
                                                    if ($v['sno'] == $v['checkSno']){ ?> checked="checked"
                                                        <?php
                                                    }
                                                } else {
                                                    if($applyNo){
                                                        if($v['sno'] == $v['checkSno']) { ?> checked="checked"
                                                            <?php
                                                        }
                                                    }else{
                                                        if($v['sortAutoFl'] == 'y'){ ?> checked="checked"
                                                            <?php
                                                        }
                                                    }
                                                } ?>/><?= $v['themeNm'] ?>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div>
                            <p class="notice-info">
                                자동진열로 설정된 메인분류는 체크(진열)상태가 기본이며, 체크해제시 해당 메인분류의 진열 예외 상품으로 저장됩니다.</br>
                                메인분류는<a href='/goods/display_main_list.php' target="_blank" class="btn-link"> 상품 > 상품 진열 관리 > 메인페이지 상품진열</a>에서 설정할 수 있습니다.
                            </p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>인기상품 포함 상태</th>
                    <td colspan="4">
                        <?php
                        foreach($populateInfo as $key => $val) {
                            ?>
                            <div class="mgt5 mgb5">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="populateListSno[]" value="<?=$val['sno']?>"
                                        <?php
                                        if($data['mode'] == 'modify') {
                                            if ($val['sno'] == $val['checkSno']){ ?> checked="checked"
                                                <?php
                                            }
                                        } else {
                                            if($applyNo){
                                                if($val['sno'] == $val['checkSno']) { ?> checked="checked"
                                                    <?php
                                                }
                                            }else{
                                                if($val['range'] == 'all') { ?> checked="checked"
                                                    <?php
                                                }
                                            }
                                        } ?>/><?=$val['populateName']?>
                                </label>
                            </div>
                            <?php
                        }
                        ?>
                        <div>
                            <p class="notice-info">
                                인기상품 수집 범위가 전체 상품으로 설정된 경우 체크(포함) 상태가 기본이며, 체크 해제 시 해당 인기상품의 수집 예외 상품으로 저장됩니다.</br>
                                인기상품은<a href='/goods/populate_list.php' target="_blank" class="btn-link"> 상품 > 상품 노출형태 관리 > 인기상품 노출 관리</a>에서 설정할 수 있습니다.
                            </p>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>

        </table>
    </div>

    <div class="table-title gd-help-manual" id="defaultInfo">
        기본 정보
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="defaultInfo"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-defaultInfo" value="<?=$toggle['defaultInfo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-defaultInfo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-defaultInfo">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col class="width-2xl"/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <?php if (gd_use_provider() === true) { ?>
                    <?php if (gd_is_provider() === false) { ?>
                        <th>공급사 구분</th>
                        <td>
                            <label class="radio-inline"><input type="radio" name="scmFl"
                                                               value="n" <?=gd_isset($checked['scmFl']['n']); ?> onclick="setScmInfo('y')"/>본사
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?>
                                       onclick="layer_register('scm','radio',true)"/>공급사
                            </label>
                            <label>
                                <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','radio',true)">공급사 선택</button>
                            </label>
                            <div id="scmLayer" class="selected-btn-group <?= $data['scmNoNm'] && $data['scmNo'] != DEFAULT_CODE_SCMNO ? 'active' : '' ?>">
                                <?php if ($data['scmNo']) { ?>
                                    <h5>선택된 공급사 : </h5>
                                    <span id="info_scm_<?= $data['scmNo'] ?>" class="btn-group btn-group-xs">
                            <input type="hidden" name="scmNo" value="<?= $data['scmNo'] ?>"/>
                                <input type="hidden" name="scmNoNm" value="<?= $data['scmNoNm'] ?>">
                                        <?php if ($data['scmNo'] != DEFAULT_CODE_SCMNO) { ?>
                                            <span class="btn"><?= $data['scmNoNm'] ?></span>
                                            <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $data['scmNo'] ?>">삭제</button>
                                        <?php } ?>
                            </span>
                                <?php } ?>
                            </div>
                        </td>
                    <?php } else { ?>
                        <div class="sr-only">
                            <input type="text" name="scmNo" value="<?= $data['scmNo'] ?>"/>
                            <input type="radio" name="scmFl" value="y" checked="checked"/>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="sr-only">
                        <input type="hidden" name="scmNo" value="<?= DEFAULT_CODE_SCMNO ?>"/>
                        <input type="radio" name="scmFl" value="n" checked="checked"/>
                    </div>
                <?php } ?>
                <th>수수료</th>
                <td <?php if (gd_use_provider() !== true || gd_is_provider() !== false) { ?>colspan="3" <?php } ?>>
                    <div class="form-inline">
                        <label title="수수료를 입력해 주세요!">
                            <input type="text" name="commission" value="<?=$data['commission']; ?>" class="form-control width50p" onchange="setCommissionPrice()"/>&nbsp;%
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="input_title r_space " nowrap="nowrap">상품코드</th>
                <td>
                    <?php
                    if ($data['mode'] == "register") {
                        echo '상품 등록시 자동 생성됩니다.';
                    } else {
                        echo $data['goodsNo'] . ' <span class="button small"><a href="' . URI_HOME . 'goods/goods_view.php?goodsNo=' . $data['goodsNo'] . '" target="_blank">화면보기</a></span>';
                    }
                    ?>
                </td>
                <th nowrap="nowrap">자체 상품코드</th>
                <td>
                    <label title="상품코드를 입력해 주세요!">
                        <input type="text" name="goodsCd" value="<?=$data['goodsCd']; ?>" class="form-control width-xl js-maxlength" maxlength="30"/>
                    </label>
                </td>
            </tr>
            <tr>
                <th class="input_title r_space require">상품명</th>
                <td colspan="3">
                    <div class="radio">
                        <label class="radio-inline" title="상품명을 일반적인 Text 형태로 작성시에는 &quot;기본 상품명&quot;을 선택하세요!">
                            <input type="radio" name="goodsNmFl" value="d" onclick="display_toggle('goodsNmExt','hide');" <?=gd_isset($checked['goodsNmFl']['d']); ?> />기본 상품명
                        </label>
                        <label class="radio-inline" title="상품명을 상품 출력하는 위치에 따라서 다르게 작성시에는 &quot;확장 상품명&quot;을 선택하세요!">
                            <input type="radio" name="goodsNmFl" value="e" onclick="display_toggle('goodsNmExt','show');" <?=gd_isset($checked['goodsNmFl']['e']); ?> />확장 상품명
                        </label>
                    </div>
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-md"/>
                            <col/>
                        </colgroup>
                        <tbody>
                        <tr>
                            <th colspan="2">기준몰 상품명</th>
                        </tr>
                        <tr>
                            <th>기본</th>
                            <td>
                                <label title="일반 상품명은 HTML Tag를 지원 하지 않습니다." class="label-width">
                                    <input type="text" name="goodsNm" value="<?=$data['goodsNm']; ?>" class="form-control input-width js-maxlength" maxlength="250"/>
                                </label>
                            </td>
                        </tr>
                        </tbody>
                        <tbody id="goodsNmExt" class="display-none">
                        <tr>
                            <th>메인</th>
                            <td>
                                <label title="메인 상품명은 HTML Tag를 지원을 하며, 메인에 상품 출력시에만 노출이 됩니다. 일반 상품명과 다른 이름을 넣을수도 있습니다." class="label-width">
                                    <input type="text" name="goodsNmMain" value="<?=$data['goodsNmMain']; ?>" class="form-control input-width js-maxlength" maxlength="250"/>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th>리스트</th>
                            <td>
                                <label title="리스트 상품명은 HTML Tag를 지원을 하며, 상품 리스트에서만 노출이 됩니다. 일반 상품명과 다른 이름을 넣을수도 있습니다." class="label-width">
                                    <input type="text" name="goodsNmList" value="<?=$data['goodsNmList']; ?>" class="form-control input-width js-maxlength" maxlength="250"/>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th>상세</th>
                            <td>
                                <label title="상세 상품명은 HTML Tag를 지원을 하며, 상품 상세설명의 상품명 출력시에만 노출이 됩니다. 일반 상품명과 다른 이름을 넣을수도 있습니다." class="label-width">
                                    <input type="text" name="goodsNmDetail" value="<?=$data['goodsNmDetail']; ?>" class="form-control input-width js-maxlength" maxlength="250"/>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th>제휴</th>
                            <td>
                                <label title="제휴 상품명은 HTML Tag를 지원을 하며, 상품 상세설명의 상품명 출력시에만 노출이 됩니다. 일반 상품명과 다른 이름을 넣을수도 있습니다." class="label-width">
                                    <input type="text" name="goodsNmPartner" value="<?=$data['goodsNmPartner']; ?>" class="form-control input-width js-maxlength" maxlength="250"/>
                                </label>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="notice-info">
                        "확장 상품명>제휴" 상품명을 입력하면 외부연동(네이버 쇼핑, 다음 쇼핑하우 등)시 별도의 상품명을 사용할 수 있습니다.
                    </p>
                    <?php if ($gGlobal['isUse'] === true) { ?>
                        <table class="table table-cols js-global-name">
                            <colgroup>
                                <col class="width-md"/>
                                <col/>
                            </colgroup>
                            <tr>
                                <th colspan="2">해외 상점 상품명(기본)</th>
                            </tr>
                            <?php
                            foreach ($gGlobal['useMallList'] as $val) {
                                if ($val['standardFl'] == 'n') {
                                    ?>
                                    <tr>
                                        <th>
                                            <span class="js-popover flag flag-16 flag-<?= $val['domainFl']?>" data-content="<?=$val['mallName']?>"></span>
                                        </th>
                                        <td>
                                            <input type="text" name="globalData[<?= $val['sno'] ?>][goodsNm]" value="<?= $data['globalData'][ $val['sno'] ]['goodsNm']; ?>" class="form-control  input-width js-maxlength" maxlength="250" <?php if(empty($data['globalData'][ $val['sno'] ]['goodsNm'])) { ?>disabled="disabled" <?php } ?> data-global=''/>
                                            <div>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" name="goodsNmGlobalFl[<?= $val['sno'] ?>]" value="y" <?= gd_isset($checked['goodsNmFl'][$val['sno']]); ?>> 기준몰 기본 상품명 공통사용
                                                </label>
                                                <a class="btn btn-sm btn-black js-translate-google" data-language="<?= $val['domainFl'] ?>" data-target-name="goodsNm">참고 번역</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php }
                            }?>
                        </table>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>검색 키워드</th>
                <td colspan="3">
                    <div class="mgt5 mgb5">
                        <label class="checkbox-inline" title="체크시 기본 상품명이 검색 키워드에 추가됩니다.">
                            <input type="checkbox" name="addGoodsKeyword" value="y">체크시 기본 상품명이 검색 키워드에 추가됩니다.
                        </label>
                    </div>
                    <label title="상품상세 페이지의 메타태그와 상품 검색시 키워드로 사용하실 수 있습니다." class="label-width">
                        <input type="text" name="goodsSearchWord" value="<?=$data['goodsSearchWord']; ?>" class="form-control input-width js-maxlength" maxlength="250"/>
                    </label>
                    <p class="notice-info">상품상세 페이지의 메타태그와 상품 검색시 키워드로 사용하실 수 있습니다.</p>
                </td>
            </tr>
            <tr>
                <th>상품 노출시간</th>
                <td  colspan="3">
                    <div class="form-inline">
                        <div class="input-group js-datetimepicker">
                            <input type="text" name="goodsOpenDt" value="<?= $data['goodsOpenDt'] ?>" class="form-control" placeholder="수기입력 가능">
                            <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>
                        부터
                    </div>
                </td>
            </tr>
            <tr>
                <th>상품상태</th>
                <td  colspan="3">
                    <?php foreach ($goodsStateList as $k => $v) {
                        echo "<label class='radio-inline'><input type='radio' name='goodsState' value='" . $k . "' " . gd_isset($checked['goodsState'][$k]) . ">" . $v . "</label>";
                    } ?>
                </td>
            </tr>
            <tr>
                <th>상품 대표색상</th>
                <td colspan="3">
                    <?php foreach ($goodsColorList as $k => $v) {
                        ?>
                        <button type="button" class="btn js-popover" data-html="true" data-color="<?=$v?>" data-content="<?=$k?>" data-placement="bottom"  style="background-color:#<?=$v?>;border:1px solid #efefef;" onclick="selectColor(this)">&nbsp;&nbsp;</button>
                    <?php } ?>
                    <div id="selectColorLayer" class="selected-btn-group active">
                        <h5>선택된 색상</h5>
                        <span>
                        <?php if (is_array($data['goodsColor'])) {
                            foreach (array_unique($data['goodsColor']) as $k => $v) {
                                if (!in_array($v,$goodsColorList) ) {
                                    continue;
                                }
                                ?>
                                <div id='goodsColor_<?= $v ?>' class="btn-group btn-group-xs">
                                    <input type='hidden' name='goodsColor[]' value='<?= $v ?>'>
                                    <span class='btn js-popover' style='background:#<?= $v ?>;border:1px solid #efefef' data-html="true" data-content="<?=array_flip($goodsColorList)[$v]?>" data-placement="bottom">&nbsp;&nbsp;&nbsp;</span>
                                    <button type='button' class='btn btn-icon-delete' data-toggle='delete' data-target='#goodsColor_<?= $v ?>'>삭제</button>
                                </div>
                            <?php }
                        } ?>
                        </span>
                    </div>
                    <p class="notice-info">
                        대표색상은 상품 검색시에 사용되며 <a href='/policy/base_code_list.php?categoryGroupCd=05' target="_blank" class="btn-link">기본설정>기본 정책>코드 관리</a>에서 추가할 수 있습니다.
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        이미지 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="setImage"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-setImage" value="<?=$toggle['setImage_'.$SessScmNo]?>">
    <div id="depth-toggle-line-setImage" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-setImage">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>저장소 선택</th>
                <td>
                    <div class="form-inline">
                        <?=gd_select_box('imageStorage', 'imageStorage', $conf['storage'], null, $data['imageStorage'], '=저장소 선택=', 'onchange="image_storage_selector(this.value);"'); ?>
                        <?php if (gd_is_provider() === false) { ?> <span class="notice-info"> 저장소 관리는 <a href='/policy/base_file_storage.php' target="_blank" class="btn-link">기본설정>기본 정책>파일 저장소 관리</a>에서 가능합니다.</span><?php } ?>
                    </div>
                    <div class="pull-left" style="padding:5px 0px 0px 5px">
                        <span id="imageStorageMode_none" class="display-none"> 저장소 선택을 하지 않으면 &quot;기본경로&quot; 설정을 사용을 합니다.</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>저장 경로</th>
                <td class="input_area bold">
                    <span id="imageStorageModeNm">
                        </span>
                    <?php
                    /*                    if ($data['mode'] == "register") {
                                        echo '<span id="imageStorageMode_local" class="display-none">'.UserFilePath::data('goods')->www().'/코드1/코드2/코드3/상품코드/</span>';
                                        echo '<span id="imageStorageMode_etc" class="display-none"><span id="imageStorageModeNm">'.$data['imageStorage'].'</span>'.DIR_GOODS_IMAGE_FTP.'코드1/코드2/코드3/상품코드/</span>';
                                    } else {
                                        echo '<span id="imageStorageMode_local" class="display-none">'.UserFilePath::data('goods', $data['imagePath'])->www().'</span>';
                                        echo '<span id="imageStorageMode_etc" class="display-none"><span id="imageStorageModeNm">'.$data['imageStorage'].'</span>'.DIR_GOODS_IMAGE_FTP.$data['imagePath'].'</span>';
                                    }
                                    */ ?>
                    <input type="hidden" name="imagePath" value="<?=$data['imagePath']; ?>"/>
                </td>
            </tr>
        </table>
    </div>

    <?php if (gd_is_provider() === false) { ?>
        <div class="table-title gd-help-manual">
            결제 정보
            <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="settleInfo"><span>닫힘</span></button></span>
        </div>

        <input type="hidden" id="depth-toggle-hidden-settleInfo" value="<?=$toggle['settleInfo_'.$SessScmNo]?>">
        <div id="depth-toggle-line-settleInfo" class="depth-toggle-line display-none"></div>
        <div id="depth-toggle-layer-settleInfo">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-lg">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th>결제수단 설정</th>
                    <td>
                        <table class="table table-cols ">
                            <colgroup>
                                <col class="width-sm">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr>
                                <th>
                                    <label class="radio-inline"><input type="radio" name="payLimitFl" value="n" <?=gd_isset($checked['payLimitFl']['n']); ?> onclick="display_toggle('payLimitConfig','hide');display_toggle('payBasic','show');"/>통합설정</label>
                                </th>
                                <td>
                                    <div>
                                        <a href="../policy/settle_settlekind.php" target="_blank" class="btn-link">기본설정>결제정책>결제 수단 설정</a>에서 설정한 기준에 따름
                                    </div>

                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label class="radio-inline"><input type="radio" name="payLimitFl" value="y" <?=gd_isset($checked['payLimitFl']['y']); ?> onclick="display_toggle('payLimitConfig','show');display_toggle('payBasic','hide');"/>개별설정</label>
                                </th>
                                <td>
                                    <div id="payBasic">이 상품의 구매 가능한 결제수단 기준을 따로 설정함</div>
                                    <div id="payLimitConfig">
                                        <div class="form-inline">
                                            <?php foreach ($goodsPayLimit as $k => $v) { ?>
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" name="payLimit[<?= $k ?>]" value="<?= $k ?>" <?=$checked['payLimit'][$k]; ?> /><?= $v ?>
                                                </label>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div>
                            <div class="notice-info">
                                상품의 개별결제수단을 설정하는 경우 선택된 결제수단으로만 상품 구매가 가능합니다.<br/> 신용카드 가맹점인 경우, 결제수단을 현금으로만 제한하는 것은 상품권 등 법적으로 신용카드 거래가 제한되는 특정한 상품의 판매를 위한 용도로만 사용하세요.<br/> 일반 상품에 신용카드 거래를 제한하게 되면, 여신전문금융업법 위반이 되어 처벌 받을수 있습니다.
                            </div>
                        </div>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <div class="table-title gd-help-manual" id="addInfo">
        추가 정보
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="addInfo"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-addInfo" value="<?=$toggle['addInfo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-addInfo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-addInfo">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col class="width-2xl"/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) { ?>
            <tr>
                <th>매입처</th>
                <td class="input_area" colspan="3">
                    <label><input type="text" name="purchaseNoNm" value="<?=$data['purchaseNoNm']; ?>"
                                  class="form-control"  onclick="layer_register('purchase', 'radio')" readonly/></label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('purchase', 'radio')">매입처 선택</button>
                    </label>
                    <a href="./purchase_register.php" target="_blank" class="btn btn-sm btn-white btn-icon-plus">매입처 추가</a>
                    <label id="purchaseNoDel" style="display:<?= $data['purchaseNoNm'] ? '':'none'; ?>"><input type="checkbox" name="purchaseNoDel" value="y"> <span class="text-red">체크시 삭제</span></label>
                    <div id="purchaseLayer" class="width100p">
                        <?php if ($data['purchaseNo']) { ?>
                        <span id="info_parchase_<?= $data['purchaseNo'] ?>" class="pull-left">
                        <input type="hidden" name="purchaseNo" value="<?= $data['purchaseNo'] ?>"/>
                        </span>
                        <?php } ?>
                    </div>
                </td>
            </tr>
        <?php } ?>
            <tr>
                <th>매입처 상품명</th>
                <td colspan="3">
                    <div class="mgt5 mgb5">
                        <label class="checkbox-inline" title="체크시 기본 상품명이 매입처 상품명에 추가됩니다.">
                            <input type="checkbox" name="purchaseNmFl" value="y"/>체크시 기본 상품명이 매입처 상품명에 추가됩니다.
                        </label>
                    </div>
                    <label class="label-width">
                        <input type="text" name="purchaseGoodsNm" value="<?=$data['purchaseGoodsNm']; ?>" class="form-control input-width js-maxlength" maxlength="250"/>
                    </label>
                </td>
            </tr>
            <tr>
                <th>브랜드</th>
                <td class="input_area">
                    <label><input type="text" name="brandCdNm" value="<?=$data['brandCdNm']; ?>"
                                  class="form-control"  onclick="layer_register('brand', 'radio')" readonly/></label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('brand', 'radio')">브랜드 선택</button>
                    </label>
                    <?php if (gd_is_provider() === false) { ?>
                        <a href="./category_tree.php?cateType=brand" target="_blank" class="btn btn-sm btn-white btn-icon-plus">브랜드 추가</a>
                    <?php } ?>
                    <label id="brandCdDel" style="display:<?= $data['brandCdNm'] ? '':'none'; ?>"><input type="checkbox" name="brandCdDel" value="y"> <span class="text-red">체크시 삭제</span></label>

                    <div id="brandLayer" class="width100p">
                        <?php if ($data['brandCd']) { ?>
                            <span id="info_brand_<?= $data['brandCd'] ?>" class="pull-left">
                        <input type="hidden" name="brandCd" value="<?= $data['brandCd'] ?>"/>
                        </span>
                        <?php } ?>
                    </div>
                    <?php if ($gGlobal['isUse'] === true) { ?>
                        <p class="notice-danger">
                            대표 카테고리와 노출상점이 다른 경우 <br/>브랜드 페이지에 상품이 노출되지않습니다.
                        </p>
                    <?php } ?>
                </td>
                <th>제조사</th>
                <td>
                    <input type="text" name="makerNm" value="<?=$data['makerNm']; ?>" class="form-control width-md js-maxlength" maxlength="40"/>
                </td>

            </tr>
            <tr>
                <th>원산지</th>
                <td>
                    <input type="text" name="originNm" value="<?=$data['originNm']; ?>" class="form-control width-md js-maxlength" maxlength="40"/>
                </td>
                <th>모델번호</th>
                <td>
                    <label title="상품의 모델번호를 작성해 주세요!">
                        <input type="text" name="goodsModelNo" value="<?=$data['goodsModelNo']; ?>" class="form-control width-md js-maxlength" maxlength="30"/>
                    </label>
                </td>

            </tr>
            <tr>
                <th>HS코드</th>
                <td colspan="3">
                    <div class="js-hscode-info">
                    </div>
                    <div class="notice-info">추가 버튼을 이용하여 국가별 HS코드를 추가 입력할 수 있습니다.</div>
                </td>
            </tr>
            <tr>
                <th>제조일</th>
                <td>
                    <label title="상품의 제조일을 선택/작성(yyyy-mm-dd)해 주세요!">
                        <div class="form-inline">
                            <div class="input-group js-datepicker">
                                <input type="text" name="makeYmd" class="form-control" value="<?=$data['makeYmd']; ?>" placeholder="수기입력 가능">
                                <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                            </div>
                        </div>
                    </label>
                </td>
                <th>출시일</th>
                <td>
                    <label title="상품의 출시일을 선택/작성(yyyy-mm-dd)해 주세요!">
                        <div class="form-inline">
                            <div class="input-group js-datepicker">
                                <input type="text" name="launchYmd" class="form-control" value="<?=$data['launchYmd']; ?>" placeholder="수기입력 가능">
                                <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                            </div>
                        </div>
                    </label>
                </td>
            </tr>
            <tr>
                <th>유효일자</th>
                <td  <?php if (gd_is_plus_shop(PLUSSHOP_CODE_QRCODE) === false) { ?>colspan="3"<?php } ?>>
                    <div class="form-inline">
                        시작일 / 종료일
                        <label title="상품의 유효일자 시작일을 선택/작성(yyyy-mm-dd)해 주세요!">
                            <div class="form-inline">
                                <div class="input-group js-datepicker">
                                    <input type="text" name="effectiveStartYmd" class="form-control width-xs" value="<?=$data['effectiveStartYmd']; ?>" placeholder="수기입력 가능">
                                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                                </div>
                            </div>
                        </label>
                        ~
                        <label title="상품의 유효일자 종료일을 선택/작성(yyyy-mm-dd)해 주세요!">
                            <div class="form-inline">
                                <div class="input-group js-datepicker">
                                    <input type="text" name="effectiveEndYmd" class="form-control width-xs" value="<?=$data['effectiveEndYmd']; ?>" placeholder="수기입력 가능">
                                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                                </div>
                            </div>
                        </label>
                    </div>
                </td>
                <?php if (gd_is_plus_shop(PLUSSHOP_CODE_QRCODE) === true) { ?>
                    <th>QR코드 노출상태</th>
                    <td>
                        <?php
                        if ($conf['qrcode']['useGoods'] == 'y') {
                            ?>
                            <label title="상품 QR코드 설정을 사용하시려면 선택해 주세요!" class="radio-inline">
                                <input type="radio" name="qrCodeFl" value="y" <?=gd_isset($checked['qrCodeFl']['y']); ?> />노출함
                            </label>
                            <label title="상품 QR코드 설정을 사용하지 않으시려면 선택해 주세요!" class="radio-inline">
                                <input type="radio" name="qrCodeFl" value="n" <?=gd_isset($checked['qrCodeFl']['n']); ?> />노출안함
                            </label>
                            <?php
                        } else {
                            echo '<div class="notice-info if-btn">QR코드 사용 여부를 확인해 주세요.</div>';
                        }
                        ?>
                    </td>
                <?php } ?>
            </tr>
            <tr>
                <th>구매가능 회원등급</th>
                <td colspan="3" >
                    <div style="position:absolute;left:740px;">
                        <div  class="form-inline">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="goodsPermissionPriceStringFl" value="y" <?=gd_isset($checked['goodsPermissionPriceStringFl']['y']); ?>  />구매불가 고객 가격 대체문구 사용
                            </label>
                            <span class="js-goods-permission-price-string">
                            <input type="text" name="goodsPermissionPriceString"  value="<?=$data['goodsPermissionPriceString']; ?>" maxlength="30" class="form-control js-maxlength"/>
                             </span>
                        </div>
                    </div>
                    <div>
                        <?php foreach ($goodsPermissionList as $k => $v) { ?>
                            <label class="radio-inline">
                                <input type="radio" name="goodsPermission" value="<?=$k; ?>" <?=gd_isset($checked['goodsPermission'][$k]); ?> onclick="set_goods_permission(this.value,'memberGroup','goodsPermissionPriceStringFl')"/>
                                <?=$v; ?>
                            </label>
                        <?php } ?>
                        <label>
                            <button type="button" class="btn btn-sm btn-gray" id="memberGroupBtn" onclick="layer_register('memberGroup')" <?php if ($data['goodsPermission'] !== 'group') echo 'disabled="disabled"'; ?>>회원등급 선택</button>
                        </label>
                        <div id="memberGroupLayer" class="selected-btn-group <?= is_array($data['goodsPermissionGroup']) ? 'active' : '' ?>">
                            <?php if (is_array($data['goodsPermissionGroup'])) { ?>
                                <h5>선택된 회원등급</h5>
                                <?php foreach ($data['goodsPermissionGroup'] as $k => $v) { ?>
                                    <span id="infoMemberGroup_<?= $k ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="memberGroupNo[]" value="<?= $k ?>"/>
                                    <span class="btn"><?= $v ?></span>
                                    <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#infoMemberGroup_<?= $k ?>">삭제</button>
                                </span>
                                <?php }
                            } ?>

                        </div>
                    </div>
                    <p class="notice-info mgb10 if-btn">구매불가 고객 가격 대체문구 사용"에 체크 및 내용 입력 시, 구매가 불가능한 고객들을 대상으로 가격 대신 해당 문구가 노출됩니다.</p>
                </td>
            </tr>
            <tr>
                <th>성인인증</th>
                <td colspan="3">
                    <div class="form-inline">

                        <label class="radio-inline">
                            <input name="onlyAdultFl" value="n" type="radio" <?=gd_isset($checked['onlyAdultFl']['n']); ?>>사용안함
                        </label>
                        <label class="radio-inline">
                            <input name="onlyAdultFl" value="y" type="radio" <?=gd_isset($checked['onlyAdultFl']['y']); ?> >사용함
                        </label>

                        <label class="checkbox-inline" style="padding-left:10px;">
                            <input type="checkbox" name="onlyAdultDisplayFl" value="y" <?=gd_isset($checked['onlyAdultDisplayFl']['y']); ?> /> 미인증 고객 상품 노출함
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="onlyAdultImageFl" value="y" <?=gd_isset($checked['onlyAdultImageFl']['y']); ?> /> 미인증 고객 상품 이미지 노출함
                        </label>
                    </div>

                    <p class="notice-info mgb10 if-btn">
                        해당 상품의 상세페이지 접근시 성인인증확인 인트로 페이지가 출력되며, 진열 이미지는 19금 이미지로 대체되어 보여집니다. <br/>
                        <?php if (gd_is_provider() === false && !gd_use_ipin() && !gd_use_auth_cellphone() ) { ?> 성인인증 기능은 별도의 인증 서비스 신청완료 후 이용 가능합니다.<br/>

                            <a href="../policy/member_auth_cellphone.php" target="_blank" class="btn-link">휴대폰인증 설정 바로가기</a>  <a href="../policy/member_auth_ipin.php" target="_blank" class="btn-link">아이핀인증 설정 바로가기</a>
                            <br/><?php } ?>

                    </p>
                    <p class="notice-danger">
                        구 실명인증 서비스는 성인인증 수단으로 연결되지 않습니다.<br/>
                    </p>
                </td>
            </tr>
            <tr>
                <th>접근 권한</th>
                <td colspan="3">
                    <div style="position:absolute;left:740px;">
                        <div class="form-inline" >
                            <label class="checkbox-inline">
                                <input type="checkbox" name="goodsAccessDisplayFl" value="y" <?=gd_isset($checked['goodsAccessDisplayFl']['y']); ?> />접근불가 고객 상품 노출함
                            </label>
                        </div>
                    </div>
                    <div>
                        <?php foreach ($goodsPermissionList as $k => $v) { ?>
                            <label class="radio-inline">
                                <input type="radio" name="goodsAccess" value="<?=$k; ?>" <?=gd_isset($checked['goodsAccess'][$k]); ?> onclick="set_goods_permission(this.value,'accessMemberGroup','goodsAccessDisplayFl')"/>
                                <?=$v; ?>
                            </label>
                        <?php } ?>
                        <label>
                            <button type="button" class="btn btn-sm btn-gray" id="accessMemberGroupBtn" onclick="layer_register('accessMemberGroup')" <?php if ($data['goodsAccess'] !== 'group') echo 'disabled="disabled"'; ?>>회원등급 선택</button>
                        </label>

                        <div id="accessMemberGroupLayer" class="selected-btn-group <?= is_array($data['goodsAccessGroup']) ? 'active' : '' ?>">
                            <?php if (is_array($data['goodsAccessGroup'])) { ?>
                                <h5>선택된 회원등급</h5>
                                <?php foreach ($data['goodsAccessGroup'] as $k => $v) { ?>
                                    <span id="infoAccessMemberGroup_<?= $k ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="accessMemberGroupNo[]" value="<?= $k ?>"/>
                                    <span class="btn"><?= $v ?></span>
                                    <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#infoAccessMemberGroup_<?= $k ?>">삭제</button>
                                </span>
                                <?php }
                            } ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>추가항목</th>
                <td colspan="3">
                    <p>
                        <button type="button" class="btn btn-sm btn-white btn-icon-plus" onclick="add_info();">항목추가</button>
                        <span class="notice-info mgl10">상품특성에 맞게 항목을 추가할 수 있습니다 (예. 감독, 저자, 출판사, 유통사, 상품영문명 등)</span>
                    </p>

                    <table class="table table-rows" id="addInfoItem">
                        <thead>
                        <tr>
                            <th class="width-2xs">순서</th>
                            <th class="width-lg">항목</th>
                            <th>내용</th>
                            <th class="width-2xs">삭제</th>
                        </tr>
                        </thead>
                        <?php
                        if (!empty($data['addInfo'])) {
                            foreach ($data['addInfo'] as $key => $val) {
                                $nextNo = $key + 1;
                                ?>
                                <tr id="addInfoItem<?=$nextNo; ?>">
                                    <td class="center"><?php if ($applyGoodsCopy === false) { ?>
                                            <input type="hidden" name="addInfo[sno][]" value="<?=$val['sno']; ?>" /><?php } ?><?=$nextNo; ?>
                                    </td>
                                    <td class="center">
                                        <input type="text" name="addInfo[infoTitle][]" value="<?=$val['infoTitle']; ?>" class="form-control width-lg"/>
                                    </td>
                                    <td class="center">
                                        <input type="text" name="addInfo[infoValue][]" value="<?=$val['infoValue']; ?>" class="form-control"/>
                                    </td>
                                    <td class="center">
                                        <input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="field_remove('addInfoItem<?=$nextNo; ?>');" value="삭제"/></span>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        상품 필수정보
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="mustInfo"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-mustInfo" value="<?=$toggle['mustInfo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-mustInfo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-mustInfo">
        <div class="notice-danger">
            공정거래위원회에서 공고한 전자상거래법 상품정보제공 고시에 관한 내용을 필독해 주세요!
            <a href="http://www.ftc.go.kr/www/FtcRelLawUList.do?key=290&law_div_cd=07" target="_blank" class="btn-link-underline">내용 확인 ></a>
        </div>
        <div class="notice-info">
            전자상거래법에 의거하여 판매 상품의 필수 (상세) 정보 등록이 필요합니다.<br/>
            <a class="btn-link-underline"  onclick="goods_must_info_popup();">품목별 상품정보고시 내용보기</a>를 참고하여 상품필수 정보를 등록하여 주세요.
        </div>
        <div class="notice-danger">
            전기용품 및 생활용품 판매 시 "전기용품 및 생활용품 안전관리법"에 관한 내용을 필독해 주세요!
            <a href="http://www.law.go.kr/lsInfoP.do?lsiSeq=180398#0000" target="_blank" class="btn-link-underline">내용 확인 ></a>
        </div>
        <div  class="notice-info">

            네이버 지식쇼핑등 가격비교사이트에 등록하려는 상품은 아래 항목명을 참조하여 동일하게 입력하셔야 정상적으로 등록됩니다.<br/>
            <table class="table table-cols" style="width:80%">
                <tr>
                    <th class="width-lg"><span>배송 · 설치비용</span> <button type="button" class="btn btn-sm btn-white" onclick="add_must_info_install(this);">복사</button></th>
                    <td  class="width-lg">예시) 서울 경기 무료배송/ 강원, 충청 2만원 추가</td>
                    <td>기본 배송비 이외에 지역, 품목 등에 따라 추가 배송비가 발생하는 경우 기재
                        ※일반적인 도서산간 지역에 대한 추가 배송비는 해당하지 않음</td>
                </tr>
                <tr>
                    <th><span>추가설치비용</span>  <button type="button" class="btn btn-sm btn-white" onclick="add_must_info_install(this);">복사</button></th>
                    <td>예시) 설치비 현장 지불</td>
                    <td>해당 상품 구매시 추가로 설치비가 발생하는 경우 기재</td>
                </tr>
            </table>
        </div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-lg">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>필수정보 선택</th>
                <td>
                    <div class="form-inline">
                        <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('must_info', 'radio')">필수정보 선택</button>
                        <a href="./goods_must_info_register.php" target="_blank" class="btn btn-sm btn-white btn-icon-plus">필수정보 추가</a>
                    </div>
                </td>
            </tr>
            <tr>
                <th>KC인증 표시 여부</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="kcmarkInfo[kcmarkFl]" value="y" <?=gd_isset($checked['kcmarkFl']['y']); ?> />사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="kcmarkInfo[kcmarkFl]" value="n" <?=gd_isset($checked['kcmarkFl']['n']); ?> />사용안함
                    </label>
                    <button type="button" class="btn btn-sm btn-gray btn-kc">예시화면</button>
                    <div class="notice-info">
                        안전관리대상 제품은 안전인증 등의 표시(KC 인증마크 및 인증번호)를 소비자가 확인할 수 있도록 상품 상세페이지 내 표시해야 합니다.<br/>
                        <a class="btn-link-underline"  href="http://safetykorea.kr/policy/targetsSafetyCert" target="_blank" >국가기술표준원(KATS) 제품안전정보센터</a>에서 인증대상 품목여부를 확인하여 등록하여 주세요.
                    </div>
                    <div class="mgt15 select-kcmark form-inline">
                        <hr class="select-kcmark">
                        <label class="select-kcmark <?= $display ?>">
                            <?= gd_select_box('kcmarkDivFl', 'kcmarkInfo[kcmarkDivFl]', $kcmarkDivFl, null, $data['kcmarkInfo']['kcmarkDivFl'], '선택'); ?>
                        </label>
                        <label class="select-kcmark <?= $display ?>">
                            <input type="text" name="kcmarkInfo[kcmarkNo]" class="form-control width-xl" value="<?=$data['kcmarkInfo']['kcmarkNo']?>" placeholder="인증번호 입력 시, - 포함하여 입력하세요." maxlength="20">
                        </label>
                        <div class="input-group js-datepicker select-kcmark-dt <?= $display ?>">
                            <input type="text" class="form-control width-md" name="kcmarkDt[]" value="<?= $data['kcmarkInfo']['kcmarkDt']; ?>" placeholder="인증일자를 입력하세요"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <div class="notice-info select-kcmark <?= $display ?>">
                            인증번호가 없는 공급자적합성확인 대상의 경우, 별도로 입력하지 않아도 무관하나 제품명, 모델명, 제조업자명 또는 수입업자명을 소비자가 확인할 수 있도록 상세페이지 내 표시해야 합니다.</br>
                            <a class="btn-link-underline"  href="http://www.kats.go.kr/content.do?cmsid=13&cid=20174&mode=view
" target="_blank" >전기용품 및 생활용품 안전관리법 가이드라인</a>의 내용을 확인해 주세요.
                        </div>
                        <div class="notice-info select-kcmark <?= $display ?>">
                            방송통신기자재의 인증번호 검색 시 인증일자가 필수로 입력되어야 검색이 가능합니다.</br>
                            <div style="color:#fa2828;">인증일자를 입력하지 않은 경우, 구매자가 인증번호를 검색할 수 없으므로 인증일자를 입력하실 것으로 권고 드립니다.</div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>항목추가</th>
                <td>
                    <button type="button" class="btn btn-sm btn-white btn-icon-goods-must-info-02" onclick="add_must_info(4);">4칸 항목 추가</button>
                    <button type="button" class="btn btn-sm btn-white btn-icon-goods-must-info-01" onclick="add_must_info(2);">2칸 항목 추가</button>
                    <span class="notice-danger"> 항목과 내용 란에 아무 내용도 입력하지 않으면 저장되지 않습니다.</span></td>
            </tr>
            </tbody>
        </table>

        <table class="table table-rows <?php if (empty($data['goodsMustInfo'])) { ?>display-none<?php } ?>" id="addMustInfo">
            <colgroup>
                <col class="width15p"/>
                <col class="width30p"/>
                <col class="width15p"/>
                <col class="width30p"/>
                <col class="width10p"/>
            </colgroup>
            <thead>
            <tr>
                <th>항목</th>
                <th>내용</th>
                <th>항목</th>
                <th>내용</th>
                <th>-</th>
            </tr>
            </thead>
            <?php
            if (!empty($data['goodsMustInfo'])) {
                $nextNo = 0;
                foreach ($data['goodsMustInfo'] as $lKey => $lVal) {
                    $colspanStr = '';
                    if (count($lVal) == 1) {
                        $colspanStr = ' colspan="3"';
                    }
                    ?>
                    <tr id="addMustInfo<?=$nextNo; ?>">
                        <?php
                        foreach ($lVal as $sKey => $sVal) {
                            ?>
                            <td class="center">
                                <input type="text" name="addMustInfo[infoTitle][<?=$nextNo; ?>][]" value="<?=$sVal['infoTitle']; ?>" class="form-control"/>
                            </td>
                            <td class="center"<?=$colspanStr; ?>>
                                <input type="text" name="addMustInfo[infoValue][<?=$nextNo; ?>][]" value="<?=$sVal['infoValue']; ?>" class="form-control"/>
                            </td>
                            <?php
                        }
                        ?>
                        <td class="center">
                            <input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="field_remove('addMustInfo<?=$nextNo; ?>');" value="삭제"/></span>
                        </td>
                    </tr>
                    <?php
                    $nextNo++;
                }
            }
            ?>
        </table>
    </div>

    <div class="table-title gd-help-manual" id="sellInfo">
        판매 정보
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="sellInfo"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-sellInfo" value="<?=$toggle['sellInfo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-sellInfo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-sellInfo">
        <table class="table table-cols sales-information">
            <colgroup>
                <col class="width-lg"/>
                <col class="width-2xl"/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>과세/면세</th>
                <td>
                    <div class="form-inline">
                        <label class="radio-inline" title="과세상품인 경우 &quot;과세&quot;를 선택후 과세율을 입력하세요!">
                            <input type="radio" name="taxFreeFl" value="t" <?=gd_isset($checked['taxFreeFl']['t']); ?> onclick="disabled_switch('taxPercent',false);"/>과세
                        </label>
                        <label title="과세율을 입력하세요">
                            <select class="form-control" name="taxPercent">
                                <option value=''>=세율=</option>
                                <?php foreach ($conf['tax']['goodsTax'] as $k => $v) { ?>
                                    <?php if ($v > 0) { ?>
                                        <option value="<?= $v ?>" <?php if ($v == $data['taxPercent']) {
                                            echo "selected";
                                        } ?> ><?= $v ?></option><?php } ?>
                                <?php } ?>
                            </select> <span class="align">%</span>
                        </label>
                        <label class="radio-inline mgl10" title="면세 상품인경우 &quot;면세&quot;를 선택하세요!">
                            <input type="radio" name="taxFreeFl" value="f" <?=gd_isset($checked['taxFreeFl']['f']); ?> onclick="disabled_switch('taxPercent',true);"/>면세
                        </label>
                    </div>
                </td>
                <th>상품 무게 / 용량</th>
                <td>
                    <div class="form-inline">
                        무게 <input type="text" name="goodsWeight" value="<?=$data['goodsWeight']; ?>" maxlength="8" class="form-control width-xs"/> <?=Globals::get('gWeight.unit'); ?><br><br>
                        용량 <input type="text" name="goodsVolume" value="<?=$data['goodsVolume']; ?>" maxlength="8" class="form-control width-xs"/> <?=gd_isset(Globals::get('gVolume.unit'), '㎖'); ?>
                    </div>
                    <div class="notice-info">
                        입력값이 0 인 경우 쇼핑몰 상세 페이지에 노출 되지 않습니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th>도서공연비<br>
                    소득공제 상품 적용 여부
                </th>
                <td colspan="3">
                    <div class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="cultureBenefitFl" value="n" <?=gd_isset($checked['cultureBenefitFl']['n']); ?>/>미적용
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="cultureBenefitFl" value="y" <?=gd_isset($checked['cultureBenefitFl']['y']); ?>/>적용
                        </label>
                    </div>
                    <div class="notice-info">
                        PG사와 소득공제 대상 사업자로 계약이 되어있는 경우 ‘적용’으로 선택해주세요.<br>
                        도서공연비 소득공제 상품은 일반상품과 네이버 페이로 함께 결제가 불가합니다. (네이버 페이 정책 참조)
                    </div>
                </td>
            </tr>
            <tr>
                <th>판매 재고</th>
                <td>
                    <label class="radio-inline" title="재고관리를 하지 않는 경우 &quot;무한정 판매&quot;를 선택하세요!">
                        <input type="radio" name="stockFl" value="n" <?=gd_isset($checked['stockFl']['n']); ?> />무한정 판매
                    </label>
                    <label class="radio-inline" title="재고관리 상품인 경우 &quot;재고량에 따름&quot;을 선택하세요!">
                        <input type="radio" name="stockFl" value="y" <?=gd_isset($checked['stockFl']['y']); ?> />재고량에 따름
                    </label>
                </td>
                <th>상품 재고</th>
                <td>
                    <label>
                        <input type="text" name="stockCnt" value="<?=$data['totalStock']; ?>" class="form-control width-2xs" <?= $disabled['stockCnt']; ?>>
                    </label>
                    개
                </td>
            </tr>
            <tr>
                <th>묶음주문 단위</th>
                <td>
                    <div class="form-inline">
                        <label>
                            <?= gd_select_box('fixedSales', 'fixedSales', $fixedSales, null, $data['fixedSales']); ?>
                            <input type="text" name="salesUnit" value="<?=$data['salesUnit']; ?>" class="form-control width-2xs"> 개 단위로 주문 및 장바구니에 담김
                        </label>
                    </div>
                </td>
                <th>품절 상태</th>
                <td>
                    <label class="radio-inline" title="판매 상품인 경우 &quot;판매&quot;를 선택하세요! 재고관리를 하는 경우 재고소진시 자동으로 품절이 됩니다.">
                        <input type="radio" name="soldOutFl" value="n" <?=gd_isset($checked['soldOutFl']['n']); ?> />정상
                    </label>
                    <label class="radio-inline" title="임의로 품절상품으로 변경시 &quot;품절(수동)&quot;을 선택하세요! 현 상품은 판매되지 않습니다.">
                        <input type="radio" name="soldOutFl" value="y" <?=gd_isset($checked['soldOutFl']['y']); ?> />품절(수동)
                    </label>
                </td>
            </tr>
            <tr>
                <th>구매수량 설정</th>
                <td colspan="3">
                    <div class="form-inline">
                        <label class="radio-inline" title="최대구매 수량을 정하지 않고 무한대인 경우 &quot;제한없음&quot;를 선택하세요!">
                            <input type="radio" name="maxOrderChk" value="n" <?=gd_isset($checked['maxOrderChk']['n']); ?> onclick="disabled_switch('fixedOrderCnt',true);disabled_switch('maxOrderCnt',true);disabled_switch('minOrderCnt',true);"/>제한없음
                        </label>
                        <label class="radio-inline" title="최대구매 수량을 정할 경우 선택 후 수량을 입력하세요!">
                            <input type="radio" name="maxOrderChk" value="y" <?=gd_isset($checked['maxOrderChk']['y']); ?> onclick="disabled_switch('fixedOrderCnt',false);disabled_switch('maxOrderCnt',false);disabled_switch('minOrderCnt',false);"/>
                            <?= gd_select_box('fixedOrderCnt', 'fixedOrderCnt', $fixedOrderCnt, null, $data['fixedOrderCnt']); ?>
                            최소 구매 수량 :
                        </label>
                        <label title="최소 구매할수 있는 수량을 넣어주세요! 기본은 1개 입니다.">
                            <input type="text" name="minOrderCnt" value="<?=$data['minOrderCnt']; ?>" class="form-control width-3xs"/>개
                        </label>
                        <label title="최대 구매할수 있는 수량을 넣어주세요!">
                            / 최대 구매 수량 :
                            <input type="text" name="maxOrderCnt" value="<?=$data['maxOrderCnt']; ?>" class="form-control width-3xs"/>개
                        </label>
                    </div>
                    <div class="notice-info display-none" id="maxOrderCheckInfo">
                        ID기준 구매수량 설정 시,<br>
                        - 구매가능 회원등급이 '회원전용(비회원제외)' 상품으로 등록됩니다.<br>
                        - 구매수량은 주문 완료 기준으로 적용됩니다.(결제여부 체크하지 않습니다.)<br>
                        - 구매 개수는 상품을 기준으로 체크하며, 상품에 포함된 옵션기준 구매수량 설정은 불가능합니다.<br>
                        - 구매 개수 수정 시 수정 전 기존 구매 개수까지 포함하여 체크됩니다.
                    </div>
                    <div class="notice-danger display-none" id="maxOrderCheckDanger">
                        ID기준 구매수량 설정 시 2019년 11월 20일의 구매이력부터 체크됩니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th>판매기간</th>
                <td colspan="3">
                    <div class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="salesDateFl" value="n" <?=gd_isset($checked['salesDateFl']['n']); ?> onclick="disabled_switch('salesDate[]',true);">제한없음
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="salesDateFl" value="y" <?=gd_isset($checked['salesDateFl']['y']); ?> onclick="disabled_switch('salesDate[]',false);">시작일 / 종료일
                        </label>


                        <div class="input-group js-datetimepicker">
                            <input type="text" name="salesDate[]" class="form-control width-md" value="<?= $data['salesStartYmd'] ?>" placeholder="수기입력 가능">
                            <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        ~
                        <div class="input-group js-datetimepicker">
                            <input type="text" name="salesDate[]" class="form-control width-md" value="<?= $data['salesEndYmd'] ?>" placeholder="수기입력 가능">
                            <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        <div class="btn-group js-dateperiod js-set-sales-date" data-toggle="buttons" data-target-name="salesDate[]" data-target-inverse="salesDate[]" >
                            <label class="btn btn-white btn-sm"><input type="radio" value="0">오늘</label>
                            <label class="btn btn-white btn-sm"><input type="radio" value="6">7일</label>
                            <label class="btn btn-white btn-sm"><input type="radio" value="14">15일</label>
                            <label class="btn btn-white btn-sm"><input type="radio" value="29">1개월</label>
                            <label class="btn btn-white btn-sm"><input type="radio" value="89">3개월</label>
                            <label class="btn btn-white btn-sm"><input type="radio" value="364">1년</label>
                        </div>
                    </div>

                </td>
            </tr>
            <?php if(gd_is_plus_shop(PLUSSHOP_CODE_RESTOCK) === true){ ?>
                <tr>
                    <th>재입고 알림</th>
                    <td colspan="3">
                        <div>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="restockFl" value="y" <?=gd_isset($checked['restockFl']['y']); ?> />상품 재입고 알림 사용
                            </label>
                        </div>
                        <div>
                            <div class="notice-info">
                                상품/옵션 품절시 쇼핑몰 상세페이지에 재입고 알림신청 버튼이 노출됩니다.<br />
                                SMS 발송내용 수정은 <a href="../member/sms_send.php" target="_blank" class="btn-link">회원>SMS관리>개별/전체 SMS 발송</a>에서 가능합니다.
                            </div>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <?php if (gd_is_provider() === false) { ?>
        <div class="table-title gd-help-manual">
            마일리지 설정
            <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="setMilage "><span>닫힘</span></button></span>
        </div>

        <input type="hidden" id="depth-toggle-hidden-setMilage" value="<?=$toggle['setMilage_'.$SessScmNo]?>">
        <div id="depth-toggle-line-setMilage" class="depth-toggle-line display-none"></div>
        <div id="depth-toggle-layer-setMilage" style="margin-bottom:20px">
            <table class="table table-cols" style="margin-bottom:0;">
                <colgroup>
                    <col class="width-lg">
                    <col>
                </colgroup>
                <tr>
                    <th>지급방법 선택</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="mileageFl" value="c" <?=gd_isset($checked['mileageFl']['c']); ?> onclick="display_mileage_set();"/>통합설정</label>
                        <label class="radio-inline"><input type="radio" name="mileageFl" value="g" <?=gd_isset($checked['mileageFl']['g']); ?> onclick="display_mileage_set();"/>개별설정</label>
                    </td>
                </tr>
                <tr>
                    <th>대상 선택</th>
                    <td class="form-inline">
                        <label class="radio-inline"><input type="radio" name="mileageGroup" value="all" <?= $checked['mileageGroup']['all'] ?> onclick="display_mileage_set();">전체회원</label>
                        <label class="radio-inline"><input type="radio" name="mileageGroup" value="group" <?= $checked['mileageGroup']['group'] ?> onclick="display_mileage_set();layer_register('mileage_group','search')">특정회원등급</label>
                        <label>
                            <button type="button" class="btn btn-sm btn-gray js-mileage-group-select">회원등급 선택</button>
                        </label>

                        <div id="mileage_group" class="selected-btn-group <?= empty($data['mileageGroupInfo']) === false ? 'active' : '' ?>">
                            <?php if (empty($data['mileageGroupInfo']) === false) { ?>
                                <h5>선택된 회원등급</h5>
                                <?php foreach ($data['mileageGroupInfo'] as $k => $v) { ?>
                                    <span id="info_mileage_group_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="mileageGroupInfo[]" value="<?= $v ?>"/>
                                <span class="btn"><?= $groupList[$v]; ?></span>
                                <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_mileage_group_<?= $v ?>">삭제</button>
                            </span>
                                <?php }
                            } ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>금액 설정</th>
                    <td>
                        <div class="mileage-set-c display-none">
                            <?php if ($conf['mileage']['giveType'] == 'price') { ?>
                                구매 금액의 <span><?=$conf['mileage']['goods']; ?>%</span>를  마일리지로 지급
                            <?php } else if ($conf['mileage']['giveType'] == 'priceUnit') { ?>
                                구매금액으로 <?=number_format($conf['mileage']['goodsPriceUnit']); ?>원 단위로 <?=number_format($conf['mileage']['goodsMileage']); ?> 마일리지 지급
                            <?php } else if ($conf['mileage']['giveType'] == 'cntUnit') { ?>
                                구매금액과 상관없이 구매상품 1개 단위로 <?=number_format($conf['mileage']['cntMileage']); ?> 마일리지 지급
                            <?php } ?>
                        </div>
                        <div class="mileage-set-g-all display-none form-inline">
                            <span class="goods-title">구매금액의</span>
                            <input type="text" name="mileageGoods" value="<?=$data['mileageGoodsUnit'] == 'percent' ? $data['mileageGoods'] : gd_money_format($data['mileageGoods'], false) ?>" class="form-control width-sm">
                            <select name="mileageGoodsUnit" class="goods-unit form-control width-2xs">
                                <option value="percent" <?=gd_isset($selected['mileageGoodsUnit']['percent']); ?>>%</option>
                                <option value="mileage" <?=gd_isset($selected['mileageGoodsUnit']['mileage']); ?>><?=Globals::get('gSite.member.mileageBasic.unit'); ?></option>
                            </select>
                        </div>
                        <div class="mileage-set-g-group display-none">
                            <table class="table table-rows" style="width:auto;">
                                <thead>
                                <tr>
                                    <th>회원등급</th>
                                    <th>지급금액</th>
                                </tr>
                                </thead>
                                <?php
                                if (empty($data['mileageGroupMemberInfo']) === false) {
                                    foreach ($data['mileageGroupMemberInfo']['groupSno'] as $key => $val) {
                                        ?>
                                        <tr>
                                            <td><?php echo gd_select_box(null, "mileageGroupMemberInfo['groupSno'][]", $groupList, null, $val, '=회원등급 선택='); ?></td>
                                            <td class="form-inline">
                                                <span class="goods-title">구매금액의</span>
                                                <input type="text" name="mileageGroupMemberInfo['mileageGoods'][]" value="<?php echo $data['mileageGroupMemberInfo']['mileageGoodsUnit'][$key] == 'percent' ? $data['mileageGroupMemberInfo']['mileageGoods'][$key] : gd_money_format($data['mileageGroupMemberInfo']['mileageGoods'][$key], false);?>" class="form-control width-sm">
                                                <select name="mileageGroupMemberInfo['mileageGoodsUnit'][]" class="goods-unit form-control width-2xs">
                                                    <option value="percent" <?=gd_isset($selected['mileageGroupMemberInfo']['mileageGoodsUnit'][$key]['percent']); ?>>%</option>
                                                    <option value="mileage" <?=gd_isset($selected['mileageGroupMemberInfo']['mileageGoodsUnit'][$key]['mileage']); ?>><?=Globals::get('gSite.member.mileageBasic.unit'); ?></option>
                                                </select>
                                                <?php if ($key === 0) { ?>
                                                    <input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus add-groupSno" data-target="mileage">
                                                <?php } else { ?>
                                                    <input type="button" value="삭제" class="btn btn-sm btn-white btn-icon-minus del-groupSno" data-target="mileage">
                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else{
                                    ?>
                                    <tr>
                                        <td><?php echo gd_select_box(null, "mileageGroupMemberInfo['groupSno'][]", $groupList, null, null, '=회원등급 선택='); ?></td>
                                        <td class="form-inline">
                                            <span class="goods-title">구매금액의</span>
                                            <input type="text" name="mileageGroupMemberInfo['mileageGoods'][]" value="" class="form-control width-sm">
                                            <select name="mileageGroupMemberInfo['mileageGoodsUnit'][]" class="goods-unit form-control width-2xs">
                                                <option value="percent" selected="selected">%</option>
                                                <option value="mileage"><?=Globals::get('gSite.member.mileageBasic.unit'); ?></option>
                                            </select>
                                            <input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus add-groupSno" data-target="mileage">
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
            <div class="notice-info">
                통합설정 <a href="../member/member_mileage_give.php" target="_blank" class="btn-link">회원>마일리지/예치금 관리>마일리지 지급설정</a>에서 설정한 기준에 따름 :
                <?php if ($conf['mileage']['giveType'] == 'price') { ?>
                    구매 금액의 <span><?=$conf['mileage']['goods']; ?>%</span>를  마일리지로 지급
                <?php } else if ($conf['mileage']['giveType'] == 'priceUnit') { ?>
                    구매금액으로 <?=number_format($conf['mileage']['goodsPriceUnit']); ?>원 단위로 <?=number_format($conf['mileage']['goodsMileage']); ?> 마일리지 지급
                <?php } else if ($conf['mileage']['giveType'] == 'cntUnit') { ?>
                    구매금액과 상관없이 구매상품 1개 단위로 <?=number_format($conf['mileage']['cntMileage']); ?> 마일리지 지급
                <?php } ?><br />
                구매금액 <a href="../member/member_mileage_basic.php" target="_blank" class="btn-link">회원>마일리지/예치금 관리>마일리지 기본설정</a>에서 설정한 기준에 따름 : <?php echo $conf['mileageBasic']['mileageText']; ?><br />
                절사기준 <a href="../policy/base_currency_unit.php" target="_blank" class="btn-link">기본설정>기본정책>금액/단위 기준설정</a>에서 설정한 기준에 따름 : <?=gd_trunc_display('mileage'); ?>
            </div>
        </div>

        <div class="table-title gd-help-manual" id="goodsDiscount">
            상품 할인/혜택 설정
            <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="goodsDiscount"><span>닫힘</span></button></span>
        </div>

        <input type="hidden" id="depth-toggle-hidden-goodsDiscount" value="<?=$toggle['goodsDiscount_'.$SessScmNo]?>">
        <div id="depth-toggle-line-goodsDiscount" class="depth-횡toggle-line display-none"></div>
        <div id="depth-toggle-layer-goodsDiscount" style="margin-bottom:20px;">

            <table class="table table-cols" style="margin-bottom:0;">
                <colgroup>
                    <col class="width-lg">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th>적용방법</th>
                    <td>
                        <div class="pdl5">
                            <label class="radio-inline">
                                <input type="radio" name="goodsBenefitSetFl" value="n"  onclick="display_benefit_set('n', 'goods_benefit_group');" <?=gd_isset($checked['goodsBenefitSetFl']['n']); ?>/>개별설정
                            </label>
                            <?php if($goodsBenefitUse == 'y'){?>
                                <label class="radio-inline">
                                    <input type="radio" name="goodsBenefitSetFl" value="y"  onclick="display_benefit_set('y', 'goods_benefit_group');layer_register('goods_benefit','layer')" <?=gd_isset($checked['goodsBenefitSetFl']['y']); ?>/>상품 혜택 적용</label>
                                <label>
                                    <button type="button" class="btn btn-sm btn-gray js-goods-benefit-select">혜택 선택</button>
                                </label>
                                <span class="notice-info"> <a href="../goods/goods_benefit_list.php" class="btn-link" target="_blank">상품>상품관리>상품 혜택관리</a>에서 등록한 상품 혜택정보를 적용할 수 있습니다.</span>
                            <?php }?>
                        </div>
                        <?php if($goodsBenefitData['sno'] > 0){?>
                            <input type="hidden" name="benefitCheckSno" value="<?=$goodsBenefitData['sno']?>">
                        <?php }?>
                        <div id="goods_benefit_group" class="selected-btn-group <?= ($goodsBenefitData['sno']) > 0 ? 'active' : '' ?>">
                            <?php if($goodsBenefitData['sno'] > 0){

                                $arrUseType    = array('nonLimit' => '제한없음', 'newGoodsDiscount' => '신상품 할인', 'periodDiscount' => '특정기간 할인');
                                $arrDiscountGroup        = array('all' => '전체', 'member' => '회원전용', 'group' => '특정회원등급');
                                $arrNewGoodsReg        = array('regDt' => '등록일', 'modDt' => '수정일');
                                $arrNewGoodsDate      = array('day' => '일', 'hour' => '시간');

                                $goodsBenefitData['goodsDiscountGroupMemberInfo'] = json_decode($goodsBenefitData['goodsDiscountGroupMemberInfo'], true);

                                if($goodsBenefitData['benefitUseType'] == 'nonLimit'){
                                    $benefitPeriod = '<span class="">'.$arrUseType[$goodsBenefitData['benefitUseType']].'</span>';
                                }else if($goodsBenefitData['benefitUseType'] == 'newGoodsDiscount'){
                                    $benefitPeriod = '<span class="">상품'.$arrNewGoodsReg[$goodsBenefitData['newGoodsRegFl']].'부터 '.$goodsBenefitData['newGoodsDate'].$arrNewGoodsDate[$goodsBenefitData['newGoodsDateFl']].'까지</span>';
                                }else{
                                    $benefitPeriod ='<span>'.gd_date_format("Y-m-d H:i",$goodsBenefitData['periodDiscountStart']).' ~ '.gd_date_format("Y-m-d H:i",$goodsBenefitData['periodDiscountEnd']).'</span>';
                                }

                                ?>
                                <h5>선택된 혜택:</h5>
                                <div id="info_goods_benefit_group_<?=$goodsBenefitData['sno']?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="benefitSno" value="<?=$goodsBenefitData['sno']?>">
                                    <input type="hidden" name="benefitScheduleSnoEndDt" value="<?=$goodsBenefitData['periodDiscountEnd']?>">
                                    <span><?=$goodsBenefitData['benefitNm']?>(
                                        <?php
                                        if($goodsBenefitData['goodsDiscountGroup'] !='group'){
                                            if($goodsBenefitData['goodsDiscountUnit'] == 'percent'){
                                                echo $goodsBenefitData['goodsDiscount']. '% - ' ;
                                            }else{
                                                echo gd_money_format($goodsBenefitData['goodsDiscount'], false). gd_currency_default().' - ';
                                            }
                                        }
                                        ?>
                                        <?=$arrDiscountGroup[$goodsBenefitData['goodsDiscountGroup']]?>
                                        ) (<?=$benefitPeriod?>)</span>
                                    <span>
                                        <button type="button" class="btn btn-sm btn-white" data-benefitSno="<?=$goodsBenefitData['sno']?>" onclick="goods_benefit_popup('<?=$goodsBenefitData['sno']?>')">수정</button>
                                        <button type="button" class="btn btn-sm btn-white" data-toggle="delete" data-target="#info_goods_benefit_group_<?=$goodsBenefitData['sno']?>">삭제</button>
                                    </span>
                                </div>
                            <?php } ?>
                            <span class="notice-info">신상품 할인, 특정기간 할인 혜택을 상품에 적용할 경우, 혜택 종료 시 혜택 제외 설정도 함께 종료됩니다.</span>
                        </div>

                        <div id="goodsBenefitConfig">
                            <table class="table table-cols mgt10" style="margin-bottom:0;">
                                <colgroup>
                                    <col class="width-lg">
                                    <col>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <th colspan="2">상품 할인 설정</th>
                                </tr>
                                <tr>
                                    <th>사용여부</th>
                                    <td>
                                        <div class="radio">
                                            <label class="radio-inline">
                                                <input type="radio" name="goodsDiscountFl" value="n" onclick="display_toggle_class('goodsDiscountFl', 'goodsDiscountConfig');" <?=gd_isset($checked['goodsDiscountFl']['n']); ?>>사용안함
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="goodsDiscountFl" value="y" onclick="display_toggle_class('goodsDiscountFl', 'goodsDiscountConfig');" <?=gd_isset($checked['goodsDiscountFl']['y']); ?>>사용함
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="goodsDiscountConfig">
                                    <th class="input_title">진행유형</th>
                                    <td class="input_area">

                                        <div>
                                            <label class="radio-inline">
                                                <input type="radio" name="benefitUseType" value="nonLimit" <?= $checked['benefitUseType']['nonLimit'] ?>>제한 없음
                                            </label>
                                        </div>

                                        <div class="pdt10">
                                            <label class="radio-inline">
                                                <input type="radio" name="benefitUseType" value="newGoodsDiscount" <?= $checked['benefitUseType']['newGoodsDiscount'] ?>>신상품 할인
                                            </label>
                                            <div class="form-inline pdt10 pdl20">
                                                상품 등록일
                                                <select name="newGoodsRegFl" class="form-control" style="display:none">
                                                    <option value="regDt" <?=gd_isset($selected['newGoodsRegFl']['regDt']); ?>>등록일</option>
                                                    <!--<option value="modDt" <?=gd_isset($selected['newGoodsRegFl']['modDt']); ?>>수정일</option>-->
                                                </select>
                                                부터
                                                <input type="text" name="newGoodsDate" value="<?=$data['newGoodsDate']?>" class="form-control width-2xs" maxlength="5">
                                                <select name="newGoodsDateFl" class="form-control">
                                                    <option value="day" <?=gd_isset($selected['newGoodsDateFl']['day']); ?>>일</option>
                                                    <option value="hour" <?=gd_isset($selected['newGoodsDateFl']['hour']); ?>>시간</option>
                                                </select>
                                                까지
                                            </div>
                                        </div>

                                        <div class="pdt10">
                                            <label class="radio-inline">
                                                <input type="radio" name="benefitUseType" value="periodDiscount" <?= $checked['benefitUseType']['periodDiscount'] ?>>특정기간 할인
                                            </label>

                                            <div class="form-inline pdt10 pdl20">
                                                <div class="input-group js-datetimepicker">
                                                    <input type="text" class="form-control width-sm" name="periodDiscountStart" value="<?=$data['periodDiscountStart'] ?>" >
                                                    <span class="input-group-addon">
                                                        <span class="btn-icon-calendar">
                                                        </span>
                                                    </span>
                                                </div>
                                                ~
                                                <div class="input-group js-datetimepicker">
                                                    <input type="text" class="form-control width-sm" name="periodDiscountEnd" value="<?=$data['periodDiscountEnd'] ?>" >
                                                    <span class="input-group-addon">
                                                        <span class="btn-icon-calendar">
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                                <tr class="goodsDiscountConfig">
                                    <th>할인금액 기준</th>
                                    <td>
                                        <input type="checkbox" checked="checked" disabled="disabled">판매가&nbsp;+&nbsp;(&nbsp;
                                        <?= gd_check_box('fixedGoodsDiscount[]', $fixedGoodsDiscount, $data['fixedGoodsDiscount']); ?>
                                        &nbsp;)&nbsp;
                                    </td>
                                </tr>
                                <tr class="goodsDiscountConfig">
                                    <th>대상 선택</th>
                                    <td>
                                        <label class="radio-inline"><input type="radio" name="goodsDiscountGroup" value="all" <?= $checked['goodsDiscountGroup']['all'] ?> onclick="display_goods_discount_set();">전체(회원+비회원)</label>
                                        <label class="radio-inline"><input type="radio" name="goodsDiscountGroup" value="member" <?= $checked['goodsDiscountGroup']['member'] ?> onclick="display_goods_discount_set();">회원전용(비회원제외)</label>
                                        <label class="radio-inline"><input type="radio" name="goodsDiscountGroup" value="group" <?= $checked['goodsDiscountGroup']['group'] ?> onclick="display_goods_discount_set();">특정회원등급</label>
                                    </td>
                                </tr>
                                <tr class="goodsDiscountConfig">
                                    <th>금액 설정</th>
                                    <td>
                                        <div class="goods-discount-all hide form-inline">
                                            <span class="goods-title">구매금액의</span>
                                            <input type="text" name="goodsDiscount" value="<?=$data['goodsDiscountUnit'] == 'percent' ? $data['goodsDiscount'] : gd_money_format($data['goodsDiscount'], false);?>" class="form-control width-sm">
                                            <select name="goodsDiscountUnit" class="goods-unit form-control width-2xs">
                                                <option value="percent" <?=gd_isset($selected['goodsDiscountUnit']['percent']); ?>>%</option>
                                                <option value="price" <?=gd_isset($selected['goodsDiscountUnit']['price']); ?>><?=gd_currency_default(); ?></option>
                                            </select>
                                        </div>
                                        <div class="goods-discount-group hide">
                                            <table class="table table-rows" style="width:auto;">
                                                <thead>
                                                <tr>
                                                    <th>회원등급</th>
                                                    <th>할인금액</th>
                                                </tr>
                                                </thead>
                                                <?php
                                                if (empty($data['goodsDiscountGroupMemberInfo']) === false) {
                                                    foreach ($data['goodsDiscountGroupMemberInfo']['groupSno'] as $key => $val) {
                                                        ?>
                                                        <tr>
                                                            <td><?php echo gd_select_box(null, "goodsDiscountGroupMemberInfo['groupSno'][]", $groupList, null, $val, '=회원등급 선택='); ?></td>
                                                            <td class="form-inline">
                                                                <span class="goods-title">구매금액의</span>
                                                                <input type="text" name="goodsDiscountGroupMemberInfo['goodsDiscount'][]" value="<?php echo $data['goodsDiscountGroupMemberInfo']['goodsDiscountUnit'][$key] == 'percent' ? $data['goodsDiscountGroupMemberInfo']['goodsDiscount'][$key] : gd_money_format($data['goodsDiscountGroupMemberInfo']['goodsDiscount'][$key], false);?>" class="form-control width-sm">
                                                                <select name="goodsDiscountGroupMemberInfo['goodsDiscountUnit'][]" class="goods-unit form-control width-2xs">
                                                                    <option value="percent" <?=gd_isset($selected['goodsDiscountGroupMemberInfo']['goodsDiscountUnit'][$key]['percent']); ?>>%</option>
                                                                    <option value="price" <?=gd_isset($selected['goodsDiscountGroupMemberInfo']['goodsDiscountUnit'][$key]['price']); ?>><?=gd_currency_default(); ?></option>
                                                                </select>
                                                                <?php if ($key === 0) { ?>
                                                                    <input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus add-groupSno" data-target="discount">
                                                                <?php } else { ?>
                                                                    <input type="button" value="삭제" class="btn btn-sm btn-white btn-icon-minus del-groupSno" data-target="discount">
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else{
                                                    ?>
                                                    <tr>
                                                        <td><?php echo gd_select_box(null, "goodsDiscountGroupMemberInfo['groupSno'][]", $groupList, null, null, '=회원등급 선택='); ?></td>
                                                        <td class="form-inline">
                                                            <span class="goods-title">구매금액의</span>
                                                            <input type="text" name="goodsDiscountGroupMemberInfo['goodsDiscount'][]" value="" class="form-control width-sm">
                                                            <select name="goodsDiscountGroupMemberInfo['goodsDiscountUnit'][]" class="goods-unit form-control width-2xs">
                                                                <option value="percent" selected="selected">%</option>
                                                                <option value="price"><?=gd_currency_default(); ?></option>
                                                            </select>
                                                            <input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus add-groupSno" data-target="discount">
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>

                            <span class="notice-info">절사기준 <a href="../policy/base_currency_unit.php" class="btn-link" target="_blank">[기본설정>기본정책>금액/단위 기준설정]</a>에서 설정한 기준에 따름 : <?=gd_trunc_display('goods'); ?></span>


                            <table class="table table-cols mgt20">
                                <colgroup>
                                    <col class="width-lg"/>
                                    <col/>
                                    <col/>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <th colspan="2">혜택 제외 설정 <span class="notice-info">상품 할인 설정의 진행유형 및 기간과 상관없이 별도 설정이 가능합니다.</span></th>
                                </tr>
                                <tr>
                                    <th>제외 혜택 선택</th>
                                    <td>
                                        <?= gd_check_box('exceptBenefit[]', $exceptBenefit, $data['exceptBenefit'], 1); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>제외 대상 선택</th>
                                    <td class="form-inline">
                                        <label class="radio-inline"><input type="radio" name="exceptBenefitGroup" value="all" <?= $checked['exceptBenefitGroup']['all'] ?> onclick="display_group_member('all', 'except_benefit_group');">전체회원</label>
                                        <label class="radio-inline"><input type="radio" name="exceptBenefitGroup" value="group" <?= $checked['exceptBenefitGroup']['group'] ?> onclick="display_group_member('group', 'except_benefit_group');layer_register('except_benefit_group','search')">특정회원등급</label>
                                        <label>
                                            <button type="button" class="btn btn-sm btn-gray js-except-benefit-group-select">회원등급 선택</button>
                                        </label>

                                        <div id="except_benefit_group" class="selected-btn-group <?= empty($data['exceptBenefitGroupInfo']) === false ? 'active' : '' ?>">
                                            <?php if (empty($data['exceptBenefitGroupInfo']) === false) { ?>
                                                <h5>선택된 회원등급</h5>
                                                <?php foreach ($data['exceptBenefitGroupInfo'] as $k => $v) { ?>
                                                    <span id="info_except_benefit_group_<?= $v ?>" class="btn-group btn-group-xs">
                                        <input type="hidden" name="exceptBenefitGroupInfo[]" value="<?= $v ?>"/>
                                        <span class="btn"><?= $groupList[$v] ?></span>
                                        <button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_except_benefit_group_<?= $v ?>">삭제</button>
                                    </span>
                                                <?php }
                                            } ?>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>


                    </td>
                </tr>
                </tbody>
            </table>
        </div>


    <?php } ?>

    <div class="table-title gd-help-manual">
        가격 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="setPrice"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-setPrice" value="<?=$toggle['setPrice_'.$SessScmNo]?>">
    <div id="depth-toggle-line-setPrice" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-setPrice">

        <input type="hidden" name="optionN[sno][0]" value="<?php if ($applyGoodsCopy === false) {
            echo gd_isset($data['option'][0]['sno']);
        } ?>"/>
        <input type="hidden" name="optionN[optionNo][0]" value="<?=gd_isset($data['option'][0]['optionNo']); ?>"/>

        <div id="gd_goods_price">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-lg"/>
                    <col class="width-xl"/>
                    <col class="width-md"/>
                    <col class="width-xl"/>
                    <col class="width-md"/>
                    <col class="width-xl"/>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr>
                    <th>정가</th>
                    <td>
                        <div class="form-inline">
                            <?=gd_currency_symbol(); ?>
                            <input type="text" name="fixedPrice" value="<?=gd_money_format($data['fixedPrice'], false); ?>" class="form-control width-sm"/>
                            <?=gd_currency_string(); ?>
                        </div>
                    </td>
                    <th>매입가</th>
                    <td>
                        <div class="form-inline">
                            <?=gd_currency_symbol(); ?>
                            <input type="text" name="costPrice" value="<?=gd_money_format($data['costPrice'], false); ?>" class="form-control width-sm"/>
                            <?=gd_currency_string(); ?>
                        </div>
                    </td>
                    <th>가격 대체 문구</th>
                    <td colspan="3">
                        <div class="form-inline">
                            <input type="text" name="goodsPriceString" value="<?=$data['goodsPriceString']; ?>" class="form-control width-sm js-maxlength" maxlength="30"/>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>판매가</th>
                    <td>
                        <div class="form-inline">
                            <?=gd_currency_symbol(); ?>
                            <input type="text" name="goodsPrice" value="<?=gd_money_format($data['goodsPrice'], false); ?>" class="form-control width-sm" onchange="setCommissionPrice()">
                            <?=gd_currency_string(); ?>
                        </div>
                    </td>
                    <th>공급가</th>
                    <td>
                        <div class="form-inline">
                            <?=gd_currency_symbol(); ?>
                            <input type="text" name="supplyPrice" value="" class="form-control width-sm" disabled="disabled">
                            <?=gd_currency_string(); ?>
                        </div>
                    </td>
                    <th>수수료율</th>
                    <td>
                        <div class="form-inline">
                            <input type="text" name="commissionText" value="0" class="form-control width-sm" disabled="disabled"> %
                        </div>
                    </td>
                    <th>수수료액</th>
                    <td>
                        <div class="form-inline">
                            <?=gd_currency_symbol(); ?>
                            <input type="text" name="commissionPrice" value="" class="form-control width-sm" disabled="disabled">
                            <?=gd_currency_string(); ?>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="table-title gd-help-manual" id="stockOption">
        옵션/재고 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="stockOption"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-stockOption" value="<?=$toggle['stockOption_'.$SessScmNo]?>">
    <div id="depth-toggle-line-stockOption" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-stockOption">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>옵션 사용</th>
                <td>
                    <label class="radio-inline">
                        <?php
                        // 상품 재고 수정 권한 없는 경우 상품재고 수정 불가
                        if (empty($data['goodsNo']) === false && Session::get('manager.functionAuth.goodsStockModify') != 'y') {
                            $optionToggle = 'true';
                        } else {
                            $optionToggle = 'false';
                        }
                        ?>
                        <input type="radio" name="optionFl" value="y" onclick="display_toggle('optionExist','show');disabled_switch('stockCnt',true);" <?=gd_isset($checked['optionFl']['y']); ?>>사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="optionFl" value="n" onclick="display_toggle('optionExist','hide');disabled_switch('stockCnt',<?=$optionToggle;?>);" <?=gd_isset($checked['optionFl']['n']); ?>>사용안함
                    </label>
                </td>
            </tr>
            </tbody>
            <tbody id="optionExist" class="display-none">
            <tr>
                <th>자주쓰는 옵션</th>
                <td>
                    <button type="button" class="btn btn-sm btn-gray" onclick="manage_option_list()">자주쓰는 옵션</button>
                    <button type="button" class="btn btn-sm btn-gray" onclick="manage_option_register();">자주쓰는 옵션 등록</button>
                </td>
            </tr>
            <?php if ($data['goodsPriceString']) { ?>
                <tr>
                    <th>안내</th>
                    <td><span class="notice-danger">가격대체문구를 사용중이므로 해당 상품은 주문이 되지 않습니다.</span></td>
                </tr>
            <?php } ?>
            <tr>
                <th>옵션 노출 방식</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="optionY[optionDisplayFl]" value="s" <?=gd_isset($checked['optionDisplayFl']['s']); ?> />일체형(조합)
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="optionY[optionDisplayFl]" value="d" <?=gd_isset($checked['optionDisplayFl']['d']); ?> />분리형(조합)
                    </label>
                </td>
            </tr>
            <tr>
                <th>옵션 이미지 노출 설정</th>
                <td>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="optionImagePreviewFl" value="y" <?=gd_isset($checked['optionImagePreviewFl']['y']); ?> />미리보기 사용
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="optionImageDisplayFl" value="y" <?=gd_isset($checked['optionImageDisplayFl']['y']); ?> />상세이미지에 추가
                    </label>
                </td>
            </tr>
            <tr>
                <th>옵션 개수</th>
                <td>
                    <div class="form-inline">
                        <?=gd_select_box('optionY_optionCnt', 'optionY[optionCnt]', gd_array_change_key_value(range(1, DEFAULT_LIMIT_OPTION)), '개', $data['optionCnt'], '=옵션 개수=', 'onchange="option_setting(this.value);"'); ?>
                        <span><input type="button" class="btn btn-white btn-sm" onclick="option_reset();" value="초기화"/></span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>옵션 등록</th>
                <td>
                    <div class="notice-info">

                        옵션 이미지 미리보기 : 쇼핑몰 상세페이지에서 옵션 선택 시 옵션 이미지가 상세이미지 영역에 노출됩니다.<br/>
                        상세이미지 하단 노출 : 옵션 이미지가 상세이미지 영역 하단에 추가 노출됩니다.<br/>
                        “직접 업로드와 URL 직접입력” 방식 모두 사용하여 이미지를 등록한 경우 “직접 업로드”된 이미지만 적용됩니다.<br/>

                        옵션명/옵션값 설정후에 자동으로 옵션의 가격 설정부분이 나오게 됩니다. <input type="button" class="btn btn-sm btn-gray" onclick="option_grid('y');" value="옵션 가격 설정 적용">
                    </div>
                    <div id="option"></div>
                    <div id="optionGrid">
                        <?php
                        if (!empty($data['option']) && $data['optionCnt'] > 0) {
                            ?>
                            <table id="optionGridTable" class="table table-cols">
                                <thead>
                                <tr>
                                    <th class="width2p"><input type="checkbox" id="allOptionCheck" value="y" onclick="check_toggle(this.id,'optionY[optionNo][]');"/></th>
                                    <th class="width2p">번호</th>
                                    <?php
                                    for ($i = 0; $i < $data['optionCnt']; $i++) {
                                        echo '<th class="width10p">' . $data['optionName'][$i] . '</th>';
                                    }
                                    ?>
                                    <th class="width10p">옵션 매입가</th>
                                    <th class="width10p">옵션가</th>
                                    <th class="width10p">재고량</th>
                                    <th class="width10p">자체 옵션코드</th>
                                    <th class="width10p">노출상태</th>
                                    <th class="width10p">품절상태</th>
                                    <th class="width10p">메모</th>
                                </tr>
                                </thead>
                                <tr>
                                    <th class="center" colspan="<?=$data['optionCnt']+2; ?>">
                                        <input type="button" onclick="option_value_apply();" value="옵션 정보 일괄 적용" class="btn btn-xs btn-gray"/>
                                    </th>
                                    <th class="center">
                                        <div class="form-inline"><?=gd_currency_symbol(); ?>
                                            <input type="text" id="option_optionCostPriceApply" class="form-control width-2xs"/><?=gd_currency_string(); ?>
                                        </div>
                                    </th>
                                    <th class="center">
                                        <div class="form-inline"><?=gd_currency_symbol(); ?>
                                            <input type="text" id="option_opotionPriceApply" class="form-control width-2xs"/><?=gd_currency_string(); ?>
                                        </div>
                                    </th>
                                    <th class="center">
                                        <div class="form-inline">
                                            <input type="text" id="option_stockCntApply" class="form-control width-2xs" <?=$disabled['option_stockCntApply'];?>/>개
                                        </div>
                                    </th>
                                    <th class="center"><input type="text" id="option_optionCodeApply" class="form-control"/>
                                    </td>
                                    <th class="center"><select class="form-control" id="option_optionViewFlApply">
                                            <option value="y">노출함</option>
                                            <option value="n">노출안함</option>
                                        </select>
                                    </td>
                                    <th class="center"><select class="form-control" id="option_optionSellFlApply">
                                            <option value="y">정상</option>
                                            <option value="n">품절</option>
                                        </select>
                                    </td>
                                    <th class="center">
                                        <div class="form-inline">
                                            <input type="text" id="option_optionMemoApply" class="form-control width-xs"/>
                                        </div>
                                    </th>
                                </tr>
                                <?php
                                $nextNo = 0;
                                foreach ($data['option'] as $key => $val) {
                                    $nextNo++;
                                    ?>
                                    <tr id="tbl_option_info_<?=$key+1?>">
                                        <td class="center"><input type="checkbox" name="optionY[optionNo][]" value="<?=$key+1?>"></td>
                                        <td class="center"><?=$key+1?></td>
                                        <?php
                                        $tmpOptionText = [];
                                        for ($i = 0; $i < $data['optionCnt']; $i++) {
                                            $tmpOptionText[] = $data['option'][$key]['optionValue' . ($i + 1)];

                                            ?>
                                            <td class="center">
                                                <?=gd_isset($data['option'][$key]['optionValue' . ($i + 1)]); ?>
                                            </td>
                                            <?php
                                        }
                                        ?>
                                        <input type="hidden" name="optionY[sno][]" value="<?php if ($applyGoodsCopy === false) {
                                            echo gd_isset($data['option'][$key]['sno']);
                                        } ?>"/>
                                        <input type="hidden" name="optionY[optionValueText][]" value="<?=implode(STR_DIVISION,$tmpOptionText)?>"/>
                                        <td class="center">
                                            <div class="form-inline">
                                                <input type="text" name="optionY[optionCostPrice][]" value="<?=gd_money_format(gd_isset($data['option'][$key]['optionCostPrice']), false);?>" class="form-control width-2xs"/>
                                            </div>
                                        </td>
                                        <td class="center">
                                            <div class="form-inline"><?=gd_currency_symbol(); ?>
                                                <input type="text" name="optionY[optionPrice][]" value="<?=gd_money_format(gd_isset($data['option'][$key]['optionPrice']), false); ?>" class="form-control width-2xs"/><?=gd_currency_string(); ?>
                                            </div>
                                        </td>
                                        <td class="center">
                                            <div class="form-inline">
                                                <input type="text" name="optionY[stockCnt][]" value="<?=gd_isset($data['option'][$key]['stockCnt']); ?>" class="form-control width-2xs" <?=$disabled['optionY[stockCnt][]'];?>/>개
                                            </div>
                                        </td>
                                        <td class="center">
                                            <input type="text" name="optionY[optionCode][]" class="form-control" value="<?=gd_isset($data['option'][$key]['optionCode']); ?>"/>
                                        </td>
                                        <td class="center"><select class="form-control" name="optionY[optionViewFl][]">
                                                <option value="y" <?php if ($data['option'][$key]['optionViewFl'] == 'y') {
                                                    echo "selected";
                                                } ?>>노출함
                                                </option>
                                                <option value="n" <?php if ($data['option'][$key]['optionViewFl'] == 'n') {
                                                    echo "selected";
                                                } ?>>노출안함
                                                </option>
                                            </select></td>
                                        <td class="center"><select class="form-control" name="optionY[optionSellFl][]">
                                                <option value="y" <?php if ($data['option'][$key]['optionSellFl'] == 'y') {
                                                    echo "selected";
                                                } ?>>정상
                                                </option>
                                                <option value="n" <?php if ($data['option'][$key]['optionSellFl'] == 'n') {
                                                    echo "selected";
                                                } ?>>품절
                                                </option>
                                            </select></td>
                                        <td class="center">
                                            <div class="form-inline">
                                                <input type="text" name="optionY[optionMemo][]" value="<?=gd_isset($data['option'][$key]['optionMemo']); ?>" class="form-control width-xs"/>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tfoot>
                                <tr>
                                    <td colspan="<?=$data['optionCnt']+7?>"><input type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="delete_option('optionY[optionNo][]','tbl_option_info_');" value="선택 삭제" /></td>
                                </tr>
                                </tfoot>
                            </table>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="notice-info">

                        옵션가는 상품의 판매가 기준 추가 또는 차감될 옵션별 금액이 있는 경우에만 입력합니다.<br/>
                        <span class="text-danger">판매가에 추가될 옵션가는 양수, 차감될 옵션가는 음수(마이너스)로 입력 합니다.<br/>
                        상품 매입가에 추가될 옵션 매입가는 양수, 차감될 옵션 매입가는 음수(마이너스)로 입력 합니다.
                        </span>

                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        텍스트 옵션 / 추가상품 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="textOption"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-textOption" value="<?=$toggle['textOption_'.$SessScmNo]?>">
    <div id="depth-toggle-line-textOption" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-textOption">
        <div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-lg"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr>
                    <th>텍스트 옵션</th>
                    <td>
                        <div>
                            <label class="radio-inline" title="텍스트 옵션을 사용시 &quot;텍스트 옵션 사용&quot;을 선택하세요! 텍스트 옵션은 DEFAULT_LIMIT_TEXTOPTION개 까지만 가능합니다.">
                                <input type="radio" name="optionTextFl" value="y" <?=gd_isset($checked['optionTextFl']['y']); ?> onclick="display_toggle('optionTextDiv','show');use_option_text();"/>사용함
                            </label>
                            <label class="radio-inline" title="텍스트 옵션을 사용시 &quot;텍스트 옵션 사용안함&quot;을 선택하세요!">
                                <input type="radio" name="optionTextFl" value="n" <?=gd_isset($checked['optionTextFl']['n']); ?> onclick="display_toggle('optionTextDiv','hide');"/>사용안함
                            </label>
                        </div>
                        <div id="optionTextDiv" class="display-none">
                            <table id="optionTextForm" class="table table-cols mgt10">
                                <colgroup>
                                    <col class="width-xl"/>
                                    <col class="width-xl"/>
                                    <col class="width-xl"/>
                                    <col class="width-xl"/>
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>옵션명</th>
                                    <th>옵션가</th>
                                    <th>입력제한 글자수</th>
                                    <th>필수</th>
                                </tr>
                                </thead>
                                <?php
                                if (!empty($data['optionText'])) {
                                    foreach ($data['optionText'] as $key => $val) {
                                        $nextNo = $key + 1;
                                        $checked['mustFl'] = null;
                                        if ($val['mustFl'] == 'y') {
                                            $checked['mustFl'] = 'checked="checked"';
                                        }
                                        ?>
                                        <tr id="optionTextForm<?=$nextNo; ?>">
                                            <td class="left">
                                                <div class="form-inline">
                                                    <?php if ($applyGoodsCopy === false) { ?>
                                                        <input type="hidden" name="optionText[sno][]" value="<?=$val['sno']; ?>"/>
                                                    <?php } ?>
                                                    <input type="text" name="optionText[optionName][]" value="<?=$val['optionName']; ?>" class="form-control width-lg"/>
                                                    <?php if ($key != '0') { ?>
                                                        <input type="button" onclick="field_remove('optionTextForm<?=$nextNo; ?>');" value="-" class="btn btn-gray btn-xs"/><?php } ?>
                                                    <?php if ($key == '0') { ?>
                                                        <input type="button" onclick="add_option_text();" value="+" class="btn btn-black btn-xs"/> <?php } ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-inline">
                                                    <?=gd_currency_symbol(); ?>
                                                    <input type="text" name="optionText[addPrice][]" value="<?=gd_money_format($val['addPrice'], false); ?>" class="width-sm form-control"/>
                                                    <?=gd_currency_string(); ?>
                                                </div>
                                            </td>
                                            <td class="center">
                                                <div class="form-inline">
                                                    <input type="text" name="optionText[inputLimit][]" value="<?=$val['inputLimit']; ?>" class="form-control js-number width-sm"/> 글자
                                                </div>
                                            </td>
                                            <td class="center">
                                                <input type="checkbox" name="optionText[mustFl][<?=$key; ?>]" value="y" <?=$checked['mustFl']; ?> />
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>추가상품</th>
                    <td>
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="addGoodsFl" value="y" <?=gd_isset($checked['addGoodsFl']['y']); ?> onclick="displayAddGoodsInfo(this.value);">사용함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="addGoodsFl" value="n" <?=gd_isset($checked['addGoodsFl']['n']); ?> onclick="displayAddGoodsInfo(this.value);">사용안함
                            </label>

                            <table class="table table-cols display-none mgt10 " id="addGoodsGroupTitleInfo">
                                <colgroup>
                                    <col class="width-md"/>
                                    <col />
                                </colgroup>
                                <tbody>
                                <tr>
                                    <th>추가상품 표시명</th>
                                    <td>
                                        <div class="form-inline">
                                            <input type="text" name="addGoodsGroupTitle" class="form-control width-md">
                                            <input type="button" class="btn btn-sm btn-white btn-icon-plus" type="button" value="추가" onclick="set_add_goods_group()"/>
                                            <span class="notice-info">표시명 추가후에 상품선택이 가능합니다.</span>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                </tbody>
                <tbody class='add-goods-group-info' <?php if (!$data['addGoods']) { ?>style="display:none" <?php } ?>>
                <tr>
                    <th style="padding:0px;vertical-align:top; ">
                        <table class="table" style="padding:0px;margin:0px;" id="addGoodsGroupInfo">
                            <?php if ($data['addGoods']) { ?>
                                <?php foreach ($data['addGoods'] as $k => $v) { ?>
                                    <tr id="addGoodsGroup<?= $k ?>" data-active="<?= $k ?>">
                                        <th>
                                            <div class="form-inline hand" onclick="select_add_goods_group('<?= $k ?>')">· <?= $v['title'] ?>
                                                <input type="hidden" name="addGoodsGroupTitle[<?= $k ?>]" value="<?= $v['title'] ?>">(<span id="addGoodsGroupCnt<?= $k ?>"><?= count($v['addGoodsList']) ?></span><input type="hidden" name="addGoodsGroupCnt[<?= $k ?>]" value="<?= count($v['addGoods']) ?>">개
                                                <span id=" id=" addGoodsGroupApplyCnt<?= $k ?>"><?php if ($v['addGoodsApplyCount'] > 0) {
                                                    echo " 중 미승인 " . $v['addGoodsApplyCount'] . "개";
                                                } ?></span>

                                                )
                                            </div>
                                            <div class="form-inline">
                                                <input type="checkbox" name="addGoodsGroupMustFl[<?= $k ?>]" value="y" <?php if ($v['mustFl'] == 'y') { ?>checked='checked'<?php } ?>>필수&nbsp;<span style="float:right"><input type="button" value="삭제" class="btn btn-icon-delete"  onclick="remove_add_goods_group(<?= $k ?>)"/></span>
                                            </div>
                                        </th>
                                    </tr>
                                <?php } ?>
                            <?php } ?>
                        </table>
                    </th>
                    <td style="vertical-align:top; ">
                        <div class="form-inline">
                            <div class="pull-left">
                                <input type="button" class="checkRegister btn btn-sm btn-black" type="button" onclick="add_goods_search_popup()" value="상품 선택"/>
                            </div>
                            <div class="pull-right">
                                <select name="add_goods_info_sel" onchange="set_add_goods_info(this.value)" class="form-control">
                                    <option>= 추가상품 그룹 선택 =</option>
                                </select>
                            </div>
                        </div>
                        <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows table-fixed">
                            <thead>
                            <tr id="goodsRegisteredTrArea">
                                <th class="width5p"><input type="checkbox" id="allCheck" value="y"
                                                           onclick="check_toggle(this.id,'itemGoodsNo');"/></th>
                                <th class="width5p">번호</th>
                                <th class="width10p">이미지</th>
                                <th class="width10p">상품명</th>
                                <th class="width15p">옵션</th>
                                <th class="width10p">판매가</th>
                                <th class="width10p">공급사</th>
                                <th class="width10p">재고</th>
                                <th class="width10p">품절상태</th>
                            </tr>
                            </thead>


                            <?php if ($data['addGoods']) { ?>
                                <?php foreach ($data['addGoods'] as $k => $v) { ?>
                                    <tbody id="addGoodsList<?= $k ?>">
                                    <?php
                                    if (empty($v['addGoodsList']) === false) {
                                        $cnt = count($v['addGoodsList']);
                                        foreach ($v['addGoodsList'] as $key => $val) {

                                            if($val['stockUseFl'] =='0') {
                                                $stockUseFl = "n";
                                            } else {
                                                $stockUseFl = "y";
                                            }

                                            list($totalStock,$stockText) = gd_is_goods_state($stockUseFl,$val['stockCnt'],$val['soldOutFl']);

                                            ?>

                                            <tr id="tbl_add_goods_<?=$val['addGoodsNo']; ?>" class="add_goods_free">
                                                <td class="center">
                                                    <input type="hidden" name="itemGoodsNm[]" value="<?= strip_tags($val['goodsNm']) ?>"/>
                                                    <input type="hidden" name="itemGoodsPrice[]" value="<?=gd_currency_display($val['goodsPrice']) ?>"/>
                                                    <input type="hidden" name="itemScmNm[]" value="<?= $val['scmNm'] ?>"/>
                                                    <input type="hidden" name="itemTotalStock[]" value="<?= $val['stockCnt'] ?>"/>
                                                    <input type="hidden" name="itemSoldOutFl[]" value="<?= gd_isset($val['soldOutFl']) ?>"/>
                                                    <input type="hidden" name="itemStockFl[]" value="<?= gd_isset($val['stockUseFl']) ?>"/>
                                                    <input type="hidden" name="itemBrandNm[]" value="<?= gd_isset($val['brandNm']) ?>"/>
                                                    <input type="hidden" name="itemMakerNm[]" value="<?= gd_isset($val['makerNm']) ?>"/>
                                                    <input type="hidden" name="itemOptionNm[]" value="<?= gd_isset($val['optionNm']) ?>"/>
                                                    <input type="hidden" name="itemImage[]" value="<?= rawurlencode(gd_html_add_goods_image($val['goodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, strip_tags($val['goodsNm']), '_blank')); ?>"/>
                                                    <input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?=$val['addGoodsNo']; ?>" value="<?=$val['addGoodsNo']; ?>"/>
                                                </td>
                                                <td class="center number" id="addGoodsNumber_<?=$val['addGoodsNo']; ?>"><?= $cnt-- ?></td>
                                                <td class="center"><?=gd_html_add_goods_image($val['goodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, gd_htmlspecialchars_decode($val['goodsNm']), '_blank'); ?></td>
                                                <td>
                                                    <?=gd_htmlspecialchars_decode($val['goodsNm']); ?>
                                                    <input type="hidden" name="goodsNoData[]" value="<?= $val['addGoodsNo'] ?>"/>
                                                </td>
                                                <td class="center"><?=$val['optionNm']; ?></td>
                                                <td class="center"><?=gd_currency_display($val['goodsPrice']); ?></td>
                                                <td class="center"><?=$val['scmNm']; ?></td>
                                                <td class="center"><?=$totalStock; ?></td>
                                                <td class="center"><?= $stockText ?></td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr id="tbl_add_goods_tr_none">
                                            <td class="no-data" colspan="9">선택된 추가 상품이 없습니다.</td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                    </tbody>
                                <?php } ?>
                            <?php } ?>

                        </table>

                        <div class="table-btn clearfix">
                            <div class="pull-left">
                                <button type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="delete_add_goods()">선택 삭제</button>
                            </div>
                        </div>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>


    <div class="table-title gd-help-manual" id="goodsImage">
        상품 이미지
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="goodsImage"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-goodsImage" value="<?=$toggle['goodsImage_'.$SessScmNo]?>">
    <div id="depth-toggle-line-goodsImage" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-goodsImage">
        <div>
            <div class="notice-info">
                <?php if (gd_is_provider() === false) { ?> 처음 상품 이미지를 등록 하신다면, 반드시
                    <a href="../policy/goods_images.php" target="_blank" class="desc_text_blue btn-link">상품 이미지 사이즈 설정</a> 먼저 설정하세요!<br/><?php } ?> 자동리사이즈는
                <span class="desc_text_blue">원본 이미지</span>만 등록하면 나머지 이미지들은 자동으로 리사이징 되는 간편한 기능입니다.<br/>
                이미지 개별 등록 시 “직접 업로드와 URL 직접입력” 방식 모두 사용할 수 있으며, URL 직접입력으로 등록된 이미지는 리사이즈되지 않습니다.<br/>
                “직접 업로드와 URL 직접입력” 방식 모두 사용하여 이미지를 여러장 등록한 경우 “확대/상세 이미지”외 나머지 이미지에는 “직접 업로드”된 이미지만 적용됩니다.<br/>
                이미지파일의 용량은 모두 합해서<span class="desc_text_red"><?=ini_get('upload_max_filesize'); ?>B까지</span>만 등록할 수 있습니다.<br/>
                <span class="text-danger">상품이미지는 되도록이면 영문으로만 올려주세요. 한글로 올리는 경우 제휴서비스 문제 및 일부 컴퓨터에서 안보이는 현상이 있습니다.</span>
            </div>

            <div id="goodsImageImg" class="display-none2 goods-img">
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-lg"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>이미지 저장소</th>
                        <td><span class="imageStorageText"></span> ( <label class="checkbox-inline"><input type="checkbox" name="imageAddUrl" value="y" onclick="image_add_url();"/> URL 직접입력 추가사용</label> )</td>
                    </tr>
                    <tr>
                        <th>원본 이미지</th>
                        <td>
                            <div class="mgt5 mgb5">
                                <label class="checkbox-inline"><input type="checkbox" name="imageResize[original]" value="y" onclick="image_resize_check_all(this.name);"/> 체크시 개별이미지의 선택된 사이즈로 자동 리사이즈되어 등록됩니다.</label>
                            </div>
                            <div id="imageOriginal" class="form-inline img-attch-space"></div>
                            <script type="text/javascript">goods_image('imageOriginal', 'y');</script>
                            <div class="notice-info">
                                <span class="text-danger">원본 이미지</span>는 자동리사이즈 기능을 위한 이미지로 따로 <span class="text-danger">저장되지 않습니다.</span><br/>
                                원본 이미지를 추가로 등록한 경우, 썸네일/리스트/운영자 추가 이미지에는 <span class="text-danger">처음 등록한 이미지만 적용</span>됩니다.
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>개별이미지</th>
                        <td>
                            <table class="table table-cols">
                                <?php
                                foreach ($conf['image'] as $key => $val) {
                                    ?>
                                    <tr>
                                        <th class="width-md"><?=$conf['image'][$key]['text']; ?></th>
                                        <td>
                                            <div class="form-inline mgb5">
                                                <label class="checkbox-inline">
                                                    <input type="checkbox" name="imageResize[<?=$key; ?>]" value="y" onclick="image_resize_check(this.name);"/>
                                                    <?php
                                                    foreach ($conf['image'][$key] as $sKey => $sVal) {
                                                        if ($sKey == 'text' || $sKey == 'addKey' || ($imageType == 'fixed' && (stripos($sKey, 'size') === 0)) ) {
                                                            continue;
                                                        }

                                                        if ($imageType == 'fixed') {
                                                            $imageKey = $sVal[0] . INT_DIVISION . $sVal[1];
                                                            $imageText = '가로 ' . $sVal[0] . ' pixel(픽셀) / 세로 ' . $sVal[1] . ' pixel(픽셀)';
                                                        } else {
                                                            $imageKey = $sVal;
                                                            $imageText = '가로 ' . $sVal . ' pixel(픽셀)';
                                                        }
                                                        $tmp[$imageKey] = $imageText;
                                                    }
                                                    if (count($tmp) == 1) {
                                                        echo '<input type="hidden" name="imageSize[' . $key . ']" value="' . array_keys($tmp)[0] . '" />"' . array_values($tmp)[0] . '"';
                                                    } else {
                                                        echo gd_select_box('imageSize' . ucfirst($key), 'imageSize[' . $key . ']', $tmp, null, null, null);
                                                    }
                                                    unset($tmp);
                                                    ?>
                                                    로 자동 리사이즈 합니다.
                                                </label>
                                            </div>

                                            <div id="image<?=ucfirst($key); ?>" class="form-inline img-attch-space"></div>

                                            <script type="text/javascript">goods_image('image<?=ucfirst($key);?>', '<?=$conf['image'][$key]['addKey'];?>');</script>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </table>
                            <p class="notice-danger">
                                개별이미지의 "자동 리사이즈" 기능은 원본 이미지를 대상으로만 적용됩니다.<br/>
                                원본이미지를 등록하지 않고 개별이미지의 자동 리사이즈를 체크한 경우 이미지는 등록되지 않습니다.
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <div id="goodsImageUrl" class="display-none">
                <table class="table table-cols">
                    <colgroup><col class="width-md" /><col/></colgroup>
                    <tr>
                        <th>이미지 저장소</th>
                        <td><span class="imageStorageText"></span></td>
                    </tr>
                    <?php
                    foreach ($conf['image'] as $key => $val) {
                        ?>
                        <tr>
                            <th><?=$conf['image'][$key]['text']; ?></th>
                            <td>
                                <div id="imageUrl<?=ucfirst($key); ?>"></div>
                                <script type="text/javascript">goods_image('imageUrl<?=ucfirst($key); ?>','<?=$conf['image'][$key]['addKey']; ?>','y');</script>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
            <?php
            if (gd_isset($data['image'])) {
                if($applyGoodsCopy) {
                    $data['imageStorage'] = $data['applyGoodsImageStorage'];
                }
                $imageAddUrlFl = "n";
                echo '<script type="text/javascript">' . chr(10) . '<!--' . chr(10);
                foreach ($data['image'] as $iKey => $iVal) {
                    $imageSize = [];
                    if ($imageType == 'fixed') {
                        foreach ($conf['image'][$iVal['imageKind']] as $key => $value) {
                            if (in_array($key, ['text', 'addKey']) === true) continue;
                            if ($iVal['imageSize'] == $value[0]) {
                                $imageSize = $value;
                                break;
                            }
                        }
                    }

                    $iVal['imageKind'] = strtolower($iVal['imageKind']);

                    if($iVal['imageKind'] != $data['image'][$iKey-1]['imageKind'] ) $imageNo = 0;

                    $imageInput = '<input type="hidden" name="imageDB[sno][]" value="' . $iVal['sno'] . '" /> ';
                    $imageInput .= '<input type="hidden" name="imageDB[imageSize][]" value="' . $iVal['imageSize'] . '" /> ';
                    $imageInput .= '<input type="hidden" name="imageDB[imageNo][]" value="' . $iVal['imageNo'] . '" /> ';
                    $imageInput .= '<input type="hidden" name="imageDB[imageKind][]" value="' . $iVal['imageKind'] . '" /> ';
                    $imageInput .= '<input type="hidden" name="imageDB[imageName][]" value="' . $iVal['imageName'] . '" /> ';
                    $imageInput .= '<input type="hidden" name="imageDB[imageRealSize][]" value="' . $iVal['imageRealSize'] . '" /> ';
                    $imageInput .= '<input type="hidden" name="imageDB[imageCode][' . $iVal['imageKind'] . $iVal['imageNo'] . ']" value="' . $iVal['sno'] . '" /> ';
                    if (strtolower(substr($iVal['imageName'],0,4)) =='http' ) {
                        $imageInput .= '<input type="hidden" name="imageDB[imageUrlFl][' . $iVal['imageKind'] . $iVal['imageNo'] . ']" value="' . $iVal['sno'] . '" /> ';
                    }

                    if($iVal['imageRealSize']) {
                        $imageWidth = explode(",",$iVal['imageRealSize'])[0];
                    } else {
                        $imageWidth = $iVal['imageSize'];
                    }

                    if ($data['imageStorage'] == 'url') {
                        if ($iVal['imageNo'] > 0) {
                            echo '	goods_image(\'imageUrl' . ucfirst($iVal['imageKind']) . '\',\'y\',\'y\');' . chr(10);
                        }
                        $preViewImg = $imageInput . gd_html_preview_image($iVal['imageName'], $data['imagePath'], $data['imageStorage'], 25, 'goods',null, null, true, true, $imageSize);
                        echo '	$(\'#imageUrl' . ucfirst($iVal['imageKind']) . 'URL' . $imageNo . '\').val(\'' . gd_htmlspecialchars_slashes($iVal['imageName'], 'add') . '\');' . chr(10);
                        echo '	$(\'#imageUrl' . ucfirst($iVal['imageKind']) . 'PreView' . $imageNo . '\').html(\' ' . gd_htmlspecialchars_slashes($preViewImg, 'add') . ' : 가로 ' . $imageWidth . 'pixel(픽셀))\');' . chr(10);
                        $imageNo++;
                    } else {
                        if($iVal['imageNo'] =='0' && strtolower(substr($iVal['imageName'],0,4)) =='http')  $imageNo++;

                        if ($iVal['imageNo'] > 0 || strtolower(substr($iVal['imageName'],0,4)) =='http' ) {
                            if(strtolower(substr($iVal['imageName'],0,4)) =='http' ) {
                                $imageAddUrlFl = "y";
                                if(!in_array($iVal['imageKind'],['detail','magnify']))  echo '	goods_image(\'image' . ucfirst($iVal['imageKind']) . '\',\'n\',\'y\');' . chr(10);
                                else echo '	goods_image(\'image' . ucfirst($iVal['imageKind']) . '\',\'r\',\'y\');' . chr(10);
                            } else {
                                echo '	goods_image(\'image' . ucfirst($iVal['imageKind']) . '\',\'r\');' . chr(10);
                            }
                        }

                        if (strtolower(substr($iVal['imageName'],0,4)) =='http' ) {
                            $delHtml = '&nbsp;&nbsp;<label class="checkbox-inline"><input type="checkbox" name="imageDB[imageUrlDelFl]['.$iVal['imageKind'] . $iVal['imageNo'].']" value="y">삭제</label>';
                            $preViewImg = $imageInput . gd_html_preview_image($iVal['imageName'], $data['imagePath'], 'url', 25, 'goods', null, null, true, true, $imageSize).$delHtml .'&nbsp;&nbsp;';
                            //if(!in_array($iVal['imageKind'],['detail','magnify']))  $imageNo = 1;
                            // else  $imageNo = $iVal['imageNo'];
                            echo '	$(\'#image' . ucfirst($iVal['imageKind']) . 'URL' . $imageNo . '\').val(\'' . gd_htmlspecialchars_slashes($iVal['imageName'], 'add') . '\');' . chr(10);
                            echo '	$(\'#image' . ucfirst($iVal['imageKind']) . 'PreView' . $imageNo . '\').html(\' ' . gd_htmlspecialchars_slashes($preViewImg, 'add'). ' : 가로 ' . $imageWidth . 'pixel(픽셀))\');'  . chr(10);

                        } else {
                            $iVal['imageName'] = gd_htmlspecialchars_stripslashes(gd_htmlspecialchars_slashes($iVal['imageName'], 'add'));
                            $delHtml = '&nbsp;&nbsp;<label class="checkbox-inline"><input type="checkbox" name="imageDB[imageDelFl]['.$iVal['imageKind'] . $iVal['imageNo'].']" value="y">삭제</label>';
                            $preViewImg = $imageInput . gd_html_preview_image($iVal['imageName'], $data['imagePath'], $data['imageStorage'], 25, 'goods', null, null, true, true, $imageSize) .$delHtml.'&nbsp;&nbsp;'. gd_htmlspecialchars_stripslashes(gd_htmlspecialchars_slashes($iVal['imageName'], 'add'));
                            echo '	$(\'#image' . ucfirst($iVal['imageKind']) . 'PreView' . $imageNo . '\').html(\' ' . gd_htmlspecialchars_slashes($preViewImg, 'add') . ' : 가로 ' . $imageWidth . 'pixel(픽셀)&nbsp;&nbsp;\');' . chr(10);
                        }
                        $imageNo++;
                    }
                }
                if ($imageAddUrlFl == 'y') echo "$('input[name=imageAddUrl]').click();" . chr(10);
                echo '//-->' . chr(10) . '</script>' . chr(10);
            }
            ?>
        </div>
    </div>

    <div class="table-title gd-help-manual" id="goodsDetail">
        상품 상세 설명
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="goodsDetail"><span>닫힘</span></button></span>
    </div>

    <input type="hidden" id="depth-toggle-hidden-goodsDetail" value="<?=$toggle['goodsDetail_'.$SessScmNo]?>">
    <div id="depth-toggle-line-goodsDetail" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-goodsDetail">
        <div class="mgb20">
            <div class="notice-info">
                <span class="text-danger">모든 이미지파일의 외부링크 (옥션, G마켓 등의 오픈마켓 포함)는 지원되지 않습니다.</span><br/> G마켓, 11번가, 옥션 등의 오픈마켓 판매를 위한 이미지는 고도호스팅의 <a href="http://hosting.godo.co.kr/imghosting/imghosting_info.php" class="btn-link-underline" target="_blank">이미지호스팅 서비스</a>를 이용해 주시기 바랍니다.
            </div>

            <table class="table table-cols">
                <colgroup>
                    <col class="width-lg"/>
                    <col />
                    <tr>
                        <th>짧은 설명</th>
                        <td colspan="3">
                            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>기준몰</th>
                    <td>
                        <label title="" class="label-width"><input type="text" name="shortDescription" value="<?=gd_isset($data['shortDescription']); ?>"
                                                                   class="form-control input-width js-maxlength" maxlength="250"/></label>
                    </td>
                </tr>
                <tbody class="js-global-name">
                <?php
                foreach ($gGlobal['useMallList'] as $val) {
                    if ($val['standardFl'] == 'n') {
                        ?>
                        <tr>
                            <th>
                                <span class="js-popover flag flag-16 flag-<?= $val['domainFl']?>" data-content="<?=$val['mallName']?>"></span>
                            </th>
                            <td>
                                <input type="text" name="globalData[<?= $val['sno'] ?>][shortDescription]" value="<?= $data['globalData'][$val['sno']]['shortDescription']; ?>" class="form-control input-width js-maxlength" maxlength="250" <?php if(empty($data['globalData'][ $val['sno'] ]['shortDescription'])) { ?>disabled="disabled" <?php } ?> data-global=''/>
                                <div>
                                    <label class="checkbox-inline label-width">
                                        <input type="checkbox" name="shortDescriptionFl[<?= $val['sno'] ?>]" value="y" <?= gd_isset($checked['shortDescriptionFl'][$val['sno']]); ?>> 기준몰 기본 상품명 공통사용
                                    </label>
                                    <a class="btn btn-sm btn-black js-translate-google" data-language="<?= $val['domainFl'] ?>" data-target-name="shortDescription">참고 번역</a>
                                </div>
                            </td>
                        </tr>
                    <?php }
                }?>
                </tbody>
            </table>

            </td>
            </tr>

            <tr>
                <th>이벤트문구</th>
                <td colspan="3">
                    <label title="상품상세 페이지나 퀵뷰시의 간략한 설명에 사용이 됩니다." class="label-width">
                        <input type="text" name="eventDescription" rows="3" class="form-control input-width js-maxlength" maxlength="250" value="<?=$data['eventDescription']; ?>"/>
                        <div class="notice-info">
                            마케팅 제휴서비스 (네이버 쇼핑, 다음 쇼핑하우) 이용 시 공통으로 사용되는 항목입니다.<br/>
                            <?php if (gd_is_provider() === false) { ?>
                                <a href="/marketing/naver_config.php" target="_blank" class="btn-link">네이버 쇼핑 설정 바로가기</a><br/>
                                "마케팅>네이버쇼핑 설정>네이버쇼핑 이벤트 문구 설정>상품별 문구 사용" 설정 후 사용하세요.<br/>
                                이벤트 문구(공통문구+상품별 문구)는 최대 100자까지 입력 가능합니다.<br/><br/>

                                <a href="/marketing/daumcpc_config.php" target="_blank" class="btn-link">다음 쇼핑하우 설정 바로가기</a><br/>
                                "마케팅>다음 쇼핑하우" 신청 후 사용가능하며, 쇼핑하우 상품 목록에 상품 정보와 함께 노출됩니다.<br/>
                            <?php } ?>
                        </div>
                </td>
            </tr>
            </table>

            <div class="desc_box">
                <?php if (gd_isset($conf['mobile']['mobileShopFl']) == 'y') { ?>
                    <ul class="nav nav-tabs nav-tabs-sm">
                        <li class="active display-inline" id="btnDescriptionShop">
                            <a href="#textareaDescriptionShop">PC쇼핑몰 상세 설명</a></li>
                        <li class="nav-none display-inline" id="btnDescriptionMobile">
                            <a href="#textareaDescriptionMobile" style="background:#F6F6F6">모바일쇼핑몰 상세 설명</a></li>
                        <li style="padding-left:10px;padding-top:5px"> <label class="checkbox-inline"><input type="checkbox" value="y"  <?=gd_isset($checked['goodsDescriptionSameFl']['y']); ?> name="goodsDescriptionSameFl" /> PC/모바일 상세설명 동일사용</label></li>
                    </ul>

                <?php } ?>
                <div id="textareaDescriptionShop">
                    <textarea name="goodsDescription" rows="3" style="width:100%; height:400px;" id="editor" class="form-control"><?=$data['goodsDescription']; ?></textarea>
                </div>
                <div id="textareaDescriptionMobile">
                    <textarea name="goodsDescriptionMobile" rows="3" style="width:100%; height:400px;" id="editor2" class="form-control"><?=$data['goodsDescriptionMobile']; ?></textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        배송비
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="delivery"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-delivery" value="<?=$toggle['delivery_'.$SessScmNo]?>">
    <div id="depth-toggle-line-delivery" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-delivery">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>배송비 선택</th>
                <td>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('delivery', 'radio')">배송비 선택</button>
                    </label>
                    <span id="deliveryLayer" class="width100p">
                            <span class="btn-group btn-group-xs">
							<input type="hidden" name="deliverySno" value="<?= $data['deliverySno'] ?>"/>
					        </span> <b>선택된 배송비 :</b> <span class="deliverySnoNm"></span>
                    </span>
                    <p class="notice-info">
                        배송비는 <a href="<?php if (gd_is_provider() === true) { ?>/provider<?php } ?>/policy/delivery_config.php" target="_blank" class="btn-link
                        ">기본설정>배송 정책>배송비조건 관리</a>에서 추가할 수 있습니다.
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual" id="relation">
        관련상품
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="relation"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-relation" value="<?=$toggle['relation_'.$SessScmNo]?>">
    <div id="depth-toggle-line-relation" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-relation">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>관련상품 설정</th>
                <td>
                    <label class="radio-inline" title="관련상품을 설정하지 않거나 없는 경우 선택해 주세요!">
                        <input type="radio" name="relationFl" value="n" onclick="relation_switch(this.value);" <?=gd_isset($checked['relationFl']['n']); ?> />사용안함
                    </label>
                    <label class="radio-inline" title="대표카테고리내 상품을 자동으로 출력을 할때 선택해 주세요!">
                        <input type="radio" name="relationFl" value="a" onclick="relation_switch(this.value);" <?=gd_isset($checked['relationFl']['a']); ?> />자동(같은 카테고리 상품이 무작위로 보여짐)
                    </label>
                    <label class="radio-inline" title="직접 상품을 선택할 경우 선택해 주세요!">
                        <input type="radio" name="relationFl" value="m" onclick="relation_switch(this.value);" <?=gd_isset($checked['relationFl']['m']); ?> />수동(아래 직접 선택등록)
                    </label>
                </td>
            </tr>
            <tr class="relationSet" style="display:none">
                <th>서로등록</th>
                <td>
                    <div class="radio">
                        <label class="radio-inline">
                            <input type="radio" name="relationSameFl" onclick="setDsiabledRelatedGoods(this.value)" value="n" <?=gd_isset($checked['relationSameFl']['n']); ?> />사용안함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="relationSameFl" onclick="setDsiabledRelatedGoods(this.value)" value="y" <?=gd_isset($checked['relationSameFl']['y']); ?> />전체상품 사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="relationSameFl" onclick="setDsiabledRelatedGoods(this.value)" value="s" <?=gd_isset($checked['relationSameFl']['s']); ?> />선택상품 사용함
                        </label>
                    </div>
                    <p class="notice-info">
                        사용함 : 본 상품이 서로등록 상품과 관련상품으로 동시에 등록됩니다. 삭제 시 양쪽모두 자동으로 관련상품 목록에서 제외됩니다.<br/> 사용안함 : 본 상품이 관련상품으로 서로등록 되지 않으며, 본 상품의 관련상품 목록에만 등록됩니다.<br/> 관련상품 노출방식을 "자동" 으로 설정할 경우, 서로등록과 상관없이 무조건 같은 카테고리의 상품이 랜덤으로 보여집니다.<br/> 관련상품 노출형태 설정은 "상품관리>상품노출형태관리>관련상품 노출 설정" 에서 하실 수 있습니다.
                        <br/>
                    </p>

                </td>
            </tr>
            <tr class="relationSet" style="display:none">
                <th>관련상품</th>
                <td>
                    현재 관련 상품 개수 :
                    <span id="relationGoodsCnt"><?= is_array($data['relationGoodsNo']) ? count($data['relationGoodsNo']) : 0; ?></span>개
                    <button type="button" class="btn btn-gray btn-xs" onclick="goods_search_popup();">상품 선택</button>
                    <button type="button" class="btn btn-gray btn-xs" onclick="delete_relation('','');">초기화</button>
                    <table class="table table-rows table-fixed" id="relationGoodsInfo">
                        <thead>
                        <tr>
                            <th class="width5p"><input type="checkbox" id="allCheck2" value="y" onclick="all_checkbox(this.id, 'itemGoodsNo');"/></th>
                            <th class="width5p">진열순서</th>
                            <th class="width5p">이미지</th>
                            <th class="width15p">상품명</th>
                            <th class="width10p">판매가</th>
                            <th class="width10p">공급사</th>
                            <th class="width5p">재고</th>
                            <th class="width10p">관련상품 노출기간</th>
                            <th class="width10p">등록일</th>
                            <th class="width5p">품절상태</th>
                            <th class="width10p">서로등록</th>
                        </tr>
                        </thead>
                        <tbody id="relationGoodsList">
                        <?php
                        if ($data['relationFl'] == 'm' && is_array($data['relationGoodsNo'])) {
                            //$cnt = count($data['relationGoodsNo']);
                            $cnt = 1;
                            foreach ($data['relationGoodsNo'] as $key => $val) {
                                if ($val['imageStorage'] != 'url' && !empty($val['imageName'])) {
                                    $val['imageName'] = $val['imageName'];
                                }

                                list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);

                                $data['relationGoodsDate'][$val['goodsNo']]['startYmd'] = ($data['relationGoodsDate'][$val['goodsNo']]['startYmd'] == null || $data['relationGoodsDate'][$val['goodsNo']]['startYmd'] == 'undefined') ? '' : $data['relationGoodsDate'][$val['goodsNo']]['startYmd'];
                                $data['relationGoodsDate'][$val['goodsNo']]['endYmd'] = ($data['relationGoodsDate'][$val['goodsNo']]['endYmd'] == null || $data['relationGoodsDate'][$val['goodsNo']]['endYmd'] == 'undefined') ? '' : $data['relationGoodsDate'][$val['goodsNo']]['endYmd'];

                                //서로등록
                                if ($data['relationGoodsEach'][$key] == 'y') {
                                    $relationGoodsEach = 'y';
                                } else {
                                    $relationGoodsEach = 'n';
                                }

                                ?>
                                <tr id="tbl_add_goods_<?= $val['goodsNo'] ?>" class="add_goods_free">
                                    <td class="center">
                                        <input type="hidden" name="itemGoodsNm[]" value="<?= strip_tags($val['goodsNm']) ?>"/>
                                        <input type="hidden" name="itemGoodsPrice[]" value="<?=gd_currency_display($val['goodsPrice']) ?>"/>
                                        <input type="hidden" name="itemScmNm[]" value="<?= $val['scmNm'] ?>"/>
                                        <input type="hidden" name="itemTotalStock[]" value="<?= $val['totalStock'] ?>"/>
                                        <input type="hidden" name="itemImage[]" value="<?= rawurlencode(gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>"/>
                                        <input type="hidden" name="itemBrandNm[]" value="<?= gd_isset($val['brandNm']) ?>"/>
                                        <input type="hidden" name="itemMakerNm[]" value="<?= gd_isset($val['makerNm']) ?>"/>
                                        <input type="hidden" name="itemSoldOutFl[]" value="<?= gd_isset($val['soldOutFl']) ?>"/>
                                        <input type="hidden" name="itemStockFl[]" value="<?= gd_isset($val['stockFl']) ?>"/>
                                        <input type="hidden" name="itemIcon[]" value="<?=rawurlencode(gd_isset($val['goodsIcon'])); ?>" />
                                        <input type="hidden" name="regDt[]" value="<?=gd_date_format('Y-m-d', gd_isset($val['regDt']))?>" />
                                        <input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?=$val['goodsNo']; ?>" value="<?= $val['goodsNo'] ?>"><input type="hidden" name="relationGoodsNo[]" value="<?= $val['goodsNo'] ?>"/>
                                        <input type="hidden" id="relationGoodsEach_<?=$val['goodsNo']; ?>" name="relationGoodsEach[]" value="<?= $relationGoodsEach ?>" />
                                    </td>
                                    <td class="center number addGoodsNumber_<?=$val['goodsNo']; ?>"><?=$cnt++?></td>
                                    <td class="center"><?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank') ?></td>
                                    <td>
                                        <?=gd_htmlspecialchars_decode($val['goodsNm']); ?>
                                        <input type="hidden" name="goodsNoData[]" value="<?= $val['goodsNo'] ?>"/>
                                        <input type="checkbox" name="sortFix[]" class="layer_sort_fix_<?=$val['goodsNo']; ?>" value="<?=$val['goodsNo']; ?>" <?php if ($data['fixGoodsNo'] && in_array($val['goodsNo'], $data['fixGoodsNo'])) {
                                            echo "checked='true'";
                                        } ?> style="display:none">
                                    </td>
                                    <td class="center"><?=number_format($val['goodsPrice']);?>원</td>
                                    <td class="center"><?=$val['scmNm']; ?></td>
                                    <td class="center"><?= $totalStock ?></td>
                                    <td class="center js-goodschoice-hide" id="relationGoodsDate_<?= $val['goodsNo'] ?>">
                                        <input type="hidden" name="relationGoodsNoStartYmd[]" id="relationGoodsStartYmd_<?= $val['goodsNo'] ?>" value="<?= gd_isset($data['relationGoodsDate'][$val['goodsNo']]['startYmd']) ?>"><input type="hidden" name="relationGoodsNoEndYmd[]" id="relationGoodsEndYmd_<?= $val['goodsNo'] ?>" value="<?= $data['relationGoodsDate'][$val['goodsNo']]['endYmd'] ?>"><span id="relationGoodsDateText_<?= $val['goodsNo'] ?>">
                                        <?php if ($data['relationGoodsDate'][$val['goodsNo']]['startYmd'] && $data['relationGoodsDate'][$val['goodsNo']]['endYmd']) { ?>
                                            <?= $data['relationGoodsDate'][$val['goodsNo']]['startYmd'] ?> ~ <?= $data['relationGoodsDate'][$val['goodsNo']]['endYmd'] ?>
                                        <?php } else {
                                            echo "지속노출";
                                        } ?>
                                    </span>

                                    </td>
                                    <td class="center js-goodschoice-hide"><?=gd_date_format('Y-m-d', $val['regDt']); ?></td>
                                    <td class="center"><?= $stockText ?></td>
                                    <td class="center js-goodschoice-hide">
                                        <select class="form-control relationEach" name="relationEach[]" data-content="<?=$val['goodsNo']?>">
                                            <option value="y" <?php if ($data['relationGoodsEach'][$key] == 'y') echo 'selected'; ?>>사용함</option>
                                            <option value="n" <?php if ($data['relationGoodsEach'][$key] == 'n') echo 'selected'; ?>>사용안함</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {  ?>
                            <tr id="tbl_add_goods_tr_none"><td colspan="11" class="no-data">선택된 상품이 없습니다.</td></tr>
                        <?php } ?>
                        </tbody>
                    </table>

                    <div class="table-btn clearfix">
                        <div class="pull-left">
                            <button type="button" class="btn btn-sm btn-white btn-icon-minus" onclick="delete_relation('itemGoodsNo[]','tbl_add_goods_')">선택 삭제</button>
                        </div>
                        <div class="pull-right">
                            <button type="button" class="btn btn-sm btn-white" onclick="setRelationGoodsDisplay()">선택상품 기간설정</button>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div id="relationGoodsDisplay" class="display-none">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-sm">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th>노출기간 선택</th>
                    <td>
                        <div class="form-inline" style="padding-top:15px">
                            <label class="radio-inline">
                                <input type="radio" name="relationDataFl" value="y" onclick="display_toggle('relationGoodsDisplayDateLayer','hide');" checked="checked"/>지속 노출
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="relationDataFl" value="n" onclick="display_toggle('relationGoodsDisplayDateLayer','show');"/>기간 노출
                            </label>
                        </div>

                        <br/>
                        <div class="display-none" id="relationGoodsDisplayDate">
                            <span class="bold">시작일 / 종료일</span>
                            <div class="form-inline" style="padding-top:5px;">

                                <div class="input-group js-datetimepicker">
                                    <input type="text" name="relationGoodsDisplayDate[]" class="form-control width-sm" placeholder="수기입력 가능">
                                    <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                                </div>
                                ~
                                <div class="input-group js-datetimepicker">
                                    <input type="text" name="relationGoodsDisplayDate[]" class="form-control width-sm" placeholder="수기입력 가능">
                                    <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                                </div>
                                <div class="btn-group js-dateperiod" data-toggle="buttons" data-target-name="relationGoodsDisplayDate" data-target-inverse="re" style="padding-top:5px;">
                                    <label class="btn btn-white btn-sm"><input type="radio" value="0">오늘</label>
                                    <label class="btn btn-gray btn-sm active"><input type="radio" value="6">7일</label>
                                    <label class="btn btn-white btn-sm"><input type="radio" value="14">15일</label>
                                    <label class="btn btn-white btn-sm"><input type="radio" value="29">1개월</label>
                                    <label class="btn btn-white btn-sm"><input type="radio" value="89">3개월</label>
                                    <label class="btn btn-white btn-sm">
                                        <input type="radio" value="364" checked="checked">1년
                                    </label>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>

    <div class="table-title gd-help-manual" id="icon">
        아이콘
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="icon"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-icon" value="<?=$toggle['icon_'.$SessScmNo]?>">
    <div id="depth-toggle-line-icon" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-icon">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>기간제한용 <span class="tip"><span>설정된 기간에만 체크된 아이콘이 노출이 됩니다!</span></span></th>
                <td>

                    <div class="form-inline">시작일 / 종료일
                        <label title="아이콘 기간 제한용 시작일을 선택/작성(yyyy-mm-dd)해 주세요!">
                            <div class="form-inline">
                                <div class="input-group js-datetimepicker">
                                    <input type="text" name="goodsIconStartYmd" class="form-control width-sm" value="<?=$data['goodsIconStartYmd']; ?>" placeholder="수기입력 가능">
                                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                                </div>
                            </div>
                        </label>
                        ~
                        <label title="아이콘 기간 제한용 유효일자 종료일을 선택/작성(yyyy-mm-dd)해 주세요!">
                            <div class="form-inline">
                                <div class="input-group js-datetimepicker endDatetime">
                                    <input type="text" name="goodsIconEndYmd" class="form-control width-sm" value="<?=$data['goodsIconEndYmd']; ?>" placeholder="수기입력 가능">
                                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <?php
                    foreach ($data['icon'] as $key => $val) {
                        if ($val['iconPeriodFl'] == 'y') {
                            echo '<label class="nobr checkbox-inline"><input type="checkbox" name="goodsIconCdPeriod[]" value="' . $val['iconCd'] . '" ' . gd_isset($checked['goodsIconCdPeriod'][$val['iconCd']]) . ' /> ' . gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']) . '</label>' . chr(10);
                        }
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th>무제한용 <span class="tip"><span>체크된 아이콘은 현 상품에 무조건 노출이 됩니다!</span></span></th>
                <td>
                    <?php
                    foreach ($data['icon'] as $key => $val) {
                        if ($val['iconPeriodFl'] == 'n') {
                            echo '<label class="nobr checkbox-inline"><input type="checkbox" name="goodsIconCd[]" value="' . $val['iconCd'] . '" ' . gd_isset($checked['goodsIconCd'][$val['iconCd']]) . ' /> ' . gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']) . '</label>' . chr(10);
                        }
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        상품이미지 돋보기
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="detailView"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-detailView" value="<?=$toggle['detailView_'.$SessScmNo]?>">
    <div id="depth-toggle-line-detailView" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-detailView">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>사용상태</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="imgDetailViewFl" value="y" onclick="display_toggle('imgDetailViewDesc','show');" <?=gd_isset($checked['imgDetailViewFl']['y']); ?>>사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="imgDetailViewFl" value="n" onclick="display_toggle('imgDetailViewDesc','hide');" <?=gd_isset($checked['imgDetailViewFl']['n']); ?>>사용안함
                    </label>
                    <div id="imgDetailViewDesc" class="desc_box display-none">
                        <p class="notice-info">
                            [상품이미지 돋보기] 기능을 사용하기 위해서는 상품이미지 등록 시 <span style="color:red">상세 이미지</span>에 큰 사이즈의 이미지를 넣어야 합니다. <span style="color:red">(500px~800px 권장)</span><br/>
                            상세 이미지를 넣으면 자동으로 상세 이미지와 마우스오버 시 보이는 큰 이미지가 등록됩니다.<br/>
                            원본 이미지를 넣고 [자동리사이즈] 기능을 이용하여 <span style="color:red">상세 이미지를 500px 이하로 등록하면 마우스오버 시 큰 이미지가 정상적으로 보이지 않을수 있습니다.</span>
                        </p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        외부 동영상(<img src="<?=PATH_ADMIN_GD_SHARE?>img/icon_youtube.gif">) 등록
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="externalVideo"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-externalVideo" value="<?=$toggle['externalVideo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-externalVideo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-externalVideo">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>사용상태</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="externalVideoFl" value="y" onclick="display_toggle('useExternalVideoInfo','show');" <?=gd_isset($checked['externalVideoFl']['y']); ?>>사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="externalVideoFl" value="n" onclick="display_toggle('useExternalVideoInfo','hide');" <?=gd_isset($checked['externalVideoFl']['n']); ?>>사용안함
                    </label>
    </div>
    </td>
    </tr>
    </tbody>
    <tbody id="useExternalVideoInfo" class="display-none">
    <tr>
        <th>퍼가기 소스 등록</th>
        <td>
            <input type="text" name="externalVideoUrl" value="<?=$data['externalVideoUrl']; ?>" class="form-control width-2xl">
        </td>
    </tr>
    <tr>
        <th>영상 Size 설정</th>
        <td>

            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                    <col/>
                </colgroup>
                <tr>
                    <th>
                        <label><input type="radio" name="externalVideoSizeFl" value="y" <?=gd_isset($checked['externalVideoSizeFl']['y']); ?>>기본
                        </label>

                    </th>
                    <td>너비 (Width) : 640</td>
                    <td>높이 (Height) : 360</td>
                </tr>
                <tr>
                    <th>
                        <label><input type="radio" name="externalVideoSizeFl" value="n" <?=gd_isset($checked['externalVideoSizeFl']['n']); ?>>사용자 Size</label>
                    </th>
                    <td>
                        <div class="form-inline">너비 (Width) :
                            <input type="text" name="externalVideoWidth" value="<?=$data['externalVideoWidth']; ?>" class="form-control width-sm">
                        </div>
                    </td>
                    <td>
                        <div class="form-inline">높이 (Height) :
                            <input type="text" name="externalVideoHeight" value="<?=$data['externalVideoHeight']; ?>" class="form-control width-sm">
                        </div>
                    </td>
                </tr>
            </table>

        </td>
    </tr>
    </tbody>
    </table>
    </div>

    <div class="table-title gd-help-manual pos-r" id="detailInfo">
        이용안내
        <span class="notice-info" style="position: absolute;left: 90px;top: 5px;">
            이용안내는 <a href="<?php if (gd_is_provider() === true) { ?>/provider<?php } ?>/policy/goods_detail_info.php" target="_blank" class="btn-link" style="text-decoration: underline;">기본설정>상품 정책>상품 상세 이용안내 관리</a>에서 추가할 수 있습니다.
            <?php if ($gGlobal['isUse'] === true) { ?>
                (해외몰 쇼핑몰화면의 경우, [직접입력],[선택입력] 항목과 상관없이 "<a href="/policy/goods_detail_info_global_register.php" target="_blank" class="btn-link" style="text-decoration: underline;">해외몰 적용 이용안내</a>"가  대체되어 노출됩니다.)
            <?php } ?>
        </span>
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="detailInfo"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-detailInfo" value="<?=$toggle['detailInfo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-detailInfo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-detailInfo">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>배송안내 선택</th>
                <td>
                    <div class="form-inline">
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoDeliveryFl" value="no" <?=gd_isset($checked['detailInfoDeliveryFl']['no']); ?>>사용안함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoDeliveryFl" value="direct" <?=gd_isset($checked['detailInfoDeliveryFl']['direct']); ?>>직접입력
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoDeliveryFl" value="selection" <?=gd_isset($checked['detailInfoDeliveryFl']['selection']); ?>>선택입력
                            </label>
                            <span id="detailInfoDeliveryLayer" class="width100p">
                                <label>
                                    <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('detail_info_delivery')">선택</button>
                                </label>
							    <input type="hidden" name="detailInfoDelivery" value="<?=$data['detailInfoDelivery']; ?>" />
					            <b>선택된 배송안내 :</b> <span id="detailInfoDeliveryInformNm"><?=$data['detailInfoDeliveryInformNm']; ?></span>
                            </span>
                        </div>
                        <div id="detailInfoDeliveryDirect" class="mgt10 mgb10 ">
                            <textarea name="detailInfoDeliveryDirectInput" rows="3" style="width:100%; height:400px;" id="detailInfoDeliveryDirectInput" class="form-control"><?=$data['detailInfoDeliveryDirectInput']; ?></textarea>
                        </div>
                        <div id="detailInfoDeliverySelection" class="mgt10 mgb10 ">
                            <textarea name="detailInfoDeliverySelectionInput" rows="3" style="width:100%; height:400px;" id="detailInfoDeliverySelectionInput" class="form-control"><?=$data['detailInfoDeliveryInformContent']; ?></textarea>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>AS안내 선택</th>
                <td>
                    <div class="form-inline">
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoASFl" value="no" <?=gd_isset($checked['detailInfoASFl']['no']); ?>>사용안함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoASFl" value="direct" <?=gd_isset($checked['detailInfoASFl']['direct']); ?>>직접입력
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoASFl" value="selection" <?=gd_isset($checked['detailInfoASFl']['selection']); ?>>선택입력
                            </label>
                            <span id="detailInfoASLayer" class="width100p">
                                 <label>
                                     <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('detail_info_as')">선택</button>
                                 </label>
                                <input type="hidden" name="detailInfoAS" value="<?=$data['detailInfoAS']; ?>" />
                                <b>선택된 AS안내 :</b> <span id="detailInfoASInformNm"><?=$data['detailInfoASInformNm']; ?></span>
                            </span>
                        </div>
                        <div id="detailInfoASDirect" class="mgt10 mgb10 ">
                            <textarea name="detailInfoASDirectInput" rows="3" style="width:100%; height:400px;" id="detailInfoASDirectInput" class="form-control"><?=$data['detailInfoASDirectInput']; ?></textarea>
                        </div>
                        <div id="detailInfoASSelection" class="mgt10 mgb10 ">
                            <textarea name="detailInfoASSelectionInput" rows="3" style="width:100%; height:400px;" id="detailInfoASSelectionInput" class="form-control"><?=$data['detailInfoASInformContent']; ?></textarea>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>환불안내 선택</th>
                <td>
                    <div class="form-inline">
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoRefundFl" value="no" <?=gd_isset($checked['detailInfoRefundFl']['no']); ?>>사용안함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoRefundFl" value="direct" <?=gd_isset($checked['detailInfoRefundFl']['direct']); ?>>직접입력
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoRefundFl" value="selection" <?=gd_isset($checked['detailInfoRefundFl']['selection']); ?>>선택입력
                            </label>
                            <span id="detailInfoRefundLayer" class="width100p">
                                 <label>
                                     <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('detail_info_refund')">선택</button>
                                 </label>
							    <input type="hidden" name="detailInfoRefund" value="<?=$data['detailInfoRefund']; ?>" />
					            <b>선택된 환불안내 :</b> <span id="detailInfoRefundInformNm"><?=$data['detailInfoRefundInformNm']; ?></span>
                            </span>
                        </div>
                        <div id="detailInfoRefundDirect" class="mgt10 mgb10 ">
                            <textarea name="detailInfoRefundDirectInput" rows="3" style="width:100%; height:400px;" id="detailInfoRefundDirectInput" class="form-control"><?=$data['detailInfoRefundDirectInput']; ?></textarea>
                        </div>
                        <div id="detailInfoRefundSelection" class="mgt10 mgb10 ">
                            <textarea name="detailInfoRefundSelectionInput" rows="3" style="width:100%; height:400px;" id="detailInfoRefundSelectionInput" class="form-control"><?=$data['detailInfoRefundInformContent']; ?></textarea>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>교환안내 선택</th>
                <td>
                    <div class="form-inline">
                        <div>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoExchangeFl" value="no" <?=gd_isset($checked['detailInfoExchangeFl']['no']); ?> />사용안함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoExchangeFl" value="direct" <?=gd_isset($checked['detailInfoExchangeFl']['direct']); ?> />직접입력
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="detailInfoExchangeFl" value="selection" <?=gd_isset($checked['detailInfoExchangeFl']['selection']); ?> />선택입력
                            </label>
                            <span id="detailInfoExchangeLayer" class="width100p">
                                 <label>
                                     <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('detail_info_exchange')">선택</button>
                                 </label>
                                 <input type="hidden" name="detailInfoExchange" value="<?=$data['detailInfoExchange']; ?>" />
                                 <b>선택된 교환안내 :</b> <span id="detailInfoExchangeInformNm"><?=$data['detailInfoExchangeInformNm']; ?></span>
                            </span>
                        </div>
                        <div id="detailInfoExchangeDirect" class="mgt10 mgb10 ">
                            <textarea name="detailInfoExchangeDirectInput" rows="3" style="width:100%; height:400px;" id="detailInfoExchangeDirectInput" class="form-control"><?=$data['detailInfoExchangeDirectInput']; ?></textarea>
                        </div>
                        <div id="detailInfoExchangeSelection" class="mgt10 mgb10 ">
                            <textarea name="detailInfoExchangeSelectionInput" rows="3" style="width:100%; height:400px;" id="detailInfoExchangeSelectionInput" class="form-control"><?=$data['detailInfoExchangeInformContent']; ?></textarea>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual" id="seoTag">
        상품 개별 SEO 태그 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="seoTag"><span>닫힘</span></button></span>
        <div class="pull-right form-inline">
            <button type="button" class="btn btn-sm btn-gray js-code-view" data-target="goods">치환코드 보기</button>
        </div>

    </div>
    <?php include($seoTagFrm); ?>
    <div class="table-title gd-help-manual">
        다음 쇼핑하우 EP 정보 등록
        <span class="depth-toggle">
            <button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="memo"><span>닫힘</span></button>
        </span>
    </div>
    <div id="depth-toggle-layer-daumEp">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>쇼핑하우 노출여부</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="daumFl" value="y" <?=gd_isset($checked['daumFl']['y']); ?>>노출함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="daumFl" value="n" <?=gd_isset($checked['daumFl']['n']); ?>>노출안함
                    </label>
                </td>
            </tr>
        </table>
    </div>
    <div class="table-title gd-help-manual">
        네이버쇼핑 EP 3.0 정보 등록
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="naverEp"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-naverEp" value="<?=$toggle['naverEp_'.$SessScmNo]?>">
    <div id="depth-toggle-line-naverEp" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-naverEp">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg">
                <col class="width-2xl">
                <col class="width-md">
                <col>
            </colgroup>
            <tr>
                <th>네이버쇼핑 노출여부</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="naverFl" value="y" <?=gd_isset($checked['naverFl']['y']); ?>>노출함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="naverFl" value="n" <?=gd_isset($checked['naverFl']['n']); ?>>노출안함
                    </label>
                </td>
            </tr>
            <tr>
                <th>수입 및 제작 여부</th>
                <td>
                    <select class="form-control" name="naverImportFlag">
                        <option value="">선택</option>
                        <?php foreach ($goodsImportType as $k => $v) { ?>
                            <option value="<?= $k ?>" <?php if ($k == $data['naverImportFlag']) {
                                echo "selected";
                            } ?> ><?= $v ?></option>
                        <?php } ?>
                    </select>
                </td>
                <th>판매방식 구분</th>
                <td>
                    <select class="form-control" name="naverProductFlag">
                        <option value="">선택</option>
                        <?php foreach ($goodsSellType as $k => $v) { ?>
                            <option value="<?= $k ?>" <?php if ($k == $data['naverProductFlag']) {
                                echo "selected";
                            } ?> ><?= $v ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>주요 사용 연령대</th>
                <td>
                    <?php foreach ($goodsAgeType as $k => $v) {
                        echo "<label class='radio-inline'><input type='radio' name='naverAgeGroup' value='" . $k . "' " . gd_isset($checked['naverAgeGroup'][$k]) . ">" . $v . "</label>";
                    } ?>
                </td>
                <th>주요 사용 성별</th>
                <td>
                    <select class="form-control" name="naverGender">
                        <option value="">선택</option>
                        <?php foreach ($goodsGenderType as $k => $v) { ?>
                            <option value="<?= $k ?>" <?php if ($k == $data['naverGender']) {
                                echo "selected";
                            } ?> ><?= $v ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>속성 정보</th>
                <td colspan="3">
                    <label class="label-width">
                        <input type="text" name="naverAttribute" value="<?=$data['naverAttribute']; ?>" class="form-control input-width js-maxlength" maxlength="500"/>
                    </label>
                    <p class="notice-info">상품의 속성정보를 ^로 구분하여 입력합니다.  예) 서울^1개^오션뷰</p>
                </td>
            </tr>
            <tr>
                <th>검색 태그</th>
                <td colspan="3">
                    <label class="label-width">
                        <input type="text" name="naverTag" value="<?=$data['naverTag']; ?>" class="form-control input-width js-maxlength" maxlength="100"/>
                    </label>
                    <p class="notice-info">상품의 검색태그를 |(vertical bar)로 구분하여 입력합니다.10개까지 입력 가능하며 10개가 넘는 경우 10개까지만 처리됩니다.  예) 물방울패턴원피스|2016 S/S신상|결혼식 아이템|여친룩</p>
                </td>
            </tr>
            <tr>
                <th>네이버 카테고리 ID</th>
                <td colspan="3">
                    <label>
                        <input type="text" name="naverCategory" value="<?=$data['naverCategory']; ?>" class="form-control width-md js-number js-maxlength" maxlength="8"/>
                    </label>
                    <p class="notice-info">네이버쇼핑 카테고리에 매칭할 수 있는 정보입니다. 카테고리 ID 정보는 <a class="btn-link" href="https://adcenter.shopping.naver.com/main.nhn" target="_blank">네이버쇼핑 쇼핑파트너존</a>에서 다운로드할 수 있습니다.</p>
                </td>
            </tr>
            <tr>
                <th>가격비교 페이지 ID</th>
                <td colspan="3">
                    <label>
                        <input type="text" name="naverProductId" value="<?=$data['naverProductId']; ?>" class="form-control width-md js-maxlength" maxlength="50"/>
                    </label>
                    <p class="notice-info">입력 시 네이버쇼핑에서 가격비교 추천상태로 변경됩니다.<br/>
                        ID확인 예) http://shopping.naver.com/detail/detail.nhn?nv_mid=<span class="text-danger">8535546055</span>&cat_id=50000151</p>
                </td>
            </tr>
        </table>
    </div>
    <?php if ($paycoFl == 'y') {?>
        <div class="table-title gd-help-manual">
            페이코쇼핑 설정
            <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="paycoShopping"><span>닫힘</span></button></span>
        </div>
        <input type="hidden" id="depth-toggle-hidden-paycoShopping" value="<?=$toggle['paycoShopping_'.$SessScmNo]?>">
        <div id="depth-toggle-line-paycoShopping" class="depth-toggle-line display-none"></div>
        <div id="depth-toggle-layer-paycoShopping">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-lg">
                    <col class="width-2xl">
                    <col class="width-md">
                    <col>
                </colgroup>
                <tr>
                    <th>페이코쇼핑 노출여부</th>
                    <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="paycoFl" value="y" <?=gd_isset($checked['paycoFl']['y']); ?>>노출함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="paycoFl" value="n" <?=gd_isset($checked['paycoFl']['n']); ?>>노출안함
                        </label>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
    <div class="table-title gd-help-manual">
        페이스북 광고 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="facebookDa"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-facebookDa" value="<?=$toggle['facebookDa_'.$SessScmNo]?>">
    <div id="depth-toggle-line-facebookDa" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-facebookDa">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg">
                <col class="width-2xl">
                <col class="width-md">
                <col>
            </colgroup>
            <tr>
                <th>페이스북 제품 피드 설정</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="fbUseFl" value="n" <?=gd_isset($checked['fbUseFl']['n']); ?>>피드 생성 안함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="fbUseFl" value="y" <?=gd_isset($checked['fbUseFl']['y']); ?>>피드 생성
                    </label>
                    <div class="notice-info">
                        피드 생성으로 설정을 하면 페이스북 제품 피드 다운로드 시 해당 상품의 피드를 생성 및 다운로드 하실 수 있습니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th>페이스북 광고 제품 피드 이미지</th>
                <td colspan="3">

                    <div id="facebookImg">
                        <div class="mgt5 mgb5">
                            <label class="checkbox-inline">
                                <input type="checkbox" name="imageFbAddUrl" value="y" onclick="facebook_image_add_url();"/> URL 직접입력 추가사용
                            </label>
                        </div>
                        <div id="imageFbGoods" class="form-inline img-attch-space"></div>
                        <script type="text/javascript">goods_image('imageFbGoods', 'y');</script>
                    </div>
                    <div id="facebookUrl">
                        <div id="imageFbGoodsURL" class="form-inline img-attch-space"></div>
                        <script type="text/javascript">
                            goods_image('imageFbGoodsURL', 'y', 'y');
                        </script>
                    </div>
                    <?php
                    if(gd_isset($fbGoodsImage)) {
                        $imageNo = 0;
                        echo '<script type="text/javascript">' . chr(10) . '<!--' . chr(10);
                        $imageFbAddUrlFl = "n";
                        foreach ($fbGoodsImage as $key => $iVal) {
                            if ($data['imageStorage'] == 'url') {
                                $imageInput .= '<input type="hidden" name="imageFbDB[imageUrlFl][' . $iVal . ']" value="' . $iVal . '" /> ';
                                if($key > 0) {
                                    echo 'goods_image(\'imageFbGoodsURL\',\'r\',\'y\');' . chr(10);
                                }
                                $delHtml = '&nbsp;&nbsp;<label class="checkbox-inline"><input type="checkbox" name="imageFbDB[imageUrlDelFl][' . $iVal . ']" value="y">삭제</label>';
                                $preViewImg = $imageInput . gd_html_preview_image($iVal, $data['imagePath'], 'url', 25, 'goods', null, null, true, true, $imageSize) . $delHtml . '&nbsp;&nbsp;';

                                echo '	$(\'#imageFbGoodsURLURL' . $imageNo . '\').val(\'' . gd_htmlspecialchars_slashes($iVal, 'add') . '\');' . chr(10);
                                echo '	$(\'#imageFbGoodsURLPreView' . $imageNo . '\').html(\' ' . gd_htmlspecialchars_slashes($preViewImg, 'add') . ' : 가로 ' . $imageWidth . 'pixel(픽셀))\');' . chr(10);
                                $imageNo++;
                            } else {
                                if (strtolower(substr($iVal, 0, 4)) == 'http') {
                                    $imageInput .= '<input type="hidden" name="imageFbDB[imageUrlFl][' . $iVal . ']" value="' . $iVal . '" /> ';
                                } else {
                                    $imageInput = '<input type="hidden" name="imageFbDB[imageName][]" value="' . $iVal . '" /> ';
                                }
                                if ($key == 0 && strtolower(substr($iVal, 0, 4)) == 'http') $imageNo++;
                                if ($key > 0 || strtolower(substr($iVal, 0, 4)) == 'http') {
                                    if (strtolower(substr($iVal, 0, 4)) == 'http') {
                                        $imageFbAddUrlFl = "y";
                                        echo 'goods_image(\'imageFbGoods\',\'r\',\'y\');' . chr(10);
                                    } else {
                                        echo 'goods_image(\'imageFbGoods\',\'r\');' . chr(10);
                                    }
                                }
                                if (strtolower(substr($iVal, 0, 4)) == 'http') {
                                    $delHtml = '&nbsp;&nbsp;<label class="checkbox-inline"><input type="checkbox" name="imageFbDB[imageUrlDelFl][' . $iVal . ']" value="y">삭제</label>';
                                    $addParam = 'onload="get_image_size(this, \'http\');"';
                                    $preViewImg = $imageInput . gd_html_preview_image($iVal, $data['imagePath'], 'url', 25, 'goods', null, $addParam, true, true, $imageSize) . $delHtml . '&nbsp;&nbsp;';

                                    echo '	$(\'#imageFbGoodsURL' . $imageNo . '\').val(\'' . gd_htmlspecialchars_slashes($iVal, 'add') . '\');' . chr(10);
                                    echo '	$(\'#imageFbGoodsPreView' . $imageNo . '\').html(\' ' . gd_htmlspecialchars_slashes($preViewImg, 'add') . ' \');' . chr(10);
                                } else {
                                    $iVal = gd_htmlspecialchars_stripslashes(gd_htmlspecialchars_slashes($iVal, 'add'));
                                    $delHtml = '&nbsp;&nbsp;<label class="checkbox-inline"><input type="checkbox" name="imageFbDB[imageDelFl][' . $iVal . ']" value="y">삭제</label>';
                                    $addParam = 'onload="get_image_size(this, \'local\');"';
                                    $preViewImg = $imageInput . gd_html_preview_image($iVal, $data['imagePath'], $data['imageStorage'], 25, 'goods', null, $addParam, true, true, $imageSize) . $delHtml . '&nbsp;&nbsp;' . gd_htmlspecialchars_stripslashes(gd_htmlspecialchars_slashes($iVal, 'add'));
                                    echo '	$(\'#imageFbGoodsPreView' . $imageNo . '\').html(\' ' . gd_htmlspecialchars_slashes($preViewImg, 'add').'&nbsp;&nbsp;\');' . chr(10);
                                }
                                $imageNo++;
                            }
                        }
                        if ($imageFbAddUrlFl == 'y') echo "$('input[name=imageFbAddUrl]').click();" . chr(10);
                        echo '//-->' . chr(10) . '</script>' . chr(10);
                    }
                    ?>

                    <div class="notice-info">
                        페이스북 광고를 할 경우 제품 피드 이미지를 등록하셔야 합니다. 이미지 사이즈는 600*600px 를 권장합니다.
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        관리자 메모
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="memo"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-memo" value="<?=$toggle['memo_'.$SessScmNo]?>">
    <div id="depth-toggle-line-memo" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-memo">
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg"/>
                <col/>
            </colgroup>
            <tr>
                <th>관리자 메모</th>
                <td>
                    <textarea name="memo" rows="3" class="form-control"><?=$data['memo']; ?></textarea>
                </td>
            </tr>
        </table>
    </div>

    <?php
    if($popupMode == 'yes') {
        ?>
        <div id="gnbAnchor" style="position: fixed; bottom: 25px; right: 25px;">
            <div class="scrollTop" style="display:none;">
                <a href="#top"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/scroll_top_btn.png"></a>
            </div>
            <div class="scrollDown" style="display:block;">
                <a href="#down"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/scroll_down_btn.png"></a>
            </div>
            <?php if (gd_is_provider() && $data['applyFl'] == 'a' && $applyGoodsCopy === false) { ?>
            <?php } else { ?>
            <div class="scrollSave">
                <input type="submit" value="" class="save-btn"/>
            </div>
            <?php } ?>
        </div>
        <?php
    }
    ?>

</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
<script type="text/javascript">

    nhn.husky.EZCreator.createInIFrame({
        oAppRef: oEditors,
        elPlaceHolder: "editor2",
        sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.html",
        htParams: {
            bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
            //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
            fOnBeforeUnload: function () {
                //alert("완료!");
            }
        }, //boolean
        fOnAppLoad: function () {
            //예제 코드
            //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
            $("#textareaDescriptionMobile").hide();
            toggleSelectionDisplay('goodsDetail');
        },
        fCreator: "createSEditor2"
    });

    function editorLoad(detailInfo,flag){

        if(flag == 'direct'){
            editorId = detailInfo + 'DirectInput';
        }else if(flag == 'selection'){
            editorId = detailInfo + 'SelectionInput';
        }else{
            return;
        }

        nhn.husky.EZCreator.createInIFrame({
            oAppRef: oEditors,
            elPlaceHolder: editorId,
            sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.html",
            htParams: {
                bUseToolbar: true,
                bUseVerticalResizer: true,
                bUseModeChanger: true,
            },
            fOnAppLoad: function () {

                if (typeof oEditors.getById['detailInfoDeliverySelectionInput'] != 'undefined') {
                    oEditors.getById['detailInfoDeliverySelectionInput'].exec("DISABLE_ALL_UI");
                    oEditors.getById['detailInfoDeliverySelectionInput'].exec("DISABLE_WYSIWYG");
                }
                if (typeof oEditors.getById['detailInfoASSelectionInput'] != 'undefined') {
                    oEditors.getById['detailInfoASSelectionInput'].exec("DISABLE_ALL_UI");
                    oEditors.getById['detailInfoASSelectionInput'].exec("DISABLE_WYSIWYG");
                }
                if (typeof oEditors.getById['detailInfoRefundSelectionInput'] != 'undefined') {
                    oEditors.getById['detailInfoRefundSelectionInput'].exec("DISABLE_ALL_UI");
                    oEditors.getById['detailInfoRefundSelectionInput'].exec("DISABLE_WYSIWYG");
                }
                if (typeof oEditors.getById['detailInfoExchangeSelectionInput'] != 'undefined') {
                    oEditors.getById['detailInfoExchangeSelectionInput'].exec("DISABLE_ALL_UI");
                    oEditors.getById['detailInfoExchangeSelectionInput'].exec("DISABLE_WYSIWYG");
                }

                infoToggleDisplay(flag,detailInfo);
            },
            fCreator: "createSEditor2"
        });
    }

    function infoToggleDisplay(flag,editorId){
        if(flag == 'direct'){ //직접입력
            $("#" + editorId + "Direct").show();
            $("#" + editorId + "Selection").hide();
            $("#" + editorId + "Layer").hide();

        }else if(flag == 'selection'){ //선택입력
            $("#" + editorId + "Direct").hide();
            $("#" + editorId + "Selection").show();
            $("#" + editorId + "Layer").show();

        }else{ //사용안함
            $("#" + editorId + "Direct").hide();
            $("#" + editorId + "Selection").hide();
            $("#" + editorId + "Layer").hide();
        }
    }

    $('input[name=detailInfoDeliveryFl]').click(function () {
        infoToggleEditor(this.value,'detailInfoDelivery');
    });
    $('input[name=detailInfoASFl]').click(function () {
        infoToggleEditor(this.value,'detailInfoAS');
    });
    $('input[name=detailInfoRefundFl]').click(function () {
        infoToggleEditor(this.value,'detailInfoRefund');
    });
    $('input[name=detailInfoExchangeFl]').click(function () {
        infoToggleEditor(this.value,'detailInfoExchange');
    });

    function infoToggleEditor(flag,detailInfo){
        infoToggleDisplay(flag,detailInfo);
        if(flag == 'direct'){
            if (typeof oEditors.getById[detailInfo+'DirectInput'] == 'undefined') editorLoad(detailInfo,flag);
        }else if(flag == 'selection'){
            if (typeof oEditors.getById[detailInfo+'SelectionInput'] == 'undefined') editorLoad(detailInfo,flag);
        }
    }



    function display_mileage_set() {
        $('div[class^="mileage-set"]').addClass('display-none');

        var mileageFl = $('input[name="mileageFl"]:checked').val();
        var mileageGroup = $('input[name="mileageGroup"]:checked').val();
        if (mileageFl == 'g') {
            $('#mileage_group').removeClass('active').empty();
            $('.js-mileage-group-select').closest('label').hide();
            $('.mileage-set-' + mileageFl + '-' + mileageGroup).removeClass('display-none');
        } else {
            $('.js-mileage-group-select').closest('label').show();
            $('.mileage-set-' + mileageFl).removeClass('display-none');
        }
        display_group_member(mileageGroup, 'mileage_group');
    }

    function display_goods_discount_set() {
        $('div[class^="goods-discount"]').addClass('hide');

        var goodsDiscountGroup = $('input[name="goodsDiscountGroup"]:checked').val();
        switch (goodsDiscountGroup) {
            case 'all':
            case 'member':
                $('.goods-discount-all').removeClass('hide');
                break;
            case 'group':
                $('.goods-discount-group').removeClass('hide');
                break;
        }
    }

    function except_benefit_disabled() {
        var length = $('input[name="exceptBenefit[]"]:checked').length;

        if (length > 0) {
            $('input[name="exceptBenefitGroup"], .js-except-benefit-group-select').prop('disabled', false);
        } else {
            $('input[name="exceptBenefitGroup"], .js-except-benefit-group-select').prop('disabled', true);
        }
    }

    function display_group_member(value, target) {
        if (value == 'all') {
            $('#' + target).empty().removeClass('active');
        } else {
            $('#' + target).addClass('active');
        }
    }

    function set_goods_title(e) {
        var goodsTitle = '구매금액의';
        switch (e.val()) {
            case 'mileage':
            case 'price':
                goodsTitle = '구매수량별';
                break;
        }
        e.closest('.form-inline').find('.goods-title').html(goodsTitle);
    }

    function setDsiabledRelatedGoods(status){
        var boolean = true;
        var selectedIndex = -1;
        if(status == 's'){
            boolean = false;
        }else if(status == 'n'){
            selectedIndex = 1;
        }else if(status == 'y'){
            selectedIndex = 0;
        }
        $('select.relationEach').prop('disabled', boolean);
        if(selectedIndex != -1){
            $('select.relationEach').prop('selectedIndex', selectedIndex);
        }
    }

    /**
     * 관련상품 체크박스 전체 체크
     */
    function all_checkbox(chkID, targetName)
    {
        var checked = false;
        if ($('input[id=\'' + chkID + '\']:checked').length == 1) {
            checked = true;
        }
        $('#relationGoodsInfo input[name*=\'' + targetName + '\']').prop('checked', checked);

        if(targetName == 'itemGoodsNo'){
            if(checked == true)    $('#tbl_add_goods_result tr').css('background-color','#f7f7f7');
            else $('#tbl_add_goods_result tr').css('background-color','');
        }
    }

    /**
     * 관련상품 서로등록 셀렉트박스 변경시
     */
    function set_add_relation_goods(value, goodsNo)
    {
        $('#relationGoodsEach_'+ goodsNo).val(value);
    }

    $(document).ready(function () {
        /* IE에서 에디터가 display:none 상태일때 로드가 안되는 현상 때문에 evnet 이후 editorLoad 되게 처리 */
        if ($('#depth-toggle-hidden-detailInfo').val() != 1) {
            editorLoad('detailInfoDelivery', '<?=$data['detailInfoDeliveryFl']?>');
            editorLoad('detailInfoAS', '<?=$data['detailInfoASFl']?>');
            editorLoad('detailInfoRefund', '<?=$data['detailInfoRefundFl']?>');
            editorLoad('detailInfoExchange', '<?=$data['detailInfoExchangeFl']?>');
            setTimeout(function () {
                infoToggleDisplay('<?=$data['detailInfoDeliveryFl']?>', 'detailInfoDelivery');
                infoToggleDisplay('<?=$data['detailInfoASFl']?>', 'detailInfoAS');
                infoToggleDisplay('<?=$data['detailInfoRefundFl']?>', 'detailInfoRefund');
                infoToggleDisplay('<?=$data['detailInfoExchangeFl']?>', 'detailInfoExchange');
            }, 1000);
        }

        if ($('input[name="maxOrderChk"]:checked').val() == 'y' && $('select[name="fixedOrderCnt"]').val() == 'id') {
            $("input:radio[name='goodsPermission']").eq(0).prop('disabled', true);
            $('#maxOrderCheckInfo').show();
            $('#maxOrderCheckDanger').show();
        }

        display_mileage_set();
        display_goods_discount_set();
        display_toggle_class('goodsDiscountFl', 'goodsDiscountConfig');
        except_benefit_disabled();

        $('.btn-kc').on('click', function(){
            var imgSrc = '../admin/gd_share/img/kcmark.jpg';
            <?php if (gd_is_provider()) { ?>
            imgSrc = '../' + imgSrc;
            <?php } ?>
            var message = '<div class="notice-info mgl15">KC인증 표시 기본 예시 입니다. (인증구분과 인증번호를 모두 입력한 경우)</div><div class="kcmark-info mgl15 mgr15 mgb15"><img src="' + imgSrc + '" class="mgt10 mgr10 mgl10 kcmark-img">[어린이제품] 안전확인 대상 품목으로 아래의 국가 통합인증 필함</br>인증번호 : <label>CB113F002-6006</label></br>(해당 인증 검사 정보는 판매자가 직접 등록한 것으로 등록 정보에 대한 책임은 판매자에게 있습니다.)</div>' +
                '<div class="table-kcmark mgt10"><div class="notice-danger mgl15">공급자적합성확인 대상 중 인증번호가 없는 경우, 인증번호를 별도로 입력하지 않아도 됩니다.</br>인증번호 미 입력 시, 아래와 같이 노출됩니다.</div><div class="kcmark-info1 mgl15 mgr15"><img src="' + imgSrc + '" class="mgr10 mgl10 kcmark-img">[어린이제품] 공급자적합성확인 대상 품목으로 아래의 국가 통합인증 필함</br>(해당 인증 검사 정보는 판매자가 직접 등록한 것으로 등록 정보에 대한 책임은 판매자에게 있습니다.)</div></div>';
            BootstrapDialog.show({
                title: 'KC인증 표시 예시',
                size: BootstrapDialog.SIZE_WIDE,
                message: message,
                closable: true
            });
        })

        $('input[name="kcmarkInfo[kcmarkFl]"]').on('click', function(){
            if ($(this).val() == 'y') {
                $('.select-kcmark').show();
            } else {
                $('.select-kcmark').hide();
            }
        })

        $('input[name="kcmarkInfo[kcmarkNo]"]').on('keyup focusout', function(){
            var oldText = $(this).val();
            var newText = oldText.replace(/[^0-9a-zA-Z-]+/g,"");
            $(this).val(newText);
        })

        // 방송통신지자재_kc인증일자 노출여부
        $('#kcmarkDivFl').change(function() {
            if($(this).val() == 'kcCd04' || $(this).val() == 'kcCd05' || $(this).val() == 'kcCd06'){
                $('.select-kcmark-dt').show();
            }else{
                $('.select-kcmark-dt').hide();
            }
        });

        // 방송통신지자재_kc인증일자 노출 여부(상품수정)
        if ($('input[name="mode"]').val() == 'modify'){
            if($('input[name="kcmarkInfo[kcmarkFl]"]:checked').val() == 'y') {
                if ($('#kcmarkDivFl option:selected').val() == 'kcCd04' || $('#kcmarkDivFl').val() == 'kcCd05' || $('#kcmarkDivFl').val() == 'kcCd06') {
                    $('.select-kcmark-dt').show();
                } else {
                    $('.select-kcmark-dt').hide();
                }
            }else{
                $('.select-kcmark-dt').hide();
            }
        }else{
            if ($('#kcmarkDivFl option:selected').val() == 'kcCd04' || $('#kcmarkDivFl').val() == 'kcCd05' || $('#kcmarkDivFl').val() == 'kcCd06') {
                $('.select-kcmark-dt').show();
            } else {
                $('.select-kcmark-dt').hide();
            }
        }

        if($('input[name="goodsBenefitSetFl"]:checked').val() == 'y'){
            display_benefit_set('y', 'goods_benefit_group');
        }

        $('.js-mileage-group-select, .js-except-benefit-group-select').bind('click', function () {
            $(this).closest('td').find('input[type="radio"][value="group"]').trigger('click');
        });

        $('.add-groupSno').click(function () {
            var target = $(this).data('target');
            switch (target) {
                case 'mileage':
                    var groupSnoName = 'select[name="mileageGroupMemberInfo[\'groupSno\'][]"]';
                    var goodsUnitName = 'select[name="mileageGroupMemberInfo[\'mileageGoodsUnit\'][]"]';
                    var inputName = 'mileageGroupMemberInfo[\'mileageGoods\'][]';
                    var appendClassName = 'mileage-set-g-group';
                    break;
                case 'discount':
                    var groupSnoName = 'select[name="goodsDiscountGroupMemberInfo[\'groupSno\'][]"]';
                    var goodsUnitName = 'select[name="goodsDiscountGroupMemberInfo[\'goodsDiscountUnit\'][]"]';
                    var inputName = 'goodsDiscountGroupMemberInfo[\'goodsDiscount\'][]';
                    var appendClassName = 'goods-discount-group';
                    break;
            }

            var groupCnt = '<?php echo $groupCnt; ?>';
            var length = $(groupSnoName).length;

            if (length >= groupCnt) {
                return;
            }

            var groupSnoInfo = $(this).closest('tr').find(groupSnoName)[0].outerHTML.replace('selected="selected"', '');
            var goodsUnitInfo = $(this).closest('tr').find(goodsUnitName)[0].outerHTML.replace('selected="selected"', '');

            var html = '<tr>' +
                '<td>' + groupSnoInfo + '</td>' +
                '<td class="form-inline"><span class="goods-title">구매금액의</span> <input type="text" name="' + inputName + '" value="" class="form-control width-sm"> ' + goodsUnitInfo + ' <input type="button" value="삭제" class="btn btn-sm btn-white btn-icon-minus del-groupSno"></td>' +
                '</tr>';
            $('.' + appendClassName + ' table').append(html);
        });

        $(document).on('click', '.del-groupSno', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('change', 'select[name="mileageGroupMemberInfo[\'groupSno\'][]"], select[name="goodsDiscountGroupMemberInfo[\'groupSno\'][]"]', function () {
            var name = this.name;
            var value = this.value;
            var flagFl = true;
            var index = $('select[name="' + name + '"]').index(this);

            $('select[name="' + name + '"]').each(function (idx) {
                if (index != idx && ($(this).val() && value == $(this).val())) {
                    flagFl = false;
                    return false;
                }
            });

            if (flagFl === false) {
                alert('이미 선택된 회원등급 입니다.');
                $(this).val('');
            }
        });

        $('.goods-unit').each(function () {
            set_goods_title($(this));
        });

        $(document).on('change', '.goods-unit', function () {
            set_goods_title($(this));
        });

        $('input[name="exceptBenefit[]"]').click(function () {
            except_benefit_disabled();
        });

        // 매입처 상품명 추가
        $('input[name="purchaseNmFl"]').click(function(){
            var goodsNm = $.trim($('input[name="goodsNm"]').val());
            var target = $('input[name="purchaseGoodsNm"]');
            $('input[name="purchaseGoodsNm"]').val(goodsNm);

            if (goodsNm.length > 0) {
                if ($(this).prop('checked')) {
                    target.val(goodsNm);
                    target.trigger('input');
                }
            }
        });

        // 탑버튼 클릭
        $(document).on("click", "a[href=#top]", function(e) {
            $('html body').animate({scrollTop: 0}, 'fast');
            $('.scrollDown').css('display','block');
            $('.scrollTop').css('display','none');
        });

        // 다운버튼 클릭
        $(document).on("click", "a[href=#down]", function(e) {
            $('html body').animate({scrollTop: $(document).scrollTop($(document).height())}, 'fast');
            $('.scrollDown').css('display','none');
            $('.scrollTop').css('display','block');
        });

        $('#gnbAnchor').css('display','block');

        // 관련상품 서로등록
        $(document).on('change', 'select.relationEach', function () {
            set_add_relation_goods(this.value, $(this).data('content'));
        });


        <?php
        /*
         * 상단 네비게이션바
         * 상품 수정팝업이 아니면 실행하지 않는다.
         */
        if (gd_isset($popupMode) == 'yes') {
        ?>
        document.title = '상품 관리 > 상품 수정 팝업';

        $(window).scroll(function () {
            $('#content').css('padding-top','52px');
            $('.gnb-mark').css('position', 'fixed');
            $('.gnb-mark').css('left', '0');
            $('.gnb-mark').css('width', '100%');

            $('.gnb-mark').css("margin", "0");
            $('.gnb-mark').css("padding", "");

            $('.gnb-mark').css('top', '');
            var scrollHeight = $(document).height();

            var height = $(document).scrollTop();

            if (height >= 1) {
                $('.gnb-mark').css("margin-top", "");
                $('.gnb-mark').css("padding", "0");

                $('.scrollTop').css('display','block');
                $('.scrollDown').css('display','block');
            } else {
                $('.gnb-mark').css('margin-top', '-50px');
                $('.gnb-mark').css('z-index', '2000');

                $('.scrollTop').css('display','none');
            }

            if (Math.round($(window).scrollTop()) >= $(document).height() - $(window).height()) {
                $('.scrollDown').css('display','none');
            }

            $('.table-title').each(function() {
                var id = $(this).attr('id');
                var idVal = "#"+id;
                var classVal = ".js-"+id

                if (id != null || id != undefined) {

                    if($(window).scrollTop() + 52 >= $(this).offset().top) {
                        //모두 초기화
                        scrollNavSet('1', idVal,classVal);
                    } else if ($(window).scrollTop() == 0) {
                        scrollNavSet('0', idVal,classVal);
                    }
                }
            });

        });
        $(window).scroll();

        //상단 탑 메뉴 링크 이동
        $('#gnb div a').on('click', function(event) {
            var id = $(this).attr('id');
            var idVal = "#"+id;
            var classVal = ".js-"+id

            scrollNavSet('1', idVal,classVal);
        });


        function scrollNavSet(scrollTop, idVal,classVal){

            $('#gnb div a').css("padding-top", "");
            $('#gnb div').closest('div').removeClass('bg-on');
            $('#gnb div a').removeClass('font-on');

            if (scrollTop > 0) {
                $(idVal).css("padding-top", "52px");
                $(classVal).closest('div').addClass('bg-on');
                $(classVal).removeClass('font-off');
                $(classVal).addClass('font-on');
            }

        }

        // 상품수정 팝업 창크기 변경시 width값이 auto로 변경되지 않도록 수정
        $(window).resize(function () {
            $('.gnb-mark').css('width', '100%');
        });

        <?php }?>

        $(document).on('change', 'input[name="maxOrderChk"]', function () {
            maxOrderCheckWithId();
        });

        $(document).on('change', 'select[name="fixedOrderCnt"]', function () {
            maxOrderCheckWithId();
        });

        function maxOrderCheckWithId() {
            if ($('input[name="maxOrderChk"]:checked').val() == 'y' && $('select[name="fixedOrderCnt"]').val() == 'id') {
                if ($('input[name="goodsPermission"]:checked').val() == 'all') {
                    dialog_confirm("ID기준 구매수량 설정 시, 이 상품의 구매가능 회원등급은 '회원전용(비회원제외)' 상품으로 등록됩니다.<br>구매수량을 ID기준으로 설정하시겠습니까?", function (result) {
                        if (result) {
                            $("input:radio[name='goodsPermission']").eq(1).trigger('click');
                            $("input:radio[name='goodsPermission']").eq(0).prop('disabled', true);
                            $('#maxOrderCheckInfo').show();
                            $('#maxOrderCheckDanger').show();
                        } else {
                            $("select[name='fixedOrderCnt']").val("option").prop("selected", true);
                            $("input:radio[name='goodsPermission']").eq(0).prop('disabled', false);
                            $('#maxOrderCheckInfo').hide();
                            $('#maxOrderCheckDanger').hide();
                        }
                    }, '', {"cancelLabel": '취소', "confirmLabel": '확인'});
                } else {
                    $("input:radio[name='goodsPermission']").eq(0).prop('disabled', true);
                    $('#maxOrderCheckInfo').show();
                    $('#maxOrderCheckDanger').show();
                }
            } else {
                $("input:radio[name='goodsPermission']").eq(0).prop('disabled', false);
                $('#maxOrderCheckInfo').hide();
                $('#maxOrderCheckDanger').hide();
            }
        }

    });

</script>
