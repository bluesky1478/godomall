<div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-3xs" />
            <col class="width-md" />
            <col class="width-md" />
            <col />
            <col class="width-lg" />
        </colgroup>
        <thead>
        <tr>
            <th>선택</th>
            <th>배송지 이름</th>
            <th>받으실 분</th>
            <th>주 소</th>
            <th>연락처</th>
        </tr>
        </thead>

        <tbody>
        <?php
        if(count($deliveryAddress) > 0){
            foreach($deliveryAddress as $key => $delivery){
        ?>
        <tr>
            <td class="ta-c">
                <label class="radio-inline">
                    <input type="radio" name="shippingSno" value="<?php echo $delivery['sno']; ?>" data-shippingName="<?php echo $delivery['shippingName']; ?>" data-shippingPhone="<?php echo $delivery['shippingPhone']; ?>" data-shippingCellPhone="<?php echo $delivery['shippingCellPhone']; ?>" data-shippingZonecode="<?php echo $delivery['shippingZonecode']; ?>" data-shippingZipcode="<?php echo $delivery['shippingZipcode']; ?>" data-shippingAddress="<?php echo $delivery['shippingAddress']; ?>" data-shippingAddressSub="<?php echo $delivery['shippingAddressSub']; ?>"/>
                </label>
            </td>
            <td>
                <?php if($delivery['defaultFl'] == 'y'){?><span style="font-size: 11px;">(기본배송지)</span><br /><?php } ?>
                <strong><?php echo $delivery['shippingTitle']; ?></strong>
            </td>
            <td><?php echo $delivery['shippingName']; ?></td>
            <td>
                <?php if (strlen($delivery['shippingZipcode']) === 7) {?><div><?php echo $delivery['shippingZipcode']; ?></div> <?php }?>
                <div>
                    <?php echo $delivery['shippingZonecode']; ?>
                    <?php echo $delivery['shippingAddress']; ?>
                    <?php echo $delivery['shippingAddressSub']; ?>
                </div>
            </td>
            <td>
                <div>전화번호 : <?php echo $delivery['shippingPhone']; ?></div>
                <div>휴대폰 : <?php echo $delivery['shippingCellPhone']; ?></div>
            </td>
        </tr>
        <?php
            }
        }
        else {
        ?>
        <tr>
            <td colspan="5" class="ta-c"> 배송지 리스트가 없습니다. </td>
        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>

    <div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>
    <div class="text-center mgt20">
        <input type="button" value="적 용" class="btn btn-lg btn-black js-adjust"/>
    </div>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        //적용
        $('.js-adjust').click(function(){
            if($("input[name='shippingSno']:checked").length < 1){
                alert("배송지를 선택해 주세요.");
                return;
            }

            var thisObj = $("input[name='shippingSno']:checked");
            var parameter = {
                //수령자명
                shippingName : thisObj.attr('data-shippingName'),
                //전화번호
                shippingPhone : thisObj.attr('data-shippingPhone'),
                //휴대폰번호
                shippingCellPhone : thisObj.attr('data-shippingCellPhone'),
                //구역번호
                shippingZonecode : thisObj.attr('data-shippingZonecode'),
                //우편번호
                shippingZipcode : thisObj.attr('data-shippingZipcode'),
                //주소
                shippingAddress : thisObj.attr('data-shippingAddress'),
                //나머지주소
                shippingAddressSub : thisObj.attr('data-shippingAddressSub')
            };
            parent.adjust_receiver_delivery_info(JSON.stringify(parameter));

            layer_close();
        });
    });

    // 페이지 출력
    function layer_list_search(pagelink) {
        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'layerFormID': '<?=$layerFormID?>',
            'memNo': '<?=$memNo?>',
            'page': pagelink
        };

        $.get('<?php echo $pageUrl?>', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }
    //-->
</script>
