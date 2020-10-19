<div class="page-header js-affix">
	<h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchBase" method="get">
    <div class="table-title gd-help-manual">매출 검색</div>
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
					<input type="radio" name="scmFl" value="1" class="js-layer-register" <?= gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="radio"/> 공급사
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
		<a href="sales_<?=$page['useScmFl'] ? 'provider_' : ''?>day.php<?=$queryString?>">일별 매출현황</a>
	</li>
	<?php if (!$page['useScmFl']) { ?>
	<li role="presentation" <?=$page['groupType'] == 'hour' ? 'class="active"' : ''?>>
		<a href="../statistics/sales_<?=$page['useScmFl'] ? 'provider_' : ''?>hour.php<?=$queryString?>">시간대별 매출현황</a>
	</li>
	<li role="presentation" <?=$page['groupType'] == 'week' ? 'class="active"' : ''?>>
		<a href="../statistics/sales_<?=$page['useScmFl'] ? 'provider_' : ''?>week.php<?=$queryString?>">요일별 매출현황</a>
	</li>
	<?php } ?>
	<li role="presentation" <?=$page['groupType'] == 'month' ? 'class="active"' : ''?>>
		<a href="../statistics/sales_<?=$page['useScmFl'] ? 'provider_' : ''?>month.php<?=$queryString?>">월별 매출현황</a>
	</li>
    <?php if (!$isProvider) { ?>
	<?php if (!$page['useScmFl']) { ?>
	<li role="presentation" <?=$page['groupType'] == 'member' ? 'class="active"' : ''?>>
		<a href="../statistics/sales_member.php<?=$queryString?>">회원구분 매출현황</a>
	</li>
	<li role="presentation" <?=$page['groupType'] == 'tax' ? 'class="active"' : ''?>>
		<a href="../statistics/sales_tax.php<?=$queryString?>">과세구분 매출현황</a>
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
			<col style="width:16%;" />
			<col style="width:16%;" />
		</colgroup>
		<thead>
		<tr>
			<th class="bln point">
				총 매출금액
			</th>
			<th>
				최대 매출금액
			</th>
			<th>
				최소 매출금액
			</th>
			<th>
				PC쇼핑몰 매출금액
			</th>
			<th>
				모바일쇼핑몰 매출금액
			</th>
			<th>
				수기주문 매출금액
			</th>
		</tr>
		</thead>
		<tbody>
			<td class="bln point">
				<strong id="totalSalePrice">0</strong>원
				<ul class="list-unstyled">
					<li><strong>판매금액</strong><span id="totalOrderSalePrice">0</span></li>
					<li><strong>환불금액</strong><span id="totalRefundSalePrice">0</span></li>
				</ul>
			</td>
			<td>
				<strong id="maxSalePrice">0</strong>원 <br /><span id="maxSaleDate" class="font-date">2015.01</span>
			</td>
			<td>
				<strong id="minSalePrice">0</strong>원 <br /><span id="minSaleDate" class="font-date">2015.01</span>
			</td>
			<td>
				<strong id="pcTotalSalePrice">0</strong>원
				<ul class="list-unstyled">
					<li><strong>판매금액</strong><span id="pcTotalOrderSalePrice">0</span></li>
					<li><strong>환불금액</strong><span id="pcTotalRefundSalePrice">0</span></li>
				</ul>
			</td>
			<td>
				<strong id="mobileTotalSalePrice">0</strong>원
				<ul class="list-unstyled">
					<li><strong>판매금액</strong><span id="mobileTotalOrderSalePrice">0</span></li>
					<li><strong>환불금액</strong><span id="mobileTotalRefundSalePrice">0</span></li>
				</ul>
			</td>
			<td>
				<strong id="writeTotalSalePrice">0</strong>원
				<ul class="list-unstyled">
					<li><strong>판매금액</strong><span id="writeTotalOrderSalePrice">0</span></li>
					<li><strong>환불금액</strong><span id="writeTotalRefundSalePrice">0</span></li>
				</ul>
			</td>
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
			<col style="width:75px"/>
			<col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
            <col style="width:100px"/>
		</colgroup>
		<thead>
		<tr class="nowrap text-center">
			<th rowspan="3" class="bln"><?=$page['title']?></th>
			<th rowspan="3" class="point1">매출총액</th>
			<th colspan="7" class="point1-line">PC쇼핑몰</th>
			<th colspan="7" class="point1-line">모바일</th>
			<th colspan="7" class="point1-line brn">수기주문</th>
		</tr>
		<tr class="nowrap text-center top-line">
			<th colspan="2" class="center-line">판매금액</th>
			<th rowspan="2" class="middle center-line">판매총액</th>
			<th colspan="2" class="center-line">환불금액</th>
			<th rowspan="2" class="middle center-line ">환불총액</th>
			<th rowspan="2" class="middle point1">매출금액</th>
            <th colspan="2" class="center-line">판매금액</th>
            <th rowspan="2" class="middle center-line">판매총액</th>
            <th colspan="2" class="center-line">환불금액</th>
            <th rowspan="2" class="middle center-line ">환불총액</th>
            <th rowspan="2" class="middle point1">매출금액</th>
            <th colspan="2" class="center-line">판매금액</th>
            <th rowspan="2" class="middle center-line">판매총액</th>
            <th colspan="2" class="center-line">환불금액</th>
            <th rowspan="2" class="middle center-line ">환불총액</th>
            <th rowspan="2" class="middle point1">매출금액</th>
		</tr>
		<tr class="nowrap text-center">
			<th class="center-line">상품</th>
			<th class="center-line">배송비</th>
			<th class="center-line">상품</th>
			<th class="center-line">배송비</th>
			<th class="center-line">상품</th>
			<th class="center-line">배송비</th>
			<th class="center-line">상품</th>
			<th class="center-line">배송비</th>
			<th class="center-line">상품</th>
			<th class="center-line">배송비</th>
			<th class="center-line">상품</th>
			<th class="center-line">배송비</th>
		</tr>
		</thead>
		<tbody>
		<?php
        $totalSalePrice = 0;
        $settlePrice = [];
        $salePrice = [];
		for ($i = 0; $i <= $page['forLimit']; $i++) {
			switch ($page['groupType']) {
				case 'day':
					$paymentDt = date('Y-m-d', strtotime('-' . ($page['forLimit'] - $i) . ' day', strtotime($search['treatDate'][1])));
					$paymentDtStr = $paymentDt;
					break;

				case 'hour':
					$paymentDt = sprintf('%02d', $i);
					$paymentDtStr = $paymentDt . ':00';
					break;

				case 'week':
					$paymentDt = $i;
					$paymentDtStr = $page['dayOfWeek'][$i];
					break;

				case 'month':
                    $paymentDt = gd_previous_month_date(($page['forLimit'] - $i), $search['treatDate'][1]);
					$paymentDtStr = $paymentDt;
					break;
			}

            // 수기주문 매출금액
            $settlePrice['order']['pc'] = gd_isset($payment['order']['pc'][$paymentDt]['settlePrice'], 0);
            $settlePrice['refund']['pc'] = gd_isset($payment['refund']['pc'][$paymentDt]['settlePrice'], 0);
            $settlePrice['total']['pc'] = $settlePrice['order']['pc'] - $settlePrice['refund']['pc'];
            $salePrice['order']['pc'] += $settlePrice['order']['pc'];
            $salePrice['refund']['pc'] += $settlePrice['refund']['pc'];
            $salePrice['total']['pc'] += $settlePrice['total']['pc'];

            // 수기주문 매출금액
            $settlePrice['order']['mobile'] = gd_isset($payment['order']['mobile'][$paymentDt]['settlePrice'], 0);
            $settlePrice['refund']['mobile'] = gd_isset($payment['refund']['mobile'][$paymentDt]['settlePrice'], 0);
            $settlePrice['total']['mobile'] = $settlePrice['order']['mobile'] - $settlePrice['refund']['mobile'];
            $salePrice['order']['mobile'] += $settlePrice['order']['mobile'];
            $salePrice['refund']['mobile'] += $settlePrice['refund']['mobile'];
            $salePrice['total']['mobile'] += $settlePrice['total']['mobile'];

            // 수기주문 매출금액
            $settlePrice['order']['write'] = gd_isset($payment['order']['write'][$paymentDt]['settlePrice'], 0);
            $settlePrice['refund']['write'] = gd_isset($payment['refund']['write'][$paymentDt]['settlePrice'], 0);
            $settlePrice['total']['write'] = $settlePrice['order']['write'] - $settlePrice['refund']['write'];
            $salePrice['order']['write'] += $settlePrice['order']['write'];
            $salePrice['refund']['write'] += $settlePrice['refund']['write'];
            $salePrice['total']['write'] += $settlePrice['total']['write'];

            // 최대매출
            if (!isset($maxSalePrice)) {
                $maxSalePrice = array_sum($settlePrice['total']);
                $maxSaleDate = $paymentDtStr;
            }
            if ($maxSalePrice < array_sum($settlePrice['total'])) {
                $maxSalePrice = array_sum($settlePrice['total']);
                $maxSaleDate = $paymentDtStr;
            }

            // 최소매출
            if (array_sum($settlePrice['total']) > 0) {
                if (!isset($minSalePrice)) {
                    $minSalePrice = array_sum($settlePrice['total']);
                    $minSaleDate = $paymentDtStr;
                }
                if ($minSalePrice > array_sum($settlePrice['total'])) {
                    $minSalePrice = array_sum($settlePrice['total']);
                    $minSaleDate = $paymentDtStr;
                }
            }
            ?>
			<tr class="nowrap text-right">
				<td class="font-date bln"><span class="font-date"><?php echo $paymentDtStr; ?></span></td>
				<td class="font-num point1"><?php echo number_format(array_sum($settlePrice['total'])); ?></td>

				<td class="font-num"><?php echo number_format(gd_isset($payment['order']['pc'][$paymentDt]['goodsPrice'], 0)); ?></td>
				<td class="font-num"><?php echo number_format(gd_isset($payment['order']['pc'][$paymentDt]['deliveryPrice'], 0)); ?></td>
				<td class="font-num point2"><?php echo number_format(gd_isset($payment['order']['pc'][$paymentDt]['settlePrice'], 0)); ?></td>
				<td class="font-num"><?php echo number_format(gd_isset($payment['refund']['pc'][$paymentDt]['goodsPrice'], 0)); ?></td>
				<td class="font-num"><?php echo number_format(gd_isset($payment['refund']['pc'][$paymentDt]['deliveryPrice'], 0)); ?></td>
				<td class="font-num  point2"><?php echo number_format(gd_isset($payment['refund']['pc'][$paymentDt]['settlePrice'], 0)); ?></td>
				<td class="font-num  point3"><?php echo number_format($settlePrice['total']['pc']); ?></td>

				<td class="font-num"><?php echo number_format(gd_isset($payment['order']['mobile'][$paymentDt]['goodsPrice'], 0)); ?></td>
				<td class="font-num"><?php echo number_format(gd_isset($payment['order']['mobile'][$paymentDt]['deliveryPrice'], 0)); ?></td>
				<td class="font-num point2"><?php echo number_format($settlePrice['order']['mobile']); ?></td>
				<td class="font-num"><?php echo number_format(gd_isset($payment['refund']['mobile'][$paymentDt]['goodsPrice'], 0)); ?></td>
				<td class="font-num"><?php echo number_format(gd_isset($payment['refund']['mobile'][$paymentDt]['deliveryPrice'], 0)); ?></td>
				<td class="font-num point2"><?php echo number_format($settlePrice['refund']['mobile']); ?></td>
				<td class="font-num point3"><?php echo number_format($settlePrice['total']['mobile']); ?></td>

				<td class="font-num"><?php echo number_format(gd_isset($payment['order']['write'][$paymentDt]['goodsPrice'], 0)); ?></td>
				<td class="font-num"><?php echo number_format(gd_isset($payment['order']['write'][$paymentDt]['deliveryPrice'], 0)); ?></td>
				<td class="font-num point2"><?php echo number_format($settlePrice['order']['write']); ?></td>
				<td class="font-num"><?php echo number_format(gd_isset($payment['refund']['write'][$paymentDt]['goodsPrice'], 0)); ?></td>
				<td class="font-num"><?php echo number_format(gd_isset($payment['refund']['write'][$paymentDt]['deliveryPrice'], 0)); ?></td>
				<td class="font-num point2"><?php echo number_format($settlePrice['refund']['write']); ?></td>
				<td class="font-num point3 brn"><?php echo number_format($settlePrice['total']['write']); ?></td>
			</tr>
        <?php } ?>
		</tbody>
		<tfoot>
			<tr class="nowrap text-right">
				<th class="font-num tac total">합계</th>
				<th class="font-num point1"><?php echo number_format(array_sum($salePrice['total'])); ?></th>

				<th class="font-num"><?php echo number_format(gd_isset($payment['order']['pc']['total']['goodsPrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['order']['pc']['total']['deliveryPrice'], 0)); ?></th>
				<th class="font-num point2"><?php echo number_format(gd_isset($payment['order']['pc']['total']['settlePrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['refund']['pc']['total']['goodsPrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['refund']['pc']['total']['deliveryPrice'], 0)); ?></th>
				<th class="font-num point2"><?php echo number_format(gd_isset($payment['refund']['pc']['total']['settlePrice'], 0)); ?></th>
				<th class="font-num point3"><?php echo number_format($salePrice['total']['pc']); ?></th>

				<th class="font-num"><?php echo number_format(gd_isset($payment['order']['mobile']['total']['goodsPrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['order']['mobile']['total']['deliveryPrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['order']['mobile']['total']['settlePrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['refund']['mobile']['total']['goodsPrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['refund']['mobile']['total']['deliveryPrice'], 0)); ?></th>
				<th class="font-num point2"><?php echo number_format(gd_isset($payment['refund']['mobile']['total']['settlePrice'], 0)); ?></th>
				<th class="font-num point3"><?php echo number_format($salePrice['total']['mobile']); ?></th>

				<th class="font-num"><?php echo number_format(gd_isset($payment['order']['write']['total']['goodsPrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['order']['write']['total']['deliveryPrice'], 0)); ?></th>
				<th class="font-num point2"><?php echo number_format(gd_isset($payment['order']['write']['total']['settlePrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['refund']['write']['total']['goodsPrice'], 0)); ?></th>
				<th class="font-num"><?php echo number_format(gd_isset($payment['refund']['write']['total']['deliveryPrice'], 0)); ?></th>
				<th class="font-num point2"><?php echo number_format(gd_isset($payment['refund']['write']['total']['settlePrice'], 0)); ?></th>
				<th class="font-num point3 brn"><?php echo number_format($salePrice['total']['write']); ?></th>
			</tr>
		</tfoot>
	</table>
</div>

<script type="text/javascript">
	$(function(){
		$('#totalSalePrice').text('<?=number_format(array_sum($salePrice['total']))?>');
		$('#totalOrderSalePrice').text('<?=number_format(array_sum($salePrice['order']))?>');
		$('#totalRefundSalePrice').text('<?=number_format(array_sum($salePrice['refund']))?>');
		$('#maxSalePrice').text('<?=number_format($maxSalePrice)?>');
		$('#maxSaleDate').text('<?=$maxSaleDate?>');
		$('#minSalePrice').text('<?=number_format($minSalePrice)?>');
		$('#minSaleDate').text('<?=$minSaleDate?>');
		$('#pcTotalSalePrice').text('<?=number_format($salePrice['total']['pc'])?>');
		$('#pcTotalOrderSalePrice').text('<?=number_format($salePrice['order']['pc'])?>');
		$('#pcTotalRefundSalePrice').text('<?=number_format($salePrice['refund']['pc'])?>');
		$('#mobileTotalSalePrice').text('<?=number_format($salePrice['total']['mobile'])?>');
		$('#mobileTotalOrderSalePrice').text('<?=number_format($salePrice['order']['mobile'])?>');
		$('#mobileTotalRefundSalePrice').text('<?=number_format($salePrice['refund']['mobile'])?>');
		$('#writeTotalSalePrice').text('<?=number_format($salePrice['total']['write'])?>');
		$('#writeTotalOrderSalePrice').text('<?=number_format($salePrice['order']['write'])?>');
		$('#writeTotalRefundSalePrice').text('<?=number_format($salePrice['refund']['write'])?>');

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

            var mode = $('<input type="hidden" name="mode" value="sales_excel_download">');
            var excel_name = $('<input type="hidden" name="excel_name" value="<?= $naviMenu->location[0] . '_' . $naviMenu->location[1] . '_' . $naviMenu->location[2] . '_' . $page['title']?>">');
            var data = $('<input type="hidden" name="data" value="' + encodeURI($('#excelData').html()) + '">');

            $form.append(mode).append(excel_name).append(data);
            $form.submit();
        });
	});
</script>
