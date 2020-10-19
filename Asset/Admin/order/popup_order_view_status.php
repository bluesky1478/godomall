<div class="page-header">
    <h3><?=$subject?></h3>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <span class="flag flag-16 flag-<?= $data['domainFl']; ?>"></span>
        <?= $data['mallName']; ?>
        <?= str_repeat('&nbsp', 6); ?>

        주문번호 : <span><?= $data['orderNo']; ?></span>
        <?= str_repeat('&nbsp', 2); ?>
        <?php if ($data['orderChannelFl'] == 'naverpay') { ?>
            <span><img src="<?=UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www()?>" /> <?= $data['apiOrderNo']; ?></span>
        <?php } ?>
        <?= str_repeat('&nbsp', 6); ?>
        주문일시 : <span><?= gd_date_format('Y년 m월 d일 H시 i분', gd_isset($data['regDt'])); ?></span>
    </div>
</div>

<!-- 결제정보 -->
<?php if(!$isProvider){ ?>
    <div class="mgt20">
        <div class="table-title">
            <span class="gd-help-manual mgt30">결제 정보</span>
        </div>

        <table class="table table-rows">
            <thead>
            <tr>
                <th>상품 판매금액</th>
                <th>총 배송비</th>
                <th>총 할인금액</th>
                <th>총 부가결제금액</th>
                <th>총 결제금액</th>
                <th>총 적립금액</th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <td class="center"><?=$commonData['totalGoodsPriceText']?></td>
                <td class="center"><?=$commonData['totalDeliveryChargeText']?></td>
                <td class="center"><?=$commonData['totalDcPriceText']?></td>
                <td class="center"><?=$commonData['totalUseAddedPriceText']?></td>
                <td class="center"><?=$commonData['settlePriceText']?></td>
                <td class="center"><?=$commonData['totalMileageText']?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <!-- 결제정보 -->
<?php } ?>

<!-- 주문상품 -->
<div class="mgt20">
    <div class="table-title">
        <span class="gd-help-manual mgt30">주문 상품</span>
    </div>

    <?php include $layoutOrderViewStatusChangeList; ?>
</div>
<!-- 주문상품 -->

<script type="text/javascript">
    <!--

    //-->
</script>
