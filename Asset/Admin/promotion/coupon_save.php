<?php
if ($nowCli == 'T') {
    ?>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <button type="button" class="btn btn-gray" disabled>쿠폰 발급 진행 중</button>
        </div>
    </div>

    <table class="table table-cols" style="margin-bottom: 3px !important;">
        <colgroup>
            <col class="width-md">
            <col class="width-3xl">
            <col class="width-md">
            <col class="width-3xl">
        </colgroup>
        <tbody>
        <tr>
            <th>쿠폰 대량 발급<br />진행현황</th>
            <td>
                <font style="color: #117ef9;">발급 완료(<strong><?php echo number_format($cliData['nowCount']); ?></strong>)</font> / 발급대상(<?php echo number_format($cliData['totalCount']); ?>)
                <span style="margin-left: 20px" class="notice-info">발급 완료 수는 ‘새로고침’하여 확인해주세요.</span>
            </td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>
    <div class=" mgb20"></div>
<?php } else { ?>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <button type="button" class="btn btn-red js-m-save">쿠폰 발급</button>
        </div>
    </div>
<?php } ?>

<h5 class="table-title gd-help-manual">쿠폰내용</h5>
<table class="table table-cols" style="margin-bottom: 3px !important;">
    <colgroup>
        <col class="width-md">
        <col class="width-3xl">
        <col class="width-md">
        <col class="width-3xl">
    </colgroup>
    <tbody>
    <tr>
        <th>쿠폰명</th>
        <td colspan="3"><?= $getData['couponNm']; ?></td>
    </tr>
    <tr>
        <th>쿠폰설명</th>
        <td colspan="3"><?= $getData['couponDescribed']; ?></td>
    </tr>
    <tr>
        <th>사용기간</th>
        <td><?= $getConvertData['useEndDate']; ?></td>
        <th>쿠폰유형</th>
        <td><?= $getConvertData['couponUseType']; ?></td>
    </tr>
    <tr>
        <th>발급구분</th>
        <td><?= $getConvertData['couponSaveType']; ?></td>
        <th>쿠폰혜택</th>
        <td><?php if ($getData['couponBenefitFixApply'] == 'all') { echo '수량별'; } ?><?= $getConvertData['couponBenefit'] . ' ' . $getConvertData['couponKindType']; ?></td>
    </tr>
    </tbody>
</table>
<div class=" mgb20"><a href="coupon_regist.php?couponNo=<?= $getData['couponNo']; ?>" class="right btn-link-underline">상세보기></a></div>
<form id="frmMemberCouponSample" action="../promotion/coupon_ps.php" method="post" target="ifrmProcess" class="content-form">
    <input type="hidden" name="mode" value="downExcelSample">
</form>
<h5 class="table-title gd-help-manual">발급 방법 선택</h5>
<form id="frmMemberCoupon" action="../promotion/coupon_ps.php" method="post" target="ifrmProcess" class="content-form" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="insertCouponSave">
    <input type="hidden" name="couponNo" value="<?= $getData['couponNo']; ?>">
    <input type="hidden" name="smsSendFlag" value="<?= $smsSendFlag; ?>">
    <table class="table table-cols">
        <colgroup>
            <col class="width-md">
            <col class="width-3xl">
            <col class="width-md">
            <col class="width-3xl">
        </colgroup>
        <tbody>
        <tr>
            <th>발급방법</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="saveMemberCouponType" value="member" checked="checked"/>회원검색
                    <select name="saveMemberType" class="select2" style="display: inline !important;">
                        <option value="chk" selected="selected">검색된 회원 선택발급</option>
                        <option value="all">검색된 회원 전체발급</option>
                    </select>
                </label>
                <label class="radio-inline">
                    <input type="radio" name="saveMemberCouponType" value="excel"/>엑셀일괄등록
                </label>
            </td>
            <th>SMS 발송상태</th>
            <td>
                <?php
                if (isset($smsSendFlag) && $smsSendFlag == 'y') {
                ?>
                    <strong>발송함</strong> <em style="color:#999999; font-size: 11px">(쿠폰발급 시 SMS도 발송됩니다.)</em><br/>
                <?php
                } else {
                ?>
                    <strong>발송안함</strong> <em style="color:#999999; font-size: 11px">(쿠폰발급 시 SMS는 발송되지 않습니다.)</em><br/>
                <?php
                }
                ?>
                <span class="notice-info">SMS 발송은 <a href="/member/sms_auto.php#promotion" target="_blank" class="btn-link-underline">회원>SMS관리>자동 SMS 설정</a>에서 설정할 수 있습니다.</span>
            </td>
        </tr>
        <tr class="save-type-excel">
            <th>
                엑셀파일등록
                <button type="button" class="btn btn-xs js-excel-sample" title="도움말 내용을 넣어주세요!">
                    샘플파일보기
                </button>
            </th>
            <td>
                <input type="file" name="excel">
                <div>
                    <h4>쿠폰 발급 방법</h4>
                    1. 아래 항목 설명되어 있는 것을 기준으로 엑셀 파일을 작성을 합니다.<br/> 2. [샘플파일보기]에서 받은 엑셀을 기준으로 파일을 작성을 합니다.<br/> 3. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;로 저장을 하셔야 합니다. 절대 csv 파일이 아닌 진짜 엑셀 파일입니다.(xls)<br/> 4. 작성된 엑셀 파일을 업로드 합니다.<br/>
                    <h4>주의사항</h4>
                    1. 엑셀 파일 저장은 반드시 &quot;Excel 97-2003 통합문서&quot;만 가능하며, csv 파일은 업로드가 되지 않습니다.<br/> 2. 엑셀 2003 사용자의 경우는 그냥 저장을 하시면 되고, 엑셀 2007 이나 엑셀 2010 인 경우는 새이름으로 저장을 선택해서 &quot;Excel 97-2003 통합문서&quot;로 저장을 하십시요.<br/> 3. 엑셀의 내용이 너무 많은 경우 업로드가 불가능 할수 있으므로 100개나 200개 단위로 나누어 올리시기 바랍니다.<br/> 4. 엑셀 파일이 작성이 완료 되었다면 하나의 회원만 테스트로 올려고보 확인후 이상이 없으시면 나머지를 올리시기 바랍니다.<br/>
                </div>
            </td>
        </tr>
        <tr class="save-type-link">
            <th>다운로드 링크</th>
            <td><?= $getMemberCouponDownLink; ?>
                <button type="button" class="btn btn-lg">복사</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<h5 class="table-title gd-help-manual save-type-member">발급 대상 검색</h5>
<form id="frmSearchBase" method="get" class="content-form js-search-form save-type-member js-form-enter-submit">
    <input type="hidden" name="sort" value="<?= gd_isset($search['sort']) ?>"/>
    <input type="hidden" name="pageNum" value="<?= gd_isset($search['pageNum'], 10) ?>"/>
    <input type="hidden" name="couponNo" value="<?= $getData['couponNo']; ?>">
    <?php include gd_admin_skin_path('member/member_detail_search.php'); ?>
</form>
<form id="frmMemberList" action="../promotion/coupon_ps.php" method="post" target="ifrmProcess" class="content-form save-type-member">
    <input type="hidden" name="mode" value="insertCouponSave"> <input type="hidden" name="kmode" value="chk">
    <input type="hidden" name="searchQuery" value="<?= $searchJson; ?>">
    <input type="hidden" name="couponNo" value="<?= $getData['couponNo']; ?>">
    <input type="hidden" name="smsSendFlag" value="<?= $smsSendFlag; ?>">

    <div class="table-header form-inline">
        <div class="pull-left">
            검색 <strong><?= number_format($page->recode['total'], 0); ?></strong>건 / 전체
            <strong><?= number_format($page->recode['amount'], 0); ?></strong>건
        </div>
        <div class="pull-right">
            <div>
                <select name="sort" class="form-control input-sm">
                    <option value="entryDt desc" <?= gd_isset($selected['sort']['entryDt desc']); ?>>회원가입일&darr;</option>
                    <option value="entryDt asc" <?= gd_isset($selected['sort']['entryDt asc']); ?>>회원가입일&uarr;</option>
                    <option value="lastLoginDt desc" <?= gd_isset($selected['sort']['lastLoginDt desc']); ?>>최종로그인&darr;</option>
                    <option value="lastLoginDt asc" <?= gd_isset($selected['sort']['lastLoginDt asc']); ?>>최종로그인&uarr;</option>
                    <option value="loginCnt desc" <?= gd_isset($selected['sort']['loginCnt desc']); ?>>방문횟수&darr;</option>
                    <option value="loginCnt asc" <?= gd_isset($selected['sort']['loginCnt asc']); ?>>방문횟수&uarr;</option>
                    <option value="memNm desc" <?= gd_isset($selected['sort']['memNm desc']); ?>>이름&darr;</option>
                    <option value="memNm asc" <?= gd_isset($selected['sort']['memNm asc']); ?>>이름&uarr;</option>
                    <option value="memId desc" <?= gd_isset($selected['sort']['memId desc']); ?>>아이디&darr;</option>
                    <option value="memId asc" <?= gd_isset($selected['sort']['memId asc']); ?>>아이디&uarr;</option>
                    <option value="mileage desc" <?= gd_isset($selected['sort']['mileage desc']); ?>>마일리지&darr;</option>
                    <option value="mileage asc" <?= gd_isset($selected['sort']['mileage asc']); ?>>마일리지&uarr;</option>
                    <option value="saleAmt desc" <?= gd_isset($selected['sort']['saleAmt desc']); ?>>주문금액&darr;</option>
                    <option value="saleAmt asc" <?= gd_isset($selected['sort']['saleAmt asc']); ?>>주문금액&uarr;</option>
                </select>&nbsp;
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
            <th><input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/></th>
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
        if (isset($getMemberData) && is_array($getMemberData) && \count($getMemberData) > 0) {
            foreach ($getMemberData as $val) {
                $lastLoginDt = (substr($val['lastLoginDt'], 2, 8) != date('y-m-d')) ? substr($val['lastLoginDt'], 2, 8) : '<span class="">' . substr($val['lastLoginDt'], 11) . '</span>';
                $txtAppFl = ($val['appFl'] == 'y' ? '승인' : '미승인');
                ?>
                <tr class="center" data-member-no="<?= $val['memNo']; ?>">
                    <td><input type="checkbox" name="chk[]" value="<?= $val['memNo']; ?>"/></td>
                    <td class="number js-layer-crm hand"><?= $page->idx--; ?></td>
                    <td class="js-layer-crm hand">
                        <span class="font-eng"><?= $val['memId']; ?></span>
                        <?= gd_get_third_party_icon_web_path($val['snsTypeFl']); ?>
                        <?php if ($val['nickNm']) { ?>
                            <div class="notice-ref notice-sm"><?= $val['nickNm']; ?></div><?php } ?>
                    </td>
                    <td class="js-layer-crm hand">
                        <?= $val['memNm']; ?>
                    </td>
                    <td class="js-layer-crm hand"><?= gd_isset($groups[$val['groupSno']]); ?></td>
                    <td class="number js-layer-crm hand"><?= number_format($val['saleAmt']); ?></td>
                    <td class="number js-layer-crm hand"><?= number_format($val['mileage']); ?></td>
                    <td class="number js-layer-crm hand"><?= number_format($val['deposit']); ?></td>
                    <td class="date js-layer-crm hand"><?= substr($val['entryDt'], 2, 8); ?></td>
                    <td class="date js-layer-crm hand"><?= $lastLoginDt; ?></td>
                    <td><?= $txtAppFl; ?></td>
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

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmMemberList select[name=\'sort\']').change(function () {
            $('input[name=\'sort\']').val($(this).val());
            $('#frmSearchBase').submit();
        });

        $('#frmMemberList').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            }
        });
        $('.js-excel-sample').click(function (e) {
            $('#frmMemberCouponSample').submit();
        });
        $('.js-m-save').click(function (e) {
            var checkCouponType = true;
            $.ajax({
                url: "../promotion/coupon_ps.php",
                method: "post",
                async: false,
                data: {
                    "mode": "checkCouponType",
                    "couponNo": $('input[name=couponNo]').val()
                },
                success: function (data) {
                    checkCouponType = data.isSuccess;
                }
            });
            if(!checkCouponType) {
                alert('발급종료 상태의 쿠폰은 발급이 불가합니다.');
                return false;
            }
            if ($('input:radio[name="saveMemberCouponType"]:checked').val() == 'member') {
                if ($('select[name="saveMemberType"]').val() == 'chk') {
                    var countMsg = $('input[name*=chk]:checked').length;
                    if (countMsg < 1) {
                        alert("쿠폰을 발급할 회원을 선택해 주세요.");
                        return false;
                    }
                    $('input:hidden[name="kmode"]').val('chk');
                } else if ($('select[name="saveMemberType"]').val() == 'all') {
                    var countMsg = '<?= $page->recode['total'];?>';
                    $('input:hidden[name="kmode"]').val('all');
                }

                var ConfirmMessage = '<div style="text-align: center;">선택된 ' + countMsg + '명의 회원에게 쿠폰을 정말로 발급 하시겠습니까?';
                <?php
                if (isset($smsSendFlag) && $smsSendFlag == 'y') {
                ?>
                ConfirmMessage += '<br><br>';
                ConfirmMessage += '<p style="display:block; color:red; font-size:11px"><img src="/admin/gd_share/img/icon_notice_red.png"> SMS 잔여 포인트가 부족한 경우 부족한 포인트만큼 문자 발송이 되지 않습니다.</p>';
                ConfirmMessage += '<em style="color:#999999; font-size: 11px">[회원>SMS관리>SMS발송 내역 보기]에서 발송결과를 꼭 확인하시기 바랍니다.</em>';
                <?php
                }
                ?>
                ConfirmMessage += '</div>';

                if ($('#frmMemberList').valid()) {
                    <?php if (isset($smsSendFlag) && $smsSendFlag === 'y') { ?>
                    if (($('select[name="saveMemberType"]').val() == 'all' && countMsg >= 100) || $('input[name^=chk]:checked').length >= 100) {
                        BootstrapDialog.confirm({
                            type: BootstrapDialog.TYPE_DANGER,
                            title: '쿠폰발급',
                            message: ConfirmMessage,
                            closable: false,
                            callback: function (result) {
                                if (result) {
                                    $.get('../share/layer_sms_password.php?mode=input', function () {
                                        console.log(arguments);
                                        BootstrapDialog.show({
                                            title: 'SMS 인증번호 변경하기',
                                            size: BootstrapDialog.SIZE_WIDE,
                                            message: arguments[0]
                                        });
                                    });
                                }
                            }
                        });
                    } else {
                        $('#frmMemberList').append("<input type='hidden' name='passwordCheckFl' value='n' />");
                        $('#frmMemberList').submit();
                    }
                    <?php } else { ?>
                    if ($('select[name="saveMemberType"]').val() == 'all') {
                        if (countMsg > 30000) {
                            ConfirmMessage = "쿠폰 발급 대상이 30,000 건을 초과하여 '대량발급'으로 처리됩니다.<br />";
                            ConfirmMessage += "발급 완료 시 까지 약 1시간 정도 소요될 수 있으며, 경우에 따라 시간이 더 소요될 수 있습니다.<br /><br />";
                            ConfirmMessage += "쿠폰 발급 처리현황은 수동 발급 화면에서 확인 가능합니다.";
                            BootstrapDialog.confirm({
                                type: BootstrapDialog.TYPE_DANGER,
                                title: '쿠폰 대량 발급 안내',
                                message: ConfirmMessage,
                                closable: false,
                                callback: function (result) {
                                    if (result) {
                                        $('#frmMemberList').submit();
                                    }
                                }
                            });
                        } else {
                            $('#frmMemberList').submit();
                        }
                    } else {
                        $('#frmMemberList').submit();
                    }
                    <?php } ?>
                }
            } else if ($('input:radio[name="saveMemberCouponType"]:checked').val() == 'excel') {
                <?php if (isset($smsSendFlag) && $smsSendFlag === 'y') { ?>
                $.get('../share/layer_sms_password.php?mode=input', function () {
                    BootstrapDialog.show({
                        title: 'SMS 인증번호 변경하기',
                        size: BootstrapDialog.SIZE_WIDE,
                        message: arguments[0]
                    });
                });
                <?php } else { ?>
                $('#frmMemberCoupon').submit();
                <?php } ?>
            }
        });
        $('input:radio[name="saveMemberCouponType"]').click(function (e) {
            changeMemberCouponSaveType();
        });
        $('select[name="saveMemberType"]').click(function (e) {
            changeMemberType();
        });
        changeMemberType();
        changeMemberCouponSaveType();
    });
    function changeMemberCouponSaveType() {
        if ($('input:radio[name="saveMemberCouponType"]:checked').val() == 'member') {
            $('.save-type-member').show();
            $('.save-type-member input').removeAttr('disabled');
            $('.save-type-excel').hide();
            $('.save-type-excel input').attr('disabled', 'disabled');
            $('.save-type-link').hide();
            $('.save-type-link input').attr('disabled', 'disabled');
        } else if ($('input:radio[name="saveMemberCouponType"]:checked').val() == 'excel') {
            $('.save-type-member').hide();
            $('.save-type-member input').attr('disabled', 'disabled');
            $('.save-type-excel').show();
            $('.save-type-excel input').removeAttr('disabled');
            $('.save-type-link').hide();
            $('.save-type-link input').attr('disabled', 'disabled');
        } else if ($('input:radio[name="saveMemberCouponType"]:checked').val() == 'link') {
            $('.save-type-member').hide();
            $('.save-type-member input').attr('disabled', 'disabled');
            $('.save-type-excel').hide();
            $('.save-type-excel input').attr('disabled', 'disabled');
            $('.save-type-link').show();
            $('.save-type-link input').removeAttr('disabled');
        }
    }
    function changeMemberType() {
        if ($('select[name="saveMemberType"]').val() == 'chk') {
            $('input[name*=chk]').removeAttr('disabled', 'disabled');
        } else {
            $('input[name*=chk]').attr('disabled', 'disabled');
        }
    }

    function sms_password_callback(password) {
        if($('input:radio[name="saveMemberCouponType"]:checked').val() == 'member') {
            var $frm = $('#frmMemberList');
        } else {
            var $frm = $('#frmMemberCoupon');
        }
        $frm.append('<input type="hidden" name="password" value="' + password + '"/>');
        $frm.submit();
    }
    //-->
</script>
