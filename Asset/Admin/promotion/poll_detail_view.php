
<table class="table table-rows">
    <colgroup>
        <col class="width-xs"/>
        <col/>
    </colgroup>
    <thead>
    <tr>
        <th>No</th>
        <th>내용</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($data as $k => $v) {?>
    <tr>
        <td class="center"><?php echo $page->idx--; ?></td>
        <td><?php echo $v; ?></td>
    </tr>
    <?php } ?>
    </tbody>
</table>
<div class="center"><?php echo $page->getPage('layer_list(\'PAGELINK\')'); ?></div>

<script type="text/javascript">
    <!--
    function layer_list(pagelink) {
        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'layerFormID': 'viewInfoForm',
            'mode': 'simple',
            'code': '<?php echo \request::get()->get('code')?>',
            'detail': '<?php echo \request::get()->get('detail')?>',
            'type': '<?php echo \request::get()->get('type')?>',
            'pagelink': pagelink
        };

        $.get('../promotion/poll_detail_view.php', parameters, function (data) {
            $('#viewInfoForm').html(data);
        });
    }
    //-->
</script>