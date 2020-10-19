<!--@formatter:off-->
<form id="form" name="form" action="sns_login_config_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mallSno" value="<?=$mallSno ?>">
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>
    <div class="design-notice-box mgb10">
        ‘페이스북 로그인 사용설정'을 설정하여 사용하시는 도중에, <strong class="text-darkred">‘앱(APP) ID' 를 변경하시게 되면,</strong><br>
        <span class="text-darkblue">페이스북에서는 새로운 쇼핑몰로 인식하게 되어, <strong>기존 페이스북과 연동된 회원들의 연동이 해제</strong></span>되므로 변경 시 주의하여 주시기 바랍니다.<br>
        (<b>고객은 로그인이 불가하며, 다시 동일 계정으로 쇼핑몰에 재가입해야 하므로</b> 고객 클레임이 발생할 수 있습니다.)<br><br>
        또한, <strong class="text-darkred">"앱(APP) ID 변경 시", 기존 앱 ID 정보는 삭제되어 복구가 불가능</strong>합니다.
    </div>
    <div class="table-title">
        페이스북 로그인 사용 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>사용 여부</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" class="js-radio-sns-login-use" name="snsLoginUse[facebook]" id="snsLoginUseFacebook_Y" value="y" <?= $checked['snsLoginUse']['facebook']['y']; ?>>
                    사용함
                </label>
                <?php if ($godoAppIdFl) { ?>
                    <label class="checkbox-inline mgl5 mgr20">
                        <input type="checkbox" value="y" id="useGodoAppId" name="useGodoAppId" <?= $checked['useGodoAppId']['y']; ?>>
                        간편설정
                    </label>
                <?php } ?>
                <label class="radio-inline">
                    <input type="radio" class="js-radio-sns-login-use" name="snsLoginUse[facebook]" id="snsLoginUseFacebook_N" value="n" <?= $checked['snsLoginUse']['facebook']['n']; ?>>
                    사용안함
                </label>
                <div class="notice notice-info">사용함으로 선택 시 쇼핑몰에 페이스북 로그인 영역이 노출되지 않으면 스킨패치를 진행하시기 바랍니다.</div>
                <?php if ($godoAppIdFl) { ?>
                    <div class="notice notice-info">간편설정을 해지 후 개별 AppID를 사용할 경우 기존 페이스북 회원은 페이스북 로그인 사용을 할 수 없습니다.</div>
                <?php } ?>
            </td>
        </tr>
        <tr class="appFacebook">
            <th>App ID</th>
            <td>
                <label>
                    <input type="text" name="appId[facebook]" id="appIdFacebook" value="<?= $appId['facebook']; ?>" class="form-control width-2xl useFl" disabled="disabled"/>
                </label>
            </td>
        </tr>
        <tr class="appFacebook">
            <th>App Secret</th>
            <td>
                <label>
                    <input type="text" name="appSecret[facebook]" id="appSecretFacebook" value="<?= $appSecret['facebook']; ?>" class="form-control width-2xl useFl" disabled="disabled"/>
                </label>
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
                                    <a href="./sns_login_config.php?mallSno=<?php echo $mall['sno']; ?>">
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
                    <?php if($data['items']['businessMember']['use']) { ?>
                        <tr>
                            <th rowspan="2">
                                <label class="checkbox-inline">
                                    <input type="checkbox" id="businessInfo" name="businessInfo" value="<?=gd_isset($data['businessInfo']); ?>">
                                    사업자 정보
                                </label>
                            </th>
                            <td>필수 :
                                <?php
                                $size = sizeof($data['items']['businessInfo']['requireY'])-1;
                                $i = 0;
                                foreach ($data['items']['businessInfo']['requireY'] as $key => $val){
                                    if($i == $size){
                                        echo $val;
                                    }else {
                                        echo $val . ", ";
                                    }
                                    $i++;
                                } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>선택 :
                                <?php
                                $size = sizeof($data['items']['businessInfo']['requireN'])-1;
                                $i = 0;
                                foreach ($data['items']['businessInfo']['requireN'] as $key => $val){
                                    if($i == $size){
                                        echo $val;
                                    }else {
                                        echo $val . ", ";
                                    }
                                    $i++;
                                } ?>
                            </td>
                        </tr>
                    <tr >
                        <?php } ?>
                        <th rowspan="2">
                            <label class="checkbox-inline">
                                <input type="checkbox" id="supplementInfo" name="supplementInfo" value="<?=gd_isset($data['supplementInfo']); ?>" >
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
                                <input type="checkbox" id="additionalInfo" name="additionalInfo" value="<?=gd_isset($data['additionalInfo']); ?>">
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
        <tr>
            <th>리디렉션 URI 정보</th>
            <td>
                <div class="panel panel-default">
                <ul class="list-group" id="redirectionUrls">
                    <?php
                    foreach ($mallRedirectUrls as $mallRedirectUrl) {
                        echo "<li class='list-group-item pdt5 pdb5 js-clipboard' title='$mallRedirectUrl'>$mallRedirectUrl</li>";
                    }
                    ?>
                </ul>
                </div>
                <div class="notice-info">"리디렉션 URI에 Strict 모드" 사용 시 반드시 위 URI 정보 모두를 "유효한 OAuth 리디렉선 URI"에 추가하셔야 정상적으로 페이스북 아이디 로그인이 사용가능합니다.</div>
                <div class="notice-info">사용중인 솔루션이 2개 이상일 경우 각각의 페이스북 아이디 로그인 설정 페이지에서 확인할 수 있는 리디렉션 URI를 모두 추가하셔야 정상적으로 사용가능합니다.</div>
                <div class="notice-info">유료 보안서버를 사용하는 경우에만 리디렉션 URI가 https:// 로 출력됩니다.</div>
            </td>
        </tr>
        </tbody>
    </table>
</form>
<!--@formatter:on-->
<script type="text/javascript">
    <!--
    function useLoginFl() {
        if ($('input:radio[name="snsLoginUse[facebook]"]:checked').val() == 'y') {
            $('.joinConfig').show(); // 회원가입 설정
            if ($('input:radio[name="simpleLoginFl"]:checked').val() == 'y') {
                $('.joinPolicy').hide();
            } else if ($('input:radio[name="simpleLoginFl"]:checked').val() == 'n') {
                $('.joinPolicy').show(); // 회원가입 항목 설정
            }
        } else if ($('input:radio[name="snsLoginUse[facebook]"]:checked').val() == 'n') {
            $('.joinPolicy').hide();
            $('.joinConfig').hide();
        }
    }

    function useSimpleJoin() {
        if ($('input:radio[name="snsLoginUse[facebook]"]:checked').val() == 'y') {
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
        if ($('input:checkbox[name="additionalInfo"]').val() == 'y') {
            $('#additionalInfo').prop('checked', true);
        } else if ($('input:checkbox[name="additionalInfo"]').val() == 'n') {
            $('#additionalInfo').prop('checked', false);
        }
    }

    function useSupplementInfo() { //부가정보 사용
        if ($('input:checkbox[name="supplementInfo"]').val() == 'y') {
            $('#supplementInfo').prop('checked', true);
        } else if ($('input:checkbox[name="supplementInfo"]').val() == 'n') {
            $('#supplementInfo').prop('checked', false);
        }
    }

    function useBusinessInfo() { //사업자정보 사용
        if ($('input:checkbox[name="businessInfo"]').val() == 'y') {
            $('#businessInfo').prop('checked', true);
        } else if ($('input:checkbox[name="businessInfo"]').val() == 'n') {
            $('#businessInfo').prop('checked', false);
        }
    }

    $(document).ready(function () {
        new ClipboardJS('li', {
            text: function (trigger) {
                return trigger.textContent;
            },
            container: document.getElementById('redirectionUrls')
        });

        var use_godo_app_id = $('#useGodoAppId');
        var sns_login_use = $('.js-radio-sns-login-use');
        sns_login_use.change(function () { //페북 로그인 사용함, 사용안함
            var $table = $(this).closest('table.table');
            if (this.value == 'y' && this.checked) {
                $table.find('input:text:lt(2)').prop('disabled', false);
                $table.find('tr:gt(0)').removeClass('display-none');
                if (use_godo_app_id.length > 0) {
                    use_godo_app_id.prop('disabled', false);
                }
            } else {
                $table.find('input:text:lt(2)').prop('disabled', true);
                if (use_godo_app_id.length > 0) {
                    use_godo_app_id.prop('checked', false);
                    use_godo_app_id.prop('disabled', true);
                }
            }
        }).filter(':checked').trigger('change');

        use_godo_app_id.change(function () { // 간편설정 체크박스
            var $table = $('.appFacebook');
            $table.prop('disabled', this.checked);
            if (this.checked) {
                $table.addClass('display-none');
            } else {
                $table.removeClass('display-none');
            }
        }).trigger('change');

        useLoginFl();
        useSimpleJoin();
        useAdditionInfo();
        useSupplementInfo();
        useBusinessInfo();

        $('input:radio[name="snsLoginUse[facebook]"]').click(function (e) {
            useLoginFl();
        });
        $('input:radio[name="simpleLoginFl"]').click(function (e) {
            useSimpleJoin();
        });
        var mallSno = $('input:hidden[name="mallSno"]').val();
        var url = document.URL;
        var start = url.indexOf('mallSno');
        var urlValue = url.substring(start + 8, start + 9);

        if (mallSno > 1) { // mallSno값이 넘어온 경우 해외몰탭 출력
            $('#simpleLoginFlN').attr("checked", true);
            $('.joinPolicy').show();
        } else if (urlValue === 1) { // 기준몰탭 클릭시 출력
            $('#simpleLoginFlN').attr("checked", true);
            $('.joinPolicy').show();
        }
        $(document).on('click', 'input:checkbox[name="businessInfo"]', function (e) {
            if ($(this).prop("checked")) {
                $(this).val('y');
            } else {
                $(this).val('n');
            }
        });
        $(document).on('click', 'input:checkbox[name="supplementInfo"]', function (e) {
            if ($(this).prop("checked")) {
                $(this).val('y');
            } else {
                $(this).val('n');
            }
        });
        $(document).on('click', 'input:checkbox[name="additionalInfo"]', function (e) {
            if ($(this).prop("checked")) {
                $(this).val('y');
            } else {
                $(this).val('n');
            }
        });
    });
    //-->
</script>
