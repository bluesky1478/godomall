<div class="loading" style="position:absolute;width:100%;height:100%;top:0px;left:0px;z-index:1060;opacity:0.5;background-position-x: 50%;display:none"></div>
<div class="table-title">전체 상품 노출 현황</div>
<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-sm"/>
                <col/>
                <col class="width-xs"/>
            </colgroup>
            <tr>
                <th>쇼핑하우<br/>상품노출 현황</th>
                <td><span class="js-stats-daum"><?=number_format($statsData['daum'])?></span>개의 상품을 다음 쇼핑에 노출 중입니다. (총 상품 수 : <span class="js-stats-total"><?=number_format($statsData['total'])?></span>개)</td>
                <td><button type="button" class="btn btn-sm btn-gray js-daum-stats-load">새로고침</button></td>
            </tr>
        </table>
        <p class="notice-danger">노출상품이 50만개 초과 시 전체 상품이 노출되지 않습니다. 노출상품 개수를 확인 후 설정해 주세요.</p>
    </div>
</div>
<div class="mgt20"></div>
<div class="table-title">카테고리별 상품 노출 현황</div>
<div class="js-form-daum-stats">
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
                    <input type="text" name="cateNm" value="<?=$search['cateNm']; ?>" class="form-control"/>
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
                    <div>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="categoryNoneFl" value="y" <?php if($search['categoryNoneFl'] =='y') { echo "checked"; } ?>> 카테고리 미지정 상품
                        </label>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
<div>
    <p class="notice-info">쇼핑하우에 노출되는 상품수를 카테고리별로 확인하실 수 있습니다.</p>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width5p">번호</th>
            <?php if ($gGlobal['isUse'] === true) { ?>
            <th class="width-xs">노출상점</th>
            <?php } ?>
            <th>카테고리명</th>
            <th class="width10p">쇼핑하우<br/>노출 상품 수</th>
            <th class="width10p">등록 상품 수</th>
            <th class="width10p">등록일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ((gd_isset($data) && is_array($data)) || ($search['categoryNoneFl'] =='y' && empty($search['cateNm']) === true && empty($search['cateGoods'][0]) === true)) {
            if ($search['categoryNoneFl'] =='y' && empty($search['cateNm']) === true && empty($search['cateGoods'][0]) === true) { ?>
                <tr>
                    <td class="center">1</td>
                    <?php if ($gGlobal['isUse'] === true) { ?>
                    <td class="center">-</td>
                    <?php } ?>
                    <td>미분류 상품 > 미분류(카테고리 없음)</td>
                    <td class="center"><?= number_format($goodsDaumStats['daum']['']); ?></td>
                    <td class="center"><?= number_format($goodsDaumStats['total']['']); ?></td>
                    <td class="center">-</td>
                </tr>
        <?php } else {
            foreach ($data as $key => $val) { ?>
                <tr>
                    <td class="center"><?= number_format($page->idx--); ?></td>
                    <?php if ($gGlobal['isUse'] === true) { ?>
                        <td>
                        <?php foreach (explode(",", $val['mallDisplay']) as $mallKey => $mallValue) {
                            if ($useMallList[$mallValue]['domainFl']) { ?>
                                <span class="js-popover flag flag-16 flag-<?= $useMallList[$mallValue]['domainFl'] ?>" data-content="<?= $useMallList[$mallValue]['mallName'] ?>"></span>
                        <?php } } ?>
                        </td>
                    <?php } ?>
                    <td>
                        <label for="layer_category_<?= $val['cateCd']; ?>"><?= $cate->getCategoryPosition($val['cateCd'], 0, ' &gt; ', false, false); ?></label>
                        <input type="hidden" id="cateNm_<?= $val['cateCd']; ?>" value="<?= gd_htmlspecialchars($cate->getCategoryPosition($val['cateCd'], 0, ' &gt; ', false, false)); ?>"/>
                    </td>
                    <td class="center"><?= number_format($goodsDaumStats['daum'][$val['cateCd']]); ?></td>
                    <td class="center"><?= number_format($goodsDaumStats['total'][$val['cateCd']]); ?></td>
                    <td class="center"><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
                </tr>
                <?php
                }
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="<?= $gGlobal['isUse'] === true ? 6 : 5; ?>">검색을 이용해 주세요.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <?php if (gd_isset($data) && is_array($data)) { ?>
        <div class="center"><?=$page->getPage('layer_list_search(\'PAGELINK\')'); ?></div>
    <?php } ?>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                layer_list_search();
                return false
            }
        });

        $(".js-daum-stats-load").click(function(e){
            get_daum_stats();
        });
    });

    function get_daum_stats() {
        $(".loading").show();
        $.post('../goods/goods_ps.php', {'mode': 'get_daum_stats'}, function (data) {
            $(".loading").hide();
            var getData = $.parseJSON(data);
            if (getData.daum && getData.total) {
                $(".js-stats-daum").html(numeral(getData.daum).format('0,0'));
                $(".js-stats-total").html(numeral(getData.total).format('0,0'));
            } else {
                alert(getData);
            }
        });
    }

    function layer_list_search(pagelink) {
        var cateNm = $('.js-form-daum-stats input[name="cateNm"]').val();
        var categoryNoneFl = $('.js-form-daum-stats input[name="categoryNoneFl"]:checked').val();
        var cateGoods = '';
        for (var i = <?=DEFAULT_DEPTH_CATE;?>; i > 0; i--) {
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
            'layerFormID': '<?=$search['layerFormID'];?>',
            'parentFormID': '<?=$search['parentFormID'];?>',
            'dataFormID': '<?=$search['dataFormID'];?>',
            'dataInputNm': '<?=$search['dataInputNm'];?>',
            'mode': '<?=gd_isset($search['mode'], 'search');?>',
            'callFunc': '<?=gd_isset($search['callFunc'], '');?>',
            'childRow': '<?=gd_isset($search['childRow']);?>',
            'cateGoods[]': cateGoods,
            'cateNm': cateNm,
            'categoryNoneFl': categoryNoneFl,
            'pagelink': pagelink
        };
        $(".loading").show();
        $.get('../share/layer_daum_stats.php', parameters, function (data) {
            $('#<?=$search['layerFormID'];?>').html(data);
        });
    }
    //-->
</script>

