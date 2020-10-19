<div style="padding:5px;">
    <table cellpadding="0" cellpadding="0" width="100%" id="orderGoodsMemoList" class="table table-rows">
        <thead>
        <tr id="orderGoodsList">
            <th class="width10p">작성일</th>
            <th class="width10p">작성자</th>
            <th class="width10p">메모 구분</th>
            <th class="width10p">상품주문번호</th>
            <th class="width30p">메모 내용</th>
        </tr>
        </thead>
        <?php
        if($memoData) {?>
        <tbody id="orderGoodsMemoData<?= $mKey; ?>">
        <?php foreach ($memoData as $mKey => $mVal) {
            ?>
            <tr data-mall-sno="<?= $mVal['mallSno'] ?>">
                <td class="text-center">
                        <span><?php if ($mVal['modDt']) {
                                echo $mVal['modDt'];
                            } else {
                                echo $mVal['regDt'];
                            } ?></span></td>
                <td class="text-center">
                    <span class="managerId"><?= $mVal['managerId']; ?></span><br/>
                    <?php if($mVal['managerNm']){?><span class="managerNm">(<?= $mVal['managerNm']; ?>)</span><?php }?>
                </td>
                <td class="text-center">
                    <span class="itemNm"><?= $mVal['itemNm']; ?></span>
                </td>
                <td class="text-center">
                        <span class="orderGoodsSno"><?php if ($mVal['type'] == 'goods') {
                                echo $mVal['orderGoodsSno'];
                            } else {
                                echo '-';
                            } ?></span>
                </td>
                <td>
                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 350px; height: 50px;">
                        <span class="content-memo"><?=str_replace(['\r\n', '\n', chr(10)], '<br>', $mVal['content']);?></span>
                    </div>
                </td>
            </tr>
            <?php
        }
        }else{
            ?>
            <tr>
                <td colspan="6" class="no-data">
                    등록된 메모가 없습니다.
                </td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>