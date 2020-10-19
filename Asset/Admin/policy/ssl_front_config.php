<form id="frmFreeSsl" name="frmFreeSsl" action="ssl_ps.php" target="ifrmProcess" method="post">
    <input type="hidden" name="mode" value="checkFreeSsl"/>
</form>
<form id="frmSsl" name="frmSsl" action="ssl_ps.php" method="post">
    <input type="hidden" name="mode" value="<?= $mode; ?>"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location); ?>
    </div>
    <div id="webhost">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <?php if ($data['setMode'] == 'serverhost') { ?>
                <!-- 서버호스팅 -->
                <tr>
                    <th>보안서버 사용상태</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="sslType" value="" <?php echo gd_isset($checked['sslType']['']); ?> />사용안함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslType" value="godo" <?php echo gd_isset($checked['sslType']['godo']); ?> />유료 보안서버 사용
                        </label>
                    </td>
                </tr>
            <?php } else if ($data['setMode'] == 'webhost') { ?>
                <!-- 고도 호스팅 -->
                <tr>
                    <th>보안서버 사용상태</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="sslType" value="" <?php echo gd_isset($checked['sslType']['']); ?> />사용안함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslType" value="free" <?php echo gd_isset($checked['sslType']['free']); ?> />무료 보안서버 사용
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslType" value="godo" <?php echo gd_isset($checked['sslType']['godo']); ?> />유료 보안서버 사용
                        </label>
                    </td>
                </tr>
            <?php } else if ($data['setMode'] == 'outhost') { ?>
                <!-- 외부 호스팅 -->
                <tr>
                    <th>보안서버 사용상태</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="sslType" value="" <?php echo gd_isset($checked['sslType']['']); ?> />사용안함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslType" value="direct" <?php echo gd_isset($checked['sslType']['direct']); ?> />직접설정
                        </label>
                    </td>
                </tr>
            <?php } ?>
            <?php if ($data['setMode'] == 'webhost') { ?>
                <!-- 무료보안서버 -->
                <tr class="free">
                    <th>보안서버 사용상태</th>
                    <td>
                        <?php if ($data['sslType'] == 'free') { ?><span class="bold">사용중</span><?php } ?>
                        <?php if ($data['sslType'] != 'free' && empty($data['freeSsl']['freeDomain']) === false) { ?>
                            <span class="bold">사용가능(저장버튼을 누르세요)</span><?php } ?>
                        <?php if ($data['sslType'] != 'free' && empty($data['freeSsl']['freeDomain']) === true) { ?>
                            <button type="button" id="requestFreeSsl" class="btn btn-sm btn-gray">무료SSL설치요청</button>
                        <?php } ?>
                        <input type="hidden" name="sslFreeDomain" value="<?php echo $data['freeSsl']['freeDomain']; ?>"/>
                    </td>
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
                            <input type="radio" name="sslFreeImageUse" value="n" <?php echo gd_isset($checked['sslFreeImageUse']['n']); ?> />표시안함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslFreeImageUse" value="y" <?php echo gd_isset($checked['sslFreeImageUse']['y']); ?> />표시함
                        </label>
                        <div class="notice-info">
                            ! 스킨 디자인 수정 및 변경에 따른 수동으로 인증마크 표시 적용방법<br/> - 스킨 소스를 변경하였거나, 스킨을 구매했을 경우, 또는 새로 스킨을 만든 경우를 위한 표시 방법입니다.<br/> - 스킨에 따라 하단소스의 Table구조가 다르니, 이 부분 유의해서 원하는 위치에 치환코드를 넣어주세요.<br/> - 위에서 인증마크 표시여부를 '표시함'으로 설정 후,<br/> &nbsp;&nbsp;[디자인관리 > 전체레이아웃 > 하단디자인 > 하단 기본타입] 을 눌러 치환코드 {=displaySSLSeal} 를 삽입하세요.
                            <a href="../design/design_page_edit.php?designPageId=outline/footer/standard.html"> 바로가기></a>
                        </div>
                    </td>
                </tr>
                <!-- //무료보안서버 -->
            <?php } ?>
            <?php if ($data['setMode'] == 'serverhost' || $data['setMode'] == 'webhost') { ?>
                <!-- 유료보안서버 -->
                <tr class="godo">
                    <th>보안서버 사용상태</th>
                    <td>
                        <span class="bold"><?php echo gd_isset($data['godoSsl']['strSslStep']); ?></span>
                        <?php if (in_array(gd_isset($data['godoSsl']['sslStep']), ['request', 'renewrequest'])) { ?>
                            &nbsp;&nbsp;
                            <button type="button" id="requestPaySsl" class="btn btn-sm btn-gray">보안서버 안내 및 사용신청</button>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="godo">
                    <th>보안서버 사용기간</th>
                    <td>
                        <?php if (empty($data['sslSdate']) === false && empty($data['sslEdate']) === false) { ?>
                            <?php echo $data['sslSdate'] ?> - <?php echo $data['sslEdate'] ?>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="godo">
                    <th>보안서버 도메인</th>
                    <td><span class="eng bold">https://<?php echo gd_isset($data['sslDomain']) ?></span></td>
                </tr>
                <tr class="godo">
                    <th>보안서버 포트</th>
                    <td><span class="num bold"><?php echo gd_isset($data['sslPort']) ?></span></td>
                </tr>
                <tr class="godo">
                    <th>보안서버 적용범위</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="sslApplyLimit" value="n" <?php echo gd_isset($checked['sslApplyLimit']['n']); ?> />개인정보 관련 페이지
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslApplyLimit" value="y" <?php echo gd_isset($checked['sslApplyLimit']['y']); ?> />전체 페이지
                        </label>
                    </td>
                </tr>
                <tr class="godo apply-limit">
                    <th>추가 보안 페이지</th>
                    <td>
                        <button type="button" class="btn btn-sm btn-icon-plus btn_add">추가</button>
                        <span class="snote nobold"> &nbsp; &nbsp; memeber/login.php 형식으로 입력하세요.</span>
                        <ul class="userSslRule clear-both" style="padding:5px 0; margin:0;">
                            <?php if (is_array($data['userSslRule']) === true) {
                                foreach ($data['userSslRule'] as $idx => $v) { ?>
                                    <li class="form-inline">
                                        <input type="text" name="userSslRule[]" value="<?php echo $v; ?>" class="form-control width-xl eng"/>
                                        <button class="btn btn-sm btn-icon-minus btn_del">삭제</button>
                                    </li>
                                <?php }
                            } ?>
                        </ul>
                        <div class="notice-info">회원 관련 / 주문 관련 / 게시판 관련 / 마이페이지 관련 등의 페이지에서 이용자의 개인정보가 암호화되어 전송되도록 선택합니다.</div>
                        <div class="notice-danger">주의 : 보안서버 사용이 적용되면 반드시 주문(결제포함) 테스트로 정상적으로 주문이 이뤄지는지 확인하시기 바랍니다.</div>
                    </td>
                </tr>
                <tr class="godo">
                    <th>인증마크 표시</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="sslGodoImageUse" value="n" <?php echo gd_isset($checked['sslGodoImageUse']['n']); ?> />표시안함
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslGodoImageUse" value="y" <?php echo gd_isset($checked['sslGodoImageUse']['y']); ?> />표시함
                        </label>
                        <div class="ssl-image" style="padding-top:10px;">
                            <label class="radio-inline" style="vertical-align:top;">
                                <input type="radio" name="sslGodoImageType" value="globalSignAlpha" <?php echo gd_isset($checked['sslGodoImageType']['globalSignAlpha']); ?> />GlobalSign(Alpha SSL)
                                <div><img src="/data/commonimg/logo_alpha.png"></div>
                            </label>
                            <label class="radio-inline" style="vertical-align:top;">
                                <input type="radio" name="sslGodoImageType" value="globalSignQuick" <?php echo gd_isset($checked['sslGodoImageType']['globalSignQuick']); ?> />GlobalSign(Quick SSL)
                                <div><img src="/data/commonimg/logo_quick.png"></div>
                            </label>
                            <label class="radio-inline" style="vertical-align:top;">
                                <input type="radio" name="sslGodoImageType" value="comodo" <?php echo gd_isset($checked['sslGodoImageType']['comodo']); ?> />Comodo
                                <div><img src="/data/commonimg/logo_comodo.png"></div>
                            </label>
                        </div>
                        <div class="notice-info">
                            ! 스킨 디자인 수정 및 변경에 따른 수동으로 인증마크 표시 적용방법<br/> - 스킨 소스를 변경하였거나, 스킨을 구매했을 경우, 또는 새로 스킨을 만든 경우를 위한 표시 방법입니다.<br/> - 스킨에 따라 하단소스의 Table구조가 다르니, 이 부분 유의해서 원하는 위치에 치환코드를 넣어주세요.<br/> - 위에서 인증마크 표시여부를 '표시함'으로 설정 후,<br/> &nbsp;&nbsp;[디자인관리 > 전체레이아웃 > 하단디자인 > 하단 기본타입] 을 눌러 치환코드 {=displaySSLSeal} 를 삽입하세요.
                            <a href="../design/design_page_edit.php?designPageId=outline/footer/standard.html"> 바로가기></a>
                        </div>
                    </td>
                </tr>
                <!-- //유료보안서버 -->
            <?php } ?>
            <?php if ($data['setMode'] == 'outhost') { ?>
                <!-- 직접설정 -->
                <tr class="direct">
                    <th>사용기간</th>
                    <td class="form-inline">
                        <input type="text" name="sslSdate" value="<?php echo gd_isset($data['sslSdate']) ?>" class="form-control width-md js-datepicker"> -
                        <input type=text name=sslEdate value="<?php echo gd_isset($data['sslEdate']) ?>" class="form-control width-md js-datepicker">
                        <span class="snote num">예.2016-02-19</span>
                    </td>
                </tr>
                <tr class="direct">
                    <th>보안서버 도메인</th>
                    <td>
                        <input type="text" name="sslDomain" value="<?php echo gd_isset($data['sslDomain']) ?>" class="form-control width-xl">
                    </td>
                </tr>
                <tr class="direct">
                    <th>보안서버 포트</th>
                    <td>
                        <input type="text" name="sslPort" value="<?php echo gd_isset($data['sslPort']) ?>" class="form-control width-md">
                    </td>
                </tr>
                <tr class="direct">
                    <th>보안서버 적용범위</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="sslApplyLimit" value="n" <?php echo gd_isset($checked['sslApplyLimit']['n']); ?> />개인정보 관련 페이지
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sslApplyLimit" value="y" <?php echo gd_isset($checked['sslApplyLimit']['y']); ?> />전체 페이지
                        </label>
                    </td>
                </tr>
                <tr class="direct apply-limit">
                    <th>추가 보안 페이지</th>
                    <td>
                        <button type="button" class="btn btn-sm btn-gray btn_add">+ 추가</button>
                        <span class="snote nobold"> &nbsp; &nbsp; !memeber/login.php 형식으로 입력하세요.</span>
                        <ul class="userSslRule clear-both" style="padding:5px 0; margin:0;">
                            <?php if (is_array($data['userSslRule']) === true) {
                                foreach ($data['userSslRule'] as $idx => $v) { ?>
                                    <li class="form-inline">
                                        <input type="text" name="userSslRule[]" value="<?php echo $v; ?>" class="form-control width-xl eng"/>
                                        <button class="btn btn-sm btn-gray btn_del">- 삭제</button>
                                    </li>
                                <?php }
                            } ?>
                        </ul>
                        <div class="notice-info">회원 관련 / 주문 관련 / 게시판 관련 / 마이페이지 관련 등의 페이지에서 이용자의 개인정보가 암호화되어 전송되도록 선택합니다.</div>
                        <div class="notice-danger">주의 : 보안서버 사용이 적용되면 반드시 주문(결제포함) 테스트로 정상적으로 주문이 이뤄지는지 확인하시기 바랍니다.</div>
                    </td>
                </tr>
                <!-- //직접설정 -->
            <?php } ?>
        </table>
    </div>
    <div class="notice-info">
        보안서버(SSL)구축 의무 강화 안내<br/>
        정보통신망법에 따라 보안서버(SSL) 구축 의무사항 위반 시 민원이 발생할 경우 사전경고없이 최고 3,000만원의 과태료가 부과됩니다.<br/>
        (적용대상 : 온라인 쇼핑몰 및 회원가입/로그인/주문/결제/게시판 등의 이용과정에서 이름, 주민등록번호, 연락처 등을 취급하는 웹사이트)<br/>
        운영하는 사이트의 개인정보 취급 여부를 반드시 확인하시고, 의무사항 적용 대상에 해당하는 사이트는 보안서버 사용을 필수로 설정하여 주세요.
    </div>
    <div class="notice-info">
        [무료보안서버 사용]으로 설정 시 주문/결제 등의 일부 페이지에서는 보안서버가 적용되지 않습니다.<br/>
        무료 보안서버를 이용중인 고객께서는 반드시 [유료 보안서버 사용]으로 전환하시어, 불이익을 받지 않도록 하시기 바랍니다.<br/>
        유료 보안서버는 설치기간이 다소(2일~7일) 소요될 수 있습니다.<br/>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 스크롤저장버튼
        $("#frmSsl").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                sslType: {
                    required: true,
                },
                sslFreeDomain: {
                    required: function (input) {
                        var required = false;
                        if ($('input:radio[name="sslType"]:checked').val() == 'free') {
                            required = true;
                        }
                        return required;
                    }
                },
                sslStep: {
                    required: true,
                },

            },
            messages: {
                sslType: {
                    required: '보안서버 사용상태을 선택해 주세요.',
                },
                sslFreeDomain: {
                    required: '무료 SSL 설치요청을 먼저 해주시기 바랍니다.<br/>무료 SSL이 설치된 후 무료 보안서버 사용을 설정하실 수 있습니다.',
                },
            }
        });

        $('#requestFreeSsl').click(function () {
            document.frmFreeSsl.submit();
        });

        $('#requestPaySsl').click(function () {
            window.open('https://hosting.godo.co.kr/valueadd/ssl_service.php?iframe=yes', '_blank');
            return false;
        });

        // 보안서버 타입 선택
        $('input[name=\'sslType\']').click(set_ssl_type);
        if ($('input[name=\'sslType\']:checked').length == 0) {
            $('input[name=\'sslType\']:eq(0)').prop('checked', true);
        }
        $('input[name=\'sslType\']').each(function () {
            set_ssl_type.call(this);
        });
        $('input[name=\'sslApplyLimit\']').click(changeSslUserRule);

        // 숫자만 입력
        $('input[name=\'sslPort\']').number_only();

        // 이벤트정의
        $('.btn_add').click(add_userSslRule_form);
        $('.btn_del').click(del_userSslRule_form);

        $('input[name=\'sslGodoImageUse\']').click(function () {
            changeSslImageType();
        });

        changeSslImageType();
        changeSslUserRule();
    });

    function changeSslUserRule() {
        if ($('input[name="sslApplyLimit"]:checked').val() == 'n') {
            $('.apply-limit').show();
        } else if ($('input[name="sslApplyLimit"]:checked').val() == 'y') {
            $('.apply-limit').hide();
        }
    }

    /**
     * 보안서버 타입 선택
     */
    function set_ssl_type() {
        if (this.checked && $(this).val() == 'free') {
            $('.free, .free *').show();
            $('.none, .none *, .godo, .godo *, .direct, .direct *').hide();
        } else if (this.checked && $(this).val() == 'godo') {
            $('.godo, .godo *').show();
            $('.none, .none *, .free, .free *, .direct *, .direct *').hide();
        } else if (this.checked && $(this).val() == 'direct') {
            $('.direct, .direct *').show();
            $('.none, .none *, .free, .free *, .godo, .godo *').hide();
        } else if (this.checked && $(this).val() == '') {
            $('.none, .none *').show();
            $('.free, .free *, .godo, .godo *, .direct, .direct *').hide();
        }
    }

    function changeSslImageType() {
        if ($('input[name="sslType"]:checked').val() == 'godo') {
            if ($('input[name="sslGodoImageUse"]:checked').val() == 'y') {
                $('.ssl-image').show();
                $('input[name="sslGodoImageType"]').prop('disabled', false);
            } else {
                $('.ssl-image').hide();
                $('input[name="sslGodoImageType"]').prop('disabled', true);
            }
        }
    }

    /**
     * 추가보안페이지 폼 추가
     */
    function add_userSslRule_form() {
        var addHtml = '';
        addHtml += '<li class="form-inline pdt5">';
        addHtml += '	<input type="text" name="userSslRule[]" value="" class="form-control width-xl eng"/>';
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
