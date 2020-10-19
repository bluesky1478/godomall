<form id="frmSetup" action="../policy/member_ps.php" method="post">
    <input type="hidden" name="mode" value="member_auth_cellphone"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        휴대폰인증 사용 설정
    </div>

    <div >
        <ul class="nav nav-tabs">
            <li role="presentation" <?php if($useTab =='kcp') { ?>class="active"<?php } ?>>
                <a href="#kcpTag" aria-controls="seo-content-tag" role="tab" data-toggle="tab">NHN KCP</a>
            </li>
            <li role="presentation" <?php if($useTab =='dream') { ?>class="active"<?php } ?>>
                <a href="#dreamTag" aria-controls="seo-content-tag" role="tab" data-toggle="tab">드림시큐리티</a>
            </li>
        </ul>
    </div>

    <div class="tab-content mgb30">
        <div role="tabpanel" class="tab-pane in <?php if($useTab =='kcp') { ?>active<?php } ?>" id="kcpTag">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>사용 설정</th>
                    <td class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" id="useFlKcpY" name="useFlKcp" value="y" <?php echo gd_isset($checked['useFlKcp']['y']); ?> />
                            사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" id="useFlKcpN" name="useFlKcp" value="n" <?php echo gd_isset($checked['useFlKcp']['n']); ?> />
                            사용안함
                        </label>
                        <p class="notice-info">
                            서비스 신청 전인 경우 먼저 서비스를 신청하세요.
                            <a href="/service/service_info.php?menu=member_auth_info" target="_blank" class="btn-link">서비스 자세히보기 ></a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>회원사 CODE</th>
                    <td class="form-inline">
                        <?php if ($dataKcp['serviceId'] == '') { ?>
                            <span class="text-red bold">미승인</span>
                        <?php } else { ?>
                            <span class="text-blue bold"><?php echo $dataKcp['serviceId']; ?></span>
                            <span class="text-red bold">(승인)</span>
                            </p>
                        <?php } ?>
                        <input type="hidden" id="serviceId" name="serviceId" value="<?php echo $dataKcp['serviceId']; ?>">
                    </td>
                </tr>
                <tr id="subTr1">
                    <th>회원 가입 시,<br />휴대폰 본인확인 정보 사용</th>
                    <td class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" id="useDataJoinFlKcpY" name="useDataJoinFlKcp" value="y" <?php echo gd_isset($checked['useDataJoinFlKcp']['y']); ?> />
                            사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" id="useDataJoinFlKcpN" name="useDataJoinFlKcp" value="n" <?php echo gd_isset($checked['useDataJoinFlKcp']['n']); ?> />
                            사용안함
                        </label>
                        <p class="notice-info">
                            휴대폰 본인인증 정보를 회원 정보로 사용하며, 해당 정보(이름, 휴대폰번호, 생일, 성별)는 고객이 수정할 수 없도록 설정합니다.
                        </p>
                        <p class="notice-info">
                            "사용함" 설정 시, 이름, 휴대폰번호, 생일, 성별 정보가  회원가입 항목으로 사용 중이면 필수값 여부와 상관없이 필수항목입니다.
                        </p>
                    </td>
                </tr>
                <tr id="subTr2">
                    <th>회원 정보 수정 시,<br />휴대폰 본인확인 정보 사용</th>
                    <td class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" id="useDataModifyFlKcpY" name="useDataModifyFlKcp" value="y" <?php echo gd_isset($checked['useDataModifyFlKcp']['y']); ?> />
                            사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" id="useDataModifyFlKcpN" name="useDataModifyFlKcp" value="n" <?php echo gd_isset($checked['useDataModifyFlKcp']['n']); ?> />
                            사용안함
                        </label>
                        <p class="notice-info">
                            "사용함" 설정 시, 이름, 휴대폰번호 등은 휴대폰 본인인증을 통해서만 회원정보 수정이 가능합니다.
                        </p>
                        <p class="notice-info">
                            "사용함" 설정 시, 주문서 작성 페이지에서의 "회원정보 반영" 기능은 사용하실 수 없습니다.
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <div role="tabpanel" class="tab-pane in <?php if($useTab =='dream') { ?>active<?php } ?>" id="dreamTag">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>사용 설정</th>
                    <td class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" id="useFlY" name="useFl" value="y" <?php echo gd_isset($checked['useFl']['y']); ?> />
                            사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" id="useFlN" name="useFl" value="n" <?php echo gd_isset($checked['useFl']['n']); ?> />
                            사용안함
                        </label>
                        <p class="notice-info">
                            서비스 신청 전인 경우 먼저 서비스를 신청하세요.
                            <a href="/service/service_info.php?menu=member_auth_info" target="_blank" class="btn-link">서비스 자세히보기 ></a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>회원사 CODE</th>
                    <td class="form-inline">
                        <input type="text" name="cpCode" value="<?php echo gd_isset($data['cpCode']); ?>" class="form-control"/>
                        <p class="notice-info">드림시큐리티에서 상점별로 발급되는 아이디 입니다. (<strong>&quot;<?php echo implode(',', (array) $dreamsecurityPrefix); ?>&quot;</strong>로 시작되어야 함)
                        </p>
                    </td>
                </tr>
                <tr id="subTr3">
                    <th>회원 가입 시,<br />휴대폰 본인확인 정보 사용</th>
                    <td class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" id="useDataJoinFlY" name="useDataJoinFl" value="y" <?php echo gd_isset($checked['useDataJoinFl']['y']); ?> />
                            사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" id="useDataJoinFlN" name="useDataJoinFl" value="n" <?php echo gd_isset($checked['useDataJoinFl']['n']); ?> />
                            사용안함
                        </label>
                        <p class="notice-info">
                            휴대폰 본인인증 정보를 회원 정보로 사용하며, 해당 정보(이름, 휴대폰번호, 생일, 성별)는 고객이 수정할 수 없도록 설정합니다.
                        </p>
                        <p class="notice-info">
                            "사용함" 설정 시, 이름, 휴대폰번호, 생일, 성별 정보가  회원가입 항목으로 사용 중이면 필수값 여부와 상관없이 필수항목입니다.
                        </p>
                    </td>
                </tr>
                <tr id="subTr4">
                    <th>회원 정보 수정 시,<br />휴대폰 본인확인 정보 사용</th>
                    <td class="form-inline">
                        <label class="radio-inline">
                            <input type="radio" id="useDataModifyFlY" name="useDataModifyFl" value="y" <?php echo gd_isset($checked['useDataModifyFl']['y']); ?> />
                            사용함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" id="useDataModifyFlN" name="useDataModifyFl" value="n" <?php echo gd_isset($checked['useDataModifyFl']['n']); ?> />
                            사용안함
                        </label>
                        <p class="notice-info">
                            "사용함" 설정 시, 이름, 휴대폰번호 등은 휴대폰 본인인증을 통해서만 회원정보 수정이 가능합니다.
                        </p>
                        <p class="notice-info">
                            "사용함" 설정 시, 주문서 작성 페이지에서의 "회원정보 반영" 기능은 사용하실 수 없습니다.
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $(':radio[name="useFlKcp"]').change(function(e) {
            if ($(this).val() == 'y') {
                if ($('#serviceId').val() == '') {
                    alert('NHN KCP 휴대폰인증을 먼저 신청해주셔야 사용이 가능합니다.');
                    $('#useFlKcpN').prop('checked', true);
                    checkUse();
                } else if ($(':radio[name="useFl"]:checked').val() == 'y') {
                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_DANGER,
                        title: 'NHN KCP 휴대폰인증 설정',
                        message: '현재 드림시큐리티에 ‘사용함’으로 설정되어있습니다. 계속하시겠습니까?',
                        closable: false,
                        callback: function(confirm) {
                            if (confirm) {
                                $('#useFlN').prop('checked', true);
                            } else {
                                $('#useFlKcpN').prop('checked', true);
                            }
                            checkUse();
                        }
                    });
                } else {
                    checkUse();
                }
            } else {
                checkUse();
            }
        });

        $(':radio[name="useFl"]').change(function(e) {
            if ($(this).val() == 'y') {
                if ($('#cpCode').val() == '') {
                    alert('드림시큐리티 휴대폰인증 회원사 코드를 신청해 입력해주셔야 사용이 가능합니다.');
                    $('#useFlN').prop('checked', true);
                    $('#cpCode').focus();

                    checkUse();
                } else if ($(':radio[name="useFlKcp"]:checked').val() == 'y') {
                    BootstrapDialog.confirm({
                        type: BootstrapDialog.TYPE_DANGER,
                        title: '드림시큐리티 휴대폰인증 설정',
                        message: '현재 NHN KCP에 ‘사용함’으로 설정되어있습니다. 계속하시겠습니까?',
                        closable: false,
                        callback: function(confirm) {
                            if (confirm) {
                                $('#useFlKcpN').prop('checked', true);
                            } else {
                                $('#useFlN').prop('checked', true);
                            }
                            checkUse();
                        }
                    });
                } else {
                    checkUse();
                }
            } else {
                checkUse();
            }
        });

        $('#frmSetup').validate({
            submitHandler: function (form) {
                var params = $(form).serializeArray();
                ajax_with_layer('./member_ps.php', params, function (params, textStatus, jqXHR) {
                layer_close();
                dialog_alert(params, '확인', {isReload: true});
                });
            }
        });

        function checkUse() {
            if ($(':radio[name="useFlKcp"]:checked').val() == 'y') {
                $('#subTr1').show();
                $('#subTr2').show();
                $('#subTr3').hide();
                $('#subTr4').hide();
            } else {
                $('#subTr1').hide();
                $('#subTr2').hide();
            }
            if ($(':radio[name="useFl"]:checked').val() == 'y') {
                $('#subTr1').hide();
                $('#subTr2').hide();
                $('#subTr3').show();
                $('#subTr4').show();
            } else {
                $('#subTr3').hide();
                $('#subTr4').hide();
            }
        }

        checkUse();
    });
    //-->
</script>
