<div>
	<table class="table table-rows">
	<thead>
	<tr>
		<th class="width5p">번호</th>
        <?php
        foreach($optionName as $v){
            ?><th class="width3p"><?=$v?></th><?php
        };
        ?>
		<th class="width5p">옵션가</th>
		<th class="width5p">재고량</th>
		<!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
        <!--<th class="width3p">판매중지수량</th>
		<th class="width3p">확인요청수량</th>-->
		<th class="width3p">노출상태</th>
		<th class="width3p">품절상태</th>
        <th class="width3p">자체옵션코드</th>
	</tr>
	</thead>
	<tbody>
<?php
    if (gd_isset($data) && is_array($data)) {
        foreach ($data as $key => $val) {

			$optionDisplay = '노출함';
			if ($val['optionViewFl'] != 'y') $optionDisplay = '노출안함';
            if($val['optionSellFl'] == 't'){
                $optionSell = $stockReason[$val['optionSellCode']];
            }else{
                $optionSell = $stockReason[$val['optionSellFl']];
            }
?>
	<tr id="tbl_goods_<?php echo $val['goodsNo'];?>">
		<td class="center"><?php echo number_format($index + 1);?></td>
        <?php
        //옵션명 한줄로 만들기
        for($i=1;$i<=5;$i++){
            if(empty($val['optionValue'.$i])) continue;
            $tmpOption[] = $val['optionValue'.$i];
        }
        foreach($tmpOption as $v){
            ?><td class="center"><?=$v?></td><?php
        }
        unset ($tmpOption);
        ?>
		<td id="goodsPrice_<?php echo $val['goodsNo'];?>" class="center"><?php echo number_format($val['optionPrice']);?> 원</td>
        <td id="totalStock_<?php echo $val['goodsNo'];?>" class="center"><?php echo number_format($val['stockCnt']);?></td>
        <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
        <!--<td id=""><?php
            if($val['sellStopFl'] == 'n') echo __('사용안함');
            else echo $val['sellStopStock'];
            ?></td>
		<td id=""><?php
            if($val['confirmRequestFl'] == 'n') echo __('사용안함');
            else echo $val['confirmRequestStock'];
            ?></td>-->
		<td class="center"><?=$optionDisplay?></td>
		<td class="center"><?=$optionSell?></td>
        <td><?php echo $val['optionCode']; ?></td>
	</tr>
<?php
            $index++;
        }
    } else {
?>
	<tr>
		<td class="center" colspan="8">검색을 이용해 주세요.</td>
	</tr>
<?php
    }
?>

	</tbody>
	</table>

</div>
<div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black" onclick="closeLayer();" /></div>

<script type="text/javascript">
	<!--
	function closeLayer()
	{
	    $('div.bootstrap-dialog-close-button:last').click();
	}
	//-->
</script>
