<form id="frmMileageGive" name="frmMileageGive" action="member_ps.php" method="post">
    <input type="hidden" name="mode" value="mileage_give"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>마일리지 지급에 대한 설정을 하실 수 있습니다.</small>
        </h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        마일리지 지급설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>사용 설정</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="giveFl" value="y" <?php echo $checked['giveFl']['y']; ?> onclick="display_toggle('mileageGiveUse', 'show');"/>
                        사용함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="giveFl" value="n" <?php echo $checked['giveFl']['n']; ?> onclick="display_toggle('mileageGiveUse', 'hide');">
                        사용안함
                    </label>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <?php if ($mileageBasic['payUsableFl'] == 'n') {
        echo '<div class="notice-danger notice-sm"><a href="member_mileage_basic.php">회원>마일리지/예치금관리>마일리지 기본설정</a>에서 마일리지 사용여부가 사용안함으로 설정되어 마일리지 지급기능을 사용할 수 없습니다.</div>';
    } ?>


    <div id="mileageGiveUse" class="display-none">
        <div class="table-title gd-help-manual">
            구매 마일리지 통합 설정
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>상품 구매 시<br>기본 지급 마일리지</th>
                <td>
                    <div class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="giveType" value="price" <?php echo $checked['giveType']['price']; ?> />
                        </label>
                        구매금액의
                        <input type="text" name="goods" value="<?php echo $data['goods']; ?>" maxlength="3" class="form-control width-2xs js-number" data-number="3,100,0" />
                        % 를 마일리지로 지급
                    </div>
                    <div class="form-inline pdt10">
                        <label class="radio-inline">
                            <input type="radio" name="giveType" value="priceUnit" <?php echo $checked['giveType']['priceUnit']; ?> />
                        </label>
                        구매금액으로
                        <input type="text" name="goodsPriceUnit" value="<?php echo $data['goodsPriceUnit']; ?>" class="form-control width-2xs js-number" /> 원 단위로
                        <input type="text" name="goodsMileage" value="<?php echo $data['goodsMileage']; ?>" class="form-control width-2xs js-number" /> 마일리지 지급
                    </div>
                    <div class="form-inline pdt10">
                        <label class="radio-inline">
                            <input type="radio" name="giveType" value="cntUnit" <?php echo $checked['giveType']['cntUnit']; ?> />
                        </label>
                        구매금액과 상관없이 구매상품 1개 단위로
                        <input type="text" name="cntMileage" value="<?php echo $data['cntMileage']; ?>" class="form-control width-2xs js-number" /> 마일리지 지급
                    </div>
                    <div class="notice-info">
                        회원 > 마일리지 / 예치금 관리 > 마일리지 기본 설정에서 설정한 구매금액 기준에 따름 : <?= $data['mileageBasic']; ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>추가 지급 마일리지</th>
                <td>
                    <dl>
                        <?php foreach ($groupList as $group) {
                            $mileageType = gd_isset($data['mileageType'], 'percent');
                            $mileageValue = $mileageType == 'percent' ? gd_isset($group['mileagePercent']) : gd_isset($group['mileagePrice']);
                            $mileageUnit = $mileageType == 'percent' ? '%' : Globals::get('gSite.member.mileageBasic.unit');
                            ?>
                            <dd>
                                회원등급이 "<?php echo $group['groupNm'] ?>"인 경우
                                <?php if ($mileageValue > 0) { ?>
                                    (<?php echo $fixedOrderTypeData[gd_isset($group['fixedOrderTypeMileage'], 'option')]; ?>) 구매금액 <?php echo gd_currency_display($group['mileageLine']); ?> 이상 구매시 <?php echo $mileageValue; ?><?php echo $mileageUnit; ?> 을(를) 추가지급
                                <?php } else { ?>
                                    추가 지급 하지 않음
                                <?php } ?>
                            </dd>
                        <?php } ?>
                    </dl>
                    <a href="../member/member_group_list.php" target="_blank" class="btn btn-link pd0">회원등급별 혜택 설정 ></a>
                </td>
            </tr>
            <tr>
                <th>마일리지 사용시<br>지급 예외 설정</th>
                <td>
                    <h5>마일리지를 사용한 주문 건에 대해</h5>
                    <div class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" name="excludeFl" value="y" <?php echo $checked['excludeFl']['y']; ?>>
                            구매 마일리지 지급함
                        </label>
                    </div>
                    <div class="form-inline pdt10">
                        <label class="radio-inline">
                            <input type="radio" name="excludeFl" value="n" <?php echo $checked['excludeFl']['n']; ?>>
                            구매 마일리지 지급안함
                        </label>
                    </div>
                    <div class="form-inline pdt10 js-exclude">
                        <label class="radio-inline">
                            <input type="radio" name="excludeFl" value="r" <?php echo $checked['excludeFl']['r']; ?>>
                            지급률 재계산 (상품구매 금액 - 사용 마일리지 금액으로 해당 상품의 마일리지 지급금액 재계산)
                        </label>
                    </div>
                    <div class="form-inline pdt10 js-exclude">
                        <label class="radio-inline">
                            <input type="radio" name="excludeFl" value="m" <?php echo $checked['excludeFl']['m']; ?>>
                            지급금액 차감 (지급예정 마일리지를 미리 계산하여 사용 마일리지를 차감하고 지급)
                        </label>
                    </div>
                    <div class="form-inline pdt10 js-exclude">
                        <label class="radio-inline">
                            <input type="radio" name="excludeFl" value="p" <?php echo $checked['excludeFl']['p']; ?>>
                            지급률 차감 (구매금액 대비 사용 마일리지 비율을 구한 후, 해당 비율로 지급 예정 마일리지도 동일 비율로 차감)
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>지급 시점</th>
                <td>
                    <?php echo $mileageGiveStatus; ?>시 지급 <a href="../policy/order_status.php" target="_blank" class="btn btn-link">주문상태별 적립시점 설정 &gt;</a>
                    <span>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="delayFl" value="y" <?php echo $checked['delayFl']['y']; ?>> 지급 유예기능 사용 :
                        </label>
                        <select name="delayDay">
                            <?php
                            for ($i = 1; $i <= 15; $i++) {
                                ?>
                                <option value="<?= $i; ?>" <?= $selected['delayDay'][$i]; ?>><?= $i; ?>일</option>
                                <?php
                            }
                            ?>
                        </select>
                    </span>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="table-title gd-help-manual">
            이벤트성 마일리지 설정
        </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>신규회원 가입</th>
                <td>
                    <div class="checkbox form-inline">
                        신규회원 가입 시
                        <input type="text" name="joinAmount" value="<?php echo $data['joinAmount']; ?>" class="form-control width-2xs js-number">
                        <?php echo Globals::get('gSite.member.mileageBasic.unit'); ?> 지급
                    </div>
                    <div class="checkbox form-inline">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="emailFl" value="y" <?php echo $checked['emailFl']['y']; ?>>
                            이메일 수신동의 시
                        </label>
                        <input type="text" name="emailAmount" value="<?php echo $data['emailAmount']; ?>" class="form-control width-2xs js-number">
                        <?php echo Globals::get('gSite.member.mileageBasic.unit'); ?> 추가 지급
                    </div>
                    <div class="checkbox form-inline">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="smsFl" value="y" <?php echo $checked['smsFl']['y']; ?>>
                            SMS 수신동의 시
                        </label>
                        <input type="text" name="smsAmount" value="<?php echo $data['smsAmount']; ?>" class="form-control width-2xs js-number">
                        <?php echo Globals::get('gSite.member.mileageBasic.unit'); ?> 추가 지급
                    </div>
                </td>
            </tr>
            <tr>
                <th>추천인 등록</th>
                <td class="js-disabled-recomm">
                    <ul class="list-style-disc list-unstyled mgl15">
                        <li class="form-inline">
                            <label>
                                추천인 아이디를 등록한 회원에게
                                <input type="text" name="recommJoinAmount" value="<?php echo $data['recommJoinAmount']; ?>" class="form-control width-2xs js-number">
                                <?php echo Globals::get('gSite.member.mileageBasic.unit'); ?> 지급
                            </label>
                        </li>
                        <li class="form-inline mgt10">
                            추천인으로 등록된 회원에게
                            <input type="text" name="recommAmount" value="<?php echo $data['recommAmount']; ?>" class="form-control width-2xs js-number">
                            <?php echo Globals::get('gSite.member.mileageBasic.unit'); ?> 지급
                            <label class="checkbox-inline">
                                <input type="checkbox" name="recommCountFl" value="y" <?php echo $checked['recommCountFl']['y']; ?>>
                                지급횟수
                            </label>
                            <input type="text" name="recommCount" value="<?php echo $data['recommCount']; ?>" class="form-control width-2xs js-number" data-number="5,99999,0">
                            회로 제한
                        </li>
                    </ul>
                    <?php if (!$data['memberJoinItemRecommIdUse']) { ?>
                        <span class="notice-info">
                            * 회원가입 시 추천인 아이디를 입력하도록 설정해야 사용하실 수 있습니다. <a href="./member_joinitem.php" target="_blank" class="btn btn-link">회원가입항목 설정></a>
                        </span>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>생일축하</th>
                <td>
                    <div class="form-inline">
                        생일인 회원에게
                        <input type="text" name="birthAmount" value="<?php echo $data['birthAmount']; ?>" class="form-control width-2xs js-number">
                        <?php echo Globals::get('gSite.member.mileageBasic.unit'); ?>을(를) 마일리지로 지급
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 저장하기
        $("#frmMileageGive").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                giveFl: {
                    required: true,
                },
                giveType: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=giveFl]:checked').val() == 'y') {
                            required = true;
                        }
                        return required;
                    }
                },
                excludeFl: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=giveFl]:checked').val() == 'y') {
                            required = true;
                        }
                        return required;
                    }
                }
            },
            messages: {
                giveFl: {
                    required: '사용 설정을 선택하세요.',
                },
                giveType: {
                    required: '상품 구매 시 기본 지급 마일리지를 선택하세요.',
                },
                excludeFl: {
                    required: '마일리지 사용시 지급 예외 설정을 선택하세요.',
                }
            }
        });

        $('input:radio[name="giveType"]').click(function (e) {
            display_exclude();
        });

        <?php if ($data['giveFl'] == 'y') {?>display_toggle('mileageGiveUse', 'show')<?php }?>

        <?php if (!$data['memberJoinItemRecommIdUse']) { ?>
        // 회원가입항목내 추천인 아이디 미사용인 경우 해당 폼 엘리먼트 비활성화 처리
        $('.js-disabled-recomm input').each(function (obj) {
            $(this).prop('disabled', true);
            $(this).prop('checked', false);
            $(this).prop('value', '');
        });
        <?php } ?>

        display_exclude();
    });

    /**
     * 출력 여부
     *
     * @param string arrayID 해당 ID
     * @param string modeStr 출력 여부 (show or hide)
     */
    function display_toggle(thisID, modeStr) {
        if (modeStr == 'show') {
            $('#' + thisID).show();
            $('.notice-danger').hide();
        } else if (modeStr == 'hide') {
            $('#' + thisID).hide();
            $('.notice-danger').show();
        }
    }

    function display_exclude() {
        if ($('input:radio[name="giveType"]:checked').val() == 'price') {
            $('.js-exclude').show();
            $('.js-exclude input').prop("disabled", false);
        } else {
            $('.js-exclude').hide();
            $('.js-exclude input').prop("disabled", true);
            $('.js-exclude input').prop("checked", false);
        }
    }
    //-->
</script>
