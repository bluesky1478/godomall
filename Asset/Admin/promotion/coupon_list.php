<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <a href="coupon_regist.php" class="btn btn-red-line">쿠폰 등록</a>
    </div>
</div>

<h5 class="table-title gd-help-manual">쿠폰 검색</h5>
<form id="frmSearchCoupon" method="get" class="js-form-enter-submit">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md">
                <col>
                <col class="width-md">
                <col>
            </colgroup>
            <tbody>
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
                <th>쿠폰유형</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="" <?= $checked['couponUseType']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="product" <?= $checked['couponUseType']['product']; ?>/>상품적용쿠폰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="order" <?= $checked['couponUseType']['order']; ?>/>주문적용쿠폰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="delivery" <?= $checked['couponUseType']['delivery']; ?>/>배송비적용쿠폰
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="gift" <?= $checked['couponUseType']['gift']; ?>/>기프트쿠폰
                    </label>
                </td>
            </tr>
            <tr>
                <th>발급방식</th>
                <td colspan="3">
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
            </tr>
            <tr>
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
                <th>혜택구분</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="couponKindType" value="" <?= $checked['couponKindType']['']; ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponKindType" value="sale" <?= $checked['couponKindType']['sale']; ?>/>상품할인
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponKindType" value="add" <?= $checked['couponKindType']['add']; ?>/>마일리지적립
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponKindType" value="delivery" <?= $checked['couponKindType']['delivery']; ?>/>배송비할인
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="couponKindType" value="deposit" <?= $checked['couponKindType']['deposit']; ?>/>예치금지급
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
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null, 'onchange="this.form.submit();"'); ?>
                <?php echo gd_select_box(
                    'pageNum', 'pageNum', gd_array_change_key_value(
                    [
                        10,
                        20,
                        30,
                        40,
                        50,
                        60,
                        70,
                        80,
                        90,
                        100,
                        200,
                        300,
                        500,
                    ]
                ), '개 보기', Request::get()->get('pageNum'), null, 'onchange="this.form.submit();"'
                ); ?>
            </div>
        </div>
    </div>
</form>

<form id="frmCouponList" action="../promotion/coupon_ps.php" method="post">
    <input type="hidden" name="mode" value="deleteCouponList"/>
    <table class="table table-rows promotion-coupon-list">
        <thead>
        <tr>
            <th><input type="checkbox" class="js-checkall" data-target-name="chkCoupon[]"/></th>
            <th>번호</th>
            <th>쿠폰명</th>
            <th>등록일</th>
            <th>등록자</th>
            <th>사용기간</th>
            <th>쿠폰유형</th>
            <th>사용범위</th>
            <th>혜택구분</th>
            <th>발급방식</th>
            <th>발급수</th>
            <th>발급상태</th>
            <th>발급내역관리</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false && is_array($data)) {
            foreach ($data as $key => $val) {
                // @todo controller에서 처리해서 가져오자 - su
                $couponStartStopAction = '';
                $couponSaveAction = '';
                if ($val['couponSaveType'] == 'manual') {
                    if ($val['couponType'] == 'y') {
                        $couponSaveAction = '<br/><button type="button" class="save-coupon-type btn btn-sm btn-gray" data-type="y" data-no="' . $val['couponNo'] . '">발급</button>';
                    }
                } else if ($val['couponSaveType'] == 'down') {
                    if ($val['couponType'] == 'y') {
                        $couponSaveAction = '<br/><button type="button" title="' . $val['couponNm'] . ' 다운로드 링크" class="btn btn-sm btn-gray js-clipboard" data-no="' . $val['couponNo'] . '" data-clipboard-text="javascript:couponLinkDown(\'' . $val['couponNo'] . '\');">링크복사</button>';
                        $couponStartStopAction = '<br/><button type="button" class="change-coupon-type btn btn-sm btn-gray" data-type="n" data-no="' . $val['couponNo'] . '">발급중지</button>';
                    } else if ($val['couponType'] == 'n') {
                        $couponStartStopAction = '<br/><button type="button" class="change-coupon-type btn btn-sm btn-gray" data-type="y" data-no="' . $val['couponNo'] . '">발급시작</button>';
                    }
                } else {
                    if ($val['couponType'] == 'y') {
                        $couponStartStopAction = '<br/><button type="button" class="change-coupon-type btn btn-sm btn-gray" data-type="n" data-no="' . $val['couponNo'] . '">발급중지</button>';
                    } else if ($val['couponType'] == 'n') {
                        $couponStartStopAction = '<br/><button type="button" class="change-coupon-type btn btn-sm btn-gray" data-type="y" data-no="' . $val['couponNo'] . '">발급시작</button>';
                    }
                }
                ?>
                <tr class="text-center">
                    <td>
                        <input type="checkbox" name="chkCoupon[]" value="<?= $val['couponNo'] ?>" <?= $countMemberCouponArrData[$val['couponNo']] > 0 ? 'disabled="disabled"' : '' ?>/>
                    </td>
                    <td><?= number_format($page->idx--); ?></td>
                    <td><?= $val['couponNm'] ?></td>
                    <td><?= gd_date_format('Y-m-d', $val['regDt']) ?></td>
                    <td><?= $val['couponInsertAdminId'].$val['deleteText'] ?></td>
                    <td><?= $convertArrData[$key]['useEndDate'] ?></td>
                    <td><?= $convertArrData[$key]['couponUseType'] ?></td>
                    <td><?= $convertArrData[$key]['couponDeviceType'] ?></td>
                    <td><?= $convertArrData[$key]['couponKindType'] ?>(<?= $convertArrData[$key]['couponBenefit']; ?>)</td>
                    <td>
                        <?= $convertArrData[$key]['couponSaveType'] ?>
                        <?= $couponSaveAction ?>
                    </td>
                    <td><?= $countMemberCouponArrData[$val['couponNo']] ?></td>
                    <td>
                        <?= $convertArrData[$key]['couponType'] ?>
                        <?= $couponStartStopAction ?>
                    </td>
                    <td>
                        <a href="../promotion/coupon_manage.php?couponNo=<?= $val['couponNo'] ?>&ypage=<?= $page->page['now'] ?>" class="btn btn-sm btn-white">관리</a>
                    </td>
                    <td>
                        <a href="coupon_regist.php?couponNo=<?= $val['couponNo'] ?>&ypage=<?= $page->page['now'] ?>" class="btn btn-sm btn-white">수정</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="14" class="no-data">
                    검색된 쿠폰이 없습니다.
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white js-delete-coupon">선택 삭제</button>
        </div>
    </div>
</form>

<div class="center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmSearchCoupon').validate({
            submitHandler: function (form) {
                form.submit();
            }
        });

        $('#frmCouponList').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'chkCoupon[]': 'required',
            },
            messages: {
                'chkCoupon[]': {
                    required: "삭제할 쿠폰을 선택해 주세요.",
                }
            }
        });

        $('.change-coupon-type').click(function (e) {
            changeCouponType($(this).attr('data-no'), $(this).attr('data-type'));
        });
        $('.save-coupon-type').click(function (e) {
            saveCoupon($(this).attr('data-no'));
        });
        $('.js-delete-coupon').click(function (e) {
            if ($('#frmCouponList').valid()) {
                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_DANGER,
                    title: '쿠폰삭제',
                    message: '선택된 ' + $('input[name*=chkCoupon]:checked').length + '개의 쿠폰을 정말로 삭제 하시겠습니까?<br />삭제 시 정보는 복구 되지 않습니다.',
                    closable: false,
                    callback: function (result) {
                        if (result) {
                            $('#frmCouponList').submit();
                        }
                    }
                });
            }
        });
        $('.js-down-link').click(function (e) {
            $(this).data('no')
        });
    });
    function changeCouponType(couponNo, couponType) {
        $.ajax({
            method: "POST",
            cache: false,
            url: "../promotion/coupon_ps.php",
            data: "mode=modifyCouponList&couponNo=" + couponNo + "&couponType=" + couponType,
            dataType: 'json'
        }).success(function (data) {
            BootstrapDialog.alert({
                type: BootstrapDialog.TYPE_INFO,
                title: '안내',
                message: data['msg'],
                closable: false,
                callback: function (result) {
                    if (result) {
                        location.reload();
                    }
                }
            });
        }).error(function (e) {
            alert(e.responseText);
        });
    }
    function saveCoupon(couponNo) {
        location.href = '../promotion/coupon_save.php?couponNo=' + couponNo;
    }
    //-->
</script>
