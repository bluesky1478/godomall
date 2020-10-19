<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <a href="mall_delivery_group_register.php" class="btn btn-red-line">해외 배송그룹 등록</a>
    </div>
</div>

<div class="table-title gd-help-manual mgt30">
    해외 배송그룹 관리
</div>
<form id="frmOverseasDeliveryList" action="../policy/mall_delivery_ps.php" method="post">
<input type="hidden" name="mode" value="groupDelete"/>
    <div class="table-responsive">
        <table class="table table-rows order-list">
            <colgroup>
                <col class="width-3xs">
                <col class="width-xs">
                <col>
                <col class="width-md">
                <col class="width-sm">
                <col class="width-sm">
            </colgroup>
            <thead>
            <tr>
                <th><input type="checkbox" value="y" class="js-checkall" data-target-name="deliverChk"/></th>
                <th>번호</th>
                <th>배송그룹명</th>
                <th>배송국가그룹 수</th>
                <th>등록일/수정일</th>
                <th>관리</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($delivery as $key => $val) { ?>
                <tr class="text-center">
                    <td><input type="checkbox" name="deliverChk[]" value="<?=$val['sno']?>"></td>
                    <td><?=$key+1?></td>
                    <td>
                        <?=$val['groupName']?>
                    </td>
                    <td>
                        <?=$val['countriesCnt']?>
                        <p class="mgt5 mgb0"><a href="#countries-list" class="btn btn-white btn-sm js-layer-countries" data-sno="<?=$val['sno']?>">국가보기</a></p>
                    </td>
                    <td><?=gd_date_format('Y-m-d', $val['regDt'])?><?=empty($val['modDt']) === false ? '<br>' . gd_date_format('Y-m-d', $val['modDt']) : ''?></td>
                    <td><a href="../policy/mall_delivery_group_register.php?sno=<?=$val['sno']?>" class="btn btn-sm btn-white">수정</a></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <div class="table-action mgt0 mgb0">
            <div class="pull-left form-inline">
                <span class="action-title">선택한 배송그룹을</span>
                <button type="button" class="btn btn-white js-delivery-country-delete" />삭제처리</button>
            </div>
        </div>
    </div>
</form>

<div class="center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    $(function(){
        // 국가보기 클릭 이벤트
        $('.js-layer-countries').click(function(e){
            var addParam = {
                sno: $(this).data('sno')
            };
            $.get('../policy/layer_delivery_countries.php', addParam, function (data) {
                var layerForm = '<div id="SelectedDeliveryCountries">' + data + '</div>';
                BootstrapDialog.show({
                    title: '선택된 배송국가 정보',
                    message: $(layerForm),
                    size: BootstrapDialog.SIZE_SMALL,
                    closable: true,
                    onhidden: function (dialog) {

                    }
                });
            });
        });

        // 폼 전송
        $('#frmOverseasDeliveryList').validate({
            dialog: false,
            ignore: [],
            submitHandler: function (form) {
                dialog_confirm('선택한 해외 배송 그룹을 삭제하시겠습니까?', function(result){
                    if (result) {
                        form.target = 'ifrmProcess';
                        form.submit();
                    }
                });
            },
            rules: {
                'deliverChk[]': {
                    required: true,
                    minlength: 1
                },

            },
            messages: {
                'deliverChk[]': {
                    required: "하나 이상 체크하세요.",
                    minlength: '하나 이상 체크하세요.'
                }
            }
        });

        // 삭제 처리 이벤트
        $('.js-delivery-country-delete').click(function(e){
            $('#frmOverseasDeliveryList').submit();
        });

    });
</script>
