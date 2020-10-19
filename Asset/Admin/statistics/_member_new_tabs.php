<?php
$pageUrl = [
    'visitDay'   => [
        '../statistics/member_new_day.php',
        '일별 신규회원 현황',
    ],
    'visitHour'  => [
        '../statistics/member_new_hour.php',
        '시간대별 신규회원 현황',
    ],
    'visitWeek'  => [
        '../statistics/member_new_week.php',
        '요일별 신규회원 현황',
    ],
    'visitMonth' => [
        '../statistics/member_new_month.php',
        '월별 신규회원 현황',
    ],
];
$currentUrl = Request::getFileUri();

$htmlNewTabs = [];
$htmlNewTabs[] = '<ul class="nav nav-tabs mgb20" id="newTabs">';
foreach ($pageUrl as $index => $item) {
    $htmlNewTabs[] = '<li';
    if (strpos($item[0], $currentUrl) > 0) {
        $htmlNewTabs[] = ' class="active"';
    }
    $htmlNewTabs[] = '><a href="' . $item[0] . '" id="' . $index . '">' . $item[1] . '</a></li>';
}
$htmlNewTabs[] = '</ul>';
echo join('', $htmlNewTabs);
unset($htmlNewTabs);
?>
<script type="text/javascript">
    var newTabs = (function ($, _, window, document, undefined) {
        var init = function () {
            $('#newTabs li a').click(move_tab);
        };

        var move_tab = function (e) {
            e.preventDefault();
            var params = $('#frmSearchStatistics').serialize();
            var linkUrl = this.href;
            if (params) {
                linkUrl = linkUrl + '?' + params;
            }
            window.location.href = linkUrl;
        };

        $('document').ready(init);
    })($, _, window, document);
</script>
