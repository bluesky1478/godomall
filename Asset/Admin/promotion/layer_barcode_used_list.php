<input type="hidden" id="bno" value="<?=$bno?>" />
<table class="table table-rows promotion-coupon-list">
    <thead>
    <tr>
        <th>번호</th>
        <th>아이디</th>
        <th>이름</th>
        <th>등급</th>
        <th>발급일</th>
        <th>사용일</th>
    </tr>
    </thead>
    <tbody>
        <?php foreach ($usedList as $data) { ?>
        <tr class="center">
            <td><?=($page->idx--)?></td>
            <td><?=$data['memId']?></td>
            <td><?=$data['memNm']?></td>
            <td><?=$data['groupNm']?></td>
            <td><?=$data['regDt']?></td>
            <td><?=$data['usedDt']?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<div id="layerUsedPagination" class="center"><?=($page->getPage())?></div>

<script type="text/javascript">
    $(function() {
        $('#layerUsedPagination > nav > ul > li > a').click(function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var pageNum = 1;
            if (href.indexOf('?') !== -1) {
                pageNum = href.split('?');
                pageNum = pageNum[1].split('page=');
                pageNum = pageNum[1].split('&');
                pageNum = pageNum[0];
            }
            var bno     = $('#bno').val();
            var params  = {bno : bno};
            $.post('layer_barcode_used_list.php?page=' + pageNum, params, function (data) {
                $('#barcodeUsedList').html(data);
                return false;
            });
        });
    });
</script>
