<div class="process">
    <div class="report">
        <h1>입금내역 일괄수정중 ...</h1>
        <table>
            <tr>
                <th>전송상태</th>
                <td>
                    <div class="briefing">
                        <ul>
                            <li>브리핑 메시지 샘플.</li>
                        </ul>
                    </div>
                </td>
            </tr>
        </table>
        <h2 class="report_step">준비중..</h2>
        <div class="report_line">
            <div class="report_white">
                <div class="report_graph"></div>
            </div>
        </div>
        <p><!--점선--></p>
        <div class="report_btn"><input type="button" value="닫기" class="btn btn-dark-gray" id="btn_batch_update_close"></div>
    </div>
</div>
<script type="text/javascript">
    $('#btn_batch_update_close').click(function () {
        layer_close();
        <?php
        if($getValue['mode'] == 'manualBatchUpdate') {
        ?>
        account_unmaching_list();
        manualMatchOrderList();
    });
    $(document).ready(function () {
        manualBatchUpdate.begin();
    })
    <?php
    } else {
    ?>
    account_list($('input[name="query"]').val());
    })
    ;
    $(document).ready(function () {
        batchUpdate.begin();
    })
    <?php } ?>

</script>