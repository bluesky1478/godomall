<style>
	#layerGoodsListOptionSimpleFrm .content{max-height: 400px;border-bottom: 1px solid #dedede; padding-top:10px; padding-bottom: 30px;}
	#layerGoodsListOptionSimpleFrm .text-center{padding-top: 20px;}
</style>
<form name="layerGoodsListOptionSimpleFrm" id="layerGoodsListOptionSimpleFrm">
	<div class="content table-responsive">
		<table class="table-cols">
			<thead>
			<tr>
				<th>번호</th>
				<?php
				foreach($getGoodsOptionName as $optionNameVal) {
					?>
					<th class="width-md text-nowrap"><?=$optionNameVal;?></th>
					<?php
				}
				?>
				<th>옵션가</th>
				<th>재고량</th>
                <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                <!--<th>판매중지수량</th>
                <th>확인요청수량</th>-->
				<th>노출상태</th>
				<th>품절상태</th>
                <th>자체옵션코드</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach($goodsOptionInfo as $key => $val) {
				if($val['optionViewFl'] == 'y') {
					$optionViewText = "노출함";
				} else {
					$optionViewText = "노출안함";
				}
                $optionSellText = $stockReason[$val['optionSellFl']];
				?>
				<tr>
					<td class="center lmenu">
						<?= $key+1;?>
					</td>
					<?php
					foreach($getGoodsOptionName as $optionCntKey => $optionCntVal) {
						?>
						<td class="center lmenu text-nowrap">
							<?= $val['optionValue'.($optionCntKey+1)]; ?>
						</td>
						<?php
					}
					?>
					<td class="center lmenu text-nowrap">
						<?= gd_currency_display($val['optionPrice']);?>
					</td>
					<td class="center lmenu">
						<?= $val['stockCnt'];?>
					</td>
                    <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                    <!--
                    <td class="center lmenu">
                        <?php
                        if($val['sellStopFl'] == 'n'){
                            echo '사용안함';
                        }else{
                            echo $val['sellStopStock'];
                        }
                        ?>
                    </td>
                    <td class="center lmenu">
                        <?php
                        if($val['confirmRequestFl'] == 'n'){
                            echo '사용안함';
                        }else{
                            echo $val['confirmRequestStock'];
                        }
                        ?>
                    </td>
                    -->
					<td class="center lmenu">
						<?= $optionViewText;?>
					</td>
					<td class="center lmenu">
						<?= $optionSellText;?>
					</td>
                    <td class="center lmenu text-nowrap">
                        <?= $val['optionCode'];?>
                    </td>
				</tr>
			<?php }?>
			</tbody>
		</table>
	</div>
	<div class="text-center">
		<input type="button" value="확인" class="btn btn-white js-close" />
	</div>
</form>

<script type="text/javascript">
	$(document).ready(function () {
		$('.js-close').click(function(){
			layer_close();
		});
	});
</script>
