<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual mgt30">
    해외 배송조건 관리
</div>
<div class="table-responsive">
    <table class="table table-rows order-list">
        <thead>
        <tr>
            <th>상점 구분</th>
            <th>배송비기준</th>
            <th>보험료 청구</th>
            <th>박스부여무게</th>
            <th>배송국가그룹 수</th>
            <th>관리</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($delivery as $key => $val) { ?>
            <tr class="text-center">
                <td>
                    <span class="flag flag-16 flag-<?=$val['domainFl']?>"></span>
                    <?=$val['mallName']?>
                </td>
                <td><?=$val['standardFlStr']?></td>
                <td><?=$val['insuranceFl'] === 'y' ? 'O' : 'X'?></td>
                <td><?=$val['boxWeight']?><?=$weightUnit?></td>
                <td>
                    <?=$val['groupCnt']?>
                    <p class="mgt5 mgb0"><a href="#countries-list" class="btn btn-white btn-sm js-layer-group" data-sno="<?=$val['sno']?>">그룹보기</a></p>
                </td>
                <td><a href="../policy/mall_delivery_register.php?sno=<?=$val['sno']?>" class="btn btn-sm btn-white">수정</a></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<div class="center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    $(function(){
        // 국가보기 클릭 이벤트
        $('.js-layer-group').click(function(e){
            var addParam = {
                sno: $(this).data('sno')
            };
            $.get('../policy/layer_delivery_group.php', addParam, function (data) {
                var layerForm = '<div id="SelectedDeliveryGroup">' + data + '</div>';
                BootstrapDialog.show({
                    title: '선택된 배송그룹 정보',
                    message: $(layerForm),
                    size: BootstrapDialog.SIZE_NORMAL,
                    closable: true,
                    onhidden: function (dialog) {

                    }
                });
            });
        });
    });
</script>
