<form id="frmCoupon" name="frmCoupon" action="coupon_ps.php" method="post" class="content_form" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?= $couponData['mode']; ?>"/>
    <input type="hidden" name="ypage" value="<?= $ypage; ?>"/>
    <input type="hidden" name="couponNo" value="<?= $couponData['couponNo']; ?>"/>
    <input type="hidden" name="couponKind" value="<?= $couponData['couponKind']; ?>"/>
    <input type="hidden" name="couponType" value="<?= $couponData['couponType']; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./coupon_offline_list.php');" />
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <h5 class="table-title">기본설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                쿠폰유형
            </th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="couponUseType" value="product" <?= $checked['couponUseType']['product'] ?> />상품적용 쿠폰
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponUseType" value="order" <?= $checked['couponUseType']['order'] ?> />주문적용 쿠폰
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponUseType" value="delivery" <?= $checked['couponUseType']['delivery'] ?> />배송비할인 쿠폰
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponUseType" value="gift" <?= $checked['couponUseType']['gift'] ?> />기프트쿠폰
                </label>
            </td>
        </tr>
        <tr>
            <th class="require">
                쿠폰명
            </th>
            <td colspan="3">
                <input type="text" name="couponNm" value="<?= $couponData['couponNm'] ?>" class="form-control width-xl" maxlength="30"/>
            </td>
        </tr>
        <tr>
            <th>
                쿠폰설명
            </th>
            <td colspan="3">
                <input type="text" name="couponDescribed" value="<?= $couponData['couponDescribed'] ?>" class="form-control width100p" maxlength="50"/>
            </td>
        </tr>
        <tr>
            <th class="require">
                사용기간
            </th>
            <td colspan="3">
                <div id="divCouponUsePeriodTypePeriod" class="radio form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="couponUsePeriodType" value="period" <?= $checked['couponUsePeriodType']['period'] ?> />
                        <div class="input-group js-fulltimepicker">
                            <input type="text" name="couponUsePeriodStartDate" value="<?= $couponData['couponUsePeriodStartDate'] ?>" class="form-control" maxlength="20">
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>
                            </span>
                        </div> ~
                        <div class="input-group js-fulltimepicker">
                            <input type="text" name="couponUsePeriodEndDate" value="<?= $couponData['couponUsePeriodEndDate'] ?>" class="form-control" maxlength="20">
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar">
                                </span>
                            </span>
                        </div>
                    </label>
                </div>
                <div id="divCouponUsePeriodTypePeriodDay" class="form-inline pdt10">
                    <label class="radio-inline">
                        <input type="radio" name="couponUsePeriodType" value="day" <?= $checked['couponUsePeriodType']['day'] ?> />
                        쿠폰 발급일로 부터
                        <input type="text" name="couponUsePeriodDay" value="<?= $couponData['couponUsePeriodDay'] ?>" class="form-control width-2xs" maxlength="3"/> 일까지 사용가능
                    </label>
                    <div class="pdt5 pdl20">
                        사용가능일을
                        <div class="input-group js-fulltimepicker">
                            <input type="text" name="couponUseDateLimit" value="<?= $couponData['couponUseDateLimit'] ?>" class="form-control" maxlength="20">
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        까지로 제한합니다.
                        <span class="notice-info">입력하지 않을 경우 제한이 없습니다.</span>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                혜택구분
            </th>
            <td colspan="3">
                <label id="labelCouponKindTypeSale" class="radio-inline">
                    <input type="radio" name="couponKindType" value="sale" <?= $checked['couponKindType']['sale'] ?> />상품할인
                </label>
                <label id="labelCouponKindTypeAdd" class="radio-inline">
                    <input type="radio" name="couponKindType" value="add" <?= $checked['couponKindType']['add'] ?> />마일리지적립
                </label>
                <label id="labelCouponKindTypeDelivery" class="radio-inline">
                    <input type="radio" name="couponKindType" value="delivery" <?= $checked['couponKindType']['delivery'] ?> />배송비할인
                </label>
                <label id="labelCouponKindTypeDeposit" class="radio-inline">
                    <input type="radio" name="couponKindType" value="deposit" <?= $checked['couponKindType']['deposit'] ?> />예치금 지급
                </label>
                <div id="labelCouponKindTypeDepositInfo" class="notice-info">예치금은 현금성 자산으로 관리되므로, 발급 시 법적요건과 시리얼 번호 관리에 유의하시기 바랍니다.</div>
            </td>
        </tr>
        <tr>
            <th>
                사용범위
            </th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="couponDeviceType" value="all" <?= $checked['couponDeviceType']['all'] ?> />PC+모바일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponDeviceType" value="pc" <?= $checked['couponDeviceType']['pc'] ?> />PC
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponDeviceType" value="mobile" <?= $checked['couponDeviceType']['mobile'] ?> />모바일
                </label>
            </td>
        </tr>
        <tr>
            <th class="require">
                혜택금액설정
            </th>
            <td colspan="3">
                <div class="form-inline mgb5">
                    <input type="text" name="couponBenefit" value="<?= gd_money_format($couponData['couponBenefit'], false); ?>" class="form-control js-number-only width-2xs" maxlength="8"/>
                    <select name="couponBenefitType" class="form-control select">
                        <option value="percent" <?= $selected['couponBenefitType']['percent'] ?>>%</option>
                        <option value="fix" <?= $selected['couponBenefitType']['fix'] ?>><?= gd_currency_default(); ?></option>
                    </select><span class="benefit-text">할인</span> <span id="benefittype_text">(구매금액 기준)</span>
                </div>
                <div class="form-inline div-benefit">
                    <label class="checkbox">
                        <input type="checkbox" name="couponMaxBenefitType" value="y" <?= $checked['couponMaxBenefitType']['y'] ?> />
                    </label>
                    <?= gd_currency_symbol(); ?>
                    <input type="text" name="couponMaxBenefit" value="<?= gd_money_format($couponData['couponMaxBenefit'], false); ?>" class="form-control js-number-only width-2xs" maxlength="8"/><?= gd_currency_string(); ?> 까지 <span class="benefit-text">할인</span>가능
                </div>
                <p class="notice-info">
                    절사기준 : <a href="../policy/base_currency_unit.php" target="_blank" class="btn-link">기본설정>기본정책>금액/단위 기준설정</a>에서 설정한 기준에 따름
                </p>
                <p class="notice-info">주문적용 쿠폰은 %(정률) / 배송비할인 쿠폰은 원(정액)으로만 설정이 가능합니다.</p>
            </td>
        </tr>
        <tr>
            <th class="require">
                등록기간 설정
            </th>
            <td colspan="3">
                <div class="form-inline">
                    <div class="input-group js-fulltimepicker">
                        <input type="text" name="couponDisplayStartDate" value="<?= $couponData['couponDisplayStartDate'] ?>" class="form-control" maxlength="20">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div> ~
                    <div class="input-group js-fulltimepicker">
                        <input type="text" name="couponDisplayEndDate" value="<?= $couponData['couponDisplayEndDate'] ?>" class="form-control" maxlength="20">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                쿠폰노출이미지
            </th>
            <td colspan="3">
                <div class="radio">
                    <label>
                        <input type="radio" name="couponImageType" value="basic" <?= $checked['couponImageType']['basic'] ?> />기본 쿠폰 이미지
                        <img src="<?= PATH_ADMIN_GD_SHARE ?>img/coupon.png" alt="기본쿠폰이미지"/>
                    </label>
                </div>
                <div class="radio form-inline reg-couponimg">
                    <label>
                        <input type="radio" name="couponImageType" value="self" <?= $checked['couponImageType']['self'] ?> />쿠폰이미지 직접등록
                    </label>
                    <input type="file" name="couponImage" class="form-control"/>
                    <?php
                    if ($couponData['couponImage']) {
                        echo '<img src="'.$couponData['couponImage'].'" alt="쿠폰이미지"/>';
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                쿠폰인증번호 타입
            </th>
            <td colspan="3">
                <input type="hidden" name="registedCouponAuthType" value="<?= $couponData['couponAuthType'] ?>"/>
                <label class="radio-inline">
                    <input type="radio" name="couponAuthType" value="n" <?= $checked['couponAuthType']['n'] ?> />1개의 인증번호 사용
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponAuthType" value="y" <?= $checked['couponAuthType']['y'] ?> />회원별로 다른 인증번호 사용
                </label>
            </td>
        </tr>
        <tr class="tr-direct">
            <th class="require">
                쿠폰인증번호 설정
            </th>
            <td colspan="3" class="form-inline">
                <input type="hidden" name="duplicateCouponAuthNumber" value=""/>
                <input type="text" name="couponAuthNumber" value="<?= $couponData['couponAuthNumber'] ?>" class="form-control width-xl" maxlength="12"/>
                <button type="button" class="btn btn-sm btn-gray" id="overlap_authnumber">중복확인</button>
            </td>
        </tr>
        </tbody>
    </table>

    <h5 id="titleLimitOption" class="table-title">제한조건 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr class="tr_gift">
            <th>
                결제수단 사용제한
            </th>
            <td colspan="3" class="form-inline">
                <label class="radio-inline">
                    <input type="radio" name="couponUseAblePaymentType" value="all" <?= $checked['couponUseAblePaymentType']['all'] ?> />제한없음
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponUseAblePaymentType" value="bank" <?= $checked['couponUseAblePaymentType']['bank'] ?> />무통장입금만 사용가능
                </label>
                <p> </p>
                <p class="notice-danger">
                    무통장입금시에만 쿠폰을 사용할 수 있도록 제한하는 것은 여신전문금융법에 저촉될 수 있습니다.
                    <a class="btn-link" style="cursor:pointer;" onclick="lawAlert();">[자세히보기]</a>
                </p>
            </td>
        </tr>
        <tr class="tr_down">
            <th>
                쿠폰 재발급 제한
            </th>
            <td colspan="3">
                <dl class="dl-horizontal">
                    <dt style="width:200px;">같은 아이디에 대해 같은 쿠폰을</dt>
                    <dd style="margin-left:200px;">
                        <div class="radio mgt0">
                            <label class="radio-inline">
                                <input type="radio" name="couponSaveDuplicateType" value="n" <?= $checked['couponSaveDuplicateType']['n'] ?> />재발급하지 않음
                            </label>
                        </div>
                        <div class="radio mgb0 form-inline">
                            <label class="radio-inline">
                                <input type="radio" name="couponSaveDuplicateType" value="y" <?= $checked['couponSaveDuplicateType']['y'] ?> />재발급함
                            </label>
                            <label class="checkbox-inline mgl10">
                                <input type="checkbox" name="couponSaveDuplicateLimitType" value="y" <?= $checked['couponSaveDuplicateLimitType']['y'] ?> />최대
                            </label>
                            <input type="text" name="couponSaveDuplicateLimit" value="<?= $couponData['couponSaveDuplicateLimit'] ?>" class="form-control js-number-only width-xs" maxlength="8"/>장
                        </div>
                    </dd>
                </dl>
            </td>
        </tr>
        <tr class="tr_down tr_auto">
            <th>
                발급 가능 회원등급 선택
            </th>
            <td colspan="3">
                <div class="form-inline">
                    <button type="button" class="btn btn-sm btn-gray" id="selectApplyMemberGroup" class="btn btn-sm" title="발급 가능 회원등급을 선택해주세요.">회원등급선택</button>
                    <label class="checkbox-inline mgl10 label_memberdown">
                        <input type="checkbox" name="couponApplyMemberGroupDisplayType" value="y" <?= $checked['couponApplyMemberGroupDisplayType']['y'] ?> />발급/사용 가능한 회원등급에게만 쿠폰노출
                    </label>
                </div>
                <div id="couponApplyMemberGroup" class="selected-btn-group <?=$couponData['couponApplyMemberGroup'] ? 'active' : ''?>">
                    <h5>선택된 회원등급</h5>
                    <?php
                    if ($couponData['couponApplyMemberGroup']) {
                        foreach ($couponData['couponApplyMemberGroup'] as $k => $v) {
                    ?>
                    <span id="idMemberGroup_<?= $couponData['couponApplyMemberGroup'][$k]['no'] ?>" class="btn-group btn-group-xs">
                        <input type="hidden" name="couponApplyMemberGroup[]" value="<?= $couponData['couponApplyMemberGroup'][$k]['no'] ?>"/>
                        <span class="btn"><?= $couponData['couponApplyMemberGroup'][$k]['name'] ?></span>
                        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#idMemberGroup_<?= $couponData['couponApplyMemberGroup'][$k]['no'] ?>">삭제</button>
                    </span>
                    <?php
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr class="tr-apply-use">
            <th>
                쿠폰 발급/사용 가능 범위 설정
            </th>
            <td colspan="3" class="form-inline">
                <label class="radio-inline">
                    <input type="radio" name="couponApplyProductType" value="all" <?= $checked['couponApplyProductType']['all'] ?> />전체상품
                </label>
                <?php if (gd_use_provider()) { ?>
                <label class="radio-inline">
                    <input type="radio" name="couponApplyProductType" value="provider" <?= $checked['couponApplyProductType']['provider'] ?> />특정 공급사
                </label>
                <?php } ?>
                <label class="radio-inline">
                    <input type="radio" name="couponApplyProductType" value="category" <?= $checked['couponApplyProductType']['category'] ?> />특정 카테고리
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponApplyProductType" value="brand" <?= $checked['couponApplyProductType']['brand'] ?> />특정 브랜드
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponApplyProductType" value="goods" <?= $checked['couponApplyProductType']['goods'] ?> />특정 상품
                </label>
            </td>
        </tr>
        <?php if (gd_use_provider()) { ?>
        <tr class="tr-apply-provider tr-apply-use">
            <th>
                발급/사용 가능 특정 공급사 선택
            </th>
            <td colspan="3" class="form-inline">
                <button type="button" id="selectApplyProvider" class="btn btn-sm" title="적용할 공급사을 선택해주세요.">공급사선택</button>
                <div id="couponApplyProvider">
                <?php
                if ($couponData['couponApplyProductType'] == 'provider' && $couponData['couponApplyProvider']) {
                    foreach ($couponData['couponApplyProvider'] as $k => $v) {
                ?>
                    <span id="idProvider_<?= $couponData['couponApplyProvider'][$k]['no'] ?>" class="btn-group btn-group-xs">
                        <input type="hidden" name="couponApplyProvider[]" value="<?= $couponData['couponApplyProvider'][$k]['no'] ?>"/>
                        <button type="button" class="btn btn-default" name="couponApplyProviderName[]"><?= $couponData['couponApplyProvider'][$k]['name'] ?></button>
                        <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#idProvider_<?= $couponData['couponApplyProvider'][$k]['no'] ?>">삭제</button>
                    </span>
                <?php
                    }
                }
                ?>
                </div>
            </td>
        </tr>
        <?php } ?>
        <tr class="tr-apply-category tr-apply-use">
            <th>
                발급/사용 가능 특정 카테고리 선택
            </th>
            <td colspan="3" class="form-inline">
                <button type="button" id="selectApplyCategory" class="btn btn-sm" title="적용할 카테고리을 선택해주세요.">카테고리선택</button>
                <div id="couponApplyCategory">
                <?php
                if ($couponData['couponApplyProductType'] == 'category' && $couponData['couponApplyCategory']) {
                    foreach ($couponData['couponApplyCategory'] as $k => $v) {
                ?>
                    <span id="idCategory_<?= $couponData['couponApplyCategory'][$k]['no'] ?>" class="btn-group btn-group-xs">
                        <input type="hidden" name="couponApplyCategory[]" value="<?= $couponData['couponApplyCategory'][$k]['no'] ?>"/>
                        <button type="button" class="btn btn-default" name="couponApplyCategoryName[]"><?= $couponData['couponApplyCategory'][$k]['name'] ?></button>
                        <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#idCategory_<?= $couponData['couponApplyCategory'][$k]['no'] ?>">삭제</button>
                    </span>
                <?php
                    }
                }
                ?>
                </div>
            </td>
        </tr>
        <tr class="tr-apply-brand tr-apply-use">
            <th>
                발급/사용 가능 특정 브랜드 선택
            </th>
            <td colspan="3" class="form-inline">
                <button type="button" id="selectApplyBrand" class="btn btn-sm" title="적용할 브랜드을 선택해주세요.">브랜드선택</button>
                <div id="couponApplyBrand">
                <?php
                if ($couponData['couponApplyProductType'] == 'brand' && $couponData['couponApplyBrand']) {
                    foreach ($couponData['couponApplyBrand'] as $k => $v) {
                ?>
                    <span id="idBrand_<?= $couponData['couponApplyBrand'][$k]['no'] ?>" class="btn-group btn-group-xs">
                        <input type="hidden" name="couponApplyBrand[]" value="<?= $couponData['couponApplyBrand'][$k]['no'] ?>"/>
                        <button type="button" class="btn btn-default" name="couponApplyBrandName[]"><?= $couponData['couponApplyBrand'][$k]['name'] ?></button>
                        <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#idBrand_<?= $couponData['couponApplyBrand'][$k]['no'] ?>">삭제</button>
                    </span>
                <?php
                    }
                }
                ?>
                </div>
            </td>
        </tr>
        <tr class="tr-apply-goods tr-apply-use">
            <th>
                발급/사용 가능 특정 상품 선택
            </th>
            <td colspan="3" class="form-inline">
                <div class="table-action table-action-header">
                    <button type="button" id="selectApplyGoods" class="btn btn-white" title="적용할 상품을 선택해주세요.">상품선택</button>
                </div>
                <table id="couponApplyGoods" class="table table-cols">
                    <thead>
                    <tr>
                        <th>번호</th>
                        <th>이미지</th>
                        <th>상품명</th>
                        <th>삭제</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($couponData['couponApplyProductType'] == 'goods' && $couponData['couponApplyGoods']) {
                        foreach ($couponData['couponApplyGoods'] as $key => $val) {
                            echo '<tr id="idGoods_' . $val['goodsNo'] . '">' . chr(10);
                            echo '<td>' . ($key + 1) . '<input type="hidden" name="couponApplyGoods[]" value="' . $val['goodsNo'] . '" /></td>' . chr(10);
                            echo '<td>' . gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank') . '</td>' . chr(10);
                            echo '<td>' . gd_remove_tag($val['goodsNm']) . '</td>' . chr(10);
                            echo '<td><span class="button gray small"><input type="button" onclick="field_remove(\'idGoods_' . $val['goodsNo'] . '\');" value="삭제" /></span></td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">

                        </td>
                    </tr>
                    </tfoot>
                </table>
                <div class="table-action">
                    <input type="button" class="btn btn-white" value="전체삭제" onclick="$('#couponApplyGoods tbody').html('');">
                </div>
            </td>
        </tr>
        <tr class="tr-except-use">
            <th>
                쿠폰 발급/사용 제외 설정
            </th>
            <td colspan="3" class="form-inline">
                <?php if (gd_use_provider()) { ?>
                <label class="checkbox-inline">
                    <input type="checkbox" name="couponExceptProviderType" value="y" <?= $checked['couponExceptProviderType']['y'] ?> />특정 공급사
                </label>
                <?php } ?>
                <label class="checkbox-inline">
                    <input type="checkbox" name="couponExceptCategoryType" value="y" <?= $checked['couponExceptCategoryType']['y'] ?> />특정 카테고리
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="couponExceptBrandType" value="y" <?= $checked['couponExceptBrandType']['y'] ?> />특정 브랜드
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="couponExceptGoodsType" value="y" <?= $checked['couponExceptGoodsType']['y'] ?> />특정 상품
                </label>
            </td>
        </tr>
        <?php if (gd_use_provider()) { ?>
        <tr class="tr-except-provider tr-except-use">
            <th>
                발급/사용 제외 특정 공급사 선택
            </th>
            <td colspan="3" class="form-inline">
                <button type="button" id="selectExceptProvider" class="btn btn-sm" title="적용할 공급사을 선택해주세요.">공급사선택</button>
                <div id="couponExceptProvider">
                <?php
                if ($couponData['couponExceptProviderType'] == 'y' && $couponData['couponExceptProvider']) {
                    foreach ($couponData['couponExceptProvider'] as $k => $v) {
                ?>
                    <span id="idExceptProvider_<?= $couponData['couponExceptProvider'][$k]['no'] ?>" class="btn-group btn-group-xs">
                        <input type="hidden" name="couponExceptProvider[]" value="<?= $couponData['couponExceptProvider'][$k]['no'] ?>"/>
                        <button type="button" class="btn btn-default" name="couponExceptProviderName[]"><?= $couponData['couponExceptProvider'][$k]['name'] ?></button>
                        <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#idExceptProvider_<?= $couponData['couponExceptProvider'][$k]['no'] ?>">삭제</button>
                    </span>
                <?php
                    }
                }
                ?>
                </div>
            </td>
        </tr>
        <?php } ?>
        <tr class="tr-except-category tr-except-use">
            <th>
                발급/사용 제외 특정 카테고리 선택
            </th>
            <td colspan="3" class="form-inline">
                <button type="button" id="selectExceptCategory" class="btn btn-sm" title="적용할 카테고리를 선택해주세요.">카테고리선택</button>
                <div id="couponExceptCategory">
                <?php
                if ($couponData['couponExceptCategoryType'] == 'y' && $couponData['couponExceptCategory']) {
                    foreach ($couponData['couponExceptCategory'] as $k => $v) {
                ?>
                    <span id="idExceptCategory_<?= $couponData['couponExceptCategory'][$k]['no'] ?>" class="btn-group btn-group-xs">
                        <input type="hidden" name="couponExceptCategory[]" value="<?= $couponData['couponExceptCategory'][$k]['no'] ?>"/>
                        <button type="button" class="btn btn-default" name="couponExceptCategoryName[]"><?= $couponData['couponExceptCategory'][$k]['name'] ?></button>
                        <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#idExceptCategory_<?= $couponData['couponExceptCategory'][$k]['no'] ?>">삭제</button>
                    </span>
                <?php
                    }
                }
                ?>
                </div>
            </td>
        </tr>
        <tr class="tr-except-brand tr-except-use">
            <th>
                발급/사용 제외 특정 브랜드 선택
            </th>
            <td colspan="3" class="form-inline">
                <button type="button" id="selectExceptBrand" class="btn btn-sm" title="적용할 브랜드을 선택해주세요.">브랜드선택</button>
                <div id="couponExceptBrand">
                <?php
                if ($couponData['couponExceptBrandType'] == 'y' && $couponData['couponExceptBrand']) {
                    foreach ($couponData['couponExceptBrand'] as $k => $v) {
                ?>
                    <span id="idExceptBrand_<?= $couponData['couponExceptBrand'][$k]['no'] ?>" class="btn-group btn-group-xs">
                        <input type="hidden" name="couponExceptBrand[]" value="<?= $couponData['couponExceptBrand'][$k]['no'] ?>"/>
                        <button type="button" class="btn btn-default" name="couponExceptBrandName[]"><?= $couponData['couponExceptBrand'][$k]['name'] ?></button>
                        <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#idExceptBrand_<?= $couponData['couponExceptBrand'][$k]['no'] ?>">삭제</button>
                    </span>
                <?php
                    }
                }
                ?>
                </div>
            </td>
        </tr>
        <tr class="tr-except-goods tr-except-use">
            <th>
                발급/사용 제외 특정 상품 선택
            </th>
            <td colspan="3" class="form-inline">
                <button type="button" id="selectExceptGoods" class="btn btn-sm" title="적용할 상품을 선택해주세요.">상품선택</button>
                <table id="couponExceptGoods" class="table table-cols">
                    <thead>
                    <tr>
                        <th>번호</th>
                        <th>이미지</th>
                        <th>상품명</th>
                        <th>삭제</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($couponData['couponExceptGoodsType'] == 'y' && $couponData['couponExceptGoods']) {
                        foreach ($couponData['couponExceptGoods'] as $key => $val) {
                            echo '<tr id="idExceptGoods_' . $val['goodsNo'] . '">' . chr(10);
                            echo '<td>' . ($key + 1) . '<input type="hidden" name="couponExceptGoods[]" value="' . $val['goodsNo'] . '" /></td>' . chr(10);
                            echo '<td>' . gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank') . '</td>' . chr(10);
                            echo '<td>' . gd_remove_tag($val['goodsNm']) . '</td>' . chr(10);
                            echo '<td><span class="button gray small"><input type="button" onclick="field_remove(\'idExceptGoods_' . $val['goodsNo'] . '\');" value="삭제" /></span></td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">
                            <input type="button" value="전체삭제" onclick="$('#couponExceptGoods tbody').html('');">
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        <tr class="tr_gift">
            <th>
                최소 상품구매금액 제한
            </th>
            <td class="form-inline" colspan="3">
                구매금액이 <?= gd_currency_symbol(); ?>
                <input type="text" name="couponMinOrderPrice" value="<?= gd_money_format($couponData['couponMinOrderPrice'], false); ?>" class="form-control width-xs" maxlength="8"/><?= gd_currency_string(); ?> 이상인 경우 결제 시 사용 가능
                <div class="pdt10" id="couponProductMinOrderType" style="display: none;">
                    구매금액 기준 옵션 :
                    <label class="radio-inline">
                        <input type="radio" name="couponProductMinOrderType" value="product" <?= $checked['couponProductMinOrderType']['product'] ?> />해당 상품 금액
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponProductMinOrderType" value="order" <?= $checked['couponProductMinOrderType']['order'] ?> />해당 상품이 포함된 주문 전체상품금액
                    </label>
                    <div class="notice-info">"주문 전체상품금액" 기준 처리 시, 해당 쿠폰의 사용 가능한 범위 내 상품에 대해서만 금액을 산정합니다.</div>
                    <div class="notice-info">
                        "주문 전체상품금액" 기준은 상품쿠폰을 주문서페이지에서 사용 "제한안함"으로 한 경우에만 적용됩니다.<br/>
                        "제한함"일 경우 해당 설정을 하더라도 "해당 상품 금액" 기준으로 처리되므로 유의하시기 바랍니다.
                    </div>
                    <?php
                    if ($productCouponChangeLimitVersionFl == true) {
                    ?>
                    <div class="notice-info changeLimitVersion" style="display:none;">
                        <span class="c-gdred">
                            해당 기능은 반드시 스킨패치를 적용하셔야 합니다. (미적용 시 일부 기능이 정상적이지 않을 수 있습니다.)
                        </span>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr class="tr_gift">
            <th>
                같은 유형의 쿠폰과 중복사용 여부
            </th>
            <td class="form-inline" colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="couponApplyDuplicateType" value="y" <?= $checked['couponApplyDuplicateType']['y'] ?> />중복사용 가능
                </label>
                <label class="radio-inline">
                    <input type="radio" name="couponApplyDuplicateType" value="n" <?= $checked['couponApplyDuplicateType']['n'] ?> />중복사용 불가
                </label>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#frmCoupon").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                mode: {
                    required: true,
                },
                couponKind: {
                    required: true,
                },
                couponType: {
                    required: true,
                },
                couponUseType: {
                    required: true,
                },
                couponNm: {
                    required: true,
                    maxlength: 30,
                },
                couponUsePeriodStartDate: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=couponUsePeriodType]:checked').val() == 'period') {
                            required = true;
                        }
                        return required;
                    }
                },
                couponUsePeriodEndDate: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=couponUsePeriodType]:checked').val() == 'period') {
                            required = true;
                        }
                        return required;
                    }
                },
                couponUsePeriodDay: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=couponUsePeriodType]:checked').val() == 'day') {
                            required = true;
                        }
                        return required;
                    }
                },
                couponBenefit: {
                    required: true,
                    min: 1,
                    maxlength: function () {
                        if ($('[name="couponBenefitType"]').val() == 'fix') {
                            var max = 8;
                        } else if ($('[name="couponBenefitType"]').val() == 'percent') {
                            var max = 3;
                        }
                        return max;
                    },
                },
                couponAuthNumber: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=couponAuthType]:checked').val() == 'n') {
                            required = true;
                        }
                        return required;
                    },
                    minlength: {
                        param:8,
                        depends: function (element) {
                            return $('[name=couponAuthType]:checked').val() == 'n';
                        }
                    },
                    maxlength: {
                        param:12,
                        depends: function (element) {
                            return $('[name=couponAuthType]:checked').val() == 'n';
                        }
                    }
                }
            },
            messages: {
                mode: {
                    required: '정상 접속이 아닙니다.(mode)',
                },
                couponKind: {
                    required: '정상 접속이 아닙니다.(kind)',
                },
                couponType: {
                    required: '정상 접속이 아닙니다.(type)',
                },
                couponUseType: {
                    required: '쿠폰유형이 선택하세요.',
                },
                couponNm: {
                    required: '쿠폰명을 입력하세요.',
                    maxlength: '쿠폰명의 길이는 최대 30자 입니다.(!)',
                },
                couponUsePeriodStartDate: {
                    required: '사용기간 시작일을 입력하세요.',
                },
                couponUsePeriodEndDate: {
                    required: '사용기간 종료일을 입력하세요.',
                },
                couponUsePeriodDay: {
                    required: '사용기간 일자를 입력하세요.',
                },
                couponBenefit: {
                    required: '제공할 할인/적립 혜택금액을 입력해주세요.',
                    min: '제공할 할인/적립 혜택금액을 입력해주세요.',
                    maxlength: function () {
                        if ($('[name="couponBenefitType"]').val() == 'fix') {
                            var max = '정액은 8자를 넘을 수 없습니다.';
                        } else if ($('[name="couponBenefitType"]').val() == 'percent') {
                            var max = '정률(%)의 경우 숫자 100까지 입력하실 수 있습니다.';
                        }
                        return max;
                    },
                },
                couponAuthNumber: {
                    required: '쿠폰인증번호를 입력하세요.',
                    minlength: '쿠폰인증번호는 8자 ~ 12자 이하입니다.',
                    maxlength: '쿠폰인증번호는 8자 ~ 12자 이하입니다.',
                }
            }
        });
        // 사용구분 선택 시
        $('input:radio[name="couponUseType"]').click(function (e) {
            changeCouponUseType();
            changeCouponKindType();
            changeCouponBenefitType();
            changeCouponAuthType();
            changeCouponApplyProductType();
            changeCouponExceptProductType();
        });
        // 해당구분 선택 시
        $('input:radio[name="couponKindType"]').click(function (e) {
            changeCouponKindType();
            changeCouponBenefitType();
            changeCouponAuthType();
            changeCouponApplyProductType();
            changeCouponExceptProductType();
        });
        // 혜택금액설정에서 정액,정율 선택 시
        $('select[name="couponBenefitType"]').click(function (e) {
            changeCouponBenefitType();
            changeCouponAuthType();
            changeCouponApplyProductType();
            changeCouponExceptProductType();
        });
        // 쿠폰인증번호 타입
        $('input:radio[name="couponAuthType"]').click(function (e) {
            changeCouponAuthType();
            changeCouponApplyProductType();
            changeCouponExceptProductType();
        });
        // 쿠폰 적용 범위 설정 선택 시
        $('input:radio[name="couponApplyProductType"]').click(function (e) {
            changeCouponApplyProductType();
        });
        // 쿠폰 제외 범위 설정 선택 시
        $('input:checkbox[name^="couponExcept"]').click(function (e) {
            changeCouponExceptProductType();
        });
        // 쿠폰 적용 해당 버튼 선택 시
        $('[id^=selectApply]').click(function (e) {
            var code = (this.id).split('selectApply');
            code = code[1];
            layer_register(code);
        });
        // 쿠폰 제외 해당 버튼 선택 시
        $('[id^=selectExcept]').click(function (e) {
            var code = (this.id).split('selectExcept');
            code = code[1];
            layer_register(code, 'except');
        });
        // 인증코드 중복확인
        $("#overlap_authnumber").click(function (e) {
            checkOfflineNumber();
        });
        changeCouponUseType();
        changeCouponKindType();
        changeCouponBenefitType();
        changeCouponAuthType();
        changeCouponApplyProductType();
        changeCouponExceptProductType();
        <?php
        if ($authCode == 'authCode') {
        ?>
        layerCouponAuth('<?= $couponData['couponNo']; ?>');
        <?php
        }
        ?>
        <!--{ ? productCouponChangeLimitVersionFl == true}-->
        $('input[name="couponProductMinOrderType"]').change(function (e) {
            if($('input:radio[name="couponProductMinOrderType"]:checked').val() == 'order') {
                $('.changeLimitVersion').show();
            } else {
                $('.changeLimitVersion').hide();
            }
        });
        <!--{ / }-->

    });
    // 사용구분에 따른 폼 변경
    function changeCouponUseType() {
        if ($('input:radio[name="couponUseType"]:checked').val() == 'product') {
            // 혜택구분
            $('input:radio[name="couponKindType"]:eq(0)').prop("disabled", false);
            if ($('input:radio[name="couponKindType"]:checked').val() == 'delivery') {
                $('input:radio[name="couponKindType"]:eq(0)').prop("checked", true);
            }
            $('input:radio[name="couponKindType"]:eq(1)').prop("disabled", false);
            $('input:radio[name="couponKindType"]:eq(2)').prop("disabled", true);
            $('input:radio[name="couponKindType"]:eq(3)').prop("disabled", true);
            $('#labelCouponKindTypeSale').show();
            if ($('#labelCouponKindTypeSale').hasClass('radio-inline') == false) {
                $('#labelCouponKindTypeSale').addClass('radio-inline');
            }
            $('#labelCouponKindTypeAdd').show();
            if ($('#labelCouponKindTypeAdd').hasClass('radio-inline') == false) {
                $('#labelCouponKindTypeAdd').addClass('radio-inline');
            }
            $('#labelCouponKindTypeDelivery').hide();
            $('#labelCouponKindTypeDeposit').hide();
            $('#labelCouponKindTypeDepositInfo').hide();

            // 혜택금액종류
            $('select[name="couponBenefitType"] option:eq(0)').prop("disabled", false);
            $('select[name="couponBenefitType"] option:eq(1)').prop("disabled", false);
            $('select[name="couponBenefitType"]').show();
            // 제한조건 설정 - 구매관련보이기
            $('.tr_gift').show();
            // 쿠폰 적용 범위 설정
            $('.tr-apply-use').show();
            $('.tr-apply-use input').prop("disabled", false);
            // 쿠폰 제한 범위 설정
            $('.tr-except-use').show();
            $('.tr-except-use input').prop("disabled", false);
            // 구매금액 기준 옵션 상품금액 OR 주문전체상품금액
            $('#couponProductMinOrderType').show();
            if($('input:radio[name="couponProductMinOrderType"]:checked').val() == 'order') {
                $('.changeLimitVersion').show();
            } else {
                $('.changeLimitVersion').hide();
            }
        } else if ($('input:radio[name="couponUseType"]:checked').val() == 'order') {
            // 혜택구분
            $('input:radio[name="couponKindType"]:eq(0)').prop("disabled", false);
            if ($('input:radio[name="couponKindType"]:checked').val() == 'delivery') {
                $('input:radio[name="couponKindType"]:eq(0)').prop("checked", true);
            }
            $('input:radio[name="couponKindType"]:eq(1)').prop("disabled", false);
            $('input:radio[name="couponKindType"]:eq(2)').prop("disabled", true);
            $('input:radio[name="couponKindType"]:eq(3)').prop("disabled", true);
            $('#labelCouponKindTypeSale').show();
            if ($('#labelCouponKindTypeSale').hasClass('radio-inline') == false) {
                $('#labelCouponKindTypeSale').addClass('radio-inline');
            }
            $('#labelCouponKindTypeAdd').show();
            if ($('#labelCouponKindTypeAdd').hasClass('radio-inline') == false) {
                $('#labelCouponKindTypeAdd').addClass('radio-inline');
            }
            $('#labelCouponKindTypeDelivery').hide();
            $('#labelCouponKindTypeDeposit').hide();
            $('#labelCouponKindTypeDepositInfo').hide();

            // 혜택금액종류
            $('select[name="couponBenefitType"] option:eq(0)').prop("disabled", false);
            $('select[name="couponBenefitType"] option:eq(0)').prop("selected", true);
            $('select[name="couponBenefitType"] option:eq(1)').prop("disabled", true);
            $('select[name="couponBenefitType"]').hide();

            // 쿠폰 적용 / 제한 범위 초기화
            $('input:radio[name="couponApplyProductType"]:eq(0)').prop("checked", true);
            $('input:checkbox[name="couponExceptProviderType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptCategoryType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptBrandType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptGoodsType"]').prop("checked", false);
            // 제한조건 설정 - 구매관련보이기
            $('.tr_gift').show();
            // 쿠폰 적용 범위 설정
            $('.tr-apply-use').hide();
            $('.tr-apply-use input').prop("disabled", true);
            // 쿠폰 제한 범위 설정
            $('.tr-except-use').hide();
            $('.tr-except-use input').prop("disabled", true);
            // 구매금액 기준 옵션 상품금액 OR 주문전체상품금액
            $('#couponProductMinOrderType').hide();
        } else if ($('input:radio[name="couponUseType"]:checked').val() == 'delivery') {
            // 혜택구분
            $('input:radio[name="couponKindType"]:eq(0)').prop("disabled", true);
            $('input:radio[name="couponKindType"]:eq(1)').prop("disabled", true);
            $('input:radio[name="couponKindType"]:eq(2)').prop("disabled", false);
            $('input:radio[name="couponKindType"]:eq(2)').prop("checked", true);
            $('input:radio[name="couponKindType"]:eq(3)').prop("disabled", true);
            $('#labelCouponKindTypeSale').hide();
            $('#labelCouponKindTypeSale').removeClass('radio-inline');
            $('#labelCouponKindTypeAdd').hide();
            $('#labelCouponKindTypeAdd').removeClass('radio-inline');
            $('#labelCouponKindTypeDelivery').show();
            if ($('#labelCouponKindTypeDelivery').hasClass('radio-inline') == false) {
                $('#labelCouponKindTypeDelivery').addClass('radio-inline');
            }
            $('#labelCouponKindTypeDeposit').hide();
            $('#labelCouponKindTypeDepositInfo').hide();

            // 혜택금액종류
            $('select[name="couponBenefitType"] option:eq(0)').prop("disabled", true);
            $('select[name="couponBenefitType"] option:eq(1)').prop("disabled", false);
            $('select[name="couponBenefitType"] option:eq(1)').prop("selected", true);
            $('select[name="couponBenefitType"]').hide();

            // 쿠폰 적용 / 제한 범위 초기화
            $('input:radio[name="couponApplyProductType"]:eq(0)').prop("checked", true);
            $('input:checkbox[name="couponExceptProviderType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptCategoryType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptBrandType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptGoodsType"]').prop("checked", false);
            // 제한조건 설정 - 구매관련보이기
            $('.tr_gift').show();
            // 쿠폰 적용 범위 설정
            $('.tr-apply-use').hide();
            $('.tr-apply-use input').prop("disabled", true);
            // 쿠폰 제한 범위 설정
            $('.tr-except-use').hide();
            $('.tr-except-use input').prop("disabled", true);
            // 구매금액 기준 옵션 상품금액 OR 주문전체상품금액
            $('#couponProductMinOrderType').hide();
        } else if ($('input:radio[name="couponUseType"]:checked').val() == 'gift') {
            // 혜택구분
            $('input:radio[name="couponKindType"]:eq(0)').prop("disabled", true);
            $('input:radio[name="couponKindType"]:eq(1)').prop("disabled", false);
            $('input:radio[name="couponKindType"]:eq(2)').prop("disabled", true);
            $('input:radio[name="couponKindType"]:eq(3)').prop("disabled", false);
            if ($('input:radio[name="couponKindType"]:checked').val() != 'deposit') {
                $('input:radio[name="couponKindType"]:eq(1)').prop("checked", true);
            }

            $('#labelCouponKindTypeSale').hide();
            $('#labelCouponKindTypeSale').removeClass('radio-inline');
            $('#labelCouponKindTypeAdd').show();
            if ($('#labelCouponKindTypeAdd').hasClass('radio-inline') == false) {
                $('#labelCouponKindTypeAdd').addClass('radio-inline');
            }
            $('#labelCouponKindTypeDelivery').hide();
            $('#labelCouponKindTypeDeposit').show();
            if ($('#labelCouponKindTypeDeposit').hasClass('radio-inline') == false) {
                $('#labelCouponKindTypeDeposit').addClass('radio-inline');
            }
            $('#labelCouponKindTypeDepositInfo').show();

            // 혜택금액종류
            $('select[name="couponBenefitType"] option:eq(0)').prop("disabled", true);
            $('select[name="couponBenefitType"] option:eq(1)').prop("disabled", false);
            $('select[name="couponBenefitType"] option:eq(1)').prop("selected", true);
            $('select[name="couponBenefitType"]').hide();

            // 쿠폰 적용 / 제한 범위 초기화
            $('input:radio[name="couponApplyProductType"]:eq(0)').prop("checked", true);
            $('input:checkbox[name="couponExceptProviderType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptCategoryType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptBrandType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptGoodsType"]').prop("checked", false);
            // 제한조건 설정 - 구매관련숨기기
            $('.tr_gift').hide();
            // 쿠폰 적용 범위 설정
            $('.tr-apply-use').hide();
            $('.tr-apply-use input').prop("disabled", true);
            // 쿠폰 제한 범위 설정
            $('.tr-except-use').hide();
            $('.tr-except-use input').prop("disabled", true);
            // 구매금액 기준 옵션 상품금액 OR 주문전체상품금액
            $('#couponProductMinOrderType').hide();
        }
    }
    // 혜택구분에 따른 폼 변경
    function changeCouponKindType() {
        if ($('input:radio[name="couponKindType"]:checked').val() == 'add') {
            if ($('input:radio[name="couponUseType"]:checked').val() == 'product') {
                $('.benefit-text').text('적립');
            } else {
                if ($('select[name="couponBenefitType"]').val() == 'percent') {
                    $('.benefit-text').text('% 적립');
                } else {
                    $('.benefit-text').text('원 적립');
                }
            }
        } else if ($('input:radio[name="couponKindType"]:checked').val() == 'deposit') {
            if ($('input:radio[name="couponUseType"]:checked').val() == 'product') {
                $('.benefit-text').text('지급');
            } else {
                if ($('select[name="couponBenefitType"]').val() == 'percent') {
                    $('.benefit-text').text('% 지급');
                } else {
                    $('.benefit-text').text('원 지급');
                }
            }
        } else {
            if ($('input:radio[name="couponUseType"]:checked').val() == 'product') {
                $('.benefit-text').text('할인');
            } else {
                if ($('select[name="couponBenefitType"]').val() == 'percent') {
                    $('.benefit-text').text('% 할인');
                } else {
                    $('.benefit-text').text('원 할인');
                }
            }
        }
    }
    // 혜택금액종류에 따른 폼 변경
    function changeCouponBenefitType() {
        // 쿠폰적용범위설정에 따른 폼 변경
        if ($('select[name="couponBenefitType"]').val() == 'percent') {
            $('input:checkbox[name="couponMaxBenefitType"]').prop("disabled", false);
            $('input:text[name="couponMaxBenefit"]').prop("disabled", false);
            $('input:text[name="couponBenefit"]').removeAttr('maxlength');
            $('input:text[name="couponBenefit"]').attr('maxlength', '3');
            $('#benefittype_text').show();
            $('.div-benefit').show();
        } else {
            $('input:checkbox[name="couponMaxBenefitType"]').prop("disabled", true);
            $('input:text[name="couponMaxBenefit"]').prop("disabled", true);
            $('input:text[name="couponBenefit"]').removeAttr('maxlength');
            $('input:text[name="couponBenefit"]').attr('maxlength', '8');
            $('#benefittype_text').hide();
            $('.div-benefit').hide();
        }
    }
    function changeCouponAuthType() {
        if ($('input:radio[name="couponAuthType"]:checked').val() == 'n') {// 1개의 인증번호 사용
            $('.tr-direct').show();
            $('.tr-direct input').prop("disabled", false);
        } else if ($('input:radio[name="couponAuthType"]:checked').val() == 'y') {// 회원별로 다른 인증번호 사용
            $('.tr-direct').hide();
            $('.tr-direct input').prop("disabled", true);
        }
    }
    // 쿠폰 발급/사용 가능 범위 설정에 따른 폼 변경
    function changeCouponApplyProductType() {
        if ($('input:radio[name="couponApplyProductType"]:checked').val() == 'all') {
            $('.tr-apply-provider').hide();
            $('.tr-apply-category').hide();
            $('.tr-apply-brand').hide();
            $('.tr-apply-goods').hide();
            $('input:checkbox[name="couponExceptProviderType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptCategoryType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptBrandType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptGoodsType"]').prop("disabled", false);
        } else if ($('input:radio[name="couponApplyProductType"]:checked').val() == 'provider') {
            $('.tr-apply-provider').show();
            $('.tr-apply-category').hide();
            $('.tr-apply-brand').hide();
            $('.tr-apply-goods').hide();
            $('input:checkbox[name="couponExceptProviderType"]').prop("disabled", true);
            $('input:checkbox[name="couponExceptProviderType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptCategoryType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptBrandType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptGoodsType"]').prop("disabled", false);
        } else if ($('input:radio[name="couponApplyProductType"]:checked').val() == 'category') {
            $('.tr-apply-provider').hide();
            $('.tr-apply-category').show();
            $('.tr-apply-brand').hide();
            $('.tr-apply-goods').hide();
            $('input:checkbox[name="couponExceptProviderType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptCategoryType"]').prop("disabled", true);
            $('input:checkbox[name="couponExceptCategoryType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptBrandType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptGoodsType"]').prop("disabled", false);
        } else if ($('input:radio[name="couponApplyProductType"]:checked').val() == 'brand') {
            $('.tr-apply-provider').hide();
            $('.tr-apply-category').hide();
            $('.tr-apply-brand').show();
            $('.tr-apply-goods').hide();
            $('input:checkbox[name="couponExceptProviderType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptCategoryType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptBrandType"]').prop("disabled", true);
            $('input:checkbox[name="couponExceptBrandType"]').prop("checked", false);
            $('input:checkbox[name="couponExceptGoodsType"]').prop("disabled", false);
        } else if ($('input:radio[name="couponApplyProductType"]:checked').val() == 'goods') {
            // 쿠폰적용제한범위설정에 따른 폼 변경
            $('.tr-apply-provider').hide();
            $('.tr-apply-category').hide();
            $('.tr-apply-brand').hide();
            $('.tr-apply-goods').show();
            $('input:checkbox[name="couponExceptProviderType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptCategoryType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptBrandType"]').prop("disabled", false);
            $('input:checkbox[name="couponExceptGoodsType"]').prop("disabled", true);
            $('input:checkbox[name="couponExceptGoodsType"]').prop("checked", false);
        }
        changeCouponExceptProductType();
    }
    // 쿠폰 발급/사용 제외 설정에 따른 폼 변경
    function changeCouponExceptProductType() {
        if ($('input:checkbox[name="couponExceptGoodsType"]').prop("checked") == true) {
            $('.tr-except-goods').show();
        } else {
            $('.tr-except-goods').hide();
        }
        if ($('input:checkbox[name="couponExceptBrandType"]').prop("checked") == true) {
            $('.tr-except-brand').show();
        } else {
            $('.tr-except-brand').hide();
        }
        if ($('input:checkbox[name="couponExceptCategoryType"]').prop("checked") == true) {
            $('.tr-except-category').show();
        } else {
            $('.tr-except-category').hide();
        }
        if ($('input:checkbox[name="couponExceptProviderType"]').prop("checked") == true) {
            $('.tr-except-provider').show();
        } else {
            $('.tr-except-provider').hide();
        }
    }
    function checkOfflineNumber() {
        var couponAuthNumber = $('input:text[name="couponAuthNumber"]').val();
        if(couponAuthNumber) {
            $.ajax({
                method: "POST",
                cache: false,
                url: "../promotion/coupon_ps.php",
                data: "mode=checkOfflineCode&couponOfflineCode=" + couponAuthNumber,
                dataType: 'json'
            }).success(function (data) {
                console.log(data);
                alert(data['msg']);
                if (data['result'] == 'ok') {
                    $('input:hidden[name="duplicateCouponAuthNumber"]').val(couponAuthNumber);
                }
            }).error(function (e) {
                alert(e.responseText);
            });
        } else {
            alert('인증번호를 입력하세요.');
        }
    }

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string codeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_register(codeStr, modeStr, isDisabled) {
        var layerFormID = 'couponRangeForm';
        var addParam = '';
        var fileStr = '';
        if (typeof modeStr == 'undefined') {
            // 레이어 창
            var parentFormID = 'couponApply' + codeStr;
            var dataFormID = 'id' + codeStr;
            var dataInputNm = 'couponApply' + codeStr;
            var layerTitle = '쿠폰 적용 ';
        } else {
            var parentFormID = 'couponExcept' + codeStr;
            var dataFormID = 'idExcept' + codeStr;
            var dataInputNm = 'couponExcept' + codeStr;
            var layerTitle = '쿠폰 제외 ';
        }

        if (codeStr == 'MemberGroup') {
            layerTitle = layerTitle + '회원등급';

            fileStr = 'member_group';
            mode = 'search';
            $("#" + parentFormID + " thead").show();
            $("#" + parentFormID + " tfoot").show();
        }
        if (codeStr == 'Goods') {
            layerTitle = layerTitle + '상품';
            fileStr = 'goods';
            mode = 'simple';
            $("#" + parentFormID + " thead").show();
            $("#" + parentFormID + " tfoot").show();
        }
        if (codeStr == 'Category') {
            layerTitle = layerTitle + '카테고리';
            fileStr = 'category';
            mode = 'search';
            $("#" + parentFormID + " thead").show();
            $("#" + parentFormID + " tfoot").show();
        }
        if (codeStr == 'Brand') {
            layerTitle = layerTitle + '브랜드';
            fileStr = 'brand';
            mode = 'search';
            $("#" + parentFormID + " thead").show();
            $("#" + parentFormID + " tfoot").show();
        }
        if (codeStr == 'Provider') {
            layerTitle = layerTitle + '공급사';
            fileStr = 'scm';
            isDisabled = 'disabled';
            mode = 'search';
            $("#" + parentFormID + " thead").show();
            $("#" + parentFormID + " tfoot").show();
        }

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
            "disabled": isDisabled,
//            "callFunc": "",
        };
        layer_add_info(fileStr, addParam);
    }

    // 여신전문금융업법 안내
    function lawAlert() {
        var message = '';
        message += '<b style="color: #0070c0;">제19조(가맹점의 준수사항)</b><br/>';
        message += '① 신용카드가맹점은 신용카드로 거래한다는 이유로 신용카드 결제를 거절하거나 신용카드회원을 불리하게 대우하지 못한다.<br/>';
        message += '② 신용카드가맹점은 신용카드로 가맹점수수료를 신용카드회원이 부담하게 하여서는 아니 된다.<br/><br/>';
        message += '<b style="color: #0070c0;">제70조(벌칙)</b><br/>';
        message += '① 다음 각 호의 어느 하나에 해당하는 자는 7년 이하의 징역 또는 5천만원 이하의 벌금에 처한다.<br/>';
        message += '4. 제19조 제1항을 위반하여 신용카드로 거래한다는 이유로 물품의 판매 또는 용역의 제공 등을 거절하거나 신용카드회원을 불리하게 대우한 자<br/>';
        message += '5. 제19조 제4항을 위반하여 가맹점수수료를 신용카드회원이 부담하게 한 자<br/>';

        dialog_alert(message, '여신전문금융업법 안내');
    }
</script>
