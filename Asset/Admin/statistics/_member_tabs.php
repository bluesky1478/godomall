<?php
$pageUrl = [
    'day'     => [
        '../statistics/member_day.php',
        '일별 회원 현황',
    ],
    'month'     => [
        '../statistics/member_month.php',
        '월별 회원 현황',
    ],
    'gender' => [
        '../statistics/member_gender.php',
        '회원 성별 현황',
    ],
    'age'    => [
        '../statistics/member_age.php',
        '회원 연령별 현황',
    ],
    'area'   => [
        '../statistics/member_area.php',
        '회원 지역별 현황',
    ],
];

$currentUrl = Request::getFileUri();

$htmlNewTabs = [];
$htmlNewTabs[] = '<ul class="nav nav-tabs mgb20" id="memberTabs">';
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
            $('#memberTabs li a').click(move_tab);
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
