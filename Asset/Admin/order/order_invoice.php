<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location);?> <small>송장을 일괄 등록하고 배송상태를 변경할 수 있습니다.</small></h3>
</div>
<?php
$excelHeader = '<style>';
$excelHeader .= '.xl31{mso-number-format:"0_\)\;\\\(0\\\)";}' . chr(10);
$excelHeader .= '.xl24{mso-number-format:"\@";} ' . chr(10);
$excelHeader .= '.title{font-weight:bold; background-color:#F6F6F6; text-align:center;} ' . chr(10);
$excelHeader .= '</style>';
echo $excelHeader;
?>
<form id="frmExcelUpload" action="order_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="uploadForm"/>
    <div class="table-title gd-help-manual">송장등록</div>
    <table class="table table-cols mgb5">
        <colgroup>
            <col class="width-xl"/>
            <col/>
        </colgroup>
        <tr>
            <th>
                송장 엑셀파일 등록
                <a href="../order/order_ps.php?mode=invoice_download" class="btn btn-sm btn-gray">샘플파일보기</a>
            </th>
            <td>
                <div class="form-inline">
                    <input type="file" name="excel" class="form-control input-group-item">
                    <button class="btn btn-sm btn-white input-group-item" type="submit">등록하기</button>
                    <span class="notice-info mgl10">1회 최대 1,000건까지 등록하실 수 있습니다.</span>
                </div>
            </td>
        </tr>
    </table>
    <p class="notice-danger text-danger">
        자동발송 설정에 따라 배송상태 변경 시 회원에게 SMS/메일로 안내메시지가 발송되므로 주의하시기 바랍니다.
    </p>

    <div class="panel panel-default">
        <div class="panel-heading">
            송장일괄등록 방법
            <button type="button" class="btn btn-sm btn-gray js-delivery-company">배송업체번호 보기</button>
        </div>
        <div class="panel-body">
            <ul class="list-unstyled mgb0">
                <li>샘플파일을 다운로드 받아 입력양식을 확인합니다.</li>
                <li>주문번호, 상품주문번호, 배송업체번호, 송장번호, 배송일, 배송완료일을 입력하고 엑셀 파일로 저장 후 등록합니다.</li>
                <li>송장번호와 함께 배송일을 입력하면 배송중으로, 배송완료일을 입력하면 배송완료로 주문상태가 자동 변경됩니다.</li>
                <li class="text-red">상품주문번호 상태가 기본설정>주문정책>주문상태설정 메뉴의 "입금 상태 설정/상품 상태 설정/배송 상태 설정" 그룹에 포함된 경우에만 송장번호가 일괄등록됩니다.</li>
            </ul>
        </div>
    </div>
</form>

<form id="frmSearchOrder" method="get" class="js-form-enter-submit">
    <div class="table-title gd-help-manual">송장일괄등록 내역보기</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md">
            <col>
            <col class="width-md">
            <col>
        </colgroup>
        <tbody>
        <?php if(gd_use_provider() === true) { ?>
        <?php if (!isset($isProvider) && $isProvider != true) { ?>
        <tr>
            <th>공급사 구분</th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="scmFl" value="all" <?php echo gd_isset($checked['scmFl']['all']); ?>/>전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="scmFl" value="0" <?php echo gd_isset($checked['scmFl']['0']); ?>/>본사
                </label>
                <label class="radio-inline">
                    <input type="radio" name="scmFl" value="1" class="js-layer-register" <?php echo gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="checkbox"/> 공급사
                </label>
                <input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="search"/>

                <div id="scmLayer" class="selected-btn-group <?=!empty($search['scmNo']) ? 'active' : ''?>">
                    <h5>선택된 공급사 : </h5>
                    <?php
                    if ($search['scmFl'] == '1') {
                        if (empty($search['scmNo']) === false) {
                            foreach ($search['scmNo'] as $k => $v) {
                                ?>
                                <div id="idscm_<?= $v ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                    <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                    <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#idscm_<?= $v ?>">삭제</button>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <?php } ?>
        <?php } ?>
        <tr>
            <th>검색어</th>
            <td>
                <div class="form-inline">
                    <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null, null, 'form-control'); ?>
                    <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                </div>
            </td>
            <th>등록상태</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="resultFl" value="" <?php echo gd_isset($checked['resultFl']['']); ?> />전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="resultFl" value="success" <?php echo gd_isset($checked['resultFl']['success']); ?> />성공
                </label>
                <label class="radio-inline">
                    <input type="radio" name="resultFl" value="fail" <?php echo gd_isset($checked['resultFl']['fail']); ?> />실패
                </label>
                <label class="radio-inline">
                    <input type="radio" name="resultFl" value="functionAuth" <?php echo gd_isset($checked['resultFl']['functionAuth']); ?> />주문상태변경실패
                </label>
            </td>
        </tr>
        <tr>
            <th>등록일 검색</th>
            <td colspan="3">
                <div class="form-inline">
                    <div class="input-group js-datetimepicker">
                        <input type="text" name="treatDate[]" value="<?= $search['treatDate'][0]; ?>" class="form-control width-sm">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datetimepicker">
                        <input type="text" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>" class="form-control width-sm">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    <?= gd_search_date(gd_isset($search['searchPeriod'], 6), 'treatDate', false) ?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="text-center">
        <button class="btn btn-lg btn-black" type="submit">검색</button>
    </div>

    <div class="table-title gd-help-manual">송장일괄등록 리스트</div>
    <div class="table-header">
        <div class="pull-left">
            검색결과 <strong class="text-danger"><?php echo number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 /
            전체 <strong class="text-danger"><?php echo number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', $page->page['list'], null); ?>
            </div>
        </div>
    </div>

</form>

<form id="frmOrderStatus" action="./order_ps.php" method="post">
    <table class="table table-rows">
        <colgroup>
            <col class="width-xs" />
            <col />
            <col />
            <col />
            <col class="width-xs" />
            <col class="width-xs" />
            <col class="width-xs" />
            <col />
            <col />
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>등록일시</th>
            <th>등록자</th>
            <th>공급사</th>
            <th>전체건수</th>
            <th>성공건수</th>
            <th>실패건수</th>
            <th>주문상태변경실패건수</th>
            <th>상세내역</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false) {
            $sortNo = count($data);
            foreach ($data as $val) {
                ?>
                <tr class="text-center">
                    <td><?=$page->idx--?></td>
                    <td><?= $val['regDt'] ?></td>
                    <td><?= $val['managerNm'] ?> (<?= $val['managerId'] ?>)<?= $val['deleteText'] ?></td>
                    <td><?= $val['companyNm'] ?></td>
                    <td><strong><?= number_format($val['successCnt'] + $val['failCnt'] + $val['functionAuthCnt']) ?></strong></td>
                    <td><strong class="text-primary"><?= number_format($val['successCnt']) ?></strong></td>
                    <td><strong class="text-danger"><?= number_format($val['failCnt']) ?></strong></td>
                    <td><strong class="text-danger"><?= number_format($val['functionAuthCnt']) ?></strong></td>
                    <td>
                        <button type="button" data-group-cd="<?= $val['groupCd'] ?>" class="btn btn-sm btn-gray js-invoice-detail">상세내역</button>
                    </td>
                </tr>
                <?php
            }
        } else {
        ?>
            <tr>
                <td colspan="9" class="no-data">조회 내역이 없습니다.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</form>
<div class="text-center"><?= $page->getPage(); ?></div>

<script type="text/html" id="templateInvoiceCheck">
    <tr class="text-center">
        <td><%=sortNo%></td>
        <td><%=sno%></td>
        <td><%=orderNo%></td>
        <td><%=invoiceCompanySno%></td>
        <td><%=invoiceNo%></td>
        <td><%=deliveryDt%></td>
        <td><%=deliveryCompleteDt%></td>
    </tr>
</script>
<script type="text/html" id="templateInvoiceComplete">
    <p class="text-center">송장 일괄등록이 완료되었습니다.</p>
    <table class="table table-rows">
        <thead>
        <tr>
            <th>전체건수</th>
            <th>성공건수</th>
            <th>실패건수</th>
            <th>주문상태변경실패건수</th>
        </tr>
        </thead>
        <tbody>
        <tr class="text-center">
            <td><%=total%></td>
            <td class="text-primary"><%=success%></td>
            <td class="text-danger"><%=fail%></td>
            <td class="text-danger"><%=functionAuth%></td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <button type="button" class="btn btn-lg btn-black js-layer-close">확인</button>
    </div>
</script>


<script type="text/javascript">
    <!--
    $(function(){
        // 송장일괄등록
        $('#frmExcelUpload').validate({
            dialog: false,
            submitHandler: function(form) {
                BootstrapDialog.confirm({
                    title: '엑셀 송장일괄 등록',
                    message: '송장 엑셀파일을 업로드 하시겠습니까?',
                    nl2br: false,
                    callback: function(result) {
                        if (result) {
                            var formData = new FormData();
                            formData.append("mode", 'checkUploadForm');
                            formData.append("excel", $("input[name=excel]")[0].files[0]);

                            $.ajax({
                                url: '../order/order_ps.php',
                                data: formData,
                                processData: false,
                                contentType: false,
                                type: 'POST',
                                dataType: 'json',
                                success: function(data){
                                    // 송장이 기등록된 경우 처리
                                    if (data.length > 0) {
                                        var compiled = _.template($('#templateInvoiceCheck').html());
                                        var html = '<div class="well well-sm">송장번호가 이미 등록된 주문이 ' + data.length + '건 있습니다. 이대로 등록을 진행하시겠습니까?</div>';
                                        html += '<table class="table table-rows">';
                                        html += '<thead>';
                                        html += '<tr>';
                                        html += '<th>번호</th>';
                                        html += '<th>상품주문번호</th>';
                                        html += '<th>주문번호</th>';
                                        html += '<th>배송업체번호</th>';
                                        html += '<th>송장번호</th>';
                                        html += '<th>배송일</th>';
                                        html += '<th>배송완료일</th>';
                                        html += '</tr>';
                                        html += '</thead>';
                                        html += '<tbody>';
                                        for (var i in data) {
                                            data[i].sortNo = parseInt(data.length - i);
                                            html += compiled(data[i]);
                                        }
                                        html += '</tbody>';
                                        html += '</tr>';
                                        html += '</table>';
                                        BootstrapDialog.confirm({
                                            title: '송장번호 중복 확인',
                                            message: html,
                                            btnCancelLabel: '등록취소',
                                            btnOKLabel: '등록진행',
                                            btnOKClass: 'btn-white',
                                            callback: function (result) {
                                                if (result) {
                                                    formSubmit();
                                                }
                                            }
                                        });
                                    } else {
                                        formSubmit();
                                    }
                                }
                            });
                        }
                    }
                });
            },
            rules: {
                excel: 'required'
            },
            messages: {
                excel: '업로드 할 송장 엑셀파일을 입력해 주세요.'
            }
        });

        // 상세내역보기
        $('.js-invoice-detail').click(function(e){
            $.get('../order/layer_order_invoice.php?groupCd=' + $(this).data('group-cd'), function(data){
                layer_popup(data, '송장일괄등록 상세보기', 'wide');
            });
        });

        // 배송업체번호 보기
        $('.js-delivery-company').click(function(e){
            $.get('../order/layer_delivery_company.php', function(data){
                layer_popup(data, '배송업체번호 보기');
            });
        });
    });

    function formSubmit() {
        var formData = new FormData();
        formData.append("mode", 'uploadForm');
        formData.append("excel", $("input[name=excel]")[0].files[0]);

        $.ajax({
            url: '../order/order_ps.php',
            data: formData,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                var compiled = _.template($('#templateInvoiceComplete').html());
                BootstrapDialog.show({
                    title: '송장 일괄등록 완료',
                    message: compiled(data),
                    onhidden: function(dialog) {
                        location.reload(true);
                    }
                });
            }
        });
    }

    /**
     * 엑셀 다운로드 약식 팝업창
     */
    function manage_formtype() {
        frame_popup('order_list_download_form.php', 700, 700, '다운로드 양식관리');
    }

    /**
     * 주문내역 엑셀 다운
     */
    function download_excel() {
        if ($('[name=formSno]').val() == '') {
            alert('다운로드 양식을 선택해주세요.');
            return false;
        }
        if ($('[name=\'orderNo[]\']:checked').length == 0) {
            alert('선택된 주문정보가 없습니다.');
            return false;
        }
        $('form[name=frmExcelDown]').submit();
    }
    //-->
</script>
