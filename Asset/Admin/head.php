<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8" />
    <title><?php echo $_mallName_?> :: 쇼핑몰 관리자 - 고도몰5</title>
    <meta name="description" content="고도몰만의 특별한 서비스! 새로워진 쇼핑몰 관리자 앱은 모바일 전용 페이지를 통해 운영 중인 쇼핑몰을 언제, 어디서든 관리하실 수 있는 서비스입니다.
	고도몰의 쇼핑몰 솔루션 e나무 임대형, e나무 무료형, e나무 독립형 고객님 모두 제한 없이 이용하실 수 있습니다. " />
    <meta name="author" content="qnibus" />
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <meta name="viewport" content="minimal-ui, width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <meta http-equiv="cleartype" content="on">
    <?php if($gSecure && $gReferrer) { ?>
        <meta name="referrer" content="origin">
    <?php } ?>

    <!-- Latest compiled and minified CSS -->
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'css/bootstrap.css')?>" rel="stylesheet"/>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'css/bootstrap-datetimepicker.css')?>" rel="stylesheet"/>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'css/bootstrap-datetimepicker-standalone.css')?>" rel="stylesheet"/>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'css/bootstrap-dialog.css')?>" rel="stylesheet"/>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/jquery-ui/jquery-ui.css')?>" rel="stylesheet"/>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'css/style.css')?>" rel="stylesheet"/>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'css/non-responsive.css')?>" rel="stylesheet"/>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'css/flags.css')?>" rel="stylesheet"/>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'css/jquery.countdownTimer.css')?>" rel="stylesheet"/>
    <?php
    $headerStyle = gd_isset($headerStyle);
    if (is_array($headerStyle)) {
        foreach ($headerStyle as $v) { ?>
            <link type = "text/css" href = "<?=gd_set_browser_cache($v); ?>" rel = "stylesheet" />
            <?php
        }
    }
    if (isset($headCss) === true) { ?>
        <style type="text/css">
            <?=$headCss;?>
        </style>
        <?php
    } ?>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'css/gd5-style.css')?>" rel="stylesheet"/>
    <link type="text/css" href="<?=gd_set_browser_cache(PATH_ADMIN_SKIN . 'css/admin-custom.css')?>" rel="stylesheet"/>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/underscore/underscore-min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/jquery.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/jquery-ui/jquery-ui.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/bootstrap/bootstrap.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/moment/moment.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/numeral/numeral.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/bootstrap/bootstrap-datetimepicker.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/bootstrap/bootstrap-dialog.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/bootstrap/bootstrap-filestyle.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/copyclipboard/ZeroClipboard.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/copyclipboard/clipboard.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/copyclipboard/clipboard-2.0.0.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/jquery-browser/dist/jquery.browser.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/jquery.serialize-object.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/jquery.countdownTimer.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/validation/jquery.validate.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/validation/localization/messages_ko.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/validation/additional-methods.min.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/datasaver/jquery.DataSaver.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/jquery.number_only.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/jquery/jquery-cookie/jquery.cookie.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/common2.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/common.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/schedule.js')?>"></script>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_GD_SHARE . 'script/admin_panel_api.js')?>"></script>
    <?php
    $headerScript = gd_isset($headerScript);
    if (is_array($headerScript)) {
        foreach ($headerScript as $url) { ?>
            <script type="text/javascript" src="<?=($url)?>"></script>
            <?php
        }
    } ?>
    <script type="text/javascript" src="<?=gd_set_browser_cache(PATH_ADMIN_SKIN . 'script/admin-custom.js')?>"></script>

    <script type="text/javascript">
        <!--
        $(document).ready(function () {
            <?php if (isset($godoSecurityAgreement)) { ?>
            godo.layer.security_agreement.show(<?= $godoSecurityAgreement; ?>);
            <?php } elseif (isset($godoLoginSecurity)) { ?>
            var panel_options = {};
            panel_options = <?= $godoLoginSecurity; ?>;
            panel_options.position = {};
            panel_options.target = $(".login-layout");

            godo.layer.login_security.show(panel_options);
            <?php } ?>

            <?php if (empty($manualData['menuKey']) === false) {?>

            // 메뉴얼 링크
            $(".gd-help-manual").each(function() {
                var linkText = $(this).text().trim().replace(/\s/g, ''); // 타이틀의 문자열의 공백을 제거함
                var manualUrl = '<?php echo sprintf($manualData['manual_url'], $manualData['menuCode'], $manualData['menuKey'], $manualData['menuFile']);?>#' + linkText;
                $(this).append(' <a href="' + manualUrl + '" target="_blank" class="link-help">페이지 도움말</a>');
            });
            <?php }?>

            <?php if (empty($tooltipData) === false) {?>

            // 툴팁 데이터
            var tooltipData = <?php echo $tooltipData;?>;
            var sectionEle = null;
            $('#content .table.table-cols th').each(function(idx){
                if ($(this).closest('table').siblings('.table-title').length) {
                    sectionEle = $(this).closest('table').prevAll('.table-title:first');
                } else if ($(this).closest('table').parent('div').siblings('.table-title').length) {
                    sectionEle = $(this).closest('table').parent('div').prevAll('.table-title:first');
                } else {
                    sectionEle = $(this).closest('table').parent('div').parent('div').prevAll('.table-title:first');
                }
                if (typeof sectionEle[0] !== "undefined") {
                    var sectionTitle = $(sectionEle[0]).html().replace(/\(?<\/?[^*]+>/gi, '').trim().replace(/ /gi, '').replace(/\n/gi, '');
                    var titleName = $(this).text().trim().replace(/ /gi, '').replace(/\n/gi, '');
                    for (var i in tooltipData) {
                        if (tooltipData[i].title == sectionTitle) {
                            if (tooltipData[i].attribute == titleName) {
                                $(this).append('<button type="button" class="btn btn-xs js-tooltip" title="' + tooltipData[i].content + '" data-placement="right" data-width="' + tooltipData[i].cntWidth + '"><span title="" class="icon-tooltip"></span></button>');
                            }
                        }
                    }
                }
            });

            if ($('.js-tooltip').length > 0) {
                // 툴팁 초기화
                $(document).on({
                    'click': function() {
                        if ($(this).attr('aria-describedby')) {
                            $(this).tooltip('destroy');
                        } else {
                            var option = {
                                trigger: 'click',
                                container: '#content',
                                html: true,
                                template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div><button class="tooltip-close">close</button></div>',
                            };
                            $(this).on('shown.bs.tooltip', function () {
                                $(".tooltip.in").css({
                                    width: $(this).data('width'),
                                    maxWidth: "none",
                                });
                            });
                            $(this).tooltip(option).tooltip('show');
                        }
                    }
                }, '.js-tooltip');
                $(document).on('click', '.tooltip.in .tooltip-close', function () {
                    $('.js-tooltip[aria-describedby=' + $(this).parent().attr('id') + ']').trigger('click');
                });
            }

            // 레이어 생성/제거시에 툴팁 제거
            $(document).on('shown.bs.modal hidden.bs.modal', '.modal', function () {
                if ($('.tooltip.in').length) {
                    $('.tooltip.in').tooltip('destroy');
                }
            });
            <?php }?>
        });
        //-->
    </script>

</head>
