<table class="table table-rows">
    <thead>
    <tr>
        <th class="width10p">번호</th>
        <th>담긴날짜</th>
        <th>아이디</th>
        <th>이름</th>
        <th>주문금액</th>
        <th>방문수</th>
        <th>회원가입일</th>
        <th>최종로그인</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (gd_isset($data) && is_array($data)) {
        $i = 0;
        foreach ($data as $key => $val) {
            ?>
            <tr class="text-center">
                <td><?php echo number_format($page->idx--);?></td>
                <td><?php echo $val['regDtFormat'];?></td>
                <td><a href="javascript:member_crm('<?= $val['memNo'] ?>')"><?php echo $val['memId'];?></a></td>
                <td><?php echo $val['memNm'];?></td>
                <td><?php echo gd_money_format($val['saleAmt']);?></td>
                <td><?php echo $val['loginCnt'];?></td>
                <td><?php echo $val['approvalDtFormat'];?></td>
                <td><?php echo $val['lastLoginDtFormat'];?></td>
            </tr>
            <?php
            $i++;
        }
    }
    ?>
    </tbody>
</table>
<div class="text-center"><?php echo $page->getPage('layerGoodsWishMemberPage(\'PAGELINK\')');?></div>

<script type="text/javascript">
    <!--
    // 페이지 출력
    function layerGoodsWishMemberPage(pagelink) {
        var dataString = '<?= $searchData; ?>';
        $.ajax({
            url: '../share/layer_goods_wish_member.php',
            type: 'post',
            data: dataString + '&' + pagelink,
            async: false,
            success: function (data) {
                $('#js-goods-wish-member').html(data);
            }
        });
    }
    //-->
</script>
