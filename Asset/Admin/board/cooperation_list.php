<div>
	<div class="phead_wrap mgt0"><div class="phead">
		<h2><?php echo end($naviMenu->location);?> <span>광고제휴건으로 들어온 문의를 처리하실 수 있습니다 .</span></h2>
	</div></div>
</div>

<form id="frmSearchBase" method="get">
<input type="hidden" name="detailSearch" value="<?php echo gd_isset($search['detailSearch']);?>" />
<input type="hidden" name="sort" />
<input type="hidden" name="pageNum" />
<div>
<table class="table table-cols">
<colgroup><col class="width-sm" /><col/></colgroup>
<tr>
	<th>검색어</th>
	<td>
		<?php echo gd_select_box('key','key',array('all'=>'=통합검색=','title'=>'문의제목','content'=>'문의내용','reply'=>'답변','name'=>'이름'),'',gd_isset($search['key']));?>
		<input type="text" name="keyword" value="<?php echo gd_isset($search['keyword']);?>" class="form-control" />
			<span class="small_submit"><span class="button small black"><input type="submit" value="검색" /></span></span>
			<span class="button small blue"><input type="button" class="detailbuttom" value="상세검색펼침" /></span>
	</td>
</tr>
</table>
</div>

<div class="input_wrap display-none">
<table class="table table-cols">
<colgroup><col class="width-sm" /><col class="width-xl" /><col class="width-sm" /><col/></colgroup>
<tr>
	<th>문의분야</th>
	<td>
		<select name="itemCd" class="form-control">
		<option value="">== 전체 ==</option>
		<?php if (isset($itemCds) && is_array($itemCds)) { foreach($itemCds as $k => $v) {?>
		<option value="<?php echo $k;?>" <?php echo gd_isset($selected['itemCd'][$k]);?>><?php echo $v;?></option>
		<?php } }?>
		</select>
	</td>
	<th>접수일</th>
	<td>
		<input type="text" class="input_int_l datepicker" maxlength="10" name="regDt[]" value="<?php echo gd_isset($search['regDt'][0]);?>" />
		~ <input type="text" class="input_int_l datepicker" maxlength="10" name="regDt[]" value="<?php echo gd_isset($search['regDt'][1]);?>" />
	</td>
</tr>
<tr>
	<th>답변여부</th>
	<td>
	<input type="radio" name="replyFl" value="" <?php echo gd_isset($checked['replyFl']['']);?>>전체
	<input type="radio" name="replyFl" value="y" <?php echo gd_isset($checked['replyFl']['y']);?>>답변 후
	<input type="radio" name="replyFl" value="n" <?php echo gd_isset($checked['replyFl']['n']);?>>답변 전
	</td>
	<th>답변일</th>
	<td>
		<input type="text" class="input_int_l datepicker" maxlength="10" name="replyDt[]"  value="<?php echo gd_isset($search['replyDt'][0]);?>" />
		~ <input type="text" class="input_int_l datepicker" maxlength="10" name="replyDt[]" value="<?php echo gd_isset($search['replyDt'][1]);?>" />
	</td>
</tr>
<tr>
	<th>답변메일여부</th>
	<td>
	<input type="radio" name="mailFl" value="" <?php echo gd_isset($checked['mailFl']['']);?>>전체
	<input type="radio" name="mailFl" value="y" <?php echo gd_isset($checked['mailFl']['y']);?>>전송 후
	<input type="radio" name="mailFl" value="n" <?php echo gd_isset($checked['mailFl']['n']);?>>전송 전
	</td>
	<th>메일전송일</th>
	<td>
		<input type="text" class="input_int_l datepicker" maxlength="10" name="mailDt[]"  value="<?php echo gd_isset($search['mailDt'][0]);?>" />
		~ <input type="text" class="input_int_l datepicker" maxlength="10" name="mailDt[]" value="<?php echo gd_isset($search['mailDt'][1]);?>" />
	</td>
</tr>
</table>
</div>

<div class="big_submit display-none"><span class="button black"><input type="submit" value="검색" /></span></div>
</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
<input type="hidden" name="mode" value="">
	<div class="list_top">
		<div class="list_stat">총 <strong><?php echo $page->recode['amount'];?></strong>개, 검색 <strong><?php echo $page->recode['total'];?></strong>개, <strong><?php echo $page->page['now'];?></strong> of <?php echo $page->page['total'];?> Pages</div>
		<div class="list_option right">
			<select name="sort" class="input_select">
			<option value="regDt desc" <?php echo gd_isset($selected['sort']['regDt desc']);?>>- 등록일 ↑</option>
			<option value="regDt asc" <?php echo gd_isset($selected['sort']['regDt asc']);?>>- 등록일 ↓</option>
		    <optgroup label="------------"></optgroup>
			<option value="title desc" <?php echo gd_isset($selected['sort']['title desc']);?>>- 문의제목 ↑</option>
			<option value="title asc" <?php echo gd_isset($selected['sort']['title asc']);?>>- 문의제목 ↓</option>
			<option value="itemCd desc" <?php echo gd_isset($selected['sort']['itemCd desc']);?>>- 문의분야 ↑</option>
			<option value="itemCd asc" <?php echo gd_isset($selected['sort']['itemCd asc']);?>>- 문의분야 ↓</option>
			</select>&nbsp;
			<?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
		</div>
	</div>

	<table class="list_form">
	<colgroup><col width="30"/><col width="50"/><col width="110"/><col/>
	<col width="70" span="4"/>
	<col width="50"/>
	</colgroup>
	<thead>
	<tr>
		<th><input type="checkbox" id="chk_all" onclick="check_toggle('chk_all','chk[]')" /></th>
		<th>번호</th>
		<th>분야</th>
		<th>문의제목</th>
		<th>글쓴이</th>
		<th>접수일</th>
		<th>답변일</th>
		<th>답변메일</th>
		<th>답변</th>
	</tr>
	</thead>
	<tbody>
	<?php
    if (isset($data) && is_array($data)) {
        foreach ($data as $val) {
            $regDt = (substr($val['regDt'],2,8) != date('y-m-d')) ? substr($val['regDt'],2,8) : '<span class="">'.substr($val['regDt'],11).'</span>';
            $replyDt = substr($val['replyDt'],10);
            $replyDt = (str_replace('-', '', $replyDt ) > 0 ? '<span class="font-date">'.$replyDt.'</span>' : '미답변');
            $mailDt = substr($val['mailDt'],10);
            $mailDt = (str_replace('-', '', $mailDt ) > 0 ? '<span class="font-date">'.$mailDt.'</span>' : '미발송');
    ?>
	<tr class="center">
		<td><input type="checkbox" name="chk[]" value="<?php echo $val['sno'];?>" /></td>
		<td class="font-num"><?php echo $page->idx--;?></td>
		<td><?php echo $itemCds[$val['itemCd']];?></td>
		<td class="left bold"><?php echo $val['title'];?></td>
		<td><?php echo $val['name'];?></td>
		<td class="font-date"><?php echo $regDt;?></td>
		<td><?php echo $replyDt;?></td>
		<td><?php echo $mailDt;?></td>
		<td><span class="button small blue"><a href="./cooperation_register.php?sno=<?php echo $val['sno'];?>">쓰기</a></span></td>
	</tr>
<?php
        }
    }
?>
	</tbody>
	</table>

	<div class="pull-left">
		<span class="button black"><button class="checkDelete" type="button">삭제</button></span>
	</div>

	<div class="center"><?php echo $page->getPage();?></div>
</form>



<script type="text/javascript">
$(document).ready(function(){
	$('input.datepicker').datepicker({ dateFormat: 'yy-mm-dd' });

	// 상세검색
	if ($('#frmSearchBase input[name=detailSearch]').val() == 'y') {
		show_detailsearch();
	} else {
		hide_detailsearch();
	}

	// 정렬&출력수
	$('select[name=sort]').change(function(){
		$('input[name=sort]').val( $(this).val() );
		$('#frmSearchBase').submit();
	});
	$('select[name=pageNum]').change(function(){
		$('input[name=pageNum]').val( $(this).val() );
		$('#frmSearchBase').submit();
	});

	// 삭제
	$('button.checkDelete').click(function() {
		if ($(':checkbox:checked').length == 0 ) {
			alert('선택된 항목이 없습니다.');
			return;
		}
		if (confirm('선택한 항목을 삭제하시겠습니까?\n삭제된 항목은 복구하실 수 없습니다.')) {
			$('#frmList input[name=mode]').val('delete');
			$('#frmList').attr('method','post');
			$('#frmList').attr('action','../board/cooperation_ps.php');
			$('#frmList').submit();
		}
	});
});
/**
 * 상세검색(hide/show)
 */
function hide_detailsearch()
{
	$('#detailsearch').slideUp('slow');
	$('#frmSearchBase input[name=detailSearch]').val('');
}
function show_detailsearch()
{
	$('#detailsearch').slideDown('slow');
	$('#frmSearchBase input[name=detailSearch]').val('y');
}
</script>
