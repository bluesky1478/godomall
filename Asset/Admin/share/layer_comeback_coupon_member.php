<div>
    <div class="mgt10"></div>
    <div>
        <form id="frmCouponSearch">
            <table class="table table-cols no-title-line">
                <colgroup>
                    <col class="width-md"/>
                </colgroup>
                <tr>
                    <th>■ <?= $title; ?></th>
                </tr>
            </table>
        </form>
    </div>
</div>

<div>
    <p class="notice-danger pdb10">
        현재 시간 기준 발송 대상 회원 리스트입니다.<br/>
        동일한 쿠폰을 보유하고 있는 회원에게는 쿠폰은 재발급되지 않고 SMS만 발송되며,<br/>
        “정보/이벤트 SMS 수신”에 동의하지 않은 회원에게는 SMS는 발송되지 않고 쿠폰만 발급됩니다.<br/>
    </p>
</div>

<div class="table-header">
    <div class="pull-left">
        총 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>명
    </div>
</div>

<table class="table table-rows">
    <thead>
    <tr>
        <th>번호</th>
        <th>아이디</th>
        <th>이름</th>
        <th>등급</th>
        <th>쿠폰보유</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($data) === false) {
        $sumCouponPrice = 0;
        $sumCouponMileage = 0;
        foreach ($data as $key => $val) {
            $sumCouponPrice = $sumCouponPrice + $val['couponPrice'];
            $sumCouponMileage = $sumCouponMileage + $val['couponMileage'];
            ?>
            <tr class="text-center">
                <td><?=number_format($page->idx--)?></td>
                <td>
                    <button type="button" class="btn btn-link font-eng js-layer-crm" data-member-no="<?= $val['memNo'] ?>"><?= $val['memId'] ?></button>
                </td>
                <td><?= $val['memNm'] ?></td>
                <td><?= $val['groupNm'] ?></td>
                <td><?= ($val['couponNo'] != '') ? 'Y' :'N'; ?></td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="5" class="no-data">조건에 맞는 발송 대상이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
<script type="text/javascript">
    $(function(){
        // 회원ID 클릭시 처리
        $('.js-layer-crm').click(function(e){
            var memNo = $(this).data('member-no');
            window.open('/share/member_crm.php?popupMode=yes&memNo=' + memNo, 'member_crm', 'width=1200,height=850,scrollbars=yes');
            return false;
        });
    });

    // 페이지 출력
    function layer_list_search(pagelink) {
        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            layerFormID: '<?php echo $layerFormID?>',
            parentFormID: '<?php echo $parentFormID?>',
            dataFormID: '<?php echo $dataFormID?>',
            dataInputNm: '<?php echo $dataInputNm?>',
            callFunc: '<?php echo $callFunc?>',
            dataSno: '<?php echo $dataSno?>',
            mode: '<?php echo $mode?>',
            pagelink: pagelink
        };
        console.log(parameters);

        $.get('<?php echo URI_ADMIN;?>share/layer_comeback_coupon_member.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }
</script>
