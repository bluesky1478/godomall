<div class="page-header js-affix">
	<h3><?=end($naviMenu->location); ?></h3>
	<div class="btn-group">
		<input type="button" id="checkRegister" value="사은품 지급조건 등록" class="btn btn-red-line" />
	</div>
</div>

<form id="frmSearchGift" name="frmSearchGift" method="get" class="js-form-enter-submit">
	<div class="table-title ">
		지급조건 검색
	</div>
<input type="hidden" name="detailSearch" value="<?=$search['detailSearch'];?>" />
	<div class="search-detail-box">
		<table class="table table-cols">
		<colgroup><col class="width-sm" /><col /><col class="width-sm" /><col /></colgroup>
			<tbody>
            <?php if(gd_use_provider() === true) { ?>
			<?php if(gd_is_provider() === false) { ?>
			<tr>
				<th>공급사 구분</th>
				<td colspan="3">
					<label class="radio-inline">
						<input type="radio" name="scmFl" value="all" <?=gd_isset($checked['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/>전체
					</label>
					<label class="radio-inline">
						<input type="radio" name="scmFl" value="n" <?=gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/>본사
					</label>
					<label class="radio-inline">
						<input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?> onclick="layer_register('scm','checkbox')"/>공급사
					</label>
					<label>
						<button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','checkbox')">공급사 선택</button>
					</label>

					<div id="scmLayer" class="selected-btn-group <?=$search['scmFl'] == 'y' && !empty($search['scmNo']) ? 'active' : ''?>">
						<h5>선택된 공급사 : </h5>
						<?php if ($search['scmFl'] == 'y') {
							foreach ($search['scmNo'] as $k => $v) { ?>
								<span id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                </span>
							<?php }
						} ?>
					</div>
				</td>
			</tr>
            <?php } ?>
            <?php } ?>
            <tr>
                <th>사은품 지급조건명</th>
				<td  colspan="3">
					<input type="text" name="presentTitle" value="<?=$search['presentTitle'];?>" class="form-control width30p" />
				</td>
			</tr>
			<tr>
				<th >기간검색</th>
				<td  colspan="3"> <div class="form-inline">
						<select name="searchDateFl" class="form-control">
							<option value="regDt" <?=gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
							<option value="modDt" <?=gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
						</select>

						<div class="input-group js-datepicker">
							<input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][0]; ?>" >
						<span class="input-group-addon">
							<span class="btn-icon-calendar">
							</span>
						</span>
						</div>

						~  <div class="input-group js-datepicker">
							<input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][1]; ?>" >
						<span class="input-group-addon">
							<span class="btn-icon-calendar">
							</span>
						</span>
						</div>


						<?=gd_search_date($search['searchPeriod'])?>
				</td>
			</tr>
			</tbody>
			<tbody class="js-search-detail" class="display-none">
			<tr>
				<th>진행상태</th>
				<td>
					<label class="radio-inline"><input type="radio" name="stateFl" value="" <?=gd_isset($checked['stateFl']['']);?> />전체</label>
					<label class="radio-inline"><input type="radio" name="stateFl" value="s" <?=gd_isset($checked['stateFl']['s']);?> />대기</label>
					<label class="radio-inline"><input type="radio" name="stateFl" value="i" <?=gd_isset($checked['stateFl']['i']);?> />진행중</label>
					<label class="radio-inline"><input type="radio" name="stateFl" value="e" <?=gd_isset($checked['stateFl']['e']);?> />종료</label>
				</td>
				<th>지급기간</th>
				<td class="form-inline">
					<label class="radio-inline"><input type="radio" name="presentPeriodFl" value="n" <?=gd_isset($checked['presentPeriodFl']['n']);?> /> 제한 없음</label>
					<label class="radio-inline">
						<input type="radio" name="presentPeriodFl" value="y" <?=gd_isset($checked['presentPeriodFl']['y']);?> />
						<div class="input-group js-datepicker">
							<input type="text" class="form-control width-xs" name="periodStartYmd" value="<?=$search['periodStartYmd']; ?>"   onclick="$('input[name=\'presentPeriodFl\']').eq(1).prop('checked',true);">
							<span class="input-group-addon">
								<span class="btn-icon-calendar">
								</span>
							</span>
						</div>

						~  <div class="input-group js-datepicker">
							<input type="text" class="form-control width-xs" name="periodEndYmd" value="<?=$search['periodEndYmd']; ?>"  onclick="$('input[name=\'presentPeriodFl\']').eq(1).prop('checked',true);" >
							<span class="input-group-addon">
								<span class="btn-icon-calendar">
								</span>
							</span>
						</div>
					</label>

				</td>
			</tr>
			<tr>
				<th>상품조건</th>
				<td  colspan="3">
					<label class="radio-inline"><input type="radio" name="presentFl" value="" <?=gd_isset($checked['presentFl']['']);?> />전체</label>
					<label class="radio-inline"><input type="radio" name="presentFl" value="a" <?=gd_isset($checked['presentFl']['a']);?> />전체 상품</label>
					<label class="radio-inline"><input type="radio" name="presentFl" value="g" <?=gd_isset($checked['presentFl']['g']);?> />특정 상품</label>
					<label class="radio-inline"><input type="radio" name="presentFl" value="c" <?=gd_isset($checked['presentFl']['c']);?> />특정 카테고리</label>
					<label class="radio-inline"><input type="radio" name="presentFl" value="b" <?=gd_isset($checked['presentFl']['b']);?> />특정 브랜드</label>
				</td>
			</tr>
			<tr>
				<th>지급조건</th>
				<td class="input_area"  colspan="3">
					<label class="radio-inline"><input type="radio" name="conditionFl" value="" <?=gd_isset($checked['conditionFl']['']);?> />전체</label>
					<label class="radio-inline"><input type="radio" name="conditionFl" value="a" <?=gd_isset($checked['conditionFl']['a']);?> />무조건</label>
					<label class="radio-inline"><input type="radio" name="conditionFl" value="l" <?=gd_isset($checked['conditionFl']['l']);?> />구매 상품 수량만큼</label>
					<label class="radio-inline"><input type="radio" name="conditionFl" value="p" <?=gd_isset($checked['conditionFl']['p']);?> />금액별</label>
					<label class="radio-inline"><input type="radio" name="conditionFl" value="c" <?=gd_isset($checked['conditionFl']['c']);?> />수량별</label>
				</td>
			</tr>
			</tbody>
		</table>
		<button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색 <span>펼침</span></button>
	</div>

	<div class="table-btn">
		<input type="submit" value="검색" class="btn btn-lg btn-black">
	</div>


	<div class="table-header">
		<div class="pull-left">
			검색 <strong><?=number_format($page->recode['total']);?></strong>개 /
			전체 <strong><?=number_format($page->recode['amount']);?></strong>개
		</div>
		<div class="pull-right form-inline">
			<?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
			<?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
		</div>
	</div>
</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
	<input type="hidden" name="mode" value="">
	<table class="table table-rows space10">
	<thead>
	<tr>
		<th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
		<th class="width5p center">번호</th>
		<th class="">사은품 지급조건명</th>
		<th class="width10p">지급기간</th>
		<th class="width10p center">상품조건</th>
		<th class="width10p center">예외조건</th>
		<th class="width10p center">증정조건</th>
		<th class="width10p">공급사</th>
		<th class="width10p center">등록일/수정일</th>
		<th class="width5p">수정</th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (gd_isset($data)) {
		$arrPresentText        = array('a' => '전체 상품', 'g' => '특정 상품', 'c' => '특정 카테고리', 'b' => '특정 브랜드', 'e' => '특정 이벤트');
		$arrExceptText        = array('exceptGoodsNo' => '상품', 'exceptCateCd' => '카테고리', 'exceptBrandCd' => '브랜드', 'exceptEventCd' => '이벤트');
		$arrConditionText    = array('a' => '무조건', 'p' => '금액별', 'c' => '수량별', 'l' => '구매수량별');
		foreach ($data as $key => $val) {
			// 진행현황 처리
			if ($val['presentPeriodFl'] == 'y') {
				if ($val['periodStartYmd'] > date('Y-m-d H:i:s')) {
					$statusStr    = '<span class="">대기</span>';
				} else if ($val['periodEndYmd'] >= date('Y-m-d H:i:s')) {
					$statusStr    = '<span class="text-blue">진행중</span>';
				} else {
					$statusStr    = '<span class="text-red">종료</span>';
				}
				$periodStr    = '<br />'.$val['periodStartYmd'].' ~ '.$val['periodEndYmd'];
			} else {
				$statusStr    = '<span class="notice-ref">제한없음</span>';
				$periodStr    = '';
			}

			// 예외 조건
			$exceptFl['exceptGoodsNo']    = $val['exceptGoodsNo'];
			$exceptFl['exceptCateCd']    = $val['exceptCateCd'];
			$exceptFl['exceptBrandCd']    = $val['exceptBrandCd'];
			$exceptFl['exceptEventCd']    = $val['exceptEventCd'];
	?>
	<tr>
		<td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>"/></td>
		<td class="center"><?=number_format($page->idx--);?></td>
		<td onclick="show_popup('./gift_present_register.php?popupMode=yes&sno=<?=$val['sno']; ?>')" class="hand ">
			<div class="pdl5 "><?=$val['presentTitle'];?> </div></td>
		<td><div class="pdl5 "><?=$statusStr;?><?=$periodStr;?></div></td>
		<td class="center"><?php if ($val['presentFl'] != 'a') {?><input type="button" value="<?=$arrPresentText[$val['presentFl']];?>" onclick="layer_info_view('presentKindCd', 'info', '<?=$val['presentKindCd'];?>','<?=$val['presentFl'];?>');" class="btn btn-black btn-xs" /><?php } else { ?><?=$arrPresentText[$val['presentFl']];?><?php } ?></td>
		<td class="center space-top10">
	<?php
			foreach ($exceptFl as $eKey => $eVal) {
				if (!empty($eVal)) {
					echo ' <input type="button" value="'.$arrExceptText[$eKey].'" onclick="layer_info_view(\''.$eKey.'\', \'info\', \''.$val[$eKey].'\');"  class=" btn-black btn-xs" style="border:0px;" />';
				}
			}
	?>
		</td>
		<td class="center"><input type="button" value="<?=$arrConditionText[$val['conditionFl']];?>" onclick="layer_info_view('gift', 'gift', '<?=$val['sno'];?>');" class="btn btn-black btn-xs" /></td>
		<td  class="center"><?=$val['scmNm']; ?></td>
		<td class="center date"><?=gd_date_format('Y-m-d', $val['regDt']); ?><?php if ($val['modDt']) {
				echo "<br/>" . gd_date_format('Y-m-d', $val['modDt']);
			} ?></td>
		<td class="center padlr10">
			<a href="./gift_present_register.php?sno=<?=$val['sno'];?>" class="btn btn-white btn-xs">수정</a>
		</td>
	</tr>
	<?php
		}
	} else {
	?>
	<tr>
		<td class="center" colspan="10">검색된 정보가 없습니다.</td>
	</tr>
	<?php
	}
	?>
	</tbody>
	</table>

	<div class="table-action">
		<div class="pull-left">
			<button type="button" class="btn btn-white checkCopy">선택 복사</button>
			<button type="button" class="btn btn-white checkDelete">선택 삭제</button>
		</div>
		<div class="pull-right">
			<button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchGift" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>" data-target-list-form="frmList" data-target-list-sno="sno">엑셀다운로드</button>
		</div>
	</div>

</form>
	<div class="center"><?=$page->getPage();?></div>





<script type="text/javascript">
	<!--
	$(document).ready(function(){
		// 삭제
		$('button.checkDelete').click(function () {
			var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
			if (chkCnt == 0) {
				alert('선택된 사은품 증정이 없습니다.');
				return;
			}

			dialog_confirm('선택한 ' + chkCnt + '개 사은품증정을  정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
				if (result) {
					$('#frmList input[name=\'mode\']').val('present_delete');
					$('#frmList').attr('method', 'post');
					$('#frmList').attr('action', './gift_ps.php');
					$('#frmList').submit();
				}
			});

		});

		$('button.checkCopy').click(function () {
			var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
			if (chkCnt == 0) {
				alert('선택된 사은품 증정이 없습니다.');
				return;
			}

			dialog_confirm('선택한 ' + chkCnt + '개 사은품증정을  정말로 복사하시겠습니까?', function (result) {
				if (result) {
					$('#frmList input[name=\'mode\']').val('present_copy');
					$('#frmList').attr('method', 'post');
					$('#frmList').attr('action', './gift_ps.php');
					$('#frmList').submit();
				}
			});

		});

		// 등록
		$('#checkRegister').click(function () {
			location.href = './gift_present_register.php';
		});

		$('select[name=\'pageNum\']').change(function () {
			$('#frmSearchGift').submit();
		});

		$('select[name=\'sort\']').change(function () {
			$('#frmSearchGift').submit();
		});
	});

	/**
	 * 정보 보기
	 *
	 * @param string modeStr 레이어창 종류
	 * @param string typeStr 레이어창 타입
	 * @param string sno 사은품 증정 sno
	 */
	function layer_info_view(modeStr, typeStr, sno,modeType)
	{
		var loadChk	= $('#viewInfoForm').length;
		var title =  "";
		var mode = "";

		if(typeStr =='info') {
			if(modeStr =='exceptGoodsNo'||(modeStr =='presentKindCd' && modeType =='g' )) {
				if (modeStr== 'presentKindCd') {
					title = '상품 조건 - 특정 상품';
				} else {
					title = '예외 조건 - 상품';
				}
				mode = "goods";
			}

			if(modeStr =='exceptCateCd'||(modeStr =='presentKindCd' && modeType =='c' )) {
				if (modeStr== 'presentKindCd') {
					title = '상품 조건 - 특정 카테고리';
				} else {
					title = '예외 조건 - 카테고리';
				}
				mode = "category";
			}

			if(modeStr =='exceptBrandCd'||(modeStr =='presentKindCd' && modeType =='b' )) {
				if (modeStr== 'presentKindCd') {
					title = '상품 조건 - 특정 브랜드';
				} else {
					title = '예외 조건 - 브랜드';
				}
				mode = "brand";
			}

		}  else if (typeStr == 'gift') {
			title = '증정 사은품';
			mode = "gift";
		}

		$.post('../share/layer_terms_view.php',{ mode : mode, sno : sno }, function(data){
			if (loadChk == 0) {
				data = '<div id="viewInfoForm">'+data+'</div>';
			}
			var layerForm = data;

			BootstrapDialog.show({
				title:title,
				message: $(layerForm),
				closable: true
			});
		});
	}

	/**
	 * 카테고리 연결하기 Ajax layer
	 */
	function layer_register(typeStr, mode, isDisabled) {

		var addParam = {
			"mode": mode,
		};

		if (typeStr == 'scm') {
			$('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
		}

		if (!_.isUndefined(isDisabled) && isDisabled == true) {
			addParam.disabled = 'disabled';
		}

		layer_add_info(typeStr,addParam);
	}
	//-->
</script>
