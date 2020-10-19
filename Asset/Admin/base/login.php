<!-- //@formatter:off -->
<form id="frmLogin" name="frmLogin" action="login_ps.php" method="post">
    <input type="hidden" name="mode" value="login"/>
    <input type="hidden" name="returnUrl" value="<?=$returnUrl?>"/>
    <table class="login-table">
        <tr>
            <td>
                <div class="login-layout">
                    <h1><img src="<?= PATH_ADMIN_GD_SHARE ?>img/logo_main.png"></h1>
                    <div class="login-form">
                        <div class="login-input">
                            <div>
                                <input type="text" id="login" name="managerId" value="<?php echo $saveManagerId;?>" placeholder="쇼핑몰 관리 아이디" class="form-control input-lg"/>
                            </div>
                            <div>
                                <input type="password" name="managerPw" value="" placeholder="쇼핑몰 관리 비밀번호" class="form-control input-lg"/>
                            </div>
                        </div>
                        <div class="login-btn">
                            <input type="submit" value="로그인" class="btn btn-black"/>
                        </div>
                    </div>

                    <div class="login-bottom">
                        <label class="checkbox-inline checkbox-lg">
                            <input type="checkbox" name="saveId" value="y" <?php if (empty($saveManagerId) === false) { echo 'checked="checked"'; }?>> 아이디 저장
                        </label>
                        <div class="pull-right">
                            <a href="../base/find_id.php" class="btn">대표운영자 아이디</a>
                            /
                            <a href="../base/find_password.php" class="btn">비밀번호 찾기</a>
                        </div>
                    </div>

                    <div id="panel_banner_loginPanel"></div>

                    <div class="copyright">
                        &copy; NHN <a href="https://www.godo.co.kr/" target="_blank">godo<span>:</span></a> Corp All Rights Reserved.
                    </div>

                    <div id="layer" style="display:none;">
                        <div>
                            <h2>아이디/비밀번호 찾기</h2>
                            <p>1. 아이디 분실 시<br/><em>세팅메일에서 아이디를 확인할 수 있습니다. <a href="https://www.godo.co.kr/customer/guide/account-find.gd" target="_blank">[자세히 보기]</a></em></p>
                            <ol>
                                <li>① 고도회원 로그인</li>
                                <li>② 마이고도 &gt; 쇼핑몰관리 &gt; 쇼핑몰 목록 페이지로 이동</li>
                                <li>③ "서비스 관리" 항목의 [관리] 버튼 클릭</li>
                                <li>④ "세팅메일 받기" 항목의 [메일보내기] 버튼 클릭</li>
                            </ol>
                            <p>2. 비밀번호 분실 시<br/><em>1:1문의로 ‘비밀번호 재설정’ 요청 주시기 바랍니다. <a href="https://www.godo.co.kr/customer/guide/account-find.gd" target="_blank">[자세히 보기]</a></em></p>
                            <ul>
                                <li>※ 최초 신청 시 입력한 관리자 아이디의 비밀번호만 변경 가능</li>
                                <li>※ 재설정 시 영문, 숫자, 특문 중 2개 이상 조합하여 10~16자 구성 필수</li>
                            </ul>
                            <ol>
                                <li>① 마이고도 > 1:1문의/답변 페이지로 이동</li>
                                <li>② "문의분류" 항목의 [회원정보관리] - [ID,PW찾기] 선택</li>
                                <li>③ "문의유형" 항목의 [기술적인 도움] 선택</li>
                                <li>④ 하단 "추가정보" 입력창에 재설정 계정 입력</li>
                            </ol>
                            <div>
                                <a href="https://www.godo.co.kr/mygodo/myGodo_shopMain.php" target="_blank">마이고도 바로가기</a> <a href="https://www.godo.co.kr/mygodo/helper_write.html" target="_blank">1:1문의 바로가기</a>
                            </div>

                            <button type="button" title="닫기" id="layerClose">닫기</button>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</form>

<!-- 고도 사이트로 이동 시작 -->
<form name="frmGotoGodomall" action="" method="post" style="height: 0;">
    <input type="hidden" name="pageKey" value="<?php echo Globals::get('gLicense.godosno'); ?>">
    <input type="hidden" name="sno" value="<?php echo Globals::get('gLicense.godosno'); ?>">
    <input type="hidden" name="mode" value="">
</form>
<!-- 고도 사이트로 이동 끝 -->
<!-- //@formatter:on -->

<script type="text/javascript">
    <!--
    var login = {dialog: {}};
    $(document).ready(function () {
        $('#frmLogin').validate({
            submitHandler: function (form) {
                $(form).find("input[name=mode]").val("login");
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                managerId: 'required',
                managerPw: 'required'
            },
            messages: {
                managerId: '쇼핑몰 관리 아이디를 입력하세요.',
                managerPw: '쇼핑몰 관리 비밀번호를 입력하세요.'
            }
        });

        $("#login").focus();

        $('.btn-icon-passwd').click(function () {
            $('#layer').show();
        });

        $('#layerClose').click(function () {
            $('#layer').hide();
        });

    });
    function open_sms_auth() {
        var $frmLogin = $('#frmLogin'), $login = $('#login');
        var params = {
            mode: 'initLoginLimit',
            url: './login_ps.php',
            data: $frmLogin.serializeArray(),
            managerId: $login.val()
        };
        $frmLogin[0].reset();
        $login.focus();
        $.get('../share/layer_godo_sms.php', params, function (data) {
            login.dialog.sms_auth = BootstrapDialog.show({
                message: $(data),
                closable: false
            });
        });
    }
    //-->
</script>
