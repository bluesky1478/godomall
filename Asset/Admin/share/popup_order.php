<?php include $layoutOrderSearchForm;// 검색 및 프린트 폼
use Component\Goods\AddGoodsAdmin;
use Component\Goods\Goods;
use Component\Naver\NaverPay;
use Component\Order\OrderAdmin;

$order = new OrderAdmin();
?>

<div class="table-responsive" style="width:100%;height:350px;overflow-x:auto;overflow-y:auto">
    <table class="table table-rows order-list">
        <thead>
        <tr>
            <th class="width3p">선택</th>
            <th class="width3p">번호</th>
            <th class="width5p">주문일시</th>
            <th class="width10p">주문번호</th>
            <th>주문상품</th>
            <th class="width3p">수량</th>
            <th class="width7p">판매가</th>
            <th class="width7p">처리상태</th>
            <th class="width10p">송장번호</th>
            <th class="width10p">공급사</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $naverPay = new NaverPay();
        if (empty($data) === false && is_array($data)) {
            $sortNo = 1; // 번호 설정
            $goods = new Goods();
            $addGoods = new AddGoodsAdmin();
            foreach ($data as $key => $val) {
                $goodsType = $val['goodsType'];
                $orderNo = $val['orderNo'];
                if($goodsType == 'addGoods') {
                    $addGoodsData = $addGoods->getDataAddGoods($val['goodsNo'])['data'];
                    $goodsPrice = $val['goodsCnt'] * ($addGoodsData['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                }
                else {
                    $goodsData = $goods->getGoodsInfo($val['goodsNo']);
                    $goodsPrice = $val['goodsCnt'] * ($goodsData['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                }

                $settlePrice = $val['settlePrice'];

                if ($val['orderChannelFl'] == 'naverpay') {
                    $checkoutData = json_decode($val['checkoutData'], true);
                    if ($naverPay->getStatusText($checkoutData)) {
                        $naverImg = sprintf("<img src='%s' > ", \UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www());
                        $val['orderStatusStr'] .= '<br>(' . $naverImg . $naverPay->getStatusText($checkoutData) . ')';
                    }
                }
                ?>
                <tr class="text-center" id="tbl_add_order_<?= $val['sno'] ?>">
                    <td>
                        <input type="<?= $checkType ?>" name="orderGoodsSno[]" value="<?= $val['sno']; ?>"/>
                        <input type="hidden" name="goodsType" value="<?=$goodsType?>">
                    </td>
                    <td class="font-num">
                        <small><?= $page->idx--; ?></small>
                    </td>
                    <td class="font-date nowrap"><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?>
                        <span style="display:none" class="itemRegDt"><?= gd_date_format('Y-m-d', $val['regDt']) ?></span>
                    </td>
                    <td>
                        <a href="../order/order_view.php?orderNo=<?= $orderNo; ?>" title="주문번호" target="_blank" class="btn btn-link font-num itemOrderNo"><?= $orderNo; ?></a>
                        <?php if ($val['orderChannelFl'] == 'naverpay') { ?>
                            <p>
                                <a href="../order/order_view.php?orderNo=<?= $orderNo; ?>" title="주문번호" target="_blank" class="btn btn-link font-num itemOrderNo"><img
                                        src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www() ?>"/> <?= $val['apiOrderNo']; ?></a>
                            </p>
                        <?php } else if ($val['orderChannelFl'] == 'payco') { ?>
                            <img src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'payco.gif')->www() ?>"/>
                        <?php } ?>
                    </td>
                    <td class="text-left">
                        <input type="hidden" name="goodsNo" value="<?= $val['goodsNo'] ?>">
                        <?php if($val['goodsType'] == 'addGoods') {?>
                        <span class="label label-default">추가</span>
                        <?php }?>
                        <a href="javascript:void();" class="one-line bold mgb5" title="주문 상품명"
                           onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);">
                            <span class="itemGoodsName"><?= gd_html_cut($val['goodsNm'], 46, '..'); ?></span></a>
                        <?php
                        // 옵션 처리
                        if (empty($val['optionInfo']) === false) {
                            echo '<div class="itemOptionInfo" title="상품 옵션">';
                            $optionInfo = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo'], true));
                            foreach ($optionInfo as $option) {
                                $tmpOption[] = $option[0] . ':' . $option[1];
                            }
                            echo implode(', ', $tmpOption);
                            echo '</div>';
                            unset($tmpOption);
                        }

                        // 텍스트 옵션 처리
                        if (empty($val['optionTextInfo']) === false) {
                            echo '<div class="itemOptionInfo2" title="텍스트 옵션">';
                            $optionTextInfo = json_decode(gd_htmlspecialchars_stripslashes($val['optionTextInfo'], true));
                            foreach ($optionTextInfo as $option) {
                                $tmpOption[] = $option[0] . ':' . $option[1];
                            }
                            echo implode(', ', $tmpOption);
                            echo '</div>';
                            unset($tmpOption);
                        }
                        ?>
                        <span class="itemImage" style="display:none">
                            <?php if($val['goodsType'] == 'addGoods') {?>
                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['imageStorage'], 30, $val['goodsNm']); ?>
                    <?php } else {?>
                  <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                    <?php }?>
                    </span>
                    </td>
                    <td class="itemGoodsCnt"><?= number_format($val['goodsCnt']); ?></td>
                    <td class="itemGoodsPrice"><?= gd_currency_display($goodsPrice); ?></td>
                    <td class="itemOrderStatus">
                        <?= $order->getOrderStatusAdmin($val['orderStatus']) ?>
                    </td>
                    <td class="itemInvoiceNo">
                        <?= $val['invoiceNo'] ?>
                    </td>
                    <td class="itemCompanyNm">
                        <?= $val['companyNm'] ?>
                    </td>

                </tr>
                <?php
            }

        } else {
            ?>
            <tr>
                <td colspan="20" class="no-data">
                    검색된 주문이 없습니다.
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>


<div class="text-center"><?= $page->getPage(); ?></div>


<div style="width: 100%;height: 140px;padding: 30px 0;" class="center">
    <input type="button" value="취소" id="goodsChoiceCancel" class="btn btn-lg btn-white" onclick="self.close();" style="font-weight: bold;margin-right: 10px"/>
    <?php if ($checkCheckboxType) { ?>
        <input type="button" value="선택완료" id="orderChoiceConfirm" class="orderChoiceConfirm btn btn-lg btn-black"/>
    <?php } else { ?>
        <input type="button" value="확인" id="orderRadioChoiceConfirm" class="orderRadioChoiceConfirm btn btn-lg btn-black"/>
    <?php } ?>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.orderRadioChoiceConfirm').bind('click', function () {
            if ($('input[type=radio][name="orderGoodsSno[]"]:checked').length < 1) {
                alert('주문을 선택해주세요.');
                return;
            }

            var resultJson = {
                "info": []
            };

            var checkedNo = $('input[type=radio][name="orderGoodsSno[]"]:checked').val();
            var orderNo = $('#tbl_add_order_' + checkedNo).find('.itemOrderNo').text();
            var goodsNo = $('#tbl_add_order_' + checkedNo).find('input[name=goodsNo]').val();
            var goodsImgageSrc = $('#tbl_add_order_' + checkedNo).find('.itemImage img').attr('src');
            var orderStatus = $('#tbl_add_order_' + checkedNo).find('.itemOrderStatus').text();
            var goodsName = $('#tbl_add_order_' + checkedNo).find('.itemGoodsName').text();
            var _arrOptionName = new Array();
            if($('#tbl_add_order_' + checkedNo).find('.itemOptionInfo').length>0) {
                _arrOptionName.push($('#tbl_add_order_' + checkedNo).find('.itemOptionInfo').text());
            }
            if($('#tbl_add_order_' + checkedNo).find('.itemOptionInfo2').length>0) {
                _arrOptionName.push($('#tbl_add_order_' + checkedNo).find('.itemOptionInfo2').text());
            }
            var optionName = _arrOptionName.join('<br>');
            var regDt = $('#tbl_add_order_' + checkedNo).find('.itemRegDt').html();
            var goodsPrice = $('#tbl_add_order_' + checkedNo).find('.itemGoodsPrice').text();
            var goodsType = $('#tbl_add_order_' + checkedNo).find('input[name=goodsType]').val();

            resultJson.info.push({
                "orderGoodsNo": checkedNo,
                "goodsNo": goodsNo,
                "orderNo": orderNo,
                "regDt": regDt,
                "goodsPrice": goodsPrice,
                "goodsImgageSrc": goodsImgageSrc,
                "goodsName": goodsName,
                "optionName": optionName,
                "orderStatus": orderStatus,
                "goodsType": goodsType,
            });
            console.log(resultJson);
            opener.setAddOrder(resultJson);
            self.close();
        })

        $('input').keydown(function (e) {
            if (e.keyCode == 13) {
                $("#frmSearchBase").submit();
                return false
            }
        });

        $('.pagination li a').click(function () {

            $("input[name='page']").val($(this).data('page'));
            $('.search-goods-btn').click();
        });

    });

    $('select[name=\'pageNum\']').change(function () {
        $('.search-goods-btn').click();
    });

    $('select[name=\'sort\']').change(function () {
        $('.search-goods-btn').click();
    })



    //-->
</script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/orderList.js"></script>
