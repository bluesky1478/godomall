<form id="frmLivefeed" name="frmLivefeed" action="livefeed_ps.php" method="post">
    <input type="hidden" name="mode" value="config"/>
    <input type="hidden" name="mallSno" value="<?=$mallSno?>" />
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>

        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        실시간 채팅 상담 설정
    </div>
    <ol class="panel pd10">
        <h4><strong>실시간 채팅 상담이란?</strong></h4>
        <li>실시간으로 고객의 문의에 응답함으로써, 떠나가는 고객까지 잡을 수 있습니다.</li>
        <li>실시간 분석을 통한 고객과 쇼핑몰 관리 데이터를 기반으로 하여, 효율적으로 쇼핑몰을 운영하고 회원 증대까지 가져올 수 있는 쇼핑몰 관리 서비스 입니다.</li>
        <li>본 서비스는 디큐엠의 라이브피드 서비스와 제휴하여 제공되는 서비스 입니다.
            <a href="../service/service_info.php?menu=marketing_livefeed_info" target="_blank" class="btn btn-black btn-xs">서비스 자세히 보기</a>
        </li>
    </ol>

    <?php if ($mallCnt > 1) { ?>
        <ul class="multi-skin-nav nav nav-tabs" style="margin:0px; border-bottom:none 0;">
            <?php foreach ($mallList as $key => $mall) { ?>
                <li role="presentation" class="js-popover <?php echo $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?php echo $mall['mallName']; ?>" data-placement="top">
                    <a href="./livefeed_config.php?mallSno=<?php echo $mall['sno']; ?>">
                        <span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span>
                        <span class="mall-name"><?php echo $mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>사용설정</th>
            <td class="form-inline">
                <label class="radio-inline">
                    <input type="radio" name="livefeedUseType" value="y" <?= $checked['livefeedUseType']['y']; ?> />사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="livefeedUseType" value="n" <?= $checked['livefeedUseType']['n']; ?> />사용안함
                </label>
            </td>
        </tr>
        <tr>
            <th>스크립트 적용 범위</th>
            <td class="form-inline">
                <label class="radio-inline">
                    <input type="radio" name="livefeedDeviceType" value="all" <?= $checked['livefeedDeviceType']['all']; ?> />PC + 모바일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="livefeedDeviceType" value="pc" <?= $checked['livefeedDeviceType']['pc']; ?> />PC쇼핑몰
                </label>
                <label class="radio-inline">
                    <input type="radio" name="livefeedDeviceType" value="mobile" <?= $checked['livefeedDeviceType']['mobile']; ?> />모바일쇼핑몰
                </label>
            </td>
        </tr>
        <tr>
            <th>인증키</th>
            <td class="form-inline">
                <input type="text" name="livefeedAuthKey" value="<?= $data['livefeedAuthKey'] ?>" class="form-control width-sm"/> <input type="text" name="livefeedServiceID" value="<?= $data['livefeedServiceID'] ?>" class="form-control width-2xs"/>
            </td>
        </tr>
        </tbody>
    </table>
</form>
<div class="notice-info">
    인증키 및 서비스아이디는 라이브피드 채팅 상담 신청 후 발급받을 수 있습니다. <a href="../service/service_info.php?menu=marketing_livefeed_info" target="_blank">신청하기></a>
</div>
<div class="notice-info">
    해외몰 실시간 상담 채팅은 해외 상점 사용 여부에 따라 이용 가능합니다. <a href="../policy/mall_config.php" target="_blank">해외몰 설정 바로가기></a>
</div>
<div class="notice-info">
    해외몰 설정 시 라이브피드 서비스 목록에 서비스 추가 후 발급받은 새로운 인증키를 등록하시기 바랍니다. <a href="http://godo.livefeed.co/service/list.do" target="_blank">라이브피드 서비스 목록 바로가기></a>
</div>
<div class="notice-info">
    라이브피드 관리자 화면에서 채팅내용 확인 및 로그분석 등을 하실 수 있습니다. <a class="js-livefeed-admin hand">라이브피드 관리자 바로가기></a>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmLivefeed').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                livefeedAuthKey: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=livefeedUseType]:checked').val() == 'y') {
                            required = true;
                        }
                        return required;
                    },
                },
                livefeedServiceID: {
                    required: function (input) {
                        var required = false;
                        if ($('[name=livefeedUseType]:checked').val() == 'y') {
                            required = true;
                        }
                        return required;
                    },
                },
            },
            messages: {
                livefeedAuthKey: {
                    required: '인증키를 입력하셔야 합니다.',
                },
                livefeedServiceID: {
                    required: '인증키를 입력하셔야 합니다.',
                },
            }
        });
        $('input:radio[name="livefeedUseType"]').click(function () {
            changeLivefeedAuthKey();
        });
        changeLivefeedAuthKey();
        changeLivefeedAdminLink();
    });
    function changeLivefeedAuthKey() {
        if ($('input:radio[name="livefeedUseType"]:checked').val() == 'y') {
            $('input:text[name="livefeedServiceID"]').prop('readonly', false);
            $('input:text[name="livefeedAuthKey"]').prop('readonly', false);
        } else {
            $('input:text[name="livefeedServiceID"]').prop('readonly', true);
            $('input:text[name="livefeedAuthKey"]').prop('readonly', true);
        }
    }
    function changeLivefeedAdminLink() {
        $('.js-livefeed-admin').click(function (e) {
            <?php
            if ($data['livefeedServiceID']) {
            ?>
                window.open('http://godo.livefeed.co/member/loginForm.do', 'livefeed');
                return false;
            <?php
            } else {
            ?>
                alert('인증키를 먼저 입력해 주세요.');
                return false;
            <?php
            }
            ?>
        });
    }
    //-->
</script>
