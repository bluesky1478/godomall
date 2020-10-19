<style>
    .external-order-description-div {}
    .external-order-description-div li { list-style-type: disc !important; }
</style>

<div class="external-order-description-div mgl20 mgb5">
    <ul>
        <li>'외부채널 주문 엑셀 파일' 작성을 위한 항목별 설명입니다. 하단에 안내된 설명과 다르게 작성 시 정상적으로 등록되지 않을 수 있으니 주의바랍니다.</li>
        <li>붉은 글자색으로 표기된 항목은 필수입력항목입니다.</li>
        <li>초록배경으로 표시된 항목은 단위가 '주문상품'인 항목이며, '주문 그룹 번호'기준 첫 번째 행에 입력된 값만 반영됩니다.</li>
    </ul>
</div>

<table class="table table-rows">
    <thead>
        <tr>
            <th>한글필드명</th>
            <th>영문필드명</th>
            <th>단위</th>
            <th>설명</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if(count($descriptionList) > 0){
        foreach($descriptionList as $key => $value) {
            $addStyleArr = [];
            if($value['require'] === true){
                $addStyleArr[] = "color : #fa2828;";
            }

            if($value['unit'] === 'order'){
                $unitName = '주문';
            }
            else if($value['unit'] === 'goods'){
                $unitName = '주문상품';
                $addStyleArr[] = "background-color : #F6FFCC;";
            }
            else {}

            $addStyle = '';
            if(count($addStyleArr) > 0){
                $addStyle = "style='".implode(" ", $addStyleArr)."';";
            }
    ?>
            <tr>
                <td class="bold" <?php echo $addStyle; ?>><?php echo $value['fieldNameKorean']; ?></td>
                <td><?php echo $value['fieldNameEnglish']; ?></td>
                <td><?php echo $unitName; ?></td>
                <td>
                    <?php
                    foreach($value['description'] as $description){
                        echo '<div>' . $description . '</div>';
                    }
                    ?>
                </td>
            </tr>
    <?php
        }
    }
    ?>
    </tbody>
</table>
<div class="table-btn">
    <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
</div>
