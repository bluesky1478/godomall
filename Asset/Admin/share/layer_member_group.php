<table class="table table-cols">
    <colgroup>
        <col class="width-sm"/>
        <col/>
    </colgroup>
    <tr>
        <th>회원등급명</th>
        <td>
            <div class="form-inline">
                <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                <input type="button" value="검색" class="btn btn-hf btn-black" onclick="layer_list_search();"/>
            </div>
        </td>
    </tr>
</table>

<table class="table table-rows no-title-line">
    <thead>
    <tr>
        <th class="width5p">
            <input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'layer_group_');"/>
        </th>
        <th class="width5p">번호</th>
        <th class="width50p">회원등급명</th>
        <th class="width10p">등록일</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (gd_isset($data) && is_array($data)) {
        $i = 0;
        foreach ($data as $key => $val) {

            ?>
            <tr id="tbl_goods_<?php echo $val['sno']; ?>">
            <td class="center">
                <input type="checkbox" id="layer_group_<?php echo $val['sno']; ?>" name="layer_group_<?php echo $i; ?>" value="<?php echo $val['sno']; ?>"/>
            </td>
            <td class="center"><?php echo number_format($key + 1); ?></td>
            <td><?php echo $val['groupNm']; ?>
                <input type="hidden" id="groupNm_<?php echo $val['sno']; ?>" value="<?php echo gd_htmlspecialchars($val['groupNm']); ?>"/>
            </td>
            <td><?php echo gd_date_format('Y-m-d', $val['regDt']); ?></td>
            <?php
            $i++;
        }
    } else {
        ?>
        <tr>
            <td class="center" colspan="4">검색을 이용해 주세요.</td>
        </tr>
        <?php
    }
    ?>

    </tbody>
</table>
<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>

<div class="text-center">
    <button type="button" class="btn btn-lg btn-black" onclick="select_code(this);">확인</button>
</div>


<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('input').keydown(function (e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });
    });

    function layer_list_search(pagelink) {
        var keyword = $('input[name=\'keyword\']').val();

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'parentFormID': '<?php echo $parentFormID?>',
            'dataFormID': '<?php echo $dataFormID?>',
            'dataInputNm': '<?php echo $dataInputNm?>',
            'keyword': keyword,
            'pagelink': pagelink,
        };
        $.get('../share/layer_member_group.php', parameters, function (data) {
            console.log(data);
            $('#<?php echo $layerFormID?>').html(data);
        });
    }


    function select_code() {
        var $this = $(arguments[0]);
        if ($('input[id*=\'layer_group_\']:checked').length == 0) {
            alert('등급을 선택해 주세요!');
            return false;
        }

        var applyGoodsCnt = 0;
        var chkGoodsCnt = 0;
        var resultJson = {
            "mode": "<?php echo $mode?>",
            "parentFormID": "<?php echo $parentFormID?>",
            "dataFormID": "<?php echo $dataFormID?>",
            "dataInputNm": "<?php echo $dataInputNm?>",
            "info": []
        };

        var layer_group_checkbox = $('input[id*=\'layer_group_\']:checked');
        layer_group_checkbox.each(function () {
            var groupSno = $(this).val();
            var groupNm = $('#groupNm_' + groupSno).val();
            if ($('#<?php echo $dataFormID?>_' + groupSno).length == 0) {
                resultJson.info.push({"groupSno": groupSno, "groupNm": groupNm});
                applyGoodsCnt++;
            }
            chkGoodsCnt++;
        });

        // 등급 레이어 닫히면서 실행될 함수
        function closeCallback() {
            if (chkGoodsCnt > 0) {
                $('#' + resultJson.parentFormID).addClass('active');
            } else {
                $('#' + resultJson.parentFormID).removeClass('active');
            }
            var modal_id = $this.closest('.modal').attr('id');
            var dialog = BootstrapDialog.dialogs[modal_id];
            dialog.options.selected_group = resultJson;
            layer_group_checkbox.prop('checked', false);
            dialog.close();
        }

        if (applyGoodsCnt > 0) {
            <?php if($callFunc) { ?>
            <?=$callFunc?>(resultJson);
            <?php } else { ?>
            displayTemplate(resultJson);
            <?php } ?>

            if (applyGoodsCnt != chkGoodsCnt) {
                BootstrapDialog.show({
                    title: '경고',
                    message: '선택한 ' + chkGoodsCnt + '개의 등급 중 ' + applyGoodsCnt + '개의 등급이 추가 되었습니다.',
                    buttons: [{
                        label: '확인', cssClass: 'btn-black', hotkey: 13, size: BootstrapDialog.SIZE_LARGE,
                        action: function (dialog) {
                            dialog.close();
                            closeCallback();
                        }
                    }],
                    events: {
                        onhidden: function () {
                            closeCallback();
                        }
                    }
                });
            } else {
                closeCallback();
            }
        } else {
            alert('동일한 등급이 이미 존재합니다.');
        }

    }

    function displayTemplate(data) {
        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5')) {
            <?php if($mode != 'simple') { ?>
                $('#' + data.parentFormID).prepend('<h5>선택된 회원등급</h5>');
            <?php } ?>
        }
        <?php if($mode =='simple') { ?>
        var parentFormCount = $('#' + data.parentFormID+' tr').length;
        <?php } ?>

        $.each(data.info, function (key, val) {
            var addHtml = '';
            var complied = _.template($('#memberGroupTemplate').html());
            <?php if($mode =='simple') { ?>
            var childRow = data.childRow ? Number(data.childRow) : 0;
            <?php } ?>
            addHtml += complied({
                groupNm: val.groupNm,
                groupSno: val.groupSno,
                dataFormID: data.dataFormID,
                dataInputNm: data.dataInputNm,
                <?php if($mode =='simple') { ?>
                key: key + Number(childRow) + parentFormCount,
                <?php } ?>
                inputArr: (data.mode == 'search' ? '[]' : '')
            });
            $('#' + data.parentFormID).append(addHtml);
        });
    }

    //-->
</script>

<?php if($mode == 'simple') { ?>
    <script type="text/html" id="memberGroupTemplate">
        <tr id="<%=dataFormID%>_<%=groupSno%>">
            <td class="center"><span class="number"><%=(key + 1)%></span>
                <input type="hidden" name="<%=dataInputNm%>[]" value="<%=groupSno%>">
                <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=groupNm%>">
            <td><%=groupNm%></td>
            <td class="center">
                <input type="button" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#<%=dataFormID%>_<%=groupSno%>" value="삭제"/>
            </td>
        </tr>
    </script>
<?php } else { ?>
<script type="text/html" id="memberGroupTemplate">
    <div id="<%=dataFormID%>_<%=groupSno%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%><%=inputArr%>" value="<%=groupSno%>">
        <input type="hidden" name="<%=dataInputNm%>Nm<%=inputArr%>" value="<%=groupNm%>">
        <span class="btn"><%=groupNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=groupSno%>">삭제</button>
    </div>
</script>
<?php } ?>
