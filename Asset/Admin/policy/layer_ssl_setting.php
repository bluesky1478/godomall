<form id="frmFreeSsl" name="frmFreeSsl" action="ssl_ps.php" target="ifrmProcess" method="post">
    <input type="hidden" name="mode" value="checkFreeSsl"/>
</form>
<form id="frmSsl" name="frmSsl">
    <input type="hidden" name="mode" value="sslSetting"/>
    <input type="hidden" name="sslConfigDomain" value="<?= $sslData['sslConfigDomain']; ?>"/>
    <input type="hidden" name="sslConfigType" value="<?= $sslData['sslConfigType']; ?>"/>
    <input type="hidden" name="checkAlert" value="<?= $sslData['checkAlert']; ?>"/>
    <div id="sslForm">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>보안서버 사용설정</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="sslConfigUse" value="n" <?= $checked['sslConfigUse']['n']; ?> />사용안함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="sslConfigUse" value="y" <?= $checked['sslConfigUse']['y']; ?> />사용함
                    </label>
                </td>
            </tr>
            <tr>
                <th>서비스 상태</th>
                <td><?= $sslData['sslConfigStatusString']; ?></td>
            </tr>
            <tr class="free">
                <th>보안서버 사용기간</th>
                <td>제한없음</td>
            </tr>
            <tr class="free">
                <th>보안서버 적용범위</th>
                <td>로그인, 회원가입, 회원정보수정 페이지에서 이용자의 개인정보 데이터가 암호화되어 전송됨</td>
            </tr>
            <tr class="free">
                <th>인증마크 표시</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="sslFreeImageUse" value="n" <?= gd_isset($checked['sslConfigImageUse']['n']); ?> />표시안함
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="sslFreeImageUse" value="y" <?= gd_isset($checked['sslConfigImageUse']['y']); ?> />표시함
                    </label>
                    <div class="notice-info">
                        ! 스킨 디자인 수정 및 변경에 따른 수동으로 인증마크 표시 적용방법<br/> - 스킨 소스를 변경하였거나, 스킨을 구매했을 경우, 또는 새로 스킨을 만든 경우를 위한 표시 방법입니다.<br/> - 스킨에 따라 하단소스의 Table구조가 다르니, 이 부분 유의해서 원하는 위치에 치환코드를 넣어주세요.<br/> - 위에서 인증마크 표시여부를 '표시함'으로 설정 후,<br/> &nbsp;&nbsp;[디자인관리 > 전체레이아웃 > 하단디자인 > 하단 기본타입] 을 눌러 치환코드 {=displaySSLSeal} 를 삽입하세요.
                        <a href="../design/design_page_edit.php?designPageId=outline/footer/standard.html"> 바로가기></a>
                    </div>
                </td>
            </tr>
            <tr class="godo">
                <th>보안서버 사용기간</th>
                <td><?= $sslData['sslConfigStartDate'] ?> - <?= $sslData['sslConfigEndDate'] ?></td>
            </tr>
            <tr class="godo">
                <th>보안서버 도메인</th>
                <td><span class="eng bold">https://<?= gd_isset($sslData['sslConfigDomain']) ?></span></td>
            </tr>
            <tr class="godo">
                <th>보안서버 포트</th>
                <td><span class="num bold"><?= gd_isset($sslData['sslConfigPort']) ?></span></td>
            </tr>
            <tr class="godo">
                <th>보안서버 적용범위</th>
                <?php
                if ($sslData['sslConfigPosition'] === 'admin') {
                    ?>
                    <td>전체페이지</td>
                    <?php
                } else {
                    ?>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="sslConfigApplyLimit" value="n" <?= gd_isset($checked['sslConfigApplyLimit']['n']); ?> />개인정보 관련 페이지
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslConfigApplyLimit" value="y" <?= gd_isset($checked['sslConfigApplyLimit']['y']); ?> />전체 페이지
                        </label>
                    </td>
                    <?php
                }
                ?>
            </tr>
            <?php
            if ($sslData['sslConfigPosition'] !== 'admin' && $sslData['sslConfigType'] === 'godo') {
                ?>
                <tr class="godo apply-limit">
                    <th>추가 보안 페이지</th>
                    <td>
                        <button type="button" class="btn btn-sm btn-icon-plus btn_add">추가</button>
                        <span class="snote nobold"> &nbsp; &nbsp; member/login.php 형식으로 입력하세요.</span>
                        <ul class="userSslRule clear-both" style="padding:5px 0; margin:0;">
                            <?php if (is_array($sslData['sslConfigUserRule']) === true) {
                                foreach ($sslData['sslConfigUserRule'] as $idx => $v) { ?>
                                    <li class="form-inline">
                                        <input type="text" name="sslConfigUserRule[]" value="<?= $v; ?>" class="form-control width-xl eng"/>
                                        <button class="btn btn-sm btn-icon-minus btn_del">삭제</button>
                                    </li>
                                <?php }
                            } ?>
                        </ul>
                        <div class="notice-info">회원 관련 / 주문 관련 / 게시판 관련 / 마이페이지 관련 등의 페이지에서 이용자의 개인정보가 암호화되어 전송되도록 선택합니다.</div>
                        <div class="notice-danger">주의 : 보안서버 사용이 적용되면 반드시 주문(결제포함) 테스트로 정상적으로 주문이 이뤄지는지 확인하시기 바랍니다.</div>
                    </td>
                </tr>
                <?php
            }
            if ($sslData['sslConfigPosition'] === 'pc' && $sslData['sslConfigType'] === 'godo') {
            ?>
                <tr class="godo">
                    <th>인증마크 표시</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="sslGodoImageUse" value="n" <?= gd_isset($checked['sslConfigImageUse']['n']); ?> />표시안함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslGodoImageUse" value="y" <?= gd_isset($checked['sslConfigImageUse']['y']); ?> />표시함
                        </label>
                        <div class="ssl-image" style="padding-top:10px;">
                            <label class="radio-inline" style="vertical-align:top;">
                                <input type="radio" name="sslConfigImageType"
                                       value="globalSignAlpha" <?= gd_isset($checked['sslConfigImageType']['globalSignAlpha']); ?> />GlobalSign(Alpha
                                SSL)
                                <div><img src="/data/commonimg/logo_alpha.png"></div>
                            </label>
                            <label class="radio-inline" style="vertical-align:top;">
                                <input type="radio" name="sslConfigImageType"
                                       value="globalSignQuick" <?= gd_isset($checked['sslConfigImageType']['globalSignQuick']); ?> />GlobalSign(Quick
                                SSL)
                                <div><img src="/data/commonimg/logo_quick.png"></div>
                            </label>
                            <label class="radio-inline" style="vertical-align:top;">
                                <input type="radio" name="sslConfigImageType"
                                       value="comodo" <?= gd_isset($checked['sslConfigImageType']['comodo']); ?> />Comodo
                                <div><img src="/data/commonimg/logo_comodo.png"></div>
                            </label>
                        </div>
                        <div class="notice-info">
                            ! 스킨 디자인 수정 및 변경에 따른 수동으로 인증마크 표시 적용방법<br/> - 스킨 소스를 변경하였거나, 스킨을 구매했을 경우, 또는 새로 스킨을 만든 경우를 위한 표시 방법입니다.<br/> -
                            스킨에 따라 하단소스의 Table구조가 다르니, 이 부분 유의해서 원하는 위치에 치환코드를 넣어주세요.<br/> - 위에서 인증마크 표시여부를 '표시함'으로 설정 후,<br/> &nbsp;&nbsp;[디자인관리
                            > 전체레이아웃 > 하단디자인 > 하단 기본타입] 을 눌러 치환코드 {=displaySSLSeal} 를 삽입하세요.
                            <a href="../design/design_page_edit.php?designPageId=outline/footer/standard.html"> 바로가기></a>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
            <!-- //유료보안서버 -->
        </table>
    </div>
    <div class="text-center" style="padding: 20px 0 25px 0;border-top: 1px solid #e6e6e6">
        <button type="button" class="btn btn-xl btn-white js-layer-close mgr5">닫기</button>
        <button type="submit" class="btn btn-xl btn-black">저장</button>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $("#frmSsl").validate({
            submitHandler: function (form) {
                sslConfigType = $('input:hidden[name="sslConfigType"]').val();
                sslConfigUse = $('input:radio[name="sslConfigUse"]:checked').val();
                sslConfigMallFl = '<?= $sslData["sslConfigMallFl"]; ?>';
                checkAlert = $('input:hidden[name="checkAlert"]').val();
                if (sslConfigType === 'free' && sslConfigUse === 'y' && sslConfigMallFl === 'kr' && checkAlert > 0) {
                    dialog_confirm('무료 보안서버 사용 시, 사용중인 기준몰의 유료보안서버는 해제됩니다.<br/>정말 사용하시겠습니까?', function (result) {
                        if (result) {
                            $.post('../policy/ssl_ps.php', $('#frmSsl').serializeArray(), function (data) {
                                if (data.result === 'ok') {
                                    dialog_alert(data.message, '확인', {isReload: true});
                                    return false;
                                } else {
                                    process_close();
                                    alert(data.message);
                                }
                            });
                        }
                    });
                } else if (sslConfigType === 'godo' && sslConfigUse === 'y' && sslConfigMallFl === 'kr' && checkAlert > 0) {
                    dialog_confirm('유료 보안서버 사용 시, 사용중인 기준몰의 무료보안서버는 해제됩니다.<br/>정말 사용하시겠습니까?', function (result) {
                        if (result) {
                            $.post('../policy/ssl_ps.php', $('#frmSsl').serializeArray(), function (data) {
                                if (data.result === 'ok') {
                                    dialog_alert(data.message, '확인', {isReload: true});
                                    return false;
                                } else {
                                    process_close();
                                    alert(data.message);
                                }
                            });
                        }
                    });
                } else {
                    $.post('../policy/ssl_ps.php', $('#frmSsl').serializeArray(), function (data) {
                        if (data.result === 'ok') {
                            dialog_alert(data.message, '확인', {isReload: true});
                            return false;
                        } else {
                            process_close();
                            alert(data.message);
                        }
                    });
                }
                return false;
            },
            dialog: false,
        });

        $('#requestFreeSsl').click(function () {
            document.frmFreeSsl.submit();
        });

        $('#requestPaySsl').click(function () {
            window.open('https://hosting.godo.co.kr/valueadd/ssl_service.php?iframe=yes', '_blank');
            return false;
        });

        $('input[name=\'sslConfigApplyLimit\']').click(changeSslUserRule);

        // 숫자만 입력
        $('input[name=\'sslConfigPort\']').number_only();

        // 이벤트정의
        $('.btn_add').click(add_userSslRule_form);
        $('.btn_del').click(del_userSslRule_form);

        $('input[name=\'sslGodoImageUse\']').click(function () {
            changeSslImageType();
        });

        set_ssl_type('<?= $sslData['sslConfigType']; ?>');
        changeSslImageType();
        changeSslUserRule();
    });

    function changeSslUserRule() {
        if ($('input[name="sslConfigApplyLimit"]:checked').val() === 'n') {
            $('.apply-limit').show();
        } else if ($('input[name="sslConfigApplyLimit"]:checked').val() === 'y') {
            $('.apply-limit').hide();
        }
    }

    /**
     * 보안서버 타입 선택
     */
    function set_ssl_type(sslType) {
        if (sslType === 'free') {
            $('.free').show();
            $('.godo').hide();
        } else {
            $('.godo').show();
            $('.free').hide();
        }
    }

    function changeSslImageType() {
        if ($('input[name="sslGodoImageUse"]:checked').val() === 'y') {
            $('.ssl-image').show();
            $('input[name="sslConfigImageType"]').prop('disabled', false);
        } else {
            $('.ssl-image').hide();
            $('input[name="sslConfigImageType"]').prop('disabled', true);
        }

    }

    /**
     * 추가보안페이지 폼 추가
     */
    function add_userSslRule_form() {
        var addHtml = '';
        addHtml += '<li class="form-inline pdt5">';
        addHtml += '	<input type="text" name="sslConfigUserRule[]" value="" class="form-control width-xl eng"/>';
        addHtml += '	<button class="btn btn-sm btn-icon-minus btn_del">삭제</button>';
        addHtml += '</li>';
        $('ul.userSslRule').append(addHtml);
        var lastLi = $('ul.userSslRule li:last-child');
        $('.btn_del', lastLi).click(del_userSslRule_form);
    }

    /**
     * 추가보안페이지 폼 삭제
     */
    function del_userSslRule_form() {
        $(this).parents('li').first().remove();
    }
    //-->
</script>
