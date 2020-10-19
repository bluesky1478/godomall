<div>
    <div class="mgt10"></div>
    <div>
        <form id="layer_search_purchase">
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-xs"/>
                <col/>
                <col class="width-3xs"/>
            </colgroup>
            <tr>
                <th>검색어</th>
                <td><div class="form-inline">
                        <?=gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control width-2xl"/>
                    </div>
                </td>
                <td rowspan="3"">
                <input type="button" value="검색" class="btn btn-black btn-hf" onclick="layer_list_search();">
                </td>
            </tr>
            <tr>
                <th>사용상태</th>
                <td>
                    <label  class="radio-inline"><input type="radio" name="useFl" value="" <?=gd_isset($checked['useFl']['all']);?> />전체</label>
                    <label  class="radio-inline"><input type="radio" name="useFl" value="y" <?=gd_isset($checked['useFl']['y']);?> />사용함</label>
                    <label  class="radio-inline"><input type="radio" name="useFl" value="n" <?=gd_isset($checked['useFl']['n']);?> />사용안함</label>
                </td>
            </tr>
            <tr>
                <th>거래상태</th>
                <td>
                    <label  class="radio-inline"><input type="radio" name="businessFl" value="" <?=gd_isset($checked['businessFl']['all']);?> />전체</label>
                    <label  class="radio-inline"><input type="radio" name="businessFl" value="y" <?=gd_isset($checked['businessFl']['y']);?> />거래중</label>
                    <label  class="radio-inline"><input type="radio" name="businessFl" value="n" <?=gd_isset($checked['businessFl']['n']);?> />거래중지</label>
                    <label  class="radio-inline"><input type="radio" name="businessFl" value="x" <?=gd_isset($checked['businessFl']['x']);?> />거래해지</label>
                </td>
            </tr>
        </table>
        </form>
    </div>
</div>

<div>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width5p"> <?php if ($mode == 'radio') { ?>선택 <?php } else { ?>
                    <input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'layer_purchase_');"/><?php } ?>
            </th>
            <th class="width5p">번호</th>
            <th class="width15p">매입처 코드</th>
            <th class="width20p">매입처 자체코드</th>
            <th class="width20p">매입처명</th>
            <th class="width15p">사용상태</th>
            <th class="width15p">거래상태</th>
            <th class="width20p">상품유형</th>
            <th class="width15p">등록일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data) && is_array($data)) {
            $businessFl    = array('y' => '거래중', 'n' => '거래중지', 'x' => '거래해지');
            $useFl        = array('y' => '사용', 'n' => '사용안함');
            $i = 0;
            foreach ($data as $key => $val) {
                ?>
                <tr>
                    <td class="center">
                        <?php if ($mode == 'radio' || $mode =='radio-search') { ?>
                            <input type="radio" id="layer_purchase_<?=$val['purchaseNo']; ?>" name="layer_purchase" value="<?=$val['purchaseNo']; ?>"/>
                        <?php } else { ?>
                            <input type="checkbox" id="layer_purchase_<?=$val['purchaseNo']; ?>" name="layer_purchase_<?=$i; ?>" value="<?=$val['purchaseNo']; ?>"/>
                        <?php } ?>

                    </td>
                    <td class="center"><?=number_format($page->idx--); ?></td>
                    <td class="center"><?=$val['purchaseNo']?></td>
                    <td class="center"><?=$val['purchaseCd']?></td>
                    <td>
                        <label for="layer_purchase_<?=$val['purchaseNo']; ?>" class="hand"> <?=$val['purchaseNm']?></label>
                        <input type="hidden" id="purchaseNo_<?=$val['purchaseNo']; ?>" value="<?=gd_htmlspecialchars($val['purchaseNm']); ?>"/>
                    </td>
                    <td class="center"><?=$useFl[$val['useFl']]?></td>
                    <td class="center"><?=$businessFl[$val['businessFl']]?></td>
                    <td class="center"><?=$val['category']?></td>
                    <td class="center"><?=gd_date_format('Y-m-d', $val['regDt']); ?></td>
                </tr>
                <?php
                $i++;
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="9">검색을 이용해 주세요.</td>
            </tr>
            <?php
        }
        ?>

        </tbody>
    </table>

    <div class="text-center"><?=$page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>
</div>

<div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black" onclick="select_code();" /></div>


<script type="text/javascript">
    <!--


    $(document).ready(function () {

        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });
    });

    function layer_list_search(pagelink) {

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }

        var frm = $("#layer_search_purchase").serializeArray();
        var parameters = {
            'layerFormID': '<?=$layerFormID?>',
            'parentFormID': '<?=$parentFormID?>',
            'dataFormID': '<?=$dataFormID?>',
            'dataInputNm': '<?=$dataInputNm?>',
            'mode': '<?=$mode?>',
            'callFunc': '<?=$callFunc?>',
            'childRow': '<?=$childRow?>',
            'search': '<?=$search?>',
            'pagelink': pagelink
        };

        $.each(frm, function(i, field){
            if(field.name) parameters[field.name] = field.value;
        });

        $.get('../share/layer_purchase.php', parameters, function (data) {
            $('#<?=$layerFormID?>').html(data);
        });
    }

    function select_code() {
        if ($('input[id*=\'layer_purchase_\']:checked').length == 0) {
            alert('매입처를 선택해 주세요!');
            return false;
        }

        var applyGoodsCnt = 0;
        var chkGoodsCnt = 0;
        var resultJson = {
            mode: "<?=$mode?>",
            parentFormID: "<?=$parentFormID?>",
            dataFormID: "<?=$dataFormID?>",
            dataInputNm: "<?=$dataInputNm?>",
            childRow: "<?=$childRow?>",
            search: '<?=$search?>',
            info: []
        };

        $('input[id*=\'layer_purchase\']:checked').each(function () {
            var purchaseNo = $(this).val();
            var purchaseNm = $('#purchaseNo_' + purchaseNo).val();

            if ($('#<?=$dataFormID?>_' + purchaseNo).length == 0) {
                resultJson.info.push({"purchaseNo": purchaseNo, "purchaseNm": purchaseNm});
                applyGoodsCnt++;
            }
            chkGoodsCnt++;
        });

        if (applyGoodsCnt > 0) {
            <?php if($callFunc) { ?>
            <?=$callFunc?>(resultJson);
            <?php } else { ?>
            displayTemplate(resultJson);
            <?php } ?>

            if (applyGoodsCnt != chkGoodsCnt) {
                alert('선택한 ' + chkGoodsCnt + '개의 매입처 중 ' + applyGoodsCnt + '개의 매입처가 추가 되었습니다.');
            }

            // 선택된 버튼 div 토글
            if (chkGoodsCnt > 0) {
                $('#' + resultJson.parentFormID).addClass('active');
            } else {
                $('#' + resultJson.parentFormID).removeClass('active');
            }

            $('div.bootstrap-dialog-close-button').click();
        } else {
            alert('동일한 매입처가 이미 존재합니다.');
        }
    }

    function displayTemplate(data) {
        if (data.dataInputNm == '') {
            data.dataInputNm = 'purchaseNo';
        }

        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5') && data.mode != 'simple') {
            $('#' + data.parentFormID).prepend('<h5>선택된 매입처</h5>');
        }

        var parentFormCount = $('#' + data.parentFormID+' tr').length;

        if (data.mode == 'radio' || data.mode == 'radio-search') {
            $("#" + data.parentFormID).html('');
            $.each(data.info, function (key, val) {
                var childRow = data.childRow ? Number(data.childRow) : 0;
                var addHtml = "";
                if(data.mode == 'radio-search'){
                    var complied = _.template($('#brandSearchInputTemplate').html());
                } else{
                    var complied = _.template($('#brandInputTemplate').html());
                }
                addHtml += complied({
                    purchaseNm: val.purchaseNm,
                    purchaseNo: val.purchaseNo,
                    dataFormID: data.dataFormID,
                    dataInputNm: data.dataInputNm,
                    key: key + childRow + parentFormCount
                });

                $("#" + data.parentFormID).append(addHtml);
                if (data.mode == 'radio-search' && data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5')) {
                    $('#' + data.parentFormID).prepend('<h5>선택된 매입처:</h5>');
                }
            });
        } else if (data.mode == 'checkbox' || data.mode == 'simple' || data.mode == 'search') {
            $.each(data.info, function (key, val) {
                var addHtml = "";

                if (data.mode == 'simple') {
                    var complied = _.template($('#brandSimpleTemplate').html());
                    addHtml += complied({
                        purchaseNm: val.purchaseNm,
                        purchaseNo: val.purchaseNo,
                        dataFormID: data.dataFormID,
                        dataInputNm: data.dataInputNm,
                        key: key + Number(data.childRow) + parentFormCount
                    });
                } else {
                    var complied = _.template($('#brandSearchTemplate').html());
                    addHtml += complied({
                        purchaseNm: val.purchaseNm,
                        purchaseNo: val.purchaseNo,
                        dataFormID: data.dataFormID,
                        dataInputNm: data.dataInputNm
                    });
                }

                //$("#" + data.parentFormID).append(addHtml);
                $("#" + data.parentFormID+" h5").after(addHtml);
            });
            $('#purchaseNoneFlText').html('선택한 매입처 미지정 상품');
        }
    }
    //-->
</script>

<script type="text/html" id="brandInputTemplate">
    <span id="<%=dataFormID%>_<%=purchaseNo%>" class="pull-left">
	<input type="hidden" name="<%=dataInputNm%>" value="<%=purchaseNo%>"/>
	<%
	if($('input[name=' + dataInputNm + 'Nm]').length > 0) {
		$('input[name=' + dataInputNm + 'Nm]').val(purchaseNm);
        if($('#'+dataInputNm+'Del').length > 0) {
            $('#'+dataInputNm+'Del').show();
        }
	} else {
	%>
	<input type="hidden" name="<%=dataInputNm%>Nm" value="<%=purchaseNm%>"/>
	<span class="outline"><b>선택된 매입처</b> <%=purchaseNm%></span>
	<span class="button gray small"><input type="button" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#<%=dataFormID%>_<%=purchaseNo%>" value="삭제"/></span>
	</span>
    <% } %>
</script>

<script type="text/html" id="brandSimpleTemplate">
    <tr id="<%=dataFormID%>_<%=purchaseNo%>">
        <td class="center"><span class="number"><%=(key + 1)%></span><input type="hidden" name="<%=dataInputNm%>[]" value="<%=purchaseNo%>"/>
            <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=purchaseNm%>"/></td>
        <td><%=purchaseNm%></td>
        <td class="center">
            <input type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=purchaseNo%>" value="삭제"/>
        </td>
    </tr>
</script>

<script type="text/html" id="brandSearchTemplate">
    <div id="<%=dataFormID%>_<%=purchaseNo%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%>[]" value="<%=purchaseNo%>">
        <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=purchaseNm%>">
        <span class="btn"><%=purchaseNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=purchaseNo%>" data-none="#purchaseNoneFlText">삭제</button>
    </div>
</script>
<script type="text/html" id="brandSearchInputTemplate">
    <div id="<%=dataFormID%>_<%=purchaseNo%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%>" value="<%=purchaseNo%>">
        <input type="hidden" name="<%=dataInputNm%>Nm" value="<%=purchaseNm%>">
        <span class="btn"><%=purchaseNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=purchaseNo%>">삭제</button>
    </div>
</script>
