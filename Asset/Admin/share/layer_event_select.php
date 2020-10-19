<div>
    <div class="mgt10"></div>
    <div>
        <form id="frmEventSearch">
            <table class="table table-cols no-title-line">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                    <col style="width: 50px;" />
                </colgroup>
                <tr>
                    <th>기획전명</th>
                    <td>
                        <input type="text" name="eventNm" value="<?= $search['keyword']; ?>" class="form-control">
                    </td>
                    <td rowspan="4">
                        <input type="submit" value="검색" class="btn btn-black btn-hf" onclick="javascript:layer_list_search();" />
                    </td>
                </tr>
                <tr>
                    <th>진열유형</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="displayCategory" value="" <?= gd_isset($checked['displayCategory']['']); ?>/>전체</label>
                        <label class="radio-inline"><input type="radio" name="displayCategory" value="n" <?= gd_isset($checked['displayCategory']['n']); ?>/>일반형</label>
                        <label class="radio-inline"><input type="radio" name="displayCategory" value="g" <?= gd_isset($checked['displayCategory']['g']); ?>/>그룹형</label>
                    </td>
                </tr>
                <tr>
                    <th>노출범위</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="device" value="" <?= gd_isset($checked['device']['']); ?>/>전체</label>
                        <label class="radio-inline"><input type="radio" name="device" value="yy" <?= gd_isset($checked['device']['yy']); ?>/>PC+모바일</label>
                        <label class="radio-inline"><input type="radio" name="device" value="yn" <?= gd_isset($checked['device']['yn']); ?>/>PC</label>
                        <label class="radio-inline"><input type="radio" name="device" value="ny" <?= gd_isset($checked['device']['ny']); ?>/>모바일</label>
                    </td>
                </tr>
                <tr>
                    <th>진행상태</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="statusText" value="" <?= gd_isset($checked['statusText']['']); ?>/>전체</label>
                        <label class="radio-inline"><input type="radio" name="statusText" value="product" <?= gd_isset($checked['statusText']['product']); ?>/>대기</label>
                        <label class="radio-inline"><input type="radio" name="statusText" value="order" <?= gd_isset($checked['statusText']['order']); ?>/>진행중</label>
                        <label class="radio-inline"><input type="radio" name="statusText" value="delivery" <?= gd_isset($checked['statusText']['delivery']); ?>/>종료</label>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<form id="frmEvent">

<div class="table-header">
    <div class="pull-left">
        검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 /
        전체 <strong class="text-danger"><?= number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
    </div>
</div>

<table class="table table-rows">
    <colgroup>
        <col style="width: 5%;" />
        <col style="width: 5%;" />
        <col />
        <col style="width: 10%;" />
        <col style="width: 10%;" />
        <col style="width: 10%;" />
        <col style="width: 20%;" />
    </colgroup>
    <thead>
    <tr>
        <th>
            <?php if ($mode == 'search') { ?>
                선택
            <?php } else { ?>
                <input type="checkbox" class="js-checkall" data-target-name="layer_eventNo" />
            <?php } ?>
        </th>
        <th>번호</th>
        <th>기획전명</th>
        <th>진열유형</th>
        <th>노출범위</th>
        <th>진행상태</th>
        <th>등록일</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($data) === false) {
        foreach ($data as $key => $val) {
            if($mode == 'search' && count($val['eventGroupArray']) > 0){
                $eventGoupSno = implode(STR_DIVISION, array_column($val['eventGroupArray'], 'sno'));
                $eventGoupName = implode(STR_DIVISION, array_column($val['eventGroupArray'], 'groupName'));
            }
            ?>
            <tr class="text-center">
                <td>
                    <?php if ($mode == 'search') { ?>
                        <input type="radio" id="layer_event_<?=$val['sno']?>" name="layer_event" value="<?=$val['sno']?>" data-eventName="<?= $val['themeNm']; ?>" data-displayCategory="<?= $val['displayCategory'] ?>" data-groupSno="<?php echo $eventGoupSno; ?>" data-groupName="<?php echo $eventGoupName; ?>" />
                    <?php } else { ?>
                        <input type="checkbox" name="layer_eventNo[]" value="<?=$val['sno']?>" data-eventName="<?=$val['themeNm']?>"/>
                    <?php } ?>
                </td>
                <td><?=number_format($page->idx--)?></td>
                <td style="word-break: break-all;"><?= $val['themeNm']; ?></td>
                <td><?= $val['displayCategoryText'] ?></td>
                <td><?= $val['displayDeviceText'] ?></td>
                <td><?= $val['statusText'] ?></td>
                <td><?= $val['regDt'] ?></td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="7" class="no-data">검색 할 기획전이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
<div class="text-center">
    <button type="button" class="btn btn-black btn-lg js-confirm">확인</button>
</div>
</form>

<script type="text/javascript">
    $(function(){
        // 기획전 저장하기 클릭시
        $('.js-confirm').click(function(){
            if('<?php echo $mode; ?>' === 'search'){
                adjust_search_event();
                return;
            }
            if ($('input[name="layer_eventNo[]"]:checked').length == 0) {
                alert('기획전을 선택해 주세요.');
                return false;
            }

            var requestData = {
                layerTargetTable : "<?php echo $layerTargetTable?>", //행이 추가될 테이블 아이디명
                layerTargetCheckboxName : "<?php echo $layerTargetCheckboxName?>", //추가될 행안의 체크박스명
                layerTargetHiddenValueName : "<?php echo $layerTargetHiddenValueName?>" //추가될 행안의 체크박스명
            };
            var targetTableEl = $('#' + requestData.layerTargetTable + '>tbody');
            var totalCount = targetTableEl.find("input[name='"+requestData.layerTargetCheckboxName+"[]']").length;
            var addRow = 0;
            var duplicateRow = 0;
            var alertMessage = '';

            $('input[name="layer_eventNo[]"]:checked').each(function() {
                if(targetTableEl.find('input[name="'+requestData.layerTargetCheckboxName+'[]"][value="'+$(this).val()+'"]').length > 0){
                    duplicateRow++;
                }
                else {
                    //tr 추가
                    var addHtml = '';
                    var complied = _.template($('#eventTemplate').html());
                    addHtml = complied({
                        checkboxName: requestData.layerTargetCheckboxName,
                        eventNo: $(this).val(),
                        eventName: $(this).attr('data-eventName'),
                        hiddenValueName : requestData.layerTargetHiddenValueName,
                        indexNo : ++totalCount
                    });

                    if($("select[name='otherEventSortTypeTb']").val() === 'top'){
                        targetTableEl.prepend(addHtml);
                        reSortEventIndexNo();
                    }
                    else {
                        targetTableEl.append(addHtml);
                    }

                    addRow++;
                }
            });
            if(duplicateRow > 0){
                alertMessage = '중복되는 ' + duplicateRow + '개를 제외한 ';
            }
            alert(alertMessage + addRow + '개의 기획전이 추가 되었습니다.');

            setTimeout(function(){
                layer_close();
            }, 2000);
        });

        // 검색 submit시 처리
        $('#frmEventSearch').submit(function(e){
            layer_list_search();
            e.preventDefault();
            return false;
        });
    });

    // 페이지 출력
    function layer_list_search(pagelink) {
        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            mode: '<?php echo $mode?>',
            layerFormID: '<?php echo $layerFormID?>',
            layerTargetTable: '<?php echo $layerTargetTable?>',
            layerTargetCheckboxName: '<?php echo $layerTargetCheckboxName?>',
            layerTargetHiddenValueName : '<?php echo $layerTargetHiddenValueName?>',
            key: 'all',
            keyword: $('input[name="eventNm"]').val(),
            device : $(':radio[name="device"]:checked').val(),
            displayCategory : $(':radio[name="displayCategory"]:checked').val(),
            statusText : $(':radio[name="statusText"]:checked').val(),
            pagelink: pagelink
        };

        $.get('<?php echo URI_ADMIN;?>share/layer_event_select.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    function adjust_search_event()
    {
        if ($('input[id*=\'layer_event_\']:checked').length == 0) {
            alert('기획전을 선택해 주세요!');
            return false;
        }

        var thisEl = $('input[id*=\'layer_event_\']:checked');

        $("input[name='event_text']").val(thisEl.attr('data-eventName'));
        $("input[name='eventThemeSno']").val(thisEl.val());
        if(thisEl.attr('data-displayCategory') === 'g'){
            $("#eventGroupSelectArea").removeClass("display-none");
            $("#eventGroupSearchSelect").empty();

            var groupSnoArr = thisEl.attr('data-groupSno').split('^|^');
            var groupNameArr = thisEl.attr('data-groupName').split('^|^');

            for ( var i in groupSnoArr ) {
                $("#eventGroupSearchSelect").append('<option value="'+ groupSnoArr[i] +'">' + groupNameArr[i] + '</option>');
            }
        }
        else {
            $("#eventGroupSelectArea").addClass("display-none");
            $("#eventGroupSearchSelect").empty();
        }

        $("#eventSearchResetArea").removeClass("display-none");

        layer_close();
    }
</script>
<script type="text/html" id="eventTemplate">
    <tr class="center">
        <td>
            <input type="checkbox" name="<%=checkboxName%>[]" value="<%=eventNo%>" />
            <input type="hidden" name="<%=hiddenValueName%>[]" value="<%=eventNo%>" />
        </td>
        <td class="js-eventConfigSortIndexNo"><%=indexNo%></td>
        <td><%=eventName%></td>
        <td><button type="button" class="btn btn-white btn-sm btn-delete js-otherEventDelete">삭제</button></td>
    </tr>
</script>
