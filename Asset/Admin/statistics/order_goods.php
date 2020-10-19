<div class="page-header js-affix">
	<h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchBase" method="get">
	<div class="table-title gd-help-manual">주문 검색 <span class="notice-info mgl5">주문분석은 결제완료(입금확인)된 일자를 기준으로 집계합니다. (단, 교환/반품/환불은 반영되지 않습니다.)</span></div>
	<table class="table table-cols">
		<colgroup>
			<col class="width-md"/>
			<col/>
		</colgroup>
		<tbody>
		<?php if ($page['useScmFl']) { ?>
		<tr>
			<th>공급사 구분</th>
			<td>
				<label class="radio-inline">
					<input type="radio" name="scmFl" value="0" <?= gd_isset($checked['scmFl']['0']); ?>/>본사
				</label>
				<label class="radio-inline">
					<input type="radio" name="scmFl" value="1" class="js-layer-register" <?= gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="checkbox"/> 공급사
				</label>
				<input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="radio"/>

				<div id="scmLayer" class="selected-btn-group <?=$search['scmFl'] == '1' && !empty($search['scmNo']) ? 'active' : ''?>">
					<h5>선택된 공급사 : </h5>
					<?php if ($search['scmFl'] == '1') { ?>
						<div id="info_scm_<?= $search['scmNo'] ?>" class="btn-group btn-group-xs">
							<input type="hidden" name="scmNo" value="<?=$search['scmNo']?>"/>
							<input type="hidden" name="scmNoNm" value="<?=$search['scmNoNm'] ?>"/>
							<span class="btn"><?=$search['scmNoNm']?></span>
							<button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#idscm_<?=$search['scmNo']?>">삭제</button>
						</div>
					<?php } ?>
				</div>
			</td>
		</tr>
		<?php } ?>
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
						<input type="text" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>" class="form-control width-xs" />
						<span class="input-group-addon">
							<span class="btn-icon-calendar">
							</span>
						</span>
					</div>

					<div class="btn-group js-dateperiod" data-toggle="buttons" data-target-name="treatDate[]">
                        <?php if ($page['groupType'] == 'month') { ?>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="27" />1개월</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="83" />3개월</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="167" />6개월</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="335" />12개월</label>
                        <?php } else { ?>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="0" />오늘</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="6" />7일</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="13" />15일</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="27" />1개월</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="83" />3개월</label>
                        <?php } ?>
					</div>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
	<div class="table-btn">
		<button type="submit" class="btn btn-lg btn-black">검색</button>
	</div>
</form>

<ul class="nav nav-tabs mgb30" role="tablist">
	<li role="presentation" <?=$page['groupType'] == 'day' ? 'class="active"' : ''?>>
		<a href="../statistics/order_<?=$page['useScmFl'] ? 'provider_' : ''?>day.php<?=$queryString?>">일별 주문현황</a>
	</li>
	<?php if (!$page['useScmFl']) { ?>
	<li role="presentation" <?=$page['groupType'] == 'hour' ? 'class="active"' : ''?>>
		<a href="../statistics/order_<?=$page['useScmFl'] ? 'provider_' : ''?>hour.php<?=$queryString?>">시간대별 주문현황</a>
	</li>
	<li role="presentation" <?=$page['groupType'] == 'week' ? 'class="active"' : ''?>>
		<a href="../statistics/order_<?=$page['useScmFl'] ? 'provider_' : ''?>week.php<?=$queryString?>">요일별 주문현황</a>
	</li>
	<?php } ?>
	<li role="presentation" <?=$page['groupType'] == 'month' ? 'class="active"' : ''?>>
		<a href="../statistics/order_<?=$page['useScmFl'] ? 'provider_' : ''?>month.php<?=$queryString?>">월별 주문현황</a>
	</li>
	<li role="presentation" <?=$page['groupType'] == 'goods' ? 'class="active"' : ''?>>
		<a href="../statistics/order_<?=$page['useScmFl'] ? 'provider_' : ''?>goods.php<?=$queryString?>">상품별 주문현황</a>
	</li>
    <?php if (!$isProvider) { ?>
	<?php if (!$page['useScmFl']) { ?>
	<li role="presentation" <?=$page['groupType'] == 'member' ? 'class="active"' : ''?>>
		<a href="../statistics/order_member.php<?=$queryString?>">회원구분 주문현황</a>
	</li>
	<?php } ?>
    <?php } ?>
</ul>

<div class="table-dashboard">
	<table class="table table-cols">
		<colgroup>
			<col style="width:20%;" />
			<col style="width:16%;" />
			<col style="width:16%;" />
			<col style="width:16%;" />
		</colgroup>
		<thead>
		<tr>
			<th class="bln">
				총 구매자수
			</th>
			<th>
				총 구매건수
			</th>
			<th>
				총 구매개수
			</th>
			<th>
				총 판매금액
			</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="bln">
				<strong id="totalMemberCnt">0</strong>명
				<ul class="list-unstyled">
					<li><strong>PC쇼핑몰</strong><span><?php echo number_format(gd_isset($total['pc']['memberCnt'], 0)); ?></span></li>
					<li><strong>모바일쇼핑몰</strong><span><?php echo number_format(gd_isset($total['mobile']['memberCnt'], 0)); ?></span></li>
					<li><strong>수기주문</strong><span><?php echo number_format(gd_isset($total['write']['memberCnt'], 0)); ?></span></li>
				</ul>
			</td>
			<td>
				<strong id="totalOrderCnt">0</strong>건
				<ul class="list-unstyled">
					<li><strong>PC쇼핑몰</strong><span><?php echo number_format(gd_isset($total['pc']['orderCnt'], 0)); ?></span></li>
					<li><strong>모바일쇼핑몰</strong><span><?php echo number_format(gd_isset($total['mobile']['orderCnt'], 0)); ?></span></li>
					<li><strong>수기주문</strong><span><?php echo number_format(gd_isset($total['write']['orderCnt'], 0)); ?></span></li>
				</ul>
			</td>
			<td>
				<strong id="totalGoodsCnt">0</strong>개
				<ul class="list-unstyled">
					<li><strong>PC쇼핑몰</strong><span><?php echo number_format(gd_isset($total['pc']['goodsCnt'], 0)); ?></span></li>
					<li><strong>모바일쇼핑몰</strong><span><?php echo number_format(gd_isset($total['mobile']['goodsCnt'], 0)); ?></span></li>
					<li><strong>수기주문</strong><span><?php echo number_format(gd_isset($total['write']['goodsCnt'], 0)); ?></span></li>
				</ul>
			</td>
			<td>
				<strong id="totalGoodsPrice">0</strong>원
				<ul class="list-unstyled">
					<li><strong>PC쇼핑몰</strong><span><?php echo number_format(gd_isset($total['pc']['goodsPrice'], 0)); ?></span></li>
					<li><strong>모바일쇼핑몰</strong><span><?php echo number_format(gd_isset($total['mobile']['goodsPrice'], 0)); ?></span></li>
					<li><strong>수기주문</strong><span><?php echo number_format(gd_isset($total['write']['goodsPrice'], 0)); ?></span></li>
				</ul>
			</td>
		</tr>
		</tbody>
	</table>
</div>

<div class="table-action mgt30">
	<div class="pull-right">
		<button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
	</div>
</div>

<div class="table-responsive statistics-board" id="excelData">
	<table class="table table-rows">
		<colgroup>
			<col width="14%"/>
			<col span="16" width="5%"/>
		</colgroup>
		<thead>
		<tr class="nowrap text-center">
			<th rowspan="2" class="bln center-line"><?=$page['title']?></th>
			<th colspan="4" class="center-line">전체</th>
			<th colspan="4" class="center-line">PC쇼핑몰</th>
			<th colspan="4" class="center-line">모바일</th>
			<th colspan="4" class="center-line brn">수기주문</th>
		</tr>
		<tr class="nowrap text-center">
			<th>구매자수</th>
			<th>구매건수</th>
			<th>구매개수</th>
			<th class="point2">판매금액</th>
			<th>구매자수</th>
			<th>구매건수</th>
			<th>구매개수</th>
			<th class="point2">판매금액</th>
			<th>구매자수</th>
			<th>구매건수</th>
			<th>구매개수</th>
			<th class="point2">판매금액</th>
			<th>구매자수</th>
			<th>구매건수</th>
			<th>구매개수</th>
			<th class="point2 brn">판매금액</th>
		</tr>
		</thead>
		<tbody>
		<?php
        $totalMemberCnt = 0;
        $totalOrderCnt = 0;
        $totalGoodsCnt = 0;
        $totalGoodsPrice = 0;
        if (count($payment) > 0) {
            foreach ($payment as $goodsNo => $val) {
                if (!is_numeric($goodsNo)) {
                    continue;
                }

                // row별 합계
                $memberCnt = gd_isset($val['pc']['memberCnt'], 0) + gd_isset($val['mobile']['memberCnt'], 0) + gd_isset($val['write']['memberCnt'], 0);
                $orderCnt = gd_isset($val['pc']['orderCnt'], 0) + gd_isset($val['mobile']['orderCnt'], 0) + gd_isset($val['write']['orderCnt'], 0);
                $goodsCnt = gd_isset($val['pc']['goodsCnt'], 0) + gd_isset($val['mobile']['goodsCnt'], 0) + gd_isset($val['write']['goodsCnt'], 0);
                $goodsPrice = gd_isset($val['pc']['goodsPrice'], 0) + gd_isset($val['mobile']['goodsPrice'], 0) + gd_isset($val['write']['goodsPrice'], 0);

                // column별 합계
                $totalMemberCnt += $memberCnt;
                $totalOrderCnt += $orderCnt;
                $totalGoodsCnt += $goodsCnt;
                $totalGoodsPrice += $goodsPrice;
                ?>
                <tr class="nowrap text-right">
                    <td class="font-date text-center bln center-line"><span class="font-date"><?php echo $val['name']; ?></span></td>
                    <td class="font-num"><?php echo number_format($memberCnt); ?></td>
                    <td class="font-num"><?php echo number_format($orderCnt); ?></td>
                    <td class="font-num"><?php echo number_format($goodsCnt); ?></td>
                    <td class="font-num point2"><?php echo number_format($goodsPrice); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($val['pc']['memberCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($val['pc']['orderCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($val['pc']['goodsCnt'], 0)); ?></td>
                    <td class="font-num point2"><?php echo number_format(gd_isset($val['pc']['goodsPrice'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($val['mobile']['memberCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($val['mobile']['orderCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($val['mobile']['goodsCnt'], 0)); ?></td>
                    <td class="font-num point2"><?php echo number_format(gd_isset($val['mobile']['goodsPrice'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($val['write']['memberCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($val['write']['orderCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($val['write']['goodsCnt'], 0)); ?></td>
                    <td class="font-num point2 brn"><?php echo number_format(gd_isset($val['write']['goodsPrice'], 0)); ?></td>
                </tr>
        <?php
                }
            } else {
            ?>
                <tr>
                    <td class="no-data" colspan="17">통계 정보가 없습니다.</td>
                </tr>
        <?php
        }
        ?>
		</tbody>
        <?php
        if (count($payment) > 0) {
            ?>
            <tfoot>
            <tr class="nowrap text-right">
                <th class="font-num tac total">합계</th>
                <th class="font-num"><?php echo number_format($totalMemberCnt); ?></th>
                <th class="font-num"><?php echo number_format($totalOrderCnt); ?></th>
                <th class="font-num"><?php echo number_format($totalGoodsCnt); ?></th>
                <th class="font-num point2"><?php echo number_format($totalGoodsPrice); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total['pc']['memberCnt'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total['pc']['orderCnt'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total['pc']['goodsCnt'], 0)); ?></th>
                <th class="font-num point2"><?php echo number_format(gd_isset($total['pc']['goodsPrice'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total['mobile']['memberCnt'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total['mobile']['orderCnt'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total['mobile']['goodsCnt'], 0)); ?></th>
                <th class="font-num point2"><?php echo number_format(gd_isset($total['mobile']['goodsPrice'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total['write']['memberCnt'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total['write']['orderCnt'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total['write']['goodsCnt'], 0)); ?></th>
                <th class="font-num point2"><?php echo number_format(gd_isset($total['write']['goodsPrice'], 0)); ?></th>
            </tr>
            </tfoot>
            <?php
        }
        ?>
	</table>
</div>

<script type="text/javascript">
	$(function(){
		$('#totalMemberCnt').text('<?=number_format($totalMemberCnt)?>');
		$('#totalOrderCnt').text('<?=number_format($totalOrderCnt)?>');
		$('#totalGoodsCnt').text('<?=number_format($totalGoodsCnt)?>');
		$('#totalGoodsPrice').text('<?=number_format($totalGoodsPrice)?>');
		$('#maxMemberCnt').text('<?=number_format($maxMemberCnt)?>');
		$('#maxMemberCntDate').text('<?=$maxMemberCntDate?>');
		$('#minMemberCnt').text('<?=number_format($minMemberCnt)?>');
		$('#minMemberCntDate').text('<?=$minMemberCntDate?>');
		$('#maxGoodsPrice').text('<?=number_format($maxGoodsPrice)?>');
		$('#maxGoodsPriceDate').text('<?=$maxGoodsPriceDate?>');
		$('#minGoodsPrice').text('<?=number_format($minGoodsPrice)?>');
		$('#minGoodsPriceDate').text('<?=$minGoodsPriceDate?>');

		// 기간 체크
		$('#frmSearchBase').validate({
			dialog: false,
			submitHandler: function(form) {
				$elements = $('input[name="treatDate[]"]');
				interval = moment($($elements[1]).val()).diff(moment($($elements[0]).val()), 'days');
				if (interval > <?=$page['maxLimit']?>) {
					alert('최대 <?=intval($page['maxLimit']/30)?>개월까지 조회할 수 있습니다.');
					return false;
				}

				form.submit();
			}
		});

        // 엑셀다운로드
        $('.btn-excel').click(function () {
            var $form = $('<form></form>');
            $form.attr('action', './excel_ps.php');
            $form.attr('method', 'post');
            $form.attr('target', 'ifrmProcess');
            $form.appendTo('body');

            var mode = $('<input type="hidden" name="mode" value="order_excel_download">');
            var excel_name = $('<input type="hidden" name="excel_name" value="<?= $naviMenu->location[0] . '_' . $naviMenu->location[1] . '_' . $naviMenu->location[2] . '_' . $page['title']?>">');
            var data = $('<input type="hidden" name="data" value="' + encodeURI($('#excelData').html()) + '">');

            $form.append(mode).append(excel_name).append(data);
            $form.submit();
        });
	});
</script>
