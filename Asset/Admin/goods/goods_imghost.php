<div>
    <div class="phead_wrap mgt0"><div class="phead">
        <h2><?=end($naviMenu->location);?> <span>내 쇼핑몰의 상품설명이미지를 이미지호스팅으로 일괄전환합니다.</span></h2>
    </div></div>
</div>

<div class="description">
    <div class="title">필독! 이미지호스팅 일괄전환이란?</div>
    오픈마켓에 입점한 운영자는 반드시 이미지호스팅을 사용해야 합니다.<br/>
    내 상점에 등록한 상품수가 많을 경우 하나하나 이미지호스팅으로 수정하는 시간이 많이 걸리게 됩니다.<br/>
    아래 기능은 내 쇼핑몰에 올려진 상품설명이미지를 이미지호스팅으로 빠르게 전환해주는 기능입니다.<br/>
    이 기능을 사용하려면 이미지호스팅이 신청되어 있어야 합니다. <span class="button small gray"><a href="http://hosting.godo.co.kr/imghosting/imghosting_info.php" target="_blank">이미지호스팅 안내보기</a></span> 를 참조하세요!
</div>

<form id="frmSearchGoods" name="frmSearchGoods" method="get">
<input type="hidden" name="detailSearch" value="<?=$search['detailSearch'];?>" />
<input type="hidden" name="sort[name]" value="<?=$sort['fieldName']?>">
<input type="hidden" name="sort[mode]" value="<?=$sort['sortMode']?>" />
<div class="table-title gd-help-manual">① 먼저 아래에서 이미지호스팅으로 전환할 상품을 검색합니다.</div>
<div>
    <table class="table table-cols">
    <colgroup><col class="width-md" /><col /></colgroup>
    <tr>
        <th>검색어</th>
        <td>
            <?=gd_select_box('key','key',$search['combineSearch'],null,$search['key'],null);?>
            <input type="text" name="keyword" value="<?=$search['keyword'];?>" class="form-control" />
            <span class="small_submit"><span class="button small black"><input type="submit" value="검색" /></span></span>
            <span class="button small blue"><input type="button" class="detailbuttom" value="상세검색펼침" /></span>
        </td>
    </tr>
    </table>
</div>

<div class="input_wrap display-none">
    <table class="table table-cols">
    <colgroup><col class="width-sm" /><col class="width-xl"/><col class="width-sm" /><col/></colgroup>
    <tr>
        <th>분류선택</th>
        <td class="contents" colspan="3">
            <?=$cate->getMultiCategoryBox(null,$search['cateGoods'],'class="input_select"'); ?>
        </td>
    </tr>
    <tr>
        <th>브랜드 선택</th>
        <td class="contents" colspan="3">
            <?=$brand->getMultiCategoryBox(null,$search['brand'],'class="input_select"'); ?>
        </td>
    </tr>
    <tr>
        <th>상품가격</th>
        <td>
            <input type="text" name="goodsPrice[]" value="<?=$search['goodsPrice'][0];?>" class="input_int_l" />원 ~
            <input type="text" name="goodsPrice[]" value="<?=$search['goodsPrice'][1];?>" class="input_int_l" />원
        </td>
        <th>마일리지</th>
        <td>
            <input type="text" name="mileage[]" value="<?=$search['mileage'][0];?>" class="input_int_l" /> ~
            <input type="text" name="mileage[]" value="<?=$search['mileage'][1];?>" class="input_int_l" />
        </td>
    </tr>
    <tr>
        <th>옵션 여부</th>
        <td>
            <label><input type="radio" name="optionFl" value="" <?=gd_isset($checked['optionFl']['']);?> />전체</label>
            <label><input type="radio" name="optionFl" value="y" <?=gd_isset($checked['optionFl']['y']);?> />옵션사용</label>
            <label><input type="radio" name="optionFl" value="n" <?=gd_isset($checked['optionFl']['n']);?> />옵션미사용</label>
        </td>
        <th>마일리지 정책</th>
        <td>
            <label><input type="radio" name="mileageFl" value="" <?=gd_isset($checked['mileageFl']['']);?> />전체</label>
            <label><input type="radio" name="mileageFl" value="c" <?=gd_isset($checked['mileageFl']['c']);?> />통합설정</label>
            <label><input type="radio" name="mileageFl" value="g" <?=gd_isset($checked['mileageFl']['g']);?> />개별설정</label>
        </td>
    </tr>
    <tr>
        <th>추가상품 여부</th>
        <td>
            <label><input type="radio" name="addGoodsFl" value="" <?=gd_isset($checked['addGoodsFl']['']);?> />전체</label>
            <label><input type="radio" name="addGoodsFl" value="y" <?=gd_isset($checked['addGoodsFl']['y']);?> />옵션사용</label>
            <label><input type="radio" name="addGoodsFl" value="n" <?=gd_isset($checked['addGoodsFl']['n']);?> />옵션미사용</label>
        </td>
        <th>텍스트옵션 여부</th>
        <td>
            <label><input type="radio" name="optionTextFl" value="" <?=gd_isset($checked['optionTextFl']['']);?> />전체</label>
            <label><input type="radio" name="optionTextFl" value="y" <?=gd_isset($checked['optionTextFl']['y']);?> />옵션사용</label>
            <label><input type="radio" name="optionTextFl" value="n" <?=gd_isset($checked['optionTextFl']['n']);?> />옵션미사용</label>
        </td>
    </tr>
    <tr>
        <th>상품출력 여부</th>
        <td>
            <label><input type="radio" name="goodsDisplayFl" value="" <?=gd_isset($checked['goodsDisplayFl']['']);?> />전체</label>
            <label><input type="radio" name="goodsDisplayFl" value="y" <?=gd_isset($checked['goodsDisplayFl']['y']);?> />출력</label>
            <label><input type="radio" name="goodsDisplayFl" value="n" <?=gd_isset($checked['goodsDisplayFl']['n']);?> />미출력</label>
        </td>
        <th>상품판매 여부</th>
        <td>
            <label><input type="radio" name="goodsSellFl" value="" <?=gd_isset($checked['goodsSellFl']['']);?> />전체</label>
            <label><input type="radio" name="goodsSellFl" value="y" <?=gd_isset($checked['goodsSellFl']['y']);?> />판매</label>
            <label><input type="radio" name="goodsSellFl" value="n" <?=gd_isset($checked['goodsSellFl']['n']);?> />판매중지</label>
        </td>
    </tr>
    <tr>
        <th>아이콘(기간제한)</th>
        <td>
<?php
    foreach ($getIcon as $key => $val) {
        if ($val['iconPeriodFl'] == 'y') {
            echo '<label class="nobr"><input type="radio" name="goodsIconCdPeriod" value="'.$val['iconCd'].'" '.gd_isset($checked['goodsIconCdPeriod'][$val['iconCd']]).' />'.gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']).'&nbsp;</label>'.chr(10);
        }
    }
?>
        </td>
        <th>아이콘(무제한)</th>
        <td>
<?php
    foreach ($getIcon as $key => $val) {
        if ($val['iconPeriodFl'] == 'n') {
            echo '<label class="nobr"><input type="radio" name="goodsIconCd" value="'.$val['iconCd'].'" '.gd_isset($checked['goodsIconCd'][$val['iconCd']]).' />'.gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']).'&nbsp;</label>'.chr(10);
        }
    }
?>
        </td>
    </tr>
    </table>
</div>

<div class="big_submit display-none"><span class="button black"><input type="submit" value="검색" /></span></div>

<div>
    <div class="list_top">
        <div class="list_stat">총 <strong><?=number_format($page->recode['amount']);?></strong>개, 검색 <strong><?=number_format($page->recode['total']);?></strong>개, <strong><?=number_format($page->page['now']);?></strong> of <?=number_format($page->page['total']);?> Pages</div>
        <div class="list_option">
            <ul>
                <li>
                    <label>등록일</label>
                    <button id="sort_gregDt_desc" class="sort_down" onclick="list_sort('g.regDt','desc', this.form.id);">내림차순</button><button id="sort_gregDt_asc" class="sort_up" onclick="list_sort('g.regDt','asc', this.form.id);">오름차순</button>
                </li>
                <li>
                    <label>상품명</label>
                    <button id="sort_ggoodsNm_desc" class="sort_down" onclick="list_sort('g.goodsNm','desc', this.form.id);">내림차순</button><button id="sort_ggoodsNm_asc" class="sort_up" onclick="list_sort('g.goodsNm','asc', this.form.id);">오름차순</button>
                </li>
                <li>
                    <label>가격</label>
                    <button id="sort_gogoodsPrice_desc" class="sort_down" onclick="list_sort('go.goodsPrice','desc', this.form.id);">내림차순</button><button id="sort_gogoodsPrice_asc" class="sort_up" onclick="list_sort('go.goodsPrice','asc', this.form.id);">오름차순</button>
                </li>
            </ul>
        </div>
    </div>
</div>
</form>

<div>
    <table class="list_form">
    <thead>
    <tr>
        <th class="width5p"><input type="checkbox" id="chk_all" onclick="check_toggle('chk_all','chk[]')" /></th>
        <th class="width5p">번호</th>
        <th class="width40p">상품명</th>
        <th class="width10p">전환이 필요한<br/>이미지갯수</th>
        <th class="width10p">등록일</th>
        <th class="width10p">가격</th>
        <th class="width10p">마일리지</th>
        <th class="width5p">재고</th>
        <th class="width5p">진열</th>
    </tr>
    </thead>
    <tbody>
<?php
    if (is_array(gd_isset($data))) {
        $arrGoodsDisplay    = array('y' => '출력', 'n' => '미출력');
        $arrGoodsSell        = array('y' => '판매', 'n' => '판매중지');
        foreach ($data as $key => $val) {
            // 상품 재고
            if ($val['stockFl'] == 'n') {
                $totalStock    = '∞';
            } else {
                $totalStock    = number_format($val['totalStock']);
            }
            // 전환이 필요한 이미지갯수
            $cnt = $imgHost->imgStatus($val['goodsDescription']);
?>
    <tr>
        <td class="center"><input type="checkbox" name="chk[]" value="<?=$val['goodsNo'];?>" /></td>
        <td class="center number"><?=number_format($page->idx--);?></td>
        <td>
            <div class="pull-left width-2xs">
                <a href="<?=URI_HOME;?>goods/goods_view.php?goodsNo=<?=$val['goodsNo'];?>" target="_blank"><?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank');?></a>
            </div>
            <div class="pull-left">
                <div onclick="goods_register_popup('<?=$val['goodsNo'];?>');" class="hand"><?=$val['goodsNm'];?></div>
<?php
            if (empty($val['goodsIconCdPeriod']) === false && is_array($val['goodsIconCdPeriod']) === true) {
                foreach ($val['goodsIconCdPeriod'] as $iKey => $iVal) {
                    echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']).' ';
                }
            }
            if (empty($val['goodsIconCd']) === false && is_array($val['goodsIconCd']) === true) {
                foreach ($val['goodsIconCd'] as $iKey => $iVal) {
                    echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']).' ';
                }
            }
?>
            </div>
        </td>
        <td class="center number"><span style="font-weight:bold; font-size:11pt; color:#0079b6" class="in"><?=number_format($cnt['in']);?></span></td>
        <td class="center date"><?=substr($val['regDt'],0,10);?></td>
        <td class="center"><span class="font-num"><?=number_format($val['goodsPrice']);?></span> 원</td>
        <td class="center number"><?=number_format($val['mileage']);?></td>
        <td class="center number"><?=$totalStock;?></td>
        <td class="center lmenu"><?=$arrGoodsDisplay[$val['goodsDisplayFl']];?><br /><?=$arrGoodsSell[$val['goodsSellFl']];?></td>
    </tr>
<?php
        }
    } else {
?>
    <tr>
        <td class="center" colspan="10">검색된 정보가 없습니다.</td>
    </tr>
<?php
    }
?>
    </tbody>
    </table>

    <div class="center"><?=$page->getPage();?></div>
</div>

<div style="margin-top:20px;">
    <div class="table-title gd-help-manual">② 위 상품리스트에서 선택한 상품의 상품설명이미지를 이미지호스팅으로 전환하거나 복원합니다.</div>
    <ul style="margin-left:33px;">
        <li><span class="button blue"><button type="button" id="btnReplace">전환하기</button></span> <label><input type="checkbox" name="replaceDeleteFl"/>이미지호스팅으로 전환 후 쇼핑몰에 남겨진 이미지를 삭제합니다.</label></li>
        <li><span class="button black"><button type="button" id="btnRestore">복원하기</button></span> <label><input type="checkbox" name="restoreDeleteFl"/>이미지 복원 후 이미지호스팅에 남겨진 이미지를 삭제합니다.</label></li>
    </ul>
</div>



<script type="text/javascript">
<!--
$(document).ready(function(){
    $('#sort_<?=str_replace('.','',$sort['fieldName']);?>_<?=$sort['sortMode'];?>').attr('class','sort<?=$sort['sortMode'] == 'desc' ? '_down_' : '_up_';?>clicked');
    $('input[name*=\'goodsPrice\']').number_only();

    $('.content_list:eq(1)').imgHost({
        isFtp: <?=(Session::has('ftpConf') ? 'true' :'null');?>
        , btnReplaceEle: $('#btnReplace')
        , btnRestoreEle: $('#btnRestore')
    });
});
//-->
</script>
