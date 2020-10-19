<form id="frmAddressList" name="frmAddressList">
    <table class="table table-rows table-fixed">
        <colgroup>
            <col class="width3p center"/>
            <col class="width15p"/>
        </colgroup>
        <tbody>
        <?php
        if ($data) {
            foreach ($data as $val) {
                foreach ($val as $info) {
                    $optInfo = '';
                    $optionInfo = [];
                    if (empty($info['optionInfo']) === false) {
                        foreach ($info['optionInfo'] as $opt) {
                            $optionInfo[] = $opt['optionName'] . ' : ' . $opt['optionValue'];
                        }
                        $optInfo = '<span style="color:#999999; font-size:11px;">' . @implode($optionInfo, ' / ') . '</span>';
                    }
                    ?>
                    <tr>
                        <td class="center"><?php echo $info['goodsImage']; ?></td>
                        <td><?php echo $info['goodsNmStandard'] . ' ' . $optInfo; ?></td>
                    </tr>
                    <tr>
                        <th colspan="2">방문 수령지 주소 : <?php echo $info['visitAddress']; ?></th>
                    </tr>
                    <?php
                }
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="3">검색된 페이지가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div id="layerAddressPagination" class="center"><?=$page->getPage()?></div>
    <div class="text-center mgt10">
        <button type="button" class="btn btn-lg btn-black js-layer-close">확인</button>
    </div>
</form>

<script type="text/javascript">
    $(function() {
        $('#layerAddressPagination > nav > ul > li > a').click(function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var page = 1;

            if (href.indexOf('?') !== -1) {
                page = href.split('?');
                page = page[1].split('page=');
                page = page[1].split('&');
                page = page[0];
            }

            var params = {orderNo:'<?php echo $value['orderNo']; ?>', goodsSno:'<?php echo json_encode($value['goodsSno']); ?>', page: page};

            $.get('layer_visit_address.php', params, function (data) {
                $('#frmAddressList').html(data);
                return false;
            });
        });
    });
</script>


