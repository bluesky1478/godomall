<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-sm"/>
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
<div class="notice-info">상품을 노출하고자 하는 카테고리를 일괄 선택하여 등록할 수 있습니다.</div>
<div class="category_batch_area">
    <div class="category-batch-menu">
        <span class="width6p"><input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'layer_category_');"/></span>
        <span class="width7p">번호</span>
        <?php if ($gGlobal['isUse'] === true) { ?><span class="width12p">노출상점</span><?php } ?>
        <span class="width75p">카테고리명</span>
    </div>
    <div class="category-batch-list">
        <?php
        if (gd_isset($data) && is_array($data)) {
            $i = 0;
            foreach ($data as $key => $val) { ?>
                <div class="category-batch-data<?php if (count($data) > 15) echo ' width102p'; ?>">
                    <span class="width6p center">
                        <?php
                            foreach(explode(",",$val['mallDisplay']) as $mallKey => $mallValue) {
                                if ($useMallList[$mallValue]['domainFl']) {
                                    $tmpDomainFl[] = $useMallList[$mallValue]['domainFl'];
                                    $tmpMallName[] = $useMallList[$mallValue]['mallName'];
                                }
                            }
                        ?>
                        <input type="checkbox" id="layer_category_<?php echo $val['cateCd']; ?>" name="layer_category_<?php echo $i; ?>" value="<?php echo $val['cateCd']; ?>" data-domain="<?=implode(",", $tmpDomainFl)?>" data-mall-name="<?=implode(",", $tmpMallName)?>"/>
                        <?php unset($tmpDomainFl, $tmpMallName); ?>
                    </span>
                    <span class="width7p center"><?php echo number_format($page->idx--); ?></span>
                    <?php if ($gGlobal['isUse'] === true) { ?>
                    <span class="width12p">
                        <?php foreach(explode(",",$val['mallDisplay']) as $mallKey => $mallValue) {
                            if($useMallList[$mallValue]['domainFl']) { ?>
                            <span class="js-popover flag flag-16 flag-<?=$useMallList[$mallValue]['domainFl']?> category-batch-flag" data-content="<?=$useMallList[$mallValue]['mallName']?>"></span>
                        <?php
                            }
                        } ?>
                    </span>
                    <?php } ?>
                    <span class="width75p">
                        <label for="layer_category_<?php echo $val['cateCd']; ?>" class="hand"><?php echo $cate->getCategoryPosition($val['cateCd'],0,' &gt; ',false,false); ?></label>
                        <input type="hidden" id="cateNm_<?php echo $val['cateCd']; ?>" value="<?php echo gd_htmlspecialchars($cate->getCategoryPosition($val['cateCd'],0,' &gt; ',false,false)); ?>"/>
                    </span>
                </div>
                <?php
                $i++;
            }
        } else { ?>
                <div class="category-batch-data center pd5">검색을 이용해 주세요.</div>
        <?php
        }
        ?>
    </div>
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

    // 카테고리 검색
    function layer_list_search() {
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

        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'cateGoods[]': cateGoods,
            'cateNm': cateNm,
            'noLimit': 'y'
        };

        $.get('../share/layer_category_batch.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }

    // 카테고리 선택
    function select_code() {
        if ($('input[id*=\'layer_category\']:checked').length == 0) {
            alert('카테고리를 선택해 주세요!');
            return false;
        }

        var applyGoodsCnt = 0;
        var chkGoodsCnt = 0;
        var resultJson = {
            "info": []
        };

        $('input[id*=\'layer_category\']:checked').each(function () {
            var cateCd = $(this).val();
            var cateNm = $('#cateNm_' + cateCd).val();
            var cateDomain = $(this).data('domain');
            var cateMallName = $(this).data('mall-name');
            if ($('#cateGoodsInfo' + cateCd).length == 0) {
                resultJson.info.push({"cateCd": cateCd, "cateNm": cateNm, "cateDomain": cateDomain, "cateMallName": cateMallName});
                applyGoodsCnt++;
            }
            chkGoodsCnt++;
        });

        if (applyGoodsCnt > 0) {
            set_category_select(resultJson);

            if (applyGoodsCnt != chkGoodsCnt) {
                alert('선택한 ' + chkGoodsCnt + '개의 카테고리 중 ' + applyGoodsCnt + '개의 카테고리가 추가 되었습니다.');
            }

            $('div.bootstrap-dialog-close-button').click();
        } else {
            alert('동일한 카테고리가 이미 존재합니다.');
        }
    }

    // 선택된 카테고리 추가
    function set_category_select(data) {
        $("#cateGoodsInfo thead, #cateGoodsInfo tbody").show();

        $.each(data.info, function (key, val) {
            var addHtml = "";
            <?php if ($gGlobal['isUse'] === true) { ?>
            var flagHtml = [];
            var tmpFlag = (val.cateDomain).split(',');
            var tmpMallName = (val.cateMallName).split(',');
            for(var f = 0 ; f < tmpFlag.length; f++) {
                flagHtml.push('<span class="js-popover flag flag-16 flag-'+tmpFlag[f]+'" data-content="'+tmpMallName[f]+'"></span>');
            }
            flagHtml = flagHtml.join("&nbsp;");
            <?php } ?>
            var complied = _.template($('#categoryBatchTemplate').html());
            addHtml += complied({
                cateNm: val.cateNm,
                cateCd: val.cateCd,
                <?php if ($gGlobal['isUse'] === true) { ?>
                flagHtml: flagHtml
                <?php } ?>
            });

            $("#cateGoodsInfo tbody").append(addHtml);

            if ($('input[name="cateCd"]:checked').length == 0) {
                $('input[name="cateCd"]:first').prop('checked', true);
            }
        });
    }
    //-->
</script>
<script type="text/html" id="categoryBatchTemplate">
    <tr id="cateGoodsInfo<%=cateCd%>">
        <?php if ($gGlobal['isUse'] === true) { ?>
        <td><%=flagHtml%></td>
        <?php } ?>
        <td class="center">
            <input type="hidden" name="link[cateCd][]" value="<%=cateCd%>" />
            <input type="hidden" name="link[cateLinkFl][]" value="y" id="cateLink_<%=cateCd%>" />
            <input type="radio" name="cateCd" value="<%=cateCd%>" />
        </td>
        <td><%=cateNm%></td>
        <td class="center"><%=cateCd%></td>
        <td class="center"><input type="button" class='btn btn-sm btn-white btn-icon-minus' onclick="field_remove('cateGoodsInfo<%=cateCd%>')" value="삭제"/></td>
    </tr>
</script>
