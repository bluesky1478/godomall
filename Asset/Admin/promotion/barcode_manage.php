<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
<input type="button" value="바코드 생성" class="btn btn-red-line" id="btnRegister"/>
</div>
<form id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
    <h5 class="table-title gd-help-manual">바코드 검색</h5>
    <div class="search-detail-box form-inline">
        <input type="hidden" name="detailSearch" value="<?= Request::get()->get('detailSearch', '') ?>"/>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md">
                <col>
                <col class="width-md">
                <col>
            </colgroup>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key']); ?>
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>등록일 검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][0]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][1]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date($search['searchPeriod'], 'searchDate', false) ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>쿠폰종류</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="couponKind" value="" <?= $checked['couponKind']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponKind" value="online" <?= $checked['couponKind']['online']; ?>/>일반쿠폰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponKind" value="offline" <?= $checked['couponKind']['offline']; ?>/>페이퍼쿠폰
                    </label>
                </td>
                <th>쿠폰유형</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="" <?= $checked['couponUseType']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="product" <?= $checked['couponUseType']['product']; ?>/>상품적용쿠폰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="order" <?= $checked['couponUseType']['order']; ?>/>주문적용쿠폰
                    </label>
                </td>
            </tr>
            <tr>
                <th>발급방식</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="couponSaveType" value="" <?= $checked['couponSaveType']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponSaveType" value="down" <?= $checked['couponSaveType']['down']; ?>/>회원다운로드
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponSaveType" value="auto" <?= $checked['couponSaveType']['auto']; ?>/>자동발급
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponSaveType" value="manual" <?= $checked['couponSaveType']['manual']; ?>/>수동발급
                    </label>
                </td>
                <th>사용범위</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="couponDeviceType" value="" <?= $checked['couponDeviceType']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponDeviceType" value="all" <?= $checked['couponDeviceType']['all']; ?>/>PC+모바일
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponDeviceType" value="pc" <?= $checked['couponDeviceType']['pc']; ?>/>PC
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponDeviceType" value="mobile" <?= $checked['couponDeviceType']['mobile']; ?>/>모바일
                    </label>
                </td>
            </tr>
            <tr>
                <th>발급상태</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="couponType" value="" <?= $checked['couponType']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponType" value="y" <?= $checked['couponType']['y']; ?>/>발급중
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponType" value="n" <?= $checked['couponType']['n']; ?>/>일시정지
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponType" value="f" <?= $checked['couponType']['f']; ?>/>발급종료
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?= number_format($page->recode['total'], 0); ?></strong>건 / 전체
            <strong><?= number_format($page->recode['amount'], 0); ?></strong>건
        </div>
    </div>
</form>

<table class="table table-rows promotion-coupon-list">
    <thead>
    <tr>
        <th><input type="checkbox" class="js-checkall" data-target-name="barcodeNo[]"/></th>
        <th>번호</th>
        <th>쿠폰종류</th>
        <th>쿠폰명</th>
        <th>등록일</th>
        <th>사용기간</th>
        <th>쿠폰유형</th>
        <th>사용범위</th>
        <th>혜택구분</th>
        <th>발급방식</th>
        <th>발급상태</th>
        <th>오프라인<br />사용 건수</th>
    </tr>
    </thead>
    <tbody>
    <?php
        if (empty($barcodeList) === true) {
    ?>
    <tr class="text-center">
        <td colspan="12">검색된 바코드가 없습니다.</td>
    </tr>
    <?php
        } else {
            foreach ($barcodeList as $data) {
    ?>
                <tr class="text-center">
                    <td><input type="checkbox" class="js-check" name="barcodeNo[]" value="<?= $data['barcodeNo'] ?>"/>
                    </td>
                    <td><?= number_format($page->idx--); ?></td>
                    <td><?= $convertOption['kind'][$data['couponKind']] ?></td>
                    <td><?= $data['couponNm'] ?></td>
                    <td><?= date('Y-m-d', strtotime($data['regDt'])) ?></td>
                    <td>
                        <?php
                        if ($data['couponUsePeriodType'] === 'period') :
                            echo $data['couponUsePeriodStartDate'] . '<br /> ~' . $data['couponUsePeriodEndDate'];
                        else :
                            echo '발급일로부터<br />' . $data['couponUsePeriodDay'] . '일까지';
                        endif;
                        ?>
                    </td>
                    <td><?= $convertOption['useType'][$data['couponUseType']] ?>적용</td>
                    <td><?= $convertOption['deviceType'][$data['couponDeviceType']] ?></td>
                    <td>
                        <?= $convertOption['benefit'][$data['couponKindType']] ?><br/>
                        (<?= number_format($data['couponBenefit'], ($data['couponBenefitType'] == '%') ? 2 : 0) ?><?= $convertOption['benefitUnit'][$data['couponBenefitType']] ?>
                        )
                    </td>
                    <td><?= $convertOption['saveType'][$data['couponSaveType']] ?></td>
                    <td><?= $convertOption['status'][$data['couponType']] ?></td>
                    <td><?= number_format($data['barcodeUsedCount']) ?><?php if ($data['barcodeUsedCount'] > 0) { ?>
                            <br/><a href="#" class="btn btn-sm btn-white btn-barcode-usedlist"
                                    data-bno="<?= $data['barcodeNo'] ?>">발급보기</a><?php } ?></td>
                </tr>
                <?php
            }
        }
    ?>
    </tbody>
</table>
<div class="table-action">
    <div class="pull-left">
        <button type="button" id="btnDeleteBarcode" class="btn btn-white js-delete-coupon">선택 삭제</button>
    </div>
</div>

<div class="center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    // PG 로그 Ajax layer
    $('#btnRegister').click(function (e) {
        $.post('layer_barcode_register.php', {}, function (data) {
            var data = '<div id="barcodeRegisterDiv">' + data + '</div>';
            layer_popup(data, '바코드 생성', 'wide-lg');
        });
    });

    $('.btn-barcode-usedlist').click(function(){
        var bno = $(this).data('bno');
        $.post('layer_barcode_used_list.php', {bno : bno}, function (data) {
            var data = '<div id="barcodeUsedList">' + data + '</div>';
            layer_popup(data, '오프라인 사용 내역', 'wide-lg');
        });
    });

    $('#btnDeleteBarcode').click(function(){
        if ($('input[name="barcodeNo[]"]:checked').length < 1) {
            dialog_alert("삭제할 바코드를 선택해주세요.");
            return false;
        }

        dialog_confirm("선택한 바코드를 삭제하시겠습니까?<br />* 바코드 정보만 삭제 되며, 쿠폰은 삭제되지 않습니다.<br />* 동일한 쿠폰에 대해 삭제 후 다시 생성 시, 바코드 번호는 새로 생성됩니다.", function (result) {
            if (result === true) {
                var params = $('input[name="barcodeNo[]"]').serialize();
                    params += '&mode=delete';
                $.post('barcode_manage_ps.php', params, function (data) {
                    if (data.isSuccess === true) {
                        document.location.reload();
                    }
                });
            }
        });

    });
</script>
