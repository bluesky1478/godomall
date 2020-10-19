<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>

    <?php //if (!isset($isProvider) && $isProvider != true) { ?>
    <div class="btn-group">
        <a href="../order/bankda_manual_match.php" class="btn btn-red-line bankdaManaulMatchALink">수동매칭</a>
    </div>
    <?php //} ?>
</div>

<form id="frmSearchBase" method="get" class="js-form-enter-submit">
	<input type="hidden" name="sort" />
	<input type="hidden" name="page_num" />
    <input type="hidden" name="query" />
    <input type="hidden" name="page" />

    <div class="table-title gd-help-manual">입금내역 검색</div>

	<table class="table table-cols">
		<colgroup>
			<col>
			<col>
		</colgroup>
		<tbody>
		<tr>
			<th>검색어</th>
			<td>
				<div class="form-inline">
					<?php echo gd_select_box('skey','skey',array('all'=>'=통합검색=','bkjukyo'=>'입금자명','bkinput'=>'입금예정금액','bkmemo4'=>'주문번호'),'',gd_isset($search['key']),'');?>
					<input type="text" name="sword" value="<?php echo gd_isset($search['keyword']);?>" class="form-control" />
				</div>
			</td>
			<th>현재상태/은행명</th>
			<td>
				<div class="form-inline">
					<select name="gdstatus" class="form-control">
						<option value=""> 전체 </option>
						<option value="N" <?php echo gd_isset($selected['gdstatus']['N']);?>>확인전</option>
						<option value="T" <?php echo gd_isset($selected['gdstatus']['T']);?>>매칭성공(by시스템)</option>
						<option value="B" <?php echo gd_isset($selected['gdstatus']['B']);?>>매칭성공(by관리자)</option>
                        <option value="M" <?php echo gd_isset($selected['gdstatus']['M']);?>>수동매칭</option>
						<option value="F" <?php echo gd_isset($selected['gdstatus']['F']);?>>매칭실패(불일치)</option>
						<option value="S" <?php echo gd_isset($selected['gdstatus']['S']);?>>매칭실패(동명이인)</option>
						<option value="A" <?php echo gd_isset($selected['gdstatus']['A']);?>>관리자입금확인완료</option>
						<option value="U" <?php echo gd_isset($selected['gdstatus']['U']);?>>관리자미확인</option>
					</select>

					<select name="bkname" class="form-control">
						<option value="">↓은행검색</option>
						<?php foreach ($rBank as $v){ ?>
							<option value="<?php echo $v;?>" <?php echo gd_isset($selected['bkname'][$v]);?>><?php echo $v;?></option>
						<?php } ?>
					</select>
				</div>
			</td>
		</tr>
		<tr>
			<th>입금일</th>
			<td colspan="3">
				<div class="form-inline">

					<div class="input-group js-datepicker">
						<input type="text" name="bkdate[]" value="<?= $search['bkdate'][0]; ?>" class="form-control width-xs">
									<span class="input-group-addon">
										<span class="btn-icon-calendar">
										</span>
									</span>
					</div>
					~
					<div class="input-group js-datepicker">
						<input type="text" name="bkdate[]" value="<?= $search['bkdate'][1]; ?>" class="form-control width-xs">
									<span class="input-group-addon">
										<span class="btn-icon-calendar">
										</span>
									</span>
					</div>

                    <?= gd_search_date(gd_isset($search['periodFl'], 6), 'bkdate[]', false) ?>
				</div>
			</td>
		</tr>

		<tr>
			<th>최종 매칭일</th>
			<td colspan="3">
				<div class="form-inline">

					<div class="input-group js-datepicker">
						<input type="text" name="gddate[]" value="<?= $search['gddate'][0]; ?>" data-init='n' class="form-control width-xs">
										<span class="input-group-addon">
											<span class="btn-icon-calendar">
											</span>
										</span>
					</div>
					~
					<div class="input-group js-datepicker">
						<input type="text" name="gddate[]" value="<?= $search['gddate'][1]; ?>" data-init='n' class="form-control width-xs">
										<span class="input-group-addon">
											<span class="btn-icon-calendar">
											</span>
										</span>
					</div>

                    <?= gd_search_date(gd_isset($search['periodFl'], 6), 'gddate[]', false) ?>
				</div>
			</td>
		</tr>
		</tbody>
	</table>

	<div class="center">
		<input type="button" value="실시간입금확인 실행하기" class="btn btn-lg btn-black" id="btnBankdaMatch">
		<input type="submit" value="통장입금내역 실시간조회" class="btn btn-lg btn-black" id="btnBankdaSearch">
	</div>
	<div>&nbsp;</div>
</form>

<form id="frmList" action="" method="get">
	<input type="hidden" name="mode" value="">
	<div class="table-header form-inline">
		<div class="pull-left">
            검색 <strong><span id="page_recode"><?php echo number_format($page->recode['total']); ?></span></strong>개 /
            전체 <strong><span id="page_rtotal"><?php echo number_format($page->recode['amount']); ?></span></strong>개
		</div>
		<div class="pull-right">
			<div>
				<select name="sort" class="form-control">
					<option value="bkdate desc" <?php echo gd_isset($selected['sort']['entryDt desc']);?>>입금일↑</option>
					<option value="bkdate asc" <?php echo gd_isset($selected['sort']['entryDt asc']);?>>입금일↓</option>
					<option value="gddatetime desc" <?php echo gd_isset($selected['sort']['lastLoginDt desc']);?>>최종매칭일↑</option>
					<option value="gddatetime asc" <?php echo gd_isset($selected['sort']['lastLoginDt asc']);?>>최종매칭일↓</option>
				</select>&nbsp;
				<?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 40, 60, 100]), '개 보기', Request::get()->get('page_num'), null); ?>
			</div>
		</div>
	</div>
	<table class="table table-rows" id="list_form">
		<colgroup><col width="60"><col width="10%"><col width="13%"><col width="10%"><col width="12%"><col><col width="10%"><col width="10%"><col width="13%"></colgroup>
		<thead>
		<tr>
			<th>번호</th>
			<th>입금완료일</th>
			<th>계좌번호</th>
			<th>은행명</th>
			<th>입금금액</th>
			<th>입금자명</th>
			<th>현재상태</th>
			<th>최종 매칭일</th>
			<th>주문번호</th>
            <th>메모</th>
		</tr>
		</thead>
		<tbody>
		</tbody>
	</table>

	<div class="center">
		<nav>
			<ul class="pagination pagination-sm" id="page_navi"></ul>
		</nav>
	</div>


	<div class="center" ><input type="button" value="일괄수정" class="btn btn-lg btn-black" id="btnAllEdit"></div>
	<input TYPE="hidden" name="nolist"/>
</form>

<script type="text/javascript">
	$(document).ready(function(){

		// 정렬&출력수
		$('select[name=sort]').change(function(){
			$('input[name=sort]').val( $(this).val() );
			$('#frmSearchBase').submit();
		});
		$('select[name=pageNum]').change(function(){
			$('input[name=page_num]').val( $(this).val() );
			$('#frmSearchBase').submit();
		});

		// Bankda Match
		$('#frmSearchBase').submit(function() {
            if($('input[name=sword]').val()) {
                $('input[name=sword]').attr('value', $('input[name=sword]').val());
            }
			account_list();
			return false;
		});
		// 실시간 입금확인실행 버튼
		$('#btnBankdaMatch').click(function() {
			layer_open_bank_matching();
		});
		// 일괄수정 버튼
		$('#btnAllEdit').click(function() {
			layer_open_batch_update();
		});

        // 주문번호 수정
        $('button[name="btnEditOrderNo"]').on('click', function() {
            modify_bankda_orderno($(this));
        });

		$('#frmSearchBase').submit();

	});

</script>

