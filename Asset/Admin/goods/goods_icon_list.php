<script type="text/javascript">
<!--

$(document).ready(function () {

	// 삭제
	$('button.checkDelete').click(function () {
		var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
		if (chkCnt == 0) {
			alert('선택된 아이콘이 없습니다.');
			return;
		}

        dialog_confirm('선택한 ' + chkCnt + '개의 아이콘을 정말로 삭제하시겠습니까?<br/>삭제시 정보는 복구 되지 않습니다.', function (result) {
            if (result) {
                $('#frmList input[name=\'mode\']').val('icon_delete');
                $('#frmList').attr('method', 'post');
                $('#frmList').attr('action', './goods_ps.php');
                $('#frmList').submit();
            }
        });
	});

	// 등록
	$('#checkRegister').click(function () {
		location.href = './goods_icon_register.php';
	});


	$('select[name=\'pageNum\']').change(function () {
		$('#frmSearchBase').submit();
	});

	$('select[name=\'sort\']').change(function () {
		$('#frmSearchBase').submit();
	});
});

//-->
</script>


<div class="page-header js-affix">
	<h3><?=end($naviMenu->location); ?></h3>
	<div class="btn-group">
		<input type="button" id="checkRegister" value="아이콘 등록" class="btn btn-red-line" />

	</div>
</div>




<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">

	<div class="table-title gd-help-manual">
		아이콘 검색
	</div>
	<div class="search-detail-box">
		<table class="table table-cols">
			<colgroup>
				<col class="width-sm"/>
				<col/>
				<col class="width-sm"/>
				<col/>
			</colgroup>
			<tr>
				<th>아이콘 이름</th>
				<td colspan="3">
					<input type="text" name="iconNm" value="<?=$search['iconNm'];?>" class="form-control width-lg" />
				</td>
			</tr>
			<tr>
				<th>기간검색</th>
				<td  colspan="3"><div class="form-inline">
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
			<tr>
				<th>기간제한 상태</th>
				<td>
					<label  class="radio-inline"><input type="radio" name="iconPeriodFl" value="" <?=gd_isset($checked['iconPeriodFl']['']);?> />전체</label>
					<label  class="radio-inline"><input type="radio" name="iconPeriodFl" value="n" <?=gd_isset($checked['iconPeriodFl']['n']);?> />무제한용</label>
					<label  class="radio-inline"><input type="radio" name="iconPeriodFl" value="y" <?=gd_isset($checked['iconPeriodFl']['y']);?> />기간제한용</label>
				</td>
				<th>사용상태</th>
				<td>
					<label  class="radio-inline"><input type="radio" name="iconUseFl" value="" <?=gd_isset($checked['iconUseFl']['']);?> />전체</label>
					<label  class="radio-inline"><input type="radio" name="iconUseFl" value="y" <?=gd_isset($checked['iconUseFl']['y']);?> />사용함</label>
					<label  class="radio-inline"><input type="radio" name="iconUseFl" value="n" <?=gd_isset($checked['iconUseFl']['n']);?> />사용안함</label>
				</td>
			</tr>
		</table>
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


<div>
	<form id="frmList" action="" method="get" target="ifrmProcess" >
	<input type="hidden" name="mode" value="">
	<table class="table table-rows">
	<thead>
	<tr>
		<th class="width5p center"><input type="checkbox"  class="js-checkall" data-target-name="sno"/></th>
		<th class="width5p">번호</th>
		<th class="width20p">아이콘 이름</th>
		<th class="width20p center">아이콘 이미지</th>
		<th class="width15p center">기간제한 상태</th>
		<th class="width15p center">사용상태</th>
		<th class="width10p center">등록일</th>
		<th class="width10p center">수정일</th>
		<th class="width5p center">수정</th>
	</tr>
	</thead>
	<tbody>
<?php
    if (gd_isset($data)) {
        $arrIconPeriod    = array('y' => '기간제한용', 'n' => '무제한용');
        $arrIconUse        = array('y' => '사용', 'n' => '사용안함');
        foreach ($data as $key => $val) {

?>
	<tr>
		<td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>"/></td>
		<td class="center number"><?=number_format($page->idx--);?></td>
		<td class="hand" onclick="show_popup('./goods_icon_register.php?popupMode=yes&sno=<?=$val['sno'];?>')"><?=$val['iconNm'];?></td>
		<td class="center"><?php if (empty($val['iconImage']) === false) { echo gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']); }?></td>
		<td class="center"><?=$arrIconPeriod[$val['iconPeriodFl']];?></td>
		<td class="center"><?=$arrIconUse[$val['iconUseFl']];?></td>
		<td class="center date"><?=gd_date_format('Y-m-d', $val['regDt']);?></td>
		<td class="center date"><?=gd_date_format('Y-m-d', $val['modDt']);?></td>
		<td class="center">
			<a href="./goods_icon_register.php?sno=<?=$val['sno'];?>" class="btn btn-white btn-xs"> 수정</a>
		</td>
	</tr>
<?php
        }
    } else {
?>
	<tr>
		<td class="center" colspan="8">검색된 정보가 없습니다.</td>
	</tr>
<?php
    }
?>
	</tbody>
	</table>


	<div class="table-action">
		<div class="pull-left">
			<button type="button" class="btn btn-white checkDelete">선택 삭제</button>
		</div>
	</div>


	</form>
	<div class="center"><?=$page->getPage();?></div>
</div>
