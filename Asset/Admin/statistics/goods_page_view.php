<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">페이지뷰 검색</div>
<form id="formSearch" method="get" class="content-form js-search-form">
    <input type="hidden" name="tabs" value="<?php echo Request::get()->get('tabs', 'interest') ?>"/>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>조회기간</th>
            <td>
                <div class="form-inline">
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDt[]"
                               value="<?= $requestParams['searchDt'][0]; ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDt[]"
                               value="<?= $requestParams['searchDt'][1]; ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar"></span>
                        </span>
                    </div>

                    <div class="btn-group js-dateperiod-statistics" data-toggle="buttons" data-target-name="searchDt[]">
                        <label class="btn btn-white btn-sm">
                            <input type="radio" name="searchPeriod" value="1">
                            전일
                        </label>
                        <label class="btn btn-white btn-sm">
                            <input type="radio" name="searchPeriod" value="7">
                            7일
                        </label>
                        <label class="btn btn-white btn-sm">
                            <input type="radio" name="searchPeriod" value="15">
                            15일
                        </label>
                        <label class="btn btn-white btn-sm">
                            <input type="radio" name="searchPeriod" value="30">
                            1개월
                        </label>
                        <label class="btn btn-white btn-sm">
                            <input type="radio" name="searchPeriod" value="90">
                            3개월
                        </label>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <button type="submit" class="btn btn-lg btn-black">검색</button>
    </div>
</form>

<ul class="nav nav-tabs">
    <li class="<?php echo $active['tabs']['interest'] ?>"><a href="../statistics/goods_page_view.php?searchDt[]=<?= $requestParams['searchDt'][0] ?>&searchDt[]=<?= $requestParams['searchDt'][1] ?>" id="">인기 페이지</a></li>
</ul>

<div class="table-action mgt30 mgb0">
    <div class="pull-left pdt5">페이지뷰 합계
        <strong class="text-danger"><?php echo $total ?></strong>
        개
    </div>
    <div class="pull-right">
        <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
    </div>
</div>

<div class="table-responsive statistics-board">
    <table class="table table-cols js-excel-data">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col/>
            <col/>
            <col class="width-2xl"/>
            <col/>
        </colgroup>
        <thead>
        <tr class="nowrap text-center">
            <th>순위</th>
            <th>페이지 URL</th>
            <th>페이지명</th>
            <th>페이지뷰</th>
            <th>비율</th>
            <?php if (Request::get()->get('tabs', 'interest') == 'interest') {
                echo '<th>평균체류시간</th>';
            } ?>
        </tr>
        </thead>
        <tbody class="center">
        <?php
        if (isset($lists) && is_array($lists) && count($lists) > 0) {
            $htmlList = [];
            $rank = 1;
            if ($total < 1) {
                $total = 1;
            }
            switch (Request::get()->get('tabs', 'interest')) {
                case 'start':
                    foreach ($lists as $index => $list) {
                        if ($list['startCount'] < 1) {
                            continue;
                        }
                        $startCountRate = ($list['startCount'] / $total) * 100;
                        $startCountRate = number_format($startCountRate, 2, '.', '');

                        $htmlList[] = '<tr class="nowrap text-center">';
                        $htmlList[] = '<td class="font-num">' . $rank . '</td><td>' . $list['pageUrl'] . '</td><td>' . $list['text'] . '</td><td class="font-num">' . $list['startCount'] . '</td>';
                        $htmlList[] = '<td class="font-num"><div class="progress">';
                        $htmlList[] = '<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="' . $startCountRate . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $startCountRate . '%;">';
                        $htmlList[] = '<strong class="text-black">' . $startCountRate . '%</strong>';
                        $htmlList[] = '</div>';
                        $htmlList[] = '</div></td>';
                        $htmlList[] = '</tr>';
                        $rank++;
                    }

                    break;
                case 'end':
                    foreach ($lists as $index => $list) {
                        if ($list['endCount'] < 1) {
                            continue;
                        }
                        $endCountRate = ($list['endCount'] / $total) * 100;
                        $endCountRate = number_format($endCountRate, 2, '.', '');

                        $htmlList[] = '<tr class="nowrap text-center">';
                        $htmlList[] = '<td class="font-num">' . $rank . '</td><td>' . $list['pageUrl'] . '</td><td>' . $list['text'] . '</td><td class="font-num">' . $list['endCount'] . '</td>';
                        $htmlList[] = '<td class="font-num"><div class="progress">';
                        $htmlList[] = '<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="' . $endCountRate . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $endCountRate . '%;">';
                        $htmlList[] = '<strong class="text-black">' . $endCountRate . '%</strong>';
                        $htmlList[] = '</div>';
                        $htmlList[] = '</div></td>';
                        $htmlList[] = '</tr>';
                        $rank++;
                    }
                    break;
                default:
                    $formatDateTime = new DateTime();
                    foreach ($lists as $index => $list) {
                        if ($list['pageViewSec'] < 1) {
                            $list['pageViewSec'] = 1;
                        }
                        if ($list['pageViewCount'] < 1) {
                            $list['pageViewCount'] = 1;
                        }
                        $pageViewRate = ($list['pageViewCount'] / $total) * 100;
                        $pageViewRate = number_format($pageViewRate, 2, '.', '');
                        $pageViewSecAvg = ($list['pageViewSec'] / $list['pageViewCount']);
                        $pageViewSecAvg = round($pageViewSecAvg);
                        $formatDateTime->setTime(0, 0, $pageViewSecAvg);
                        $htmlList[] = '<tr class="nowrap text-center">';
                        $htmlList[] = '<td class="font-num">' . $rank . '</td><td>' . $list['pageUrl'] . '</td><td>' . $list['text'] . '</td><td class="font-num">' . $list['pageViewCount'] . '</td>';
                        $htmlList[] = '<td class="font-num"><div class="progress">';
                        $htmlList[] = '<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="' . $pageViewRate . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $pageViewRate . '%;">';
                        $htmlList[] = '<strong class="text-black">' . $pageViewRate . '%</strong>';
                        $htmlList[] = '</div>';
                        $htmlList[] = '</div></td>';
                        $htmlList[] = '<td class="font-num">' . $formatDateTime->format('i분 s초') . '</td>';
                        $htmlList[] = '</tr>';
                        $rank++;
                    }
                    break;
            }
            echo join('', $htmlList);
        } else {
            if (Request::get()->get('tabs', 'interest') == 'interest') {
                echo '<tr><td class="no-data" colspan="5">통계 정보가 없습니다.</td></tr>';
            } else {
                echo '<tr><td class="no-data" colspan="4">통계 정보가 없습니다.</td></tr>';
            }
        }
        ?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    var widthSize = 930;
    $(document).ready(function () {
        $(':checked').trigger('click');

        $('.btn-excel').click(function (e) {
            e.preventDefault();
            statistics_excel_download('',".js-excel-data",$(".js-excel-data").html());
        });
    });

</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
