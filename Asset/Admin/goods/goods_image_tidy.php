<div>
	<div class="phead_wrap mgt0"><div class="phead">
		<h2><?=end($naviMenu->location);?> <span>업로드된 상품 이미지중 사용하지 않는 이미지를 정리합니다.</span></h2>
	</div></div>
</div>

<div>
	<div class="table-title gd-help-manual">
		상품 이미지 일괄 정리 <span>복사/수정 등으로 사용하지 않는 이미지를 정리합니다.</span>
	</div>

	<div>
		<table class="table table-cols">
		<colgroup><col class="width-sm" /><col/></colgroup>
			<tr>
				<th>진행 정보</th>
				<td>정리할 상품수 : <?=number_format($goodsCnt);?>개</td>
			</tr>
			<tr>
				<th>진행 상태</th>
				<td>
					<div id="progressText">0 %</div>
					<div style="height:10px; border:1px solid #d9d9d9; background-color:#FFFFFF; width:100%">
						<div id="progressBar" style="height:6px; background-color:#FF0000; width:0%"></div>
					</div>
				</td>
			</tr>
		</table>
		<?php if ($goodsCnt > 0) {?>
		<div id="processBtn" class="center"><span class="button blue"><a href="./goods_image_tidy_ps.php?mode=goods_image_tidy" target="goodsImageProcess">일괄 정리</a></span></div>
		<?php }?>
	</div>
</div>
<br />
<!-- 처리 Iframe -->
<iframe name="goodsImageProcess" id="goodsImageProcess" src="<?=URI_HOME?>blank.php" width="100%" height="300" class="display-none"></iframe>
<!-- //처리 Iframe -->
