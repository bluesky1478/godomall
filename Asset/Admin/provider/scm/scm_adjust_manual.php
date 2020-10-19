<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>
<!-- 주의! 본사, 공급사 별도 수정 필요 -->
<form id="frmAdjustManual" name="frmAdjustManual" method="post" action="./scm_adjust_ps.php">
    <input type="hidden" name="mode" value="insertScmAdjustManual">
    <div class="table-title gd-help-manual">
        수기 정산 등록
    </div>
    <div>
        <div class="mgt10"></div>
        <div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>정산타입</th>
                    <td class="form-inline"><?= gd_select_box('scmAdjustType', 'scmAdjustType', $scmAdjustType, null, $scmAdjustType, null, null, "form-control width-sm"); ?></td>
                </tr>
                <tr>
                    <th class="require">판매금액</th>
                    <td class="form-inline">
                        <?= gd_currency_symbol(); ?>
                        <select name="scmAdjustTotalPriceType" class="form-control width-3xs">
                            <option value="p">+</option>
                            <option value="m">-</option>
                        </select> <input type="text" name="scmAdjustTotalPrice" value="" class="form-control width-lg">
                        <?= gd_currency_string(); ?>
                    </td>
                </tr>
                <tr>
                    <th class="require">수수료</th>
                    <td class="form-inline">
                        <input type="text" name="scmAdjustCommission" value="" class="form-control width-3xs">%
                    </td>
                </tr>
                <tr>
                    <th>수수료액</th>
                    <td class="form-inline"><?= gd_currency_symbol(); ?>
                        <input type="text" name="scmAdjustCommissionPrice" readonly="readonly" class="form-control width-lg"><?= gd_currency_string(); ?>
                    </td>
                </tr>
                <tr>
                    <th>정산금액</th>
                    <td class="form-inline"><?= gd_currency_symbol(); ?>
                        <input type="text" name="scmAdjustPrice" readonly="readonly" class="form-control width-lg"><?= gd_currency_string(); ?>
                    </td>
                </tr>
                <tr>
                    <th class="require">메모</th>
                    <td><textarea name="scmAdjustMemo" class="form-control width-2xl"></textarea></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="text-center">
        <input type="submit" value="확인" class="btn btn-red btn-lg"/>
        <button type="button" class="btn btn-white btn-lg js-layer-close">취소</button>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 폼체크
        $('#frmAdjustManual').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                scmAdjustTotalPrice: {
                    required: true,
                },
                scmAdjustCommission: {
                    required: true,
                },
                scmAdjustMemo: {
                    required: true,
                },
            },
            messages: {
                scmAdjustTotalPrice: {
                    required: '판매금액을 입력해주세요.',
                },
                scmAdjustCommission: {
                    required: '수수료를 입력해주세요.',
                },
                scmAdjustMemo: {
                    required: '메모를 입력해주세요.',
                },
            }
        });
        $('select[name="scmAdjustTotalPriceType"]').change(function () {
            getScmAdjustPrice();
        });
        $('input:text[name="scmAdjustTotalPrice"]').keyup(function () {
            getScmAdjustPrice();
        });
        $('input:text[name="scmAdjustCommission"]').keyup(function () {
            getScmAdjustPrice();
        });
    });
    function getScmAdjustPrice() {
        if ($('select[name="scmAdjustTotalPriceType"]').val() == 'p') {
            var scmAdjustTotalPrice = parseFloat($.trim($('input:text[name="scmAdjustTotalPrice"]').val()));
        } else {
            var scmAdjustTotalPrice = parseFloat('-' + $.trim($('input:text[name="scmAdjustTotalPrice"]').val()));
        }
        var scmAdjustCommission = parseInt($.trim($('input:text[name="scmAdjustCommission"]').val()), 10);

        if (scmAdjustTotalPrice && scmAdjustCommission >= 0) {
            var scmAdjustCommissionPrice = Math.floor(scmAdjustTotalPrice * scmAdjustCommission / 100);
            var scmAdjustPrice = scmAdjustTotalPrice - scmAdjustCommissionPrice;
            $('input:text[name="scmAdjustCommissionPrice"]').val(scmAdjustCommissionPrice);
            $('input:text[name="scmAdjustPrice"]').val(scmAdjustPrice);
        }
    }
    //-->
</script>
