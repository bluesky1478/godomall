<form id="frmSearch" action="" method="get">
    <input type="hidden" name="sno" value="<?=$sno?>">
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
    </div>
    <div class="table-title">
        단축URL 조회수 검색
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md" />
            <col />
        </colgroup>
        <tbody>
        <tr>
            <th>단축 URL</th>
            <td>
                <?=$data['shortUrl']?>
                <button type="button" class="btn btn-sm btn-white js-clipboard" title="단축주소" data-clipboard-text="<?=$data['shortUrl']?>">복사</button>
            </td>
        </tr>
        <tr>
            <th>URL 설명</th>
            <td><?=$data['description'];?></td>
        </tr>
        <tr>
            <th>기간검색</th>
            <td colspan="3">
                <div class="form-inline">
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $search['searchDate'][0]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                    </div>

                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $search['searchDate'][1]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                    </div>

                    <?= gd_search_date($search['searchPeriod'], 'searchDate', false) ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>검색기준</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="queryType" value="day" <?=$checked['queryType']['day']?>>일별
                </label>
                <label class="radio-inline">
                    <input type="radio" name="queryType" value="month" <?=$checked['queryType']['month']?>>월별
                </label>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색기간 조회수 <strong><?= number_format($page->recode['total']); ?></strong>회 /
            전체 조회수 <strong><?= number_format($page->recode['amount']); ?></strong>회
        </div>
        <div class="pull-right form-inline">
            <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
</form>

<div class="table-responsive mgt0" id="excelData">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width-md">날짜</th>
            <th>조회수</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($statistics) === false) {
            foreach ($statistics as $val) {
                ?>
                <tr class="center">
                    <td class="font-date" style="mso-number-format:'\@'">
                        <?php
                        if ($search['queryType'] === 'day') {
                            echo $val['year'] . '-' . $val['month'] . '-' . $val['day'];
                        } else {
                            echo $val['year'] . '-' . $val['month'];
                        }
                        ?>
                    </td>
                    <td><?=$val['count'];?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="10" class="no-data">통계정보가 없습니다.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<div class="table-action">
    <div class="pull-right">
        <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
    </div>
</div>
<div class="center"><?php echo $page->getPage(); ?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 리스트 정렬
        $('#sort, #pageNum').change(function(e){
            $('#frmSearch').submit();
        });

        // 엑셀다운로드
        $('.btn-excel').click(function () {
            var $form = $('<form></form>');
            $form.attr('action', './short_url_ps.php');
            $form.attr('method', 'post');
            $form.attr('target', 'ifrmProcess');
            $form.appendTo('body');

            var mode = $('<input type="hidden" name="mode" value="shorturlExcelDownload">');
            var excel_name = $('<input type="hidden" name="excel_name" value="단축주소_통계_<?=date('Y-m-d H_i_s')?>">');
            var data = $('<input type="hidden" name="data" value="' + encodeURI($('#excelData').html()) + '">');

            $form.append(mode).append(excel_name).append(data);
            $form.submit();
        });
    });
    //-->
</script>
