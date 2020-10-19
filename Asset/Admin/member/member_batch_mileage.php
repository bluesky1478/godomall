<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./member_batch_mileage_list.php');" />
        <input type="button" value="마일리지 지급/차감" class="btn btn-red btn-register"/>
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
    <form id="formExcel" action="excel_mileage_ps.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="mileageExcelUpload"/>

        <table class="table table-cols">
            <colgroup>
                <col class="width-md">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>엑셀파일등록 <a href="excel_mileage_ps.php?mode=downloadSample" class="btn btn-gray btn-sm mgt5" id="btnExcelSample">샘플파일보기</a>
                </th>
                <td>
                    <input type="file" name="excel" value=""/>
                    <div class="contents">
                        <div class="notice-info">엑셀파일등록 시 유의사항</div>
                        <div>- 엑셀 파일은 반드시 "Excel 97-2003 통합문서(.xls)" 형식만 가능합니다.</div>
                        <div>- 엑셀의 내용이 너무 많은 경우 업로드가 불가능 할수 있으므로 100개나 200개 단위로 나누어 올리시기 바랍니다.</div>
                        <div>- 엑셀 내용중 "1번째 줄은 설명", "2번째 줄은 excel DB 코드", "3번째 줄은 설명" 이며, "4번째 줄부터" 데이터 입니다.</div>
                        <div>- 엑셀 내용중 2번째 줄 "excel DB" 코드는 필수 데이타 입니다. 그리고 반드시 내용은 "4번째 줄부터" 작성 하셔야 합니다.</div>
                        <div>- 엑셀 항목 중 “아이디”와 “지급/차감 마일리지”는 필수값입니다.</div>
                        <div>- [샘플파일보기] 버튼을 눌러 다운로드 되는 엑셀 샘플파일을 참고하시기 바랍니다.</div>
                        <div>- 차감 처리 시 "지급/차감 마일리지"에 마이너스 숫자(예시) -400)을 입력하시면 되고, 차감할 마일리지보다 보유 마일리지가 적은 회원은 마이너스 처리됩니다.</div>
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
                <?php echo gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
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
                <tr class="center hand" data-member-no="<?= $val['memNo']; ?>">
                    <td>
                        <input type="checkbox" name="chk[]" value="<?= $val['memNo']; ?>"
                               data-appFl="<?= ($val['appFl'] == 'y' ? 'y' : '') ?>"
                               data-maillingFl="<?= ($val['maillingFl'] == 'y' ? 'y' : '') ?>"
                               data-smsFl="<?= ($val['smsFl'] == 'y' ? 'y' : '') ?>"/>
                    </td>
                    <td>
                        <span class="number  js-layer-crm"><?= $page->idx--; ?></span>
                    </td>
                    <td>
                        <span class="font-eng js-layer-crm"><?= $val['memId']; ?>
                            <?= gd_get_third_party_icon_web_path($val['snsTypeFl']); ?>
                            <?php if ($val['nickNm']) { ?>
                                <div class="notice-ref notice-sm"><?= $val['nickNm']; ?></div><?php } ?>
                        </span>
                    </td>
                    <td>
                        <span class="js-layer-crm"> <?= $val['memNm']; ?></span>
                    </td>
                    <td>
                        <span class="js-layer-crm"><?= gd_isset($groups[$val['groupSno']]); ?></span>
                    </td>
                    <td>
                        <span class="number js-layer-crm"><?= gd_currency_display($val['saleAmt']); ?></span>
                    </td>
                    <td>
                        <span class="number js-layer-crm"><?= gd_currency_display($val['mileage']); ?></span>
                    </td>
                    <td>
                        <span class="number js-layer-crm"><?= gd_currency_display($val['deposit']); ?></span>
                    </td>
                    <td>
                        <span class="date js-layer-crm"><?= substr($val['entryDt'], 2, 8); ?></span>
                    </td>
                    <td>
                        <span class="date js-layer-crm"><?= $lastLoginDt; ?></span>
                    </td>
                    <td>
                        <span class="js-layer-crm"><?= $txtAppFl; ?></span>
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
    <input type="hidden" name="mode" value="batch_mileage"/>
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
            <tr name="trMileageDetail">
                <th>지급/차감여부</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="mileageCheckFl" value="add" <?= $checked['mileageCheckFl']['add']; ?> />
                        지급(+)
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="mileageCheckFl" value="remove" <?= $checked['mileageCheckFl']['remove']; ?> />
                        차감(-)
                    </label>
                </td>
            </tr>
            <tr name="trMileageDetail">
                <th class="require">금액설정</th>
                <td>
                    <span>(+)</span>
                    <input type="text" name="mileageValue" value="<?= gd_isset($search['mileageValue']); ?>" class="js-number" maxlength="8"/>
                    원
                </td>
            </tr>
            <tr name="trMileageDetail">
                <th class="require">지급/차감사유</th>
                <td>
                    <?= gd_select_box('reasonCd', 'reasonCd', $mileageReasons, null, $search['reasonCd'], '=지급/차감사유 선택='); ?>
                    <div>
                        <?php
                        if ($search['reasonCd'] == '01005011') {
                            echo '<input type="text" name="contents" class="form-control" value="' . $search['contents'] . '"/>';
                        } else {
                            echo '<input type="hidden" name="contents" class="form-control" value="' . $search['contents'] . '"/>';
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <tr id="trMileageCheckFlag" class="<?php if (Request::get()->get('mileageCheckFl', 'add') == 'add') echo 'display-none' ?>">
                <th>마일리지<br/>부족 시 차감방법</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="removeMethodFl"
                               value="minus" <?= $checked['removeMethodFl']['minus']; ?>
                        />
                        마일리지 마이너스 처리 (예: -2000)
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="removeMethodFl"
                               value="exclude" <?= $checked['removeMethodFl']['exclude']; ?>
                        />
                        마일리지 부족시 차감대상 제외
                    </label>
                </td>
            </tr>
            <tr>
                <th>회원안내</th>
                <td>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="guideSend[]"
                               value="sms" <?= gd_isset($checked['guideSend']['sms']); ?>
                        />SMS발송
                    </label>
                    <a href="#member" class="btn-link js-link-sms-auto">상세설정 ></a>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="guideSend[]"
                               value="email" <?= gd_isset($checked['guideSend']['email']); ?>
                        />이메일발송
                    </label>
                    <a href="#point" class="btn-link js-link-mail-auto">상세설정 ></a>
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
    $(document).ready(function () {
        var $formSetup = $('.js-setup-form');
        var $formSearch = $('.js-search-form');
        var $formList = $('.js-list-form');
        $formSetup.$batch_target = $formSetup.find('input[name="batchTarget[]"]');
        $formSetup.$mileageValue = $formSetup.find('input[name="mileageValue"]');

        $('.btn-register').click(function () {
            if ($(':radio[name=targetMemberFl]:checked', $formSearch).val() == 'excel') {
                //                alert('준비 중입니다.');
                $(':checkbox[name="guideSend[]"]:checked').each(function (idx, item) {
                    $('#formExcel').append('<input type="hidden" name="' + item.name + '" value="' + item.value + '">');
                });
                $('#formExcel').submit();
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
                mileageValue: {
                    required: function () {
                        return $(':radio[name=targetMemberFl]:checked', $formSearch).val() == 'search';
                    }
                },
                "batchTarget[]": {
                    required: function () {
                        var isSelect = $('select[name=memberType] option:selected', $formSearch).val() == 'select';
                        var isSearch = $(':radio[name=targetMemberFl]:checked', $formSearch).val() == 'search';
                        return isSearch && isSelect && $(':checkbox:checked', $formList).length === 0;
                    }
                }
            }, messages: {
                reasonCd: "마일리지를 지급/차감한 사유를 입력해주세요.",
                mileageValue: "지급/차감할 마일리지 금액을 입력해주세요.",
                "batchTarget[]": "마일리지를 지급/차감할 대상 회원을 확인해주세요."
            }, submitHandler: function (form) {
                var $form = $(form);
                var params = $form.serializeArray();

                var mileageCheckFl = $(':radio[name=mileageCheckFl]:checked', $form).val();
                var targetMemberFl = $(':radio[name=targetMemberFl]:checked', $formSearch).val();
                var memberType = $('select[name=memberType] option:selected', $formSearch).val();

                params.push({name: "mileageCheckFl", value: mileageCheckFl});
                params.push({name: "targetMemberFl", value: targetMemberFl});
                params.push({name: "memberType", value: memberType});

                if (memberType == 'select') {
                    $("input[name='chk[]']:checked", $formList).each(function () {
                        params.push({name: "chk[]", value: $(this).val()});
                    });
                }

                if (mileageCheckFl == 'add' && targetMemberFl == 'search' && memberType == 'query') {
                    params.push({name: "mode", value: "all_add_mileage"});
                } else if (mileageCheckFl == 'add' && targetMemberFl == 'search' && memberType == 'select') {
                    params.push({name: "mode", value: "add_mileage"});
                } else if (mileageCheckFl == 'remove' && targetMemberFl == 'search' && memberType == 'query') {
                    params.push({name: "mode", value: "all_remove_mileage"});
                } else if (mileageCheckFl == 'remove' && targetMemberFl == 'search' && memberType == 'select') {
                    params.push({name: "mode", value: "remove_mileage"});
                } else {
                    alert("지급/차감 조건을 선택해주세요.");
                }

                ajax_with_layer('../member/member_batch_ps.php', params, function (data, textStatus, jqXHR) {
                    layer_close();
                    if (data[0]) {
                        var resultStorage = data[1];
                        var dialogMessage = "지급/차감이 완료되었습니다. 처리된 내역을 확인하시겠습니까?";
                        if (resultStorage && (resultStorage.excludeCount > 0)) {
                            if (resultStorage.excludeCount == resultStorage.totalCount) {
                                dialogMessage = "선택한 회원의 마일리지가 차감할 금액보다 부족합니다. 대상회원을 다시 선택해주세요.";
                            }
                        }

                        BootstrapDialog.confirm({
                            title: "마일리지 지급/차감 처리 완료",
                            message: dialogMessage,
                            btnOKLabel: "처리내역 확인",
                            btnCancelLabel: "지급/차감 계속진행",
                            callback: function (result) {
                                if (result) {
                                    top.location.href = '../member/member_batch_mileage_list.php';
                                } else {
                                    top.location = top.location.pathname;
                                }
                            }
                        });
                    } else {
                        alert("마일리지 지급/차감 처리 중 오류가 발생하였습니다.");
                    }
                });
            }
        });

        $('select[name=reasonCd]', $formSetup).on('change', function (e) {
            e.preventDefault();
            var $target = $(e.target);
            var $option = $target.find(':selected');
            var $contents = $('input[name=contents]');

            if ('01005011' == $option.val()) {
                $contents.attr('type', 'text').focus();
                $contents.val('');
            } else {
                $contents.attr('type', 'hidden');
                $contents.val($option.text());
            }
        });

        var $mileageCheckFl = $(':radio[name=mileageCheckFl]', $formSetup);
        $mileageCheckFl.on('change', function (e) {
            var $target = $(e.target);
            var $trMileageCheckFlag = $('#trMileageCheckFlag');
            if ($target.val() == 'remove') {
                $formSetup.$mileageValue.prev('span').html('(-)');
                $trMileageCheckFlag.removeClass('display-none');
            } else {
                $formSetup.$mileageValue.prev('span').html('(+)');
                $trMileageCheckFlag.addClass('display-none');
            }
        });

        $('#formSearch').validate({
            submitHandler: function (form) {
                var queryString1 = $(form).serialize();
                var queryString2 = $formSetup.find(':checked, :text, select').serialize();
                window.location.href = '../member/member_batch_mileage.php?' + queryString1 + '&' + queryString2;
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
            var $mileageDetail = $('tr[name="trMileageDetail"]');
            var $trMileageCheckFlag = $('#trMileageCheckFlag');
            var $frmList = $('#frmList');
            if (this.value == 'excel') {
                $search.addClass('display-none');
                $searchBtn.addClass('display-none');
                $jsBatchExcel.removeClass('display-none');
                $jsBatchExcel.find(':radio[name="targetMemberFl"]:eq(1)').prop('checked', true);
                $mileageDetail.addClass('display-none');
                $trMileageCheckFlag.addClass('display-none');
                $frmList.addClass('display-none');
            } else {
                $search.removeClass('display-none');
                $search.find(':radio[name="targetMemberFl"]:eq(0)').prop('checked', true);
                $searchBtn.removeClass('display-none');
                $jsBatchExcel.addClass('display-none');
                $mileageDetail.removeClass('display-none');
                $frmList.removeClass('display-none');
                if ($mileageDetail.find(':radio[name="mileageCheckFl"]:eq(1)').is(':checked')) {
                    $trMileageCheckFlag.removeClass('display-none');
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
                <input type="radio" name="targetMemberFl" class="" value="search" <?= $checked['targetMemberFl']['search']; ?>/>
                회원검색
            </label>
            <select name="memberType" class="form-inline">
                <option class="js-apply-query" value="query" <?= $selected['memberType']['query']; ?>>검색회원 전체적용</option>
                <option class="js-apply-query" value="select" <?= $selected['memberType']['select']; ?>>회원선택 적용</option>
            </select>
            <label class="radio-inline">
                <input type="radio" name="targetMemberFl" class="" value="excel" <?= $checked['targetMemberFl']['excel']; ?>/>
                엑셀일괄등록
            </label>
        </td>
    </tr>
</script>
