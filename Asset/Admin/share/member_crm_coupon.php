<form name="frmCouponSearch" id="formSearch" method="get">
    <input type="hidden" name="detailSearch" value="y"/>
    <input type="hidden" name="memNo" id="memNo" value="<?= $requestGetParams['memNo']; ?>"/>
    <input type="hidden" name="navTabs" id="navTabs" value="<?= $requestGetParams['navTabs']; ?>"/>
    <input type="hidden" name="pageNum" value="<?= $requestGetParams['pageNum']; ?>"/>
    <input type="hidden" name="memberCouponState" value=""> <input type="hidden" name="dateOpt" value="">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>발급일 검색</th>
                <td>
                    <div class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" name="wDate[]" value="<?php echo $search['wDate'][0]; ?>" class="form-control width-xs" placeholder="수기입력 가능"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="wDate[]" value="<?php echo $search['wDate'][1]; ?>" class="form-control width-xs" placeholder="수기입력 가능"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>사용 구분</th>
                <td>
                    <label>
                        <select class="form-control" name="memberCouponState">
                            <option value="y" <?= $selected['memberCouponState']['y']; ?>>사용가능</option>
                            <option value="n" <?= $selected['memberCouponState']['n']; ?>>사용불가</option>
                        </select>
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black"/>
    </div>
</form>
<div class="table-header">
    <div class="pull-left">
        검색 <strong><?php echo number_format($page->recode['total']); ?></strong>개 /
        전체 <strong><?php echo number_format($page->recode['amount']); ?></strong>개
    </div>
    <div class="pull-right">
        <?php echo gd_select_box('sort', 'sort', $sorts, null, $requestGetParams['sort'], null); ?>
        <?php echo gd_select_box_by_page_view_count($requestGetParams['pageNum']); ?>
    </div>
</div>

<table class="table table-rows table-fixed">
    <colgroup>
        <col class="width5p"/>
        <col/>
        <col class="width15p"/>
        <col class="width15p"/>
        <col class="width10p"/>
        <col class="width10p"/>
        <col class="width10p"/>
        <col class="width10p"/>
    </colgroup>
    <thead>
    <tr>
        <th>번호</th>
        <th>쿠폰명</th>
        <th>발급일</th>
        <th>사용기간</th>
        <th>쿠폰유형</th>
        <th>사용범위</th>
        <th>혜택구분</th>
        <th>제한조건</th>
        <th>수정</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($data) === false) {
        $listHtml = [];
        foreach ($data as $key => $val) {
            $listHtml[] = '<tr data-no="' . $val['couponNo'] . '">';
            $listHtml[] = '<td class="text-center number">' . number_format($page->idx--) . '</td>';
            $listHtml[] = '<td class="text-center">' . $val['couponNm'] . '</td>';
            $listHtml[] = '<td class="text-center">' . $val['regDt'] . '</td>';
            $listHtml[] = '<td class="text-center">' . gd_date_format('Y-m-d H:i', $val['memberCouponStartDate']) . '<br/>~ ' . gd_date_format('Y-m-d H:i', $val['memberCouponEndDate']) . '</td>';
            $listHtml[] = '<td class="text-center">' . $convertArrData[$key]['couponUseType'] . '</td>';
            $listHtml[] = '<td class="text-center">' . $convertArrData[$key]['couponDeviceType'] . '</td>';
            $listHtml[] = '<td class="text-center">' . $convertArrData[$key]['couponKindType'] . '<br/>(' . $convertArrData[$key]['couponBenefit'] . ')</td>';
            $listHtml[] = '<td class="text-center"><button type="button" class="btn btn-gray btn-sm btn-modify">상세보기</button></td>';
            $listHtml[] = '<td class="text-center">';
            if ($val['memberCouponUsable'] == 'YES') {
                $listHtml[] = '사용가능';
            } else if ($val['memberCouponUsable'] == 'USE_CART' && $selected['memberCouponState']['y']) {
                $listHtml[] = '장바구니사용';
            } else if ($val['memberCouponUsable'] == 'USE_ORDER') {
                $listHtml[] = '주문사용';
            } else if ($val['memberCouponUsable'] == 'EXPIRATION_START_PERIOD') {
                $listHtml[] = '사용전';
            } else if (($val['memberCouponUsable'] == 'USE_CART' && $selected['memberCouponState']['n']) || $val['memberCouponUsable'] == 'EXPIRATION_END_PERIOD') {
                $listHtml[] = '사용만료';
            }
            $listHtml[] = '</td>';
            $listHtml[] = '</tr>';
        }
        echo implode('', $listHtml);
    } else {
        echo '<tr><td colspan="9" class="no-data">쿠폰 내역이 없습니다.</td></tr>';
    }
    ?>
    </tbody>
</table>

<div class="center"><?php echo $page->getPage(); ?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('select[name=\'sort\']').change({targetForm: '#formSearch'}, member.page_sort);
        $('select[name=\'pageNum\']').change({targetForm: '#formSearch'}, member.page_number);

        $('.btn-modify').click(function (e) {
            layer_close();
            layer_coupon_info($(e.target).closest('tr').data('no'));
        });
    });
    //-->
</script>
