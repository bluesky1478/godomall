<div class="page-header js-affix">
	<h3>상품 엑셀 다운로드</h3>
</div>

<div class="table-title gd-help-manual">
	<?=end($naviMenu->location); ?>
</div>

<form id="frmExcelGoods" name="frmExcelGoods" action="excel_goods_ps.php" method="post">
<input type="hidden" name="mode" value="excel_down" />
<input type="hidden" name="preFix" value="goods" />
<input type="hidden" name="downType" value="all" />
	<ul class="nav nav-tabs mgb30" role="tablist">
		<li class="active">
			<a href="#methodAll" data-toggle="tab">전체 상품 다운로드</a>
		</li>
		<li>
			<a href="#methodSearch" data-toggle="tab">검색 상품 다운로드</a>
		</li>
	</ul>
	<div class="tab-content">
		<div id="methodAll" class="tab-pane fade in active">
			<div class="table-title gd-help-manual">
				상품 전체를 다운로드 받습니다.
			</div>
			<table class="table table-cols">
			<colgroup><col class="width-md" /><col/></colgroup>
			<tr>
				<th>다운로드</th>
				<td>
					<input type="submit" class="btn btn-white btn-icon-excel" value="엑셀 다운로드" />
				</td>
			</tr>
			<tr id="methodAllScm">
				<?php if (gd_use_provider() === true) { ?>
					<?php if (gd_is_provider() === false) { ?>
						<th>공급사 구분</th>
						<td>
							<label class="radio-inline"><input type="radio" name="scmFl"
															   value="n" checked />본사
							</label>
							<label class="radio-inline">
								<input type="radio" name="scmFl" value="y"
									   onclick="layer_register('scm','radio',true)"/>공급사
							</label>
							<label>
								<button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','radio',true)">공급사 선택</button>
							</label>
							<div id="scmLayer" class="selected-btn-group <?= $data['scmNo'] ? 'active' : '' ?>">
							</div>
						</td>
					<?php } else { ?>
						<div class="sr-only">
							<input type="text" name="scmNo" value="<?= $data['scmNo'] ?>"/>
							<input type="radio" name="scmFl" value="y" checked="checked"/>
						</div>
					<?php } ?>
				<?php } else { ?>
					<div class="sr-only">
						<input type="hidden" name="scmNo" value="<?= DEFAULT_CODE_SCMNO ?>"/>
						<input type="radio" name="scmFl" value="n" checked="checked"/>
					</div>
				<?php } ?>
			</tr>
			<tr>
				<th>다운로드 범위</th>
				<td class="form-inline">
					<label class="radio-inline">
						<input type="radio" name="downRangeAll" value="all" checked="checked" />전체
					</label>
					<label class="radio-inline">
						<input type="radio" name="downRangeAll" value="part" />부분
					</label>
					<input type="text" name="partStartAll" value="1" class="form-control" /> 번째 부터
					<input type="text" name="partCntAll" value="300" class="form-control" /> 개의 상품
				</td>
			</tr>
			</table>
		</div>

		<div id="methodSearch" class="tab-pane fade">
			<div class="table-title gd-help-manual">
				상품 검색과 원하는 항목체크 후 다운로드 받기
			</div>
			<table class="table table-cols">
			<colgroup><col class="width-md" /><col class="width-2xl"/><col class="width-md" /><col/></colgroup>
			<tr>
				<th>다운로드</th>
				<td class="contents" colspan="3">
					<input type="submit" class="btn btn-white btn-icon-excel" value="엑셀 다운로드" />
				</td>
			</tr>
				<tr  id="methodSearchScm">

				</tr>
			<tr>
				<th>검색어</th>
				<td class="contents" colspan="3"><div class="form-inline">
					<?=gd_select_box('key','key',array('all'=>'=통합검색=','goodsNm'=>'상품명','goodsNo'=>'상품코드','goodsCd'=>'자체상품코드','goodsSearchWord'=>'검색 키워드'),null,null,null);?>
					<input type="text" name="keyword" value="" class="form-control" />
						</div>
				</td>
			</tr>
			<tr>
				<th>분류선택</th>
				<td class="contents" colspan="3">
					<div class="form-inline"><?=$cate->getMultiCategoryBox(null, null, 'class="form-control"'); ?></div>
				</td>
			</tr>
			<tr>
				<th>브랜드 선택</th>
				<td class="contents" colspan="3">
					<div class="form-inline"><?=$brand->getMultiCategoryBox(null, null, 'class="form-control"'); ?></div>
				</td>
			</tr>
			<tr>
				<th>상품가격</th>
				<td><div class="form-inline">
					<input type="text" name="goodsPrice[]" value="" class="form-control width-2xs" />원 ~
					<input type="text" name="goodsPrice[]" value="" class="form-control width-2xs" />원</div>
				</td>
				<th>마일리지</th>
				<td><div class="form-inline">
					<input type="text" name="mileage[]" value="" class="form-control width-2xs" /> ~
					<input type="text" name="mileage[]" value="" class="form-control width-2xs" /> </div>
				</td>
			</tr>
			<tr>
				<th>옵션 여부</th>
				<td>
					<label class="radio-inline"><input type="radio" name="optionFl" value="" checked="checked" />전체</label>
					<label class="radio-inline"><input type="radio" name="optionFl" value="y" />옵션사용</label>
					<label class="radio-inline"><input type="radio" name="optionFl" value="n" />옵션미사용</label>
				</td>
				<th>마일리지 정책</th>
				<td>
					<label class="radio-inline"><input type="radio" name="mileageFl" value="" checked="checked" />전체</label>
					<label class="radio-inline"><input type="radio" name="mileageFl" value="c" />통합설정</label>
					<label class="radio-inline"><input type="radio" name="mileageFl" value="g" />개별설정</label>
				</td>
			</tr>
			<tr>
				<th>추가상품 여부</th>
				<td>
					<label class="radio-inline"><input type="radio" name="addGoodsFl" value="" checked="checked" />전체</label>
					<label class="radio-inline"><input type="radio" name="addGoodsFl" value="y" />옵션사용</label>
					<label class="radio-inline"><input type="radio" name="addGoodsFl" value="n" />옵션미사용</label>
				</td>
				<th>텍스트옵션 여부</th>
				<td>
					<label class="radio-inline"><input type="radio" name="optionTextFl" value="" checked="checked" />전체</label>
					<label class="radio-inline"><input type="radio" name="optionTextFl" value="y" />옵션사용</label>
					<label class="radio-inline"><input type="radio" name="optionTextFl" value="n" />옵션미사용</label>
				</td>
			</tr>
			<tr>
				<th>상품출력 여부</th>
				<td>
					<label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="" checked="checked" />전체</label>
					<label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="y" />출력</label>
					<label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="n" />미출력</label>
				</td>
				<th>상품판매 여부</th>
				<td>
					<label class="radio-inline"><input type="radio" name="goodsSellFl" value="" checked="checked" />전체</label>
					<label class="radio-inline"><input type="radio" name="goodsSellFl" value="y" />판매</label>
					<label class="radio-inline"><input type="radio" name="goodsSellFl" value="n" />판매중지</label>
				</td>
			</tr>
			<tr>
				<th>상품 정렬 방식</th>
				<td>
	<?php
		$arrOrderBy    = array(
			'g.goodsNo asc'            => '등록일↑',
			'g.goodsNo desc'            => '등록일↓',
			'g.goodsNm asc'            => '상품명↑',
			'g.goodsNm desc'        => '상품명↓',
			'go.goodsPrice asc'        => '가격순↑',
			'go.goodsPrice desc'    => '가격순↓'
		);
		echo gd_select_box('orderBy','orderBy',$arrOrderBy,null,null,null);
	?>
				</td>
				<th>다운로드 범위</th>
				<td>
					<div class="form-inline">
						<label class="radio-inline"><input type="radio" name="downRange" value="all" />전체</label>
						<label class="radio-inline"><input type="radio" name="downRange" value="part" checked="checked" />부분</label>
						<input type="text" name="partStart" value="1" class="form-control" /> 번째 부터
						<input type="text" name="partCnt" value="100" class="form-control" /> 개의 상품
					</div>
				</td>
			</tr>
			<tr>
				<th>항목 선택</th>
				<td colspan="3">
					<table class="table table-rows goods-excel-down-item">
						<colgroup>
	<?php
		$tdCnt    = 3;
		for ($i = 0; $i < $tdCnt; $i++) {
	?>
							<col class="width-2xs" /><col class="width-xs"/><col class="width-xs"/>
	<?php	}    ?>
						</colgroup>
						<thead>
						<tr>
	<?php	for ($i = 0; $i < $tdCnt; $i++) {    ?>
							<th><input type="checkbox" class="js-checkall" name="fieldCheckAll" data-target-id="fieldCheck_<?=$i;?>"></th>
							<th>한글필드명</th>
							<th>영문필드명</th>
	<?php	}    ?>
						</tr>
						</thead>
						<tbody>
	<?php
		foreach ($excelField as $key => $val) {
			if (($key % $tdCnt) == 0 || $key == 0) {
				echo '					<tr>'.chr(10);
			}
	?>
							<td class="center"><input type="checkbox" name="fieldCheck[]" id="fieldCheck_<?=($key % $tdCnt).'_'.$val['dbKey'];?>" value="<?=$val['dbKey'];?>" /></td>
							<td><?=$val['text'];?></td>
							<td><?=$val['excelKey'];?></td>
	<?php
		if (($key % $tdCnt) == ($tdCnt - 1)) {
				echo '					</tr>'.chr(10);
			}
		}
	?>
						</tbody>
					</table>
				</td>
			</tr>
			</table>
		</div>
	</div>

</form>




<script type="text/javascript">
	$(document).ready(function(){
		$('#pricePercent').number_only();

		$('.nav-tabs a').click(function (e) {
			if($(this).attr('href') == '#methodAll') {

				$('#methodAll').attr('class','display-block');
				$('#methodSearch').attr('class','display-none');

				$('input[name=\'downType\']').val('all');

				if($("#methodAllScm").html().trim() =='') {
					$("#methodAllScm").html($("#methodSearchScm").html());
					$("#methodSearchScm").html('');
					$("#scmLayer").html('');
				}

			} else {

				$('#methodAll').attr('class','display-none');
				$('#methodSearch').attr('class','display-block');

				$('input[name=\'downType\']').val('search');

				if($("#methodSearchScm").html().trim() =='') {
					$("#methodSearchScm").html($("#methodAllScm").html());
					$("#methodAllScm").html('');
					$("#scmLayer").html('');
				}

			}

			$('.nav-tabs li').removeClass('active');
			$(this).closest('li').addClass('active');

			return false;
		});

		$("input[name='scmFl']").click(function () {
			if ($(this).val() == 'n') {
				$("#scmLayer").html('');
			}
		});

        $('input[name="fieldCheckAll"]:checkbox').trigger('click');
		//글로벌 데이터는 기본체크 안됨
		$('input[id*="globalData_"]:checkbox').prop("checked",false);
        //KC인증마크 관련 데이터는 기본체크 안됨
        $('input[id*="kcmark"]:checkbox').prop("checked",false);
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
		layer_add_info(typeStr, addParam);

	}

</script>
