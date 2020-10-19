<form id="frmLayerSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
    <div class="search-detail-box form-inline">
        <input type="hidden" name="detailSearch" value="<?= Request::get()->get('detailSearch', '') ?>"/>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
                <col class="width-sm">
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
                            <input type="text" class="form-control width-xs" name="couponSearchDate[]" value="<?php echo $search['couponSearchDate'][0]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="couponSearchDate[]" value="<?php echo $search['couponSearchDate'][1]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date($search['searchPeriod'], 'couponSearchDate', false) ?>
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
                <td colspan="4" style="border-bottom:0px;">
                    <div class="notice-info">
                        바코드 생성이 가능한 쿠폰만 검색됩니다. 자세한 내용은 [바코드 관리 매뉴얼] 메뉴를 참고해주세요.
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" id="btnSearchCoupon" value="검색" class="btn btn-lg btn-black">
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
        <th>선택</th>
        <th>번호</th>
        <th>쿠폰종류</th>
        <th>쿠폰명</th>
        <th>등록일</th>
        <th>사용기간</th>
        <th>쿠폰유형</th>
        <th>사용범위</th>
        <th>혜택구분</th>
        <th>발급방식</th>
        <th>발급수</th>
        <th>발급상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($couponList) === true) {
        ?>
        <tr class="text-center">
            <td colspan="12">검색된 쿠폰이 없습니다.</td>
        </tr>
        <?php
    } else {
        foreach ($couponList as $data) {
            ?>
            <tr class="text-center">
                <td><input type="radio" name="selectCoupon" value="<?= $data['couponNo'] ?>"></td>
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
                <td><?= $data['couponSaveCount'] ?></td>
                <td><?= $convertOption['status'][$data['couponType']] ?></td>
            </tr>
            <?php
        }
    }
    ?>
    </tbody>
</table>

<div id="layerPagination" class="center"><?= $page->getPage(); ?></div>

<div id="layerPagination" class="center"><input type="button" value="바코드 생성하기" class="btn btn-lg btn-black" id="btnAddBarcode"/></div>


<script type="text/javascript">
    $(function(){
        $('#btnSearchCoupon').click(function(e){
            e.preventDefault();
            var params = $('#frmLayerSearchBase').serialize();
            $.post('layer_barcode_register.php?page=1', params, function (data) {
                $('#barcodeRegisterDiv').html(data);
                return false;
            });
        });

        $('#layerPagination > nav > ul > li > a').click(function(e){
            e.preventDefault();
            var href = $(this).attr('href');
            var pageNum = 1;
            if (href.indexOf('?') !== -1) {
                pageNum = href.split('?');
                pageNum = pageNum[1].split('page=');
                pageNum = pageNum[1].split('&');
                pageNum = pageNum[0];
            }

            var params = $('#frmLayerSearchBase').serialize();
            $.post('layer_barcode_register.php?page=' + pageNum, params, function (data) {
                $('#barcodeRegisterDiv').html(data);
                return false;
            });
        });

        $('#btnAddBarcode').click(function(){
            var couponNo    = $('input:radio[name="selectCoupon"]:checked').val();

            if (typeof couponNo === "undefined") {
                dialog_alert('쿠폰을 선택해주세요.');
                return false;
            }

            $.post('barcode_manage_ps.php', {'couponNo' : couponNo, 'mode' : 'add'}, function (data) {
                var msg = data.msg;
                if (data.isSuccess === true) {
                    dialog_alert('바코드 생성이 완료되었습니다.', '안내', {isReload:true});
                } else {
                    dialog_alert(msg);
                }
                return false;
            }, 'json');
        });
    });

</script>
