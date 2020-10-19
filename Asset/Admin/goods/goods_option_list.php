<div class="page-header js-affix">
	<h3><?=end($naviMenu->location); ?> </h3>
	<div class="btn-group">
		<input type="button" id="checkRegister" value="옵션 등록" class="btn btn-red-line" />
	</div>
</div>


<form id="frmSearchOption" name="frmSearchOption" method="get" class="js-form-enter-submit">
	<div class="table-title gd-help-manual">
		옵션 검색
	</div>

	<table class="table table-cols">
	<colgroup><col class="width-md" /><col /></colgroup>
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
					<input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?> onclick="layer_register('scm', 'checkbox')"/>공급사
				</label>
				<label>
					<button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm', 'checkbox')">공급사 선택</button>
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
		<td><div class="form-inline">
			<?=gd_select_box('key','key',array('all'=>'=통합검색=','optionManageNm'=>'옵션 관리명','optionName'=>'옵션명'),null,$search['key'],null);?>
			<input type="text" name="keyword" value="<?=$search['keyword'];?>" class="form-control" />
			</div>
		</td>
	</tr>
	<tr>
		<th>옵션노출 방식</th>
		<td>
			<label class="radio-inline"><input type="radio" name="optionDisplayFl" value="" <?=gd_isset($checked['optionDisplayFl']['']);?> />전체</label>
			<label class="radio-inline"><input type="radio" name="optionDisplayFl" value="s" <?=gd_isset($checked['optionDisplayFl']['s']);?> />일체형</label>
			<label class="radio-inline"><input type="radio" name="optionDisplayFl" value="d" <?=gd_isset($checked['optionDisplayFl']['d']);?> />분리형</label>
		</td>
	</tr>
	<tr>
		<th>기간검색</th>
		<td>
			<div class="form-inline">
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
	</table>


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

<form id="frmList" action="" method="get" target="ifrmProcess" >
	<input type="hidden" name="mode" value="">
	<table class="table table-rows ">
	<thead>
	<tr>
		<th class="width5p"><input type="checkbox"  class="js-checkall" data-target-name="sno"/></th>
		<th class="width5p">번호</th>
		<th class="width30p">옵션 관리명</th>
		<th class="width10p">옵션표시</th>
		<th class="width20p">옵션명</th>
		<th class="width10p">공급사</th>
		<th class="width10p">등록일</th>
		<th class="width10p">수정일</th>
		<th class="width5p">수정</th>
	</tr>
	</thead>
	<tbody>
<?php
    if (gd_isset($data)) {
        $arrOptionDisplay    = array('s' => '일체형', 'd' => '분리형');
        foreach ($data as $key => $val) {
            $arrOptionName    = explode(STR_DIVISION,$val['optionName']);
?>
	<tr>
		<td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>"/></td>
		<td class="center number"><?=number_format($page->idx--);?></td>
		<td class="hand" onclick="show_popup('./goods_option_register.php?popupMode=yes&sno=<?=$val['sno']?>')"><?=$val['optionManageNm'];?></td>
		<td class="center"><?=$arrOptionDisplay[$val['optionDisplayFl']];?></td>
		<td class="center"><?=str_replace(STR_DIVISION,',',$val['optionName']);?></td>
		<td class="center"><?=$val['scmNm']?></td>
		<td class="center date"><?=gd_date_format('Y-m-d', $val['regDt']);?></td>
		<td class="center date"><?=gd_date_format('Y-m-d', $val['modDt']);?></td>
		<td class="center padlr10"><a href="./goods_option_register.php?sno=<?=$val['sno'];?>" class="btn btn-white btn-xs">수정</a></td>
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
			<button type="button" class="btn btn-white js-check-copy">선택 복사</button>
			<button type="button" class="btn btn-white js-check-delete">선택 삭제</button>
		</div>
	</div>

	<div class="center"><?=$page->getPage();?></div>
</form>

<script type="text/javascript">
	<!--
	$(document).ready(function(){

		// 삭제
		$('button.js-check-delete').click(function () {
			var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
			if (chkCnt == 0) {
				alert('선택된 자주쓰는 옵션이 없습니다.');
				return;
			}

			dialog_confirm('선택한 ' + chkCnt + '개의 자주쓰는 옵션을 정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
				if (result) {
					$('#frmList input[name=\'mode\']').val('option_delete');
					$('#frmList').attr('method', 'post');
					$('#frmList').attr('action', './goods_ps.php');
					$('#frmList').submit();
				}
			});

		});


		$('button.js-check-copy').click(function () {
			var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
			if (chkCnt == 0) {
				alert('선택된 자주쓰는 옵션이 없습니다.');
				return;
			}

			dialog_confirm('선택한 ' + chkCnt + '개의 자주쓰는 옵션을 복사하시겠습니까?', function (result) {
				if (result) {
					$('#frmList input[name=\'mode\']').val('option_copy');
					$('#frmList').attr('method', 'post');
					$('#frmList').attr('action', './goods_ps.php');
					$('#frmList').submit();
				}
			});

		});

		// 등록
		$('#checkRegister').click(function () {
			location.href = './goods_option_register.php';
		});


		$('select[name=\'pageNum\']').change(function () {
			$('#frmSearchOption').submit();
		});

		$('select[name=\'sort\']').change(function () {
			$('#frmSearchOption').submit();
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

