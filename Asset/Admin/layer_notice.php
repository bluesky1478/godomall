<?php
/**
 * 관리자 레이어 공지
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 * @author    Shin Donggyu <artherot@godo.co.kr>
 */

// 레이어 공지 체크 (공지건수가 있고 공급사가 아닌경우)
if ($layerNotice['cnt'] > 0 && gd_is_provider() === false) {
    $layerBoxHeight = 65 * $layerNotice['cnt'];
    $layerNoticeCnt = count($layerNotice['kind']);
    ?>
    <div id="gnbNoticeAnchor">
        <img src="<?= PATH_ADMIN_GD_SHARE ?>img/btn_gnb_notice.png" class="hand js-notice-toggle">
    </div>

    <div id="gnbNoticeBox" style="min-height:<?php echo $layerBoxHeight;?>px; height:auto !important; height:<?php echo $layerBoxHeight;?>px;">
        <span class="close js-notice-toggle">닫기</span>
        <?php
        foreach ($layerNotice['kind'] as $kindKey => $kindVal) {
            // 레이어 공지가 있는 경우
            if ($layerNotice[$kindVal] === true) {
                ?>
                <div class="content">
                    <div class="notice-title">
                        <?php echo $layerNotice[$kindVal . 'Title']; ?>
                        <div class="notice-btn"><?php echo $layerNotice[$kindVal . 'Button']; ?></div>
                    </div>
                    <?php echo $layerNotice[$kindVal . 'Desc']; ?>
                </div>
                <?php
                // 라인 처리
                if ($kindKey < ($layerNoticeCnt - 1)) {
                    if ($layerNotice[$kindVal] === true && $layerNotice['cnt'] > 1) {
                        echo '<div class="notice-line"></div>';
                    }
                }
            }
        }
        ?>
    </div>
    <script type="text/javascript">
        <!--
        $(document).ready(function () {
            // 내용 출력 (초기 또는 오픈한 경우에만 출력)
            if ($.cookie('gnbNoticeCookie') == null || $.cookie('gnbNoticeCookie') == 'open') {
                // 안내 박스 토글
                $("#gnbNoticeBox").slideToggle(400);

                // 하단 안내 버튼 사라짐
                $("#gnbNoticeAnchor").fadeOut(400);
            } else {
                // 하단 안내 버튼 보임
                $("#gnbNoticeAnchor").fadeIn(400);
            }

            // 버튼 클릭시
            $('.js-notice-toggle').click(function () {
                $("#gnbNoticeBox").slideToggle(400, function () {
                    // 닫은 경우
                    if ($("#gnbNoticeBox").is(":hidden") == true) {
                        $.removeCookie('gnbNoticeCookie');
                        $.cookie('gnbNoticeCookie', 'close', {expires: <?php echo $layerNotice['cookie'];?>, path: '/'});

                        // 하단 안내 버튼 보임
                        $("#gnbNoticeAnchor").fadeIn(400);
                    }
                    // 오픈한 경우
                    else {
                        $.removeCookie('gnbNoticeCookie');
                        $.cookie('gnbNoticeCookie', 'open', {expires: <?php echo $layerNotice['cookie'];?>, path: '/'});

                        // 하단 안내 버튼 사라짐
                        $("#gnbNoticeAnchor").fadeOut(400);
                    }
                });
            });
        });
        //-->
    </script>
    <?php
    }
    if (isset($layerPopupNotice) && !gd_is_provider()) {
        echo $layerPopupNotice;
    }
    if (isset($layerPopupNotice_190123) && !gd_is_provider()) {
        echo $layerPopupNotice_190123;
    }
    if (isset($layerPopupNotice_exchange)) {
        echo $layerPopupNotice_exchange;
    }
?>
