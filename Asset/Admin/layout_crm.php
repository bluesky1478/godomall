<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 * CRM전용 레이아웃
 */
?>
<?php include UserFilePath::adminSkin('head.php'); ?>
<body class="<?php echo $adminBodyClass; ?> layout-blank layout-crm menu-no-border">

<div id="content" style="min-width:1000px;">
    <div class="col-xs-12">
        <?php include($layoutHeader); ?>
        <!--    <div class="scroll-content">-->
        <!--    </div>-->
        <?php include($layoutContent); ?>
    </div>
</div>
<iframe name="ifrmProcess" src="/blank.php" width="100%" height="200" class="<?= App::isDevelopment() === true ? 'display-block' : 'display-none' ?>"></iframe>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        <?= gd_isset($menuAccessAuth); ?>
        $('#formMemberSearch').validate({
            dialog: false,
            submitHandler: function (form) {
                var key = $('select[name=key]', form).val();
                var keyword = $('input[name=keyword]', form).val();
                if (keyword.length > 0) {
                    layer_member_search(keyword, key);
                } else {
                    alert('검색어가 없습니다.');
                }
            },
            rules: {
                keyword: 'required'
            },
            messages: {
                keyword: '검색어가 없습니다.'
            }
        });

        $('#btnCounsel').click(function () {
            member_counsel($('input[name=memNo]').val());
        });

        $('#btnSendMail').click(function (e) {
            var maillingFl = e.target.dataset.maillingFl;
            var email = e.target.dataset.email;
            if (email === '-' || email.length < 1) {
                alert('이메일 정보가 없는 회원입니다.');
                return;
            }
            if (maillingFl === 'y') {
                member_mail($('input[name=memNo]').val());
            } else {
                dialog_confirm('수신거부한 회원 입니다. 메일 발송을 하시려면 확인을 눌러주세요.', function (result) {
                    if (result) {
                        member_mail($('input[name=memNo]').val());
                    } else {
                        layer_close();
                    }
                });
            }
        });

        $('#btnSendSms').click(function (e) {
            var smsFl = e.target.dataset.smsFl;
            var cellPhone = e.target.dataset.cellPhone;
            if (cellPhone === '-' || cellPhone.length < 1) {
                alert('SMS 정보가 없는 회원입니다.');
                return;
            }
            if (smsFl === 'y') {
                member_sms($('input[name=memNo]').val(), $('input[name=memNm]').val(), $('input[name=cellPhone]').val());
            } else {
                dialog_confirm('수신거부한 회원 입니다. SMS 발송을 하시려면 확인을 눌러주세요.', function (result) {
                    if (result) {
                        member_sms($('input[name=memNo]').val(), $('input[name=memNm]').val(), cellPhone);
                    } else {
                        layer_close();
                    }
                });
            }
        });

        // 팝업 닫기
        $('.close').click(function (e) {
            window.close();
        });
    });
    //-->
</script>
</body>
</html>
