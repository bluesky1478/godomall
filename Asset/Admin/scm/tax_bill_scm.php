<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchTaxbill" name="frmSearchTaxbill" method="get" class="content-form">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>공급사 구분</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="all" <?= gd_isset($checked['scmFl']['all']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="0" <?= gd_isset($checked['scmFl']['0']); ?>/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="1" class="js-layer-register" <?= gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="checkbox"/> 공급사
                    </label>
                    <input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="search"/>

                    <div id="scmLayer" class="selected-btn-group <?= $search['scmFl'] == '1' && !empty($search['scmNo']) ? 'active' : '' ?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($search['scmFl'] == '1') { ?>
                            <?php foreach ($search['scmNo'] as $k => $v) { ?>
                                <div id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                    <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                    <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td>
                    <div class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][0]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        <?= gd_search_date($search['treatDate'], 'treatDate', false) ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>
</form>

<div class="table-responsive statistics-board scm-adjust-total">
    <form id="frmScmAdjust" action="./scm_adjust_ps.php" method="post" class="content_list" target="ifrmProcess">
        <input type="hidden" name="mode" value="insertScmAdjustScmTaxBill">
        <input type="hidden" name="treatDate[]" value="<?= $search['treatDate'][0]; ?>">
        <input type="hidden" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>">
        <input type="hidden" name="periodFl" value="<?= $search['periodFl']; ?>">
        <input type="hidden" name="taxBillDate" value="">
        <table class="table table-rows">
            <thead>
            <tr class="nowrap text-center">
                <th class="center-line" rowspan="2"><input type="checkbox" class="js-checkall" data-target-name="chkScm">
                </th>
                <th class="center-line" rowspan="2">번호</th>
                <th class="center-line" rowspan="2">공급사</th>
                <th class="center-line" rowspan="2">세금등급</th>
                <th class="center-line" rowspan="2">공급가액</th>
                <th class="center-line" rowspan="2">VAT</th>
                <th class="center-line" rowspan="2">합계</th>
                <th class="center-line" colspan="3">수수료(VAT포함)</th>
                <th class="center-line" rowspan="2">정산 개별 발급</th>
            </tr>
            <tr class="nowrap text-center">
                <th class="center-line">합계</th>
                <th class="center-line">상품</th>
                <th class="center-line">배송비</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (is_array(gd_isset($data['list']))) {
                $num = 1;
                foreach ($data['list'] as $key => $val) {
                    $taxCommissionPrice = ($val['o']['taxPrice'] + $val['d']['taxPrice']) - ($val['oa']['taxPrice'] + $val['da']['taxPrice']);
                    $vatCommissionPrice = ($val['o']['vatPrice'] + $val['d']['vatPrice']) - ($val['oa']['vatPrice'] + $val['da']['vatPrice']);
                    $totalCommissionPrice = $taxCommissionPrice + $vatCommissionPrice;
                    $orderCommissionPrice = ($val['o']['taxPrice'] + $val['oa']['taxPrice'] + $val['o']['vatPrice'] + $val['oa']['vatPrice']);
                    $deliveryCommissionPrice = ($val['d']['taxPrice'] + $val['da']['taxPrice'] + $val['d']['vatPrice'] + $val['da']['vatPrice']);
                    $totalTypeCommissionPrice = $orderCommissionPrice + $deliveryCommissionPrice;
                    ?>
                    <tr>
                        <td class="center">
                            <input type="checkbox" name="chkScm[]" value="<?= $val['scmNo']; ?>"/>
                        </td>
                        <td class="center number"><?= $num; ?></td>
                        <td class="center number"><?= $val['scmName']; ?></td>
                        <td class="center number">과세</td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($taxCommissionPrice) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($vatCommissionPrice) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($totalCommissionPrice) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($totalTypeCommissionPrice) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($orderCommissionPrice) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($deliveryCommissionPrice) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number">
                            <button type="button" data-no="<?= $val['scmNo']; ?>" class="btn btn-white btn-sm btnModify js-scm-adjust">개별발급</button>
                        </td>
                    </tr>
                    <?php
                    $num++;
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="11">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php
        if ($taxConf['taxInvoiceUseFl'] == 'y' && ($taxConf['gTaxInvoiceFl'] == 'y' || $taxConf['eTaxInvoiceFl'] == 'y')) {
        ?>
        <div class="table-action">
            <div class="pull-left form-inline">
                <span class="action-title">선택한 공급사의</span>
                <select name="scmAdjustTaxBill" id="scmAdjustTaxBill" class="form-control">
                    <?php
                    if ($taxConf['gTaxInvoiceFl'] == 'y') {
                        ?>
                        <option value="basic">일반세금계산서</option>
                        <?php
                    }
                    if ($taxConf['eTaxInvoiceFl'] == 'y') {
                        ?>
                        <option value="godo">전자세금계산서</option>
                        <?php
                    }
                    ?>
                </select>
                <button type="button" class="btn btn-white js-scm-tax-bill"/>발행</button>
            </div>
        </div>
        <?php
        }
        ?>
    </form>
</div>
<script>
    $(document).ready(function () {
        $('#frmScmAdjust').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'chkScm[]': 'required',
            },
            messages: {
                'chkScm[]': {
                    required: "세금계산서를 발급할 공급사를 선택해 주세요.",
                }
            }
        });
        $('.js-scm-adjust').click(function () {
            var param = $('#frmSearchTaxbill').serialize();
            var scmNo = $(this).data('no');
            location.href = './tax_bill_order.php?scmNo=' + scmNo + '&' + param;
        });
        $('.js-scm-tax-bill').click(function () {
            layer_date_view();
        });
    });

    /**
     * 발행일 입력
     */
    function layer_date_view() {
        var loadChk = $('#taxBillDateForm').length;
        var chkNum = $('input[name="chkScm[]"]:checked').length;
        var title = "세금계산서 발행";

        if (chkNum > 0) {
            $.post('./layer_tax_bill_date.php', {chkNum: chkNum}, function (data) {
                if (loadChk == 0) {
                    data = '<div id="taxBillDateForm">' + data + '</div>';
                }
                var layerForm = data;
                BootstrapDialog.show({
                    title: title,
                    size: BootstrapDialog.SIZE_WIDE,
                    message: $(layerForm),
                    closable: true,
                });
            });
        } else {
            alert('세금계산서를 발급할 공급사를 선택해 주세요.');
            return false;
        }
    }
</script>
