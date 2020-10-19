
<style type="text/css">
    .order_title {margin:10px 40px 10px 40px; padding:10px 30px 10px 30px; border:3px solid #627DCE; background-color:#F7F7F7; font-size:11px; line-height:16px;}
    td, th { font-family: 돋움; font-size: 9pt; color: 333333;}

    #cssblue { margin:0px auto 0px auto; width: 604px; border: solid 2px #364F9E;  }
    #cssblue strong { font:18pt 굴림; color:#364F9E; font-weight:bold; }
    #cssblue table { border-collapse: collapse; }
    #cssblue td, #cssblue table { border-color: #364F9E; border-style: solid; border-width: 0; }

    #cssblue #head { border-width: 1px 1px 0 1px; }
    #cssblue #box td { border-width: 1px; padding-top: 3px; }

    #cssred { margin:0px auto 0px auto; width: 604px; border: solid 2px #FF4633;  }
    #cssred strong { font:18pt 굴림; color:#FF4633; font-weight:bold; }
    #cssred table { border-collapse: collapse; }
    #cssred td, #cssred table { border-color: #FF4633; border-style: solid; border-width: 0; }

    #cssred #head { border-width: 1px 1px 0 1px; }
    #cssred #box td { border-width: 1px; padding-top: 3px; }
</style>


<?php
$noList = [];
foreach($orderData as $key => $val) {
    if(empty($val) === true) {
        $noList[] = $key;
    }
}
if (empty($noList) === false) {
?>
    <div class="panel panel-default hidden-print">
        <div class="panel-heading">
            <h4>아래 주문은 <span class="text-danger">세금계산서를 출력할 수 없습니다.</span><br>
            세금계산서 발행을 신청하지 않았거나, 승인이 안된 상태입니다.</h4>
        </div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>주문번호</dt>
                <dd>
                    <ul>
                        <?php foreach ($noList as $orderNo) { ?>
                        <li><?=$orderNo?></li>
                        <?php } ?>
                    </ul>
                </dd>
            </dl>
        </div>
    </div>
<?php } ?>

<?php foreach($orderData as $key => $val) { ?>
<div class="page-break">
<?php foreach($taxInfo['classids'] as $k => $v) { ?>
<?php if($val) { ?>
<?php if ($k != 0) echo '<hr style="border:1px dashed #d9d9d9;" />'; ?>
<?php foreach($val['taxInvoiceInfo'] as $k1 => $v1) { ?>
<?php if($v1['totalPrice'] > 0) { ?>
<div id="<?=$v?>" class="<?=$v?>">
    <table id="head" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="50%" align="right"><strong><?php if($v1['tax'] =='t') echo "세 금"; ?>계 산 서</strong></td>
                        <td width="50%" style="padding-left:6px">(<?=$taxInfo['headuser'][$v]?>)</td>
                    </tr>
                </table>
            </td>
            <td width="60" style="border-right-width: 3px;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td height="28" align="center">책&nbsp;번&nbsp;호</td>
                    </tr>
                    <tr>
                        <td height="24" align="center">일련번호</td>
                    </tr>
                </table>
            </td>
            <td width="150">
                <table width="100%" border="0" cellspacing="0" cellpadding="4">
                    <tr height="26">
                        <td width="50%" align="right" style="border-right-width: 1px; border-bottom-width: 1px;"> 권</td>
                        <td width="50%" align="right" style="border-bottom-width: 1px;"> 호</td>
                    </tr>
                    <tr height="26">
                        <td align="center" style="border-right-width: 1px;">&nbsp;</td>
                        <td align="center">&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <col width="3%"><col width="47%"><col width="3%">
        <tr>
            <!-- 공급자 -->
            <td align="center" style="border-width: 3px 1px 0px 1px; padding-left: 2px; line-height: 36px;">공<br>급<br>자</td>
            <td valign="top" height="100%" style="border-width: 3px 1px 0 0; background: url(<?=$sealPath?>) no-repeat; background-position: 210px 18px;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <col width="53"><col width="100"><col width="26">
                    <tr height="38" align="center">
                        <td style="border-width: 0 1px 1px 2px;">등록번호</td>
                        <td colspan="3" style="border-width: 0 2px 1px 0; padding-left:6px;"><?=$gMall['businessNo']?></td>
                    </tr>
                    <tr height="38" align="center">
                        <td style="border-width: 0 1px 3px 2px;">상&nbsp;&nbsp;&nbsp;&nbsp;호<br>(법인명) </td>
                        <td style="padding:0 6px; border-bottom-width:3px;"><?=$gMall['companyNm']?></td>
                        <td style="border-width: 0 1px 3px 1px;">성명</td>
                        <td style="border-width: 0 2px 3px 0; padding-right:10px;" align="left">&nbsp;<?=$gMall['ceoNm']?></td>
                    </tr>
                    <tr height="38" align="center">
                        <td style="border-width: 0 1px 1px 0px;">사 업 장<br>소 재 지 </td>
                        <td colspan="3" style="padding: 0 6px; border-bottom-width: 1px;" align="left"><?=$gMall['address']?> <?=$gMall['addressSub']?></td>
                    </tr>
                    <tr height="38" align="center">
                        <td style="border-right-width: 1px;">업&nbsp;&nbsp;&nbsp;&nbsp;태</td>
                        <td style="padding:0 6px;"><?=$gMall['service']?></td>
                        <td style="border-width: 0 1px;">종<br>목 </td>
                        <td style="padding: 0 6px;"><?=$gMall['item']?></td>
                    </tr>
                </table>
            </td>
            <!-- 공급받는자 -->
            <td align="center" style="border-top-width: 3px; border-right-width: 1px; line-height: 22px; padding-left: 2px">공<br>급<br>받<br>는<br>자</td>
            <td valign="top" height="100%" style="border-top-width: 3px;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <col width="53"><col width="100"><col width="26">
                    <tr height="38" align="center">
                        <td style="border-width: 0 1px 1px 2px;">등록번호</td>
                        <td colspan="3" style="border-bottom-width: 1px; padding-left:6px;"><?=$val['taxBusiNo']?></td>
                    </tr>
                    <tr height="38" align="center">
                        <td style="border-width: 0 1px 3px 2px;">상&nbsp;&nbsp;&nbsp;&nbsp;호<br>(법인명) </td>
                        <td style="padding:0 6px; border-bottom-width:3px;"><?=$val['taxCompany']?></td>
                        <td style="border-width: 0 1px 3px 1px;">성명</td>
                        <td style="border-bottom-width: 3px; padding-right:10px;"><?=$val['taxCeoNm']?></td>
                    </tr>
                    <tr height="38" align="center">
                        <td style="border-width: 0 1px 1px 0px;">사 업 장<br>소 재 지 </td>
                        <td colspan="3" style="padding: 0 6px; border-bottom-width: 1px;" align="left"><?=$val['taxAddress']?> <?=$val['taxAddressSub']?></td>
                    </tr>
                    <tr height="38" align="center">
                        <td style="border-right-width: 1px;">업&nbsp;&nbsp;&nbsp;&nbsp;태</td>
                        <td style="padding:0 6px;"><?=$val['taxService']?></td>
                        <td style="border-width: 0 1px;">종<br>목 </td>
                        <td style="padding: 0 6px;"><?=$val['taxItem']?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table id="box" width="100%" border="0" cellpadding="0" cellspacing="0" style="border-top-width:3px; border-bottom-width:2px;">
        <col width="34"><colgroup span="2" width="20"></colgroup><col width="34"><colgroup span="11"></colgroup><?php if($v1['tax'] =='t') { ?><col width="1"><colgroup span="10"></colgroup><col width="64"><?php } else { ?><col width="240"><?php } ?>
        <tr align="center">
            <td colspan="3">작&nbsp;&nbsp;&nbsp;성</td>
            <td colspan="12">공&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;급&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;가&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;액</td>
            <?php if($v1['tax'] =='t') { ?>
            <td><img src="" width="1" height="1" /></td>
            <td colspan="10" style="border-right-width:3px;">세&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;액</td>
            <?php } ?>
            <td >비&nbsp;&nbsp;&nbsp;&nbsp;고</td>
        </tr>
        <tr align="center">
            <td>년</td>
            <td>월</td>
            <td>일</td>
            <td style="letter-spacing:-2px">공란수</td>
            <td>백</td>
            <td>십</td>
            <td>억</td>
            <td>천</td>
            <td>백</td>
            <td>십</td>
            <td>만</td>
            <td>천</td>
            <td>백</td>
            <td>십</td>
            <td>일</td>
            <?php if($v1['tax'] =='t') { ?>
            <td><img src="" width="1" height="1" /></td>
            <td>십</td>
            <td>억</td>
            <td>천</td>
            <td>백</td>
            <td>십</td>
            <td>만</td>
            <td>천</td>
            <td>백</td>
            <td>십</td>
            <td>일</td>
            <?php } ?>
            <td style="border-left-width:3px;">&nbsp;</td>
        </tr>
        <tr align="center" height="34">
            <td><?=gd_date_format('Y',$val['issueDt'])?></td>
            <td><?=gd_date_format('m',$val['issueDt'])?></td>
            <td><?=gd_date_format('d',$val['issueDt'])?></td>
            <td>&nbsp;<?=(11-strlen($v1['price']))?></td>


            <?php for ($ixx = (strlen($v1['price']) - 11); $ixx < strlen($v1['price']); ++$ixx){?>
                <td><?php if ($ixx >= 0) { echo $v1['price'][$ixx]; } else { echo '&nbsp;'; }?></td>
            <?php }?>
            <?php if($v1['tax'] =='t') { ?>
            <td><img src="" width="1" height="1"></td>
            <?php for ($ixx = (strlen($v1['vat']) - 10); $ixx < strlen($v1['vat']); ++$ixx){?>
                <td><?php if ($ixx >= 0) { echo $v1['vat'][$ixx]; } else { echo '&nbsp;'; }?></td>
            <?php }?>
            <?php }?>
            <td style="border-left-width:3px; line-height:11px;"><?php echo substr($key,0,8);?><br><?php echo substr($key,8);?></td>
        </tr>
    </table>

    <!-- 주문list -->
    <table id="box" width="100%" border="0" cellspacing="0" cellpadding="0">
        <colgroup span="2" width="3%"></colgroup><col><colgroup span="2" width="9%"></colgroup><col width="12%"><col width="19%"><?php if($v1['tax'] =='t') { ?><col width="13%"><col width="8%"> <?php } else { ?><col width="21%"><?php } ?>
        <tr height="24" align="center">
            <td>월</td>
            <td>일</td>
            <td>품&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;목</td>
            <td>규&nbsp;&nbsp;격</td>
            <td>수&nbsp;&nbsp;량</td>
            <td>단&nbsp;&nbsp;가</td>
            <td>공&nbsp;&nbsp;급&nbsp;&nbsp;가&nbsp;&nbsp;액</td>
            <?php if($v1['tax'] =='t') { ?> <td>세&nbsp;&nbsp;액</td> <?php } ?>
            <td>비&nbsp;&nbsp;고</td>
        </tr>
        <tr height="25" align="center">
            <td><?=gd_date_format('m',$val['issueDt'])?></td>
            <td><?=gd_date_format('d',$val['issueDt'])?></td>
            <td style="padding: 0 6px; word-break:break-all"><?=$val['requestGoodsNm']?></td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td align="right" style="padding-right:6px">&nbsp;</td>
            <td align="right" style="padding-right:6px"><?=number_format($v1['price'])?>원</td>
            <?php if($v1['tax'] =='t') { ?> <td align="right" style="padding-right:6px"><?=number_format($v1['vat'])?>원</td> <?php } ?>
            <td>&nbsp;</td>
        </tr>

        <? for($jj = 1; $jj < 4; $jj++){ ?>
            <tr height="25">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <?php if($v1['tax'] =='t') { ?>  <td>&nbsp;</td> <?php } ?>
                <td>&nbsp;</td>
            </tr>
        <? } ?>
    </table>

    <table id="box" width="100%" border="0" cellspacing="0" cellpadding="0">
        <col width="100"><colgroup span="4" width="88"></colgroup>
        <tr align="center">
            <td style="border-top-width: 0;">합 계 금 액</td>
            <td style="border-top-width: 0;">현&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;금</td>
            <td style="border-top-width: 0;">수&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;표</td>
            <td style="border-top-width: 0;">어&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;음</td>
            <td style="border-top-width: 0;">외상미수금</td>
            <td rowspan="3" style="border-top-width: 0;">위 금액을 영수 함</td>
        </tr>
        <tr height="25" align="center">
            <td><?=number_format($v1['totalPrice'])?>원</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>
</div>
<br/>
<?php } ?>
<?php } ?>
<?php } ?>
<?php }
if ($printCnt != count($orderData)) {
    echo '<hr class="hidden-print" style="margin:20px 0px 20px 0px;  border-top:dashed 1px #000000;" />';
}?>
</div>
<?php } ?>

