<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>
<!-- 프린트 출력을 위한 form -->
<form id="frmTaxBillPrint" name="frmTaxBillPrint" action="../scm/tax_bill_print.php" method="post" class="display-none" target="taxBillPrintPopup">
    <input type="hidden" name="scmAdjustTaxBillNo" value=""/>
    <input type="hidden" name="taxBillPrintMode" value=""/>
</form>
<!-- // 프린트 출력을 위한 form -->
<form id="frmSearchAdjust" name="frmSearchAdjust" method="get" class="content-form">
    <div class="table-title">
        발행 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col>
            </colgroup>
            <tbody>
            <?php
            if (!gd_is_provider()) {
                ?>
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
                <?php
            }
            ?>
            <tr>
                <th>발행 종류</th>
                <td>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="taxBillType[]" value="" class="js-not-checkall" data-target-name="taxBillType[]" <?php echo gd_isset($checked['taxBillType']['']); ?>/>전체
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="taxBillType[]" value="basic" <?php echo gd_isset($checked['taxBillType']['basic']); ?>/>일반세금계산서
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="taxBillType[]" value="godo" <?php echo gd_isset($checked['taxBillType']['godo']); ?>/>전자세금계산서
                    </label>
                </td>
            </tr>
            <tr>
                <th>발행일 검색</th>
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

    <div class="table-header">
        <div class="pull-left">
            검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 /
            전체 <strong class="text-danger"><?= number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
        </div>
        <div class="pull-right form-inline">
            <ul>
                <li>
                    <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null, 'onchange="this.form.submit();"'); ?>
                </li>
                <li>
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
                        ]
                    ), '개 출력', Request::get()->get('pageNum'), null, 'onchange="this.form.submit();"'
                    ); ?>
                </li>
            </ul>
            <select class="form-control" id="taxBillPrintMode" name="taxBillPrintMode">
                <option value="">=인쇄 종류 선택=</option>
                <option value="blue">공급받는자용</option>
                <?php
                if (!gd_is_provider()) {
                    ?>
                    <option value="red">공급자용</option>
                    <?php
                }
                ?>
            </select>
            <input type="button"  value="프린트" class="btn btn-white btn-icon-print btn-tax-invoice">
        </div>
    </div>
</form>

<div class="content_list">
    <form id="frmScmAdjust" action="./scm_adjust_ps.php" method="post" class="content_list">
        <input type="hidden" name="mode" value="">
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width3p center"><input type="checkbox" class="js-checkall" data-target-name="chk"></th>
                <th class="width3p">번호</th>
                <th class="width8p">발행일</th>
                <th class="width8p">공급사</th>
                <th class="width15p">사업자정보</th>
                <th class="width5p">세금등급</th>
                <th class="width8p">발행액</th>
                <th class="width8p">공급가액</th>
                <th class="width8p">세액</th>
                <th class="width8p">내용</th>
                <th>종류</th>
                <th class="width8p">발행상태</th>
                <th class="width8p">상세보기</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (empty(gd_isset($data)) === false) {
                foreach ($data as $key => $val) {
                    ?>
                    <tr>
                        <td class="center">
                            <input type="checkbox" name="chk[]" value="<?php echo $val['scmAdjustTaxBillNo']; ?>"/>
                        </td>
                        <td class="center number"><?php echo number_format($page->idx--); ?></td>
                        <td class="center date"><?php echo $val['scmAdjustTaxBillDt']; ?></td>
                        <td class="center"><?php echo $val['scmCompanyNm']; ?></td>
                        <td class="center">
                            사업자등록번호:<?php echo $val['scmBusinessNo']; ?><br/>
                            대표자 : <?php echo $val['scmCeoNm']; ?> </td>
                        <td class="center">과세</td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($val['scmAdjustTaxPrice'] + $val['scmAdjustVatPrice']) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($val['scmAdjustTaxPrice']) . ' ' . gd_currency_string(); ?></td>
                        <td class="center number"><?php echo gd_currency_symbol() . ' ' . gd_money_format($val['scmAdjustVatPrice']) . ' ' . gd_currency_string(); ?></td>
                        <td class="center">수수료</td>
                        <td class="center"><?php echo $val['scmAdjustTaxBillType'] == 'basic' ? '일반세금계산서' : '전자세금계산서'; ?></td>
                        <td class="center"><?php echo $val['scmAdjustTaxBillState'] == 'y' ? '발행' : '취소'; ?></td>
                        <td class="center">
                            <button type="button" data-tax-no="<?= $val['scmAdjustTaxBillNo']; ?>" data-no="<?= $val['scmNo']; ?>" class="btn btn-white btn-sm btnModify js-scm-adjust">상세정보</button>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="13">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </form>
    <div class="center"><?php echo $page->getPage(); ?></div>

</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.js-scm-adjust').click(function () {
            var scmNo = $(this).data('no');
            var taxBillNo = $(this).data('tax-no');
            location.href = './tax_bill_view.php?taxBillNo=' + taxBillNo + '&scmNo=' + scmNo;
        });

        $('.btn-tax-invoice').click(function(e){
            var chkCnt = $('input:checkbox[name="chk[]"]:checked').length;
            if (chkCnt == 0 || chkCnt >  1) {
                alert('출력할 세금계산서 1개를 선택해주세요.');
                return false;
            } else {
                $("#frmTaxBillPrint input[name='scmAdjustTaxBillNo']").val($('#frmScmAdjust input:checkbox[name="chk[]"]:checked').val());
            }
            if($("#frmSearchAdjust #taxBillPrintMode").val() == '') {
                alert('인쇄 종류를 선택해 주세요.');
                return false;
            } else {
                $("#frmTaxBillPrint input[name='taxBillPrintMode']").val($("#frmSearchAdjust #taxBillPrintMode").val());
            }

            // 새창에 form 값을 전송
            var taxBillPrint = window.open('', 'taxBillPrintPopup', 'width=992,height=600,menubar=yes,scrollbars=yes');
            $("#frmTaxBillPrint").submit();
        });
    });
    //-->
</script>
