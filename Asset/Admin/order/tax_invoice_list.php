<div class="page-header js-affix">
	<h3><?php echo end($naviMenu->location); ?>
	</h3>
</div>


<!-- 프린트 출력을 위한 form -->
<form id="frmOrderPrint" name="frmOrderPrint" action="" method="post" class="display-none">
	<input type="hidden" name="orderPrintCode" value=""/>
	<input type="hidden" name="orderPrintMode" value=""/>
	<input type="hidden" name="modeStr" value=""/>
</form>
<!-- // 프린트 출력을 위한 form -->

<!-- 검색을 위한 form -->
<form id="frmSearchOrder" method="get" class="js-form-enter-submit">
	<input type="hidden" name="detailSearch" value="<?= $search['detailSearch']; ?>"/>

	<div class="table-title gd-help-manual">발행 내역 검색</div>

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
				<td>
					<div class="form-inline">
						<?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null, null, 'form-control'); ?>
						<input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
					</div>
				</td>
				<th>사업자번호</th>
				<td>
					<div class="form-inline">
						<input type="text" name="taxBusiNo" value="<?= $search['taxBusiNo']; ?>" class="form-control"/>
					</div>
				</td>
			</tr>
			<tr>
				<th>기간검색</th>
				<td colspan="3">
					<div class="form-inline">
                        <?= gd_select_box('treatDate', 'treatDate', $search['treatDate'], null, $search['searchDateFl'], null, null, 'form-control'); ?>
						<div class="input-group js-datepicker">
							<input type="text" name="searchDate[]" value="<?= $search['searchDate'][0]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
						</div>
						~
						<div class="input-group js-datepicker">
							<input type="text" name="searchDate[]" value="<?= $search['searchDate'][1]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
						</div>

                        <?= gd_search_date(gd_isset($search['searchPeriod'], 6), 'searchDate[]', false) ?>
					</div>
				</td>
			</tr>
			<tr>
				<th>주문상태</th>
				<td colspan="3">
					<dl class="dl-horizontal dl-checkbox publishpage-orderstatus">
						<dt>
                            <span>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="orderStatus[]" value="" class="js-not-checkall" data-target-name="orderStatus[]" <?= gd_isset($checked['orderStatus']['']) ?>/> 전체
                                </label>
                            </span>
						</dt>
						<dd>
							<?php $chk = 0;
							foreach ($statusSearchableRange as $key => $val) { ?>
								<?php if(in_array($key,$tax->statusListExclude) == false) { ?>
                                <span>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="orderStatus[]" value="<?= $key ?>" <?= gd_isset($checked['orderStatus'][$key]) ?> /> <?= $val ?>
                                    </label>
                                </span>
								<?php $chk++;
								if ($chk % 8 == 0) {
									echo '<br/>';
								}
							} } ?>
						</dd>
					</dl>
				</td>
			</tr>
            <tr>
                <th>과세/면세</th>
                <td colspan="3" class="form-inline">
                    <label class="radio-inline"><input type="radio" name="taxFl" value=""<?= gd_isset($checked['taxFl']['']) ?>> 전체</label>
                    <label class="radio-inline"><input type="radio" name="taxFl" value="t" <?= gd_isset($checked['taxFl']['t']) ?>> 과세</label>
                    <label class="radio-inline"><input type="radio" name="taxFl" value="f" <?= gd_isset($checked['taxFl']['f']) ?>>  면세</label>
                </td>
            </tr>
			</tbody>
		</table>
	</div>
	<div class="table-btn">
		<input type="submit" value="검색" class="btn btn-lg btn-black">
	</div>

<?php if($taxStats) {
	?>
	<div>

		<table class="table table-cols tax-table">
			<tbody>
			<tr>
				<th class="width10p">종류</th>
				<th>업체수</th>
				<th>발행수</th>
				<th>발행액</th>
				<th>공급가액</th>
				<th>세액</th>
			</tr>
			</tbody>
			<tbody>
			<?php foreach($taxStats as $k => $v) { ?>
				<tr>
					<td class="center" >
						<?php if($v['issueFl'] =='g') { echo "일반"; }  else {  echo "전자"; } ?>
						<?php if($v['taxFreeFl'] =='t') { echo "세금"; }?>
						계산서

					</td>
					<td class="center" ><?=number_format($v['companyCnt'])?></td>
					<td class="center" ><?=number_format($v['issueCnt'])?></td>
					<td class="center" ><?=number_format($v['price']+$v['vat'])?></td>
					<td class="center" ><?=number_format($v['price'])?></td>
					<td class="center" ><?=number_format($v['vat'])?></td>
				</tr>
			<?php } ?>
			</tbody>
			</table>
	</div>
    <div class="notice-info">전자세금계산서 이용시, 국세청 시스템에서 지원되지 않는 일부 특정문자는 제외되어 전송됩니다.  ex)랲, 됬, 풻 등</div>
<?php } ?>
	<div class="table-header top-border-clear">
		<div class="pull-left">
			검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 /
			전체 <strong class="text-danger"><?= number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
		</div>
		<div class="pull-right">
			<div class="form-inline">
				<?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
				<?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10,20,30,40,50,60,70,80,90,100,200,300,500,]), '개 보기', $page->page['list']); ?>
			</div>
		</div>
	</div>
</form>
<!-- // 검색을 위한 form -->

<!-- 프린트 출력을 위한 form -->
<form id="frmOrderPrint" name="frmOrderPrint" action="" method="post" class="display-none">
	<input type="hidden" name="orderPrintCode" value=""/>
	<input type="hidden" name="orderPrintMode" value=""/>
</form>
<!-- // 프린트 출력을 위한 form -->

<div>

	<form id="frmList" action="" method="get" target="ifrmProcess">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="godobillSend" value="">


		<div class="table-action mgt0 mgb0">
			<div class="pull-left form-inline">
				<button type="button" class="btn btn-white checkDelete">선택 삭제</button>
			</div>
			<div class="pull-right">
				<div class="form-inline">
					<select class="form-control " id="orderPrintMode" name="">
						<option value="">=인쇄 선택=</option>
						<option value="blue">공급받는자용</option>
						<option value="red">공급자용</option>
					</select>
					<input type="button"  value="프린트" class="btn btn-white btn-icon-print btn-tax-invoice">
				</div>
			</div>
		</div>


		<table class="table table-rows">
			<thead>
			<tr>
				<th class="width2p">
					<input type="checkbox" value="y" class="js-checkall" data-target-name="orderNo[]">
				</th>
				<th class="width3p">번호</th>
				<th class="width5p">발행요청일</th>
                <th class="width5p">주문번호/주문자</th>
				<th class="width5p">주문상태</th>
				<th class="width5p">요청인</th>
				<th >사업자정보</th>
				<th class="width5p">결제금액</th>
				<th class="width5p">세금등급</th>
				<th class="width5p">발행액</th>
				<th class="width5p">공급가액</th>
				<th class="width5p">세액</th>
				<th class="width5p">발행일</th>
				<th class="width5p">종류</th>
				<th class="width5p">발행상태</th>
				<th class="width5p">처리일</th>
				<th class="width10p">메모</th>
			</tr>
			</thead>
			<tbody>
			<?php
			if (gd_isset($data)) {

				$taxFreeStr = array('t' => '과세','f'=>'면세');

				$printStr = array('y'=>'발행완료(인쇄후)','n'=>'미발행(인쇄전)');
				$godoBillStr = array('y'=>'전송완료','n'=>'전송실패');

                foreach ($data as $key => $val) {

                    foreach($val['taxInvoiceInfo'] as $k => $v) {
                        ?>
                        <tr <?php if($v['totalPrice'] <= 0) { echo "style=background:#efefef"; } ?>>
                            <?php if($k =='0') { ?>
                                <td class="center" rowspan="<?=count($val['taxInvoiceInfo'])?>">
                                    <input type="checkbox" name="orderNo[]" value="<?php echo $val['orderNo']; ?>"  id="orderNo_<?php echo $val['orderNo']; ?>"	<?php if($val['issueFl'] =='e') { echo "disabled='true'"; } ?>/>
                                    <input type="hidden" name="sno[<?=$val['orderNo']?>]" value="<?php echo $val['sno']; ?>"/>
                                </td>
                                <td class="center" rowspan="<?=count($val['taxInvoiceInfo'])?>"><?php echo number_format($page->idx--); ?></td>
                            <?php } ?>

                            <td class="center" style="padding:0"><?php echo gd_date_format('Y-m-d', $val['regDt']); ?></td>
                            <td class="center order-no">
                                <a href="./order_view.php?orderNo=<?= $val['orderNo']; ?>" title="주문번호" target="_blank" class="btn btn-link font-num"><?php echo $val['orderNo']; ?></a>
                                <br />
                                <?= $val['applicantNm']; ?>(<?= $val['applicantId']; ?>)
                            </td>
                            <td class="center"><?php echo $val['orderStatusStr']; ?></td>
                            <td class="center"><?php echo $val['requestNm']; ?> (<?= $val['requestId']; ?>)</td>
                            <td>
                                <div class="form-inline">사업자 번호 : <?=$val['taxBusiNo']?></div>
                                <div class="form-inline">회사명 : <?=$val['taxCompany']?></div>
                                <div class="form-inline">대표자명 : <?=$val['taxCeoNm']?></div>
                                <div class="form-inline">업태 : <?=$val['taxService']?></div>
                                <div class="form-inline">종목 : <?=$val['taxItem']?></div>
                                <div class="form-inline">사업장 주소 : <?=$val['taxZonecode']?> <?=$val['taxAddress']?> <?=$val['taxAddressSub']?></div>
                                <div class="form-inline">발행 이메일 : <?=$val['taxEmail']?></div>
                            </td>
                            <td class="center"><span class="font-num"><?php echo gd_currency_display($v['totalPrice']); ?></span></td>
                            <td class="center"><?php echo $taxFreeStr[$v['tax']]; ?></td>
                            <td class="center"><span class="font-num"><?php echo gd_currency_display($v['totalPrice']); ?></span></td>
                            <td class="center"><span class="font-num"><?php echo gd_currency_display($v['price']); ?></span></td>
                            <td class="center"><span class="font-num"><?php echo gd_currency_display($v['vat']); ?></span></td>
                            <td class="center"><?= gd_date_format('Y-m-d',$val['issueDt'])?></td>
                            <td class="center"><?php if($val['issueFl'] =='g') { echo "일반"; }  else {  echo "전자"; } ?>
                                <?php if($v['tax'] =='t') { echo "세금"; }?>
                                계산서
                            <td class="center">
                                <?php if($val['issueFl'] =='g') {
                                    if(empty($val['taxIssueInfo'][$v['tax']]['issueStatusFl']) == false) {
                                        echo $printStr[$val['taxIssueInfo'][$v['tax']]['printFl']];
                                    }
                                    if($val['taxIssueInfo'][$v['tax']]['printFl'] =='y') {
                                        echo gd_date_format('Y-m-d', $val['taxIssueInfo'][$v['tax']]['printDt']);
                                    }
                                } else {
                                    echo $godoBillStr[$val['taxIssueInfo'][$v['tax']]['issueStatusFl']];
                                    if($val['taxIssueInfo'][$v['tax']]['issueStatusFl'] =='n') {?>
                                        <div>
                                            <input type="button" onclick="resend_godobill('<?=$val['orderNo']?>','<?=$v['tax']?>');" value="재전송" class="btn btn-sm btn-black">
                                        </div>
                                    <?php } ?>
                                <?php } ?>
                            </td>
                            <td class="center"><?=$val['processDt']?></td>
                            <td class="center">
                                <textarea rows="15" cols="25" class="form-control" readonly/><?= $val['adminMemo'] ?></textarea>
                            </td>
                        </tr>

						<?php
					}
				}
			} else {
				?>
				<tr>
					<td class="center" colspan="15">검색된 정보가 없습니다.</td>
				</tr>
				<?php
			}
			?>

			</tbody>
		</table>



		<div class="table-action clearfix">
			<div class="pull-left form-inline">
				<button type="button" class="btn btn-white checkDelete">선택 삭제</button>
			</div>
			<div class="pull-right form-inline">
				<button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchOrder" data-search-count="<?= $page->recode['total'] ?>" data-total-count="<?= $page->recode['amount'] ?>" data-target-list-form="frmList" data-target-list-sno="orderNo">엑셀다운로드
			</div>
		</div>

	</form>

	<div class="center"><?php echo $page->getPage();?></div>



</div>




<script type="text/javascript">
	<!--

	$(document).ready(function () {
		// 삭제
		$('button.checkDelete').click(function () {
			var chkCnt = $('input:checkbox[name="orderNo[]"]:checked').length;
			if (chkCnt == 0) {
				alert('선택된 내역이 없습니다.');
				return;
			}
			if (confirm('선택한 ' + chkCnt + '개의 세금계산서 발행요청건을 정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.')) {
				$('#frmList input[name=\'mode\']').val('tax_invoice_delete');
				$('#frmList').attr('method', 'post');
				$('#frmList').attr('action', 'tax_invoice_ps.php');
				$('#frmList').submit();
			}
		});


		$('select[name=\'pageNum\']').change(function () {
			$('#frmSearchOrder').submit();
		});

		$('select[name=\'sort\']').change(function () {
			$('#frmSearchOrder').submit();
		});



		$('.btn-tax-invoice').click(function(e){

			var chkCnt = $('input:checkbox[name="orderNo[]"]:checked').length;
			if (chkCnt == 0 || chkCnt >  1) {
				alert('세금계산서를 출력할 주문을 한 건 선택해주세요.')
				return;
			}

			var orderNo = $('input:checkbox[name="orderNo[]"]:checked').val();
			var modeStr = $("#orderPrintMode").val();
			if(modeStr=='') {
				alert('인쇄모드를 선택해주세요');
				return false;
			}

			$("#frmOrderPrint input[name='modeStr']").val(modeStr);



			order_print_popup('taxInvoice', 'frmOrderPrint', 'frmList', 'orderNo[]', <?=$isProvider ? 'true' : 'false'?>);


		});

	});

	function resend_godobill(orderNo,taxFreeFl) {

		var parameters = {
			'mode': 'resend_godobill',
			'orderNo': orderNo,
			'taxFreeFl': taxFreeFl,
		};

		$.post('tax_invoice_ps.php', parameters, function (data) {

				var getData = $.parseJSON(data);

				if(getData[[1]]) alert(getData[[1]]);
				else alert('세금계산서가 재발급되었습니다.');

				document.location.reload();
		});

	}



	//-->
</script>
