<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">전체 회원 검색</div>
<form id="formSearch" method="get" class="content-form js-search-form">
    <?= $htmlPeriodTable ?>
</form>

<?php include('_member_tabs.php') ?>

<div class="table-dashboard">
    <table class="table table-cols">
        <tbody>
        <tr>
            <th class="bln point">전체 회원수</th>
            <th>총 주문건수</th>
            <th>총 주문금액</th>
            <th>총 페이지뷰</th>
            <th>총 방문횟수</th>
        </tr>
        <tr>
            <td class="bln point font-num">
                <strong><?= number_format($vo->getTotal()) ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($vo->getOrderCount()) ?></strong>
                <ul class="list-unstyled">
                    <li>
                        <strong>PC쇼핑몰</strong>
                        <span><?= number_format($vo->getOderCountPc()) ?></span>
                    </li>
                    <li>
                        <strong>모바일쇼핑몰</strong>
                        <span><?= number_format($vo->getOrderCountMobile()) ?></span>
                    </li>
                </ul>
            </td>
            <td class="font-num">
                <strong><?= number_format($vo->getSettlePrice()) ?></strong>
                <ul class="list-unstyled">
                    <li>
                        <strong>PC쇼핑몰</strong>
                        <span><?= number_format($vo->getSettlePricePc()) ?></span>
                    </li>
                    <li>
                        <strong>모바일쇼핑몰</strong>
                        <span><?= number_format($vo->getSettlePriceMobile()) ?></span>
                    </li>
                </ul>
            </td>
            <td class="font-num">
                <strong><?= number_format($vo->getVisitPageView()) ?></strong>
                <ul class="list-unstyled">
                    <li>
                        <strong>PC쇼핑몰</strong>
                        <span><?= number_format($vo->getVisitPageViewPc()) ?></span>
                    </li>
                    <li>
                        <strong>모바일쇼핑몰</strong>
                        <span><?= number_format($vo->getVisitPageViewMobile()) ?></span>
                    </li>
                </ul>
            </td>
            <td class="font-num">
                <strong><?= number_format($vo->getVisit()) ?></strong>
                <ul class="list-unstyled">
                    <li>
                        <strong>PC쇼핑몰</strong>
                        <span><?= number_format($vo->getVisitPc()) ?></span>
                    </li>
                    <li>
                        <strong>모바일쇼핑몰</strong>
                        <span><?= number_format($vo->getVisitMobile()) ?></span>
                    </li>
                </ul>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="table-action mgt30 mgb0">
    <div class="pull-right">
        <div>
            <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
        </div>
    </div>
</div>
<div class="table-responsive statistics-board" id="excelData">
    <table class="table table-cols">
        <colgroup>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
            <col>
        </colgroup>
        <thead>
        <tr>
            <th rowspan="2">순위</th>
            <th rowspan="2">회원 아이디</th>
            <th rowspan="2" class="">주문건수<br/>합계</th>
            <th colspan="2">주문건수</th>
            <th rowspan="2" class="">주문금액<br/>합계</th>
            <th colspan="2">주문금액</th>
            <th rowspan="2" class="">페이지뷰<br/>합계</th>
            <th colspan="2">페이지뷰</th>
            <th rowspan="2" class="">방문횟수<br/>합계</th>
            <th colspan="2">방문횟수</th>
        </tr>
        <tr>
            <th>PC쇼핑몰</th>
            <th>모바일쇼핑몰</th>
            <th>PC쇼핑몰</th>
            <th>모바일쇼핑몰</th>
            <th>PC쇼핑몰</th>
            <th>모바일쇼핑몰</th>
            <th>PC쇼핑몰</th>
            <th>모바일쇼핑몰</th>
        </tr>
        </thead>
        <tbody>
        <?php echo $htmlTable; ?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $('.btn-excel').click(function (e) {
            e.preventDefault();
            var $form = $('<form></form>');
            $form.attr('action', './excel_ps.php');
            $form.attr('method', 'post');
            $form.attr('target', 'ifrmProcess');
            $form.appendTo('body');

            var mode = $('<input type="hidden" name="mode" value="excel_form_html_convert">');
            var excel_name = $('<input type="hidden" name="excel_name" value="<?= $naviMenu->location[0] . '_' . $naviMenu->location[1] . '_' . $naviMenu->location[2] . '_' . $page['title']?>">');
            var data = $('<input type="hidden" name="data" value="' + encodeURI($('#excelData').html()) + '">');

            $form.append(mode).append(excel_name).append(data);
            $form.submit();
        });
    });

</script>
