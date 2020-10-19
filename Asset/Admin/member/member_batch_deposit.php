<?php
$requestGetParams = Request::get()->all();
$groups = gd_member_groups();
$depositReasons = gd_code('01006');
$contents = Request::get()->get('contents', '기타');
?>
<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./member_batch_deposit_list.php');"/>
        <input type="button" value="예치금 지급/차감" class="btn btn-red btn-register"/>
    </div>
</div>
<form id="formSearch" method="get" class="content-form js-search-form js-form-enter-submit">
    <input type="hidden" name="pageNum" value="<?= Request::get()->get('pageNum', 10); ?>"/>

    <div class="table-title gd-help-manual">
        <div class="pull-left">대상회원 선택</div>
    </div>
    <?php include('member_detail_search.php'); ?>
</form>
<div class="js-batch-excel display-none">
    <form id="formExcel" action="excel_deposit_ps.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="depositExcelUpload"/>

        <table class="table table-cols">
            <colgroup>
                <col class="width-md">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>엑셀파일등록 <a href="excel_deposit_ps.php?mode=downloadSample" class="btn btn-gray btn-sm mgt5" id="btnExcelSample">샘플파일보기</a>
                </th>
                <td>
                    <input type="file" name="excel" value=""/>
                    <div class="contents">
                        <div class="notice-info">엑셀파일등록 시 유의사항</div>
                        <div>- 엑셀 파일은 반드시 "Excel 97-2003 통합문서(.xls)" 형식만 가능합니다.</div>
                        <div>- 엑셀의 내용이 너무 많은 경우 업로드가 불가능 할수 있으므로 100개나 200개 단위로 나누어 올리시기 바랍니다.</div>
                        <div>- 엑셀 내용중 "1번째 줄은 설명", "2번째 줄은 excel DB 코드", "3번째 줄은 설명" 이며, "4번째 줄부터" 데이터 입니다.</div>
                        <div>- 엑셀 내용중 2번째 줄 "excel DB" 코드는 필수 데이타 입니다. 그리고 반드시 내용은 "4번째 줄부터" 작성 하셔야 합니다.</div>
                        <div>- 엑셀 항목 중 “아이디”와 “지급/차감 예치금”는 필수값입니다.</div>
                        <div>- [샘플파일보기] 버튼을 눌러 다운로드 되는 엑셀 샘플파일을 참고하시기 바랍니다.</div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
<form id="frmList" action="" method="get" target="ifrmProcess" class="content-form js-list-form">
    <div class="table-header form-inline">
        <div class="pull-left">
            회원리스트 (검색결과
            <strong><?= $page->recode['total']; ?></strong>
            명, 전체
            <strong><?= $page->recode['amount']; ?></strong>
            명)
        </div>
        <div class="pull-right">
            <div>
                <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
            </div>
        </div>
    </div>
    <table class="table table-rows">
        <colgroup>
            <col class="width-xs"/>
            <col class="width-xs"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
        </colgroup>
        <thead>
        <tr>
            <th>
                <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
            </th>
            <th>번호</th>
            <th>아이디/닉네임</th>
            <th>이름</th>
            <th>등급</th>
            <th>주문금액</th>
            <th>마일리지</th>
            <th>예치금</th>
            <th>회원가입일</th>
            <th>최종로그인</th>
            <th>가입승인</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (isset($data) && is_array($data) && \count($data) > 0) {
            foreach ($data as $val) {
                $lastLoginDt = (substr($val['lastLoginDt'], 2, 8) != date('y-m-d')) ? substr($val['lastLoginDt'], 2, 8) : '<span class="">' . substr($val['lastLoginDt'], 11) . '</span>';
                $txtAppFl = ($val['appFl'] == 'y' ? '승인' : '미승인');
                ?>
                <tr class="center" data-member-no="<?= $val['memNo']; ?>">
                    <td>
                        <input type="checkbox" name="chk[]" value="<?= $val['memNo']; ?>"
                               data-appFl="<?= ($val['appFl'] == 'y' ? 'y' : '') ?>"
                               data-maillingFl="<?= ($val['maillingFl'] == 'y' ? 'y' : '') ?>"
                               data-smsFl="<?= ($val['smsFl'] == 'y' ? 'y' : '') ?>"/>
                    </td>
                    <td class="font-num">
                        <span class="js-layer-crm hand"><?= $page->idx--; ?></span>
                    </td>
                    <td class="">
                        <span class="font-eng js-layer-crm hand"><?= $val['memId']; ?>
                            <?= gd_get_third_party_icon_web_path($val['snsTypeFl']); ?>
                            <?php if ($val['nickNm']) { ?>
                                <div class="notice-ref notice-sm"><?= $val['nickNm']; ?></div><?php } ?></span>
                    </td>
                    <td>
                        <span class="js-layer-crm hand"><?= $val['memNm']; ?></span>
                    </td>
                    <td>
                        <span class="js-layer-crm hand"><?= gd_isset($groups[$val['groupSno']]); ?></span>
                    </td>
                    <td class="font-num">
                        <span class="js-layer-crm hand"><?= gd_currency_display($val['saleAmt']); ?></span>
                    </td>
                    <td class="font-num">
                        <span class="js-layer-crm hand"><?= gd_currency_display($val['mileage']); ?></span>
                    </td>
                    <td class="font-num">
                        <span class="js-layer-crm hand"><?= gd_currency_display($val['deposit']); ?></span>
                    </td>
                    <td class="font-date">
                        <span class="js-layer-crm hand"><?= substr($val['entryDt'], 2, 8); ?></span>
                    </td>
                    <td class="font-date">
                        <span class="js-layer-crm hand"><?= $lastLoginDt; ?></span>
                    </td>
                    <td>
                        <span class="js-layer-crm hand"><?= $txtAppFl; ?></span>
                    </td>
                </tr>
                <?php
            }
        } elseif ($isSkip) {
            echo '<tr><td class="center" colspan="11">검색기능을 이용해주세요.</td></tr>';
        } else {
            echo '<tr><td class="center" colspan="11">검색된 정보가 없습니다.</td></tr>';
        }
        ?>
        </tbody>
    </table>
    <div class="center"><?= $page->getPage(); ?></div>
</form>
<form name="setupForm" id="setupForm" method="post" class="content-form js-setup-form">
    <input type="hidden" name="mode" value="batch_deposit"/>
    <input type="hidden" name="batchTarget[]" value=""/>
    <input type="hidden" name="targetMemberFl" value=""/>
    <input type="hidden" name="memberType" value=""/>
    <input type="hidden" name="searchJson" value="<?= $searchJson; ?>"/>

    <div class="form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <tr name="trDepositDetail">
                <th>지급/차감여부</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="depositCheckFl" value="add" <?= $checked['depositCheckFl']['add']; ?> />
                        지급(+)
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="depositCheckFl" value="remove" <?= $checked['depositCheckFl']['remove']; ?> />
                        차감(-)
                    </label>
                </td>
            </tr>
            <tr name="trDepositDetail">
                <th class="require">금액설정</th>
                <td>
                    <span>(+)</span>
                    <input type="text" name="depositValue" value="<?= $search['depositValue']; ?>" class="js-number" maxlength="8"/>
                    원
                </td>
            </tr>
            <tr name="trDepositDetail">
                <th class="require">지급/차감사유</th>
                <td>
                    <?= gd_select_box('reasonCd', 'reasonCd', $depositReasons, null, $search['reasonCd'], '=지급/차감사유 선택='); ?>
                    <div>
                        <?php
                        if ('01006006' === Request::get()->get('reasonCd')) {
                            echo '<input type="text" name="contents" class="form-control" value="' . $contents . '"/>';
                        } else {
                            echo '<input type="hidden" name="contents" class="form-control" value="' . $contents . '"/>';
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr id="trDepositCheckFlag" class="<?php if (Request::get()->get('depositCheckFl', 'add') == 'add') echo 'display-none' ?>">
                <th>예치금<br/>부족 시 차감방법</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="removeMethodFl" value="minus" <?= $checked['removeMethodFl']['minus']; ?> />
                        남은 예치금만 차감
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="removeMethodFl" value="exclude" <?= $checked['removeMethodFl']['exclude']; ?> />
                        예치금 부족시 차감대상 제외
                    </label>
                </td>
            </tr>
            <tr>
                <th>회원안내</th>
                <td>
                    <label>
                        <input type="checkbox" name="guideSend[]" value="sms" <?= gd_isset($checked['guideSend']['sms']); ?> />
                        SMS발송 <a href="../member/sms_auto.php" target="_blank" class="btn-link">상세설정 ></a>
                    </label>
                    <label>
                        <input type="checkbox" name="guideSend[]" value="email" <?= gd_isset($checked['guideSend']['email']); ?> />
                        이메일발송 <a href="../member/mail_config_auto.php" target="_blank" class="btn-link">상세설정 ></a>
                    </label>
                    <div>
                        <span class="notice-info">* SMS는 잔여포인트가 있어야 발송됩니다. (잔여포인트 :
                            <span class="text-darkred bold"><?= number_format(gd_get_sms_point(), 1); ?></span>
                            ) <a href="#" class="btn btn-xs btn-gray mgl10 js-link-sms-charge">SMS포인트 충전하기</a>
                        </span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</form>
<script type="text/javascript">
    var deposit = {
        reasonCd: <?= json_encode($depositReasons); ?>
    };
    $(document).ready(function () {
        var $formSetup = $('.js-setup-form');
        var $formSearch = $('.js-search-form');
        var $formList = $('.js-list-form');
        $formSetup.$batch_target = $formSetup.find('input[name="batchTarget[]"]');
        $formSetup.$depositValue = $formSetup.find('input[name="depositValue"]');

        $('.btn-register').click(function () {
            if ($(':radio[name=targetMemberFl]:checked', $formSearch).val() == 'excel') {
                //                alert('준비 중입니다.');
                var $formExcel = $('#formExcel');
                $formExcel.find('input[name="guideSend[]"]').remove();
                $(':checkbox[name="guideSend[]"]:checked').each(function (idx, item) {
                    $('#formExcel').append('<input type="hidden" name="' + item.name + '" value="' + item.value + '">');
                });
                $formExcel.submit();
            } else {
                $formSetup.submit();
            }
        });

        $formSetup.validate({
            ignore: [],
            rules: {
                reasonCd: {
                    required: function () {
                        return $(':radio[name=targetMemberFl]:checked', $formSearch).val() == 'search';
                    }
                },
                depositValue: {
                    required: function () {
                        return $(':radio[name=targetMemberFl]:checked', $formSearch).val() == 'search';
                    }
                },
                "batchTarget[]": {
                    required: function () {
                        var isSelect = $('select[name=memberType] option:selected', $formSearch).val() == 'select';
                        var isSearch = $(':radio[name=targetMemberFl]:checked', $formSearch).val() == 'search';
                        return isSelect && isSearch && $(':checkbox:checked', $formList).length == 0;
                    }
                }
            }, messages: {
                reasonCd: "예치금을 지급/차감한 사유를 입력해주세요.",
                depositValue: "지급/차감할 예치금 금액을 입력해주세요.",
                "batchTarget[]": "예치금을 지급/차감할 대상 회원을 확인해주세요."
            }, submitHandler: function (form) {
                var $form = $(form);
                var data = $form.serializeArray();

                var depositCheckFl = $(':radio[name=depositCheckFl]:checked', $form).val();
                var targetMemberFl = $(':radio[name=targetMemberFl]:checked', $formSearch).val();
                var memberType = $('select[name=memberType] option:selected', $formSearch).val();

                data.push({name: "depositCheckFl", value: depositCheckFl});
                data.push({name: "targetMemberFl", value: targetMemberFl});
                data.push({name: "memberType", value: memberType});

                if (memberType == 'select') {
                    $("input[name='chk[]']:checked", $formList).each(function () {
                        data.push({name: "chk[]", value: $(this).val()});
                    });
                }

                if (depositCheckFl == 'add' && targetMemberFl == 'search' && memberType == 'query') {
                    data.push({name: "mode", value: "add_deposit_all"});
                } else if (depositCheckFl == 'add' && targetMemberFl == 'search' && memberType == 'select') {
                    data.push({name: "mode", value: "add_deposit"});
                } else if (depositCheckFl == 'remove' && targetMemberFl == 'search' && memberType == 'query') {
                    data.push({name: "mode", value: "remove_deposit_all"});
                } else if (depositCheckFl == 'remove' && targetMemberFl == 'search' && memberType == 'select') {
                    data.push({name: "mode", value: "remove_deposit"});
                } else {
                    alert("지급/차감 조건을 선택해주세요.");
                }

                ajax_with_layer('../member/member_batch_ps.php', data, function (data, textStatus, jqXHR) {
                    layer_close();

                    if (data[0]) {
                        var resultStorage = data[1];
                        var dialogMessage = "지급/차감이 완료되었습니다. 처리된 내역을 확인하시겠습니까?";
                        if (resultStorage && (resultStorage.excludeCount > 0)) {
                            if (resultStorage.excludeCount == resultStorage.totalCount) {
                                dialogMessage = "선택한 회원의 예치금이 차감할 금액보다 부족합니다. 대상회원을 다시 선택해주세요.";
                            }
                        }
                        BootstrapDialog.confirm({
                            title: "예치금 지급/차감 처리 완료",
                            message: dialogMessage,
                            btnOKLabel: "처리내역 확인",
                            btnCancelLabel: "지급/차감 계속진행",
                            callback: function (result) {
                                if (result) {
                                    top.location.href = '../member/member_batch_deposit_list.php';
                                } else {
                                    top.location = top.location.pathname;
                                }
                            }
                        });
                    } else {
                        BootstrapDialog.show({
                            message: "예치금 지급/차감 처리 중 오류가 발생하였습니다."
                        });
                    }
                });
            }
        });

        $('select[name=reasonCd]', $formSetup).on('change', function (e) {
            e.preventDefault();

            var $target = $(e.target);
            var $option = $target.find(':selected');
            var $contents = $('input[name=contents]');

            if ('01006006' === $option.val()) {
                $contents.attr('type', 'text').focus();
                $contents.val('');
            } else {
                $contents.attr('type', 'hidden');
                $contents.val(deposit.reasonCd[$option.val()]);
            }
        });

        var $depositCheckFl = $(':radio[name=depositCheckFl]', $formSetup);
        $depositCheckFl.on('change', function (e) {
            var $target = $(e.target);
            var $trDepositCheckFlag = $('#trDepositCheckFlag');
            if ($target.val() == 'remove') {
                $formSetup.$depositValue.prev('span').html('(-)');
                $trDepositCheckFlag.removeClass('display-none');
            } else {
                $formSetup.$depositValue.prev('span').html('(+)');
                $trDepositCheckFlag.addClass('display-none');
            }
        });

        $('#formSearch').validate({
            submitHandler: function (form) {
                var queryString1 = $(form).serialize();
                var queryString2 = $formSetup.find(':checked, :text, select, input[name="contents"]').serialize();
                window.location.href = '../member/member_batch_deposit.php?' + queryString1 + '&' + queryString2;
            }
        });

        var $templateAddDetailSearch = $('#templateAddDetailSearch');
        $('.search-detail-box tbody:eq(0)').prepend(_.template($templateAddDetailSearch.html()));
        $('.js-batch-excel tbody:eq(0)').prepend(_.template($templateAddDetailSearch.html()));
        $formSetup.find(':checkbox:checked, :radio:checked').trigger('change');

        // 회원검색 셀렉트 초기화
        if ($('select[name="memberType"]').length) {
            $('select[name="memberType"]').val('select').trigger('change');
        }

        member.toggleEventApplyQuery();

        var $targetMemberFl = $(':radio[name="targetMemberFl"]');
        $targetMemberFl.on('change', function () {
            var $jsBatchExcel = $('.js-batch-excel');
            var $search = $('.search-detail-box');
            var $searchBtn = $('.search-detail-box + .table-btn');
            var $depositDetail = $('tr[name="trDepositDetail"]');
            var $trDepositCheckFlag = $('#trDepositCheckFlag');
            var $frmList = $('#frmList');
            if (this.value == 'excel') {
                $search.addClass('display-none');
                $searchBtn.addClass('display-none');
                $jsBatchExcel.removeClass('display-none');
                $jsBatchExcel.find(':radio[name="targetMemberFl"]:eq(1)').prop('checked', true);
                $depositDetail.addClass('display-none');
                $trDepositCheckFlag.addClass('display-none');
                $frmList.addClass('display-none');
            } else {
                $search.removeClass('display-none');
                $search.find(':radio[name="targetMemberFl"]:eq(0)').prop('checked', true);
                $searchBtn.removeClass('display-none');
                $jsBatchExcel.addClass('display-none');
                $depositDetail.removeClass('display-none');
                $frmList.removeClass('display-none');
                if ($depositDetail.find(':radio[name="depositCheckFl"]:eq(1)').is(':checked')) {
                    $trDepositCheckFlag.removeClass('display-none');
                }
            }
        });
        $targetMemberFl.filter(':checked').trigger('change');
    });
</script>
<script type="text/html" id="templateAddDetailSearch">
    <tr>
        <th>대상회원 선택</th>
        <td colspan="3">
            <label class="radio-inline">
                <input type="radio" name="targetMemberFl" value="search" <?= $checked['targetMemberFl']['search']; ?>/>
                회원검색
            </label>
            <select name="memberType" class="form-inline">
                <option class="js-apply-query" value="query" <?= $selected['memberType']['query']; ?>>검색회원 전체적용</option>
                <option class="js-apply-query" value="select" <?= $selected['memberType']['select']; ?>>회원선택 적용</option>
            </select>
            <label class="radio-inline">
                <input type="radio" name="targetMemberFl" value="excel" <?= $checked['targetMemberFl']['excel']; ?>/>
                엑셀일괄등록
            </label>
        </td>
    </tr>
</script>
