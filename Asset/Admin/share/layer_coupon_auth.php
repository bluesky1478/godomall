<form id="frmCouponOfflineCodeSample" action="../promotion/coupon_ps.php" method="post" target="ifrmProcess" class="content-form">
    <input type="hidden" name="mode" value="downCouponOfflineCodeExcelSample">
</form>
<div>
    <div class="mgt10"></div>
    <form id="frmOfflineCode" method="post" action="coupon_ps.php" class="content_form" enctype="multipart/form-data">
        <input type="hidden" name="mode" value="insertCouponOfflineCodeAuto">
        <input type="hidden" name="couponNo" value="<?= $couponData['couponNo']; ?>">
        <div class="search-detail-box">
            <table class="table table-cols no-title-line">
                <colgroup>
                    <col class="width-sm"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>쿠폰명</th>
                    <td><?= $couponData['couponNm']; ?></td>
                </tr>
                <tr>
                    <th>등록방법</th>
                    <td>
                        <?php if($couponData['couponType'] == 'f') { ?>
                            <span class="notice-danger">발급종료 상태의 쿠폰은 인증번호 등록이 불가합니다.</span>
                        <?php } else {?>
                            <label class="radio-inline">
                                <input type="radio" name="couponCreateType" value="auto" checked="checked"/>자동생성
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="couponCreateType" value="excel"/>엑셀업로드
                            </label>
                        <?php } ?>
                    </td>
                </tr>
                <?php if($couponData['couponType'] != 'f') { ?>
                <tr class="tr-auto">
                    <th>등록수량</th>
                    <td>
                        <input type="text" name="couponAmount" value="" maxlength="5" class="width10p">장
                        <p class="notice-info">1회 최대 등록수량은 1,000장 입니다.</p>
                    </td>
                </tr>
                <tr class="tr-excel">
                    <th>
                        엑셀업로드
                        <button type="button" class="btn btn-xs js-excel-sample" title="도움말 내용을 넣어주세요!">
                            샘플파일보기
                        </button>
                    </th>
                    <td><input type="file" name="excel">
                        <p class="notice-info">엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;만 가능하며, csv 파일은 업로드가 되지 않습니다.</p>
                        <p class="notice-info">1회 최대 등록수량은 5,000장 입니다. (쿠폰인증번호는 최대 한글 6자, 영문숫자 12자리까지만 입력 가능합니다.)</p>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </form>
</div>

<div>
    <form name="frmCouponOfflineCodeList" method="post" id="frmCouponOfflineCodeList">
        <input type="hidden" name="couponNo" value="<?= $couponData['couponNo']; ?>">
        <table class="table table-rows">
            <thead>
            <tr>
                <th class="width10p">
                    <input type="checkbox" id="allCheck" value="y" class="js-checkall" data-target-name="layer_auth_code[]"/>
                </th>
                <th class="width10p">번호</th>
                <th>쿠폰인증번호</th>
                <th class="width20p">등록일</th>
                <th class="width20p">등록자</th>
                <th class="width20p">발급상태</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data) && is_array($data)) {
                $i = 0;
                foreach ($data as $key => $val) {
                    if ($val['couponOfflineCodeSaveType'] == 'y') {
                        $couponOfflineCodeSaveType = '발급';
                        $disabled = 'disabled="disabled"';
                    } else {
                        $couponOfflineCodeSaveType = '미발급';
                        $disabled = '';
                    }
                    ?>
                    <tr class="text-center">
                        <td>
                            <input type="checkbox" id="layer_code_<?= $val['couponOfflineCode']; ?>" name="layer_auth_code[]" value="<?= $val['couponOfflineCode']; ?>" <?= $disabled; ?>/>
                        </td>
                        <td><?= number_format($page->idx--); ?></td>
                        <td class="text-left">
                            <label for="layer_code_<?= $val['couponOfflineCode']; ?>">
                                <?= $val['couponOfflineCodeUser']; ?>
                            </label>
                        </td>
                        <td><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
                        <td><?= $val['couponOfflineInsertAdminId']; ?></td>
                        <td><?= $couponOfflineCodeSaveType; ?></td>
                    </tr>
                    <?php
                    $i++;
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="6">검색을 이용해 주세요.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </form>
    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white js-delete-auth">선택 삭제</button>
        </div>
        <?php if($excelFl =='y') { ?>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmOfflineCode" data-search-count="<?= $page->recode['total'] ?>" data-total-count="<?= $page->recode['amount'] ?>" data-target-list-form="frmCouponOfflineCodeList" data-target-list-sno="layer_auth_code">엑셀다운로드</button>
        </div>
        <?php } ?>
    </div>
    <div class="center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>
</div>
<div class="center">
    <input type="button" value="취소" class="btn btn-lg btn-white js-layer-close"/>
    <?php if($couponData['couponType'] != 'f') { ?>
    <input type="button" value="등록" class="btn btn-lg btn-black js-insert"/>
    <?php } ?>
</div>
<div id="excelResult" style="visibility: hidden;"></div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $("#frmOfflineCode").validate({
            dialog: false,
            submitHandler: function (form) {
                if ($(form).find('[name="mode"]').val() == 'insertCouponOfflineCodeAuto') {
                    form.target = 'ifrmProcess';
                    form.submit();
                } else {
                    var url = $(form).attr('action');
                    var data = $(form).serializeObject();
                    var formData = new FormData();
                    formData.append('mode', data.mode);
                    formData.append('couponNo', data.couponNo);
                    formData.append('couponCreateType', data.couponCreateType);
                    formData.append('couponAmount', data.couponAmount);
                    formData.append('excel', $('input[name="excel"]')[0].files[0]);

                    $.ajax({
                        url: url,
                        data: formData,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function (data) {
                            BootstrapDialog.show({
                                title: '인증번호 등록/관리',
                                message: data.msg,
                                closable: false,
                                buttons: [{
                                    label: '확인',
                                    cssClass: 'btn-primary',
                                    action: function (dialog) {
                                        dialog.close();
                                        $('.js-layer-close').trigger('click');

                                        // 엑셀 다운로드
                                        $('#excelResult').html(data.content);
                                        $('#excelResult').table2excel({
                                            filename: '인증번호 등록결과',
                                            sheetName: '등록결과',
                                            fileext: '.xls'
                                        });
                                        layerCouponAuth(<?= $couponData['couponNo']; ?>);
                                    }
                                }]
                            });
                        }
                    });
                }
            },
            rules: {
                mode: {
                    required: true,
                },
                couponNo: {
                    required: true,
                },
                couponAmount: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=couponCreateType]:checked').val() == 'auto') {
                            required = true;
                        }
                        return required;
                    }
                },
                excel: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=couponCreateType]:checked').val() == 'excel') {
                            required = true;
                        }
                        return required;
                    }
                },
            },
            messages: {
                mode: {
                    required: '정상 접속이 아닙니다.(mode)',
                },
                couponNo: {
                    required: '정상 접속이 아닙니다.(kind)',
                },
                couponAmount: {
                    required: '수량을 입력하세요.',
                },
                excel: {
                    required: '엑셀 파일을 등록해 주세요.',
                },
            }
        });
        $('.js-insert').click(function (e) {
            $('#frmOfflineCode').submit();
        });
        $('.js-excel-sample').click(function (e) {
            $('#frmCouponOfflineCodeSample').submit();
        });
        $('.js-delete-auth').click(function (e) {
            var chkCnt = $('input:checkbox[name="layer_auth_code[]"]:checked').length;
            if (chkCnt > 0) {
                $.ajax({
                    method: "POST",
                    cache: false,
                    url: "../promotion/coupon_ps.php",
                    data: "mode=deleteCouponOfflineCode&couponNo=<?= $couponData['couponNo']; ?>&" + $('input:checkbox[name="layer_auth_code[]"]:checked').serialize(),
                    dataType: 'json'
                }).success(function (data) {
                    alert(data['msg']);
                    if (data['result'] == 'ok') {
                        var pagelink = 'couponNo=<?= $couponData['couponNo']; ?>';
                        layer_list_search(pagelink);
                    }
                }).error(function (e) {
                    alert(e.responseText);
                });

            } else {
                alert('삭제할 인증번호를 선택해주세요.');
            }
        });
        // 등록방법 선택 시
        $('input:radio[name="couponCreateType"]').click(function (e) {
            changeCouponCreateType();
        });
        changeCouponCreateType();
    });
    function changeCouponCreateType() {
        if ($('input:radio[name="couponCreateType"]:checked').val() == 'auto') {
            $('#frmOfflineCode > input:hidden[name="mode"]').val('insertCouponOfflineCodeAuto');
            $('.tr-auto').show();
            $('.tr-excel').hide();
        } else if ($('input:radio[name="couponCreateType"]:checked').val() == 'excel') {
            $('#frmOfflineCode > input:hidden[name="mode"]').val('insertCouponOfflineCodeExcel');
            $('.tr-auto').hide();
            $('.tr-excel').show();
        }
    }
    function layer_list_search(pagelink) {
        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }

        $.get('<?php echo URI_ADMIN;?>share/layer_coupon_auth.php', pagelink, function (data) {
            $('#layer_coupon_auth').html(data);
        });
    }
    //-->
</script>
