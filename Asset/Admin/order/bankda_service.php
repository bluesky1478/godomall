<div class="page-header js-affix">
	<h3><?php echo end($naviMenu->location); ?>
	</h3>
</div>
<p>
	<div>
	<?php if (empty($ceoName) === true) {?>
		대표자명을 입력하셔야 자동입금확인 서비스를 신청할 수 있습니다. <span><a href="../policy/base_info.php">대표자명 입력하기</a></span><br/>
	<?php }?>
	</div>
</p>

<iframe name="ifrout" src="<?php echo $ifrsrc;?>" frameborder="0" width="100%" height="1000"></iframe>
