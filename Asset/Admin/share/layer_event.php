<div>
    <div class="mgt10"></div>
    <div>
        <form id="frmEventSearch">
            <table class="table table-cols no-title-line">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                    <col class="width-md"/>
                    <col/>
                    <col class="width-xs"/>
                </colgroup>
                <tr>
                    <th>기획전명</th>
                    <td>
                        <input type="text" name="eventNm" value="<?= $search['keyword']; ?>" class="form-control">
                    </td>
                    <th>상태</th>
                    <td>
                        <?= gd_select_box('statusText', 'statusText', $statusText, null, $search['statusText']); ?>
                    </td>
                    <td rowspan="2">
                        <input type="submit" value="검색" class="btn btn-black btn-hf" onclick="layer_list_search();" />
                    </td>
                </tr>
                <tr>
                    <th>노출범위</th>
                    <td colspan="3">
                        <?= gd_select_box('displayDeviceText', 'displayDeviceText', $displayDeviceText, null, $search['displayDeviceText']); ?>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<div class="table-header">
    <div class="pull-left">
        검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 /
        전체 <strong class="text-danger"><?= number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
    </div>
</div>

<table class="table table-rows">
    <thead>
    <tr>
        <th>선택</th>
        <th>번호</th>
        <th>기획전명</th>
        <th>노출범위</th>
        <th>상태</th>
        <th>등록일</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($data) === false) {
        $sumEventPrice = 0;
        $sumEventMileage = 0;
        foreach ($data as $key => $val) {
            ?>
            <tr class="text-center">
                <td><input type="radio" name="eventNo" value="<?=$val['sno']?>"></td>
                <td><?=number_format($page->idx--)?></td>
                <td>
                    <input type="hidden" id="eventNm_<?php echo $val['sno'];?>" value="<?php echo gd_htmlspecialchars($val['themeNm']);?>" />
                    <?= $val['themeNm']; ?>
                </td>
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
    <button type="submit" class="btn btn-black btn-lg js-close">확인</button>
</div>

<script type="text/javascript">
    $(function(){
        // 기획전 저장하기 클릭시
        $('.js-close').click(function(e){
            if ($('input[name="eventNo"]:checked').length == 0) {
                alert('기획전을 선택해 주세요.');
                return false;
            }

            var applyGoodsCnt = 0,
                chkGoodsCnt = 0,
                resultJson = {
                    mode: "<?php echo $mode?>",
                    parentFormID: "<?php echo $parentFormID?>",
                    dataFormID: "<?php echo $dataFormID?>",
                    dataInputNm: "<?php echo $dataInputNm?>",
                    info: []
                };

            $('input[name="eventNo"]:checked').each(function() {
                var eventNo = $(this).val();
                var eventNm = $('#eventNm_' + eventNo).val();

                if ($('#' + resultJson.dataFormID + '_' + eventNo).length == 0) {
                    resultJson.info.push({
                        eventNo: eventNo,
                        eventNm: eventNm
                    });
                    applyGoodsCnt++;
                }
                chkGoodsCnt++;
            });

            if (applyGoodsCnt > 0) {
                displayTemplate(resultJson);

                if (applyGoodsCnt == chkGoodsCnt) {
                    alert(applyGoodsCnt+'개의 기획전이 추가 되었습니다.');
                } else {
                    alert('선택한 '+chkGoodsCnt+'개의 기획전 중 '+applyGoodsCnt+'개의 기획전이 추가 되었습니다.');
                }

                // 선택된 버튼 div 토글
                if (chkGoodsCnt > 0) {
                    $('#' + resultJson.parentFormID).addClass('active');
                } else {
                    $('#' + resultJson.parentFormID).removeClass('active');
                }

                $('div.bootstrap-dialog-close-button').click();
            } else {
                alert('동일한 기획전이 이미 존재합니다.');
                return false;
            }
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
        var eventNm = $('input[name=\'eventNm\']').val();

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            layerFormID: '<?php echo $layerFormID?>',
            parentFormID: '<?php echo $parentFormID?>',
            dataFormID: '<?php echo $dataFormID?>',
            dataInputNm: '<?php echo $dataInputNm?>',
            callFunc: '<?php echo $callFunc?>',
            mode: '<?php echo $mode?>',
            key: 'all',
            keyword: $('input[name=\'eventNm\']').val(),
            eventUseType: $('select[name=\'eventUseType\']').val(),
            eventDeviceType: $('select[name=\'eventDeviceType\']').val(),
            eventType: $('select[name=\'eventType\']').val(),
            pagelink: pagelink
        };
        console.log(parameters);

        $.get('<?php echo URI_ADMIN;?>share/layer_event.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    // 화면출력
    function displayTemplate(data) {
        $('#' + data.parentFormID).html('');

        $.each(data.info, function (key,val) {
            var addHtml = '';
            var complied = _.template($('#eventTemplate').html());
            addHtml += complied({
                dataFormID: data.dataFormID,
                dataInputNm: data.dataInputNm,
                eventNo: val.eventNo,
                eventNoNm: val.eventNm,
            });
            $('#' + data.parentFormID).html(addHtml);
        });

        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5')) {
            $('#' + data.parentFormID).prepend('<h5>선택된 기획전:</h5>');
        }
    }
</script>
<script type="text/html" id="eventTemplate">
    <div id="<%=dataFormID%>_<%=eventNo%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%>" value="<%=eventNo%>">
        <input type="hidden" name="<%=dataInputNm%>Nm" value="<%=eventNoNm%>">
        <span class="btn"><%=eventNoNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=eventNo%>">삭제</button>
    </div>
</script>
