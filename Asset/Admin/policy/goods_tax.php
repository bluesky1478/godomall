<form id="frmGoodsTax" name="frmGoodsTax" action="./goods_ps.php" target="ifrmProcess" method="post">
<input type="hidden" name="mode" value="goods_tax" />
	<div class="page-header js-affix">
		<h3><?php echo end($naviMenu->location); ?>
		</h3>
		<div class="btn-group">
			<input type="submit" value="저장" class="btn btn-red" />
		</div>
	</div>
	<div class="table-title gd-help-manual">
		상품 부가세 설정
	</div>
	<table class="table table-cols">
		<colgroup><col class="width-md" /><col/></colgroup>
		<tr>
			<th>부가세 세율</th>
			<td>

					<table id="goodsAddTax" class="vat-set">
						<colgroup><col class="width-xs" /><col/></colgroup>
						<?php foreach($data['goodsTax'] as $k=> $v) {?>

							<tr id="goodsAddTax<?=$k?>">
								<?php if($k =='0') { ?>
									<td colspan="2"><label class="radio-inline"><input type="radio" name="taxBasic[goods]" value="<?=$k?>" <?php echo gd_isset($checked['goodsTax'][$v]);?>> <?=$v?>%<input type="hidden" name="goodsTax[<?=$k?>]" value="<?=$v?>"></label></td>
								<?php }  else if($k =='1') { ?>
									<td><label class="radio-inline"><input type="radio" name="taxBasic[goods]" value="<?=$k?>" <?php echo gd_isset($checked['goodsTax'][$v]);?>> <?=$v?>%(면세) </label><input type="hidden" name="goodsTax[<?=$k?>]" value="<?=$v?>"  ></td>
									<td class="bln"><input type="button" class="btn btn-white btn-icon-plus btn-sm" onclick="add_tex('goods');" value="추가" /></td>
								<?php }  else { ?>
									<td><div class="form-inline"><label ><input type="radio" name="taxBasic[goods]" value="<?=$k?>" <?php echo gd_isset($checked['goodsTax'][$v]);?>></label> <input type="text" class="form-control width-3xs js-number"  name="goodsTax[<?=$k?>]" value="<?=$v?>" >%</div></td>
									<td class="bln"><input type="button" onclick="field_remove('goodsAddTax<?=$k?>');" class="btn btn-white btn-icon-minus btn-sm" value="삭제" /></td>
								<?php } ?>

							</tr>
						<?php } ?>


					</table>

				</div>
				<div class="notice-info">
					선택된 세율은 <a href="/goods/goods_register.php" target="_blank" class="btn-link">상품&gt;상품관리&gt;상품등록</a> 에서 상품 등록 시 기본 세율이 됩니다.
				</div>
				<div class="notice-danger">
					세금계산서는 부가가치세율이 10% 또는 0%인 경우에만 발급할 수 있으며, 그 외 세율로 설정된 상품이 포함된 주문은 세금계산서가 발급되지 않으므로 유의 바랍니다.
				</div>

			</td>
		</tr>
	</table>
<br/>

	<div class="table-title gd-help-manual">
		배송비 부가세 설정
	</div>
	<table class="table table-cols">
		<colgroup><col class="width-md" /><col/></colgroup>
		<tr>
			<th>부가세 세율</th>
			<td>
				<table id="deliveryAddTax" class="vat-set">
					<colgroup><col class="width-xs" /><col/></colgroup>
					<?php foreach($data['deliveryTax'] as $k=> $v) {?>

						<tr id="deliveryAddTax<?=$k?>">
							<?php if($k =='0') { ?>
								<td colspan="2">
									<label class="radio-inline"><input type="radio" name="taxBasic[delivery]" value="<?=$k?>" <?php echo gd_isset($checked['deliveryTax'][$v]);?>> <?=$v?>%</label><input type="hidden" name="deliveryTax[<?=$k?>]" value="<?=$v?>"></td>
							<?php }  else if($k =='1') { ?>
								<td><label class="radio-inline"><input type="radio" name="taxBasic[delivery]" value="<?=$k?>" <?php echo gd_isset($checked['deliveryTax'][$v]);?>> <?=$v?>%(면세)</label> <input type="hidden" name="deliveryTax[<?=$k?>]" value="<?=$v?>"  ></td>
								<td class="bln"><input type="button" class="btn btn-white btn-icon-plus btn-sm" onclick="add_tex('delivery');" value="추가" /></td>
							<?php }  else { ?>
								<td><div class="form-inline"><label ><input type="radio" name="taxBasic[delivery]" value="<?=$k?>" <?php echo gd_isset($checked['deliveryTax'][$v]);?>></label> <input type="text" class="form-control width-3xs js-number"  name="deliveryTax[<?=$k?>]" value="<?=$v?>" >%</div></td>
								<td class="bln"><input type="button" onclick="field_remove('deliveryAddTax<?=$k?>');" class="btn btn-white btn-icon-minus btn-sm" value="삭제" /></td>
							<?php } ?>

						</tr>
					<?php } ?>

				</table>
				<div class="notice-info">
					선택된 세율은 <a href="/policy/delivery_regist.php" target="_blank" class="btn-link">기본설정&gt;배송정책&gt;배송비조건 등록</a> 에서 배송비조건 등록 시 기본 세율이 됩니다.
				</div>
				<div class="notice-danger">
					세금계산서는 부가가치세율이 10% 또는 0%인 경우에만 발급할 수 있으며, 그 외 세율로 설정된 상품이 포함된 주문은 세금계산서가 발급되지 않으므로 유의 바랍니다.
				</div>
			</td>
		</tr>
	</table>

</form>

<script type="text/javascript">
	<!--
	//비과세추가
	function add_tex(tbl){
		var fieldID		= tbl+'AddTax';

		if($('#'+fieldID).find('tr:last').length) {
			var fieldNoChk	= $('#'+fieldID).find('tr:last').get(0).id.replace(fieldID,'');
		} else {
			var fieldNoChk	= 0;
		}

		var fieldNo		= parseInt(fieldNoChk) + 1;
		var addHtml		= '';
		addHtml	+= '<tr id="'+fieldID+fieldNo+'">';
		addHtml	+= '<td><div class="form-inline"><input type="radio" name="taxBasic['+tbl+']" value="'+fieldNo+'"> <input type="text" name="'+tbl+'Tax['+fieldNo+']" value="" class="form-control width-3xs js-number" />%</td>';
		addHtml	+= '<td><input type="button" class="btn btn-white btn-icon-minus btn-sm" onclick="field_remove(\''+fieldID+fieldNo+'\');" value="삭제" /></div></td>';
		addHtml	+= '</tr>';
		$('#'+fieldID).append(addHtml);

		$('input.js-number').one('keyup', function (e) {
			$(this).number_only();

		});

		$('input.js-number').on('change', function (event) {
			if($(this).val() > 100) {
				$(this).val('100');
			}
		});

	}

	$(document).ready(function(){
		// 과세/비과세 설정 저장
		<?php if(gd_isset($checked['taxFreeFl']['f'])) { ?>
		$('input[name=\'taxPercent\']').prop('readonly', true);
		<?php } ?>

		$('input.js-number').on('change', function (event) {
			if($(this).val() > 100) {
				$(this).val('100');
			}
		});
	});
	//-->
</script>
