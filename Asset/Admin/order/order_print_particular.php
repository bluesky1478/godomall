<?php
$classids = array('cssblue', 'cssred'); // -- 공급받는자용, 공급자용
$headuser = array('cssblue' => '공급받는자보관용', 'cssred' => '공급자보관용');
?>
<style type="text/css">
#cssblue {
  margin: 0px auto 0px auto;
  width: 604px;
  border: solid 2px #364F9E;
}

#cssblue strong {
  font: 24px 굴림;
  color: #364F9E;
  font-weight: bold;
}

#cssblue table {
  border-collapse: collapse;
}

#cssblue td, #cssblue table {
  border-color: #364F9E;
  border-style: solid;
  border-width: 0px;
}

#cssblue #head {
  border-width: 1px 1px 0px 1px;
}

#cssblue #box td {
  border-width: 1px;
  padding-top: 3px;
}

#cssred {
  margin: 0px auto 0px auto;
  width: 604px;
  border: solid 2px #FF4633;
}

#cssred strong {
  font: 24px 굴림;
  color: #FF4633;
  font-weight: bold;
}

#cssred table {
  border-collapse: collapse;
}

#cssred td, #cssred table {
  border-color: #FF4633;
  border-style: solid;
  border-width: 0px;
}

#cssred #head {
  border-width: 1px 1px 0px 1px;
}

#cssred #box td {
  border-width: 1px;
  padding-top: 3px;
}

.orderPrintBottomInfoArea {
    margin: 7px auto 0px auto;
    width: 604px;
    padding: 3px;
}
.orderPrintBottomInfoArea > div {
    border-bottom: 1px #BDBDBD solid;
    width: 100%;
    padding-bottom: 3px;
}
.orderPrintBottomInfoArea > div:last-child {
    border-bottom: 0px;
}
</style>
<?php
$printCnt = 0;
foreach ($orderData as $key => $data) {
    // translator load
    if (empty($translator) == false) {
        $translator[$key]->register();
    }
    $printCnt++;
?>
<div class="page-break">
<?php
    foreach ($classids as $cloop => $classid) {
        $totalQuantity = 0;
        if ($cloop != 0) {
            echo '<hr style="border:1px dashed #d9d9d9;">';
        }
?>
    <div class="<?= $classid;?>">
    <div id="<?= $classid;?>">
        <table id="head" width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td width="50%" align="right"><strong <?php if ($data['mallSno'] == '2') echo 'style="font-size:16px;"'; ?>><?= __('거%s래%s명%s세%s표', '&nbsp;', '&nbsp;', '&nbsp;', '&nbsp;'); ?></strong></td>
                            <td width="50%" style="padding-left: 6px">(<?= __($headuser[$classid]); ?>)</td>
                        </tr>
                    </table>
                </td>
                <td width="<?php if ($data['mallSno'] != '2') echo '60'; else echo '85'; ?>" style="border-right-width: 2px;">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr><td height="28" align="center"><?= __('일%s자', '&nbsp;'); ?></td></tr>
                        <tr><td height="24" align="center"><?= __('No.'); ?></td></tr>
                    </table>
                </td>
                <td width="150">
                    <table width="100%" border="0" cellspacing="0" cellpadding="4">
                        <tr height="26">
                            <td style="border-bottom-width: 1px;">&nbsp;<?= gd_date_format(__('Y년 m월 d일'), $data['regDt']);?> </td>
                        </tr>
                        <tr height="26">
                            <td align="center">&nbsp;</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="min-height: 170px;">
            <col width="41%" />
            <col width="3%" />
            <tr>
                <!-- 합계금액 -->
                <td valign="top" height="100%" style="border-width: 2px 1px 0px 1px; padding: 0px;">
                    <?php
                    if($classid === 'cssblue' && $data['orderPrint']['BusinessInfoUse'] === 'y'){ ?>
                        <table id="buyerT" cellSpacing="0" cellPadding="0" width="100%" border="0" style="height: 100%;">
                            <colgroup>
                                <col width="10" />
                                <col width="60" />
                                <col  />
                            </colgroup>
                            <tr height="34" align="center">
                                <td rowspan="5" style="padding: 0 2px 0 2px;"><?= __('공%s급%s받%s는%s자', '<br>', '<br>', '<br>', '<br>'); ?></td>
                                <td style="border-left-width: 1px;" ><?= __('등록번호'); ?></td>
                                <td style="border-left-width: 1px;" >
                                    <?php if($data['orderPrint']['orderPrintBusinessInfoType'] == 'companyWithNumber') { echo $data['orderPrint']['businessInfo']['busiNo']; } ?>
                                </td>
                            </tr>
                            <tr height="34" align="center">
                                <td style="border-width: 1px;"><?= __('상%s호', '&nbsp;&nbsp;&nbsp;&nbsp;'); ?><br>(<?= __('법인명'); ?>)</td>
                                <td style="border-width: 1px;">
                                    <table cellSpacing="0" cellPadding="0" width="100%" border="0" style="height: 100%;">
                                        <colgroup>
                                            <col  />
                                            <col width="17%" />
                                        </colgroup>
                                        <tr>
                                            <td align="center" style="word-break:break-all"><?=$data['orderPrint']['businessInfo']['company']?>
                                                <?php if ($data['orderPrint']['businessInfoType'] == 'companyWithOrder') { ?>
                                                <br/>(<?=$data['orderPrint']['businessInfo']['orderName']?>)</td>
                                                <?php } ?>
                                            <td align="right" style="padding-right: 3px;"><?= __('귀하'); ?></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr height="34" align="center">
                                <td style="border-width: 1px;"><?= __('사업장%s주소', '<br>'); ?></td>
                                <td style="border-width: 1px;"><?=$data['orderPrint']['businessInfo']['address']?></td>
                            </tr>
                            <tr height="34" align="center">
                                <td style="border-width: 1px;"><?= __('전화번호'); ?></td>
                                <td style="border-width: 1px;"><?=$data['orderPrint']['businessInfo']['phone']?></td>
                            </tr>
                            <tr height="34" align="center">
                                <td style="border-left-width: 1px;"><?= __('합계금액'); ?></td>
                                <td style="border-left-width: 1px; text-align: right; padding-right: 3px;">
                                    <?= gd_global_order_money_format($data['totalAmount'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?>
                                </td>
                            </tr>
                        </table>
                    <?php } else { ?>
                        <table cellSpacing="0" cellPadding="2" width="100%" border="0" style="height: 100%;">
                            <tr>
                                <td style="padding-right: 10px; border-bottom-width: 1px"></td>
                                <td style="padding-right: 10px; border-bottom-width: 1px" valign="bottom" align="right" colspan="2">
                                    <table cellSpacing="0" cellPadding="0" width="100%" border="0" style="height: 100%;">
                                        <colgroup>
                                            <col  />
                                            <col width="17%" />
                                        </colgroup>
                                        <tr>
                                            <?php if ($data['mallSno'] == '2') { ?>
                                                <td align="right" style="padding-right: 3px;"><?= __('귀하'); ?></td>
                                            <?php } ?>
                                            <?php if ($data['orderPrint']['BusinessInfoUse'] === 'y') { ?>
                                            <td align="center" style="word-break:break-all"><?=$data['orderPrint']['businessInfo']['company']?>
                                                <?php if ($data['orderPrint']['businessInfoType'] == 'companyWithOrder') { ?>
                                                <br/>(<?=$data['orderPrint']['businessInfo']['orderName']?>)</td>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <td align="center" style="word-break:break-all"> <?= $data['orderName'];?> </td>
                                            <?php } ?>
                                            <?php if ($data['mallSno'] != '2') { ?>
                                            <td align="right" style="padding-right: 3px;"><?= __('귀하'); ?></td>
                                            <?php } ?>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr height="22">
                                <td></td>
                                <td valign="bottom" colspan="2"><?= __('아래와 같이 계산합니다.'); ?></td>
                            </tr>
                            <tr height="34">
                                <td align="center" width="20%"><?= __('합계금액'); ?></td>
                                <td style="padding-right: 10px" align="right" colspan="2">&nbsp;<font size="3"><?= gd_global_order_money_format($data['supplyPrice'] + $data['taxPrice'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></font>&nbsp;<font size="3"><?= __('원정'); ?></font></td>
                            </tr>
                        </table>
                    <?php } ?>
                </td>
                <!-- 공급자 -->
                <td id="centerTd"align="center"  style="border-top-width: 2px; border-right-width: 1px; line-height: 36px; padding-left: 2px"><?= __('공%s급%s자', '<br>', '<br>'); ?></td>
                <td valign="top" height="100%" style="border-width: 2px 1px 0 0; background: url(<?= $sealPath;?>) no-repeat; background-position: 220px 10px;">
                    <table id="providerT" width="100%" border="0" cellspacing="0" cellpadding="0" style="height: 100%;">
                        <col width="53" />
                        <col width="100" />
                        <col width="45" />
                        <tr height="34" align="center">
                            <td style="border-width: 0 1px 1px 0;"><?= __('등록번호'); ?></td>
                            <td colspan="3" style="border-bottom-width: 1px; padding-left: 6px;"><?= Globals::get('gMall.businessNo');?></td>
                        </tr>
                        <tr height="34" align="center">
                            <td style="border-width: 0 1px 1px 0; line-height: 12px;"><?= __('상%s호', '&nbsp;&nbsp;&nbsp;&nbsp;'); ?><br>(<?= __('법인명'); ?>)</td>
                            <td style="padding: 0 6px; border-bottom-width: 1px;"><?= Globals::get('gMall.companyNm');?></td>
                            <td style="border-width: 0 1px 1px 1px;"><?= __('성%s명', '&nbsp;'); ?></td>
                            <td style="border-bottom-width: 1px; padding-right: 10px;" align="left">&nbsp;<?= Globals::get('gMall.ceoNm');?></td>
                        </tr>
                        <tr height="34" align="center">
                            <td style="border-width: 0 1px 1px 0px; line-height: 12px;"><?= __('사%s업%s장%s소%s재%s지', '&nbsp;', '&nbsp;', '<br>', '&nbsp;', '&nbsp;'); ?></td>
                            <td colspan="3" style="padding: 0 6px; border-bottom-width: 1px;" align="left"><?= Globals::get('gMall.address');?> <?= Globals::get('gMall.addressSub');?></td>
                        </tr>
                        <tr height="34" align="center">
                            <td style="border-width: 0 1px 1px 0px;"><?= __('업%s태', '&nbsp;&nbsp;&nbsp;&nbsp;'); ?></td>
                            <td style="border-width: 0 1px 1px 0px; padding: 0 6px;"><?= Globals::get('gMall.service');?></td>
                            <td style="border-width: 0 1px 1px 0px; padding-left: 4px"><?= __('종%s목', '&nbsp;'); ?></td>
                            <td style="border-width: 0 1px 1px 0px;padding: 0 6px;"><?= Globals::get('gMall.item');?></td>
                        </tr>
                        <tr height="34" align="center">
                            <td style="border-right-width: 1px;"><?= __('전화번호', '&nbsp;&nbsp;&nbsp;&nbsp;'); ?></td>
                            <td style="padding: 0 6px;"><?= Globals::get('gMall.phone');?></td>
                            <td style="border-width: 0 1px; padding-left: 4px"><?= __('팩스', '&nbsp;'); ?></td>
                            <td style="padding: 0 6px;"><?= Globals::get('gMall.fax');?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 주문list -->
        <table id="box" width="100%" border="0" cellspacing="0" cellpadding="0" style="border-top-width: 2px;">
            <colgroup span="2" width="3%"></colgroup>
            <col />
            <colgroup span="2" width="6%"></colgroup>
            <col width="10%" />
            <col width="14%" />
            <col width="10%" />
            <tr height="24" align="center">
                <td><?= __('월'); ?></td>
                <td><?= __('일'); ?></td>
                <td><?= __('품%s목', '&nbsp;'); ?>&nbsp; / &nbsp;<?= __('규%s격', '&nbsp;'); ?></td>
                <td><?= __('단%s위', '&nbsp;'); ?></td>
                <td><?= __('수%s량', '&nbsp;'); ?></td>
                <td><?= __('단%s가', '&nbsp;'); ?></td>
                <td><?= __('공%s급%s가%s액', '&nbsp;', '&nbsp;', '&nbsp;'); ?></td>
                <td><?= __('세%s액', '&nbsp;'); ?></td>
            </tr>
<?php
        foreach ($data['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $key => $val) {
                    $totalQuantity += (int)$val['goodsCnt']; //전체 소계
?>
            <tr height="22">
                <td align="center"><?= gd_date_format('m', $data['regDt']); ?></td>
                <td align="center"><?= gd_date_format('d', $data['regDt']); ?></td>
                <td>
                    &nbsp;<?php echo $val['goodsNm']; if ($val['orderStatus'] === 'r3') echo ' (' . __('환불') . ')'; if (empty($val['optName']) === false) {echo ' ' . $val['optName'];} ?>
                    <?php
                    if(!empty($val['optionInfo'][0]['deliveryInfoStr'])) echo '['.$val['optionInfo'][0]['deliveryInfoStr'].']'
                    ?>
                    <?php
                    $tmpPrintGood = array();
                    if($data['orderPrint']['orderPrintGoodsNo'] == 'y') $tmpPrintGood[] = $val['goodsNo'];
                    if($data['orderPrint']['orderPrintGoodsCd'] == 'y') $tmpPrintGood[] = $val['goodsCd'];
                    if(count($tmpPrintGood) > 0) echo '<br>[' . implode('/', $tmpPrintGood) . ']';
                    unset($tmpPrintGood);
                    ?>
                </td>
                <td>&nbsp;</td>
                <td align="center"><?php if ($val['orderStatus'] !== 'r3') echo $val['goodsCnt']; ?></td>
                <td align="right" style="padding-right: 6px"><?php if ($val['orderStatus'] !== 'r3') echo gd_global_order_money_format($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']); ?></td>
                <td align="right" style="padding-right: 6px"><?php if ($val['orderStatus'] !== 'r3') echo gd_global_order_money_format($val['goodsVat']['supply'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']); ?></td>
                <td align="right" style="padding-right: 6px"><?php if ($val['orderStatus'] !== 'r3') echo gd_global_order_money_format($val['goodsVat']['tax'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']); ?></td>
            </tr>
<?php

                    if (empty($val['addGoods']) === false) {
                        foreach ($val['addGoods'] as $aKey => $aVal) {
                            $totalQuantity += (int)$val['goodsCnt'];  //전체 소계
?>
            <tr height="22">
                <td align="center"><?= gd_date_format('m', $aVal['regDt']); ?></td>
                <td align="center"><?= gd_date_format('d', $aVal['regDt']); ?></td>
                <td>&nbsp;
                    <?php echo $aVal['goodsNm']; if ($val['orderStatus'] === 'r3') echo ' (' . __('환불') . ')' ; ?>
                    <?php
                    $tmpPrintGood = array();
                    if($data['orderPrint']['orderPrintGoodsNo'] == 'y') $tmpPrintGood[] = $val['goodsNo'];
                    if($data['orderPrint']['orderPrintGoodsCd'] == 'y') $tmpPrintGood[] = $val['goodsCd'];
                    if(count($tmpPrintGood) > 0) echo '<br>[' . implode('/', $tmpPrintGood) . ']';
                    unset($tmpPrintGood);
                    ?>
                </td>
                <td>&nbsp;</td>
                <td align="center"><?php if ($val['orderStatus'] !== 'r3') echo $val['goodsCnt']; ?></td>
                <td align="right" style="padding-right: 6px"><?php if ($val['orderStatus'] !== 'r3') echo gd_global_order_money_format($aVal['goodsPrice'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']); ?></td>
                <td align="right" style="padding-right: 6px"><?php if ($val['orderStatus'] !== 'r3') echo gd_global_order_money_format($aVal['goodsVat']['supply'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']); ?></td>
                <td align="right" style="padding-right: 6px"><?php if ($val['orderStatus'] !== 'r3') echo gd_global_order_money_format($aVal['goodsVat']['tax'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']); ?></td>
            </tr>
<?php
                        }
                    }
                }
            }
        }

        $linePlus = 0;
        if ($data['totalDeliveryCharge'] > 0) {
            $linePlus = $linePlus + 1;
            $totalQuantity += 1;  //전체 소계
?>
            <tr height="22">
                <td align="center"><?= gd_date_format('m', $data['regDt']); ?></td>
                <td align="center"><?= gd_date_format('d', $data['regDt']); ?></td>
                <td>&nbsp;<?= __('배송비'); ?></td>
                <td>&nbsp;</td>
                <td align="center">1</td>
                <td align="right" style="padding-right: 6px"><?= gd_global_order_money_format($data['totalDeliveryCharge'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']); ?></td>
                <td align="right" style="padding-right: 6px"><?= gd_global_order_money_format($data['deliveryVat']['supply'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']); ?></td>
                <td align="right" style="padding-right: 6px"><?= gd_global_order_money_format($data['deliveryVat']['tax'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']); ?></td>
            </tr>
<?php
        }
?>
<?php
        if ($data['totalSalePrice'] !== 0) {
            $linePlus = $linePlus + 1;
            $totalQuantity += 1;  //전체 소계
?>
            <tr height="22">
                <td align="center"><?= gd_date_format('m', $data['regDt']);?></td>
                <td align="center"><?= gd_date_format('d', $data['regDt']);?></td>
                <td>&nbsp;<?= __('주문할인'); ?></td>
                <td>&nbsp;</td>
                <td align="center">1</td>
                <td align="right" style="padding-right: 6px">
                    <?php if($data['totalSalePrice'] > 0){ ?>-<?php } ?>
                    <?= gd_global_order_money_format(abs($data['totalSalePrice']), $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?>
                </td>
                <td align="right" style="padding-right: 6px">
                    <?php if($data['saleVat']['supply'] > 0){ ?>-<?php } ?>
                    <?= gd_global_order_money_format(abs($data['saleVat']['supply']), $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?>
                </td>
                <td align="right" style="padding-right: 6px">
                    <?php if($data['saleVat']['tax'] > 0){ ?>-<?php } ?>
                    <?= gd_global_order_money_format(abs($data['saleVat']['tax']), $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?>
                </td>
            </tr>
<?php
        }
?>
<?php
        if ($data['useMileage'] > 0) {
            $useMileageVat = gd_tax_all($data['useMileage'], $data['goodsTaxRate']);
            $totalQuantity += 1;  //전체 소계
?>
            <tr height="22">
                <td align="center"><?= gd_date_format('m', $data['regDt']);?></td>
                <td align="center"><?= gd_date_format('d', $data['regDt']);?></td>
                <td>&nbsp;<?= __('마일리지'); ?></td>
                <td>&nbsp;</td>
                <td align="center">1</td>
                <td align="right" style="padding-right: 6px">-<?= gd_global_order_money_format($data['useMileage'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></td>
                <td align="right" style="padding-right: 6px">-<?= gd_global_order_money_format($useMileageVat['supply'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></td>
                <td align="right" style="padding-right: 6px">-<?= gd_global_order_money_format($useMileageVat['tax'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></td>
            </tr>
<?php
        }
?>
<?php
        if ($data['useDeposit'] > 0) {
            $useDepositVat = gd_tax_all($data['useDeposit'], $data['goodsTaxRate']);
            $totalQuantity += 1;  //전체 소계
?>
            <tr height="22">
                <td align="center"><?= gd_date_format('m', $data['regDt']);?></td>
                <td align="center"><?= gd_date_format('d', $data['regDt']);?></td>
                <td>&nbsp;<?= __('예치금'); ?></td>
                <td>&nbsp;</td>
                <td align="center">1</td>
                <td align="right" style="padding-right: 6px">-<?= gd_global_order_money_format($data['useDeposit'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></td>
                <td align="right" style="padding-right: 6px">-<?= gd_global_order_money_format($useDepositVat['supply'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></td>
                <td align="right" style="padding-right: 6px">-<?= gd_global_order_money_format($useDepositVat['tax'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></td>
            </tr>
<?php
        }
?>

            <tr height="22">
                <td align=middle colspan=8>*** <?= __('이%s하%s여%s백', '&nbsp;', '&nbsp;', '&nbsp;'); ?> ***</td>
            </tr>
<?php
        for ($i = (count($data['goods']) + $linePlus); $i < 4; $i++) {
?>
            <tr height="22">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
<?php
        }
?>
        </table>

        <table id="box" width="100%" border="0" cellspacing="0" cellpadding="0">
            <col width="10%" />
            <col width="20%" />
            <col width="10%" />
            <col width="20%" />
            <col width="6%" />
            <col width="10%" />
            <col width="14%" />
            <col width="10%" />
            <tr height="22" align="center">
                <?php
                $subtotalColspan = '6';
                if($data['orderPrint']['orderPrintQuantityDisplay'] === 'y'){
                    $subtotalColspan = '4';
                }
                ?>
                <td style="border-top-width: 0;" colspan="<?=$subtotalColspan?>"><?= __('소%s계', '&nbsp;'); ?></td>
                <?php if($data['orderPrint']['orderPrintQuantityDisplay'] === 'y'){ ?>
                <td style="border-top-width: 0; padding-right: 6px;" align="right"><?=number_format($totalQuantity)?></td>
                <td style="border-top-width: 0;"></td>
                <?php } ?>
                <td style="border-top-width: 0; padding-right: 6px" align="right"><?= gd_global_order_money_format($data['supplyPrice'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></td>
                <td style="border-top-width: 0; padding-right: 6px" align="right"><?= gd_global_order_money_format($data['taxPrice'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></td>
            </tr>
            <tr height="22" align="center">
                <td><?= __('미수금'); ?></td>
                <td>&nbsp;</td>
                <td><?= __('합%s계', '&nbsp;'); ?></td>
                <td><?= gd_global_order_money_format($data['totalAmount'], $data['exchangeRate'], $data['currencyPolicy']['globalCurrencyDecimal']);?></td>
                <td colspan="2"><?= __('인수자'); ?></td>
                <td colspan="2" align="right" style="padding-right: 6px;"><?= $data['orderName'];?></td>
            </tr>
        </table>
    </div>

    <!-- 하단 추가정보 표기 -->
    <?php
    if($data['orderPrint']['orderPrintBottomInfo'] === 'y') {
        if($data['orderPrint']['bottomInfoDisplayType'] === 'array'){
            echo "<div class='orderPrintBottomInfoArea'>";
            foreach($data['orderPrint']['bottomInfo'] as $key => $value){
                echo "<div>".nl2br($value)."</div>";
            }
            echo "</div>";
        }
        else {
            echo "<div class='orderPrintBottomInfoArea'>".nl2br($data['orderPrint']['bottomInfo'])."</div>";
        }
        if($data['orderPrint']['orderPrintBottomInfoType'] == 'a'){
            echo "<div class='orderPrintBottomInfoArea'>";
            foreach($data['orderPrint']['bottomInfo'] as $key => $value){
                echo "<div>".nl2br($value)."</div>";
            }
            echo "</div>";
        }
    }
    ?>
    <!-- 하단 추가정보 표기 -->

    </div>
<?php
    }
?>
<?php
    if ($printCnt != count($orderData)) {
        echo '<hr class="hidden-print" style="margin:20px 0px 20px 0px;  border-top:dashed 1px #000000;" />';
    }
?>
</div>
<?php
}
?>

<script type="text/javascript">
    //거래명세서 표 깨짐현상 수정.
    $(document).ready(function(){
        var centerHeight = $("#centerTd").height();
        $("#providerT").height(centerHeight); //공급자 표 높이 조절
        $("#buyerT").height(centerHeight); //공급받는자 표 높이 조절
    });
</script>
