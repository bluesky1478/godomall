<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <button type="button" class="btn btn-red-line js-add-field-insert">추가정보 등록</button>
    </div>
</div>
<form id="frmConfig" name="frmConfig" action="./order_add_field_ps.php" target="ifrmProcess" method="post">
    <input type="hidden" name="mode" value="configOrderAddField"/>
    <input type="hidden" name="mallSno" value="<?php echo $mallSno; ?>"/>

    <?php if ($mallCnt > 1) { ?>
        <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
            <?php foreach ($mallList as $key => $mall) { ?>
                <li role="presentation" class="js-popover <?php echo $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?php echo $mall['mallName']; ?>" data-placement="top">
                    <a href="./order_add_field_list.php?mallSno=<?php echo $mall['sno']; ?>">
                        <span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span>
                        <span class="mall-name"><?php echo $mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <div class="table-title gd-help-manual">
        추가정보 사용 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col class="width-lg">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th>추가정보 사용여부</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldUseFl" value="y" <?= gd_isset($checked['orderAddFieldUseFl']['y']); ?>/>사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderAddFieldUseFl" value="n" <?= gd_isset($checked['orderAddFieldUseFl']['n']); ?>/>사용안함
                </label>
            </td>
            <td>
                <button type="submit" class="btn btn-red">저장</button>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<form id="frmSort" name="frmSort" action="./order_add_field_ps.php" target="ifrmProcess" method="post">
    <input type="hidden" name="mode" value="changeOrderAddFieldSort"/>
    <div class="table-title gd-help-manual">
        추가정보 리스트
    </div>

    <div class="table-action mgb0 mgt0">
        <div class="pull-left">
            <div class="btn-group">
                <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">맨위</button>
                <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">위</button>
                <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">아래</button>
                <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">맨아래</button>
            </div>
        </div>
        <button type="submit" class="mgl10 btn btn-red">순서저장</button>
    </div>
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width-3xs"><input type="checkbox" class="js-checkall" data-target-name="chkAddField[]"/></th>
            <th class="width-2xs">노출순서</th>
            <?php if ($mallCnt > 1) { ?>
            <th class="width-sm">상점 구분</th>
            <?php } ?>
            <th class="width-2xs">노출상태</th>
            <th class="width-2xs">필수여부</th>
            <th class="width-sm">항목명</th>
            <th class="width-sm">노출유형</th>
            <th class="">상세설정</th>
            <th class="width-sm">상품조건</th>
            <th class="width-sm">예외조건</th>
            <th class="width-xs">등록일/수정일</th>
            <th class="width-xs">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($data) {
            $i = 1;
            foreach ($data as $key => $val) {
                if ($mallCnt > 1) {
                    $mallDataSno = gd_isset($val['mallSno'], 1);
                } else {
                    $mallDataSno = '';
                }
                ?>
                <tr class="text-center move-row">
                    <td><input type="checkbox" name="chkAddField[]" value="<?= $val['orderAddFieldNo'] ?>"/></td>
                    <td><?= $i ?>
                        <input type="hidden" name="orderAddFieldSort[]" value="<?= $val['orderAddFieldNo'] ?>"/></td>
                    <?php if ($mallCnt > 1) { ?>
                        <td>
                            <span class="va-m flag flag-32 flag-<?php echo $mallList[$mallSno]['domainFl']; ?>"></span>
                            <?php echo $mallList[$mallSno]['mallName']; ?>
                            <input type="hidden" name="mallSno[<?= $key; ?>]" value="<?php echo $mallSno; ?>" />
                        </td>
                    <?php } ?>
                    <td><?= gd_select_box('orderAddFieldDisplay[' . $val['orderAddFieldNo'] . ']', 'orderAddFieldDisplay[' . $val['orderAddFieldNo'] . ']', $display, null, $val['orderAddFieldDisplay'], null, 'onChange="changeOrderAddFieldDisplay(' . $val['orderAddFieldNo'] . ');"') ?></td>
                    <td><?= gd_select_box('orderAddFieldRequired[' . $val['orderAddFieldNo'] . ']', 'orderAddFieldRequired[' . $val['orderAddFieldNo'] . ']', $required, null, $val['orderAddFieldRequired'], null, 'onChange="changeOrderAddFieldRequired(' . $val['orderAddFieldNo'] . ');"') ?></td>
                    <td><?= $val['orderAddFieldName'] ?></td>
                    <td><?= $convertData[$key]['orderAddFieldType'] ?></td>
                    <td class="left"><?= $convertData[$key]['orderAddFieldOption'] ?></td>
                    <td><?= $convertData[$key]['orderAddFieldApply'] ?></td>
                    <td><?= $convertData[$key]['orderAddFieldExcept'] ?></td>
                    <td><?= gd_date_format('Y-m-d', $val['regDt']); if ($val['modDt']) {echo "<br/>" . gd_date_format('Y-m-d', $val['modDt']);} ?></td>
                    <td>
                        <a href="order_add_field_regist.php?<?= (empty($val['mallSno']) === false) ? 'mallSno=' . $val['mallSno'] . '&' : ''?>orderAddFieldNo=<?= $val['orderAddFieldNo'] ?>" class="btn btn-sm btn-white">수정</a>
                        <button class="btn btn-sm btn-white js-add-field-del" data-no="<?= $val['orderAddFieldNo']; ?>">삭제</button>
                    </td>
                </tr>
                <?php
                $i++;
            }
        } else {
            ?>
            <tr class="text-center move-row">
                <td colspan="11">등록된 주문서 추가정보가 없습니다.</td>
            </tr>
        <?php
        }
        ?>
    </table>
    <div class="table-action">
        <div class="pull-left">
            <div class="btn-group">
                <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">맨위</button>
                <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">위</button>
                <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">아래</button>
                <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">맨아래</button>
            </div>
        </div>
        <button type="submit" class="mgl10 btn btn-red">순서저장</button>
    </div>
</form>

<form id="frmDelete" name="frmDelete" action="./order_add_field_ps.php" target="ifrmProcess" method="post">
    <input type="hidden" name="mode" value="deleteOrderAddField"/>
    <input type="hidden" name="orderAddFieldNo" value=""/>
    <input type="hidden" name="mallSno" value="<?php echo $mallSno; ?>"/>
</form>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.js-add-field-insert').click(function () {
            <?php
            if ($dataCount >= LIMIT_ORDER_ADD_FIELD_AMOUNT) {
            ?>
            alert('주문서 추가정보는 최대 <?= LIMIT_ORDER_ADD_FIELD_AMOUNT; ?>개까지 등록 가능합니다.');
            return false;
            <?php } else { ?>
            location.href = "order_add_field_regist.php<?php echo empty($mallSno) === false ? '?mallSno=' . $mallSno : ''; ?>";
            return false;
            <?php } ?>
        });
        $('.js-add-field-del').click(function () {
            var addFieldNo = $(this).data('no');
            dialog_confirm('정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
                if (result) {
                    $('#frmDelete > input:hidden[name="orderAddFieldNo"]').val(addFieldNo);
                    $('#frmDelete').submit();
                }
            });
            return false;
        });
        $('.js-layer-apply').click(function () {
            var no = $(this).data('no');
            var type = $(this).data('type');
            layerOrderAddFieldApply(type, no);
        });
        $('.js-layer-except').click(function () {
            var no = $(this).data('no');
            var type = $(this).data('type');
            layerOrderAddFieldExcept(type, no);
        });

        $('input:checkbox[name="chkAddField[]"]').click(function () {
            var row = $(this).parent('td').parent('tr');
            if ($(this).prop("checked") == true) {
                row.addClass('warning');
            } else {
                row.removeClass('warning');
            }
        });

        // Row 이동 - 키보드
        $(document).keydown(function (event) {
            switch (event.keyCode) {
                case 38:
                    changeTableRowPrev();
                    break;
                case 40:
                    changeTableRowNext();
                    break;
            }
        });

        // Row 이동 - 버튼
        $('.js-moverow').click(function (e) {
            switch ($(this).data('direction')) {
                case 'up':
                    changeTableRowPrev();
                    break;
                case 'down':
                    changeTableRowNext();
                    break;
                case 'top':
                    changeTableRowPrevFirst();
                    break;
                case 'bottom':
                    changeTableRowNextLast();
                    break;
            }
        });
    });

    function changeOrderAddFieldDisplay(orderAddFieldNo) {
        $.ajax({
            type: 'post',
            url: './order_add_field_ps.php',
            data: {
                mode: 'changeOrderAddFieldDisplay',
                orderAddFieldNo: orderAddFieldNo,
                orderAddFieldDisplay: $('select[name="orderAddFieldDisplay[' + orderAddFieldNo + ']"').val(),
                mallSno: $('input[name="mallSno"]').val()
            },
            success: function (data) {
                alert(data);
            },
            error: function () {
                alert('다시 시도해 주세요.');
                location.reload();
            }
        });
    }

    function changeOrderAddFieldRequired(orderAddFieldNo) {
        $.ajax({
            type: 'post',
            url: './order_add_field_ps.php',
            data: {
                mode: 'changeOrderAddFieldRequired',
                orderAddFieldNo: orderAddFieldNo,
                orderAddFieldRequired: $('select[name="orderAddFieldRequired[' + orderAddFieldNo + ']"').val(),
                mallSno: $('input[name="mallSno"]').val()
            },
            success: function (data) {
                alert(data);
            },
            error: function () {
                alert('다시 시도해 주세요.');
                location.reload();
            }
        });
    }

    // 위로 이동
    function changeTableRowPrev() {
        $('input:checkbox[name="chkAddField[]"]').each(function () {
            if ($(this).prop("checked") == true) {
                var row = $(this).parent('td').parent('tr');
                if (typeof row.prev().html() != 'undefined') {
                    row.insertBefore(row.prev());
                } else {
                    return false;
                }
            }
        });
    }

    // 아래로 이동
    function changeTableRowNext() {
        $($('input:checkbox[name="chkAddField[]"]').get().reverse()).each(function () {
            if ($(this).prop("checked") == true) {
                var row = $(this).parent('td').parent('tr');
                if (typeof row.next().html() != 'undefined') {
                    row.insertAfter(row.next());
                } else {
                    return false;
                }
            }
        });
    }

    // 맨위로 이동
    function changeTableRowPrevFirst() {
        var rowCount = $('input:checkbox[name="chkAddField[]"]').length;
        for (i = 0; i < rowCount; i++) {
            changeTableRowPrev();
        }
    }

    // 맨아래로 이동
    function changeTableRowNextLast() {
        var rowCount = $('input:checkbox[name="chkAddField[]"]').length;
        for (i = 0; i < rowCount; i++) {
            changeTableRowNext();
        }
    }

    function layerOrderAddFieldApply(type, no) {
        var loadChk = $('#formAddFieldApply').length;
        if (type == 'category') {
            var kind = '특정 카테고리';
        } else if (type == 'brand') {
            var kind = '특정 브랜드';
        } else if (type == 'goods') {
            var kind = '특정 상품';
        }
        var title = '상품 조건 - ' + kind;
        $.post('layer_order_add_field.php', {mode: 'apply', no: no}, function (data) {
            if (loadChk == 0) {
                layerForm = '<div id="formAddFieldApply">' + data + '</div>';
            }

            BootstrapDialog.show({
                title: title,
                message: $(layerForm),
                closable: true
            });
        });
    }

    function layerOrderAddFieldExcept(type, no) {
        var loadChk = $('#formAddFieldExcept').length;
        if (type == 'category') {
            var kind = '예외 카테고리';
        } else if (type == 'brand') {
            var kind = '예외 브랜드';
        } else if (type == 'goods') {
            var kind = '예외 상품';
        }
        var title = '예외 조건 - ' + kind;
        $.post('layer_order_add_field.php', {mode: 'except', type: type, no: no}, function (data) {
            if (loadChk == 0) {
                layerForm = '<div id="formAddFieldExcept">' + data + '</div>';
            }

            BootstrapDialog.show({
                title: title,
                message: $(layerForm),
                closable: true
            });
        });
    }
    //-->
</script>
