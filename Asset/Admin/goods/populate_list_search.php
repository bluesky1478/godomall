<?php if($goodsColorList) {?>
    <script>
        <!--
        function selectColor(val,target,name) {
            var color = $(val).data('color');
            var title = $(val).data('content');

            if($(target+" #"+name+color).length == '0') {
                var addHtml = "<div id='"+name+ color + "' class='btn-group btn-group-xs'>";
                addHtml += "<input type='hidden' name='goodsColor[]' value='" + color + "'>";
                addHtml += "<button type='button' class='btn btn-default js-popover' data-html='true' data-content='"+title+"' data-placement='bottom' style='background:#" + color + ";'>&nbsp;&nbsp;&nbsp;</button>";
                addHtml += "<button type='button' class='btn btn-icon-delete' data-toggle='delete' data-target='#"+name+ color + "'>삭제</button></div>";
            }
            $(target+" #selectColorLayer").append(addHtml);

            $('.js-popover').popover({trigger: 'hover',container: '#content',});
        }
        //-->
    </script>
<?php } ?>


<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="js-form-enter-submit">
    <div class="table-title gd-help-manual">
        <?php if($search['delFl'] =='y') { echo "삭제 "; } ?>인기상품 노출 검색
        <?php if(empty($searchConfigButton) && $searchConfigButton != 'hide'){?>
        <span class="search"><button type="button" class="btn btn-sm btn-black" onclick="set_search_config(this.form)">검색설정저장</button></span>
        <?php }?>
    </div>

    <div class="search-detail-box">
        <input type="hidden" name="detailSearch" value="<?=$search['detailSearch']; ?>"/>
        <input type="hidden" name="delFl" value="<?=$search['delFl']; ?>"/>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <?php if(gd_use_provider() === true) { ?>
            <?php if(gd_is_provider() === false) { ?>
            <tr>
                <th>공급사 구분</th>
                <td colspan="3">
                    <?php if($mode['page']!='delivery') { ?>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="all" <?=gd_isset($checked['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/>전체
                    </label>
                    <?php } ?>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="n" <?=gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?> onclick="layer_register('scm', 'checkbox')"/>공급사
                    </label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','checkbox')">공급사 선택</button>
                    </label>

                    <div id="scmLayer" class="selected-btn-group <?=$search['scmFl'] == 'y' && !empty($search['scmNo']) ? 'active' : ''?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($search['scmFl'] == 'y') {
                            foreach ($search['scmNo'] as $k => $v) { ?>
                                <span id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                </span>
                            <?php }
                        } ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
            <?php } ?>
            <tr>
                <th>인기상품 노출명</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?=gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="regDt" <?=gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                            <option value="modDt" <?=gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][0]; ?>" />
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][1]; ?>" />
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date($search['searchPeriod'], 'searchDate', true) ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>순위타입</th>
                <td><select name="searchTypeFl" class="form-control">
                        <option value=""> = 선택 =</option>
                        <option value="sell" <?=gd_isset($selected['searchTypeFl']['sell']); ?>>상품 판매순위 - 판매금액</option>
                        <option value="hit" <?=gd_isset($selected['searchTypeFl']['hit']); ?>>상품 클릭수 순위</option>
                        <option value="sellCnt" <?=gd_isset($selected['searchTypeFl']['sellCnt']); ?>>상품 판매순위 - 판매횟수</option>
                        <option value="view" <?=gd_isset($selected['searchTypeFl']['view']); ?>>상품 조회수 순위</option>
                        <option value="cart" <?=gd_isset($selected['searchTypeFl']['cart']); ?>>장바구니 담기 순위</option>
                        <option value="wishlist" <?=gd_isset($selected['searchTypeFl']['wishlist']); ?>>찜리스트 담기 순위</option>
                        <option value="review" <?=gd_isset($selected['searchTypeFl']['review']); ?>>상품 후기 작성 순위</option>
                        <option value="score" <?=gd_isset($selected['searchTypeFl']['score']); ?>>상품 후기 평점 순위</option>
                    </select></td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>


    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?=number_format($page->recode['total']);?></strong>개 /
            전체 <strong><?=number_format($page->recode['amount']);?></strong>개
        </div>
    </div>
    <input type="hidden" name="searchFl" value="y">
    <input type="hidden" name="applyPath" value="<?=gd_php_self()?>">
</form>
<script>

function brand_del(){
    $('input[name=brandCdNm]').val('');
}
</script>
