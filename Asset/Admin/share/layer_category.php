<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-xs"/>
                <col/>
                <col class="width-3xs"/>
            </colgroup>
            <tr>
                <th>카테고리명</th>
                <td>
                    <input type="text" name="cateNm" value="<?php echo $search['cateNm']; ?>" class="form-control"/>
                </td>
                <td rowspan="2">
                    <input type="button" value="검색" class="btn btn-hf btn-black" onclick="layer_list_search();">
                </td>
            </tr>
            <tr>
                <th>카테고리선택</th>
                <td>
                    <div class="form-inline">
                        <?=$cate->getMultiCategoryBox('cateBatch', gd_isset($search['cateGoods']), 'class="form-control"');?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width5p"> <?php if ($mode == 'radio') { ?>선택 <?php } else { ?>
                    <input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'layer_category_');"/><?php } ?>
            </th>
            <th class="width5p">번호</th>
            <?php if ($gGlobal['isUse'] === true) { ?><th class="width10p">노출상점</th><?php } ?>
            <th class="width50p">카테고리명</th>
            <th class="width15p">등록일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data) && is_array($data)) {
            $i = 0;
            foreach ($data as $key => $val) {
                ?>
                <tr>
                    <td class="center">
                        <?php if ($mode == 'radio') { ?>
                            <input type="radio" id="layer_category_<?php echo $val['cateCd']; ?>" name="layer_category" value="<?php echo $val['cateCd']; ?>"/>
                        <?php } else { ?>
                            <input type="checkbox" id="layer_category_<?php echo $val['cateCd']; ?>" name="layer_category_<?php echo $i; ?>" value="<?php echo $val['cateCd']; ?>"/>
                        <?php } ?>

                    </td>
                    <td class="center"><?php echo number_format($page->idx--); ?></td>
                    <?php if ($gGlobal['isUse'] === true) { ?><td>
                        <?php foreach(explode(",",$val['mallDisplay']) as $mallKey => $mallValue) {
                            if($useMallList[$mallValue]['domainFl']) {
                            ?>
                            <span class="js-popover flag flag-16 flag-<?=$useMallList[$mallValue]['domainFl']?>" data-content="<?=$useMallList[$mallValue]['mallName']?>"></span>
                        <?php }
                        } ?>
                        </td><?php } ?>
                    <td>
                        <label for="layer_category_<?php echo $val['cateCd']; ?>" class="hand"><?php echo $cate->getCategoryPosition($val['cateCd'],0,' &gt; ',false,false); ?></label>
                        <input type="hidden" id="cateNm_<?php echo $val['cateCd']; ?>" value="<?php echo gd_htmlspecialchars($cate->getCategoryPosition($val['cateCd'],0,' &gt; ',false,false)); ?>"/>
                    </td>
                    <td class="center"><?php echo gd_date_format('Y-m-d', $val['regDt']); ?></td>
                </tr>
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

    <div class="center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>
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
        var cateNm = $('input[name=\'cateNm\']').val();

        var cateGoods = '';
        for (var i = <?php echo DEFAULT_DEPTH_CATE;?>; i > 0; i--) {
            if ($('#cateBatch' + i).length > 0) {
                if ($('#cateBatch' + i).val()) {
                    cateGoods = $('#cateBatch' + i).val();
                    break;
                }
            } else if ($('#cateGoods' + i).val()) {
                cateGoods = $('#cateGoods' + i).val();
                break;
            }
        }

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'parentFormID': '<?php echo $parentFormID?>',
            'dataFormID': '<?php echo $dataFormID?>',
            'dataInputNm': '<?php echo $dataInputNm?>',
            'mode': '<?php echo $mode?>',
            'callFunc': '<?php echo $callFunc?>',
            'childRow': '<?php echo $childRow?>',
            'cateGoods[]': cateGoods,
            'cateNm': cateNm,
            'pagelink': pagelink
        };

        $.get('../share/layer_category.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    function select_code() {
//        alert($('input[name=cateNm]').val());
        if ($('input[id*=\'layer_category\']:checked').length == 0) {
            alert('카테고리를 선택해 주세요!');
            return false;
        }

        var applyGoodsCnt = 0;
        var chkGoodsCnt = 0;
        var resultJson = {
            "mode": "<?php echo $mode?>",
            "parentFormID": "<?php echo $parentFormID?>",
            "dataFormID": "<?php echo $dataFormID?>",
            "dataInputNm": "<?php echo $dataInputNm?>",
            "childRow": "<?php echo $childRow?>",
            "info": []
        };


        $('input[id*=\'layer_category\']:checked').each(function () {
            var cateCd = $(this).val();
            var cateNm = $('#cateNm_' + cateCd).val();
            if ($('#<?php echo $dataFormID?>_' + cateCd).length == 0) {
                resultJson.info.push({"cateCd": cateCd, "cateNm": cateNm});
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
                alert('선택한 ' + chkGoodsCnt + '개의 카테고리 중 ' + applyGoodsCnt + '개의 카테고리가 추가 되었습니다.');
            }
            // 선택된 버튼 div 토글
            if (chkGoodsCnt > 0) {
                $('#' + resultJson.parentFormID).addClass('active');
            } else {
                $('#' + resultJson.parentFormID).removeClass('active');
            }
            $('div.bootstrap-dialog-close-button').click();
        } else {
            alert('동일한 카테고리가 이미 존재합니다.');
        }
    }

    function displayTemplate(data) {
        if (data.dataInputNm == '') {
            data.dataInputNm = 'categoryCd';
        }

        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5') && data.mode != 'simple' ) {
            $('#' + data.parentFormID).prepend('<h5>선택된 카테고리</h5>');
        }

        var parentFormCount = $('#' + data.parentFormID+' tr').length;

        if (data.mode == 'input') {
            $("#" + data.parentFormID).html('');
            var childRow = data.childRow ? Number(data.childRow) : 0;

            $.each(data.info, function (key, val) {
                var addHtml = "";
                var complied = _.template($('#categoryInputTemplate').html());
                addHtml += complied({
                    cateNm: val.cateNm,
                    cateCd: val.cateCd,
                    dataFormID: data.dataFormID,
                    dataInputNm: data.dataInputNm,
                    key: key + childRow +parentFormCount
                });
                $("#" + data.parentFormID).append(addHtml);
            });
        } else if (data.mode == 'search' || data.mode == 'simple' ) {
            $.each(data.info, function (key, val) {
                var addHtml = "";
                if (data.mode == 'simple') {
                    var complied = _.template($('#categorySimpleTemplate').html());
                    addHtml += complied({
                        cateNm: val.cateNm,
                        cateCd: val.cateCd,
                        dataFormID: data.dataFormID,
                        dataInputNm: data.dataInputNm,
                        key: key + Number(data.childRow)+parentFormCount
                    });
                } else {
                    var complied = _.template($('#categorySearchTemplate').html());
                    addHtml += complied({
                        cateNm: val.cateNm,
                        cateCd: val.cateCd,
                        dataFormID: data.dataFormID,
                        dataInputNm: data.dataInputNm
                    });
                }
                $("#" + data.parentFormID).append(addHtml);

            });
        }
    }
    //-->
</script>
<script type="text/html" id="categoryInputTemplate">
    <span id="<%=dataFormID%>_<%=cateCd%>" class="pull-left">
	<input type="hidden" name="<%=dataInputNm%>" value="<%=cateCd%>"/>
	<%
	if($('input[name=' + dataInputNm + 'Nm]').length > 0) {
		$('input[name=' + dataInputNm + 'Nm]').val(cateNm);
	} else {
	%>
	<input type="hidden" name="<%=dataInputNm%>Nm" value="<%=cateNm%>"/>
	<span class="outline"><b>선택된 카테고리</b> <%=cateNm%></span>
	<span class="button gray small"><input type="button"  data-toggle="delete"  data-target="#<%=dataFormID%>_<%=cateCd%>" value="삭제"/></span>
	</span>
    <% } %>
</script>

<script type="text/html" id="categorySimpleTemplate">
    <tr id="<%=dataFormID%>_<%=cateCd%>">
        <td class="center"><span class="number"><%=(key + 1)%></span><input type="hidden" name="<%=dataInputNm%>[]" value="<%=cateCd%>"/>
            <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=cateNm%>"/></td>
        <td><%=cateNm%></td>
        <td class="center">
            <input type="button"  class="btn btn-sm btn-gray" data-toggle="delete"  data-target="#<%=dataFormID%>_<%=cateCd%>" value="삭제"/>
        </td>
    </tr>
</script>

<script type="text/html" id="categorySearchTemplate">
    <div id="<%=dataFormID%>_<%=cateCd%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%>[]" value="<%=cateCd%>">
        <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=cateNm%>">
        <button type="button" class="btn btn-gray"><%=cateNm%></button>
        <button type="button" class="btn btn-red" data-toggle="delete" data-target="#<%=dataFormID%>_<%=cateCd%>">삭제</button>
    </div>
</script>
