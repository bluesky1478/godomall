<form id="frmBankSender" action="./order_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="modifyBankInfo" />
    <input type="hidden" name="orderNo" value="<?=$orderNo?>" />
    <table class="table table-cols no-title-line">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>입금계좌</th>
            <td>
                <?= gd_select_box('bankAccount', 'bankAccount', $bankData, null, $orderData['bankAccount'], '=입금 계좌 선택='); ?>
            </td>
        </tr>
        <tr>
            <th>입금자명</th>
            <td>
                <input type="text" name="bankSender" id="bankSender" value="<?=$orderData['bankSender']?>" class="form-control width-md"/>
            </td>
        </tr>
    </table>
    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
        <button type="submit" class="btn btn-lg btn-black">저장</button>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmBankSender').validate({
            submitHandler: function (form) {
                form.submit();
            },
            dialog: false,
            rules: {
                bankAccountSelector: 'required',
                bankSenderSelector: 'required'
            },
            messages: {
                bankAccountSelector: '입금계좌를 선택하세요.',
                bankSenderSelector: '입금자명을 입력하세요.'
            }
        });
    });
    //-->
</script>
