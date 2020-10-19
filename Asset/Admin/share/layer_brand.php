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
                <th>브랜드명</th>
                <td>
                    <input type="text" name="cateNm" value="<?php echo $search['cateNm']; ?>" class="form-control"/>
                </td>
                <td rowspan="2"">
                    <input type="button" value="검색" class="btn btn-black btn-hf" onclick="layer_list_search();">
                </td>
            </tr>
            <tr>
                <th>브랜드 선택</th>
                <td><div class="form-inline"><?php echo $brand->getMultiCategoryBox(null, gd_isset($search['brand']), 'class="form-control"'); ?></div></td>
            </tr>
        </table>
    </div>
</div>

<div>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width5p"> <?php if ($mode == 'radio') { ?>선택 <?php } else { ?>
                    <input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'layer_brand_');"/><?php } ?>
            </th>
            <th class="width5p">번호</th>
            <?php if ($gGlobal['isUse'] === true) { ?><th class="width10p">노출상점</th><?php } ?>
            <th class="width50p">브랜드명</th>
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
                        <?php if ($mode == 'radio' || $mode =='radio-search') { ?>
                            <input type="radio" id="layer_brand_<?php echo $val['cateCd']; ?>" name="layer_brand" value="<?php echo $val['cateCd']; ?>"/>
                        <?php } else { ?>
                            <input type="checkbox" id="layer_brand_<?php echo $val['cateCd']; ?>" name="layer_brand_<?php echo $i; ?>" value="<?php echo $val['cateCd']; ?>"/>
                        <?php } ?>

                    </td>
                    <td class="center"><?php echo number_format($page->idx--); ?></td>
                    <?php if ($gGlobal['isUse'] === true) { ?><td>
                        <?php foreach(explode(",",$val['mallDisplay']) as $mallKey => $mallValue) {
                            if ($useMallList[$mallValue]['domainFl']) { ?>
                                <span class="js-popover flag flag-16 flag-<?= $useMallList[$mallValue]['domainFl'] ?>" data-content="<?= $useMallList[$mallValue]['mallName'] ?>"></span>
                            <?php }
                        }?>
                    </td><?php } ?>
                    <td>
                        <label for="layer_brand_<?php echo $val['cateCd']; ?>" class="hand"><?php echo $brand->getCategoryPosition($val['cateCd'],0,' &gt; ',false,false); ?></label>
                        <input type="hidden" id="cateNm_<?php echo $val['cateCd']; ?>" value="<?php echo gd_htmlspecialchars($val['cateNm']); ?>"/>
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

    <div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>
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
        var brand = '';
        for (var i = <?php echo DEFAULT_DEPTH_BRAND;?>; i > 0; i--) {
            if ($('#brand' + i).val()) {
                brand = $('#brand' + i).val();
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
            'search': '<?php echo $search?>',
            'brand[]': brand,
            'cateNm': cateNm,
            'pagelink': pagelink
        };
        $.get('../share/layer_brand.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    function select_code() {
        if ($('input[id*=\'layer_brand_\']:checked').length == 0) {
            alert('브랜드를 선택해 주세요!');
            return false;
        }

        var applyGoodsCnt = 0;
        var chkGoodsCnt = 0;
        var resultJson = {
            mode: "<?php echo $mode?>",
            parentFormID: "<?php echo $parentFormID?>",
            dataFormID: "<?php echo $dataFormID?>",
            dataInputNm: "<?php echo $dataInputNm?>",
            childRow: "<?php echo $childRow?>",
            search: '<?php echo $search?>',
            info: []
        };

        $('input[id*=\'layer_brand\']:checked').each(function () {
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
                alert('선택한 ' + chkGoodsCnt + '개의 브랜드 중 ' + applyGoodsCnt + '개의 브랜드가 추가 되었습니다.');
            }

            // 선택된 버튼 div 토글
            if (chkGoodsCnt > 0) {
                $('#' + resultJson.parentFormID).addClass('active');
            } else {
                $('#' + resultJson.parentFormID).removeClass('active');
            }

            $('div.bootstrap-dialog-close-button').click();
        } else {
            alert('동일한 브랜드가 이미 존재합니다.');
        }
    }

    function displayTemplate(data) {
        if (data.dataInputNm == '') {
            data.dataInputNm = 'brandCd';
        }

        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5') && data.mode != 'simple') {
            $('#' + data.parentFormID).prepend('<h5>선택된 브랜드</h5>');
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
                    cateNm: val.cateNm,
                    cateCd: val.cateCd,
                    dataFormID: data.dataFormID,
                    dataInputNm: data.dataInputNm,
                    key: key + childRow + parentFormCount
                });

                $("#" + data.parentFormID).append(addHtml);
                if (data.mode == 'radio-search' && data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5')) {
                    $('#' + data.parentFormID).prepend('<h5>선택된 브랜드:</h5>');
                    $('#brandNoneFlText').html('선택한 브랜드 미지정 상품');
                }
            });
        } else if (data.mode == 'checkbox' || data.mode == 'simple' || data.mode == 'search') {
            $.each(data.info, function (key, val) {
                var addHtml = "";

                if (data.mode == 'simple') {
                    var complied = _.template($('#brandSimpleTemplate').html());
                    addHtml += complied({
                        cateNm: val.cateNm,
                        cateCd: val.cateCd,
                        dataFormID: data.dataFormID,
                        dataInputNm: data.dataInputNm,
                        key: key + Number(data.childRow) + parentFormCount
                    });
                } else {
                    var complied = _.template($('#brandSearchTemplate').html());
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

        //브랜드 미지정 상품이 있을시 체크해제
        /*if($("input[name='brandNoneFl']").length > 0){
            $("input[name='brandNoneFl']").prop("checked", false);
        }*/
    }
    //-->
</script>

<script type="text/html" id="brandInputTemplate">
    <span id="<%=dataFormID%>_<%=cateCd%>" class="pull-left">
	<input type="hidden" name="<%=dataInputNm%>" value="<%=cateCd%>"/>
	<%
	if($('input[name=' + dataInputNm + 'Nm]').length > 0) {
		$('input[name=' + dataInputNm + 'Nm]').val(cateNm);
        if($('#'+dataInputNm+'Del').length > 0) {
            $('#'+dataInputNm+'Del').show();
        }
	} else {
	%>
	<input type="hidden" name="<%=dataInputNm%>Nm" value="<%=cateNm%>"/>
	<span class="outline"><b>선택된 브랜드</b> <%=cateNm%></span>
	<span class="button gray small"><input type="button" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#<%=dataFormID%>_<%=cateCd%>" value="삭제"/></span>
	</span>
    <% } %>
</script>

<script type="text/html" id="brandSimpleTemplate">
    <tr id="<%=dataFormID%>_<%=cateCd%>">
        <td class="center"><span class="number"><%=(key + 1)%></span><input type="hidden" name="<%=dataInputNm%>[]" value="<%=cateCd%>"/>
            <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=cateNm%>"/></td>
        <td><%=cateNm%></td>
        <td class="center">
           <input type="button" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#<%=dataFormID%>_<%=cateCd%>" value="삭제"/>
        </td>
    </tr>
</script>

<script type="text/html" id="brandSearchTemplate">
    <div id="<%=dataFormID%>_<%=cateCd%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%>[]" value="<%=cateCd%>">
        <input type="hidden" name="<%=dataInputNm%>Nm[]" value="<%=cateNm%>">
        <button type="button" class="btn btn-gray"><%=cateNm%></button>
        <button type="button" class="btn btn-red" data-toggle="delete" data-target="#<%=dataFormID%>_<%=cateCd%>">삭제</button>
    </div>
</script>
<script type="text/html" id="brandSearchInputTemplate">
    <div id="<%=dataFormID%>_<%=cateCd%>" class="btn-group btn-group-xs">
        <input type="hidden" name="<%=dataInputNm%>" value="<%=cateCd%>">
        <input type="hidden" name="<%=dataInputNm%>Nm" value="<%=cateNm%>">
        <span class="btn"><%=cateNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#<%=dataFormID%>_<%=cateCd%>" data-none="#brandNoneFlText">삭제</button>
    </div>
</script>
