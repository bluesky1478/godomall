<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<h5 class="table-title gd-help-manual">쿠폰내용</h5>
<table class="table table-cols">
    <colgroup>
        <col class="width-sm"/>
        <col/>
    </colgroup>
    <tbody>
    <tr>
        <th>쿠폰명</th>
        <td><?= $getData['couponNm']; ?></td>
    </tr>
    <tr>
        <th>쿠폰설명</th>
        <td><?= $getData['couponDescribed']; ?></td>
    </tr>
    <tr>
        <th>사용기간</th>
        <td><?= $getConvertData['useEndDate']; ?></td>
    </tr>
    <tr>
        <th>쿠폰유형</th>
        <td><?= $getConvertData['couponUseType']; ?></td>
    </tr>
    <tr>
        <th>발급구분</th>
        <td><?= $getConvertData['couponSaveType']; ?></td>
    </tr>
    <tr>
        <th>쿠폰혜택</th>
        <td><?php if ($getData['couponBenefitFixApply'] == 'all') { echo '수량별'; } ?> <?= $getConvertData['couponBenefit'] . ' ' . $getConvertData['couponKindType']; ?></td>
    </tr>
    <tr>
        <td colspan="2"><a href="coupon_regist.php?couponNo=<?= $getData['couponNo']; ?>" target="_blank">상세보기></a></td>
    </tr>
    </tbody>
</table>


<h5 class="table-title gd-help-manual">쿠폰 검색</h5>
<form id="frmSearchMemberCoupon" method="get">
    <input type="hidden" name="couponNo" value="<?= $getData['couponNo']; ?>"/>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
                <col class="width-sm">
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
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('keyDate', 'keyDate', $search['combineSearchDate'], null, $search['keyDate']); ?>
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="keywordDate[]" value="<?php echo $search['keywordDate'][0]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="keywordDate[]" value="<?php echo $search['keywordDate'][1]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date($search['searchPeriod'], 'keywordDate', false) ?>
                    </div>
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
            검색 <strong><?= number_format($page->recode['total'], 0); ?></strong>건 /
            전체 <strong><?= number_format($page->recode['amount'], 0); ?></strong>건
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null, 'onchange="this.form.submit();"'); ?>
            </div>
        </div>
    </div>
</form>

<form id="frmMemberCouponList" action="../promotion/coupon_ps.php" method="post">
    <input type="hidden" name="mode" value="deleteCouponManage" />
    <input type="hidden" name="couponNo" value="<?= $getData['couponNo']; ?>" />
    <table class="table table-rows">
        <colgroup>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col class="width-lg">
            <col class="width-lg">
            <col class="width-lg">
            <col class="width-lg">
            <col>
        </colgroup>
        <thead>
        <tr>
            <th><input type="checkbox" class="js-checkall" data-target-name="chkMemberCoupon[]"/></th>
            <th>번호</th>
            <th>아이디</th>
            <th>이름</th>
            <th>등급</th>
            <th>발급일</th>
            <th>만료일</th>
            <th>사용일</th>
            <th>처리자</th>
            <th>쿠폰상태</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($getMemberData) === false && is_array($getMemberData)) {
            foreach ($getMemberData as $key => $val) {
                if($val['memberCouponState'] == 'y') {
                    $memberCouponUseText = '미사용';
                    $usedDt = '-';
                } else if ($val['memberCouponState'] == 'cart') {
                    $memberCouponUseText = '장바구니사용';
                    $usedDt = '-';
                } else {
                    $memberCouponUseText = '사용';
                    $usedDt = gd_date_format('Y-m-d H:i:s', $val['memberCouponUseDate']);
                }
                $tmpArrSaveAdminId = explode(INT_DIVISION, $val['couponSaveAdminId']);

                ?>
                <tr class="text-center">
                    <td>
                        <input type="checkbox" name="chkMemberCoupon[]" value="<?= $val['memberCouponNo'] ?>" <?= ($val['memberCouponState'] == 'order' || $val['memberCouponState'] == 'coupon') ? 'disabled="disabled"' : '' ?> />
                    </td>
                    <td><?= number_format($page->idx--); ?></td>
                    <td><span class="mgr5"><?= $val['memId']; ?></span><?= gd_get_third_party_icon_web_path($val['snsTypeFl']); ?></td>
                    <td><?= $val['memNm']; ?></td>
                    <td><?= gd_isset($getGroupData[$val['groupSno']]); ?></td>
                    <td><?= gd_date_format('Y-m-d H:i:s', $val['regDt']); ?></td>
                    <td><?= gd_date_format('Y-m-d H:i:s', $val['memberCouponEndDate']); ?></td>
                    <td><?= $usedDt; ?></td>
                    <?php if ($tmpArrSaveAdminId[1]) { ?>
                        <td><span onclick="comeback_coupon_popup('<?=$tmpArrSaveAdminId[1]?>');" class="hand"><?=$tmpArrSaveAdminId[0];?></span></td>
                    <?php } else { ?>
                        <td><?= $tmpArrSaveAdminId[0]; ?></td>
                    <?php } ?>
                    <td><?= $memberCouponUseText; ?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="10" class="no-data">
                    발급된 쿠폰이 없습니다.
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white js-delete-membercoupon">선택 삭제</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchMemberCoupon" data-search-count="<?= $page->recode['total'] ?>" data-total-count="<?= $page->recode['amount'] ?>" data-target-list-form="frmMemberCouponList" data-target-list-sno="sno">엑셀다운로드</button>
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
//            ,
//            rules: {
//                'keyword': 'required'
//            },
//            messages: {
//                'keyword': {
//                    required: "검색어를 입력하세요.",
//                }
//            }
        });

        $('#frmMemberCouponList').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'chkMemberCoupon[]': 'required'
            },
            messages: {
                'chkMemberCoupon[]': {
                    required: "하나 이상 체크하세요.",
                }
            }
        });

        $('.js-delete-membercoupon').click(function (e) {
            $('#frmMemberCouponList').submit();
        });
    });
    //-->
</script>
