<div class="table-title gd-help-manual mgt10">추천받은 회원아이디 내역</div>
<table class="table table-rows">
<thead>
<tr>
	<th class="width-2xs">번호</th>
	<th>이름</th>
	<th>아이디</th>
	<th>등급</th>
	<th>회원가입일</th>
	<th>승인</th>
</tr>
</thead>
<tbody>
<?php
if (isset($data) && is_array($data)) {
	foreach ($data as $val) {
		$txtAppFl = ( $val['appFl'] == 'y' ? '승인' : '미승인' );
?>
<tr class="center">
	<td class="font-num"><?php echo $page->idx--;?></td>
	<td><?php echo $val['memNm'];?></td>
	<td>
		<span class="font-eng"><?php echo $val['memId'];?></span>
		<?php if ($val['nickNm']){?><div><?php echo $val['nickNm'];?></div><?php }?>
	</td>
	<td><?php echo gd_isset($groups[$val['groupSno']]);?></td>
	<td class="font-date"><?php echo substr($val['entryDt'],2,8);?></td>
	<td><?php echo $txtAppFl;?></td>
</tr>
<?php
	}
}
?>
</tbody>
</table>
<div class="center"><?php echo $page->getPage();?></div>
