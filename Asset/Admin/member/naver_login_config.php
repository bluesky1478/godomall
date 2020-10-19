<form id="form" name="form" action="naver_login_request_ps.php" method="post" enctype="multipart/form-data" target="naver_login">
    <input type="hidden" name="servicemURL" value="<?= $data['servicemURL']; ?>"/>
    <input type="hidden" name="clientName" value="<?= $data['clientName']; ?>"/>
    <input type="hidden" name="firstCheck" value="<?=$data['useFl']?>" />
    <input type="hidden" name="imageURL" value="<?=$data['imageURL']?>" />
    <input type="hidden" name="categoryCode" value="<?=$data['categoryCode']?>" />
    <input type="hidden" name="mode" value="adminAuthorize" />
    <input type="hidden" name="token" id="token" value="" />
    <input type="hidden" name="confirmyn" id="confirmyn" value="n" />
    <input type="hidden" name="parentForm" id="parentForm" value="form" />
    <input type="hidden" name="serviceNameOrg" id="serviceNameOrg" value="<?= $data['serviceName']; ?>" />
    <input type="hidden" name="serviceURLOrg" id="serviceURLOrg" value="<?= $data['serviceURL']; ?>" />
    <input type="hidden" name="serviceCategoryOrg" id="serviceCategoryOrg" value="<?= $data['categoryCode']; ?>" />
    <input type="hidden" name="serviceImageOrg" id="serviceImageOrg" value="<?= $imageURL; ?>" />

    <input type="hidden" name="simpleLoginFlOrg" id="simpleLoginFlOrg" value="<?= $data['simpleLoginFl']; ?>" />
    <input type="hidden" name="baseInfoOrg" id="baseInfoOrg" value="<?= $data['baseInfo']; ?>" />
    <input type="hidden" name="additionalInfoOrg" id="additionalInfoOrg" value="<?= $data['additionalInfo']; ?>" />
    <input type="hidden" name="supplementInfoOrg" id="supplementInfoOrg" value="<?= $data['supplementInfo']; ?>" />

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="button" value="저장" id="btnConfirm" class="btn btn-red"/>
    </div>

    <div class="table-title">
        네이버 아이디 로그인 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>사용여부</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="useFl" id="useFlY" value="y" <?= $checked['useFl']['y']; ?> <?=$data['useFl'] == 'f' ? 'disabled="disabled"' : '' ?>>
                    사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useFl" id="useFlN" value="n" <?= $checked['useFl']['n']; ?>>
                    사용안함
                </label>
                <?php if($data['useFl'] != 'y') { ?>
                    <div class="notice-info">[<a href="#" class="btn-link regist-btn">네이버 아이디 로그인 사용 신청</a>]을 통해 신청을 하셔야 네이버 아이디 로그인 사용을 하실 수 있습니다.</div>
                <?php } ?>
                <div class="notice-info">사용함으로 선택 시 쇼핑몰에 네이버 아이디 로그인 영역이 노출되지 않으면 스킨패치를 진행하시기 바랍니다.</div>
            </td>
        </tr>
        <tr>
            <th class="require">Client ID</th>
            <td>
                <label>
                    <input type="text" name="clientId" id="clientId" value="<?= $data['clientId']; ?>" class="form-control width-2xl useFl" readonly="readonly"/>
                </label>
                <?php if($data['useFl'] == 'f') { ?>
                    <button type="button" class="btn btn-gray btn-sm js-naver-login-request" version="<?= $data['useFl'] == 'f' ? 'first' : 'second' ?>"> 네이버 아이디 로그인 사용 신청</button>
                <?php } else { ?>
                    <span class="notice-info mgl5">재신청 및 Client ID는 변경할 수 없습니다.</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th class="require">Client 시크릿코드</th>
            <td>
                <label>
                    <input type="text" name="clientSecret" id="clientSecret" value="<?= $data['clientSecret']; ?>" class="form-control width-2xl useFl" readonly="readonly"/>
                </label>
            </td>
        </tr>
        <?php if (empty($data['clientId']) === false) { ?>
            <tr class="use-option">
                <th>네이버 아이디 로그인<br/>사용 신청정보</th>
                <td>
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-md"/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th class="require">쇼핑몰명</th>
                            <td>
                                <input type="text" name="serviceName" id="serviceName" placeholder="예) 고도몰" value="<?= $data['serviceName']; ?>" class="form-control width-lg" maxlength="40" flag="origin" />
                                <div class="check-msg-origin c-gdred display-none">40자 이내의 영문, 한글, 숫자, 공백문자, "-", "_"만 입력 가능합니다.</div>
                                <div class="notice-info">네이버 아이디로 로그인할 때 사용자에게 표시되는 이름입니다.</div>
                                <div class="notice-info">40자 이내의 영문, 한글, 숫자, 공백문자, "-", "_"만 입력 가능합니다.</div>
                            </td>
                        </tr>
                        <tr>
                            <th class="require">대표 URL 입력</th>
                            <td>
                                <input type="text" name="serviceURL" id="serviceURL" value="<?= $data['serviceURL']; ?>" placeholder="예) www.godo.co.kr" class="form-control width-lg" />
                                <div class="notice-info">한글 도메인의 경우 퓨니코드로 전환 후 URL을 입력해주시거나, 앱 등록 후 네이버 아이디 로그인 애플리케이션 설정에서 대표 URL 정보를 직접 수정해 주셔야 사용이 가능합니다.</div>
                            </td>
                        </tr>
                        <tr>
                            <th>카테고리</th>
                            <td>
                                <select name="serviceCategory" class="form-control">
                                    <option value="">= 카테고리 선택 =</option>
                                    <?php foreach($category as $key => $val) { ?>
                                        <option value="<?=$key?>" <?=$key == $data['categoryCode'] ? 'selected' : '' ?>><?=$val?></option>
                                    <?php } ?>
                                </select>
                                <span class="notice-info">네이버 아이디 로그인 애플리케이션 설정에 등록될 정보입니다.</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="require">로고이미지</th>
                            <td>
                                <div class="form-inline pdb10">
                                    <input type="file" name="serviceImage" class="form-control file" />
                                </div>
                                <?= $imageURLPrint ?>
                                <div class="notice-info mgt5">네이버 아이디 로그인 연동 과정에서 사용자에게 노출되는 이미지입니다.</div>
                                <div class="notice-info mgt5">권장 크기는 140X140 사이즈이며 jpg, png, 또는 gif만 등록 가능합니다.</div>
                                <div class="notice-info mgt5">500KB 이하의 파일을 업로드 해주세요.</div>
                            </td>
                        </tr>
                    </table>

                    <div class="notice-info">네이버 아이디 로그인 사용 신청정보 수정은 네이버 Developers 의 내 애플리케이션 > API 설정에서도 가능합니다.</div>
                    <div class="notice-info">네이버 Developers에서 수정된 정보는 솔루션의 신청정보에는 업데이트되지 않습니다.</div>
                </td>
            </tr>
        <?php } ?>
        <tr class="joinConfig">
            <th>회원가입 설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="simpleLoginFl" id="simpleLoginFlN" value="y" <?= $checked['simpleLoginFl']['y']; ?>>
                    간편 회원가입
                </label>
                <label class="radio-inline">
                    <input type="radio" name="simpleLoginFl" id="simpleLoginFlY" value="n" <?= $checked['simpleLoginFl']['n']; ?>>
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
                <table class="table table-cols">
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
                        <td>선택 :  <?php
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
                                if($i == $size) {
                                    echo $val['name'];
                                } else {
                                    echo $val['name']. ", ";
                                }
                                $i++;
                            } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>선택 :  <?php
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
<style type="text/css">
    img.service-image {max-width:140px;max-height:140px;}
    img.noimage {width:140px; height:140px;border:1px solid #D5D5D5;}
</style>
<script type="text/javascript">
    var target = 'form';
    var sizeFl = true;
    var pattern = /[^ㄱ-ㅎㅏ-ㅣ가-힣a-zA-Z0-9\s\-\_]/gi;
    $(function () {
        $("img").error(function(){
            $(this).attr("src", "<?= PATH_ADMIN_GD_SHARE ?>img/skin_noimg.jpg").addClass("noimage");
        });

        $(".regist-btn").click(function(e) {
            e.preventDefault();
            $('.js-naver-login-request').trigger("click");
        });

        $('.js-naver-login-request').click(function (e) {
            e.preventDefault();
            var firstCheck = $("input[name=firstCheck]").val();

            if (firstCheck == "f" || $(':radio[name="useFl"]:checked').val() == 'y') {
                var loadChk = 0;
                $.ajax({
                    url: '../member/layer_naver_login_request.php',
                    type: 'get',
                    async: false,
                    success: function (data) {
                        if (loadChk == 0) {
                            data = '<div id="layerNaverLogin">' + data + '</div>';
                        }
                        BootstrapDialog.show({
                            title: '네이버 아이디 로그인 사용신청',
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

        $(document).on('keydown', 'input[name="serviceName"]', function(e) {
            if (e.key.search(pattern) > -1) {
                e.preventDefault();
            }
            if(e.keyCode == 25) {
                e.preventDefault();
            }
            if ($(this).val().length > $(this).attr('maxlength')) {
                alert("최대 " + $(this).attr('maxlength') + "자까지만 입력이 가능합니다.");
                $(this).val($(this).val().substr(0, $(this).attr('maxlength')));
            }
        }).on('paste', 'input[name="serviceName"]', function(e) {
            var thisValue = $(this);
            setTimeout(function() {
                var pasteValue = thisValue.val();
                if (pattern.test(pasteValue)) {
                    pasteValue = pasteValue.replace(/[^ㄱ-ㅎㅏ-ㅣ가-힣a-zA-Z0-9\s\-\_]/g, "");
                    $('.check-msg-' + thisValue.attr("flag")).addClass('display-none');
                }
                $(thisValue).val(pasteValue);
            }, 100);
        }).on('input propertychange', 'input[name="serviceName"]', function(e) {
            var value = e.target.value;
            var checkMsg = '.check-msg-' + $(this).attr("flag");
            if (value.search(pattern) > -1) {
                $(checkMsg).removeClass('display-none');
            } else {
                $(checkMsg).addClass('display-none');
            }
            if ($(this).val().length > $(this).attr('maxlength')) {
                alert("최대 " + $(this).attr('maxlength') + "자까지만 입력이 가능합니다.");
                $(this).val($(this).val().substr(0, $(this).attr('maxlength')));
            }
        });

        $(document).on('change blur', 'input[name="serviceImage"]', function () {
            var file = $(this).prop('files')[0];

            if (file) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    if (e.loaded > 500 * 1024) {
                        sizeFl = false;
                    } else {
                        sizeFl = true;
                    }
                }
                reader.readAsDataURL(file);
            }
        });
        $(document).on('click', '#btnConfirm', function () {
            target = 'form';

            if(checkSameData()) {
                $('input[name="mode"]').val('useChange');
                $(form).attr("target", "_self").submit();
            } else {
                dataSubmit(target, sizeFl);
            }
        });
        $(document).on('click', '#layerBtnConfirm', function () {
            target = 'layerForm';
            dataSubmit(target, sizeFl);
        });
    });

    function checkSameData() {
        var chkArr = ['serviceName', 'serviceURL', 'serviceCategory', 'serviceImage', 'simpleLoginFl', 'baseInfo', 'additionalInfo','supplementInfo'];
        var returnValue = true;

        $.each(chkArr, function(i, val) {
            var originData = $("input[name="+val+"Org]").val();
            var newData = $("input[name="+val+"]").val();
            if(val == 'serviceCategory') {
                newData = $("select[name="+val+"]").val();
            } else if(val == 'serviceImage') {
                originData = "";
            } else if(val == 'simpleLoginFl') {
                newData = $("input:radio[name="+val+"]:checked").val()
            }
            if(originData != newData) {
                returnValue = false;
            }
        });
        return returnValue;
    }

    function checkText(events, thisValue) {
        var pattern = /[^가-힣ㄱ-ㅎㅏ-ㅣa-zA-Z0-9\s\-\_]/gi;
        var returnValue = thisValue;
        if (pattern.test(events.key)) {
            events.preventDefault();
        }
        if (returnValue.val().length > returnValue.attr('maxlength')) {
            alert("최대 " + returnValue.attr('maxlength') + "자까지만 입력이 가능합니다.");
            returnValue = returnValue.val().substr(0, returnValue.attr('maxlength'));
        } else {
            returnValue = returnValue.val();
        }
        return returnValue;
    }

    function dataSubmit(target, sizeFl) {
        var extFl = ['jpg', 'png', 'gif'];
        var exceptDomain = ['http', 'https', 'http://', 'https://'];

        if (target == 'form' && !$('input[name="clientId"]').val()) {
            return false;
        }

        if($('#' + target + ' input[name="serviceName"]').val().search(pattern) > -1) {
            alert('40자 이내의 영문, 한글, 숫자, 공백문자, "-", "_"만 입력 가능합니다.');
            return false;
        }
        if($('#' + target + ' input[name="serviceName"]').val() == "") {
            alert('쇼핑몰명을 입력해주세요.');
            return false;
        }
        if($('#' + target + ' input[name="serviceURL"]').val() == "" || $.inArray($('#' + target + ' input[name="serviceURL"]').val(), exceptDomain) >= 0) {
            alert('대표 URL을 입력해주세요.');
            return false;
        }
        if($("input[name=imageURL]").val() == "" && $("input[name='serviceImage']").val() == "") {
            alert('로고이미지를 업로드해주세요.');
            return false;
        }

        // 토큰 값 받아오기 전
        if ($('#' + target + ' input[name="confirmyn"]').val() == 'n') {
            var confirmMsg = '네이버 아이디 로그인 사용 정보를 업데이트 하시겠습니까?';
            if($("input[name='firstCheck']").val() == 'f') {
                confirmMsg = '네이버 아이디 로그인 사용 신청을 하시겠습니까?';
            }
            if (confirm(confirmMsg)) {
                if ($('#' + target + ' input[name="token"]').val()) {
                } else {
                    if ($('#' + target + ' input[name="serviceImage"]').val()) {
                        var ext = $('#' + target + ' input[name="serviceImage"]').val().split('.');
                        var extension = ext.pop().toLowerCase();
                        if (sizeFl == false) {
                            alert('500kb이하의 로고 이미지만 등록이 가능합니다.');
                            $('#' + target + ' input[name="confirmyn"]').val('n');
                            return false;
                        } else if($.inArray(extension, extFl) < 0) {
                            alert('로고 이미지는 jpg, png, gif 형식의 파일만 사용하실 수 있습니다.');
                            $('#' + target + ' input[name="confirmyn"]').val('n');
                            return false;
                        }
                    }
                    window.open('', 'naver_login', 'width=500,height=665');
                    document.getElementById(target).submit();
                }
            }
        }
    }

    function useLoginFl() { //사용여부(useFl)에 따른 회원가입설정, 회원가입 항목 설정 테이블 출력
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
    function useSimpleJoin() { // 간편 회원가입 or 일반 회원가입 선택시 회원가입항목 출력
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
        if ($('input:checkbox[name="supplementInfo"]').val() == 'y') {
            $('#supplementInfo').prop('checked', true);
        } else if ($('input:checkbox[name="supplementInfo"]').val() == 'n') {
            $('#supplementInfo').prop('checked', false);
        }
    }

        $(document).ready(function () {
            useLoginFl();
            useSimpleJoin();
            useAdditionInfo();
            useSupplementInfo();
            $('input:radio[name="useFl"]').click(function () {
                useLoginFl();
            });
            $('input:radio[name="simpleLoginFl"]').click(function () {
                useSimpleJoin();
            });
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

</script>
