<?php
/**
 * @var array                          $combineSearch
 * @var array                          $rangeSearch
 * @var array                          $checked
 * @var array                          $couponData
 * @var Framework\Object\SimpleStorage $attendance
 */
//debug(Request::get()->all());
?>
<form id="formSearch" name="formSearch" method="get" class="content-form js-search-form">
    <input type="hidden" id="sno" name="sno" value="<?= Request::get()->get('sno', '') ?>"/>
    <input type="hidden" id="page" name="page" value="<?= Request::get()->get('page', '1') ?>"/>
    <input type="hidden" id="pageNum" name="pageNum" value="<?= Request::get()->get('pageNum', 5) ?>"/>
    <input type="hidden" id="benefitGiveMode" name="benefitGiveMode" value="<?= $benefitGiveMode; ?>"/>
    <input type="hidden" id="couponUseLayer" name="couponUseLayer" value="attendance_detail"/>
    <div class="mgt10">
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>출석체크이벤트명</th>
                <td><?= $attendance->get('title', ''); ?></td>
            </tr>
            <tr>
                <th>회원검색</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box('key', 'key', $combineSearch, null, Request::get()->get('key', '')) ?>
                        <label for="keyword" style="display:none;"></label>
                        <input id="keyword" name="keyword" value="<?= Request::get()->get('keyword', '') ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td>
                    <div class="form-inline">
                        <?= gd_select_box('rangeKey', 'rangeKey', $rangeSearch, null, Request::get()->get('rangeKey', '')) ?>
                        <div class="input-group js-datepicker">

                            <input type="text" name="rangeDt[]" class="form-control width-xs" placeholder=""
                                   value="<?= Request::get()->get('rangeDt')[0]; ?>">
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>
                            </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="rangeDt[]" class="form-control width-xs" placeholder=""
                                   value="<?= Request::get()->get('rangeDt')[1]; ?>">
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>
                            </span>
                        </div>
                        <div class="btn-group js-dateperiod" data-toggle="buttons" data-target-name="rangeDt">
                            <label class="btn btn-white btn-sm">
                                <input type="radio" value="0"
                                       name="rangeDtPeriod" <?= gd_isset($checked['rangeDtPeriod']['0']); ?>>
                                오늘
                            </label>
                            <label class="btn btn-white btn-sm">
                                <input type="radio" value="7"
                                       name="rangeDtPeriod" <?= gd_isset($checked['rangeDtPeriod']['7']); ?>>
                                7일
                            </label>
                            <label class="btn btn-white btn-sm">
                                <input type="radio" value="15"
                                       name="rangeDtPeriod" <?= gd_isset($checked['rangeDtPeriod']['15']); ?>>
                                15일
                            </label>
                            <label class="btn btn-white btn-sm">
                                <input type="radio" value="30"
                                       name="rangeDtPeriod"<?= gd_isset($checked['rangeDtPeriod']['30']); ?>>
                                1개월
                            </label>
                            <label class="btn btn-white btn-sm">
                                <input type="radio" value="90"
                                       name="rangeDtPeriod" <?= gd_isset($checked['rangeDtPeriod']['90']); ?>>
                                3개월
                            </label>
                            <label class="btn btn-white btn-sm">
                                <input type="radio" value="-1"
                                       name="rangeDtPeriod" <?= gd_isset($checked['rangeDtPeriod']['-1']); ?>>
                                전체
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>조건달성여부</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="conditionDtFl"
                               value="" <?= gd_isset($checked['conditionDtFl']['']); ?>/>
                        전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="conditionDtFl"
                               value="y" <?= gd_isset($checked['conditionDtFl']['y']); ?>/>
                        달성
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="conditionDtFl"
                               value="n" <?= gd_isset($checked['conditionDtFl']['n']); ?>/>
                        미달성
                    </label>
                </td>
            </tr>
            <tr>
                <th>혜택지급여부</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="benefitDtFl"
                               value="" <?= gd_isset($checked['benefitDtFl']['']); ?>/>
                        전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="benefitDtFl"
                               value="y" <?= gd_isset($checked['benefitDtFl']['y']); ?>/>
                        지급
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="benefitDtFl"
                               value="n" <?= gd_isset($checked['benefitDtFl']['n']); ?>/>
                        미지급
                    </label>
                </td>
            </tr>
        </table>
    </div>
    <div class="table-btn">
        <input type="button" value="검색" class="btn btn-lg btn-black" id="btnAttendanceSearch"/>
    </div>
</form>
<form id="formList" name="formList">
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width-3xs">
                <input type="checkbox" class="js-checkall" data-target-name="layerChk[]"/>
            </th>
            <th class="width-2xs">번호</th>
            <th class="width-md">아이디</th>
            <th>이름</th>
            <th>최종참여일시</th>
            <th class="width-xs">누적참여횟수</th>
            <th>조건달성일시</th>
            <th>혜택지급일시</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $listsHtml = [];
        if (isset($lists) && is_array($lists)) {
            foreach ($lists as $index => $list) {
                $modDt = substr($list['modDt'], 0, 16);
                $regDt = substr($list['regDt'], 0, 16);
                $modDtFl = gd_isset($modDt, '') != '';
                $regDtFl = gd_isset($regDt, '') != '';

                $conditionDt = substr($list['conditionDt'], 0, 16);
                $conditionDtFl = gd_isset($conditionDt, '') === '' || substr($conditionDt, 0,4) === '0000';
                $benefitDt = substr($list['benefitDt'], 0, 16);
                $benefitDtFl = gd_isset($benefitDt, '') === '' || substr($benefitDt, 0,4) === '0000';
                $disabled = 'disabled="disabled"';
                if (!$conditionDtFl && $benefitDtFl) {
                    $disabled = '';
                }

                $listsHtml[] = '<tr class="center">';
                $listsHtml[] = '<td><label for="chk[' . $list['memNo'] . ']"><input type="checkbox" id="chk[' . $list['memNo'] . ']" name="layerChk[]" value="' . $list['memNo'] . '" data-group-sno="' . $list['groupSno'] . '" ' . $disabled . '/></label></td>';
                $listsHtml[] = '<td>' . $layerPage->idx-- . '</td>';
                $listsHtml[] = '<td>' . $list['memId'] . '</td>';
                $listsHtml[] = '<td>' . $list['memNm'] . '</td>';
                $listsHtml[] = '<td>' . ($modDtFl ? $modDt : ($regDtFl ? $regDt : '')) . '</td>';
                $listsHtml[] = '<td>' . $list['attendanceCount'] . '</td>';
                $listsHtml[] = '<td>' . ($conditionDtFl ? '-' : $conditionDt) . '</td>';
                $listsHtml[] = '<td>' . ($benefitDtFl ? '-' : $benefitDt) . '</td>';
                $listsHtml[] = '</tr>';
            }
            echo join('', $listsHtml);
        }
        ?>
        </tbody>
    </table>
</form>
<div>
    <input type="hidden" name="memberCouponNo" value=<?=$memberCouponInfo?>>
</div>
<div class="text-center"><?php echo $layerPage->getPage('#'); ?></div>
<?php if ($attendance->get('benefitGiveFl', '') == 'manual') { ?>
    <form id="formSetting" name="formSetting" method="get" class="content-form">
        <div class="mgt10">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>대상회원 선택</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="targetFl"
                                   value="select" <?= gd_isset($checked['targetFl']['select']); ?>/>
                            선택회원
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="targetFl"
                                   value="search" <?= gd_isset($checked['targetFl']['search']); ?>/>
                            검색회원 전체
                        </label>
                    </td>
                </tr>
                <?php if (gd_use_mileage() || gd_use_coupon()) { ?>
                    <tr>
                        <th>혜택지급</th>
                        <td>
                            <?php if (gd_use_mileage()) { ?>
                                <div class="form-inline">
                                    <label class="radio-inline">
                                        <input type="radio" name="benefitFl"
                                               value="mileage" <?= gd_isset($checked['benefitFl']['mileage']); ?>/>
                                        <?= gd_display_mileage_name() ?>
                                        <input type="text" class="form-control" id="benefitMileage" name="benefitMileage" value="<?= Request::get()->get('benefitMileage', '') ?>"/>
                                        <?= gd_display_mileage_unit() ?> 지급
                                    </label>
                                </div>
                            <?php } ?>
                            <?php if (gd_use_coupon()) { ?>
                                <div class="form-inline mgt5">
                                    <label class="radio-inline">
                                        <span id="couponBenefit">
                                        <input type="radio" name="benefitFl"
                                               value="coupon" <?= gd_isset($checked['benefitFl']['coupon']); ?>/>
                                        쿠폰
                                        <select class="form-control " id="benefitCouponSno" name="benefitCouponSno" aria-required="true">
                                            <option value="">쿠폰 선택</option>
                                            <?php
                                            foreach($couponData as $val) {
                                                $couponSelected = (Request::get()->get('benefitCouponSno', '') == $val['couponNo']) ? 'selected="selected"' : '';
                                                $couponDisabled = ($val['couponType'] == 'f') ? 'disabled' : '';
                                                if ($val['couponType'] != 'f' || Request::get()->get('benefitCouponSno', '') == $val['couponNo']) {
                                                    ?>
                                                    <option value="<?= $val['couponNo'] ?>" <?=$couponSelected?> <?=$couponDisabled?>><?= $couponDisabled ? '(발급종료)':''; ?><?= $val['couponNm'] ?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                        지급
                                        </span>
                                        <button type="button" class="btn btn-sm btn-gray js-coupon-register" id="btnCouponRegister">신규쿠폰 등록</button>
                                        <span id="selectInfo" class="notice-info">대상회원에게 발급 가능한 쿠폰만 노출됩니다.</span>
                                    </label>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </form>
<?php } ?>
<div class="text-center">
    <?php if ($attendance->get('benefitGiveFl', '') == 'manual') { ?>
        <input type="button" value="혜택지급" class="btn btn-lg btn-black" id="btnBenefitGive"/>
    <?php } ?>
    <input type="button" value="닫기" class="btn btn-lg btn-white js-close"/>
</div>

<script type="text/javascript">
    <!--
    (function ($, _, window, document, undefined) {
        var dialog, validateFormSetting, validateFormList;
        var $formSearch, $formList, $formSetting;

        var get_benefit_give_parameter = function () {
            var params1 = $formSearch.serializeArray();
            params1.push({name: "mode", value: $('#benefitGiveMode').val()});
            var params2 = [];

            $('input[name="layerChk[]"]:checked').each(function () {
                params2.push({name: "layerChk[]", value: $(this).val()});
                params2.push({name: "groupSno[]", value: $(this).data("groupSno")});
            });
            var params3 = $formSetting.serializeArray();
            var params = $.merge(params1, params2);
            params = $.merge(params, params3);

            if (($('input[name="benefitFl"]:checked').val() =='coupon')) {
                params.forEach(function (item, index){
                    if(item.name == "benefitFl") {
                        item.value = $('input[name="benefitFl"]:checked').val();
                    }
                    if(item.name == "benefitCouponSno"){
                        item.value = $('select[name="benefitCouponSno"]').val();
                    }
                });
            }else if(($('input[name="benefitFl"]:checked').val() =='mileage')) {
                params.forEach(function (item, index){
                    if(item.name == "benefitMileage") {
                        item.value = $('input[name="benefitMileage"]').val();
                    }
                });
            }
            if (($('input[name="targetFl"]:checked').val() == "search")) { //검색회원 전체
                params.forEach(function (item, index){
                    if(item.name == "targetFl") {
                        item.value = $('input[name="targetFl"]:checked').val();
                    }
                    if(item.name== "keyword"){
                        if(item.value != $("#keyword").val()){
                            item.value = $("#keyword").val();
                        }
                    }
                });
            }

            if (($('input[name="targetFl"]:checked').val() == "select")) { //선택회원
                params.forEach(function (item, index){
                    if(item.name== "keyword"){
                        if(item.value != $("#keyword").val()){
                            item.value = $("#keyword").val();
                        }
                    }
                });
            }
            return params;
        };

        var click_benefit_give = function () {
            if (validateFormList.form() && validateFormSetting.form()) {
                ajax_with_layer('../promotion/attendance_ps.php', get_benefit_give_parameter(), function (data) {
                    alert(data);
                }, function (error) {
                    console.log('e', error);
                    alert(error);
                });
                layer_list_search();
            }
        };


        var set_dialog_event = function () {
            dialog.$modalBody
                .on('click', '.pagination a', function (e) {
                e.preventDefault();
                var pageNum = ($(e.target).prop('tagName') == 'IMG') ? $(e.target.parentElement).data('page') : $(e.target).data('page');
                $('#page', dialog.$modalBody).val(pageNum);
                layer_list_search();
            })
                .off('click', '#btnBenefitGive').on('click', '#btnBenefitGive', click_benefit_give)
                .off('click', '#btnAttendanceSearch').on('click', '#btnAttendanceSearch', function (e) {
                layer_list_search();
            }).on('click', '.js-close', function () {
                layer_close();
            }).on('click', '.js-checkall', function () {
                if ($(this).data('target-name')) {
                    $('input:checkbox[name*=\'' + $(this).data('target-name') + '\']:not(:disabled)').prop('checked', this.checked);
                } else {
                    // 테이블에서만 사용 가능
                    var name = $(this).closest('table').find('thead input:checkbox').data('target-name');
                    if (!_.isUndefined(name)) {
                        $('input:checkbox[name*=\'' + name + '\']:not(:disabled)').prop('checked', this.checked);
                    }
                }
                $('input[name="layerChk[]"]').trigger('change');
            }).on('change', ':checkbox[name="layerChk[]"]', function () {
                var memberCoupon = JSON.parse($('input[name="memberCouponNo"]').val()); // 회원별 사용가능한 쿠폰
                var allCoupon = <?=json_encode($couponData)?>; // 전체 쿠폰
                var allCouponAmount = <?=$couponAmountInfo ?>;
                var notUseCouponNo = [];
                var couponNo = [];

                for (var i in allCoupon) {
                    notUseCouponNo.push(i);
                }
                $('input[name="layerChk[]"]').each(function () {
                    if (this.checked) {
                        if(memberCoupon[this.value] == "") memberCoupon[this.value] = null;
                        if (memberCoupon[this.value] != null) {
                            var tmpCouponNo = memberCoupon[this.value].split("||"); // 사용가능 쿠폰번호
                            for (var i in tmpCouponNo) {
                                couponNo.push(tmpCouponNo[i]);
                            }
                        }
                    }
                });

                var useCouponNo = [];
                var checkedMemberCnt = $('input[name="layerChk[]"]:checked').length;

                //체크된 회원이 1명 이상인 경우 회원수만큼 중복된 쿠폰번호 출력
                if (checkedMemberCnt > 1) {
                    for (var i in allCoupon) { // 전체 쿠폰
                        var cnt = 0;
                        for (var j in couponNo) {
                            if (i == couponNo[j]){
                                cnt = cnt + 1;
                            }
                        }
                        if (cnt == checkedMemberCnt)
                            useCouponNo.push(i);
                    }
                } else {//체크된 회원이 1명인 경우 그대로적용
                    useCouponNo = couponNo;
                }

                if(useCouponNo != null) {
                    for (var i in useCouponNo) {
                        var useCouponNoTag = $('#benefitCouponSno option[value=' + useCouponNo[i] + ']');
                        if(useCouponNoTag.parent().is("div")){
                            useCouponNoTag.unwrap();
                        }
                        useCouponNoTag.show();
                        //- 전체발송수량이 정해져있는 쿠폰인 경우 발송가능한 수량보다 회원수가 많을 경우 disabled 처리
                        for (var j in allCouponAmount) {
                            if (useCouponNo[i] == j && checkedMemberCnt > allCouponAmount[j]) {
                                useCouponNoTag.hide();
                                if(useCouponNoTag.parent().is("div") === false){
                                    useCouponNoTag.wrap('<div id="not show"/>');
                                }
                            }
                        }

                        var removeIdx = notUseCouponNo.indexOf(useCouponNo[i]);
                        if (removeIdx != -1) {
                            notUseCouponNo.splice(removeIdx, "1");
                        }
                    }
                }

                for (var i in notUseCouponNo) {
                    var notUseCouponNoTag =  $('#benefitCouponSno option[value=' + notUseCouponNo[i] + ']');
                    notUseCouponNoTag.hide();
                    if(notUseCouponNoTag.parent().is("div") === false){
                        notUseCouponNoTag.wrap('<div id="not show"/>');
                    }
                }
                notUseCouponNo = [];
                couponNo = [];
            }).on('click', '#couponBenefit', function () {
                if (($('input[name="benefitFl"]:checked').val() =='coupon')) {
                    if(($('input[name="layerChk[]"]:checked').length == 0) && $('input[name="targetFl"]:checked').val() == "select") {
                       $('input[name="benefitFl"]:input[value="mileage"]').prop("checked", true);
                        alert('선택된 회원이 없습니다.');
                        return;
                    }
                }
            }).on('change', 'input[name="targetFl"]', function () {
                if($('input[name="targetFl"]:checked').val() == "search"){
                    $("#selectInfo").hide();
                    $("#benefitCouponSno option").each(function(){
                       if($(this).parent().is("div")){
                           $(this).unwrap();
                           $(this).show();
                       }
                    });
                }else if($('input[name="targetFl"]:checked').val() == "select"){
                    $("#selectInfo").show();
                }
            }).on('click', '#benefitCouponSno', function () {
                if($('input[name="benefitFl"]:input[value="coupon"]').is(":checked") === false && $('input[name="layerChk[]"]:checked').length==0){
                    $('#couponBenefit').trigger('click');
                }
            }).on('click', '.js-coupon-register', function () {
                window.open('../promotion/coupon_regist.php?couponSaveType=auto&couponEventType=attend&callback=coupon_reload');
            }).on('click', 'div.bootstrap-dialog-close-button', function () {
                if ($('input[name*=\'scmNo\']').length == 0) {
                    $('[name=scmFl][value=n]').prop('checked', true);
                }
            });
            //            .on('click', ':radio[name="benefitFl"]', click_benefit_fl);
        };

        var init = function () {
            init_datetimepicker();
            $('input').keyup(function (e) {
                if (e.which == 13) layer_list_search();
            });

            dialog = attendance_list.get_dialog();
            set_dialog_event();

            $formSearch = $('#formSearch');
            $formList = $('#formList');
            $formSetting = $('#formSetting');

            validateFormList = $formList.validate({
                rules: {
                    "layerChk[]": {
                        required: function () {
                            return $(':radio[name=targetFl]:checked', $formSetting).val() == 'select';
                        }
                    }
                }, messages: {
                    "layerChk[]": {
                        required: "선택된 회원이 없습니다."
                    }
                }
            });

            validateFormSetting = $formSetting.validate({
                rules: {
                    benefitMileage: {
                        required: function () {
                            return $(':radio[name=benefitFl]:checked', $formSetting).val() == 'mileage';
                        }
                    }, benefitCouponSno: {
                        required: function () {
                            return $(':radio[name=benefitFl]:checked', $formSetting).val() == 'coupon';
                        }
                    }
                }, messages: {
                    benefitMileage: "지급할 마일리지 금액을 입력해주세요.",
                    benefitCouponSno: "지급할 쿠폰을 선택해주세요."
                }

            });
        };

        var layer_list_search = function () {
            var params = $('#formSearch').serializeArray();
            $.get('<?= URI_ADMIN;?>share/layer_attendance_detail.php', params, function (data) {
                $('div#layer-wrap', dialog.$modalBody).html(data);
            });
        };

        $(document).ready(init);
    })($, _, window, document);

    //-->
</script>
