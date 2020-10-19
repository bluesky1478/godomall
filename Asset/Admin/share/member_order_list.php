<script type="text/javascript">
<!--
	$(document).ready(function(){
		self.focus();
	});
//-->
</script>

<div>
	<div class="phead_wrap mgt10"><div class="phead">
		<h2 id="logTitle">회원 주문내역 보기 <span><?php echo $info['memNm'];?> 회원님의 지금까지의 주문 내역 입니다.</span></h2>
	</div></div>

	<div>
		<table class="table table-cols">
		<colgroup><col class="width-sm" /><col/></colgroup>
		<tr>
			<th>회원 정보</th>
			<td>
				<span title="회원이름" class="bold note"><?php echo $info['memNm'];?></span>
				(<span title="아이디" class="notice-ref"><?php echo $info['memId'];?></span>)
			</td>
		</tr>
		<tr>
			<th>주문 건수</th>
			<td><span title="주문 금액" class="font-num"><?php echo $page->recode['total'];?> 건</span></td>
		</tr>
		<tr>
			<th>주문 금액</th>
			<td><span title="주문 금액" class="font-num"><?php echo number_format($info['saleAmt']);?> 원</span> (배송완료기준)</td>
		</tr>
		</table>
	</div>
</div>
<table class="table table-rows">
<thead>
<tr>
	<th>순서</th>
	<th>주문번호</th>
	<th>주문금액</th>
	<th>주문일</th>
	<th>처리상태</th>
	<th>결제방법</th>
</tr>
</thead>
<?php
if (empty($data) === false) {
	foreach ($data as $key => $val) {
?>
<tr>
	<td class="center number"><?php echo $page->idx--;?></td>
	<td class="center"><a onclick="order_view_popup('<?php echo $val['orderNo'];?>');" title="주문번호" class="order_no"><?php echo $val['orderNo'];?></a></td>
	<td class="center"><span title="주문 금액" class="num_form"><?php echo number_format($val['settlePrice']);?></span></td>
	<td class="center"><span title="주문일자" class="font-num"><?php echo gd_date_format('Y-m-d H:i', $val['regDt']);?></span></td>
	<td class="center"><span title="주문 상태"><?php echo $val['orderStatusStr'];?></span></td>
	<td class="center"><span title="주문 상태"><?php echo $val['settleKindStr'];?></span></td>
</tr>
<?php
	}
} else {
?>
<tr>
	<td class="center" colspan="6"><?php echo $info['memNm'];?> 회원님은 아직 주문을 하지 않으셨습니다.</td>
</tr>
<?php
}
?>
</table>

<div class="center"><?php echo $page->getPage();?></div>
