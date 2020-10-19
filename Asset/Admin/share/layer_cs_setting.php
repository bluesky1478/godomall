<form id="frmConfig" name="frmBank" action="../base/main_setting_ps.php" method="post">
    <input type="hidden" name="mode" value="boardPeriod"/>
    <input type="hidden" name="code" value="cs"/>
    <table class="table table-cols no-title-line">
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
        <?php if (gd_is_provider() === false) { ?>
            <tr>
                <th>
                    노출항목<br>
                    <p class="nobold">
                        (
                        <strong class="text-red" id="selectedCheckbox">0</strong>
                        / 4 선택)
                    </p>
                </th>
                <td id="innerTable">
                    <table class="table table-rows table-fixed mgb0">
                        <thead>
                        <tr>
                            <th class="width-2xs">
                                <input type="checkbox" class="js-checkall" data-target-name="sno">
                            </th>
                            <th class="width-md">게시판명</th>
                            <th class="">아이디</th>
                        </tr>
                        </thead>
                    </table>
                    <div style="overflow-y:auto;height:300px;">
                        <table class="table table-rows table-fixed">
                            <tbody>
                            <?php
                            if (count($lists) > 0) {
                                $listHtml = [];
                                foreach ($lists as $index => $item) {
                                    $listHtml[] = '<tr class="center">';
                                    $listHtml[] = '<td class="width-2xs"><input name="sno[]" type="checkbox" data-bd-kind="' . $item['bdKind'] . '" value="' . $item['sno'] . '" ' . $checked['sno'][$item['sno']] . '></td>';
                                    $listHtml[] = '<td class="js-bd-nm width-md">' . $item['bdNm'] . '</td>';
                                    $listHtml[] = '<td class="js-bd-id ">' . $item['bdId'] . '</td>';
                                    $listHtml[] = '</tr>';
                                }
                                echo implode('', $listHtml);
                            } else {
                                echo '<tr class="no-data"><td colspan="3"></td></tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
        <input type="submit" value="저장" class="btn btn-lg btn-black"/>
    </div>
</form>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 체크박스 카운트
        $('input[name="sno[]"]').change(function (e) {
            $('#selectedCheckbox').text($('input[name="sno[]"]:checked').length);
        });

        // 체크박스 초기화
        $('#selectedCheckbox').text($('input[name="sno[]"]:checked').length);
        $("#frmConfig").validate({
            submitHandler: function (form) {
                $('input[name="sno[]"]:checked').each(function (idx, item) {
                    var id_value = $(item).closest('tr').find('.js-bd-id').text();
                    var nm_value = $(item).closest('tr').find('.js-bd-nm').text();
                    var bd_kind = $(item).data('bdKind');
                    $(form).append($('<input type="hidden" name="id[]" value="' + id_value + '">'));
                    $(form).append($('<input type="hidden" name="bdNm[]" value="' + nm_value + '">'));
                    $(form).append($('<input type="hidden" name="bdKind[]" value="' + bd_kind + '">'));
                });
                form.target = 'ifrmProcess';
                form.submit();
                layer_close();
            },
            rules: {
                period: 'required',
                'sno[]': {
                    required: true,
                    maxlength: 4
                }
            },
            messages: {
                period: {
                    required: '기간을 선택해주세요.'
                },
                'sno[]': {
                    required: '노출항목은 최소 1개 선택하셔야 합니다.',
                    maxlength: '노출항목은 4개 까지 선택할 수 있습니다.'
                }
            }
        });
    });
    //-->
</script>
