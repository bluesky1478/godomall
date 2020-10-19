<div>
	<div class="phead_wrap mgt10"><div class="phead">
		<h2>각 폴더별 페이지주소 <span>링크작업시 필요한 페이지 주소를 쉽게 확인하고 복사해서 사용하세요.</span></h2>
	</div></div>

	<!-- 폴더명 -->
	<table width="100%" border="1" bordercolor="#cccccc" style="border-collapse:collapse">
	<tr height="45" align="center">
	<?php
		$tableWidth    = 100 / count($arrMenus);
		foreach ($arrMenus as $key => $val) {
			$bgcolor = ($selectDir == $key ? '#60666d' : '858b93');
	?>
		<td width="<?php echo $tableWidth?>%" style="padding-top:3px" bgcolor="<?php echo $bgcolor;?>"><a href="./layer_pageurlcopy.php?selectDir=<?php echo $key?>" style="color:#fff;"><b><?php echo $val?></b><br /><?php echo $key?></a></td>
	<?php }?>
	</tr>
	</table>
	<!-- //폴더명 -->

	<!-- 페이지주소 -->
	<table width="100%" border="2" bordercolor="#cccccc" style="border-collapse:collapse; margin-top:5px;">
	<tr height="25" bgcolor="f2f2f2">
		<th style="padding-top:3px; color:#555;">폴더명</th>
		<th style="padding-top:3px; color:#555;">페이지설명</th>
		<th style="padding-top:3px; color:#555;">페이지주소</th>
	</tr>
	<?php foreach ($data as $i => $val) {?>
	<tr height="29">
		<td align="center" class="font-eng"><?php echo $selectDir?></td>
		<td style="padding-left:9px"><?php echo $val['fileText']?></td>
		<td style="padding-left:9px">
			<span class="font-eng"><?php echo $val['link']?></span>
			<span><span id="cl_url_<?php echo $i;?>" class="cl_url"><input type="hidden" value="<?php echo $val['link']?>"/></span></span>
		</td>
	</tr>
	<?php }?>
	</table>
	<!-- //페이지주소 -->
</div>



<script type="text/javascript">
<!--
$(document).ready(function(){
	// URL 복사
	// @todo 신규 클립보드로 변경해야 함
	$('.cl_url').each(function() {
		var id = $(this).attr('id');
		var link = $('input',$(this)).val();
		clipboard({'id':id,'copyText':link,'imgUrl':'<?=PATH_ADMIN_GD_SHARE?>img/design/btn_urlcopy.gif','callBack':'clipboardDone','width':42,'height':15,'cls':'urlcopy'});
	});
	$('.urlcopy').parent().attr('title', 'URL 복사').css({'vertical-align':'middle', 'display':'inline-block', 'cursor':'pointer'});
});
//-->
</script>
