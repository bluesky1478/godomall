<form id="frmMainSetting" name="frmBank" action="../base/main_setting_ps.php" method="post">
    <input type="hidden" name="mode" value="<?= $mode ?>"/>
    <input type="hidden" name="code" value="order"/>
    <table class="table table-cols no-title-line mgb0">
        <colgroup>
            <col class="width-xs"/>
            <col/>
        </colgroup>
        <tr>
            <th>조회기간</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="period" value="0" <?= $checked['period'][0] ?> class="form-control">
                    오늘
                </label>
                <label class="radio-inline">
                    <input type="radio" name="period" value="6" <?= $checked['period'][6] ?> class="form-control">
                    7일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="period" value="14" <?= $checked['period'][14] ?> class="form-control">
                    15일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="period" value="29" <?= $checked['period'][29] ?> class="form-control">
                    1개월
                </label>
            </td>
        </tr>
        <tr>
            <th>조회범위</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="orderCountFl" value="order" <?= $checked['orderCountFl']['order'] ?> class="form-control">
                    주문번호별
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderCountFl" value="goods" <?= $checked['orderCountFl']['goods'] ?> class="form-control">
                    상품주문번호별
                </label>
            </td>
        </tr>
        <tr>
            <th>
                노출항목<br>
                <p class="nobold">
                    (
                    <strong class="text-red" id="selectedCheckbox">0</strong>
                    / 8 선택)
                </p>
            </th>
            <td>
                <?php $chk = 0;
                foreach ($statusSearchableRange as $key => $val) { ?>
                    <label class="checkbox-inline mgb5 width-xs">
                        <input type="checkbox" name="orderStatus[]" <?= in_array($key, $orderStatus) ? 'checked="checked"' : '' ?> value="<?= $key ?>" <?= gd_isset($checked['orderStatus'][$key]) ?> /> <?= $val ?>
                    </label>
                    <?php $chk++;
                    if ($chk % 3 == 0) {
                        echo '<br/>';
                    }
                } ?>
            </td>
        </tr>
    </table>
    <div class="notice-info mgb15">"주문번호별"은 주문에 포함된 처리상태별 주문건수가 표시됩니다.</div>
    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
        <input type="submit" value="저장" class="btn btn-lg btn-black"/>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 체크박스 카운트
        $('input[name="orderStatus[]"]').change(function (e) {
            $('#selectedCheckbox').text($('input[name="orderStatus[]"]:checked').length);
        });

        // 체크박스 초기화
        $('#selectedCheckbox').text($('input[name="orderStatus[]"]:checked').length);

        // 폼체크

        var messages = {
            period: {
                required: '기간을 선택해주세요.'
            },
            'orderStatus[]': {
                required: '노출항목은 최소 1개 선택하셔야 합니다.',
                maxlength: '노출항목은 8개 까지 선택할 수 있습니다.'
            }
        };
        var rules = {
            period: 'required',
            'orderStatus[]': {
                required: true,
                maxlength: 8
            }
        };
        <?php
        if ($mode == 'orderPresentation') {
            echo 'delete messages["orderStatus[]"]["required"];';
            echo 'delete rules["orderStatus[]"]["required"];';
        }
        ?>
        $('#frmMainSetting').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
                layer_close();
            },
            rules: rules,
            messages: messages
        });
    });
    //-->
</script>
