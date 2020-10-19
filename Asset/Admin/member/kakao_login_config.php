<form id="form" name="form" action="kakao_login_config_ps.php" method="post">
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title">
        카카오 아이디 로그인 설정
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
            <th class="require">REST API 키</th>
            <td>
                <label>
                    <input type="text" name="restApiKey" id="restApiKey" value="<?= $data['restApiKey']; ?>" class="form-control width-2xl useFl" readonly="readonly"/>
                </label>
            </td>
        </tr>
        <tr>
            <th class="require">ADMIN 키</th>
            <td>
                <label>
                    <input type="text" name="adminKey" id="adminKey" value="<?= $data['adminKey']; ?>" class="form-control width-2xl useFl" readonly="readonly"/>
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
                            <th rowspan="2">
                                <label class="checkbox-inline">
                                    <input type="checkbox" id="businessInfo" name="businessInfo" value="<?=gd_isset($data['businessInfo']); ?>" >
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
        </tbody>
    </table>
    <div class="notice-info">
    <a href="https://developers.kakao.com/docs/latest/ko/getting-started/app" target="_blank" class="notice-ref btn-link">[REST API 키, ADMIN 키 발급방법]</a>
    </div>
    <div class="notice-info">
        카카오 개발자 센터 > 내 어플리케이션 > 제품설정 > 카카오 로그인 에서 “활성화 상태 : ON“ 으로 변경 후 "로그인 Redirect URI" 항목에 반드시 다음 URL (/member/kakao/kakao_login.php) 을 추가하셔야만 정상적으로 카카오 아이디 로그인 사용 가능합니다.</br>
        자세한 사항은 해당 <a href="https://plus.godo.co.kr/goods/view.gd?idx=33" target="_blank" class="notice-ref btn-link">플러스샵앱의 매뉴얼</a>을 참조하시기 바랍니다.
    </div>
</form>

<script type="text/javascript">
    <!--
    function useLoginFl() {
        if($('input:radio[name="useFl"]:checked').val()=='y'){
            $('#restApiKey').prop("readonly", false);
            $('#adminKey').prop("readonly", false);
            if($('input:radio[name="simpleLoginFl"]:checked').val() == 'y') {
                $('.joinPolicy').hide();
            } else if ($('input:radio[name="simpleLoginFl"]:checked').val() == 'n') {
                $('.joinPolicy').show(); // 회원가입 항목 설정
            }
            $('.joinConfig').show();
        }else if($('input:radio[name="useFl"]:checked').val()=='n'){
            $('#restApiKey').prop("readonly", true);
            $('#adminKey').prop("readonly", true);
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
    function useBusinessInfo() { //사업자정보 사용
        if ($('input:checkbox[name="businessInfo"]').val() == 'y') {
            $('#businessInfo').prop('checked', true);
        } else if ($('input:checkbox[name="businessInfo"]').val() == 'n') {
            $('#businessInfo').prop('checked', false);
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
        useBusinessInfo();
        $("#form").validate({
            dialog: false,
            ignore: '.ignore',
            rules: {
                restApiKey: {
                    required: function () {
                        return $(':radio[name="useFl"]:checked').val() == 'y';
                    }
                },
                adminKey: {
                    required: function () {
                        return $(':radio[name="useFl"]:checked').val() == 'y';
                    }
                }
            },
            messages: {
                restApiKey: {
                    required: "REST API 키를 입력해주세요"
                },
                adminKey: {
                    required: "ADMIN 키를 입력해주세요"
                }
            },
            submitHandler: function (form) {
                checkUseFlSame();
                form.target = 'ifrmProcess';
                form.submit();
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
        $(document).on('click','input:checkbox[name="businessInfo"]', function (e) {
            if($(this).prop("checked")) {
                $(this).val('y');
            } else {
                $(this).val('n');
            }
        });
    });
    //-->
</script>

<div class="information">
    <h4>안내</h4>
    <div class="content">
        <div>
            <strong>카카오톡 간편로그인 설정 시 PC 보안서버 사용에 따른 안내</strong>
        </div>
        <div style="line-height: 20px;">
            ㆍ쇼핑몰 사용자가 카카오계정 관리 페이지 또는 카카오톡 고객센터를 통해
            &nbsp;&nbsp;쇼핑몰 연결 끊기를 진행하기 위해서는 쇼핑몰에 PC 보안서버가 설치돼 있어야 합니다.<br>
            &nbsp;&nbsp;쇼핑몰에 PC 보안서버 적용 후 Kakao Developers 페이지 내 사용자 관리에서 아래 주소를 입력해주세요.<br>
            &nbsp;&nbsp;고도몰 주소: <strong><?= $data['mallDomain'] ?></strong>
        </div>
        <div style="padding-top: 9px;">
            <strong>보안서버 설치 확인 및 사용 방법</strong>
        </div>
        <div style="line-height: 20px;">
            ㆍ기본설정 → 보안서버관리 → PC쇼핑몰 보안서버 관리에서 하실 수 있습니다.<br>
            &nbsp;&nbsp;<a href="../policy/ssl_pc_setting.php" target="_blank" class="notice-ref btn-link">보안서버 관리 바로가기</a>
        </div>
    </div>
</div>