<div class="loading" style="position:absolute;width:100%;height:100%;top:0px;left:0px;z-index:1060;opacity:0.5;background-position-x: 50%;display:none"></div>
<div class="table-title">
    전체 상품 노출 현황
</div>
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
                <th>네이버 쇼핑<br/>상품노출 현황</th>
                <td>
                <?php if ($statsData['naver'] > $naverGradeMaxCount) {?>
                    <span class="js-stats-naver text-danger bold"><?=number_format($statsData['naver'])?></span>개의 상품을 네이버 쇼핑에 노출 중입니다. (총 상품 수 : <span class="js-stats-total"><?=number_format($statsData['total'])?></span>개)
                <?php } else {?>
                    <span class="js-stats-naver"><?=number_format($statsData['naver'])?></span>개의 상품을 네이버 쇼핑에 노출 중입니다. (총 상품 수 : <span class="js-stats-total"><?=number_format($statsData['total'])?></span>개)
                <?php }?>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-gray js-naver-stats-load">새로고침</button>
                </td>
            </tr>

        </table>
        <p class="notice-info">
            네이버 쇼핑몰 몰등급 설정 정보 | 쇼핑몰 몰등급  : <?php echo $naverGradeName?>,  최대 상품수 : <?php echo number_format($naverGradeMaxCount)?>개
        </p>
        <p class="notice-danger">
            네이버 쇼핑 노출 상품수는 네이버 쇼핑몰 몰등급에 따라 제한됩니다. 몰등급별 최대 상품수 초과 시, 최근등록일 순으로 노출 상품이 조정됩니다.
        </p>
    </div>
</div>
<div class="mgt20"></div>
<div class="table-title">
    카테고리별 상품 노출 현황
</div>
<div class="js-form-naver-stats">
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
    <p class="notice-info">
        네이버 쇼핑에 노출되는 상품수를 카테고리별로 확인하실 수 있습니다.
    </p>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width5p">번호</th>
            <?php if ($gGlobal['isUse'] === true) { ?><th class="width-xs">노출상점</th><?php } ?>
            <th>카테고리명</th>
            <th class="width10p">네이버 쇼핑<br/>노출 상품 수</th>
            <th class="width10p">등록 상품 수</th>
            <th class="width10p">등록일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ((gd_isset($data) && is_array($data)) || ($search['categoryNoneFl'] =='y' && empty($search['cateNm']) === true && empty($search['cateGoods'][0]) === true)) {

            if($search['categoryNoneFl'] =='y' && empty($search['cateNm']) === true && empty($search['cateGoods'][0]) === true) { ?>
                <tr>
                    <td class="center">1</td>
                    <?php if ($gGlobal['isUse'] === true) { ?>
                        <td class="center">-</td>
                        <?php } ?>
                    <td>미분류 상품 > 미분류(카테고리 없음)</td>
                    <td class="center"><?= number_format($goodsNaverStats['naver']['']); ?></td>
                    <td class="center"><?= number_format($goodsNaverStats['total']['']); ?></td>
                    <td class="center">-</td>
                </tr>
            <?php } else {
                foreach ($data as $key => $val) {
                    ?>
                    <tr>
                        <td class="center"><?= number_format($page->idx--); ?></td>
                        <?php if ($gGlobal['isUse'] === true) { ?>
                            <td>
                            <?php foreach (explode(",", $val['mallDisplay']) as $mallKey => $mallValue) {
                                if ($useMallList[$mallValue]['domainFl']) {
                                    ?>
                                    <span class="js-popover flag flag-16 flag-<?= $useMallList[$mallValue]['domainFl'] ?>"
                                          data-content="<?= $useMallList[$mallValue]['mallName'] ?>"></span>
                                <?php }
                            } ?>
                            </td><?php } ?>
                        <td>
                            <label for="layer_category_<?= $val['cateCd']; ?>"
                                   class="hand"><?= $cate->getCategoryPosition($val['cateCd'], 0, ' &gt; ', false, false); ?></label>
                            <input type="hidden" id="cateNm_<?= $val['cateCd']; ?>"
                                   value="<?= gd_htmlspecialchars($cate->getCategoryPosition($val['cateCd'], 0, ' &gt; ', false, false)); ?>"/>
                        </td>
                        <td class="center"><?= number_format($goodsNaverStats['naver'][$val['cateCd']]); ?></td>
                        <td class="center"><?= number_format($goodsNaverStats['total'][$val['cateCd']]); ?></td>
                        <td class="center"><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
                    </tr>
                    <?php
                }
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="5">검색을 이용해 주세요.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <?php  if (gd_isset($data) && is_array($data)) { ?>
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

        $(".js-naver-stats-load").click(function(e){
            get_naver_stats();
        });

    });

    function get_naver_stats() {
        $(".loading").show();
        $.post('../goods/goods_ps.php', {'mode': 'get_naver_stats'}, function (data) {
            if(data) {
                var getData = $.parseJSON(data);
                $(".js-stats-naver").html(getData.naver);
                $(".js-stats-total").html(getData.total);
                $(".loading").hide();
            }
        });
    }

    function layer_list_search(pagelink) {
        var cateNm = $('.js-form-naver-stats input[name=\'cateNm\']').val();
        var categoryNoneFl = $('.js-form-naver-stats input[name=\'categoryNoneFl\']:checked').val();

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
            'layerFormID': '<?=$layerFormID?>',
            'parentFormID': '<?=$parentFormID?>',
            'dataFormID': '<?=$dataFormID?>',
            'dataInputNm': '<?=$dataInputNm?>',
            'mode': '<?=$mode?>',
            'callFunc': '<?=$callFunc?>',
            'childRow': '<?=$childRow?>',
            'cateGoods[]': cateGoods,
            'cateNm': cateNm,
            'categoryNoneFl': categoryNoneFl,
            'pagelink': pagelink
        };
        $(".loading").show();
        $.get('../share/layer_naver_stats.php', parameters, function (data) {
            $('#<?=$layerFormID?>').html(data);
        });
    }

    //-->
</script>

