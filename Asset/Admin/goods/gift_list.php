<div class="page-header js-affix">
	<h3><?=end($naviMenu->location); ?> </h3>
	<div class="btn-group">
		<input type="button" id="checkRegister" value="사은품 등록" class="btn btn-red-line" />
	</div>
</div>

<form id="frmSearchGift" name="frmSearchGift" method="get" class="js-form-enter-submit">
	<div class="table-title ">
		사은품 검색
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
				<th>검색어</th>
				<td colspan="3"><div class="form-inline">
						<?=gd_select_box('key','key',array('all'=>'=통합검색=','giftNm'=>'사은품명','giftNo'=>'사은품코드','giftCd'=>'자체 사은품코드'),null,$search['key'],null);?>
						<input type="text" name="keyword" value="<?=$search['keyword'];?>" class="form-control" />
						</div>
				</td>
			</tr>
			<tr>
				<th>기간검색</th>
				<td colspan="3"><div class="form-inline">
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
					</div>
				</td>
			</tr>
			</tbody>
			<tbody class="js-search-detail" class="display-none">
			<tr>
				<th>브랜드</th>
				<td><div class="form-inline">
						<label><input type="text" name="brandCdNm" value="<?=$search['brandCdNm']; ?>"
									  class="form-control width-sm"  readonly onclick="layer_register('brand', 'radio')"/> </label>
						<label><input type="button" value="브랜드선택" class="btn btn-gray btn-sm" onclick="layer_register('brand', 'radio')"/></label>

						<div id="brandLayer" class="width100p">
							<?php if ($search['brandCd']) { ?>
								<span id="idbrand<?= $search['brandCd'] ?>" class="pull-left">
                        <input type="hidden" name="brandCd" value="<?= $search['brandCd'] ?>"/>
                        </span>
							<?php } ?>
						</div>
					</div>
				</td>
				<th>제조사</th>
				<td class="contents"><input type="text" name="makerNm" value="<?=$search['makerNm'];?>" class="form-control width-sm" /></td>
			</tr>
			<tr>
				<th>재고상태</th>
				<td class="contents" colspan="3">
					<label  class="radio-inline" ><input type="radio" name="stockFl" value="all" <?=gd_isset($checked['stockFl']['all']);?> />전체</label>
					<label  class="radio-inline" ><input type="radio" name="stockFl" value="n" <?=gd_isset($checked['stockFl']['n']);?> />제한없음</label>
					<label  class="radio-inline" ><input type="radio" name="stockFl" value="y" <?=gd_isset($checked['stockFl']['y']);?> />재고사용(재고있음)</label>
					<label  class="radio-inline" ><input type="radio" name="stockFl" value="x" <?=gd_isset($checked['stockFl']['x']);?> />재고사용(재고없음)</label>
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


<form id="frmList" action=""  target="ifrmProcess">
	<input type="hidden" name="mode" value="">
	<table class="table table-rows">
		<thead>
		<tr>
			<th class="width5p"><input type="checkbox" id="allCheck" value="y"
									   onclick="check_toggle(this.id,'giftNo');"/></th>
			<th class="width5p">번호</th>
			<th class="width40p">사은품명</th>
			<th class="width10p">공급사</th>
			<th class="width10p">브랜드</th>
			<th class="width10p">제조사</th>
			<th class="width10p">등록일</th>
			<th class="width10p">재고</th>
			<th class="width5p">수정</th>
		</tr>
		</thead>
		<tbody>
		<?php
		if (gd_isset($data)) {
			foreach ($data as $key => $val) {
				if ($val['stockFl'] == 'n') {
					$strStockFl    = '제한없음';
				} else {
					if ($val['stockCnt'] > 0) {
						$strStockFl    = number_format($val['stockCnt']);
					} else {
						$strStockFl    = '품절';
					}
				}
				?>
				<tr>
					<td class="center"><input type="checkbox" name="giftNo[<?=$val['giftNo']; ?>]" value="<?=$val['giftNo']; ?>"/></td>
					<td class="center number"><?=number_format($page->idx--);?></td>
					<td onclick="show_popup('./gift_register.php?popupMode=yes&giftNo=<?=$val['giftNo']; ?>')" class="hand">
						<?=gd_html_gift_image($val['imageNm'], $val['imagePath'], $val['imageStorage'], 40, $val['giftNm']);?>
						<?=$val['giftNm'];?>
					</td>
					<td class="center"><?=$val['scmNm'];?></td>
					<td class="center"><?=$val['brandNm'];?></td>
					<td class="center"><?=$val['makerNm'];?></td>
					<td class="center date"><?=gd_date_format('Y-m-d', $val['regDt']);?></td>
					<td class="center"><?=$strStockFl;?></td>
					<td class="center padlr10">
						<a href="./gift_register.php?giftNo=<?=$val['giftNo'];?>" class="btn btn-white btn-xs">수정</a>
					</td>
				</tr>
				<?php
			}
		} else {
			?>
			<tr>
				<td class="center" colspan="9">검색된 정보가 없습니다.</td>
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
			<button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchGift" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>" data-target-list-form="frmList" data-target-list-sno="giftNo">엑셀다운로드</button>
		</div>
	</div>
</form>

<div class="center"><?=$page->getPage();?></div>

<script type="text/javascript">
	<!--
	$(document).ready(function(){
		// 삭제
		$('button.checkDelete').click(function () {
			var chkCnt = $('input[name*="giftNo["]:checkbox:checked').length;
			if (chkCnt == 0) {
				alert('선택된 사은품이 없습니다.');
				return;
			}

			dialog_confirm('선택한 ' + chkCnt + '개 사은품을  정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
				if (result) {
					$('#frmList input[name=\'mode\']').val('delete');
					$('#frmList').attr('method', 'post');
					$('#frmList').attr('action', './gift_ps.php');
					$('#frmList').submit();
				}
			});

		});

		$('button.checkCopy').click(function () {
			var chkCnt = $('input[name*="giftNo["]:checkbox:checked').length;
			if (chkCnt == 0) {
				alert('선택된 사은품이 없습니다.');
				return;
			}

			dialog_confirm('선택한 ' + chkCnt + '개 사은품을  정말로 복사하시겠습니까?', function (result) {
				if (result) {
					$('#frmList input[name=\'mode\']').val('copy');
					$('#frmList').attr('method', 'post');
					$('#frmList').attr('action', './gift_ps.php');
					$('#frmList').submit();
				}
			});

		});

		// 등록
		$('#checkRegister').click(function () {
			location.href = './gift_register.php';
		});

		$('select[name=\'pageNum\']').change(function () {
			$('#frmSearchGift').submit();
		});

		$('select[name=\'sort\']').change(function () {
			$('#frmSearchGift').submit();
		});
	});

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



