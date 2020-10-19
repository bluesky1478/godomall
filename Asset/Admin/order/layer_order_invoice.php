<div class="table-title">송장등록 정보</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col class="width-lg"/>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tbody>
    <tr>
        <th>공급사</th>
        <td><?=$info['companyNm']?></td>
        <th>등록자</th>
        <td><?=$info['managerNm']?> (<?=$info['managerId']?>)</td>
    </tr>
    <tr>
        <th>등록일자</th>
        <td><?=$info['regDt']?></td>
        <th>등록상태</th>
        <td>
            <label class="radio-inline">
                <input type="radio" name="completeFl" value="" <?=$checked['completeFl']['']?> /> 전체
            </label><br/>
            <label class="radio-inline">
                <input type="radio" name="completeFl" value="y" <?=$checked['completeFl']['y']?> /> 성공
            </label>
            <label class="radio-inline">
                <input type="radio" name="completeFl" value="n" <?=$checked['completeFl']['n']?> /> 실패
            </label>
            <label class="radio-inline">
                <input type="radio" name="completeFl" value="f" <?=$checked['completeFl']['f']?> /> 주문상태변경실패
            </label>
        </td>
    </tr>
    </tbody>
</table>

<div class="table-title">송장등록 리스트</div>
<div class="table-responsive" id="excelData">
    <table class="table table-rows">
        <thead>
        <tr>
            <th>번호</th>
            <th>상품주문번호</th>
            <th>주문번호</th>
            <th>배송업체정보</th>
            <th>송장번호</th>
            <th>배송일</th>
            <th>배송완료일</th>
            <th>상태</th>
            <th>실패사유</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false) {
            $count = count($data);
            foreach ($data as $key => $val) {
                // 성공여부
                if (isset($val['completeFl']) && $val['completeFl'] == 'y') {
                    $completeFl = '성공';
                } else {
                    $completeFl = '<span class="text-danger">실패</span>';
                }

                // 송장이 입력된 경우만 배송일 및 완료일 추가
                if (empty($val['invoiceCompanySno']) === false && empty($val['invoiceNo']) === false) {
                    $deliveryDt = $val['deliveryDt'];
                    $deliveryCompleteDt = $val['deliveryCompleteDt'];
                }
                ?>
                <tr class="text-center">
                    <td><?=number_format($count)?></td>
                    <td><?= $val['orderGoodsNo'] == 0 ? '-' : $val['orderGoodsNo']; ?></td>
                    <td style="mso-number-format:'\@'"><?= $val['orderNo'] == 0 ? '-' : $val['orderNo']; ?></td>
                    <td><?= $val['invoiceCompanySno']; ?></td>
                    <td style="mso-number-format:'\@'"><?= $val['invoiceNo']; ?></td>
                    <td><?= $deliveryDt; ?></td>
                    <td><?= $deliveryCompleteDt; ?></td>
                    <td><?= $completeFl; ?></td>
                    <td><?= $val['failReason']; ?></td>
                </tr>
                <?php
                $count--;
            }
        }
        else {
            ?>
            <tr>
                <td class="no-data" colspan="10">내역이 없습니다.</td>
            </tr>

        <?php } ?>
        </tbody>
    </table>
</div>

<div class="table-action">
    <div class="pull-right">
        <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        // 등록상태 라디오 버튼 클릭 처리
        $('input[name=completeFl]').click(function(e){
            $.get('../order/layer_order_invoice.php?groupCd=<?=$groupCd?>&completeFl=' + $(this).val(), function(data) {
                $('.modal-body').empty().html(data);
            });
        });

        // 엑셀다운로드
        $('.btn-excel').click(function () {
            var $form = $('<form></form>');
            $form.attr('action', './order_ps.php');
            $form.attr('method', 'post');
            $form.attr('target', 'ifrmProcess');
            $form.appendTo('body');

            var mode = $('<input type="hidden" name="mode" value="invoiceDetailExcelDownload">');
            var excel_name = $('<input type="hidden" name="excel_name" value="송장일괄등록_상세_<?=date('Y-m-d H_i_s')?>">');
            var data = $('<input type="hidden" name="data" value="' + encodeURI($('#excelData').html()) + '">');

            $form.append(mode).append(excel_name).append(data);
            $form.submit();
        });
    });
</script>
