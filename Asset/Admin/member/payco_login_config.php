<form id="form" name="form" action="payco_login_config_ps.php" method="post">
    <input type="hidden" name="serviceName" value="<?= $data['serviceName']; ?>"/>
    <input type="hidden" name="serviceURL" value="<?= $data['serviceURL']; ?>"/>
    <input type="hidden" name="consumerName" value="<?= $data['consumerName']; ?>"/>
    <input type="hidden" name="mallSno" value="<?= $mallSno ?>"/>
    <input type="hidden" name="useFlOrg" value="<?=$data['useFl'] ?>"/>
    <input type="hidden" name="checkUseFlSame" id="checkUseFlSame" value=""/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        페이코 아이디 로그인 설정
    </div>
    <div class="panel pd10">
        <p><b>페이코 아이디 로그인이란?</b></p>
        <p>
            600만 페이코 이용자를 내 쇼핑몰 회원으로!<br/> 회원가입, 로그인, 결제까지 쉽고 빠르게 이어지는 페이코 아이디 로그인 서비스를 사용해보세요.<br/> [페이코 로그인 사용 신청] 버튼 클릭 한 번으로 바로 쇼핑몰에서 사용할 수 있습니다.
        </p>
        <p> 페이코 아이디로 회원가입 시 페이코의 회원정보를 불러와 쉽고 빠르게 가입할 수 있습니다.<br/>모바일 쇼핑몰에서는 더욱 간편하게 로그인할 수 있습니다.<br/>쇼핑몰에는 가입한 회원의 회원정보가 보관되므로 다른 쇼핑몰 회원과 같이 개인정보활용동의에 따른 프로모션을 할 수 있습니다.
        </p>
        <p>페이코 아이디 로그인으로 더 빠르게, 더 많이 쇼핑몰 회원을 늘리세요.
            <a href="/service/service_info.php?menu=member_payco_info" class="btn btn-gray btn-sm">서비스 자세히 보기</a>
        </p>
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>사용설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="useFl" id="useFlY" value="y" <?= $checked['useFl']['y']; ?>>
                    사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useFl" id="useFlN" value="n" <?= $checked['useFl']['n']; ?>>
                    사용안함
                </label>
            </td>
        </tr>
        <tr>
            <th class="require">Client ID</th>
            <td>
                <label>
                    <input type="text" name="clientId" id="clientId" value="<?= $data['clientId']; ?>" class="form-control width-2xl useFl" disabled="disabled"/>
                </label>
                <button type="button" class="btn btn-gray btn-sm js-payco-login-request">페이코 로그인 사용 <?= $data['clientId'] == '' ? '신청' : '재신청' ?></button>
            </td>
        </tr>
        <tr>
            <th class="require">Client 시크릿코드</th>
            <td>
                <label>
                    <input type="text" name="clientSecret" id="clientSecret" value="<?= $data['clientSecret']; ?>" class="form-control width-2xl useFl" disabled="disabled"/>
                </label>
            </td>
        </tr>
        <tr>
            <th>페이코 로그인 사용<br/>신청정보</th>
            <td>
                <div class="pdb24">쇼핑몰 이름 : <?= $data['serviceName']; ?></div>
                <div class="pdb24">쇼핑몰 URL : <?= $data['serviceURL']; ?></div>
                <div class="pdb24">상호(회사)명 : <?= $data['consumerName']; ?></div>
                <div >신청된 정보가 다를 경우 <a href="#" class="js-payco-login-request notice-ref notice-sm btn-link">페이코 로그인 재신청</a>
                을 클릭하여 재신청 해주시기 바랍니다.</div>
            </td>
        </tr>
        <tr class="joinConfig">
            <th>회원가입 설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="simpleLoginFl" id="simpleLoginFlY" value="y" <?= $checked['simpleLoginFl']['y']; ?>>
                    간편 회원가입
                </label>
                <label class="radio-inline">
                    <input type="radio" name="simpleLoginFl" id="simpleLoginFlN" value="n" <?= $checked['simpleLoginFl']['n']; ?>>
                    일반 회원가입
                </label>
                <div class="notice-info simpleJoin">
                    간편 회원가입 사용 시 아이디와 이름 항목은 회원가입 시 필수로 노출되며<br>이메일, 휴대폰번호, 성별, 생일 항목은 회원>회원 관리>회원 가입 항목 관리의 사용/필수 설정에 따라 노출됩니다
                </div>
            </td>
        </tr>
        <tr class="joinPolicy">
            <th>회원가입 항목 설정</th>
            <td>
                <table  class="table table-cols">
                    <tbody>
                    <?php if ($mallCnt > 1) { ?>
                        <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
                            <?php foreach ($mallList as $key => $mall) { ?>
                                <li role="presentation" class="js-popover <?php echo $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?php echo $mall['mallName']; ?>" data-placement="top">
                                    <a href="./payco_login_config.php?mallSno=<?php echo $mall['sno']; ?>">
                                        <span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span>
                                        <span class="mall-name"><?php echo $mall['mallName']; ?></span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                        <tr>
                            <th rowspan="2" width="150px">
                                <label class="checkbox-inline">
                                    <input type="checkbox" id="baseInfo" name="baseInfo" value="y" checked="checked" disabled="disabled">
                                기본 정보
                                </label>
                            </th>
                            <td>필수 :
                                <?php
                                $size = sizeof($data['items']['baseInfo']['requireY'])-1;
                                $i = 0;
                                foreach ($data['items']['baseInfo']['requireY'] as $key => $val){
                                    if($i == $size){
                                        echo $val;
                                    }else {
                                        echo $val . ", ";
                                    }
                                    $i++;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>선택 :
                                <?php
                                $size = sizeof($data['items']['baseInfo']['requireN'])-1;
                                $i = 0;
                                foreach ($data['items']['baseInfo']['requireN'] as $key => $val){
                                    if($i == $size){
                                        echo $val;
                                    }else {
                                        echo $val . ", ";
                                    }
                                    $i++;
                                } ?>
                            </td>
                        </tr>
                    <?php if($data['items']['businessMember']['use']) {?>
                        <tr>
                            <th>
                                <label class="checkbox-inline">
                                    <input type="checkbox" id="businessInfo" name="businessInfo" value="" disabled="disabled">
                                사업자 정보
                                </label>
                            </th>
                            <td>사업자 회원 가입은 지원되지 않습니다.</td>
                        </tr>
                    <?php } ?>
                        <tr >
                            <th rowspan="2">
                                <label class="checkbox-inline">
                                    <input type="checkbox" id="supplementInfo" name="supplementInfo" value="<?=gd_isset($data['supplementInfo']); ?>">
                                부가정보
                                </label>
                            </th>
                            <td>필수 : <?php
                                $size = sizeof($data['items']['supplementInfo']['requireY'])-1;
                                $i = 0;
                                foreach ($data['items']['supplementInfo']['requireY'] as $key => $val){
                                    if($i == $size){
                                        echo $val;
                                    }else {
                                        echo $val . ", ";
                                    }
                                    $i++;
                                } ?> </td>
                        </tr>
                        <tr>
                            <td>선택 : <?php
                                $size = sizeof($data['items']['supplementInfo']['requireN'])-1;
                                $i = 0;
                                foreach ($data['items']['supplementInfo']['requireN'] as $key => $val){
                                    if($i == $size){
                                        echo $val;
                                    }else {
                                        echo $val . ", ";
                                    }
                                    $i++;
                                } ?> </td>
                        </tr>
                        <tr>
                            <th rowspan="2">
                                <label class="checkbox-inline">
                                    <input type="checkbox" id="additionalInfo" name="additionalInfo" value="<?=gd_isset($data['additionalInfo'],'y'); ?>">
                                추가 정보
                                </label>
                            </th>
                            <td>필수 : <?php
                                $size = sizeof($data['items']['additionInfo']['requireY'])-1;
                                $i = 0;
                                foreach ($data['items']['additionInfo']['requireY'] as $key => $val){
                                    if($i == $size){
                                        echo $val['name'];
                                    }else {
                                        echo $val['name']. ", ";
                                    }
                                    $i++;
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>선택 : <?php
                                $size = sizeof($data['items']['additionInfo']['requireN'])-1;
                                $i = 0;
                                foreach ($data['items']['additionInfo']['requireN'] as $key => $val){
                                    if($i == $size) {
                                        echo $val['name'];
                                    } else {
                                        echo $val['name']. ", ";
                                    }
                                    $i++;
                                } ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="notice-info">
                정보 별로 노출되는 항목은 <a href="../member/member_joinitem.php" target="_blank" class="notice-ref notice-sm btn-link">회원>회원 관리>회원 가입 항목 관리</a>의 사용/필수 설정에 따릅니다.
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>
<script type="text/javascript">
    <!--
    function useLoginFl() {
        if($('input:radio[name="useFl"]:checked').val()=='y'){
            if($('input:radio[name="simpleLoginFl"]:checked').val() == 'y') {
                $('.joinPolicy').hide();
            } else if ($('input:radio[name="simpleLoginFl"]:checked').val() == 'n') {
                $('.joinPolicy').show(); // 회원가입 항목 설정
            }
            $('.joinConfig').show();
        }else if($('input:radio[name="useFl"]:checked').val()=='n'){
            $('.joinPolicy').hide();
            $('.joinConfig').hide();
        }
    }
    function useSimpleJoin() {
        if($('input:radio[name="useFl"]:checked').val()=='y') {
            if ($('input:radio[name="simpleLoginFl"]:checked').val() == 'y') {
                $('.joinPolicy').hide();
                $('.simpleJoin').show();
            } else if ($('input:radio[name="simpleLoginFl"]:checked').val() == 'n') {
                $('.joinPolicy').show();
                $('.simpleJoin').hide();
            }
        }
    }
    function useAdditionInfo() { //추가정보 사용
        if($('input:checkbox[name="additionalInfo"]').val() == 'y') {
            $('#additionalInfo').prop('checked', true);
        } else if($('input:checkbox[name="additionalInfo"]').val() == 'n') {
            $('#additionalInfo').prop('checked', false);
        }
    }
    function useSupplementInfo() { //부가정보 사용
        if($('input:checkbox[name="supplementInfo"]').val() == 'y') {
            $('#supplementInfo').prop('checked', true);
        } else if($('input:checkbox[name="supplementInfo"]').val() == 'n'){
            $('#supplementInfo').prop('checked', false);
        }
    }

    function checkUseFlSame(){ // useFl 값 변경 체크
        var useFlOrg = $('input:hidden[name="useFlOrg"]').val();
        var useFlCurrent = $('input:radio[name="useFl"]:checked').val();

        if(useFlOrg == useFlCurrent){
            $('input[name="checkUseFlSame"]').val('true');
        }else {
            $('input[name="checkUseFlSame"]').val('false');
        }
    }
    $(document).ready(function () {
        useLoginFl();
        useSimpleJoin();
        useAdditionInfo();
        useSupplementInfo();
        $("#form").validate({
            dialog: false,
            ignore: '.ignore',
            rules: {
                clientId: {
                    required: function () {
                        return $(':radio[name="useFl"]:checked').val() == 'y';
                    }
                },
                clientSecret: {
                    required: function () {
                        return $(':radio[name="useFl"]:checked').val() == 'y';
                    }
                }
            },
            messages: {
                clientId: {
                    required: "Client ID를 입력해 주세요."
                },
                clientSecret: {
                    required: "Client 시크릿코드를 입력해주세요."
                }
            },
            submitHandler: function (form) {
                checkUseFlSame();
                form.target = 'ifrmProcess';
                form.submit();
            }
        });

        $('.js-payco-login-request').click(function (e) {
            e.preventDefault();
            if ($(':radio[name="useFl"]:checked').val() == 'y') {
                var loadChk = 0;
                $.ajax({
                    url: '../member/layer_payco_login_request.php',
                    type: 'get',
                    async: false,
                    success: function (data) {
                        if (loadChk == 0) {
                            data = '<div id="layerPaycoLogin">' + data + '</div>';
                        }
                        BootstrapDialog.show({
                            title: '페이코 아이디 로그인 사용신청',
                            size: BootstrapDialog.SIZE_WIDE,
                            message: $(data),
                            closable: true
                        });
                    }
                });
            } else {
                alert('사용설정을 사용함으로 선택해 주시기 바랍니다.');
            }
        });

        $(':radio[name="payUsableFl"]').change(function () {
            if ($(this).val() == 'y') {
                display_toggle('mileageBasicUse', 'show');
                $('.joinConfig').show();
                $('.joinPolicy').show();
            } else {
                display_toggle('mileageBasicUse', 'hide');
                $('.joinConfig').hide();
                $('.joinPolicy').hide();
            }
        });
        $('input:radio[name="simpleLoginFl"]').click(function (e) {
           useSimpleJoin();
        });
        $('input:radio[name="useFl"]').click(function (e) {
            useLoginFl();
        });

        var mallSno = $('input:hidden[name="mallSno"]').val();
        var url = document.URL;
        var start = url.indexOf('mallSno');
        var urlValue = url.substring(start+8,start+9);

        if(mallSno > 1) { // mallSno값이 넘어온 경우 해외몰탭 출력
            $('#simpleLoginFlN').attr("checked", true);
            $('.joinPolicy').show();
        } else if(urlValue == 1) { // 기준몰탭 클릭시 출력
            $('#simpleLoginFlN').attr("checked", true);
            $('.joinPolicy').show();
        }

        $(document).on('click','input:checkbox[name="supplementInfo"]', function (e) {
            if($(this).prop("checked")) {
                $(this).val('y');
            } else {
                $(this).val('n');
            }
        });
        $(document).on('click','input:checkbox[name="additionalInfo"]', function (e) {
            if($(this).prop("checked")) {
                $(this).val('y');
            } else {
                $(this).val('n');
            }
        });
    });
    //-->
</script>
