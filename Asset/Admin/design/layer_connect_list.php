<form id="frmConnectList" name="frmConnectList" method="post" target="ifrmProcess">
    <input type="hidden" name="skinNm" value="<?=$skinNm?>"/>
    <table class="table table-rows table-fixed">
        <colgroup>
            <col class="width3p"/>
            <col class="width15p"/>
            <col class="width15p" />
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>PC 스킨 페이지</th>
            <th>모바일 스킨 페이지</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result) {
            foreach ($result as $key => $val) {
            ?>
            <tr class="text-center">
                <td><?=$page->idx--?></td>
                <td><?php echo $val['linkurl']; ?></td>
                <td><?php echo $val['connectPage']; ?></td>
            </tr>
            <?php
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
    <div id="layerUsedPagination" class="center"><?=$page->getPage()?></div>
</form>

<script type="text/javascript">
    $(function() {
        var skinNm = $('input[name="skinNm"]').val();

        $('#layerUsedPagination > nav > ul > li > a').click(function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var page = 1;
            var pageNum = 10;

            if (href.indexOf('?') !== -1) {
                page = href.split('?');
                page = page[1].split('page=');
                page = page[1].split('&');
                page = page[0];
            }

            var params = {mode: 'connectPage', skinName: skinNm, page: page, pageNum: pageNum};

            $.post('layer_connect_list.php', params, function (data) {
                $('#frmConnectList').html(data);
                return false;
            });
        });
    });
</script>


