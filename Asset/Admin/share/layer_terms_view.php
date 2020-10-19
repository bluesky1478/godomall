<div>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width10p">번호</th>
            <?php
            if ($mode == 'goods') {
                ?>
                <th class="width90p">상품명</th>
                <?php
            } else if ($mode == 'category') {
                ?>
                <th class="width90p">카테고리</th>
                <?php
            } else if ($mode == 'brand') {
                ?>
                <th class="width90p">브랜드</th>
                <?php
            } else if ($mode == 'event') {
                ?>
                <th class="width90p">이벤트</th>
                <?php
            } else if ($mode == 'gift') {
                ?>
                <th class="width20p">조건</th>
                <th class="width30p">사은품</th>
                <th class="width30p">선택수량</th>
                <th class="width30p">지급수량</th>
                <?php
            } else if ($mode == 'scm') {
                ?>
                <th class="width90p">공급사</th>
                <?php
            }
            ?>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data) && is_array($data)) {
            $i = 1;
            foreach ($data as $key => $val) {
                if ($mode == 'goods') {
                    $strImage    = gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank');
                    $textValue    = $strImage.' '.'<a href="../goods/goods_register.php?goodsNo='.$val['goodsNo'].'" target="_blank">'.$val['goodsNm'].'</a>';
                } else if ($mode == 'category') {
                    $textValue    = $val;
                } else if ($mode == 'brand') {
                    $textValue    = $val;
                } else if ($mode == 'event') {
                    $textValue    = $val;
                } else if ($mode == 'scm') {
                    $textValue    = $val;
                } else if ($mode == 'gift') {
                    $textValue    = '';
                    $textValue1    = number_format($val['conditionStart']).' ~ '.number_format($val['conditionEnd']);
                    $textValue2    = '';
                    if (is_array($val['multiGiftNo'])){
                        $tmpValue    = array();
                        foreach ($val['multiGiftNo'] as $mKey => $mVal) {
                            $strImage    = gd_html_gift_image($mVal['imageNm'], $mVal['imagePath'], $mVal['imageStorage'], 30, $mVal['giftNm']);
                            $tmpValue[]    = $strImage.' '.$mVal['giftNm'];
                        }
                        $textValue2        = implode('<br />', $tmpValue);
                    }

                    if ($val['selectCnt'] == 0) {
                        $textValue3    = '전체지급';
                    } else {
                        $textValue3   = $val['selectCnt'].'개씩 지급';
                    }

                    $textValue4   = $val['giveCnt'].'개씩 지급';
                }
                ?>
                <tr>
                    <td class="center"><?php echo $i++;?></td>
                    <?php
                    if ($mode == 'gift') {
                        ?>
                        <td><?php echo gd_isset($textValue1) ;?></td>
                        <td><?php echo gd_isset($textValue2) ;?></td>
                        <td><?php echo gd_isset($textValue3) ;?></td>
                        <td><?php echo gd_isset($textValue4) ;?></td>
                        <?php
                    } else {
                    ?>
                    <td><?php echo gd_isset($textValue) ;?></td>
                        <?php } ?>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
</div>
