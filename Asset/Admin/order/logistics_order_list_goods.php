<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<?php
include $layoutOrderSearchForm;// 검색 및 프린트 폼 ?>

<form id="frmOrderStatus" action="./logistics_ps.php" method="post">
    <input type="hidden" name="mode" value="reservation"/>
    <input type="hidden" name="viewType" value="<?=$search['view']?>"/>
    <?php
    include $layoutOrderList;// 주문리스트 ?>
</form>

<div class="text-center"><?= $page->getPage(); ?></div>
<table class="table table-cols">
    <colgroup><col class="width-md"><col><col class="width-md"></colgroup>
    <tbody><tr>
        <th>택배예약</th>
        <td>
            <div>
                <label class="radio-inline">
                    <input type="radio" name="godoPostSendFl" value="search" checked/> 선택된 <span id="selectOrderCnt">0</span>개의 주문에 대해서 대한통운 택배예약을 합니다.
                </label>
                <div class="notice-danger">주의! 택배예약된 주문의 경우 환불 혹은 교환취소를 할 경우 택배예약 취소를 먼저 해주세요!
                    </div>
                <div class="notice-info">묶음배송이 된 주문 건의 경우 묶음배송 코드가 같은 주문 건들은 합포장으로 처리됩니다.
                </div>
                <div class="notice-info">리스트 정렬 기준을 묶음배송 기준으로 정렬하면, 묶음배송 코드를 확인하기 용이합니다.
                </div>
            </div>
        </td>
        <td>
            <input type="button" value="대한통운 택배 예약하기" class="btn btn-lg btn-black js-send-logistcs">
        </td>
    </tr>
    </tbody></table>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/orderList.js?ts=<?=time();?>"></script>
