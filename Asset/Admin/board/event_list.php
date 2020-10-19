<script type="text/javascript">
<!--

function deleteData(sno) {
	if (confirm("삭제하시겠습니까?")) {
		$("#frmList input[name=sno]").val(sno);
		$("#frmList").submit();
	}
}

$(document).ready(function(){
	$('input.datepicker').datepicker({ dateFormat: 'yy-mm-dd' });
	// 삭제
	$('button.checkDelete').click(function() {
		if ($(':checkbox:checked').length == 0 ) {
			alert('선택된 항목이 없습니다.');
			return;
		}
		if (confirm('선택한 항목을 삭제하시겠습니까?\n삭제된 항목은 복구하실 수 없습니다.')) {
			$('#frmList input[name=mode]').val('delete');
			$('#frmList').attr('method','post');
			$('#frmList').attr('action','../board/board_ps.php');
			$('#frmList').submit();
		}
	});

	$("#frmList").formProcess('alert', [
		{inputName:'sno', name:'번호', required:true}
	]);
});

//-->
</script>

<div>
	<div class="phead_wrap mgt0"><div class="phead">
		<h2><?=end($naviMenu->location);?> <span>이벤트페이지를 직접 디자인하고 이벤트상품들을 선정하실 수 있습니다 </span></h2>
	</div></div>
</div>

<form id="frmSearchBase" method="get">
<div>
	<table class="table table-cols">
	<colgroup><col class="width-sm" /><col/><col class="width-sm" /><col/></colgroup>
	<tr>
		<th>검색어</th>
		<td>
			<?=gd_select_box('skey','skey',array('all'=>'=통합검색=','subject'=>'제목','contents'=>'내용'),'',gd_isset($search['skey']));?>
			<input type="text" name="sword" value="<?=gd_isset($search['sword']);?>" class="form-control" size="20" />
		</td>
		<th>기간검색</th>
		<td>
			<input type="text" name="startDt" value="<?=gd_isset($search['startDt']);?>" class="input_date datepicker" /> -
			<input type="text" name="endDt" value="<?=gd_isset($search['endDt']);?>" class="input_date datepicker" />
		</td>
	</tr>
	</table>
	<div class="center"><span class="button black"><input type="submit" value="검색" /></span></div>
</div>
</form>

<form id="frmList" action="event_ps.php" method="post">
<input type="hidden" name="mode" value="delete">
<input type="hidden" name="sno" value="">
	<div class="list_top">
		<div class="list_stat">총 <strong><?=$pager->recode['amount'];?></strong>개, 검색 <strong><?=$pager->recode['total'];?></strong>개, <strong><?=$pager->page['now'];?></strong> of <?=$pager->page['total'];?> Pages</div>
	</div>

	<table class="list_form">
	<colgroup><col class="width-2xs" /><col /><col class="width-sm" /><col class="width-sm" /><col class="width-2xs" /><col class="width-2xs" /><col class="width-2xs" /></colgroup>
	<thead>
	<tr>
		<th>번호</th>
		<th>제목</th>
		<th>이벤트시작일</th>
		<th>이벤트종료일</th>
		<th>미리보기</th>
		<th>수정</th>
		<th>삭제</th>
	</tr>
	</thead>
	<tbody>
<?php
    if (gd_array_is_empty($data) === false) {
       foreach ($data as $val) {
?>
	<tr class="center">
		<td class="font-num"><?=number_format($pager->idx--);?></td>
		<td><a href="./event_regist.php?mode=modify&sno=<?=$val['sno']?>"><?=$val['subject']?></a></td>
		<td class="font-date"><?=$val['startDt'];?></td>
		<td class="font-date"><?=$val['endDt'];?></td>
		<td><span class="button small black"><a href="/event/event.php?sno=<?=$val['sno']?>" target="_blank">미리보기</a></span></td>
		<td><span class="button small blue"><a href="./event_regist.php?mode=modify&sno=<?=$val['sno']?>">수정</a></span></td>
		<td><span class="button small red"><a onclick="deleteData('<?=$val['sno']?>')">삭제</a></span></td>
	</tr>
<?php
        }
    }
?>
	</tbody>
	</table>

	<div class="rigthbutton">
		<span class="button black"><a href="event_regist.php?mode=regist">이벤트등록</a></span>
	</div>

	<div class="center"><?=$pager->getPage();?></div>
</form>

<div class="one-line"></div>

