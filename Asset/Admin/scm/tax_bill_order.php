<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchScm" method="get">
    <div class="search-detail-box">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col>
            <col class="width-sm"/>
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th>공급사</th>
            <td colspan="3"><?= $scm['companyNm']; ?> (<?= $scm['ceoNm']; ?>)</td>
        </tr>
        <tr>
            <th>검색 기간</th>
            <td colspan="3"><?= $treadDate; ?></td>
        </tr>
        <tr>
            <th>상태</th>
            <td><?= $scm['scmType'] == 'y' ? '운영' : '탈퇴'; ?></td>
            <th>사업자등록번호</th>
            <td><?= $scm['businessNo']; ?></td>
        </tr>
        <tr>
        </tbody>
    </table>
    </div>

    <!-- div class="table-header">

        <div class="pull-left">
            공급사 리스트 (검색결과
            <strong><?= number_format($page->recode['total'], 0); ?></strong>건, 전체<strong><?= number_format($page->recode['amount'], 0); ?></strong>건)
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?php echo gd_select_box(
                    'pageNum', 'pageNum', gd_array_change_key_value(
                    [
                        10,
                        20,
                        30,
                        40,
                        50,
                        60,
                        70,
                        80,
                        90,
                        100,
                        200,
                        300,
                        500,
                    ]
                ), '개 보기', Request::get()->get('pageNum'), null
                ); ?>
            </div>
        </div>
    </div -->
</form>

<div class="content_list">
    <form id="frmScmAdjust" action="./scm_adjust_ps.php" method="post" class="content_list" target="ifrmProcess">
        <input type="hidden" name="mode" value="insertScmAdjustOrderTaxBill">
        <input type="hidden" name="scmNo" value="<?= $getValue['scmNo']; ?>">
        <input type="hidden" name="scmFl" value="<?= $getValue['scmFl']; ?>">
        <input type="hidden" name="treatDate[]" value="<?= $getValue['treatDate'][0]; ?>">
        <input type="hidden" name="treatDate[]" value="<?= $getValue['treatDate'][1]; ?>">
        <input type="hidden" name="periodFl" value="<?= $getValue['periodFl']; ?>">
        <input type="hidden" name="taxBillDate" value="">
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="chk"></th>
                <th class="width5p">번호</th>
                <th class="width8p">정산요청번호</th>
                <th class="width8p">요청일</th>
                <th class="width8p">정산타입</th>
                <th class="width5p">요청타입</th>
                <th class="width5p">세금등급</th>
                <th>판매(배송)금액</th>
                <th>수수료</th>
                <th>정산금액</th>
                <th class="width8p">정산상태</th>
                <th class="width8p">정산정보</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (is_array(gd_isset($data))) {
                foreach ($data as $key => $val) {
                    ?>
                    <tr>
                        <td class="center">
                            <input type="checkbox" name="chk[]" data-state="<?php echo $val['scmAdjustState']; ?>" value="<?php echo $val['scmAdjustNo']; ?>"/>
                        </td>
                        <td class="center number"><?php echo number_format($page->idx--); ?></td>
                        <td class="center number"><?php echo $val['scmAdjustCode']; ?></td>
                        <td class="center date"><?php echo $val['scmAdjustDt']; ?> </td>
                        <td class="center lmenu"><?php echo $conventData[$key]['scmAdjustType']; ?></td>
                        <td class="center lmenu"><?php echo $conventData[$key]['scmAdjustKind']; ?></td>
                        <td class="center lmenu">과세</td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($val['scmAdjustTotalPrice']) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($val['scmAdjustCommissionPrice']) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($val['scmAdjustPrice']) . ' ' . gd_currency_string(); ?></td>
                        <td class="center lmenu"><?php echo $conventData[$key]['scmAdjustState']; ?></td>
                        <td class="center">
                            <input type="button" value="상세정보" class="btn btn-white btn-sm btnModify" onclick="layer_info_view('<?= $val['scmAdjustNo'] ?>')">
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="12">검색된 정보가 없습니다.</td>
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
                    <span class="action-title">선택한 정산의</span>
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
                    <button type="button" class="btn btn-white js-order-tax-bill"/>발행</button>
                </div>
            </div>
            <?php
        }
        ?>
    </form>
    <div class="center"><?php echo $page->getPage(); ?></div>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmScmAdjust').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'chk[]': 'required',
            },
            messages: {
                'chk[]': {
                    required: "세금계산서를 발급할 건을 선택해 주세요.",
                }
            }
        });
        $('.js-order-tax-bill').click(function () {
            layer_date_view();
        });
    });

    /**
     * 발행일 입력
     */
    function layer_date_view() {
        var loadChk = $('#taxBillDateForm').length;
        var chkNum = $('input[name="chk[]"]:checked').length;
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
            alert('세금계산서를 발급할 정산을 선택해 주세요.');
            return false;
        }
    }
    /**
     * 상세 정보 보기
     */
    function layer_info_view(scmAdjustNo) {
        var loadChk = $('#scmAdjustInfoForm').length;
        var title = "정산 상세 정보";

        $.post('./layer_adjust_info.php', {scmAdjustNo: scmAdjustNo}, function (data) {
            if (loadChk == 0) {
                data = '<div id="scmAdjustInfoForm">' + data + '</div>';
            }
            var layerForm = data;
            BootstrapDialog.show({
                title: title,
                size: BootstrapDialog.SIZE_WIDE,
                message: $(layerForm),
                closable: true
            });
        });
    }
    //-->
</script>
