<div id="ajax-list">
    <div class="table-title">이미지 파일명 : <span style="font-weight:normal"><?=stripslashes($imageName)?></span></div>
    <table class="table table-rows">
        <colgroup>
            <col class="width-3xs"/>
            <col class="width-sm"/>
            <col/>
            <col class="width-sm"/>
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>상품코드</th>
            <th>상품명</th>
            <th>이미지종류</th>
            <th>등록일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $confImage = gd_policy('goods.image'); // 이미지 설정
        foreach($data['list'] as $val) {
            $imageKindTxt = $confImage[$val['imageKind']]['text'];
            ?>
        <tr class="text-center">
            <td><?=$val['no']?></td>
            <td><?=$val['goodsNo']?></td>
            <td><?=$val['goodsNm']?></td>
            <td><?=$imageKindTxt?></td>
            <td><?=$val['regDt']?></td>
        </tr>
        <?php }?>
        </tbody>
    </table>
    <div class="center"><?=$data['pageHtml']?></div>
</div>
<script>
    function layer_list_search(pagelink)
    {
        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
         var imageName = '<?=$imageName?>';
        $.get('./layer_tmp_goods_info.php', { 'pagelink' : pagelink , 'imageName' : imageName }, function(data){
            $('#ajax-list').html(data);
        });
    }

</script>
