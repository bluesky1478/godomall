<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchAdjust" name="frmSearchAdjust" method="get" class="content-form">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>공급사 구분</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="all" <?= gd_isset($checked['scmFl']['all']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="0" <?= gd_isset($checked['scmFl']['0']); ?>/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="1" class="js-layer-register" <?= gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="checkbox"/> 공급사
                    </label>
                    <input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="search"/>

                    <div id="scmLayer" class="selected-btn-group <?= $search['scmFl'] == '1' && !empty($search['scmNo']) ? 'active' : '' ?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($search['scmFl'] == '1') { ?>
                            <?php foreach ($search['scmNo'] as $k => $v) { ?>
                                <div id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                    <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                    <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td>
                    <div class="form-inline">
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
</form>

<div class="table-responsive statistics-board scm-adjust-total top-search">
    <table class="table table-rows">
        <thead>
        <tr class="nowrap text-center">
            <th class="center-line black-right-line" rowspan="2">공급사</th>
            <th class="center-line point1" colspan="4">매출<br/>A + B - C</th>
            <th class="grey-right-line" rowspan="2">정산요청건<br/>D</th>
            <th class="black-right-line" rowspan="2">정산확정건<br/>E</th>
            <th class="end" rowspan="2">지급완료건<br/>F</th>
        </tr>
        <tr class="nowrap text-center">
            <th class="point2">매출<br/>A+B-C</th>
            <th class="point3">정산금액<br/>A</th>
            <th class="point4">수수료매출<br/>B</th>
            <th class="point5 black-right-line">정산후환불<br/>C</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="text-right center-line number black-right-line"><?= number_format((int)$data['summary']['count']['scm']); ?>개</td>
            <td class="text-right center-line number emphasis1"><?php echo gd_currency_symbol() . ' ' . gd_money_format($data['summary']['price']['total'] + $data['summary']['price']['refundTotal']) . ' ' . gd_currency_string(); ?></td>
            <td class="text-right center-line number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($data['summary']['price']['adjust']) . ' ' . gd_currency_string(); ?></td>
            <td class="text-right center-line number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($data['summary']['price']['commission']) . ' ' . gd_currency_string(); ?></td>
            <td class="text-right center-line number black-right-line"><?php echo gd_currency_symbol() . ' ' . gd_money_format($data['summary']['price']['refundTotal']) . ' ' . gd_currency_string(); ?></td>
            <td class="text-right number grey-right-line"><?= number_format((int)$data['summary']['count']['1']); ?>건</td>
            <td class="text-right number black-right-line "><?= number_format((int)$data['summary']['count']['10']); ?>건</td>
            <td class="text-right center-line number  end"><?= number_format((int)$data['summary']['count']['30']); ?>건</td>
        </tr>
        </tbody>
    </table>
</div>

<div class="table-responsive statistics-board scm-adjust-total">
    <form id="frmScmAdjust" action="./scm_adjust_ps.php" method="post">
        <input type="hidden" name="mode" value="modifyScmAdjustList">
        <table class="table table-rows">
            <thead>
            <tr class="nowrap text-center">
                <th class="center-line" rowspan="3"><input type="checkbox" class="js-checkall" data-target-name="chk">
                </th>
                <th class="center-line" rowspan="3">번호</th>
                <th class="center-line black-right-line" rowspan="3">공급사</th>
                <th class="center-line point1" colspan="10">매출<br/>A + B - C</th>
                <th class="grey-right-line" colspan="3" rowspan="2">정산요청건<br/>D</th>
                <th class="center-line black-right-line" rowspan="3">정산확정건<br/>E</th>
                <th class="center-line end" rowspan="3">지급완료건<br/>F</th>
            </tr>
            <tr class="nowrap text-center">
                <th class="point2" rowspan="2">매출<br/>A+B-C</th>
                <th class="point3" colspan="3">정산금액<br/>A</th>
                <th class="point4" colspan="3">수수료매출<br/>B</th>
                <th class="point5 black-right-line" colspan="3">정산후환불<br/>C</th>
            </tr>
            <tr>
                <th class="center point6">합계<br/>A=a+b</th>
                <th class="center">상품<br/>a</th>
                <th class="center">배송비<br/>b</th>
                <th class="center point7">합계<br/>B=c+d</th>
                <th class="center">상품<br/>c</th>
                <th class="center">배송비<br/>d</th>
                <th class="center point8">합계<br/>C=e+f</th>
                <th class="center">상품<br/>e</th>
                <th class="center black-right-line">배송비<br/>f</th>
                <th class="center point9">합계<br/>D=g+h</th>
                <th class="grey-right-line">상품<br/>g</th>
                <th class="grey-right-line">배송비<br/>h</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $num = 1;
            foreach ($data['list'] as $key => $val) {
                // 매출금액
                $price['adjust']['order'] = ($val['10']['order']['adjust'] + $val['30']['order']['adjust']);
                $price['adjust']['delivery'] = ($val['10']['delivery']['adjust'] + $val['30']['delivery']['adjust']);
                $price['commission']['order'] = ($val['10']['order']['commission'] + $val['30']['order']['commission']);
                $price['commission']['delivery'] = ($val['10']['delivery']['commission'] + $val['30']['delivery']['commission']);
                $price['refund']['orderAfter'] = ($val['10']['orderAfter']['total'] + $val['30']['orderAfter']['total']);
                $price['refund']['deliveryAfter'] = ($val['10']['deliveryAfter']['total'] + $val['30']['deliveryAfter']['total']);
                $price['total'] = array_sum($price['adjust']) + array_sum($price['commission']) + array_sum($price['refund']);
                // 정산요청 금액
                $step1 = $val['1']['order']['ea'] + $val['1']['delivery']['ea'] + $val['1']['orderAfter']['ea'] + $val['1']['deliveryAfter']['ea'];
                // 정산확정 금액
                $step10 = $val['10']['order']['ea'] + $val['10']['delivery']['ea'] + $val['10']['orderAfter']['ea'] + $val['10']['deliveryAfter']['ea'];
                // 지급완료 금액
                $step30 = $val['30']['order']['ea'] + $val['30']['delivery']['ea'] + $val['30']['orderAfter']['ea'] + $val['30']['deliveryAfter']['ea'];
                ?>
                <tr>
                    <td class="center">
                        <input type="checkbox" name="chkScm[]" value="<?= $val['scm']['scmNo']; ?>"/>
                    </td>
                    <td class="center number"><?= $num; ?></td>
                    <td class="center number black-right-line"><?= $val['scm']['scmName']; ?></td>
                    <td class="center number emphasis1"><?php echo gd_currency_symbol() . ' ' . gd_money_format($price['total']) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format(array_sum($price['adjust'])) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($price['adjust']['order']) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($price['adjust']['delivery']) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format(array_sum($price['commission'])) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($price['commission']['order']) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($price['commission']['delivery']) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format(array_sum($price['refund'])) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($price['refund']['orderAfter']) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number  black-right-line"><?php echo gd_currency_symbol() . ' ' . gd_money_format($price['refund']['deliveryAfter']) . ' ' . gd_currency_string(); ?></td>
                    <td class="center number grey-right-line"><?php echo number_format($step1); ?>건</td>
                    <td class="center number grey-right-line"><?php echo number_format($val['1']['order']['ea'] + $val['1']['orderAfter']['ea']); ?>건</td>
                    <td class="center number grey-right-line"><?php echo number_format($val['1']['delivery']['ea'] + $val['1']['deliveryAfter']['ea']); ?>건</td>
                    <td class="center number black-right-line"><?php echo number_format($step10); ?>건</td>
                    <td class="center number end"><?php echo number_format($step30); ?>건</td>
                </tr>
                <?php
                $num++;
            }
            ?>
            </tbody>
        </table>
<!--        <div class="table-action">-->
<!--            <div class="pull-right">-->
<!--                <a class="btn btn-white btn-icon-excel">엑셀다운로드</a>-->
<!--            </div>-->
<!--        </div>-->
    </form>
</div>
<div class="table-action">
    <div class="pull-right">
        <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchAdjust" data-target-list-form="frmScmAdjust" data-target-list-sno="chkScm[]" data-search-count="<?=$num?>" data-total-count="<?=$num?>">엑셀다운로드</button>
    </div>
</div>
