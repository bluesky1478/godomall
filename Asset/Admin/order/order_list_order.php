<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<?php include $layoutOrderSearchForm;// 검색 및 프린트 폼 ?>

<form id="frmOrderStatus" action="./order_ps.php" method="post">
    <input type="hidden" name="mode" value="combine_status_change"/>
    <input type="hidden" id="orderStatus" name="changeStatus" value=""/>

    <div class="table-action mgt0 mgb0">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 주문을</span>
            <?php
            foreach ($status as $key => $val) {
                if (in_array(substr($key, 0, 1), $statusStandardCode[$currentStatusCode]) === true && in_array(substr($key, 0, 1), $statusExcludeCd) === false) {
                    $arrOrderStatus[$key] = $val;
                }
            }
            echo gd_select_box('orderStatusTop', null, $arrOrderStatus, null, null, '=주문상태=');
            ?>
            <button type="submit" class="btn btn-white js-order-status" />일괄처리</button>
            <button type="button" class="btn btn-white js-status-cancel" />취소처리</button>
        </div>
        <div class="pull-right">
            <input type="button" value="입금요청 SMS발송" class="btn btn-white js-sms-send" data-type="select" data-opener="order" data-target-selector="input[name*=statusCheck]:checked"/>
        </div>
    </div>

    <?php include $layoutOrderList;// 주문리스트 ?>

    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 주문을</span>
            <?php echo gd_select_box('orderStatusBottom', 'orderStatusBottom', $arrOrderStatus, null, null, '=주문상태='); ?>
            <button type="submit" class="btn btn-white js-order-status" />일괄처리</button>
            <button type="button" class="btn btn-white js-status-cancel" />취소처리</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchOrder" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>" data-state-code ="<?=$currentStatusCode?>" data-target-list-form="frmOrderStatus" data-target-list-sno="statusCheck" >엑셀다운로드</button>
        </div>
    </div>
</form>

<div class="text-center"><?= $page->getPage(); ?></div>

<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/orderList.js?ts=<?=time();?>"></script>
