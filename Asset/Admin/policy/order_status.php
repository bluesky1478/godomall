<form id="frmOrderStatus" name="frmOrderStatus" action="order_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="updateOrderStatus"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>주문 상태에 대한 처리 방법을 변경하거나 상태명을 변경하거나 추가를 하실 수 있습니다.</small>
        </h3>
        <div class="btn-group">
            <?php if ($isUsableMall === true) { ?>
                <a href="./order_status_global.php" class="btn btn-red-line mgr5">해외몰 주문상태 관리</a>
            <?php } ?>
            <input type="submit" value="저장" class="btn btn-red">
        </div>
    </div>

    <div class="table-title gd-help-manual">
        주문 상태별 설정
    </div>
    <div class="table-responsive">
        <table class="table table-cols orderstatus-board table-layout-fixed">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-xl"/>
                <col class="width-sm"/>
                <col class="width-sm"/>
                <col class="width-2xs"/>
                <col class="width-xs"/>
                <col class="width-xs"/>
                <col class="width-xs"/>
                <col class="width-xs"/>
<!--                <col class="width-xs"/>-->
                <col class=""/>
            </colgroup>
            <thead>
            <tr>
                <th rowspan="2">기준상태</th>
                <th rowspan="2">상태설명</th>
                <th rowspan="2">관리자페이지<br />노출이름</th>
                <th rowspan="2">쇼핑몰페이지<br />노출이름</th>
                <th rowspan="2">사용<br />설정</th>
<!--                <th rowspan="2">배송지<br />수정가능</th>-->
                <th colspan="2" class="order-starts">혜택지급시점</th>
                <th colspan="2" class="order-starts">혜택차감시점</th>
                <th rowspan="2">재고차감<br />시점</th>
            </tr>
            <tr>
                <th>마일 리지</th>
                <th>쿠폰</th>
                <th>마일리지</th>
                <th style="border-right: 1px solid #aeaeae;">쿠폰</th>
            </tr>
            </thead>
            <?php
            foreach ($orderStep as $key => $val) {
                gd_isset($policy[$key]['correct'], 'n');
                ?>
                <tr>
                    <th colspan="5">
                        <div class="form-inline">
                            <span class="text-primary"><?php echo $val['title']; ?> 상태 설정</span>
                            <?php if ($val['add'] == 'y') { ?>
                                <button type="button" data-status="<?php echo $key; ?>" class="btn btn-sm btn-white btn-icon-plus js-add-status">추가</button>
                            <?php } ?>
                            <?php if ($val['code'] == 'p') { ?>
                                <span class="notice-danger">통계데이터는 해당 "입금 상태 설정" 그룹의 주문상태로 변경한 시점부터 집계됩니다.</span>
                            <?php } ?>
                        </div>
                    </th>
<!--                    <th class="text-center">-->
<!--                        --><?php //if ($val['correct'] == "y") { ?>
<!--                            <label>-->
<!--                                <input name="tmp[correct]" type="radio" --><?php //echo gd_isset($checked['correct'][$policy[$key]['correct']]); ?><!-- />-->
<!--                            </label>-->
<!--                            <input type="hidden" name="orderStep[--><?php //echo $key; ?><!--][correct]" value="--><?php //echo $policy[$key]['correct']; ?><!--"/>-->
<!--                        --><?php //} ?>
<!--                    </th>-->
                    <th class="text-center">
                        <?php if ($val['mplus'] == "y") { ?>
                            <label>
                                <input type="radio" name="tmp[mplus]" <?php echo gd_isset($checked['mplus'][$policy[$key]['mplus']]); ?> />
                                <?php if ($key == 'payment') { ?>
                                    <br/><small class="top-checkbox">(결제완료 시)</small>
                                <?php } elseif ($key == 'delivery') { ?>
                                    <br/><small class="top-checkbox">(배송완료 시)</small>
                                <?php } elseif ($key == 'settle') { ?>
                                    <br/><small class="top-checkbox">(구매확정 시)</small>
                                <?php } ?>
                            </label>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][mplus]" value="<?= $policy[$key]['mplus']; ?>"/>
                        <?php } ?>
                    </th>
                    <th class="text-center">
                        <?php if ($val['cplus'] == "y") { ?>
                            <label>
                                <input type="radio" name="tmp[cplus]" <?php echo gd_isset($checked['cplus'][$policy[$key]['cplus']]); ?> />
                                <?php if ($key == 'payment') { ?>
                                    <br/><small class="top-checkbox">(결제완료 시)</small>
                                <?php } elseif ($key == 'delivery') { ?>
                                    <br/><small class="top-checkbox">(배송완료 시)</small>
                                <?php } elseif ($key == 'settle') { ?>
                                    <br/><small class="top-checkbox">(구매확정 시)</small>
                                <?php } ?>
                            </label>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][cplus]" value="<?= $policy[$key]['cplus']; ?>"/>
                        <?php } ?>
                    </th>
                    <th class="text-center">
                        <?php if ($key == 'order') { ?>
                            <label>
                                <input type="checkbox" checked="checked" disabled/>
                            </label>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][mminus]" value="y"/>
                        <?php } ?>
                    </th>
                    <th class="text-center">
                        <?php if ($key == 'order') { ?>
                            <label>
                                <input type="checkbox" checked="checked" disabled/>
                            </label>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][cminus]" value="y"/>
                        <?php } ?>
                    </th>
                    <th class="text-center">
                        <?php if ($val['sminus'] == "y") { ?>
                            <label>
                                <input type="radio" name="tmp[sminus]" <?php echo gd_isset($checked['sminus'][$policy[$key]['sminus']]); ?> />
                            </label>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][sminus]" value="<?= $policy[$key]['sminus']; ?>"/>
                        <?php } ?>
                    </th>
                </tr>
                <?php
                foreach ($val['sub'] as $sKey => $sVal) {
                    gd_isset($policy[$key][$sKey]['admin'], $sVal['title']);
                    gd_isset($policy[$key][$sKey]['user'], $sVal['title']);
                    gd_isset($policy[$key][$sKey]['useFl'], 'n');
                    ?>
                    <tr id="<?php echo $key; ?>_<?php echo $sKey; ?>">
                        <td class="text-center bold">
                            <?php echo $sVal['title']; ?>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][mode]" value="<?php echo $sVal['mode']; ?>"/>
                        </td>
                        <td>
                            <p class="notice-info mgb0"><?php echo $sVal['desc']; ?></p>
                        </td>
                        <td class="text-center">
                            <input type="text" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][admin]" value="<?php echo $policy[$key][$sKey]['admin']; ?>" class="form-control"/>
                        </td>
                        <td class="text-center">
                            <input type="text" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][user]" value="<?php echo $policy[$key][$sKey]['user']; ?>" class="form-control"/>
                        </td>
                        <td class="text-center">
                            <?php if ($sVal['indispensable'] == "n") { ?>
                                <label>
                                    <input type="checkbox" <?php echo gd_isset($checked['useFl'][$policy[$key][$sKey]['useFl']]); ?> />
                                </label>
                                <input type="hidden" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][useFl]" value="<?php echo $policy[$key][$sKey]['useFl']; ?>"/>
                            <?php } else { ?>
                                <label>
                                    <input type="checkbox" checked="checked" class="disabled" disabled="disabled">
                                </label>
                                <input type="hidden" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][useFl]" value="y"/>
                            <?php } ?>
                        </td>
                        <td colspan="5">&nbsp;</td>
                    </tr>
                    <?php
                }
            }
            ?>
        </table>
    </div>

    <div class="table-title gd-help-manual">
        취소 상태별 설정
    </div>
    <div class="table-responsive">
        <table class="table table-cols orderstatus-board table-layout-fixed">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-xl"/>
                <col class="width-sm"/>
                <col class="width-sm"/>
                <col class="width-2xs"/>
                <col class="width-xs"/>
                <col class="width-xs"/>
                <col class=""/>
            </colgroup>
            <thead>
            <tr>
                <th rowspan="2">기준상태</th>
                <th rowspan="2">상태설명</th>
                <th rowspan="2">관리자페이지<br/>노출이름</th>
                <th rowspan="2">쇼핑몰페이지<br/>노출이름</th>
                <th rowspan="2">사용설정</th>
                <th colspan="2">혜택복원시점</th>
                <th rowspan="2">재고복원시점</th>
                <th rowspan="2">재고차감시점</th>
            </tr>
            <tr>
                <th>마일리지</th>
                <th style="border-right: 1px solid #aeaeae;">쿠폰</th>
            </tr>
            </thead>
            <?php
            foreach ($cancelStep as $key => $val) {
                gd_isset($policy[$key]['correct'], 'n');
                ?>
                <tr>
                    <th colspan="5">
                        <div class="form-inline">
                            <span class="text-primary"><?php echo $val['title']; ?> 상태 설정</span>
                            <?php if ($val['add'] == 'y') { ?>
                                <button type="button" data-status="<?php echo $key; ?>" class="btn btn-sm  btn-icon-plus js-add-status">추가</button>
                            <?php } ?>
                            <?php if ($key == 'cancel') { ?> :
                                <input type="text" name="orderStep[autoCancel]" value="<?php echo $policy['autoCancel']; ?>" class="form-control input-sm width-2xs js-number"/> 일 동안 미입금시 주문자동취소 <small class="text-muted">(자동취소를 원하지 않는 경우 0으로 설정)</small> <?php } ?>
                        </div>
                    </th>
                    <th class="text-center">
                        <?php if ($val['mrestore'] == "y") { ?>
                            <label>
                                <input type="checkbox" <?php echo gd_isset($checked['mrestore'][$policy[$key]['mrestore']]); ?> checked="checked" disabled="disabled" />
                            </label>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][mrestore]" value="y"/>
                        <?php } ?>
                    </th>
                    <th class="text-center">
                        <?php if ($val['crestore'] == "y") { ?>
                            <label>
                                <input type="checkbox" checked="checked" disabled checked="checked" />
                            </label>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][crestore]" value="y"/>
                        <?php } ?>
                    </th>
                    <th class="text-center">
                        <?php if ($val['srestore'] == "y") { ?>
                            <label>
                                <input type="checkbox" <?php echo gd_isset($checked['srestore'][$policy[$key]['srestore']]); ?><?php if ($key == 'refund') { ?> checked="checked" disabled="disabled"<?php } ?> />
                                <?php if ($key == 'back') { ?>
                                    <br/><small class="top-checkbox">(반품회수완료 시)</small>
                                <?php } elseif ($key == 'exchange') { ?>
                                    <br/><small class="top-checkbox">(교환완료 시)</small>
                                <?php } elseif ($key == 'refund') { ?>
                                    <br/><small class="top-checkbox">(환불완료 시)</small>
                                <?php } ?>
                            </label>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][srestore]" value="<?php echo $key == 'refund' ? 'y' : $policy[$key]['srestore']; ?>"/>
                        <?php } ?>
                    </th>
                    <th class="text-center">
                        <?php if ($val['sminus'] == "y") { ?>
                            <label>
                                <input type="checkbox" <?php echo gd_isset($checked['sminus'][$policy[$key]['sminus']]); ?> />
                            </label>
                            <input type="hidden" name="orderStep[<?php echo $key; ?>][sminus]" value="<?=$policy[$key]['sminus']?>"/>
                        <?php } ?>
                    </th>
                </tr>
                <?php
                foreach ($val['sub'] as $sKey => $sVal) {
                    gd_isset($policy[$key][$sKey]['admin'], $sVal['title']);
                    gd_isset($policy[$key][$sKey]['user'], $sVal['title']);
                    gd_isset($policy[$key][$sKey]['useFl'], 'n');

                    // 결제시도 숨김 처리 (오픈이후 화면단에 출력 예정)
                    if ($sKey === 'f1') {
                ?>
                        <input type="hidden" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][mode]" value="<?php echo $sVal['mode']; ?>"/>
                        <input type="hidden" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][admin]" value="결제시도" class="form-control"/>
                        <input type="hidden" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][user]" value="결제시도" class="form-control"/>
                <?php
                    } else {
                        ?>
                        <tr id="<?php echo $key; ?>_<?php echo $sKey; ?>">
                            <td class="text-center bold">
                                <?php echo $sVal['title']; ?>
                                <input type="hidden" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][mode]" value="<?php echo $sVal['mode']; ?>"/>
                            </td>
                            <td>
                                <p class="notice-info mgb0"><?php echo $sVal['desc']; ?></p>
                            </td>
                            <td class="text-center">
                                <input type="text" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][admin]" value="<?php echo $policy[$key][$sKey]['admin']; ?>" class="form-control"/>
                            </td>
                            <td class="text-center">
                                <input type="text" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][user]" value="<?php echo $policy[$key][$sKey]['user']; ?>" class="form-control"/>
                            </td>
                            <td class="text-center">
                                <?php if ($sVal['indispensable'] == "n") { ?>
                                    <label>
                                        <input type="checkbox" <?php echo gd_isset($checked['useFl'][$policy[$key][$sKey]['useFl']]); ?> />
                                    </label>
                                    <input type="hidden" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][useFl]" value="<?php echo $policy[$key][$sKey]['useFl']; ?>"/>
                                <?php } else { ?>
                                    <label>
                                        <input type="checkbox" checked="checked" class="disabled" disabled="disabled">
                                    </label>
                                    <input type="hidden" name="orderStep[<?php echo $key; ?>][<?php echo $sKey; ?>][useFl]" value="y"/>
                                <?php } ?>
                            </td>
                            <td colspan="4">&nbsp;</td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
        </table>
    </div>

</form>

<script type="text/javascript">
    <!--
    /**
     * 상태 정보 채우기
     */
    function fill_status() {
        <?php
            foreach($policy as $key => $val) {
                if (is_array($policy[$key])) {
                    foreach($policy[$key] as $dKey => $dVal) {
                        if(preg_match('/'.substr($key,0,1).'[0-9]+/',$dKey)){
                            $tmp[$key][$dKey] = $policy[$key][$dKey];
                        }
                    }
                    $subCnt = count($step[$key]['sub']);
                    $subTmpCnt = count($tmp[$key]);
                    if ($subCnt != $subTmpCnt){
                        for ($i = ($subCnt+1); $i <= $subTmpCnt; $i++) {
                            $subKey        = substr($key,0,1) . $i;
                            $checkYn    = 'false';
                            if ($policy[$key][$subKey]['useFl'] == 'y') {
                                $checkYn    = 'true';
                            }
                            echo "add_status('".$key."');".chr(10);
                            echo "$('input[name=\"orderStep[".$key."][".$subKey."][admin]\"]').val('".gd_htmlspecialchars_slashes($policy[$key][$subKey]['admin'],'add')."');".chr(10);
                            echo "$('input[name=\"orderStep[".$key."][".$subKey."][user]\"]').val('".gd_htmlspecialchars_slashes($policy[$key][$subKey]['user'],'add')."');".chr(10);
                            echo "$('input[name=\"orderStep[".$key."][".$subKey."][useFl]\"]').val('".$policy[$key][$subKey]['useFl']."');".chr(10);
                            echo "$('input[name=\"orderStep[".$key."][".$subKey."][useFl]\"]').siblings('label').children('input:checkbox').prop('checked',".$checkYn.");".chr(10);
                        }
                    }
                }
            }
            unset($tmp);
        ?>
    }

    /**
     * 상태 추가하기
     *
     * @param string fieldID 상태별 ID
     */
    function add_status(fieldID) {
        var fieldCnt = $('tr[id*=\'' + fieldID + '\']').length;
        var subFieldID = fieldID.substr(0, 1);
        var fieldSubID = subFieldID + parseInt(fieldCnt + 1);
        var aftFieldID = fieldID + '_' + subFieldID + fieldCnt;
        var newFieldID = fieldID + '_' + fieldSubID;

        var complied = _.template($('#statusAddTemplate').html());
        var html = complied({
            fieldID: fieldID,
            newFieldID: newFieldID,
            subFieldID: subFieldID,
            fieldSubID: fieldSubID,
        });
        $('#' + aftFieldID).after(html);
    }

    /**
     * 상태 최대 갯수 체크하기
     *
     * @param object element 상태추가버튼
     * @param boolean isAdd 상태추가여부
     * @returns {boolean}
     */
    var orderStatusLimit = 9;
    function chkOrderStatusLimit(element, isAdd) {
        var fieldID = $(element).data('status');
        var fieldCnt = $('tr[id*=\'' + fieldID + '\']').length;
        if (isAdd === true && fieldCnt >= orderStatusLimit) {
            return false;
        } else if (fieldCnt > orderStatusLimit) {
            return false;
        }
        return true;
    }

    $(document).ready(function () {
        $("#frmOrderStatus").validate({
            submitHandler: function (form) {
                var pass = true;
                $('.js-add-status').each(function () {
                    if (chkOrderStatusLimit($(this)) !== true) {
                        pass = false;
                        alert('주문 상태별 ' + orderStatusLimit + '개까지 등록가능합니다.');
                        return false;
                    }
                });
                if (pass === false) return false;

                form.target = 'ifrmProcess';
                form.submit();
            }
        });

        $('input[id=\'orderStep[delivery][mminus]\']').prop('disabled', true);
        $('input[id=\'orderStep[delivery][cminus]\']').prop('disabled', true);
        $('input[id*=\'[minus]\']').prop('disabled', true);
        $('input[id*=\'[restore]\']').prop('disabled', true);
        $('input[name*=\'autoCancel\']').number_only();

        // input checkbox 값을 input text 에 넣기
        $(document).on('change', '#frmOrderStatus input:checkbox', function(e){
            if ($(this).prop('checked') == true) {
                $(this).closest('td, th').children('input[type=hidden]').val('y');
            } else {
                $(this).closest('td, th').children('input[type=hidden]').val('n');
            }
        });

        // input radio 값을 input text 에 넣기
        $(document).on('change', '#frmOrderStatus input:radio', function(e){
            $('input[name="'+$(this).prop('name')+'"]:radio').prop('checked', false);
            $('input[name="'+$(this).prop('name')+'"]:radio').closest('td, th').children('input[type=hidden]').val('n');
            $(this).prop('checked', true);
            $(this).closest('td, th').children('input[type=hidden]').val('y');
        });

        $('.js-add-status').click(function(e){
            if (chkOrderStatusLimit($(this), true) !== true) {
                alert('주문 상태는  ' + orderStatusLimit + '개까지 등록가능합니다.');
                return;
            }
            add_status($(this).data('status'));
        });

        $(document).on('click', '.js-delete-row', function(e){
            var tr = $(this).closest('tr');
            var id = tr.attr('id').split('_');
            var params = {
                mode: 'checkOrderStatus',
                orderStatus: id[id.length - 1]
            };
            $.post('../policy/order_ps.php', params, function(data){
                if (data.error == 0) {
                    tr.remove();
                } else {
                    alert(data.message);
                }
            });
            return false;
        });

        fill_status();
    });
    //-->
</script>
<script type="text/html" id="statusAddTemplate">
    <tr id="<%=newFieldID%>" name="<%=fieldID%>add">
        <td class="text-center bold">
            추가 <button type="button" class="btn btn-sm btn-white btn-icon-minus js-delete-row">삭제</button>
            <input type="hidden" name="orderStep[<%=fieldID%>][<%=fieldSubID%>][mode]" value="oi" />
        </td>
        <td>
            <p class="notice-info mgb0">추가된 주문 상태입니다.</p>
        </td>
        <td class="text-center"><input type="text" name="orderStep[<%=fieldID%>][<%=fieldSubID%>][admin]" value="" class="form-control" /></td>
        <td class="text-center"><input type="text" name="orderStep[<%=fieldID%>][<%=fieldSubID%>][user]" value="" class="form-control" /></td>
        <td class="text-center">
            <label><input type="checkbox" /></label>
            <input type="hidden" name="orderStep[<%=fieldID%>][<%=fieldSubID%>][useFl]" value="" />
            </td>
        <td colspan="3"></td>
    </tr>
</script>
