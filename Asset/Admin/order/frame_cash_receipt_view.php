<script type="text/javascript">
<!--
$(document).ready(function(){
});
//-->
</script>

<div>
    <div class="table-title gd-help-manual">
        현금영수증 발급 요청 정보
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-sm" /><col class="width-lg"/><col class="width-sm" /><col /></colgroup>
        <tr>
            <th>PG 업체</th>
            <td>
<?php
    if (empty($pgConf['pgName'])) {
        echo '전자결제 서비스 업체와 계약이 필요합니다.';
    } else {
        echo $pgConf['pgNm'];

    }
?>
            </td>
            <th>발급상태</th>
            <td><?php echo $cashReceiptApplyMsg;?></td>
        </tr>
        <tr>
            <th>주문번호</th>
            <td class="font-num"><?php echo $data['orderNo'];?></td>
            <th>주문상태</th>
            <td>
<?php
            if ($data['issueMode'] == 'a') {
                echo '개별발급';
            } else {
                echo gd_isset($orderStatus[$data['orderStatus']]);
            }
?>
            </td>
        </tr>
        <tr>
            <th>신청자명</th>
            <td><?php echo $data['requestNm'];?></td>
            <th>발급방법</th>
            <td>
<?php
    echo $arrIssue[$data['issueMode']];
    if ($data['issueMode'] == 'a') {
        echo ' ('.$data['managerId'].')';
    }
?>
            </td>
        </tr>
        <tr>
            <th>휴대폰 번호</th>
            <td class="font-num"><?php echo $data['requestCellPhone'];?></td>
            <th>이메일</th>
            <td><?php echo $data['requestEmail'];?></td>
        </tr>
        <tr>
            <th>상품명</th>
            <td colspan="3"><?php echo $data['requestGoodsNm'];?></td>
        </tr>
        <tr>
            <th>신청 금액</th>
            <td class="font-num" colspan="3">
                <div class="pull-left width-sm">발행액 : <?php echo number_format($data['settlePrice']);?>원</div>
                <div class="pull-left width-sm">공급액 : <?php echo number_format($data['supplyPrice']);?>원</div>
                <div class="pull-left width-sm">부가세 : <?php echo number_format($data['taxPrice']);?>원</div>
                <div class="pull-left width-sm">면세 : <?php echo number_format($data['freePrice']);?>원</div>
                <div class="pull-left width-sm display-none">봉사료 : <?php echo number_format($data['servicePrice']);?></div>
                <?php if ($data['issueMode'] == 'p') {?><div class="clear-both snote">※ PG 자동발급의 경우 금액이 상이할 수 있습니다.</div><?php }?>
            </td>
        </tr>
        <tr>
            <th>발행 용도</th>
            <td colspan="3"><?php echo $arrUseFl[$data['useFl']]; ?></td>
        </tr>
        <tr>
            <th>인증 번호</th>
            <td class="font-num" colspan="3"><?php echo $certNo . ' (' . $arrCertFl[$data['certFl']] . ')'; ?></td>
        </tr>
    </table>
<?php if ($data['issueMode'] == 'a' && empty($data['adminMemo']) === false) { ?>
    <div class="table-title gd-help-manual">
        관리자 메모 <span>관리자가 작성한 메모를 확인 합니다.</span>
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-sm" /><col /></colgroup>
        <tr>
            <th>관리자 메모</th>
            <td><?php echo nl2br(gd_htmlspecialchars_decode($data['adminMemo']));?></td>
        </tr>
    </table>
<?php }?>

<?php  if (empty($data['pgLog']) === false) { ?>
    <div class="table-title gd-help-manual">
        PG 처리 정보 <small>발급 및 취소에 대한 PG사의 정보 입니다.</small>
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-sm" /><col /></colgroup>
        <tr>
            <th>처리 상태</th>
            <td><?php echo $arrStatus[$data['statusFl']]; ?></td>
        </tr>
        <tr>
            <th>처리 일자</th>
            <td><?php echo $data['processDt'];?></td>
        </tr>
<?php if (empty($data['pgTid']) === false) { ?>
        <tr>
            <th>거래번호</th>
            <td><?php echo $data['pgTid'];?></td>
        </tr>
<?php  }?>
<?php  if (empty($data['pgAppNo']) === false) { ?>
        <tr>
            <th>승인번호</th>
            <td><?php echo $data['pgAppNo'];?></td>
        </tr>
<?php  }?>
<?php  if (empty($data['pgAppNoCancel']) === false) { ?>
        <tr>
            <th>취소승인번호</th>
            <td><?php echo $data['pgAppNoCancel'];?></td>
        </tr>
<?php  }?>
        <tr>
            <th>PG 로그</th>
            <td>
                <div class="boxScroll font-kor pd10" style="width:500px">
                    <?php echo nl2br(gd_htmlspecialchars_decode($data['pgLog']));?>
                </div>
            </td>
        </tr>
    </table>
<?php  }?>

<?php  if ($cashReceiptApplyFl === true) { ?>
    <script type="text/javascript">
        <!--
        $(document).ready(function (n) {
            // 현금영수증 발급
            $('.js-approval-each').click(function () {
                BootstrapDialog.show({
                    title: '현금영수증 발급',
                    message: '[<?php echo $data['orderNo'];?>] 현금영수증 발급 처리를 하겠습니까?',
                    buttons: [{
                        id: 'btn-approval',
                        label: '현금영수증 발급',
                        cssClass: 'btn-red',
                        action: function(dialog) {
                            dialog.close();

                            $('form[name=\'frmCashReceipt\']').submit();
                        }
                    },
                        {
                            id: 'btn-close',
                            label: '닫기',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }]
                });
                return;
            });

            // 현금영수증 발급 취소
            $('.js-cancel-each').click(function () {
                BootstrapDialog.show({
                    title: '현금영수증 발급 취소',
                    message: '[<?php echo $data['orderNo'];?>] 현금영수증 발급 취소 처리를 하겠습니까?',
                    buttons: [{
                        id: 'btn-cancel',
                        label: '현금영수증 발급 취소',
                        cssClass: 'btn-warning',
                        action: function(dialog) {
                            dialog.close();

                            $('form[name=\'frmCashReceipt\']').submit();
                        }
                    },
                        {
                            id: 'btn-close',
                            label: '닫기',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }]
                });
                return;
            });
        });
        //-->
    </script>
    <form name="frmCashReceipt" method="post" action="./cash_receipt_ps.php" target="ifrmProcess">
        <input type="hidden" name="orderNo" value="<?php echo $data['orderNo'];?>" />
        <?php
        // 현금영수증 발행
        if (in_array($data['statusFl'], ['r', 'f'])) {
            ?>
        <input type="hidden" name="mode" value="pg_approval" />
        <div class="text-center">
            <button type="button" class="btn btn-black js-approval-each">현금영수증 발급</button>
        </div>

        <?php
        }
        // 현금영수증 취소
        elseif (in_array($data['statusFl'], ['y'])) {
            ?>
        <input type="hidden" name="mode" value="pg_cancel" />
            <input type="hidden" name="adminChk" value="y" />
        <div class="text-center">
            <button type="button" class="btn btn-white js-cancel-each">현금영수증 발급 취소</button>
        </div>
        <?php  }?>
    </form>
<?php  }?>
</div>
