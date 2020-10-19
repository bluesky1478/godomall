<script type="text/javascript">
<!--
	$(document).ready(function(){
		self.focus();
	});
//-->
</script>

<div>
	<div class="phead_wrap mgt10"><div class="phead">
		<h2 id="logTitle">쿠폰 내역</h2>
	</div></div>

	<div>
		<table class="table table-rows">
		<thead>
		<tr>
			<th class="width10p">번호</th>
			<th>쿠폰이름</th>
			<th class="width15p">혜택</th>
			<th class="width15p">발급일</th>
			<th class="width15p">유효기간</th>
			<th class="width15p">사용일</th>
		</tr>
		</thead>
		<tbody>
	<?php
        if (gd_isset($data) && is_array($data)) {
            foreach ($data as $key => $val) {
    ?>
		<tr class="center">
			<td class="font-num"><?php echo number_format($pager->idx--);?></td>
			<td><?php echo $val['couponNm'];?></td>
			<td><span class="font-num"><?php echo $val['benefit'];?></span><?php echo (($val['benefitUnit'] == 'p')?'%':'원')?> <?php echo (($val['benefitMethod'] == 'dc')?'할인':'적립')?></td>
			<td class="font-date"><?php echo gd_date_format('Y-m-d', $val['regDt']);?></td>
			<td class="font-date">
			<?php
                if ($val['expireDays']) {
                    echo '발급후 ' . $val['expireDays'] . '일';
                }
                else {
                    echo $val['expireSdt'].'~'.$val['expireEdt'];
                }
            ?>
			</td>
			<td class="font-date"><?php echo $val['useDt'];?></td>
		</tr>
	<?php
            }
        } else {
    ?>
		<tr>
			<td class="no-data" colspan="6">쿠폰 내역이 없습니다.</td>
		</tr>
	<?php
        }
    ?>

		</tbody>
		</table>

		<div class="center"><?php echo $pager->getPage()?></div>
	</div>
</div>
