<?php
use Framework\Utility\DateTimeUtils;

$classids = array( 'cssblue', 'cssred' ); //-- 공급받는자용, 공급자용
$headuser = array( '공급받는자용', '공급자용' ); //-- 공급받는자용, 공급자용
?>
<style type="text/css">
table.reception { margin: 0px auto 0px auto; height: 700px; }
#cssblue { width: 306px; border: solid 2px #364f9e; margin-right:5px; }
#cssblue table { border-collapse: collapse; }
#cssblue td { border-color:#364f9e; border-width:2px; border-style:solid; }

#cssblue #head { border-color:#364f9e; border-width:2px 2px 0px 2px; border-style:solid; }
#cssblue #head td { border-width:0px; border-style:solid; }

#cssred { width: 306px; border: solid 2px #ff4633; margin-left:5px; }
#cssred table { border-collapse: collapse; }
#cssred td { border-color:#ff4633; border-width:2px; border-style:solid; }

#cssred #head { border-color:#ff4633; border-width:2px 2px 0px 2px; border-style:solid; }
#cssred #head td { border-width:0px; border-style:solid; }
</style>
<?php
    $printCnt    = 0;
    foreach ($orderData as $key => $data) {
        $printCnt++;

        $settlePrice = $data['totalRealSettlePrice'];
        ?>
<div class="page-break">
<table cellspacing="10" cellpadding="0" border="0" align="center" class="reception">
<tr valign="top">
<?php
    for($loop=0; $loop < 2; $loop++) {
?>
	<td>
	<div id="<?= $classids[$loop];?>" class="<?= $classids[$loop];?>">

		<table id="head" cellspacing="0" cellpadding="0" width="100%" border="0">
		<tr>
			<td width="23%" height="40">&nbsp;</td>
			<td align="center" width="44%">&nbsp;<font size="4">영 수 증</font></td>
			<td width="33%"><font style="font-weight: normal" size="1">( <?= $headuser[$loop];?> )</font></td>
		</tr>
		</table>

		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td height="100%" valign="top" style="border-width: 3px 1px 0px 0px; background: url(<?= $sealPath;?>) no-repeat; background-position: 240px 35px;">
				<table cellspacing="0" cellpadding="2" width="100%" border="0">
				<col width="8%" /><col width="20%" /><col width="30%" /><col width="12%" />
				<tr>
					<td valign="bottom" colspan="2" height="22">no.</td>
					<td style="border-top-width: 0px;" align="right" colspan="3">&nbsp;<font style="font-weight: normal; font-size: 16px" color="black"><?= $data['orderName'];?>&nbsp;&nbsp;</font><font size=3>귀하</font>&nbsp;</td>
				</tr>
				<tr align="center">
					<td rowspan="4" height="35">공<br><br>급<br><br>자</td>
					<td>사 업 자<br>등록번호</td>
					<td colspan="3" align="left" style="padding-left:10px">&nbsp;<font size="3"><?= Globals::get('gMall.businessNo'); ?></font></td>
				</tr>
				<tr align="center" height="35">
					<td>상 호</td>
					<td>&nbsp;<?= Globals::get('gMall.companyNm');?></td>
					<td>성명</td>
					<td align="left">&nbsp;<?= Globals::get('gMall.ceoNm');?></td>
				</tr>
				<tr align="center" height="35">
					<td>사 업 장<br>소 재 지</td>
					<td colspan=3>&nbsp;<?= Globals::get('gMall.address');?> <?= Globals::get('gMall.addressSub');?></td>
				</tr>
				<tr align="center" height="35">
					<td>업태</td>
					<td>&nbsp;<?= Globals::get('gMall.service');?></td>
					<td>종목</td>
					<td>&nbsp;<?= Globals::get('gMall.item');?></td>
				</tr>
				</table>
			</td>
		</tr>
		</table>

		<table width="100%" border="0" cellspacing="0" cellpadding="2">
		<tr align="center" height="22">
			<td style="border-top-width: 0px;">작성년월일</td>
			<td style="border-left-width: 3px; border-right-width: 3px;">공급대가총액</td>
			<td style="border-top-width: 0px;">비 고</td>
		</tr>
		<tr align="center" height="22">
			<td>&nbsp; <?= gd_date_format('Y. m. d.', $data['regDt']);?></td>
			<td style="border-left-width: 3px; border-right-width: 3px; border-bottom-width: 4px;">&nbsp;￦<?php echo number_format($settlePrice);?></td>
			<td align="right"></td>
		</tr>
		</table>

		<table width="100%" border="0" cellspacing="0" cellpadding="4">
		<tr align="center" height="22">
			<td style="border-top-width: 0px; border-bottom-width: 0px;">위 금액을 정히 영수( 청구 )함</td>
		</tr>
		</table>

		<table width="100%" border="0" cellspacing="0" cellpadding="2">
		<col width="5%" /><col width="5%" /><col /><col width="10%" /><col width="15%" /><col width="15%" />
		<tr align="center" height="22">
			<td>월</td>
			<td>일</td>
			<td>품 목</td>
			<td>수량</td>
			<td>단가</td>
			<td>금액</td>
		</tr>
		<tr height="22">
			<td align="center"><?= gd_date_format('m', $data['regDt']);?></td>
			<td align="center"><?= gd_date_format('d', $data['regDt']);?></td>
			<td>&nbsp;<?= $data['orderGoodsNm'];?></td>
			<td align="center"><?= $data['orderGoodsCnt']?></td>
			<td align="right"><?= number_format($settlePrice);?>&nbsp;</td>
			<td align="right"><?= number_format($settlePrice);?>&nbsp;</td>
		</tr>
        <?php if (empty($data['refund']) == false && $data['refund']['refundFl'] == 'y') { ?>
        <tr height="22">
            <td align="center"><?= gd_date_format('m', $data['regDt']);?></td>
            <td align="center"><?= gd_date_format('d', $data['regDt']);?></td>
            <td>&nbsp;<?= $data['refund']['refundGoodsNm'];?></td>
            <td align="center"><?= $data['refund']['refundCnt'];?></td>
            <td align="right">-<?= number_format($data['refund']['refundPrice']);?>&nbsp;</td>
            <td align="right">-<?= number_format($data['refund']['refundPrice']);?>&nbsp;</td>
        </tr>
        <?php } ?>
		<tr>
			<td align="center" colspan=6>*** 이 하 여 백 *** </td>
		</tr>
		<?php for ($i = 1; $i < 13; $i++) { ?>
		<tr height="22">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<?php } ?>
		</table>

		<table width="100%" border="0" cellspacing="0" cellpadding="4">
		<tr align="center">
			<td style="border-top-width: 0px;" height="25"><font style="font-weight: normal" size="1">부가가치세법시행규칙 제25조 규정에 의한 ( 영수증 )으로 개정.</font></td>
		</tr>
		</table>
	</div>
	</td>
<?php
        }
?>
</tr>
</table>
<?php
        if ($printCnt != count($orderData)) {
            echo '<hr class="hidden-print" style="margin:20px 0px 20px 0px;  border-top:dashed 1px #000000;" />';
        }
?>
</div>
<?php
    }
?>
