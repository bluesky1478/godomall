<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<?php include $layoutOrderSearchForm;// 검색 폼 ?>

<form id="frmOrderStatus" action="./order_ps.php" method="post">
    <input type="hidden" name="mode" value="combine_status_change"/>
    <input type="hidden" id="orderStatus" name="changeStatus" value=""/>

    <div class="table-action-dropdown">
        <div class="table-action mgt0 mgb0">
            <div class="pull-left form-inline">
                <span class="action-title">선택한 주문을</span>
                <?php echo gd_select_box('orderStatusTop', null, $selectBoxOrderStatus, null, null, '=주문상태='); ?>
                <button type="submit" class="btn btn-white js-order-status" />일괄처리</button>
            </div>
            <div class="pull-right">
                <div class="form-inline">
                    <?php if ($search['view'] != 'orderGoods') { ?>
                    <div class="dropdown">
                        <button type="button" id="btnSmsLayer" class="btn btn-red js-sms-layer-open dropdown-toggle dropdown-arr" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">SMS발송</button>
                        <ul class="dropdown-menu mgt10" aria-labelledby="btnSmsLayer">
                            <li class="dropdown-item"><a class="js-sms-send" data-type="select" data-opener="order" data-target-selector="input[name*=statusCheck]:checked">선택 주문 배송</a></li>
                            <li class="dropdown-item"><a class="js-sms-send" data-type="search" data-opener="order" data-target-selector="#frmSearchOrder" data-status-mode="<?=$currentStatusCode?>">검색 주문 배송</a></li>
                        </ul>
                    </div>
                    <?php } ?>
                    <?= gd_select_box('orderPrintMode', null, ['report' => '주문내역서', 'customerReport' => '주문내역서 (고객용)', 'reception' => '간이영수증', 'particular' => '거래명세서', 'taxInvoice' => '세금계산서'], null, null, '=인쇄 선택=', null) ?>
                    <input type="button" onclick="order_print_popup($('#orderPrintMode').val(), 'frmOrderPrint', 'frmOrderStatus', 'statusCheck[', <?=$isProvider ? 'true' : 'false'?>);" value="프린트" class="btn btn-white btn-icon-print"/>
                </div>
            </div>
        </div>
    </div>
    <?php include $layoutOrderList;// 주문리스트 ?>

    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 주문을</span>
            <?php echo gd_select_box('orderStatusBottom', 'orderStatusBottom', $selectBoxOrderStatus, null, null, '=주문상태='); ?>
            <button type="submit" class="btn btn-white js-order-status" />일괄처리</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchOrder" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>" data-state-code ="<?=$currentStatusCode?>" data-target-list-form="frmOrderStatus" data-target-list-sno="statusCheck" >엑셀다운로드</button>
        </div>
    </div>
</form>

<div class="text-center"><?= $page->getPage(); ?></div>

<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/orderList.js?ts=<?=time();?>"></script>
