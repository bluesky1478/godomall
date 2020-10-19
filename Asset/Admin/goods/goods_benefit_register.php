<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $("#frmBenefit").validate({
            submitHandler: function (form) {


                if ($('input[name="goodsDiscountFl"]').val() == 'y') {
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

                if ($('input[name="benefitUseType"]:checked').val() == 'newGoodsDiscount') {
                    if ($('input[name="newGoodsDate"]') == '' || $('input[name="newGoodsDate"]').val() < 1) {
                        alert('신상품 할인 기간을 입력해주세요');
                        return false;
                    }
                }

                if ($('[name="goodsDiscountGroup"]:checked').val() != 'group' && $('input[name="goodsDiscount"]').val() < 1) {
                    alert('상품 할인 설정의 금액설정 항목을 입력하세요');
                    return false;
                }

                if ($('input[name="benefitUseType"]:checked').val() == 'periodDiscount') {
                    var sdt = dateGetTime($('input[name="periodDiscountStart"]').val());
                    var edt = dateGetTime($('input[name="periodDiscountEnd"]').val());
                    if (sdt > edt) {
                        alert('특정기간 할인의 시작일/종료일을 확인해 주세요');
                        return false;
                    }
                }

                form.target='ifrmProcess';
                form.submit();
            },
            // onclick: false, // <-- add this option
            rules: {
                benefitNm: 'required',
                periodDiscountStart: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=benefitUseType]:checked').val() == 'periodDiscount') {
                            required = true;
                        }
                        return required;
                    }
                },
                periodDiscountEnd: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=benefitUseType]:checked').val() == 'periodDiscount') {
                            required = true;
                        }
                        return required;
                    }
                },

            },

            messages: {
                benefitNm: {
                    required: '혜택명을 입력하세요.'
                },
                periodDiscountStart: {
                    required: '특정기간 할인의 시작일/종료일을 확인해 주세요.'
                },
                periodDiscountEnd: {
                    required: '특정기간 할인의 시작일/종료일을 확인해 주세요.'
                },

            }
        });


        display_goods_discount_set();
        display_toggle_class('goodsDiscountFl', 'goodsDiscountConfig');
        except_benefit_disabled();
        display_benefitUseType();

        $('.js-mileage-group-select, .js-except-benefit-group-select').bind('click', function () {
            $(this).closest('td').find('input[type="radio"][value="group"]').trigger('click');
        });

        $('.js-goods-benefit-select').bind('click', function () {
            $(this).closest('td').find('input[name="benefitScheduleFl"][value="y"]').trigger('click');
        });

        $("input[name='benefitUseType']").change(function(){
            display_benefitUseType();
        });

        $('.add-groupSno').click(function(){
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

        $(document).on('click', '.del-groupSno', function(){
            $(this).closest('tr').remove();
        });

        $(document).on('change', 'select[name="mileageGroupMemberInfo[\'groupSno\'][]"], select[name="goodsDiscountGroupMemberInfo[\'groupSno\'][]"]', function(){
            var name = this.name;
            var value = this.value;
            var flagFl = true;
            var index = $('select[name="' + name + '"]').index(this);

            $('select[name="' + name + '"]').each(function(idx){
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

        $('.goods-unit').each(function(){
            set_goods_title($(this));
        });

        $(document).on('change', '.goods-unit', function(){
            set_goods_title($(this));
        });

        $('input[name="exceptBenefit[]"]').click(function(){
            except_benefit_disabled();
        });

        $("input[name='newGoodsDate']").number_only(5, 99999, 1);
        $("input[name='goodsDiscount']").number_only();

    });


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

    function display_benefitUseType() {
        $('.periodDiscountConfig').hide();

        var type = $('input[name="benefitUseType"]:checked').val();
        switch (type) {
            case 'nonLimit':
            case 'newGoodsDiscount':
                $('.periodDiscountConfig').hide();
                break;
            case 'periodDiscount':
                $('.periodDiscountConfig').show();
                break;
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
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
            <?php if ($data['mode'] == 'modify') { ?>
            "goodsBenefitSno": <?=$data['sno']; ?>,
            <?php } ?>
        };

        if (typeStr == 'goods_benefit') {
            addParam['layerFormID'] = 'goodsBenefitGroup';
            addParam['parentFormID'] = 'goods_benefit_group';
            addParam['dataFormID'] = 'info_goods_benefit_group';
            addParam['dataInputNm'] = 'benefitScheduleNextSno';
            addParam['callFunc'] = 'set_add_goods_benefit';
            if($('input[name="periodDiscountEnd"]').val() == ''){
                alert('다음혜택 예약을 설정하려면 현재 등록중인 특정기간을 입력하셔야 합니다.');
                return false;
            }
            addParam['registerDt'] = $('input[name="periodDiscountEnd"]').val();
        }

        if (typeStr == 'except_benefit_group') {
            addParam['layerFormID'] = 'exceptBenefitGroup';
            addParam['parentFormID'] = 'except_benefit_group';
            addParam['dataFormID'] = 'info_except_benefit_group';
            addParam['dataInputNm'] = 'exceptBenefitGroupInfo';
            typeStr = 'member_group';
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr, addParam);

    }

    function display_next_benefit(value, target){
        if (value == 'n') {
            $('#' + target).empty().removeClass('active');
        } else {
            $('#' + target).addClass('active');
        }
    }

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

    //-->
</script>
<form id="frmBenefit" name="frmBenefit" action="./goods_benefit_ps.php" method="post">
    <input type="hidden" name="mode" value="<?=$data['mode'];?>"/>
    <input type="hidden" name="checkNextSno" value="<?=$data['benefitScheduleNextSno'];?>"/>
    <input type="hidden" name="benefitSchedulePrevSno" value="<?=$data['benefitSchedulePrevSno'];?>"/>
    <input type="hidden" name="goodsDiscountFl" value="y"/>
    <input type="hidden" name="popupMode" value="<?=$popupMode;?>"/>
    <?php if ($data['mode'] == 'modify') { ?>
        <input type="hidden" name="sno" value="<?=$data['sno']; ?>" />
    <?php } ?>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <?php
            if($popupMode != 'yes') {
                ?>
                <input type="button" value="목록" class="btn btn-white btn-icon-list"
                       onclick="goList('./goods_benefit_list.php');"/>
                <?php
            }?>
            <input type="submit" value="저장" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title gd-help-manual">
        기본설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>

        <tr>
            <th class="input_title r_space require">혜택명</th>
            <td class="input_area">
                <label>
                    <input type="text" name="benefitNm" value="<?=gd_isset($data['benefitNm']); ?>" class="form-control width-xl js-maxlength" maxlength="30"/>
                </label>
            </td>
        </tr>
        <tr>
            <th class="input_title r_space">진행유형</th>
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
                        <select name="newGoodsRegFl" class="form-control" style="display: none">
                            <option value="regDt" <?=gd_isset($selected['newGoodsRegFl']['regDt']); ?>>등록일</option>
                            <!--<option value="modDt" <?=gd_isset($selected['newGoodsRegFl']['modDt']); ?>>수정일</option>-->
                        </select>
                        부터
                        <input type="text" name="newGoodsDate" value="<?=$data['newGoodsDate']?>" class="form-control width-2xs" >
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
            <th class="require">금액 설정</th>
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
        <tr>
            <th class="input_title r_space">아이콘 설정</th>
            <td class="inut_area" >
                <?php
                foreach ($data['icon'] as $key => $val) {
                    if ($val['iconPeriodFl'] == 'n') {
                        echo '<label class="nobr checkbox-inline"><input type="checkbox" name="goodsIconCd[]" value="' . $val['iconCd'] . '" ' . gd_isset($checked['goodsIconCd'][$val['iconCd']]) . ' /> ' . gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']) . '</label>' . chr(10);
                    }
                }
                ?>
                <p class="notice-info">
                    <a href="/goods/goods_icon_list.php" target="_blank" class="btn-link">상품>상품 관리>상품 아이콘 관리</a>에 등록된 아이콘 중 무제한형 아이콘만 설정 가능합니다.
                </p>
                <p class="notice-info">
                    상품에 이미 동일한 아이콘이 적용된 경우, 아이콘은 1개만 노출됩니다.
                </p>
            </td>
        </tr>
        <tr class="periodDiscountConfig">
            <th class="input_title r_space">다음 혜택 예약설정</th>
            <td class="inut_area" >
                <label class="radio-inline">
                    <input type="radio" name="benefitScheduleFl" value="n" <?=gd_isset($checked['benefitScheduleFl']['n']); ?> onclick="display_next_benefit('n', 'goods_benefit_group');"/>사용안함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="benefitScheduleFl" value="y" <?=gd_isset($checked['benefitScheduleFl']['y']); ?> onclick="display_next_benefit('y', 'goods_benefit_group');layer_register('goods_benefit','layer_benefit')"/>사용함
                </label>
                <label>
                    <button type="button" class="btn btn-sm btn-gray js-goods-benefit-select">혜택 선택</button>
                </label>
                <div id="goods_benefit_group" class="selected-btn-group <?= ($data['benefitScheduleNextSno']) > 0 ? 'active' : '' ?>">
                    <?php if($nextBenefitData['sno'] > 0){

                        $arrUseType    = array('nonLimit' => '제한없음', 'newGoodsDiscount' => '신상품 할인', 'periodDiscount' => '특정기간 할인');
                        $arrDiscountGroup        = array('all' => '전체', 'member' => '회원전용', 'group' => '특정회원등급');
                        $arrNewGoodsReg        = array('regDt' => '등록일', 'modDt' => '수정일');
                        $arrNewGoodsDate      = array('day' => '일', 'hour' => '시간');

                        $nextBenefitData['goodsDiscountGroupMemberInfo'] = json_decode($nextBenefitData['goodsDiscountGroupMemberInfo'], true);
                        $stateText = '';
                        if($nextBenefitData['benefitUseType'] == 'periodDiscount') {

                            if ($nextBenefitData['periodDiscountStart'] < date('Y-m-d H:i:s') && $nextBenefitData['periodDiscountEnd'] > date('Y-m-d H:i:s')) {
                                $stateText = "<span class='text-blue'>진행중</span><br>";
                            } else if ($nextBenefitData['periodDiscountEnd'] < date('Y-m-d H:i:s')) {
                                $stateText = "<span class='text-red'>종료</span><br>";
                            } else if ($nextBenefitData['periodDiscountStart'] > date('Y-m-d H:i:s')) {
                                $stateText = "<span>대기중</span><br>";
                            }
                        }
                        if($nextBenefitData['benefitUseType'] == 'nonLimit'){
                            $benefitPeriod = '<span class="text-blue">'.$arrUseType[$nextBenefitData['benefitUseType']].'</span>';
                        }else if($nextBenefitData['benefitUseType'] == 'newGoodsDiscount'){
                            $benefitPeriod = '<span class="text-blue">상품'.$arrNewGoodsReg[$nextBenefitData['newGoodsRegFl']].'부터 '.$nextBenefitData['newGoodsDate'].$arrNewGoodsDate[$nextBenefitData['newGoodsDateFl']].'까지</span>';
                        }else{
                            $benefitPeriod ='<span>'.gd_date_format("Y-m-d H:i",$nextBenefitData['periodDiscountStart']).' ~ '.gd_date_format("Y-m-d H:i",$nextBenefitData['periodDiscountEnd']).'</span>';
                        }

                        ?>
                    <h5>선택된 혜택:</h5>
                    <div id="info_goods_benefit_group_<?=$nextBenefitData['sno']?>" class="btn-group btn-group-xs">
                        <input type="hidden" name="benefitScheduleNextSno" value="<?=$nextBenefitData['sno']?>">
                        <span><?=$nextBenefitData['benefitNm']?>(<?=$nextBenefitData['goodsDiscountUnit'] == 'percent' ? $nextBenefitData['goodsDiscount']. '%' : gd_money_format($nextBenefitData['goodsDiscount'], false). gd_currency_default();?> - <?=$arrDiscountGroup[$nextBenefitData['goodsDiscountGroup']]?>) (<?=$benefitPeriod?>)</span>
                        <span>
                            <button type="button" class="btn btn-sm btn-white" data-benefitSno="<?=$nextBenefitData['sno']?>" onclick="goods_benefit_popup('<?=$nextBenefitData['sno']?>')">수정</button>
                            <button type="button" class="btn btn-sm btn-white" data-toggle="delete" data-target="#info_goods_benefit_group_<?=$nextBenefitData['sno']?>">삭제</button>
                        </span>
                    </div>
                    <?php } ?>
                </div>
                <div class="mgt10">
                    <p class="notice-danger">
                            혜택의 시작일이 현재 혜택보다 이전인 혜택은 다음 혜택으로 예약설정이 불가합니다.
                    </p>
                    <p class="notice-danger">
                            다음 혜택 예약설정은 다른 혜택과 중복으로 선택이 불가합니다.
                    </p>
                </div>

            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual pos-r">
        혜택 제외 설정 <span class="notice-info" style="position: absolute;left: 140px;top: 5px;">신상품 할인, 특정기간 할인 혜택을 상품에 적용할 경우, 혜택 종료 시 혜택 제외 설정도 함께 종료됩니다.</span>
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>

        <tr>
            <th class="input_title r_space">제외 혜택 선택</th>
            <td class="input_area">
                <?= gd_check_box('exceptBenefit[]', $exceptBenefit, $data['exceptBenefit'], 1); ?>
            </td>
        </tr>
        <tr>
            <th class="input_title r_space">제외 대상 선택</th>
            <td class="input_area">
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
    </table>


</form>
