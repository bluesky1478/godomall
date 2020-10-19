<form id="frm" action="../member/member_group_ps.php" method="post">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <input type="button" value="저장" class="btn btn-red" id="btnSubmit">
    </div>
    <input type="hidden" name="mode" value="appraisal_rule"/>

    <div class="table-title gd-help-manual">회원등급 평가방법</div>
    <div class="form-inline">
        <table class="table table-cols table-bordered">
            <colgroup>
                <col class="width-md"/>
                <col class="width-md"/>
                <col/>
                <col/>
            </colgroup>
            <tr>
                <th>자동/수동평가</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="automaticFl" value="n" <?= gd_isset($checked['automaticFl']['n']); ?>/>
                        수동 평가
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="automaticFl" value="y" <?= gd_isset($checked['automaticFl']['y']); ?>/>
                        자동 평가
                    </label>
                    <div class="row mgt10">
                        <div class="col-xs-12">
                            <span class="notice-info">자동 평가 선택 시 설정된 평가방법 및 평가기준의 산출기간/등급산정일에 따라 회원등급이 자동 평가됩니다.</span>
                        </div>
                        <div class="col-xs-12">
                            <span class="notice-info">
                                수동 평가 선택 시 회원등급이 자동으로 평가되지 않습니다. 실적에 따른 평가 필요 시 <a href="#" class="js-btn-appraisal">[회원등급 수동평가]</a>를 눌러 회원등급을 평가합니다.
                            </span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>하향평가 사용여부</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="downwardAdjustment" value="y" <?= gd_isset($checked['downwardAdjustment']['y']); ?>/>
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="downwardAdjustment" value="n" <?= gd_isset($checked['downwardAdjustment']['n']); ?>/>
                        사용안함
                    </label>
                    <div class="row mgt10">
                        <div class="col-xs-12">
                            <span class="notice-info">사용안함으로 설정할 경우, 자동/수동 평가에 따라 회원의 등급순서가 하향되지 않습니다</span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>
                    <label class="radio-inline">
                        <input type="radio" name="apprSystem"
                               value="figure" <?= gd_isset($checked['apprSystem']['figure']); ?>/>
                        실적 수치제
                    </label>
                </th>
                <td colspan="3">
                    주문금액, 상품주문건수, 주문상품후기횟수를 종합하여 평가하는 방법입니다. 회원등급별 평가기준을 입력하세요.
                    <div class="notice-info">수동 평가 선택 시 회원등급이 자동으로 평가되지 않고 <a href="#" class="js-btn-appraisal">[회원등급 수동평가]</a>를 눌러 회원등급을 평가합니다.</div>
                </td>
            </tr>
            <tr class="sysPoint">
                <th rowspan="7">
                    <label class="radio-inline">
                        <input type="radio" name="apprSystem" value="point" <?= gd_isset($checked['apprSystem']['point']); ?>/>
                        실적 점수제
                    </label>
                </th>
            </tr>
            <tr>
                <th colspan="2" class="text-center">쇼핑몰 전체 실적</th>
                <th class="text-center">모바일샵 추가 실적</th>
            </tr>
            <tr class="sysPoint">
                <th>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="appraisalPointOrderPriceFl" value="y" <?= gd_isset($checked['appraisalPointOrderPriceFl']['y']); ?> data-target-class="terms_p01"/>
                        주문금액
                    </label>
                </th>
                <td>
                    <input type="text" name="apprPointOrderPriceUnit"
                           value="<?= gd_isset($groupData['apprPointOrderPriceUnit'], 0) ?>"
                           size="10"
                           class="form-control input_int_l terms_p01 numberonly"/>
                    원당
                    <input type="text" name="apprPointOrderPricePoint"
                           value="<?= gd_isset($groupData['apprPointOrderPricePoint'], 0) ?>" size="10"
                           class="form-control input_int_m terms_p01 numberonly"/>
                    점
                </td>
                <td>
                    <input type="text" name="apprPointOrderPriceUnitMobile"
                           value="<?= gd_isset($groupData['apprPointOrderPriceUnitMobile'], 0) ?>"
                           size="10"
                           class="form-control input_int_l terms_p01 numberonly"/>
                    원당
                    <input type="text" name="apprPointOrderPricePointMobile"
                           value="<?= gd_isset($groupData['apprPointOrderPricePointMobile'], 0) ?>" size="10"
                           class="form-control input_int_m terms_p01 numberonly"/>
                    점
                </td>
            </tr>
            <tr class="sysPoint">
                <th>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="appraisalPointOrderRepeatFl" value="y" <?= gd_isset($checked['appraisalPointOrderRepeatFl']['y']); ?> data-target-class="terms_p02"/>
                        상품주문건수
                    </label>
                </th>
                <td>
                    구매 1회당
                    <input type="text" name="apprPointOrderRepeatPoint"
                           value="<?= gd_isset($groupData['apprPointOrderRepeatPoint'], 0) ?>" size="10"
                           class="form-control input_int_m terms_p02 numberonly"/>
                    점
                </td>
                <td>
                    구매 1회당
                    <input type="text" name="apprPointOrderRepeatPointMobile"
                           value="<?= gd_isset($groupData['apprPointOrderRepeatPointMobile'], 0) ?>" size="10"
                           class="form-control input_int_m terms_p02 numberonly"/>
                    점
                </td>
            </tr>
            <tr class="sysPoint">
                <th>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="appraisalPointReviewRepeatFl" value="y" <?= gd_isset($checked['appraisalPointReviewRepeatFl']['y']); ?> data-target-class="terms_p03"/>
                        주문상품후기
                    </label>
                </th>
                <td>
                    구매 후기당
                    <input type="text" name="apprPointReviewRepeatPoint"
                           value="<?= gd_isset($groupData['apprPointReviewRepeatPoint'], 0) ?>" size="10"
                           class="form-control input_int_m terms_p03 numberonly"/>
                    점
                </td>
                <td>
                    구매 후기당
                    <input type="text" name="apprPointReviewRepeatPointMobile"
                           value="<?= gd_isset($groupData['apprPointReviewRepeatPointMobile'], 0) ?>" size="10"
                           class="form-control input_int_m terms_p03 numberonly"/>
                    점
                </td>
            </tr>
            <tr class="sysPoint">
                <th>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="appraisalPointLoginRepeatFl" value="y" <?= gd_isset($checked['appraisalPointLoginRepeatFl']['y']); ?> data-target-class="terms_p04"/>
                        로그인 횟수
                    </label>
                </th>
                <td>
                    1회/일 로그인당
                    <input type="text" name="apprPointLoginRepeatPoint"
                           value="<?= gd_isset($groupData['apprPointLoginRepeatPoint'], 0) ?>" size="10"
                           class="form-control input_int_m terms_p04 numberonly"/>
                    점
                </td>
                <td>
                    1회/일 로그인당
                    <input type="text" name="apprPointLoginRepeatPointMobile"
                           value="<?= gd_isset($groupData['apprPointLoginRepeatPointMobile'], 0) ?>" size="10"
                           class="form-control input_int_m terms_p04 numberonly"/>
                    점
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">회원등급별 평가기준</div>
    <div class="form-inline">
        <table class="table table-cols table-bordered">
            <colgroup>
                <col class="width-md"/>
                <col class="width-sm"/>
                <col/>
                <col/>
                <col/>
                <col class="width-md"/>
                <col class="width-sm"/>
                <col class="width-sm"/>
            </colgroup>
            <thead>
            <tr>
                <th rowspan="2">회원등급명</th>
                <th rowspan="2">등급혜택</th>
                <th colspan="3">
                    <span class="sysFigureGroup display-none">실적 수치</span>
                    <span class="sysPointGroup display-none">실적 점수</span>
                </th>
                <th rowspan="2">실적계산기간</th>
                <th rowspan="2">등급 평가일</th>
            </tr>
            <tr>
                <th colspan="2">쇼핑몰 전체실적</th>
                <th colspan="">모바일샵 추가실적</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data) && is_array($data)) {
                $i = 0;
                $rows = count($data);
                foreach ($data as $idx => $val) {
                    $tmp = [];
                    if ($val['dcType'] == 'percent' && empty($val['dcPercent']) === false) {
                        array_push($tmp, sprintf('추가 %s 할인', $val['dcPercent'] . '%'));
                    } else if ($val['dcType'] == 'price' && empty($val['dcPrice']) === false) {
                        array_push($tmp, sprintf('추가 %s원 할인', $val['dcPrice']));
                    }
                    if ($val['mileageType'] == 'percent' && empty($val['mileagePercent']) === false) {
                        array_push($tmp, sprintf('추가 %s 적립', $val['mileagePercent'] . '%'));
                    } else if ($val['mileageType'] == 'price' && empty($val['mileagePrice']) === false) {
                        array_push($tmp, sprintf('추가 %s원 적립', $val['mileagePrice']));
                    }
                    if (empty($val['overlapDcPercent']) == false) {
                        array_push($tmp, sprintf('중복 %s 할인', $val['overlapDcPercent'] . '%'));
                    }
                    if (count($tmp) > 0) {
                        $benefit = '<ul class="list-group"><li class="">' . implode('</li><li class="mgt3">', $tmp) . '</li></ul>';
                    } else {
                        $benefit = '없음';
                    }
                    if (!empty($val['coupon'])){
                        $coupon = ''.$val['coupon'].'쿠폰발급';
                    }else{
                        $coupon = '쿠폰미발급';
                    }
                    ?>
                    <tr <?= (1 == $val['sno']) ? 'data-default="true"' : '' ?>>
                        <td>
                            <input type="hidden" name="sno[]" value="<?= $val['sno']; ?>"/>
                            <span><?= $val['groupNm']; ?></span>
                            <br/>
                        </td>
                        <td>
                            <span class="notice-ref notice-sm"><?= $benefit; ?><?= $coupon; ?></span>
                        </td>
                        <!-- 실적 수치제 -->
                        <th class="sysFigureGroup display-none">
                            <?php
                            $chknm1 = 'apprFigureOrderPriceFl[' . $val['sno'] . ']';
                            $chknm2 = 'apprFigureOrderRepeatFl[' . $val['sno'] . ']';
                            $chknm3 = 'apprFigureReviewRepeatFl[' . $val['sno'] . ']';

                            $checkClass1 = 'apprFigureOrderPrice' . $val['sno'];
                            $checkClass2 = 'apprFigureOrderRepeat' . $val['sno'];
                            $checkClass3 = 'apprFigureReviewRepeat' . $val['sno'];

                            $checked['apprFigureOrderPriceFl'][$val['sno']][$val['apprFigureOrderPriceFl']] =
                            $checked['apprFigureOrderRepeatFl'][$val['sno']][$val['apprFigureOrderRepeatFl']] =
                            $checked['apprFigureReviewRepeatFl'][$val['sno']][$val['apprFigureReviewRepeatFl']] = 'checked="checked"';
                            ?>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="<?= $chknm1 ?>" value="y" <?= $checked['apprFigureOrderPriceFl'][$val['sno']]['y'] ?> data-target-class="<?= $checkClass1 ?>"/>
                                주문금액
                            </label>
                            <br/>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="<?= $chknm2 ?>" value="y" <?= $checked['apprFigureOrderRepeatFl'][$val['sno']]['y'] ?> data-target-class="<?= $checkClass2 ?>"/>
                                상품주문건수
                            </label>
                            <br/>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="<?= $chknm3 ?>" value="y" <?= $checked['apprFigureReviewRepeatFl'][$val['sno']]['y'] ?> data-target-class="<?= $checkClass3 ?>"/>
                                주문상품후기
                            </label>
                        </th>
                        <td class="sysFigureGroup display-none">
                            <label class="checkbox-inline">
                                <input type="text"
                                       name="apprFigureOrderPriceMore[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprFigureOrderPriceMore']; ?>" size="6"
                                       class="form-control input_num_xl <?= $checkClass1 ?> numberonly"/>
                                만원 이상
                            </label>
                            ~
                            <label class="checkbox-inline">
                                <input type="text" name="apprFigureOrderPriceBelow[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprFigureOrderPriceBelow']; ?>" size="6"
                                       class="form-control input_num_xl <?= $checkClass1 ?> numberonly"/>
                                만원 미만
                            </label>
                            <br/>
                            <label class="checkbox-inline">
                                <input type="text" name="apprFigureOrderRepeat[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprFigureOrderRepeat']; ?>" size="6"
                                       class="form-control input_num_m <?= $checkClass2 ?> numberonly"/>
                            </label>
                            회 이상 <br/>
                            <label class="checkbox-inline">
                                <input type="text"
                                       name="apprFigureReviewRepeat[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprFigureReviewRepeat']; ?>" size="6"
                                       class="form-control input_num_m <?= $checkClass3 ?> numberonly"/>
                            </label>
                            개 이상

                        </td>
                        <td class="sysFigureGroup display-none">
                            <label class="checkbox-inline">
                                <input type="text"
                                       name="apprFigureOrderPriceMoreMobile[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprFigureOrderPriceMoreMobile']; ?>" size="6"
                                       class="form-control input_num_xl <?= $checkClass1 ?> numberonly"/>
                                만원 이상
                            </label>
                            ~
                            <label class="checkbox-inline">
                                <input type="text" name="apprFigureOrderPriceBelowMobile[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprFigureOrderPriceBelowMobile']; ?>" size="6"
                                       class="form-control input_num_xl <?= $checkClass1 ?> numberonly"/>
                                만원 미만
                            </label>
                            <br/>
                            <label class="checkbox-inline">
                                <input type="text" name="apprFigureOrderRepeatMobile[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprFigureOrderRepeatMobile']; ?>" size="6"
                                       class="form-control input_num_m <?= $checkClass2 ?> numberonly"/>
                            </label>
                            회 이상 <br/>
                            <label class="checkbox-inline">
                                <input type="text"
                                       name="apprFigureReviewRepeatMobile[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprFigureReviewRepeatMobile']; ?>" size="6"
                                       class="form-control input_num_m <?= $checkClass3 ?> numberonly"/>
                            </label>
                            개 이상
                        </td>
                        <!-- //실적 수치제 -->
                        <!-- 실적 점수제 -->
                        <td class="sysPointGroup display-none" colspan="2">
                            <label class="checkbox-inline">
                                <input type="text" name="apprPointMore[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprPointMore']; ?>" size="4"
                                       class="form-control input_num_m numberonly"/>
                            </label>
                            점 이상 ~
                            <label class="checkbox-inline">
                                <input type="text" name="apprPointBelow[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprPointBelow']; ?>" size="4"
                                       class="form-control input_num_m numberonly"/>
                            </label>
                            점 미만
                        </td>
                        <td class="sysPointGroup display-none" colspan="">
                            <label class="checkbox-inline">
                                <input type="text" name="apprPointMoreMobile[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprPointMoreMobile']; ?>" size="4"
                                       class="form-control input_num_m numberonly"/>
                            </label>
                            점 이상 ~
                            <label class="checkbox-inline">
                                <input type="text" name="apprPointBelowMobile[<?= $val['sno']; ?>]"
                                       value="<?= $val['apprPointBelowMobile']; ?>" size="4"
                                       class="form-control input_num_m numberonly"/>
                            </label>
                            점 미만
                        </td>
                        <!-- //실적 점수제 -->
                        <?php if ($idx == 0) { ?>
                            <td rowspan="<?= $rows; ?>" class="">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <label class="radio-inline">
                                            <input type="radio" name="calcPeriodFl"
                                                   value="n" <?= gd_isset($checked['calcPeriodFl']['n']); ?>/>
                                            기간제한 없음
                                        </label>
                                    </div>
                                    <div class="col-xs-12">
                                        <label class="radio-inline">
                                            <input type="radio" name="calcPeriodFl"
                                                   value="y" <?= gd_isset($checked['calcPeriodFl']['y']); ?>/>
                                            기간제한 있음
                                        </label>
                                    </div>
                                    <div class="col-xs-12">
                                        <select name="calcPeriodBegin" class="form-control">
                                            <option value="-1d" <?= gd_isset($selected['calcPeriodBegin']['-1d']); ?>>
                                                직전(어제)
                                            </option>
                                            <option value="-1w" <?= gd_isset($selected['calcPeriodBegin']['-1w']); ?>>
                                                1주일전
                                            </option>
                                            <option value="-2w" <?= gd_isset($selected['calcPeriodBegin']['-2w']); ?>>
                                                2주일전
                                            </option>
                                            <option value="-1m" <?= gd_isset($selected['calcPeriodBegin']['-1m']); ?>>
                                                한달전
                                            </option>
                                        </select> 부터
                                    </div>
                                    <div class="col-xs-12">
                                        <select name="calcPeriodMonth" class="form-control">
                                            <option value="1" <?= gd_isset($selected['calcPeriodMonth']['1']); ?>>1
                                            </option>
                                            <option value="2" <?= gd_isset($selected['calcPeriodMonth']['2']); ?>>2
                                            </option>
                                            <option value="3" <?= gd_isset($selected['calcPeriodMonth']['3']); ?>>3
                                            </option>
                                            <option value="6" <?= gd_isset($selected['calcPeriodMonth']['6']); ?>>6
                                            </option>
                                        </select> 개월간
                                    </div>
                                </div>
                            </td>
                            <td rowspan="<?= $rows; ?>" class="figure-group">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <select name="calcCycleMonth">
                                            <option value="1" <?= gd_isset($selected['calcCycleMonth']['1']); ?>>1
                                            </option>
                                            <option value="2" <?= gd_isset($selected['calcCycleMonth']['2']); ?>>2
                                            </option>
                                            <option value="3" <?= gd_isset($selected['calcCycleMonth']['3']); ?>>3
                                            </option>
                                            <option value="6" <?= gd_isset($selected['calcCycleMonth']['6']); ?>>6
                                            </option>
                                        </select> 개월마다
                                    </div>
                                    <div class="col-xs-12">해당월<select name="calcCycleDay">
                                            <?php
                                            for ($i = 1; $i <= 31; $i++) {
                                                echo sprintf('<option value="%s" %s>%s</option>', $i, gd_isset($selected['calcCycleDay'][$i]), $i);
                                            }
                                            ?>
                                        </select>일
                                    </div>
                                </div>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        var $checkboxAppraisal;

        var gd_group_list = {
            layerRule: null,
            message: {
                figure: "현재 설정된 평가방법은 실적수치제이며, 회원등급별 평가기준에 따라 주문금액, 상품주문건수, 주문상품후기횟수를 종합하여 회원등급이 평가됩니다. 회원등급 평가를 진행하시겠습니까?",
                point: "현재 설정된 평가방법은 실적점수제이며, 주문금액, 상품주문건수, 주문상품후기횟수, 로그인횟수를 점수로 환산하여 회원등급이 평가됩니다. 회원등급 평가를 진행하시겠습니까?"
            }
        };

        var frmObj = $('#frm');
        frmObj.validate({
            submitHandler: function (form) {
                var params = $(form).serializeArray();
                post_with_reload(form.action, params);
            }
        });

        $('#btnSubmit').click(function () {
            if (validate_submit()) {
                frmObj.submit();
            }
        });

        $('.js-btn-appraisal').click(function (e) {
            BootstrapDialog.confirm({
                title: $(e.target).text(),
                message: gd_group_list.message[$(':radio[name=apprSystem]:checked').val()],
                callback: function (result) {
                    if (result) {
                        var data = {
                            'mode': 'checkSendSms',
                            'checkType': 'group'
                        }
                        var $ajax = $.ajax('../member/member_ps.php', {type: "post", data: data});
                        $ajax.done(function (data, textStatus, jqXHR) {
                            if(data === true) {
                                $.get('../share/layer_sms_password.php?mode=input', function () {
                                    BootstrapDialog.show({
                                        title: 'SMS 인증번호 입력',
                                        size: BootstrapDialog.SIZE_WIDE,
                                        message: arguments[0]
                                    });
                                });
                            } else {
                                alert('데이터를 처리중입니다. 잠시만 기다려 주시기 바랍니다.');
                                post_with_reload('../member/member_group_ps.php', {
                                    mode: "appraisal",
                                    passwordCheckFl: 'n'
                                });
                            }
                        });
                    }
                }
            });
        });

        $('#layerClose').click(layer_close);

        var $apprSystem = $('input[name=\'apprSystem\']');
        $apprSystem.click(set_appr_system);
        $apprSystem.each(function () {
            set_appr_system.call(this);
        });

        $('input[name=\'dcPercent\'], input[name=\'mileagePercent\']').number_only(4, 100, 100);
        $('.numberonly').number_only();

        disableDefaultGroup();

        $checkboxAppraisal = $(':checkbox[name*="appraisalPoint"], :checkbox[name*="apprFigure"]');
        $checkboxAppraisal.change(function () {
            var $this = $(this);
            var targetClass = $this.data('targetClass');
            var $target = $('.' + targetClass);
            $target.each(function (i, item) {
                var checked = $this.prop('checked');
                $(item).prop('disabled', !checked);
            });
        });
        $checkboxAppraisal.trigger("change");

        function set_appr_system() {
            if ($(this).prop('checked') === false) return;
            var thisVal = $('input[name=\'apprSystem\']:checked').val();
            if (thisVal == 'figure') {
                $('.sysPoint input:not(input[name=\'apprSystem\'])').prop('disabled', true);
                $('input', $('.sysFigureGroup').show()).prop('disabled', false);
                $('input:not(:text)', $('.sysPointGroup').hide()).prop('disabled', true);
                $(':checkbox[name*=\'apprFigure\']').each(function () {
                    set_terms.call(this);
                });
                $('.figure-group').find(':radio, select:not([name="calcCycleMonth"], [name="calcCycleDay"])').prop('disabled', false);
            } else if (thisVal == 'point') {
                $('.sysPoint input:not(input[name=\'apprSystem\'])').prop('disabled', false);
                $('input', $('.sysFigureGroup:not([name="calcPeriodFl"], [name=""])').hide()).prop('disabled', true);
                $('input', $('.sysPointGroup').show()).prop('disabled', false);
                $('input[name*=\'terms_p\']').each(function () {
                    set_terms.call(this);
                });
                $('.figure-group').find(':radio, select:not([name="calcCycleMonth"], [name="calcCycleDay"])').prop('disabled', true);
            }
            disableDefaultGroup();
            if ($checkboxAppraisal) {
                $checkboxAppraisal.trigger("change");
            }
        }
    });

    function sms_password_callback(password) {
        post_with_reload('../member/member_group_ps.php', {
            mode: "appraisal",
            password: password
        });
    }

    function validate_submit() {
        if ($(':radio[name=apprSystem]:checked').val() == 'figure') {
            return validate_figure();
        } else {
            return validate_point();
        }
    }

    function validate_point() {
        var isOpen = true;
        $('.sysPoint :checkbox').each(function () {
            if (this.checked) {
                isOpen = false;
                return false;
            }
        });
        if (isOpen) {
            var message = '선택한 실적점수제의 평가 기준이 없습니다. 평가 기준이 없을 경우 회원등급이 자동/수동으로 평가되지 않습니다. 이대로 저장하시겠습니까?';
            var callback = function (isConfirm) {
                if (isConfirm) {
                    $('#frm').submit();
                }
            };
            dialog_confirm(message, callback);
        } else {
            return true;
        }
    }

    function validate_figure() {
        var isOpen = false;
        $('th.sysFigureGroup').not(':last').each(function () {
            var isGroupChecked = is_figure_group_checked($(this));
            if (isGroupChecked === false) {
                isOpen = true;
                return false;
            }
        });
        if (isOpen) {
            var message = '회원등급 평가 실적 기준이 입력되지 않은 회원등급이 있습니다. 해당 회원등급은 등급평가 시 제외됩니다. 이대로 저장하시겠습니까?';
            var callback = function (isConfirm) {
                if (isConfirm) {
                    $('#frm').submit();
                }
            };
            dialog_confirm(message, callback);
        } else {
            return true;
        }
    }

    function is_figure_group_checked($group) {
        var isReturn = false;
        $group.find(':checked').each(function () {
            if (this.checked) {
                isReturn = true;
                return false;
            }
        });
        return isReturn;
    }

    function disableDefaultGroup() {
        $('tr[data-default=true]').find(':input').prop('disabled', true);
    }
    /**
     * 조건 disabled
     */
    function set_terms() {
        var nm = $(this).attr('name');
        var checked = $(this).prop('checked');
        $('input.' + nm).each(function () {
            if (checked === false) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
    }
    //-->
</script>
