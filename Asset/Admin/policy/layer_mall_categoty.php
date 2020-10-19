<div class="row mgb30">
	<?php foreach ($data as $key => $val) { ?>
		<div class="col-xs-4 mgb10">
			<label class="radio-inline">
				<input type="radio" name="category" value="<?=$val?>" /><?=$val?>
			</label>
		</div>
	<?php } ?>
</div>
<div class="text-center">
	<button type="button" class="btn btn-black btn-lg js-layer-close">저장</button>
</div>

<script type="text/javascript">
	<!--
	$(function(){
		$('.js-layer-close').bind('click', function(e){
			if ($('input[name=category]:radio:checked').length > 0) {
				var mallCategory	= $('input[name=category]:radio:checked').val();
				$('input[name=mallCategory]').val(mallCategory);
				$('#mallCategory').html(mallCategory);
			} else {
				alert('대표카테고리를 선택해 주세요!');
				return false;
			}
		});
	});
	//-->
</script>
