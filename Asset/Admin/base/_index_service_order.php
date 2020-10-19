<?php if ($mainServiceAccess === true && $mainServiceOrderAccess == '') return; ?>
<!-- 주문관리 -->
<div class="main-section order-management">
    <div class="table-title btm-line">
        <span class="gd-help-manual">주문관리</span>
        <div class="pull-right"><a href="#" class="btn btn-icon-setting btn-sm btn-white js-setting-order" data-role="orderSetting">세팅</a></div>
    </div>

    <div class="main-section-inner">

        <?php if (empty($eachOrderStatus) === false) { ?>
            <?php
            $orderList = [];
            $orderList[] = '<ol class="content order list-unstyled reform">';
            $eachOrderStatus = array_merge($eachOrderStatus, array_fill(0, (8 - count($eachOrderStatus)), ''));
            foreach ($eachOrderStatus as $index => $orderStatus) {
                if (empty($orderStatus)) {
                    $orderList[] = '<li class="order-management-no-item' . $orderStatus['active'] . '"></li>';
                } else {
                    $orderList[] = '<li class="' . $orderStatus['active'] . '">';
                    $orderList[] = '<a href="' . $orderStatus['link'] . '">';
                    $orderList[] = '<span class="status">' . $orderStatus['name'] . '</span>';
                    $orderList[] = '<div class="order-list-val">' . number_format($orderStatus['count']) . '</div>';
                    $orderList[] = '</a>';
                    $orderList[] = '</li>';
                }
                if ((($index + 1) % 4) === 0) {
                    $orderList[] = '</ol><ol class="content order list-unstyled reform">';
                }
            }
            array_pop($orderList);
            $orderList[] = '</ol>';
            echo implode('', $orderList);
            ?>
        <?php } else { ?>
            <p class="no-data">주문내역이 없습니다.</p>
        <?php } ?>
    </div>
    <div class="clear-both"></div>
</div>
