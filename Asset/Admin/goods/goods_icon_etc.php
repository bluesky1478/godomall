<script type="text/javascript">
<!--

//-->
</script>
<form id="frmIcon" name="frmIcon" target="ifrmProcess" action="./goods_ps.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="mode" value="icon_etc" />
<input type="hidden" name="iconType" value="<?=$iconType;?>" />


	<div class="page-header js-affix">
		<h3><?=end($naviMenu->location); ?> 수정</h3>
		<div class="btn-group">
			<input type="submit" value="저장" class="btn btn-red" />

		</div>
	</div>

	<div class="table-title gd-help-manual">
		상품 아이콘 관리
	</div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col /></colgroup>
		<tr>
			<th class="require">아이콘 이름</th>
			<td><?=$iconNm;?></td>
		</tr>
		<tr>
			<th>저장 경로</th>
			<td class="input_area bold"><?=UserFilePath::icon('goods_icon')->www();?></td>
		</tr>
		<tr>
			<th>아이콘 이미지</th>
			<td>
				<div class="form-inline">
					<div style="padding:10px;border:1px solid #AEAEAE;float:left;text-align:center;display:table-cell; vertical-align:middle;margin-right:10px;">
						<?=gd_html_icon($iconType);?>
					</div>
					<div style="float:left;">
						<input type="file" name="iconImage" value="" class="form-control" />
					</div>
				</div>
				<div style="clear:both;padding-bottom:10px;"></div>
				<div class="notice-danger" >반드시 GIF 이미지로 올려주셔야 합니다.</div>
				<div class="notice-info">아이콘 이미지 사이즈는 작게 해서 올려 주세요. 해당 이미지 크기 그대로 출력이 됩니다.</div>
			</td>
		</tr>
		</table>
	</div>
</form>
