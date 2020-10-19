<?php
$mode = Request::get()->get('mode', 'search');
$parentFormID = Request::get()->get('parentFormID');
$dataFormID = Request::get()->get('dataFormID');
$dataInputNm = Request::get()->get('dataInputNm');
$layerFormID = Request::get()->get('layerFormID');
$callFunc = Request::get()->get('callFunc', '');
$parentPage = Request::get()->get('parentPage', '');
?>
<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>게시판명</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="bdNm" value="" class="form-control" data-uri="<?php echo URI_ADMIN; ?>"/>
                        <input type="button" value="검색" class="btn btn-hf btn-black" onclick="layer_list_search();">
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="table-header form-inline">
    <div class="pull-left">
        전체 <strong><?= $page->recode['amount']; ?></strong>건
    </div>
</div>
<table class="table table-rows table-fixed">
    <thead>
    <tr>
        <th class="width-2xs">
            <input type="checkbox" class="js-checkall" data-target-name="chk[]"/>
        </th>
        <th class="width-2xs">번호</th>
        <th>게시판명</th>
        <th class="width-md">게시판아이디</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (isset($list) && is_array($list)) {
        $total = count($list);
        $htmlList = [];
        foreach ($list as $row) {
            if($parentPage == 'mailAuto' && $row['bdKind'] == 'event'){
                continue;
            }
            $htmlList[] = '<tr class="center">';
            $htmlList[] = '<td><input type="checkbox" id="layer_board_' . $row['sno'] . '" name="chk[]" value="' . $row['sno'] . '"></td>';
            $htmlList[] = '<td>' . number_format($total--) . '</td>';
            $htmlList[] = '<td>' . $row['bdNm'] . '</td>';
            $htmlList[] = '<td>' . $row['bdId'] . '</td>';
            $htmlList[] = '</tr>';
        }
        echo join('', $htmlList);
    }
    ?>
    </tbody>
</table>
<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>
<div class="text-center">
    <div class="text-center">
        <input type="button" value="확인" class="btn btn-lg btn-black js-close"/>
    </div>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.js-close').click(function (e) {
            var $checkedBoard = $('input[id*=\'layer_board_\']:checked');
            if ($checkedBoard.length == 0) {
                alert('게시판을 선택해 주세요!');
                return false;
            }

            var applyGoodsCnt = 0;
            var chkGoodsCnt = 0;

            var resultJson = {
                "mode": "<?=$mode;?>",
                "parentFormID": "<?=$parentFormID;?>",
                "dataFormID": "<?=$dataFormID;?>",
                "dataInputNm": "<?=$dataInputNm;?>",
                "info": []
            };

            $checkedBoard.each(function () {
                var boardNo = $(this).val();
                var $tr = $(this).closest('tr');
                var boardNm = $tr.find('td:eq(2)').text();
                var boardId = $tr.find('td:eq(3)').text();

                if ($('#' + resultJson.dataFormID + '_' + boardNo).length == 0) {
                    resultJson.info.push({"boardNo": boardNo, "boardNm": boardNm, "boardId": boardId});
                    applyGoodsCnt++;
                }
                chkGoodsCnt++;
            });

            if (applyGoodsCnt > 0) {
                displayTemplate(resultJson);

                <?php if($callFunc !== '') { ?>
                <?=$callFunc;?>(resultJson);
                <?php } ?>

                if (applyGoodsCnt == chkGoodsCnt) {
                    alert(applyGoodsCnt + '개의 게시판이 추가 되었습니다.');
                } else {
                    alert('선택한 ' + chkGoodsCnt + '개의 게시판 중 ' + applyGoodsCnt + '개의 게시판이 추가 되었습니다.');
                }

                if (chkGoodsCnt > 0) {
                    $('#' + resultJson.parentFormID).addClass('active');
                } else {
                    $('#' + resultJson.parentFormID).removeClass('active');
                }

                $('div.bootstrap-dialog-close-button').click();
            } else {
                alert('동일한 게시판이 이미 존재합니다.');
            }
        });

        $('div.bootstrap-dialog-close-button').click(function () {
            if ($('input[name*=\'scmNo\']').length == 0) {
                $('[name=scmFl][value=n]').prop('checked', true);
            }
        });

        $('input').keyup(function (e) {
            if (e.which == 13) layer_list_search();
        });

        $('.js-checkall').click(function (e) {
            $('input:checkbox[name*=\'' + $(this).data('target-name') + '\']:not(:disabled)').prop('checked', this.checked);
        });

    });

    function layer_list_search(pagelink) {
        var bdNm = $('input[name=\'bdNm\']').val();

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            "mode": "<?=$mode;?>",
            "parentFormID": "<?=$parentFormID;?>",
            "dataFormID": "<?=$dataFormID;?>",
            "dataInputNm": "<?=$dataInputNm;?>",
            "parentPage": "<?=$parentPage;?>",
            'layerFormID': '<?=$layerFormID;?>',
            'callFunc': '<?=$callFunc;?>',
            'key': 'bdNm',
            'keyword': bdNm,
            'pagelink': pagelink
        };

        $.get('<?= URI_ADMIN;?>share/layer_board.php', parameters, function (data) {
            $('#<?=$layerFormID;?>').html(data);
        });
    }

    // 화면출력
    function displayTemplate(data) {
        if (data.mode == 'input')   $("#" + data.parentFormID).html('');

        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5')) {
            $('#' + data.parentFormID).prepend('<h5>선택된 게시판</h5>');
        }

        $.each(data.info, function (key, val) {
            var addHtml = '';
            var complied = _.template($('#boardSearchTemplate').html());
            addHtml += complied({
                boardNm: val.boardNm,
                boardNo: val.boardNo,
                dataFormID: data.dataFormID,
                dataInputNm: data.dataInputNm,
                inputArr: (data.mode == 'input' ? '' : '[]')
                //inputArr: '[]'
            });
            $('#' + data.parentFormID).append(addHtml);
        });
    }
    //-->
</script>
<script type="text/html" id="boardSearchTemplate">
    <div id="<%=dataFormID%>_<%=boardNo%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%><%=inputArr%>" value="<%=boardNo%>">
        <input type="hidden" name="<%=dataInputNm%>Nm<%=inputArr%>" value="<%=boardNm%>">
        <span class="btn"><%=boardNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=boardNo%>">삭제</button>
    </div>
</script>
