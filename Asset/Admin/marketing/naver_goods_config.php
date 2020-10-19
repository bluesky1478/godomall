<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?> </h3>
    <div class="btn-group">
        <input type="button" value="노출상품 현황 확인" class="btn btn-white js-layer-naver-stats"/>
        <input type="button" value="저장" class="btn btn-red" id="batchSubmit"/>
    </div>
</div>


<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="js-form-enter-submit">
    <div class="table-title gd-help-manual">
        <?php if($search['delFl'] =='y') { echo "삭제 "; } ?>상품 검색
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
            <tr>
                <th>카테고리</th>
                <td class="contents" colspan="3">
                    <div class="form-inline">
                        <?=$cate->getMultiCategoryBox(null, $search['cateGoods']); ?>
                        <label class="checkbox-inline mgl10">
                            <input type="checkbox" name="categoryNoneFl" value="y" <?=gd_isset($checked['categoryNoneFl']['y']); ?>> 카테고리 미지정 상품
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>검색어</th>
                <td>
                    <div class="form-inline">
                        <?=gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
                <th>브랜드</th>
                <td>
                    <div class="form-inline">

                        <label><input type="button" value="브랜드선택" class="btn btn-sm btn-gray"  onclick="layer_register('brand', 'radio')"/></label>

                        <label class="checkbox-inline mgl10"><input type="checkbox" name="brandNoneFl" value="y" <?=gd_isset($checked['brandNoneFl']['y']); ?>> 브랜드 미지정 상품</label>

                        <div id="brandLayer" class="selected-btn-group <?=!empty($search['brandCd']) ? 'active' : ''?>">
                            <h5>선택된 브랜드 : </h5>
                            <?php if (empty($search['brandCd']) === false) { ?>
                                <div id="info_brand_<?= $search['brandCd'] ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="brandCd" value="<?= $search['brandCd'] ?>"/>
                                    <input type="hidden" name="brandCdNm" value="<?= $search['brandCdNm'] ?>"/>
                                    <span class="btn"><?= $search['brandCdNm'] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_brand_<?= $search['brandCd'] ?>">삭제</button>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                </td>
            </tr>
            <tr>
                <th>PC쇼핑몰<br />상품노출 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="" <?=gd_isset($checked['goodsDisplayFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="y" <?=gd_isset($checked['goodsDisplayFl']['y']); ?> />노출함</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="n" <?=gd_isset($checked['goodsDisplayFl']['n']); ?> />노출안함</label>
                </td>
                <th>PC쇼핑몰<br />상품판매 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="" <?=gd_isset($checked['goodsSellFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="y" <?=gd_isset($checked['goodsSellFl']['y']); ?> />판매함</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="n" <?=gd_isset($checked['goodsSellFl']['n']); ?> />판매안함</label>
                </td>
            </tr>
            <tr>
                <th>모바일쇼핑몰<br />상품노출 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="" <?=gd_isset($checked['goodsDisplayMobileFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="y" <?=gd_isset($checked['goodsDisplayMobileFl']['y']); ?> />노출함</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="n" <?=gd_isset($checked['goodsDisplayMobileFl']['n']); ?> />노출안함</label>
                </td>
                <th>모바일쇼핑몰<br />상품판매 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="" <?=gd_isset($checked['goodsSellMobileFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="y" <?=gd_isset($checked['goodsSellMobileFl']['y']); ?> />판매함</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="n" <?=gd_isset($checked['goodsSellMobileFl']['n']); ?> />판매안함</label>
                </td>
            </tr>
            <tr>
                <th>판매 재고</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="stockFl" value="" <?=gd_isset($checked['stockFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="stockFl" value="n" <?=gd_isset($checked['stockFl']['n']); ?> />무한정 판매</label>
                    <label class="radio-inline"><input type="radio" name="stockFl" value="y" <?=gd_isset($checked['stockFl']['y']); ?> />재고량에 따름</label>
                </td>
                <th>품절 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="soldOut" value="" <?=gd_isset($checked['soldOut']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="soldOut" value="y" <?=gd_isset($checked['soldOut']['y']); ?> />품절</label>
                    <label class="radio-inline"><input type="radio" name="soldOut" value="n" <?=gd_isset($checked['soldOut']['n']); ?> />정상</label>
                </td>
            </tr>
            <tr>
                <th>네이버쇼핑 노출여부</th>
                <td colspan="3">
                    <label class="radio-inline"><input type="radio" name="naverFl" value="" <?=gd_isset($checked['naverFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="naverFl" value="y" <?=gd_isset($checked['naverFl']['y']); ?> />노출함</label>
                    <label class="radio-inline"><input type="radio" name="naverFl" value="n" <?=gd_isset($checked['naverFl']['n']); ?> />노출안함</label>
                </td>
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
        <div class="pull-right form-inline">
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
    <input type="hidden" name="searchFl" value="y">
    <input type="hidden" name="applyPath" value="<?=gd_php_self()?>">
</form>


<form id="frmBatchNaver" name="frmBatchNaver" action="../goods/goods_ps.php"    target="ifrmProcess" method="post">
    <input type="hidden" name="mode" value="batch_naver_config" />
    <input type="hidden" name="modDtUse" value="" />
    <?php
    foreach ($batchAll as $key => $val) {
        echo '<input type="hidden" name="queryAll['.$key.']" value="'.$val.'" />'.chr(10);
    }
    ?>
    <div class="table-responsive">
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width-2xs center" rowspan="2"><input type="checkbox" class="js-checkall" data-target-name="arrGoodsNo[]"></th>
                <th class="width-2xs center" rowspan="2">번호</th>
                <th class="width-xs" rowspan="2">이미지</th>
                <th class="center" rowspan="2">상품명</th>
                <th class="width-xs center" rowspan="2">네이버 쇼핑<br/>노출여부</th>
                <th class="width-xs center" rowspan="2">공급사</th>
                <th class="width-md center" colspan="2">노출상태</th>
                <th class="width-md center" colspan="2">판매상태</th>
                <th class="width-xs  center" rowspan="2">품절여부</th>
                <th class="width-xs  center" rowspan="2">재고</th>
                <th class="width-md center" rowspan="2">판매가</th>
                <th class="width-md center" rowspan="2">상품할인</th>
            </tr>
            <tr>
                <th class="width-2xs center">PC</th>
                <th class="width-2xs center">모바일</th>
                <th class="width-2xs center">PC</th>
                <th class="width-2xs center">모바일</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data) && count($data) > 0 ) {
                $arrGoodsDisplay = ['y' => '노출함', 'n' => '노출안함'];
                $arrGoodsSell = ['y' => '판매함', 'n' => '판매안함'];
                $goodsConfig = (gd_policy('goods.display')); //상품 설정 config 불러오기
                $goodsConfig['goodsModDtTypeAll'] = gd_isset($goodsConfig['goodsModDtTypeAll'], 'y');
                $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');
                foreach ($data as $key => $val) {
                    if ($val['goodsDiscountFl'] == 'y') {
                        if ($val['goodsDiscountUnit'] == 'price') $goodsDiscount = gd_currency_symbol() . $val['goodsDiscount'] . gd_currency_string();
                        else $goodsDiscount = $val['goodsDiscount'] . '%';
                    } else $goodsDiscount = '사용안함';

                    list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);

                    ?>
                    <tr>
                        <td class="center number">
                            <input type="checkbox" name="arrGoodsNo[]" value="<?=$val['goodsNo']; ?>"/>
                        </td>
                        <td class="center"><?=number_format($page->idx--); ?></td>
                        <td class="center">
                            <?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                        </td>
                        <td>
                            <a href="../goods/goods_register.php?goodsNo=<?=$val['goodsNo']; ?>" target="_blank"><span class="emphasis_text"><?=$val['goodsNm']; ?></span></a>
                        </td>
                        <td class="center"><?=$arrGoodsDisplay[$val['naverFl']]; ?></td>
                        <td class="center"><?= $val['scmNm'] ?></td>
                        <td class="center"><?=$arrGoodsDisplay[$val['goodsDisplayFl']]; ?></td>
                        <td class="center"><?=$arrGoodsDisplay[$val['goodsDisplayMobileFl']]; ?></td>
                        <td class="center"><?=$arrGoodsSell[$val['goodsSellFl']]; ?></td>
                        <td class="center"><?=$arrGoodsSell[$val['goodsSellMobileFl']]; ?></td>
                        <td class="center"><?=$stockText?></td>
                        <td class="center"><?=$totalStock?></td>
                        <td class="center number">
                            <div class="form-inline"><?=gd_currency_symbol(); ?><?=gd_money_format($val['goodsPrice']); ?><?=gd_currency_string(); ?></div>
                        </td>
                        <td class="center"><?=$goodsDiscount?></td>
                    </tr>
                    <?php
                }
            }  else {

                ?>
                <tr><td class="no-data" colspan="10">검색된 정보가 없습니다.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="center"><?=$page->getPage();?></div>
    <div class="mgt10"></div>
    <div>
        <label class="checkbox-inline"><input type="checkbox" id="batchAll" name="batchAll" value="y" />검색된 상품 전체(<?=number_format($page->recode['total']);?>개 상품)를 수정합니다.</label>
        <p class="notice-danger mgt5">상품수가 많은 경우 비권장합니다. 가능하면 한 페이지씩 선택하여 수정하세요.</p>
    </div>
    <div>
        <table class="table table-cols" id="setNaverConfig">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th class="center">
                    네이버 쇼핑 노출설정
                </th>
                <td>
                    <label class="radio-inline"><input type="radio" name="naverFl" value="y"  checked />노출함</label>
                    <label class="radio-inline"><input type="radio" name="naverFl" value="n" />노출안함</label>
                </td>
            </tr>
        </table>
    </div>
</form>

<script type="text/javascript">
    <!--

    $(document).ready(function(){

        $(".js-layer-naver-stats").click(function(e){
            layer_add_info('naver_stats');
        });

        $( "#batchSubmit" ).click(function() {

            var msg = '';

            if ($('#batchAll:checked').length == 0) {
                if ($('input[name="arrGoodsNo[]"]:checked').length == 0) {
                    $.warnUI('항목 체크', '선택된 항목이 없습니다.');
                    return false;
                }

                msg += '선택된 상품의 ';
            } else {
                msg += '검색된 전체 상품의 ';
            }

            msg += '네이버 쇼핑 노출 상태를 '+$('#setNaverConfig input[name="naverFl"]:checked').closest('label').text()+ '으 로 \n';

            msg += '일괄 수정하시겠습니까?\n\n';
            msg += '[주의] 일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.';


            dialog_confirm(msg, function (result) {
                if (result) {
                    //상품수정일 변경 확인 팝업
                    <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                    dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result2) {
                        if (result2) {
                            $('input[name="modDtUse"]').val('y');
                        } else {
                            $('input[name="modDtUse"]').val('n');
                        }
                        $( "#frmBatchNaver").submit();
                    }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                    <?php } else { ?>
                        //상품 수정일 변경 범위설정 체크
                        <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y') { ?>
                            $('input[name="modDtUse"]').val('y');
                        <?php } else { ?>
                            $('input[name="modDtUse"]').val('n');
                        <?php } ?>
                        $( "#frmBatchNaver").submit();
                    <?php } ?>
                }
            });



        });


        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchGoods').submit();
        });


    });

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode
        };

        // 레이어 창

        if (typeStr == 'scm') {
            addParam['mode'] = 'radio';
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (typeStr == 'delivery') {
            addParam['dataInputNm']		= 'deliverySno';
            var scmFl = $('input[name="scmFl"]:checked').val();
            if(scmFl !='all')
            {
                addParam['scmFl'] =scmFl;

                if($('input[name="scmNo[]"]').val()) addParam['scmNo'] =$('input[name="scmNo[]"]').val();
                else addParam['scmNo'] = $('input[name="scmNo"]').val();
            }

        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }
    //-->
</script>
