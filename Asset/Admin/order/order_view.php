<!-- 프린트 출력을 위한 form -->
<form id="frmOrderPrint" name="frmOrderPrint" action="" method="post" class="display-none">
    <input type="checkbox" name="orderNo" value="<?= gd_isset($data['orderNo']); ?>" checked="checked"/>
    <input type="hidden" name="orderPrintCode" value=""/>
    <input type="hidden" name="orderPrintMode" value=""/>
</form>

<!-- // 프린트 출력을 위한 form -->

<!-- 주문상태 일괄 변경을 위한 form -->
<form id="frmOrderStatus" method="post" action="../order/order_ps.php"></form>
<!-- //주문상태 일괄 변경을 위한 form -->

<form id="frmOrder" name="frmOrder" action="./order_ps.php" method="post">
    <input type="hidden" name="mode" value=""/>
    <input type="hidden" name="orderNo" value="<?= $data['orderNo'] ?>"/>
    <input type="hidden" name="info[sno]" value="<?= gd_isset($data['infoSno']); ?>" />
    <input type="hidden" name="info[data]" value="" />
    <input type="hidden" name="no" value="">
    <input type="hidden" name="oldManagerId" value="">

    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <span class="flag flag-16 flag-<?= $data['domainFl']; ?>"></span>
            <?= $data['mallName']; ?>
            <?= str_repeat('&nbsp', 6); ?>

            주문번호 : <span><?= $data['orderNo']; ?></span>

            <!-- 묶음배송 주문리스트 -->
            <?php if(count($packetOrderList) > 0){ ?>
                <div class="dropdown display-inline">
                    <button type="button" class="dropdown-toggle btn btn-sm btn-black" style="color: #FFFFFF !important; " data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">묶음배송</button>
                    <ul class="dropdown-menu dropdown-packet-area" aria-labelledby="packetPresentation">
                        <li class="dropdown-packet-area-header">묶음배송 주문번호</li>
                        <?php
                        foreach($packetOrderList as $key => $packetOrderNo){
                        ?>
                        <li class="dropdown-item">
                            <a href="../order/order_view.php?orderNo=<?=$packetOrderNo?>" target="_blank">
                                <span class="dropdown-item-val text-primary"><?=$packetOrderNo?></span>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
            <!-- 묶음배송 주문리스트 -->

            <?= str_repeat('&nbsp', 2); ?>
            <?php if ($data['orderChannelFl'] == 'naverpay') { ?>
                <span><img src="<?=UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www()?>" /> <?= $data['apiOrderNo']; ?></span>
            <?php } else if ($data['orderChannelFl'] == 'etc') { ?>
                <span><img src="<?=UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'etc.gif')->www()?>" /> <?= $data['apiOrderNo']; ?></span>
            <?php } else {} ?>

            <?= str_repeat('&nbsp', 6); ?>
            주문일시 : <span><?= gd_date_format('Y년 m월 d일 H시 i분', gd_isset($data['regDt'])); ?></span>
            <?= str_repeat('&nbsp', 6); ?>
            주문유형 : <span><?= $data['orderTypeFlNm']; ?></span>
            <div class="pull-right">
                <div class="form-inline">
                    <?= gd_select_box('orderPrintMode', null, ['report' => '주문내역서', 'customerReport' => '주문내역서 (고객용)', 'reception' => '간이영수증', 'particular' => '거래명세서', 'taxInvoice' => '세금계산서'], null, null, '=인쇄 선택=', null, 'form-control input-sm') ?>
                    <input type="button" onclick="order_print_popup($('#orderPrintMode').val(), 'frmOrderPrint', 'frmOrderPrint', 'orderNo', <?=$isProvider ? 'true' : 'false'?>);" value="인쇄" class="btn btn-sm btn-white"/>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$isProvider && $data['orderChannelFl']!='naverpay' && $data['orderChannelFl']!='etc') { ?>
        <div class="table-dashboard">
            <table class="table table-cols">
                <colgroup>
                    <col style="width: 33%;" />
                    <col style="width: 34%;" />
                    <col style="width: 33%;" />
                </colgroup>
                <thead>
                <tr>
                    <th>총 결제금액</th>
                    <th>총 취소금액</th>
                    <th>총 환불금액</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="bln">
                        <strong><?= gd_currency_display(gd_isset($data['dashBoardPrice']['settlePrice'])); ?></strong>
                        <ul class="list-unstyled">
                            <li><strong>결제 예정금액</strong><span><?= gd_currency_display(gd_isset($data['dashBoardPrice']['dueSettlePrice'])); ?></span></li>
                        </ul>
                    </td>
                    <td>
                        <strong><?= gd_currency_display(gd_isset($data['dashBoardPrice']['cancelPrice'])); ?></strong>
                        <ul class="list-unstyled">
                            <li><strong>&nbsp;</strong><span></span></li>
                        </ul>
                    </td>
                    <td>
                        <strong><?= gd_currency_display(gd_isset($data['dashBoardPrice']['refundPrice'])); ?></strong>
                        <ul class="list-unstyled">
                            <?php if (!$isProvider) { // 공급사인 경우 링크 제거?>
                                <?php if((int)$data['dashBoardRefundHandleSno'] > 0){ ?>
                                    <li>
                                        <strong>
                                            <?php if (gd_date_format('Y-m-d H:i', $data['regDt']) < gd_date_format('Y-m-d H:i', '2019-07-10 07:40:00')) { //배포일 ?>
                                            <a href="./refund_view.php?orderNo=<?= $data['orderNo'] ?>&handleSno=<?= $data['dashBoardRefundHandleSno'] ?>&isAll=1&statusFl=1" target="_blank" class="btn-link-blue js-order-refund">환불 예정금액<img src="/admin/gd_share/img/icon_grid_open.png" alt="팝업창열기" class="hand mgl5" border="0"></a>
                                            <?php } else { ?>
                                            <a href="./refund_view_new.php?orderNo=<?= $data['orderNo'] ?>&handleSno=<?= $data['dashBoardRefundHandleSno'] ?>&isAll=1&statusFl=1" target="_blank" class="btn-link-blue js-order-refund-new">환불 예정금액<img src="/admin/gd_share/img/icon_grid_open.png" alt="팝업창열기" class="hand mgl5" border="0"></a>
                                            <?php } ?>
                                        </strong>
                                        <span><?= gd_currency_display(gd_isset($data['dashBoardPrice']['dueRefundPrice'])); ?></span>
                                    </li>
                                <?php } else { ?>
                                    <li>
                                        <strong>환불 예정금액</strong>
                                        <span><?= gd_currency_display(gd_isset($data['dashBoardPrice']['dueRefundPrice'])); ?></span>
                                    </li>
                                <?php } ?>
                            <?php } else { ?>
                                <li><strong>환불 예정금액</strong><span><?= gd_currency_display(gd_isset($data['dashBoardPrice']['dueRefundPrice'])); ?></span>
                                </li>
                            <?php } ?>
                        </ul>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <div class="table-title">
        <span class="gd-help-manual mgt30">상품정보</span>
    </div>
    <div id="tabOrderStatus" clear>
        <ul class="nav nav-tabs mgb30" role="tablist">
            <li role="presentation" class="<?=$data['normalGoods']['active'] == 'order' ? 'active' : ''?>">
                <a href="#tab-status-order" data-toggle="tab">주문내역 (<strong><?=number_format($data['normalGoods']['ordercnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
            </li>
            <?php if (!$isProvider) { ?>
                <li role="presentation" class="<?=$data['normalGoods']['active'] == 'cancel' ? 'active' : ''?>">
                    <a href="#tab-status-cancel" data-toggle="tab">취소내역 (<strong><?=number_format($data['normalGoods']['cancelcnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
                </li>
            <?php } ?>
            <li role="presentation" class="<?=$data['normalGoods']['active'] == 'exchange' ? 'active' : ''?>">
                <a href="#tab-status-exchange" data-toggle="tab">교환내역 (<strong><?=number_format($data['normalGoods']['exchangecnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
            </li>
            <li role="presentation" class="<?=$data['normalGoods']['active'] == 'back' ? 'active' : ''?>">
                <a href="#tab-status-back" data-toggle="tab">반품내역 (<strong><?=number_format($data['normalGoods']['backcnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
            </li>
            <li role="presentation" class="<?=$data['normalGoods']['active'] == 'refund' ? 'active' : ''?>">
                <a href="#tab-status-refund" data-toggle="tab">환불내역 (<strong><?=number_format($data['normalGoods']['refundcnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
            </li>
            <?php if (!$isProvider) { ?>
                <li role="presentation" class="<?=$data['normalGoods']['active'] == 'fail' ? 'active' : ''?>">
                    <a href="#tab-status-fail" data-toggle="tab">결제 중단/실패 내역 (<strong><?=number_format($data['normalGoods']['failcnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
                </li>
            <?php } ?>
        </ul>
        <div class="tab-content loading">
            <div class="table-action" style="margin-bottom: 0px !important;">
                <?php
                if((!$isProvider) && $data['orderChannelFl'] === 'shop'){
                ?>
                <div class="pull-left form-inline" style="height: 26px;">
                    <button type="button" class="btn btn-sm btn-white js-layer-enuri">운영자 추가 할인</button>
                </div>
                <?php
                }
                ?>

                <div class="pull-right form-inline" style="height: 26px;">
                    <div class="display-inline-block mgr10" style="height: 26px;">
                        <?php if($ieRowRank === true){ ?>
                            <span class="btn-group mgl30" style="margin-bottom: 23px;">
                                <button type="button" class="btn btn-sm btn-black active js-convert-exchange" data-use-mall="false">기준몰</button>
                                    <?php if ($gGlobal['isUse'] && $data['mallSno'] > 1) { ?>
                                        <button type="button" class="btn btn-sm btn-white js-convert-exchange active" data-use-mall="true">해외상점</button>
                                    <?php } ?>
                            </span>
                        <?php } else { ?>
                            <label class="switch-button-label">
                                <input type="checkbox" class="js-convert-exchange active" data-use-mall="false" />
                                <span class="slider-button round">기준몰</span>
                            </label>
                        <?php } ?>
                    </div>

                    <div class="display-inline-block">
                        <button type="button" class="js-layer-register btn btn-sm btn-black" id="orderGridConfigBtn" data-type="order_grid_config" data-order-grid-mode="<?='view_'.$data['normalGoods']['active']?>" style="margin-bottom: 25px;">조회항목설정</button>
                    </div>
                </div>
            </div>

            <div role="tab-status-order" class="tab-pane <?=$data['normalGoods']['active'] == 'order' ? 'in active' : ''?>" id="tab-status-order"></div>
            <?php if (!$isProvider) { ?>
                <div role="tab-status-cancel" class="tab-pane <?=$data['normalGoods']['active'] == 'cancel' ? 'in active' : ''?>" id="tab-status-cancel"></div>
            <?php } ?>
            <div role="tab-status-exchange" class="tab-pane <?=$data['normalGoods']['active'] == 'exchange' ? 'in active' : ''?>" id="tab-status-exchange"></div>
            <div role="tab-status-back" class="tab-pane <?=$data['normalGoods']['active'] == 'back' ? 'in active' : ''?>" id="tab-status-back"></div>
            <div role="tab-status-refund" class="tab-pane <?=$data['normalGoods']['active'] == 'refund' ? 'in active' : ''?>" id="tab-status-refund"></div>
            <div role="tab-status-fail" class="tab-pane <?=$data['normalGoods']['active'] == 'fail' ? 'in active' : ''?>" id="tab-status-fail"></div>
        </div>
    </div>

    <?php
    $claimElementDisabled = '';
    if($data['orderChannelFl'] == 'naverpay' ) {
        $claimElementDisabled = 'disabled';
    }
    if (empty($data['gift']) === false) {
        ?>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col class="width-md"/>
                <col/>
                <col class="width-xl"/>
                <col class="width-md"/>
            </colgroup>
            <tbody>
            <tr>
                <th class="text-left" rowspan="<?=count($data['gift']) + 2;?>"><span class="gd-help-manual">사은품 정보</span></th>
                <th class="text-center">이미지</th>
                <th class="text-center">사은품명</th>
                <th class="text-center">사은품 지급조건명</th>
                <th class="text-center">수량</th>
                <th class="text-center">비고</th>
            </tr>
            <?php
            $total = 0;
            foreach ($data['gift'] as $key => $val) {
                $total += $val['giveCnt'];
                ?>
                <tr class="text-center">
                    <td><?= html_entity_decode($val['imageUrl']); ?></td>
                    <td><?= $val['giftNm']; ?></td>
                    <td><?= $val['presentTitle']; ?></td>
                    <td><?= number_format($val['giveCnt']); ?></td>
                    <td>-</td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <th colspan="3" class="text-right">합계 수량</th>
                <th class="text-center"><?=number_format($total)?></th>
                <th></th>
            </tr>
            </tbody>
        </table>
        <?php
    }
    ?>

    <div class="mgb30">
        <?php if($data['orderChannelFl'] == 'naverpay'){?>
            <div class="notice-danger">
                환불/반품/교환 상세정보는 네이버페이센터에서 확인하시기 바랍니다.
                <a href="https://admin.pay.naver.com/" target="_blank">네이버페이센터 바로가기></a></div>
            <div class="notice-info">
                "화불배송/방문수령/퀵배송/기타"배송방식의 주문은, 네이버페이 정책에 따라 송장번호 입력이 비활성화 처리됩니다.
            </div>
            <?php if($showNaverPayReload){?>
                <div class="notice-info">
                    주문에 노출되지 않는 상품이 있는 경우 상품주문번호 조회를 해주세요. [
                    <a href="javascript:void(0)" class="js-btn-naverpay-reload">네이버페이 상품주문번호 조회</a>]
                </div>
            <?php }?>
            <div style="height:50px"></div>
        <?php }?>

        <?php if ($data['mallSno'] > DEFAULT_MALL_NUMBER) { ?>
            <span class="notice-danger" style="font-size:12px;">멀티상점 주문의 경우 PG부분 결제취소가 불가하므로 전체 반품/환불처리만 가능합니다.</span>
        <?php } ?>
        <?php if($data['orderChannelFl'] == 'naverpay') {?>
            <?php if($data['statusMode'] == 'o') {?>
                <span class="notice-danger">입금대기상태의 네이버페이 주문은 클래임 접수/주문상태변경이 불가능합니다.</span>
            <?php }else if($data['statusMode'] == 'd') {?>
                <span class="notice-danger">배송중/배송완료 상태의 네이버페이 주문은 클래임 중 반품접수만 가능합니다.</span>
            <?php }else if($data['statusMode'] == 's') {?>
                <span class="notice-danger">구매확정 상태의 네이버페이 주문은 클래임 접수/주문상태변경이 불가능합니다.</span>
            <?php }?>
        <?php } elseif ($data['orderChannelFl'] == 'payco') {?>
            <span class="payco-notice notice-danger display-none">페이코를 통한 바로이체 결제건의 부분취소는, 주문취소 상태만 연동되며 실제환불은 별도로 구매자에게 지급하셔야 합니다.</span>
        <?php }?>
    </div>

    <div class="table-title gd-help-manual">
        <?php if (!$isProvider) { ?>취소/<?php } ?>교환/반품/환불 정보
    </div>
    <div id="tabClaimStatus">
        <ul class="nav nav-tabs mgb30" role="tablist">
            <?php if (!$isProvider) { ?>
                <li role="presentation" class="<?=$data['claimGoods']['active'] == 'cancel' ? 'active' : ''?>">
                    <a href="#tab-claim-cancel" data-toggle="tab">취소정보 (<strong><?=number_format($data['claimGoods']['cancelcnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
                </li>
            <?php } ?>
            <li role="presentation" class="<?=$data['claimGoods']['active'] == 'exchange' ? 'active' : ''?>">
                <a href="#tab-claim-exchange" data-toggle="tab">교환정보 (<strong><?=number_format($data['claimGoods']['exchangecnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
            </li>
            <li role="presentation" class="<?=$data['claimGoods']['active'] == 'back' ? 'active' : ''?>">
                <a href="#tab-claim-back" data-toggle="tab">반품정보 (<strong><?=number_format($data['claimGoods']['backcnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
            </li>
            <li role="presentation" class="<?=$data['claimGoods']['active'] == 'refund' ? 'active' : ''?>">
                <a href="#tab-claim-refund" data-toggle="tab">환불정보 (<strong><?=number_format($data['claimGoods']['refundcnt']['orderGoodsCnt'])?></strong> 건)</strong></a>
            </li>
        </ul>
        <div class="tab-content loading">
            <?php if (!$isProvider) { ?>
                <div role="tab-claim-cancel" class="tab-pane <?=$data['claimGoods']['active'] == 'cancel' ? 'in active' : ''?>" id="tab-claim-cancel"></div>
            <?php } ?>
            <div role="tab-claim-exchange" class="tab-pane <?=$data['claimGoods']['active'] == 'exchange' ? 'in active' : ''?>" id="tab-claim-exchange"></div>
            <div role="tab-claim-back" class="tab-pane <?=$data['claimGoods']['active'] == 'back' ? 'in active' : ''?>" id="tab-claim-back"></div>
            <div role="tab-claim-refund" class="tab-pane <?=$data['claimGoods']['active'] == 'refund' ? 'in active' : ''?>" id="tab-claim-refund"></div>
        </div>
    </div>

    <?php
    // 에스크로 배송등록
    if (gd_isset($settle['prefix']) == 'e' && in_array($data['statusMode'], $order->statusReceiptPossible) && $pgEscrowConf['delivery'] == 'y') {
        ?>
        <div class="table-title gd-help-manual">에스크로 진행 안내</div>
        <?php
        // 에스크로 구매확인 여부
        if (empty($data['escrowConfirmFl']) === true) {
            ?>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>에스크로 안내</th>
                    <td class="input_area note">
                        <?php
                        if ($data['escrowDeliveryFl'] == 'n') {
                            echo '<span class="notice-info"> 해당 주문은 에스크로 주문입니다. 배송처리시 자동으로 에스크로 배송등록이 됩니다.</span>';
                        } else {
                            echo '<span class="notice-info"> 에스크로 배송등록이 완료 되었습니다.</span>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php
        } else {
            ?>
            <div class="table-title gd-help-manual">에스크로 구매결정</div>
            <div>
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>결정 여부</th>
                        <td class="input_area note">
                            <?php
                            if ($data['escrowConfirmFl'] == 'accept') {
                                echo '<span class="notice-info"> 에스크로 구매 결정이 승인 처리 되었습니다.</span>';
                            } else if ($data['escrowConfirmFl'] == 'reject') {
                                if ($data['escrowDenyFl'] == 'y') {
                                    echo '<span class="notice-info"> 에스크로 구매 거절 처리가 완료 되었습니다.</span>';
                                } else {
                                    echo '<span class="notice-info"> 고객이 에스크로 구매 거절을 하였습니다.</span>';
                                    if ($pgEscrowConf['deny'] == 'y') {
                                        echo '<span class="notice-danger notice-info"> 아래 &quot;에스크로 거절확인&quot;을 눌러 주세요. &quot;에스크로 거절확인&quot; 이후 배송 완료 처리 또는 반품 처리를 하시기 바랍니다.</span>';
                                    } else {
                                        echo '<span class="notice-danger notice-info"> PG 관리자 모드에서 처리하시기 바랍니다.</span>';
                                    }
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <?php if ($data['escrowConfirmFl'] == 'reject' && $data['escrowDenyFl'] != 'y' && $pgEscrowConf['deny'] == 'y') { ?>
                        <tr>
                            <th>에스크로 거절확인</th>
                            <td><input type="button" onclick="escrow_deny_register();" value="에스크로 거절확인"  class="btn btn-sm btn-gray"/></span></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <?php
        }
    }
    ?>
    <?php if (!$isProvider) { ?>
    <div class="row">
        <!-- 쿠폰/할인/혜택 정보 -->
        <div class="col-md-12">
            <div class="table-title gd-help-manual">
                쿠폰/할인/혜택 정보
            </div>
            <?php
            include $layoutOrderViewBenefitInfo;
            ?>
            <!-- 쿠폰/할인/혜택 정보 -->
        </div>
    </div>
    <?php } ?>
    <div class="row">
        <?php if (!$isProvider) { ?>
            <!-- 결제정보 -->
            <div class="col-xs-6">
                <div class="table-title gd-help-manual">
                    결제정보
                    <?php if (empty($data['isDefaultMall']) === true) { ?>
                        <div class="pull-right"><small>(환율: <?=$data['currencyIsoCode']?> <?=$data['exchangeRate']?>)</small></div>
                    <?php } ?>
                </div>

                <div id="tabSettleStatus" style="border-top: 1px solid #888888; margin-bottom: 10px;">
                    <ul class="nav nav-tabs mgb0 mgt5" role="tablist" style="border-bottom: 0px !important;">
                        <li role="presentation" <?php if($originalDataCount < 1){ ?>class="active"<?php } ?>>
                            <a href="#tab-settle-first" data-toggle="tab">최초 결제정보</a>
                        </li>
                        <?php if($originalDataCount > 0){ ?>
                        <li role="presentation" class="active">
                            <a href="#tab-settle-last" data-toggle="tab">최종 결제정보</a>
                        </li>
                        <?php } ?>
                    </ul>
                    <div class="tab-content">
                        <div role="tab-settle-first" class="tab-pane in <?php if($originalDataCount < 1){ ?>active<?php } ?>" id="tab-settle-first">
                            <?php include $layoutOrderViewSettleFirstInfo; ?>
                        </div>
                        <?php if($originalDataCount > 0){ ?>
                        <div role="tab-settle-last" class="tab-pane active" id="tab-settle-last">
                            <?php include $layoutOrderViewSettleLastInfo; ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <!-- 결제정보 -->

            <div class="col-xs-6">
                <div class="table-title gd-help-manual">결제수단</div>
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>주문 채널</th>
                        <td>
                        <span class="text_emphasis">
                            <?=$data['orderChannelFl'] . (empty($data['trackingKey']) === false ? ' <span class="c-gdred">' . $channel['paycoShopping'] . '</span>' : ''); ?>
                        </span>
                        </td>
                    </tr>
                    <tr>
                        <th>결제 방법</th>
                        <td>
                        <span class="text_emphasis">
                            <?php if($data['orderChannelFl'] == 'naverpay'){
                                echo $data['checkoutData']['orderData']['PaymentMeans'];
                                if ($data['settleKind'] == 'fa' || $data['settleKind'] == 'gr') {
                                    echo  '(입금기한 : '.$data['checkoutData']['orderData']['PaymentDueDate'].')';
                                }
                            } else {?>
                                <?php if (gd_isset($settle['prefix']) == 'e') { ?>
                                    에스크로
                                <?php } ?>
                                <?php if (gd_isset($settle['prefix']) == 'f') { ?>
                                    간편결제
                                <?php } ?>
                                <?= gd_isset($settle['name']); ?>

                                <input type="button" class="btn btn-sm btn-gray js-pg-log" value="PG 로그" />
                            <?php }?>
                        </span>
                        </td>
                    </tr>
                    <?php if (gd_isset($data['settleKind']) == 'gb') { ?>
                        <tr>
                            <th>입금계좌</th>
                            <td>
                                <span id="bankAccount"><?= str_replace(STR_DIVISION, ' / ', gd_isset($data['bankAccount'])); ?></span>
                                <input type="button" class="btn btn-sm btn-gray js-bank-change" value="입금 은행 변경"/>
                            </td>
                        </tr>
                        <tr>
                            <th>입금자명</th>
                            <td><span id="bankSender"><?= gd_isset($data['bankSender']); ?></span></td>
                        </tr>
                    <?php } else { ?>
                        <?php if (empty($settle['settleReceipt']) === false) { ?>
                            <tr>
                                <th>전표 보기</th>
                                <td><input type="button" value="전표 보기" onclick="pg_receipt_view_admin('<?= $settle['settleReceipt']; ?>','<?= $data['orderNo']; ?>','<?= $data['cash']['sno']; ?>');" class="btn btn-sm btn-gray"/></td>
                            </tr>
                        <?php } ?>
                        <?php if (gd_isset($settle['method']) == 'c') { ?>
                            <tr>
                                <th>카드사명</th>
                                <td><?= gd_isset($data['pgSettleNm'][0]); ?></td>
                            </tr>
                            <?php if (gd_isset($data['pgSettleCd'][0]) != '' && gd_isset($data['pgSettleCd'][0]) != '0' && gd_isset($data['pgSettleCd'][0]) != '00') { ?>
                                <tr>
                                    <th>할부개월</th>
                                    <td><?php if (gd_isset($data['pgSettleCd'][1]) == '1') { ?>무이자 <?php } ?><?= gd_isset($data['pgSettleCd'][0]); ?>개월</td>
                                </tr>
                            <?php } ?>
                        <?php } else if (gd_isset($settle['method']) == 'c') { ?>
                            <tr>
                                <th>이체은행</th>
                                <td><?= gd_isset($data['pgSettleNm'][0]); ?></td>
                            </tr>
                        <?php } else if (gd_isset($settle['method']) == 'v') { ?>
                            <tr>
                                <th>입금계좌</th>
                                <td><?= gd_isset($data['pgSettleNm'][0]); ?> / <?= gd_isset($data['pgSettleNm'][1]); ?> / <?= gd_isset($data['pgSettleNm'][2]); ?></td>
                            </tr>
                            <tr>
                                <th>입금기한</th>
                                <td><?= gd_isset($data['pgSettleCd'][0]); ?></td>
                            </tr>
                        <?php } else if (gd_isset($settle['method']) == 'h') { ?>
                            <tr>
                                <th>통신사</th>
                                <td><?= gd_isset($data['pgSettleNm'][0]); ?></td>
                            </tr>
                            <?php if (empty($data['pgSettleCd'][0]) === false) { ?>
                                <tr>
                                    <th>휴대폰번호</th>
                                    <td><?= gd_isset($data['pgSettleCd'][0]); ?></td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                    <tr>
                        <th>주문확인일</th>
                        <td><span class="font-date"><?= gd_isset($data['regDt']); ?></span></td>
                    </tr>
                    <tr>
                        <th>결제확인일</th>
                        <td><span class="font-date"><?= gd_isset($data['paymentDt']); ?></span></td>
                    </tr>
                    <tr <?php echo $styleDisplayNone; ?>>
                        <th>현금영수증 신청여부</th>
                        <td class="form-inline">
                            <?php
                            if (gd_isset($data['receiptFl']) == 'n') {
                                if ($receipt['cashFl'] === 'n' && $receipt['taxFl'] === 'n') {
                                    echo '<span class="notice-danger">신청불가</span>';
                                } else {
                                    if ($receipt['cashFl'] === 'y') {
                                        if ($receipt['periodFl'] === 'y') {
                                            echo '<input type="button" class="btn btn-sm btn-gray" onclick="cash_receipt_register();" value="현금영수증 신청"/>';
                                        } else {
                                            echo '<span class="notice-info">현금영수증 발급불가 (결제완료 후 ' . $receipt['periodDay'] . ' 일 이내)</span>';
                                        }
                                    } else {
                                        echo '<span class="notice-info">현금영수증 사용안함</span>';
                                    }
                                }
                            }
                            // 현금영수증인 경우
                            else if (gd_isset($data['receiptFl']) == 'r') {
                                // 현금영수증 신청 정보
                                if ($data['cash']['statusFl'] == 'r') {
                                    echo '<input type="button" class="btn btn-sm btn-gray" onclick="cash_receipt_process(\'' . $data['orderNo'] . '\', \'' . $data['cash']['sno'] . '\');" value="현금영수증 신청 정보"/>';
                                }
                                // 현금영수증 영수증 보기
                                else {
                                    echo '<input type="button" class="btn btn-sm btn-gray" onclick="pg_receipt_view_admin(\'cash\', \'' . $data['orderNo'] . '\', \'' . $data['cash']['sno'] . '\');" value="현금영수증 보기"/>';
                                }
                            }
                            // 현금영수증 재발행
                            if($orderStatusChk){
                                echo '&nbsp <input type="button" class="btn btn-sm btn-gray" onclick="cash_receipt_reissue(\'' . $data['orderNo'] . '\', \'' . $data['cash']['sno'] . '\');" value="현금영수증 재발행"/>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr <?php echo $styleDisplayNone; ?>>
                        <th>세금계산서 신청여부</th>
                        <td class="form-inline">
                            <?php
                            if (gd_isset($data['receiptFl']) == 'n') {
                                if ($receipt['cashFl'] === 'n' && $receipt['taxFl'] === 'n') {
                                    echo '<span class="notice-danger">신청불가</span>';
                                } else {
                                    if ($receipt['taxFl'] === 'y') {
                                        echo '<input type="button" class="btn btn-sm btn-gray" onclick="tax_invoice_register();" value="세금계산서 신청"/>';
                                    } else {
                                        echo '<span class="notice-info">세금계산서 사용안함</span>';
                                    }
                                }
                            }
                            // 세금계산서인 경우
                            else if (gd_isset($data['receiptFl']) == 't') {
                                // 신청 단계인경우
                                if (gd_isset($data['tax']['statusFl']) == 'r') {
                                    ?>
                                    <input type="button" value="세금계산서 수정" class="btn btn-sm btn-red" onclick="tax_invoice_register('modify');"/>
                                    <?php
                                }
                                if (gd_isset($data['tax']['statusFl']) == 'y') {
                                    if ($taxInfo['godobill'] == 'y' && empty($data['tax']['godobillCd']) === false) {
                                        ?>
                                        고도빌 발급
                                        <?php
                                    } else {
                                        ?>
                                        <input type="button" value="세금계산서 출력" onclick="order_print_popup('taxInvoice', 'frmOrderPrint', 'frmOrderPrint', 'orderNo', <?=$isProvider ? 'true' : 'false'?>);"class="btn btn-gray btn-sm"/>
                                        <?php
                                    }
                                }
                            }

                            if((gd_isset($data['receiptFl']) == 'n' && $receipt['taxFl'] === 'y') || gd_isset($data['receiptFl']) == 't') { ?>
                            <input type="button" value="처리로그" class="btn btn-sm btn-gray" onclick="tax_log()"/>
                            <?php } ?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php } else { ?>
            <div class="col-xs-12">
                <div class="table-title gd-help-manual">결제정보</div>
                <div>
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-md"/>
                            <col class="width-md"/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th>상품 판매금액</th>
                            <td class="text-right">
                                <strong><?= gd_currency_display(gd_isset($data['totalGoodsPrice'])); ?></strong>
                            </td>
                            <td></td>
                        </tr>
                        <?php
                        // 환불건의 경우 기본배송비, 지역별 배송비를 구분하지 않으므로 통일하여 보여준다.
                        if((int)$data['claimGoods']['refundcnt']['orderGoodsCnt'] > 0) {
                            ?>
                            <tr>
                                <th>배송비</th>
                                <td class="text-right text-primary">
                                    (+) <?= gd_currency_display(gd_isset($data['totalDeliveryCharge'])); ?>
                                </td>
                                <td></td>
                            </tr>
                            <?php
                        } else {
                            ?>
                            <tr>
                                <th>배송비</th>
                                <td class="text-right text-primary">
                                    (+) <?= gd_currency_display(gd_isset($data['totalDeliveryPolicyCharge'])); ?>
                                </td>
                                <td class="input_area info_note">
                                    <?php
                                    if (isset($data['delivery']) === true) {
                                        $deliveryCharge = 0;
                                        $deliveryGoodsCharge = 0;
                                        if (empty($data['delivery']) === false) {
                                            foreach ($data['delivery'] as $key => $val) {
                                                echo '<div>● [' . $key . ']</div>';
                                                if ($val['deliveryConfFl'] == 'y') {
                                                    echo '<div>' . nl2br($val['deliveryConfLog']) . '</div>';
                                                }
                                                if ($val['deliveryGoodsFl'] == 'y') {
                                                    echo '<div>' . nl2br($val['deliveryGoodsLog']) . '</div>';
                                                }
                                                if ($val['deliveryFreeFl'] == 'y') {
                                                    echo '<div>' . nl2br($val['deliveryFreeLog']) . '</div>';
                                                }
                                                if ($val['deliveryCollectFl'] == 'y') {
                                                    echo '<div>' . nl2br($val['deliveryCollectLog']) . '</div>';
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>지역별 배송비</th>
                                <td class="text-right text-primary">
                                    (+) <?= gd_currency_display(gd_isset($data['totalDeliveryAreaCharge'])); ?>
                                </td>
                                <td></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <th>합계금액</th>
                            <td class="text-right">
                                <strong><?= gd_currency_display($data['totalGoodsPrice'] + $data['totalDeliveryCharge']); ?></strong>
                            </td>
                            <td>&nbsp;</td>
                        </tr>
                    </table>
                    </div>
                </div>
            <?php } ?>


            <div class="row">
                <!-- 주문자 정보 -->
                <div class="col-xs-12">
                    <div class="col-xs-6" id="layoutOrderViewOrderInfoArea">
                        <div class="table-title gd-help-manual">
                            <div class="flo-left">주문자 정보</div>
                            <div class="flo-right">
                                <button type="button" class="btn btn-red btn-sm js-orderInfoBtn">정보수정</button>
                                <button type="button" class="btn btn-red-box btn-sm js-orderInfoBtnSave js-orderViewInfoSave display-none" data-submit-mode="modifyOrderInfo">저장</button>
                            </div>
                        </div>

                        <div id="layoutOrderViewOrderInfo">
                            <?php include $layoutOrderViewOrderInfo; ?>
                        </div>
                        <div id="layoutOrderViewOrderInfoModify" class="display-none">
                            <?php include $layoutOrderViewOrderInfoModify; ?>
                        </div>
                    </div>
                    <!-- 주문자 정보 -->

                    <!-- 수령자 정보 -->
                    <div class="col-xs-6 js-order-view-receiver-area">
                        <div class="table-title gd-help-manual">
                            <div class="flo-left">
                                수령자 정보
                                <?php
                                $infoSno = $data['infoSno'];
                                //복수배송지를 사용중이며 복수배송지를 사용한 주문건일 경우
                                if(count($data['multiShippingList']) > 0) {
                                    if((int)$data['multiShippingList'][0]['orderInfoCd'] === 1){
                                        echo ' - 메인 배송지';
                                    }
                                    else {
                                        echo ' - 추가 배송지' . ((int)$data['multiShippingList'][0]['orderInfoCd'] - 1);
                                    }
                                    $infoSno = $data['multiShippingList'][0]['sno'];
                                }
                                ?>
                            </div>
                            <div class="flo-right">
                                <button type="button" class="btn btn-red btn-sm js-receiverInfoBtn">정보수정</button>
                                <button type="button" class="btn btn-red-box btn-sm js-receiverInfoBtnSave js-orderViewInfoSave display-none" data-submit-mode="modifyReceiverInfo" data-order-info-sno="<?=$infoSno?>" data-use-safenumber-fl="<?=$data['receiverUseSafeNumberFl'];?>">저장</button>
                            </div>
                        </div>

                        <div class="js-layout-order-view-receiver-info">
                            <?php include $layoutOrderViewReceiverInfo; ?>
                        </div>
                        <div class="js-layout-order-view-receiver-info-modify display-none">
                            <?php include $layoutOrderViewReceiverInfoModify; ?>
                        </div>
                    </div>
                    <!-- 수령자 정보 -->
                </div>
            </div>

            <!-- 복수배송지 수령자 정보 : 메인배송지를 제외한 배송지 정보 -->
            <!-- 복수배송지 수령자 정보 : 메인배송지를 제외한 배송지 정보 -->
            <?php
            if(count($data['multiShippingList']) > 0) {
                $originalOrderData = $data;
                $multiShippingList = $data['multiShippingList'];
                $multiShippingIndex = 0;
                foreach($multiShippingList as $key => $multiShippingData){
                    if($multiShippingIndex === 0){
                        $multiShippingIndex++;
                        continue;
                    }
                    $data = array_merge((array)$data, (array)$multiShippingData);
                    $data['receiverCellPhone'] = explode("-", $data['receiverCellPhone']);
                    $data['receiverPhone'] = explode("-", $data['receiverPhone']);
                    $data['infoSno'] = $multiShippingData['sno'];
            ?>
                <?php if($multiShippingIndex === 1 || $multiShippingIndex%2 != 0){ ?><div class="row"><div class="col-xs-12"><?php } ?>
                    <div class="col-xs-6 js-order-view-receiver-area">
                        <div class="table-title gd-help-manual">
                            <div class="flo-left">
                                수령자 정보 - 추가 배송지<?=(int)$multiShippingData['orderInfoCd']-1?>
                            </div>
                            <div class="flo-right">
                                <button type="button" class="btn btn-red btn-sm js-receiverInfoBtn">정보수정</button>
                                <button type="button" class="btn btn-red-box btn-sm js-receiverInfoBtnSave js-orderViewInfoSave display-none" data-submit-mode="modifyReceiverInfo" data-order-info-sno="<?=$multiShippingData['sno']?>" data-use-safenumber-fl="<?=$data['receiverUseSafeNumberFl'];?>">저장</button>
                            </div>
                        </div>

                        <div class="js-layout-order-view-receiver-info">
                            <?php include $layoutOrderViewReceiverInfo; ?>
                        </div>
                        <div class="js-layout-order-view-receiver-info-modify display-none">
                            <?php include $layoutOrderViewReceiverInfoModify; ?>
                        </div>
                    </div>
                    <?php if($multiShippingIndex%2 == 0 || count($originalOrderData['multiShippingList'])-1 == $multiShippingIndex){ ?></div></div><?php } ?>
            <?php
                    $multiShippingIndex++;
                }
                $data = $originalOrderData;
                unset($originalOrderData);
            }
            ?>
            <!-- 복수배송지 수령자 정보 : 메인배송지를 제외한 배송지 정보 -->
            <!-- 복수배송지 수령자 정보 : 메인배송지를 제외한 배송지 정보 -->

            <?php
            if (empty($addFieldData) === false || empty($naverpayIndividualCustomUniqueCode) === false || empty($paycoIndividualCustomUniqNo) === false) {
                ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="table-title gd-help-manual">추가 정보</div>
                        <table class="table table-cols">
                            <?php if($naverpayIndividualCustomUniqueCode) {?>
                                <tr>
                                    <th>개인통관 고유번호(네이버)</th>
                                </tr>
                                <tr>
                                    <td><?=$naverpayIndividualCustomUniqueCode?></td>
                                </tr>
                            <?php }?>
                            <?php if($paycoIndividualCustomUniqNo) {?>
                                <tr>
                                    <th>개인통관 고유번호(페이코)</th>
                                </tr>
                                <tr>
                                    <td><?=$paycoIndividualCustomUniqNo?></td>
                                </tr>
                            <?php }?>
                            <?php
                            foreach ($addFieldData as $addFieldKey => $addFieldVal) {
                                if ($addFieldVal['process'] == 'goods') {
                                    foreach ($addFieldVal['data'] as $addDataKey => $addDataVal) {
                                        ?>
                                        <tr>
                                            <th><?= $addFieldVal['name']; ?> : <?= $addFieldVal['goodsNm'][$addDataKey]; ?></th>
                                        </tr>
                                        <tr>
                                            <td><?= $addDataVal; ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <th><?= $addFieldVal['name']; ?></th>
                                    </tr>
                                    <tr>
                                        <td><?= $addFieldVal['data']; ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <?php
            }
            ?>

            <?php if (!$isProvider) { ?>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="table-title gd-help-manual">요청사항 / 상담메모</div>
                        <div class="pull-left notice-info">
                            요청사항/상담메모의 내용이 수정 또는 삭제된 경우 "저장" 버튼을 클릭해야 적용됩니다.
                        </div>
                        <table class="table table-rows mgb5">
                            <colgroup>
                                <col class="width-sm" />
                                <col class="width-md" />
                                <col class="width50p" />
                                <col class="width50p" />
                                <col class="width-sm" />
                            </colgroup>
                            <thead>
                            <tr>
                                <th>작성일</th>
                                <th>작성자</th>
                                <th>요청사항</th>
                                <th>상담메모</th>
                                <th>관리</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($data['consult']) === false) { ?>
                                <?php foreach ($data['consult'] as $key => $val) { ?>
                                    <tr class="text-center">
                                        <td class="nowrap"><?=$val['regDt']?></td>
                                        <td class="nowrap"><?=$val['managerId']?> / <?=$val['managerNm']?></td>
                                        <td class="text-left js-request-memo"><?=$val['requestMemo']?></td>
                                        <td class="text-left js-consult-memo"><?=$val['consultMemo']?></td>
                                        <td class="nowrap">
                                            <button type="button" class="btn btn-sm btn-gray js-consult-modify" data-sno="<?=$val['sno']?>">수정</button>
                                            <button type="button" class="btn btn-sm btn-gray js-consult-delete" data-sno="<?=$val['sno']?>">삭제</button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="5" class="no-data">
                                        등록된 내용이 없습니다.
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-xs-12">
                        <table class="table table-cols">
                            <colgroup>
                                <col class="width-md"/>
                                <col/>
                                <col class="width-md"/>
                                <col/>
                            </colgroup>
                            <tbody>
                            <tr>
                                <th>고객요청사항</th>
                                <td>
                                    <input type="hidden" name="consult[sno]" value="">
                                    <input type="hidden" name="consult[orderNo]" value="<?=$data['orderNo']?>">
                                    <textarea maxlength="1000" name="consult[requestMemo]" class="form-control js-maxlength"></textarea>
                                </td>
                                <th>고객상담메모</th>
                                <td>
                                    <table class="width100p">
                                        <tr>
                                            <td>
                                                <textarea maxlength="1000" name="consult[consultMemo]" class="form-control js-maxlength"></textarea>
                                            </td>
                                            <td class="width3p pdb20 pdl5">
                                                <button type="button" class="btn btn-black btn-sm js-custom-memo-reset mgb5" >초기화</button>
                                                <button type="button" class="btn btn-red btn-sm js-orderViewInfoSave mgl5 mgt3" data-submit-mode="modifyConsultMemo">저장</button>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="table-title gd-help-manual">관리자메모</div>

                        <table class="table table-rows mgb5">
                            <colgroup>
                                <col class="width-md" />
                                <col class="width-md" />
                                <col class="width-sm" />
                                <col class="width-sm" />
                                <col class="width50p" />
                                <col class="width15p" />
                            </colgroup>
                            <thead>
                            <tr>
                                <th>작성일</th>
                                <th>작성자</th>
                                <th>메모 구분</th>
                                <th>상품주문번호</th>
                                <th>메모 내용</th>
                                <?php if (!$isProvider) { ?>
                                <th>관리</th>
                                <?php }?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            if (empty($memoData) === false) {
                            foreach ($memoData as $mKey => $mVal) {
                            ?>
                            <tbody id="orderGoodsMemoData<?= $mKey; ?>">
                            <tr data-mall-sno="<?= $mVal['mallSno'] ?>">
                                <td class="text-center"><span><?php if ($mVal['modDt']) { echo $mVal['modDt']; } else { echo $mVal['regDt']; } ?></span></td>
                                <td class="text-center">
                                    <span class="managerId"><?= $mVal['managerId']; ?></span><br/>
                                    <?php if($mVal['managerNm']){?><span class="managerNm">(<?= $mVal['managerNm']; ?>)</span><?php }?>
                                </td>
                                <td class="text-center">
                                    <span class="itemNm"><?= $mVal['itemNm']; ?></span>
                                </td>
                                <td class="text-center"><span class="orderGoodsSno"><?php if ($mVal['type'] == 'goods') { echo $mVal['orderGoodsSno']; } else { echo '-'; } ?></span></td>
                                <td>
                                    <span class="content-memo"><?=str_replace(['\r\n', '\n', chr(10)], '<br>', $mVal['content']);?></span>
                                </td>
                                <?php if (!$isProvider) { ?>
                                <td class="text-center">
                                    <span class="mod-button" style="padding-bottom: 5px;">
                                        <button type="button" class="btn btn-sm btn-gray js-admin-memo-modify" data-sno="<?=$mVal['orderGoodsSno'];?>" data-type="<?=$mVal['type'];?>" data-memocd="<?=$mVal['memoCd'];?>" data-manager-sno="<?=$mVal['managerSno'];?>" data-m-sno="<?=$managerSno;?>" data-content="<?=$mVal['content'];?>" data-no="<?=$mVal['sno'];?>">수정</button>
                                    </span>
                                    <span class="del-button">
                                        <button type="button" class="btn btn-sm btn-gray js-admin-memo-delete" data-sno="<?=$mVal['orderGoodsSno'];?>" data-type="<?=$mVal['type'];?>" data-manager-sno="<?=$mVal['managerSno'];?>" data-m-sno="<?=$managerSno;?>" data-no="<?=$mVal['sno'];?>">삭제</button>
                                    </span>
                                </td>
                                <?php }?>
                            </tr>
                            </tbody>
                            <?php
                            }
                            }else{
                                ?>
                                <tr>
                                    <td colspan="6" class="no-data">
                                        등록된 내용이 없습니다.
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="center"><?= $page->getPage(); ?></div>
                    <?php if (!$isProvider) { ?>
                        <div class="col-xs-12">
                            <table class="table table-cols">
                                <colgroup>
                                    <col class="width-sm">
                                    <col>
                                    <col class="width-sm">
                                    <col>
                                </colgroup>
                                <tbody>
                                <tr>
                                    <th>메모 유형</th>
                                    <td>
                                        <label class="radio-inline">
                                            <input type="radio" name="memoType" value="order" checked="checked"/>주문번호별
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="memoType" value="goods" />상품주문번호별
                                        </label>
                                    </td>
                                    <th>메모 구분</th>
                                    <td>
                                        <?= gd_select_box('orderMemoCd', 'orderMemoCd', $memoCd, null, null, '=메모 구분='); ?>
                                    </td>
                                </tr>
                                <tr id="tr_goodsSelect" class="display-none">
                                    <th>상품 선택</th>
                                    <td colspan="3">
                                        <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows table-fixed">
                                            <thead>
                                            <tr id="orderGoodsList">
                                                <th class="width5p"><input type="checkbox" id="allCheck" value="y"
                                                                           onclick="check_toggle(this.id,'sno');"/></th>
                                                <th class="width10p">상품주문번호</th>
                                                <th class="width20p">주문상품</th>
                                                <th class="width15p">처리상태</th>
                                            </tr>
                                            </thead>
                                            <?php
                                            foreach($goodsData as $fKey => $fVal) {
                                                ?>
                                                <tbody id="addGoodsList<?= $fKey; ?>">
                                                <tr data-mall-sno="<?=$fVal['mallSno']?>">
                                                    <td class="text-center">
                                                        <input type="checkbox" name="sno[]" value="<?=$fVal['sno'];?>" >
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="sno"><?=$fVal['sno'];?></span>
                                                    </td>
                                                    <td>
                                                        <span class="goodsNm" style="font-weight: bold;"><?=$fVal['goodsNm'];?></span><br/>
                                                        <span class="optionInfo"><?=$fVal['optionInfo'];?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="orderStatus"><?=$fVal['orderStatus'];?></span>
                                                    </td>
                                                </tr>
                                                </tbody>
                                                <?php
                                            }
                                            ?>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <th>메모 내용</th>
                                    <td colspan="3">
                                        <textarea name="adminOrderGoodsMemo" class="form-control" rows="6"><?=$data['content']?></textarea>
                                    </td>
                                    <td class="width3p">
                                        <button type="button" class="btn btn-black btn-sm js-memo-reset mgb5" >초기화</button>
                                        <button type="button" class="btn btn-red btn-sm js-orderViewInfoSave mgl5" data-submit-mode="adminOrdGoodsMemo">저장</button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php }?>
                </div>
            <?php } ?>
        </div>
</form>
<script type="text/javascript">
    <!--
    var settleKind = '<?=$data['settleKind']?>';
    var orderGoodsCnt = '<?=$data['orderGoodsCnt']?>';
    var orderChannelFl = '<?=$data['orderChannelFl']?>';
    var apiOrderNo = '<?=$data['apiOrderNo']?>';
    var orderNo = '<?=$data['orderNo']?>';

    $(document).ready(function () {
        $('#tabOrderStatus > ul li > a').each(function(index, value){
            if ($(this).find('strong').html() > 0) {
                $(this).trigger('click');
                return false;
            }
        });

        $('.js-btn-naverpay-reload').bind('click',function () {
            $.post('order_ps.php', {
                mode: 'collectNaverpayOrder',
                orderNo: orderNo,
                naverpayOrderNo: apiOrderNo,
                async:false,
                type : 'json',
            }, function (data) {
                if(data.result == true){
                    alert('<b>갱신이 완료되었습니다.</b>');
                    setTimeout(function(){
                        location.reload();
                    },1000)
                }
                else {
                    alert(data.msg);
                }
                console.log(data);
            });
        });

        // 주문 로그 보기 Ajax layer
        $(document).on('click', '.js-order-log', function (e) {
            var goodsSno = $(this).data('sno');
            var goodsNm = $(this).data('name');
            $.post('layer_order_log.php', {
                orderNo: '<?= gd_isset($data['orderNo']);?>',
                goodsSno: goodsSno,
                goodsNm: goodsNm
            }, function (data) {
                layer_popup(data, '주문 로그 보기', 'wide');
            });
        });

        $('.js-convert-exchange').click(function(e) {
            if($(this).attr('type') === 'checkbox'){
                if($(this).prop('checked') === true){
                    $(this).attr('data-use-mall', 'true');
                    $(this).siblings("span").html("해외몰");
                }
                else {
                    $(this).attr('data-use-mall', 'false');
                    $(this).siblings("span").html("기준몰");
                }
            }
            else {
                $('.js-convert-exchange').removeClass('active');
                $('.js-convert-exchange').removeClass('btn-black').addClass('btn-white');
                $(this).addClass('active');
                $(this).removeClass('btn-white').addClass('btn-black');
            }

            $('#tabOrderStatus > .nav-tabs li.active a').trigger({
                type: 'show.bs.tab',
                isUseMall: $(this).attr('data-use-mall')
            });
        });

        // 상품정보 내역탭의 내용이 나타날때 발생하는 이벤트
        $('#tabOrderStatus > .nav-tabs a').bind('show.bs.tab', function(e){
            var orderStatusMode = $(this).attr('href').replace('#tab-status-', ''),
                tabId = $(this).attr('href');

            var isUseMall = false;
            if (!_.isUndefined(e.isUseMall)) {
                isUseMall = e.isUseMall;
            } else {
                isUseMall = $('.js-convert-exchange.active').attr('data-use-mall');
            }

            $('#tabOrderStatus .tab-content').addClass('loading');
            $.ajax({
                method: 'post',
                url: './inc_order_view.php',
                data: {
                    orderNo: '<?=$data['orderNo']?>',
                    orderStatusMode: orderStatusMode,
                    isUseMall: isUseMall,
                },
                async: true,
                dataType: 'html',
                cache: false
            }).success(function(data){
                $('#tabOrderStatus .tab-content').removeClass('loading');
                $(tabId).html(data);

                $("#orderGridConfigBtn").attr('data-order-grid-mode', 'view_'+orderStatusMode);
            }).error(function (e) {
                $('#tabOrderStatus .tab-content').removeClass('loading');
                alert(e.responseText);
            });
        });

        // 클래임정보 내역탭의 내용이 나타날때 발생하는 이벤트
        $('#tabClaimStatus > .nav-tabs a').bind('show.bs.tab', function(e){
            var orderStatusMode = $(this).attr('href').replace('#tab-claim-', ''),
                tabId = $(this).attr('href');
            $('#tabClaimStatus .tab-content').addClass('loading');
            $.ajax({
                method: 'post',
                url: './inc_claim_view.php',
                data: {
                    orderNo: '<?=$data['orderNo']?>',
                    orderStatusMode: orderStatusMode
                },
                async: true,
                dataType: 'html',
                cache: true
            }).success(function(data){
                $('#tabClaimStatus .tab-content').removeClass('loading');
                $(tabId).html(data);
            }).error(function (e) {
                $('#tabClaimStatus .tab-content').removeClass('loading');
                alert(e.responseText);
            });
        });

        // 시작시 주문내역 출력
        if ($('#tabOrderStatus .nav-tabs li').hasClass('active')) {
            $('#tabOrderStatus .nav-tabs li.active a').trigger('show.bs.tab');
        }
        if ($('#tabClaimStatus .nav-tabs li').hasClass('active')) {
            $('#tabClaimStatus .nav-tabs li.active a').trigger('show.bs.tab');
        }

        // 일괄 처리 모드의 체크
        $(document).on('change', '.js-status-change', function (e) {
            set_check_reset();
            if ($(this).val() == '') {
                return false;
            }

            // !중요! 결제확인을 입금대기로 변경하려는 경우 모두 입금대기로 변경되어져야 한다.
            if ($('input[name*=\'bundle[statusCheck]\']:checkbox:checked').length > 0 && $(this).val() == 'o1' && 'p' == '<?=$data['statusMode'];?>') {
                $('input[name*=\'bundle[statusCheck]\']:checkbox').prop('checked', true);
            }
            var tmpCode = $(this).val().substr(0, 1);

            set_check_reset(true);
            set_check_status(tmpCode)
        });

        //주문상태, 송장번호 일괄적용 (체크박스 관련 부분만 폼을 별도로 생성해서 작업 되어진다)
        $(document).on('click', '.js-order-status-delivery', function (e) {
            var dom = {
                table: $(this).closest('.table-action').siblings('table').eq(0),
                statusSelect: $(this).siblings('#bundleOrderStatus'),
                invoiceSelect: $(this).siblings('#applyDeliverySno'),
                invoiceText : $(this).siblings('#applyInvoiceNo'),
            };

            if (!$('input[name*=\'bundle[statusCheck]\']:checkbox:checked').length) {
                alert('일괄 처리할 상품을 선택해 주세요.');
                return false;
            }
            if((!dom.statusSelect.val()) && (parseInt(dom.invoiceSelect.val()) < 1 ) && $.trim(dom.invoiceText.val()) === ''){
                alert('주문상태나 송장정보를 선택해 주세요.');
                return false;
            }
            if((parseInt(dom.invoiceSelect.val()) < 1 ) && $.trim(dom.invoiceText.val()) !== ''){
                alert('배송업체를 선택해 주세요.');
                return false;
            }

            //주문상태 변경을 시도하려는 경우
            if(dom.statusSelect.val()){

                //네이버 페이 주문상태 변경
                if(dom.statusSelect.val().indexOf('naverpay')>-1){
                    var snoList = [];
                    $('[name^="bundle[statusCheck]"]:checked').not(':disabled').each(function () {
                        if($(this).is(':visible')){
                            snoList.push($(this).val());
                        }
                    });
                    var orderGoodsNos = snoList.join(',');
                    var tmpNaverPayMode = dom.statusSelect.val().split('_');
                    var naverPayMode = tmpNaverPayMode[2];
                    if(naverPayMode == 'ReleaseReturnHold' || naverPayMode == 'ReleaseExchangeHold'){//반품 or 교환 보류 해제
                        $.post('../order/order_naverpay_ps.php', {'mode': naverPayMode,'orderNo': '<?= gd_isset($data['orderNo']);?>',  'orderGoodsNos' : orderGoodsNos}, function (data) {
                                if((data.result) === 'success'){
                                    top.location.reload();
                                }
                                else {
                                    alert('<b>'+data.msg+'</b>');
                                }
                            },'json'
                        );

                        return;
                    }

                    $.get('../order/layer_naverpay_order.php', {'mode': 'naverpayLayer','status': dom.statusSelect.val(),'orderNo': '<?= gd_isset($data['orderNo']);?>', 'handleSno': $(this).data('handle-sno') , 'orderGoodsNos' : orderGoodsNos}, function (data) {
                        if(data.substring(0,5) == 'error'){
                            var errorData = data.split("|");
                            alert(errorData[1]);
                            return;
                        }

                        BootstrapDialog.show({
                            title: dom.statusSelect.find('option:selected').text()+' 처리',
                            size: get_layer_size('wide'),
                            message: data,
                            closable: true,
                        });
                    });

                    return;
                }
                //네이버 페이 주문상태 변경
                var modalMessage = '선택한 상품을 "' + dom.statusSelect.find('option:selected').html() + '" 상태로 변경하시겠습니까?';
                if (dom.statusSelect.val() == 'o1' && 'p' == '<?=$data['statusMode'];?>') {
                    $('input[name*=\'bundle[statusCheck]\']:checkbox').prop('checked', true);
                    modalMessage = '해당 주문의 전체상품이 "' + dom.statusSelect.find('option:selected').html() + '" 상태로 변경됩니다.<br>변경하시겠습니까?';
                }
                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_WARNING,
                    title: '주문상태 변경',
                    message: modalMessage,
                    callback: function (result) {
                        // 확인 버튼 클릭시
                        if (result) {
                            submitOrderStatusDelivery(dom);
                        }
                    }
                });
            }
            else {
                submitOrderStatusDelivery(dom);
            }


        });

        // 쿠폰 사용 보기 Ajax layer
        $('.js-order-coupon').click(function (e) {
            $.post('layer_order_coupon.php', {'orderNo': '<?= gd_isset($data['orderNo']);?>'}, function (data) {
                layer_popup(data, '쿠폰 정보 보기', 'wide');
            });
        });

        // 입금 은행 변경 Ajax layer
        $('.js-bank-change').click(function (e) {
            $.post('layer_bank_selector.php', {'orderNo': '<?= gd_isset($data['orderNo']);?>'}, function (data) {
                layer_popup(data, '입금 은행 변경');
            });
        });

        // PG 로그 Ajax layer
        $('.js-pg-log').click(function (e) {
            $.post('layer_order_log_pg.php', {'orderNo': '<?= gd_isset($data['orderNo']);?>'}, function (data) {
                layer_popup(data, 'PG 로그 보기', 'normal');
            });
        });

        //운영자 추가 할인 설정 Ajax layer
        $('.js-layer-enuri').click(function (e) {
            $.post('layer_order_enuri.php', {'orderNo': '<?= gd_isset($data['orderNo']);?>'}, function (data) {
                layer_popup(data, '운영자 추가 할인 설정', 'wide-lg');
            });
        });

        // 최초결제정보 토글
        $('.js-pay-toggle').click(function(e){
            var target = $(this).closest('tr').siblings('#' + $(this).data('target')).eq(0);
            var tr = $(this).closest('tr'),
                td = tr.find('td.th .list-unstyled');
            if (target.find('td').is(':visible')) {
                $(this).removeClass('active');
                $(this).closest('th').css({borderBottom: '1px solid #E6E6E6'});
                target.find('th').css({display: 'none'});
                target.find('td').css({display: 'none'});
            } else {
                $(this).addClass('active');
                $(this).closest('th').css({borderBottom: 'none'});
                target.find('th').css({display: ''});
                target.find('td').css({display: ''});
            }
        });

        // 최초결제정보의 토글 버튼 노출 여부 설정
        $('.js-pay-toggle').each(function(idx){
            var count = $(this).data('number');
            if (count == 0) {
                $(this).remove();
            }
        });

        // 요청사항/상담메모
        $('.js-consult-modify').click(function(e){
            $('input[name="consult[sno]"]').val($(this).data('sno'));
            $('textarea[name="consult[requestMemo]"]').val($(this).closest('tr').find('.js-request-memo').text());
            $('textarea[name="consult[consultMemo]"]').val($(this).closest('tr').find('.js-consult-memo').text());
        });

        // 요청사항/상담메모 삭제 처리
        $('.js-consult-delete').click(function(e){
            var element = $(this).closest('tr'),
                self = $(this);
            BootstrapDialog.confirm({
                type: BootstrapDialog.TYPE_WARNING,
                title: '요청사항 및 상담메모 삭제',
                message: '선택한 상담메모를 삭제하시겠습니까? 삭제하시면 복구하실 수 없습니다.',
                callback: function (result) {
                    // 확인 버튼 클릭시
                    if (result) {
                        // 다른 폼에 데이터를 추가해서 일괄변경 처리를 한다.
                        $.post('../order/order_ps.php', {
                            mode: 'delete_consult',
                            sno: self.data('sno'),
                            orderNo: '<?= gd_isset($data['orderNo']);?>'
                        }, function (data) {
                            alert(data.message);
                            if (data.code == 0) {
                                element.remove();
                            }
                        });
                    }
                }
            });
        });

        //주문자 정보 - 정보수정
        $('.js-orderInfoBtn').click(function(){
            $("#layoutOrderViewOrderInfo, #layoutOrderViewOrderInfoModify, .js-orderInfoBtn, .js-orderInfoBtnSave").toggleClass('display-none');
        });

        //수령자 정보 - 정보수정
        $('.js-receiverInfoBtn').click(function(){
            var parentCloset = $(this).closest(".js-order-view-receiver-area");
            parentCloset.find(".js-layout-order-view-receiver-info, .js-layout-order-view-receiver-info-modify, .js-receiverInfoBtn, .js-receiverInfoBtnSave").toggleClass('display-none');
        });

        //주문자 정보, 수령자 정보, 고객상담메모, 관리자메모 저장
        $('.js-orderViewInfoSave').click(function(){

            if($(this).data('submit-mode') === 'modifyConsultMemo'){
                if($.trim($("textarea[name='consult[requestMemo]']").val()) === '' && $.trim($("textarea[name='consult[consultMemo]']").val()) === ''){
                    alert('고객요청사항 혹은 고객상담메모를 입력해 주세요.');
                    return;
                }
            }
            else if($(this).data('submit-mode') === 'modifyReceiverInfo') {
                //수령자 정보 수정일 시
                $("#frmOrder>input[name='info[sno]']").val($(this).data('order-info-sno'));

                var parentObj = $(this).closest(".js-order-view-receiver-area");

                // 안심번호 사용 상태에서 휴대폰 번호 수정시 안심번호 수정
                <?php if (gd_isset($safeNumberFl)) { ?>
                var oldCellPhone = parentObj.find('input[name="info[receiverOriginCellPhone]"]').val();
                var newCellPhone = parentObj.find('input[name="info[receiverCellPhone]"]').val();
                var currentSafeNumber = parentObj.find('input[name="info[receiverSafeNumber]"]').val();

                if ($(this).data('use-safenumber-fl') == 'y' && oldCellPhone != newCellPhone) {
                    var errorSafeNumberFl = false;

                    $.ajax({
                        method: "POST",
                        cache: false,
                        async: false,
                        dataType: 'json',
                        url: "../order/order_ps.php",
                        data: {
                            'mode': 'get_reciever_safe_number',
                            'sno' : $(this).data('order-info-sno'),
                            'receiverUseSafeNumberFl' : $(this).data('use-safenumber-fl'),
                            'receiverSafeNumber': currentSafeNumber,
                            'receiverOldCellPhone' : oldCellPhone,
                            'receiverNewCellPhone' : newCellPhone
                        }
                    }).success(function (data) {
                        if (data.result != 'success') {
                            errorSafeNumberFl = true;
                        }
                    }).error(function (e) {
                        console.log(e);
                    });
                }
                <?php } ?>

                var dataParam = {
                    receiverName : parentObj.find("input[name='info[receiverName]']").val(),
                    receiverPhonePrefixCode : parentObj.find("select[name='info[receiverPhonePrefixCode]']").val(),
                    receiverPhone : parentObj.find("input[name='info[receiverPhone]']").val(),
                    receiverCellPhonePrefixCode : parentObj.find("input[name='info[receiverCellPhonePrefixCode]']").val(),
                    receiverCellPhone : parentObj.find("input[name='info[receiverCellPhone]']").val(),
                    receiverCountrycode : parentObj.find("select[name='info[receiverCountrycode]']").val(),
                    receiverZonecode : parentObj.find("input[name='info[receiverZonecode]']").val(),
                    receiverZipcode : parentObj.find("input[name='info[receiverZipcode]']").val(),
                    receiverCity : parentObj.find("input[name='info[receiverCity]']").val(),
                    receiverState : parentObj.find("input[name='info[receiverState]']").val(),
                    receiverAddress : parentObj.find("input[name='info[receiverAddress]']").val(),
                    receiverAddressSub : parentObj.find("input[name='info[receiverAddressSub]']").val(),
                    visitName : parentObj.find("input[name='info[visitName]']").val(),
                    visitPhone : parentObj.find("input[name='info[visitPhone]']").val(),
                    visitMemo : parentObj.find("textarea[name='info[visitMemo]']").val(),
                };
                <?php if (gd_isset($safeNumberFl)) { ?>
                dataParam.receiverSafeNumber = currentSafeNumber;
                <?php } ?>
                $("#frmOrder>input[name='info[data]']").val(JSON.stringify(dataParam));
            }else if($(this).data('submit-mode') === 'adminOrdGoodsMemo'){
                if($.trim($("textarea[name='adminOrderGoodsMemo']").val()) === ''){
                    alert('관리자 메모를 등록해주세요.');
                    return false;
                }
                var checkedValue = $("input[type=radio][name=memoType]:checked").val();
                var snoLength = $('input[name=\"sno[]\"]:checked').length;
                if(checkedValue == 'goods'){
                    if (!snoLength) {
                        alert('선택된 상품이 없습니다.');
                        return false;
                    }
                }else if(checkedValue == 'order'){
                    if(snoLength) {
                        $('input:checkbox[name=\"sno[]\"]').attr("checked", false);
                    }
                }

                if($('#frmOrder>input[name="no"]').val()){
                    $("#frmOrder>input[name='mode']").val('admin_order_goods_memo_modify');
                }else{
                    $("#frmOrder>input[name='mode']").val($(this).data('submit-mode'));
                }

            }

            if($('#frmOrder>input[name="no"]').val()){
                $("#frmOrder>input[name='mode']").val('admin_order_goods_memo_modify');
            }else {
                $("#frmOrder>input[name='mode']").val($(this).data('submit-mode'));
            }
            $("#frmOrder").attr('target', 'ifrmProcess');
            <?php if (gd_isset($safeNumberFl)) { ?>
            // 안심번호 통신 오류로 인해 안심번호 갱신을 못했을 경우
            if (errorSafeNumberFl) {
                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_WARNING,
                    title: '안심번호 연결',
                    message: '일시적으로 안심번호에 연결된 휴대폰 번호를 수정할 수 없습니다. 안심번호를 연결하지 않고 휴대폰 번호를 수정하겠습니까?',
                    closable: false,
                    callback: function (result) {
                        parentObj.find('input[name="info[receiverSafeNumber]"]').append('<input type="hidden" name="info[safeNumberMode]" value="">');
                        // 확인 - 안심번호 해지, 취소 - 휴대폰 번호 수정하지 않음
                        if (result) {
                            parentObj.find('input[name="info[safeNumberMode]"]').val('cancel');
                        } else {
                            parentObj.find('input[name="info[safeNumberMode]"]').val('except');
                        }
                        $("#frmOrder").submit();
                    }
                });
            } else {
                $("#frmOrder").submit();
            }
            <?php } else { ?>
            $("#frmOrder").submit();
            <?php } ?>
        });

        <?php if (gd_isset($safeNumberFl) && $safeNumberFl != 'off') { ?>
        // 안심번호 사용해지
        $('.js-cancel-safeNumber').on('click', function() {
            var orderInfoSno = $(this).data('order-info-sno'),
                safeNumber = $(this).data('safenumber'),
                receiverCellPhone = $(this).data('receiver-cellphone');

            BootstrapDialog.confirm({
                type: BootstrapDialog.TYPE_WARNING,
                title: '안심번호 사용해지',
                message: '해당 번호의 안심번호 연결이 해지됩니다. (다시 연결은 할 수 없습니다.)<br>계속하시겠습니까?',
                callback: function (result) {
                    // 확인 버튼 클릭시
                    if (result) {
                        post_with_reload('../order/order_ps.php', {
                            mode: 'cancel_safe_number',
                            sno: orderInfoSno,
                            safeNumber: safeNumber,
                            phoneNumber: receiverCellPhone,
                        });
                    }
                }
            });
        });

        // 안심번호 수동발급
        $('.js-reset-safeNumber').on('click', function() {
            var orderInfoSno = $(this).data('order-info-sno'),
                useSafeNumberFl = $(this).data('use-safenumber-fl'),
                receiverCellPhone = $(this).data('receiver-cellphone');

            BootstrapDialog.confirm({
                type: BootstrapDialog.TYPE_WARNING,
                title: '안심번호 수동발급',
                message: '안심번호 수동발급을 요청하시겠습니까?',
                callback: function (result) {
                    // 확인 버튼 클릭시
                    if (result) {
                        post_with_reload('../order/order_ps.php', {
                            mode: 'reset_safe_number',
                            sno: orderInfoSno,
                            receiverUseSafeNumberFl: useSafeNumberFl,
                            receiverCellPhone: receiverCellPhone,
                        });
                    }
                }
            });
        });
        <?php } ?>

        // 상품 주문번호별 일때 상품선택 노출
        $('input:radio[name="memoType"]').click(function () {
            if($(this).val() == 'goods'){
                $('#tr_goodsSelect').removeClass('display-none');
            }else{
                $('#tr_goodsSelect').addClass('display-none');
            }
        });

        // 메모 수정
        $('.js-admin-memo-modify').click(function () {
            if(($(this).data('manager-sno') == $(this).data('m-sno')) || ($(this).data('manager-sno') == 0)) {
                var contentStr = $(this).data('content').toString().replace(/\\r\\n/gi, "\n");
                //var contentStr = $(this).data('content').replace(/\\r\\n/gi, "\n");

                // 수정 버튼 누를때마다 체크박스 초기화
                $("#allCheck, input:checkbox[name=\"sno[]\"]").prop("checked",false);

                // 수정 버튼 누를때마다 체크박스 초기화
                $("#allCheck, input:checkbox[name=\"sno[]\"]").prop("checked",false);

                // 수정 모드로 변경
                $('input[name="mode"]').attr('value', 'admin_order_goods_memo_modify');
                $('input[name="no"]').attr('value',$(this).data('no'));
                $('input[name="oldManagerId"]').attr('value',$(this).data('manager-sno'));
                $("input:radio[name=memoType]:radio[value=\'" + $(this).data('type') + "\']").prop('checked', true);
                $("input:checkbox[name=\"sno[]\"][value=\'" + $(this).data('sno') + "\']").prop("checked", true);
                $("#allCheck, input:checkbox[name=\"sno[]\"][value!=\'" + $(this).data('sno') + "\']").prop("disabled", true);
                $("#orderMemoCd").val($(this).data('memocd')).prop("selected", true);
                $("textarea[name='adminOrderGoodsMemo']").val(contentStr);

                if ($(this).data('type') == 'order') {
                    $('#tr_goodsSelect').addClass('display-none');
                } else {
                    $('#tr_goodsSelect').removeClass('display-none');
                }
            }else{
                alert('메모를 등록한 관리자만 수정가능합니다.');
                return false;
            }
        });

        // 메모 삭제
        $('.js-admin-memo-delete').click(function () {
            if(($(this).data('manager-sno') == $(this).data('m-sno')) || ($(this).data('manager-sno') == 0)) {
                var no = $(this).data('no');
                dialog_confirm('선택한 관리자메모를 삭제하시겠습니까? 삭제하시면 복구 하실 수 없습니다.', function (result) {
                    if (result) {
                        //var orderNo = "<?= $orderNo;?>";
                        $.ajax({
                            method: "POST",
                            cache: false,
                            url: "../order/order_ps.php",
                            data: "mode=admin_order_goods_memo_delete&no=" + no,
                        }).success(function (data) {
                            alert(data);
                        }).error(function (e) {
                            alert(e.responseText);
                            return false;
                        });
                    }
                });
            }else{
                alert('메모를 등록한 관리자만 삭제가능합니다.');
                return false;
            }
        });

        // 메모 초기화
        $('.js-memo-reset').click(function () {
            $("input[name='memoType'][value='order']").prop("checked",true);
            $("#orderMemoCd").val($(this).data('memocd')).prop("selected", false);
            $("#allCheck, input:checkbox[name=\"sno[]\"][value!=\'" + $(this).data('sno') + "\']").prop("checked", false);
            $("#allCheck, input:checkbox[name=\"sno[]\"]").prop("disabled", false);
            $("textarea[name='adminOrderGoodsMemo']").val('');
            $('#tr_goodsSelect').addClass('display-none');
            $('input[name="mode"]').attr('value', '');
            $('input[name="no"]').attr('value','');
        });

        // 고객요청사항 및 고객상담메모 초기화
        $('.js-custom-memo-reset').click(function () {
            $("textarea[name='consult[requestMemo]'], textarea[name='consult[consultMemo]").val('');
            //$("textarea[name='consult[consultMemo]']").val('');
        });
    });

    /**
     * 주문 처리 상품의 가능 여부를 리셋
     *
     * @param string chkMode 리셋 모드
     */
    function set_check_reset(isDisabled) {
        if (isDisabled) {
            // tr 의 배경색 및 checkbox를 disabled 합니다.
            $('tr[id*="statusCheck_"]').addClass('disabled');
            $('tr[id*="addStatusCheck_"]').addClass('disabled');
            $('tr[id*="statusCheck_"] input').prop('disabled', true);
            $('tr[id*="statusCheck_"] select').prop('disabled', true);
        } else {
            // tr 의 배경색 및 checkbox를 초기화 합니다.
            $('tr[id*="statusCheck_"]').removeClass('disabled');
            $('tr[id*="addStatusCheck_"]').removeClass('disabled');
            $('tr[id*="statusCheck_"] input').prop('disabled', false);
            $('tr[id*="statusCheck_"] select').prop('disabled', false);
        }
    }

    /**
     * 에스크로 거절 확인
     */
    function escrow_deny_register() {
        frame_popup('frame_escrow_deny.php?orderNo=<?= gd_isset($data['orderNo']);?>', '에스크로 거절 확인');
    }

    /**
     * 현금영수증 신청
     */
    function cash_receipt_register() {
        frame_popup('frame_cash_receipt_register.php?orderNo=<?= gd_isset($data['orderNo']);?>', '현금영수증 신청');
    }

    /**
     * 세금계산서 신청
     *
     * @param string modeStr 모드
     */
    function tax_invoice_register(modeStr) {
        if (typeof modeStr == 'undefined') {
            titleStr = '신청';
            modeStr = 'register';
        } else {
            titleStr = '수정';
        }
        frame_popup('frame_tax_invoice_register.php?orderNo=<?= gd_isset($data['orderNo']);?>&mode=' + modeStr, '세금계산서 ' + titleStr);
    }

    /**
     * 세금계산서 처리로그
     */
    function tax_log() {
        $.get('frame_tax_log.php', {'orderNo': '<?= gd_isset($data['orderNo']);?>'}, function (data) {
            layer_popup(data, '세금계산서 처리로그', 'normal');
        });
    }

    function select_email_domain(name,select) {
        if (typeof select === 'undefined') {
            select = 'emailDomain';
        }
        var $email = $(':text[name="' + name + '"]');
        var $emailDomain = $('select[id='+select+']');
        $emailDomain.on('change', function (e) {
            var emailValue = $email.val();
            var indexOf = emailValue.indexOf('@');
            if (indexOf == -1) {
                if ($emailDomain.val() === 'self') {
                    $email.val(emailValue + '@');
                } else {
                    $email.val(emailValue + '@' + $emailDomain.val());
                }
                $email.trigger('focusout');
            } else {
                if ($emailDomain.val() === 'self') {
                    $email.val(emailValue.substring(0, indexOf + 1));
                    $email.focus();
                } else {
                    $email.val(emailValue.substring(0, indexOf + 1) + $emailDomain.val());
                    $email.trigger('focusout');
                }
            }
        });
    }

    //주문상태, 송장번호 변경 submit
    function submitOrderStatusDelivery(dom)
    {
        // 다른 폼에 데이터를 추가해서 일괄변경 처리를 한다.
        var $form = $('#frmOrderStatus');
        var invoiceSelectValue = dom.invoiceSelect.val();
        var invoiceTextValue = dom.invoiceText.val();
        var statusSelectValue = dom.statusSelect.find('option:selected').val();

        $form.empty().append('<input type="hidden" name="mode" value="modifyOrderStatusDelivery" />');
        if(statusSelectValue){
            $form.append('<input type="hidden" name="orderStatusUpdateFl" value="y" />');
        }
        if(invoiceSelectValue != 0 && invoiceTextValue){
            $form.append('<input type="hidden" name="orderInvoiceUpdateFl" value="y" />');
        }
        $form.append('<input type="hidden" name="changeStatus" value="' + statusSelectValue + '" />');
        $form.append('<input type="hidden" name="orderChannelFl" value="' + orderChannelFl + '" />');

        $('input[name*="bundle[statusCheck]"]:checkbox:checked').not(':disabled').each(function (idx) {
            var tdElement = $(this).closest('td');
            var statusMode = tdElement.find('input[name*="bundle[statusMode]"]').val().substr(0, 1);
            var orderGoodsNo = tdElement.find('input[name*="bundle[goods][sno]"]').val();
            var deliveryMethodFl = tdElement.find('input[name*="bundle[deliveryMethodFl]"]').val();
            var defaultInvoiceCompanySno = tdElement.find('input[name*="bundle[defaultInvoiceCompanySno]"]').val();

            $form.append('<input type="hidden" name="statusMode[' + statusMode + '][]" value="' + statusMode + '" />');
            $form.append('<input type="hidden" name="statusCheck[' + statusMode + '][]" value="<?= gd_isset($data['orderNo'])  . INT_DIVISION;?>' + orderGoodsNo + '" />');
            $form.append('<input type="hidden" name="invoiceCompanySno[' + statusMode + '][' + orderGoodsNo + ']" value="' + invoiceSelectValue + '" />');
            $form.append('<input type="hidden" name="deliveryMethodFl[' + statusMode + '][' + orderGoodsNo + ']" value="' + deliveryMethodFl + '" />');
            $form.append('<input type="hidden" name="defaultInvoiceCompanySno[' + statusMode + '][' + orderGoodsNo + ']" value="' + defaultInvoiceCompanySno + '" />');
            $form.append('<input type="hidden" name="invoiceNo[' + statusMode + '][' + orderGoodsNo + ']" value="' + invoiceTextValue + '" />');
        });
        $('#frmOrderStatus').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            }
        });
        $('#frmOrderStatus').submit();
    }

    select_email_domain('info[orderEmail]');

    /**
     * 현금영수증 재발행 팝업
     */
    function cash_receipt_reissue(orderNo, sno) {
        window.open('./popup_order_cash_receipt_reissue.php?popupMode=yes&orderNo=' + orderNo + '&sno=' + sno, 'cash_receipt_reissue', 'width=600,height=500');
    }
    //-->
</script>
