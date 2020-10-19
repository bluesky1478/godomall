<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="content-form js-form-enter-submit">
    <div class="table-title gd-help-manual">
        주문상품 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null, null, 'form-control input-sm'); ?>
                        <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('treatDateFl', 'treatDateFl', $search['combineTreatDate'], null, $search['treatDateFl'], null, null, 'form-control input-sm'); ?>
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][0]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        <?= gd_search_date($search['treatDate'], 'treatDate', false) ?>
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
            검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 /
            전체 <strong class="text-danger"><?= number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
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
            ), '개 출력', Request::get()->get('pageNum'), null
            ); ?>
        </div>
    </div>
</form>

<form id="frmScmAdjust" action="./scm_adjust_ps.php" method="post">
    <input type="hidden" name="mode" value="insertScmAdjustOrder"/>
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width3p"><input type="checkbox" value="y" class="js-checkall" data-target-name="orderGoodsNo"/></th>
            <th class="width3p">번호</th>
            <th class="width5p">주문일시</th>
            <th class="width7p">주문번호</th>
            <th class="width5p">주문자</th>
            <th class="width5p">상품주문번호</th>
            <th>주문상품</th>
            <th class="width3p">수량</th>
            <th class="width7p">금액</th>
            <th class="width5p">수수료</th>
            <th class="width5p">판매수수료</th>
            <th class="width7p">정산금액</th>
            <th class="width7p">총 정산금액</th>
            <th class="width5p">처리상태</th>
            <th class="width5p">공급사</th>
            <th class="width3p">결제방법</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false && is_array($data)) {
            foreach ($data as $key => $val) {
                $goodsPrice = $val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                if ($val['addGoodsCnt'] > 0) {
                    $rowSpan = 'rowspan="'.($val['addGoodsCnt']+1).'"';
                } else {
                    $rowSpan = '';
                }
                ?>
                <tr class="text-center">
                    <td <?= $rowSpan; ?>><input type="checkbox" name="orderGoodsNo[]" value="<?= $val['sno']; ?>"/></td>
                    <td <?= $rowSpan; ?> class="font-num"><small><?= $page->idx--; ?></small></td>
                    <td <?= $rowSpan; ?>><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?></td>
                    <td <?= $rowSpan; ?>><a href="../order/order_view.php?orderNo=<?= $val['orderNo']; ?>" title="주문번호" target="_blank" class="btn btn-link"><?= $val['orderNo']; ?></a></td>
                    <td <?= $rowSpan; ?>><?= $val['orderName'] ?>
                        <p>
                            <?php if (!$val['memNo']) { ?>
                                (비회원)
                            <?php } else { ?>
                                (<?= $val['memId'] ?>)
                            <?php } ?>
                        </p>
                    </td>
                    <td <?= $rowSpan; ?>><a href="../order/order_view.php?orderNo=<?= $val['orderNo']; ?>" title="상품주문번호" target="_blank" class="btn btn-link"><?= $val['sno'] ?></a></td>
                    <td class="text-left">
                        <div class="goods_name one_line hand" title="주문 상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>', 'true');"><?= gd_html_cut(gd_remove_only_tag(stripslashes($val['goodsNm'])), 46, '..'); ?></div>
                        <?php
                        // 옵션 처리
                        if (empty($val['optionInfo']) === false) {
                            echo '<div class="option_info" title="상품 옵션">';
                            foreach ($val['optionInfo'] as $option) {
                                echo $option[0] . ':', $option[1] . ', ';
                            }
                            echo '</div>' . chr(10);

                        }

                        // 텍스트 옵션 처리
                        if (empty($val['optionTextInfo']) === false) {
                            echo '<div class="option_info" title="텍스트 옵션">';
                            foreach ($val['optionTextInfo'] as $option) {
                                echo $option[0] . ':', $option[1] . ', ';
                            }
                            echo '</div>' . chr(10);
                        }
                        ?>
                    </td>
                    <td class="goods_cnt"><?= number_format($val['goodsCnt']); ?></td>
                    <td><?= gd_currency_display($goodsPrice); ?></td>
                    <td><?= $val['commission']; ?>%</td>
                    <td><?= gd_currency_symbol() ?><?= gd_money_format($val['goodsAdjustCommission']); ?></span><?= gd_currency_string() ?></td>
                    <td><?= gd_currency_symbol() ?><?= gd_money_format($val['goodsAdjustPrice']); ?></span><?= gd_currency_string() ?></td>
                    <td <?= $rowSpan; ?>><?= gd_currency_symbol() ?><?= gd_money_format($val['totalAdjustPrice']); ?></span><?= gd_currency_string() ?></td>
                    <td <?= $rowSpan; ?>><div title="주문 상품별 주문 상태"><?= $val['orderStatusStr']; ?></div></td>
                    <td <?= $rowSpan; ?>><?= $val['companyNm'] ?></td>
                    <td <?= $rowSpan; ?>><?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $val['settleKind'] . '.gif')->www(), $val['settleKindStr']); ?></td>
                </tr>
                <?php
                if ($val['addGoodsCnt'] > 0) {
                    foreach ($val['addGoods'] as $aVal) {
                        ?>
                        <tr class="text-center add-goods">
                            <td class="text-left">
                                <span class="label label-default" title="<?= $aVal['sno'] ?>">추가</span>
                                <span class="goods_name one_line hand" title="추가 상품명" onclick="addgoods_register_popup('<?= $aVal['addGoodsNo']; ?>', 'true');"><?= gd_html_cut(gd_remove_only_tag(stripslashes($aVal['goodsNm'])), 46, '..'); ?>
                                    <small>(<?= gd_html_cut($aVal['optionNm'], 46, '..'); ?>)</small>
                                </span>
                            </td>
                            <td class="goods_cnt"><?= number_format($aVal['goodsCnt']); ?></td>
                            <td><?= gd_currency_display($aVal['goodsPrice'] * $aVal['goodsCnt']); ?></td>
                            <td><?= $aVal['commission']; ?>%</td>
                            <td><?= gd_currency_symbol() ?><?= gd_money_format($aVal['addGoodsAdjustCommission']); ?></span><?= gd_currency_string() ?></td>
                            <td><?= gd_currency_symbol() ?><?= gd_money_format($aVal['addGoodsAdjustPrice']); ?></span><?= gd_currency_string() ?></td>
                        </tr>
                        <?php
                    }
                }
            }
        } else {
            ?>
            <tr>
                <td colspan="16" class="no-data">
                    검색된 주문이 없습니다.
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 주문상품을</span>
            <button type="submit" class="btn btn-white"/>
            정산요청</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchGoods" data-target-list-form="frmScmAdjust" data-target-list-sno="orderGoodsNo[]" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>">엑셀다운로드</button>
        </div>
    </div>
</form>
<div class="text-center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    $(document).ready(function () {
        // 폼체크
        $('#frmScmAdjust').validate({
            submitHandler: function (form) {
                if ($('input[name*=orderGoodsNo]:checked').length < 1) {
                    alert('선택된 주문 상품이 없습니다.');
                    return false;
                }

                form.target = 'ifrmProcess';
                form.submit();
            }
        });

        // 리스트 정렬
        $('#sort, #pageNum').change(function (e) {
            $('#frmSearchGoods').submit();
        });
    });
</script>
