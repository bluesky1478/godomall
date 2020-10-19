<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location);?> <small>현재 등록된 상품 전체 리스트 입니다.</small></h3>
</div>

<form id="frmSearchGoods" name="frmSearchGoods" method="get">
<input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch'];?>" />
<input type="hidden" name="sort[name]" value="<?=$sort['fieldName']?>">
<input type="hidden" name="sort[mode]" value="<?=$sort['sortMode']?>" />
<table class="table table-cols">
<colgroup><col class="width-md" /><col /></colgroup>
<tr>
	<th>검색어</th>
	<td>
		<?php echo gd_select_box('key','key',$search['combineSearch'],null,$search['key'],null);?>
		<input type="text" name="keyword" value="<?php echo $search['keyword'];?>" class="form-control" />
		<span class="small_submit"><span class="button small black"><input type="submit" value="검색" /></span></span>
		<span class="button small blue"><input type="button" class="detailbuttom" value="상세검색펼침" /></span>
	</td>
</tr>
</table>

<div class="display-none">
	<table class="table table-cols">
	<colgroup><col class="width-sm" /><col class="width-xl"/><col class="width-sm" /><col/></colgroup>
	<tr>
		<th>분류선택</th>
		<td class="contents" colspan="3">
			<?php echo $cate->getMultiCategoryBox(null,$search['cateGoods'],'class="input_select"'); ?>
		</td>
	</tr>
	<tr>
		<th>브랜드 선택</th>
		<td class="contents" colspan="3">
			<?php echo $brand->getMultiCategoryBox(null,$search['brand'],'class="input_select"'); ?>
		</td>
	</tr>
	<tr>
		<th>상품가격</th>
		<td>
			<input type="text" name="goodsPrice[]" value="<?php echo $search['goodsPrice'][0];?>" class="input_int_l" />원 ~
			<input type="text" name="goodsPrice[]" value="<?php echo $search['goodsPrice'][1];?>" class="input_int_l" />원
		</td>
		<th>마일리지</th>
		<td>
			<input type="text" name="mileage[]" value="<?php echo $search['mileage'][0];?>" class="input_int_l" /> ~
			<input type="text" name="mileage[]" value="<?php echo $search['mileage'][1];?>" class="input_int_l" />
		</td>
	</tr>
	<tr>
		<th>옵션 여부</th>
		<td>
			<label><input type="radio" name="optionFl" value="" <?php echo gd_isset($checked['optionFl']['']);?> />전체</label>
			<label><input type="radio" name="optionFl" value="y" <?php echo gd_isset($checked['optionFl']['y']);?> />옵션사용</label>
			<label><input type="radio" name="optionFl" value="n" <?php echo gd_isset($checked['optionFl']['n']);?> />옵션미사용</label>
		</td>
		<th>마일리지 정책</th>
		<td>
			<label><input type="radio" name="mileageFl" value="" <?php echo gd_isset($checked['mileageFl']['']);?> />전체</label>
			<label><input type="radio" name="mileageFl" value="c" <?php echo gd_isset($checked['mileageFl']['c']);?> />통합설정</label>
			<label><input type="radio" name="mileageFl" value="g" <?php echo gd_isset($checked['mileageFl']['g']);?> />개별설정</label>
		</td>
	</tr>
	<tr>
		<th>추가상품 여부</th>
		<td>
			<label><input type="radio" name="addGoodsFl" value="" <?php echo gd_isset($checked['addGoodsFl']['']);?> />전체</label>
			<label><input type="radio" name="addGoodsFl" value="y" <?php echo gd_isset($checked['addGoodsFl']['y']);?> />옵션사용</label>
			<label><input type="radio" name="addGoodsFl" value="n" <?php echo gd_isset($checked['addGoodsFl']['n']);?> />옵션미사용</label>
		</td>
		<th>텍스트옵션 여부</th>
		<td>
			<label><input type="radio" name="optionTextFl" value="" <?php echo gd_isset($checked['optionTextFl']['']);?> />전체</label>
			<label><input type="radio" name="optionTextFl" value="y" <?php echo gd_isset($checked['optionTextFl']['y']);?> />옵션사용</label>
			<label><input type="radio" name="optionTextFl" value="n" <?php echo gd_isset($checked['optionTextFl']['n']);?> />옵션미사용</label>
		</td>
	</tr>
	<tr>
		<th>상품출력 여부</th>
		<td>
			<label><input type="radio" name="goodsDisplayFl" value="" <?php echo gd_isset($checked['goodsDisplayFl']['']);?> />전체</label>
			<label><input type="radio" name="goodsDisplayFl" value="y" <?php echo gd_isset($checked['goodsDisplayFl']['y']);?> />출력</label>
			<label><input type="radio" name="goodsDisplayFl" value="n" <?php echo gd_isset($checked['goodsDisplayFl']['n']);?> />미출력</label>
		</td>
		<th>상품판매 여부</th>
		<td>
			<label><input type="radio" name="goodsSellFl" value="" <?php echo gd_isset($checked['goodsSellFl']['']);?> />전체</label>
			<label><input type="radio" name="goodsSellFl" value="y" <?php echo gd_isset($checked['goodsSellFl']['y']);?> />판매</label>
			<label><input type="radio" name="goodsSellFl" value="n" <?php echo gd_isset($checked['goodsSellFl']['n']);?> />판매중지</label>
		</td>
	</tr>
	<tr>
		<th>무한정 판매 여부</th>
		<td>
			<label><input type="radio" name="stockFl" value="" <?php echo gd_isset($checked['stockFl']['']);?> />전체</label>
			<label><input type="radio" name="stockFl" value="n" <?php echo gd_isset($checked['stockFl']['n']);?> />무한정 판매</label>
			<label><input type="radio" name="stockFl" value="y" <?php echo gd_isset($checked['stockFl']['y']);?> />재고량에 따름</label>
		</td>
		<th>품절상품 여부</th>
		<td>
			<label><input type="radio" name="soldOut" value="" <?php echo gd_isset($checked['soldOut']['']);?> />전체</label>
			<label><input type="radio" name="soldOut" value="y" <?php echo gd_isset($checked['soldOut']['y']);?> />품절상품</label>
			<label><input type="radio" name="soldOut" value="n" <?php echo gd_isset($checked['soldOut']['n']);?> />판매상품</label>
		</td>
	</tr>
	<tr>
		<th>아이콘(기간제한)</th>
		<td>
			<label class="nobr"><input type="radio" name="goodsIconCdPeriod" value="" <?php echo gd_isset($checked['goodsIconCdPeriod']['']);?> />전체</label>
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
			<label class="nobr"><input type="radio" name="goodsIconCd" value="" <?php echo gd_isset($checked['goodsIconCd']['']);?> />전체</label>
<?php
    foreach ($getIcon as $key => $val) {
        if ($val['iconPeriodFl'] == 'n') {
            echo '<label class="nobr"><input type="radio" name="goodsIconCd" value="'.$val['iconCd'].'" '.gd_isset($checked['goodsIconCd'][$val['iconCd']]).' />'.gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']).'&nbsp;</label>'.chr(10);
        }
    }
?>
		</td>
	</tr>
	<tr>
		<th>배송정책</th>
		<td class="contents" colspan="3">
			<label><input type="radio" name="deliveryFl" value="" onclick="delivery_switch(this.value);" <?php echo gd_isset($checked['deliveryFl']['']);?> />전체</label>
<?php
    foreach (Globals::get('gDelivery') as $key => $val) {
?>
			<label><input type="radio" name="deliveryFl" value="<?php echo $key;?>" onclick="delivery_switch(this.value);" <?php echo gd_isset($checked['deliveryFl'][$key]);?> /> <?php echo $val;?> 정책</label>
<?php
    }
?>
			<div id="deliveryConf_free" class="display-none">
				<label><input type="radio" name="deliveryFree" value="" <?php echo gd_isset($checked['deliveryFree']['']);?> />무료 배송 정책 전체</label>
				<label><input type="radio" name="deliveryFree" value="one" <?php echo gd_isset($checked['deliveryFree']['one']);?> /> 해당 상품만 무료</label>
				<label><input type="radio" name="deliveryFree" value="goods" <?php echo gd_isset($checked['deliveryFree']['goods']);?> /> 상품별 배송 정책 상품 무료</label>
				<label><input type="radio" name="deliveryFree" value="all" <?php echo gd_isset($checked['deliveryFree']['all']);?> /> 모두 무료</label>
			</div>
		</td>
	</tr>
	<?php if (gd_isset($mobile['mobileShopFl']) == 'y') {?>
	<tr>
		<th>모바일 출력 여부</th>
		<td>
			<label><input type="radio" name="goodsDisplayMobileFl" value="" <?php echo gd_isset($checked['goodsDisplayMobileFl']['']);?> />전체</label>
			<label><input type="radio" name="goodsDisplayMobileFl" value="y" <?php echo gd_isset($checked['goodsDisplayMobileFl']['y']);?> />출력</label>
			<label><input type="radio" name="goodsDisplayMobileFl" value="n" <?php echo gd_isset($checked['goodsDisplayMobileFl']['n']);?> />미출력</label>
		</td>
		<th>모바일 상세 설명</th>
		<td>
			<label><input type="radio" name="mobileDescriptionFl" value="" <?php echo gd_isset($checked['mobileDescriptionFl']['']);?> />전체</label>
			<label><input type="radio" name="mobileDescriptionFl" value="y" <?php echo gd_isset($checked['mobileDescriptionFl']['y']);?> />존재</label>
			<label><input type="radio" name="mobileDescriptionFl" value="n" <?php echo gd_isset($checked['mobileDescriptionFl']['n']);?> />미존재</label>
		</td>
	</tr>
	<?php }?>
	</table>
</div>

<div class="big_submit display-none"><span class="button black"><input type="submit" value="검색" /></span></div>

<div>
	<div class="list_top">
		<div class="list_stat">총 <strong><?php echo number_format($page->recode['amount']);?></strong>개, 검색 <strong><?php echo number_format($page->recode['total']);?></strong>개, <strong><?php echo number_format($page->page['now']);?></strong> of <?php echo number_format($page->page['total']);?> Pages</div>
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

<table class="table table-rows">
<thead>
<tr>
	<th class="width5p">번호</th>
	<th class="width-2xs"></th>
	<th class="width40p">상품정보</th>
	<th class="width10p">등록일</th>
	<th class="width10p">가격</th>
	<th class="width10p">마일리지</th>
	<th class="width5p">재고</th>
	<th class="width5p">진열</th>
	<th class="width5p">수정</th>
	<th class="width5p">복사</th>
	<th class="width5p">삭제</th>
</tr>
</thead>
<tbody>
<?php
if (is_array(gd_isset($data))) {
	$arrGoodsDisplay    = array('y' => '출력', 'n' => '미출력');
	$arrGoodsSell        = array('y' => '판매', 'n' => '판매중지');
	$arrGoodsTax        = array('t' => '과세', 'f' => '비과세');
	$arrDeliveryFree    = array('one' => '해당 상품만', 'goods' => '상품별 배송', 'all' => '모두 무료');
	foreach ($data as $key => $val) {
		// 상품 재고
		if ($val['stockFl'] == 'n') {
			$totalStock    = '∞';
		} else {
			$totalStock    = number_format($val['totalStock']);
		}
?>
<tr>
	<td class="center number"><?php echo number_format($page->idx--);?></td>
	<td>
		<div class="width-2xs">
			<?php echo gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank');?>
		</div>
	</td>
	<td>
		<div onclick="goods_register_popup('<?php echo $val['goodsNo'];?>');" class="hand"><?php echo $val['goodsNm'];?></div>
		<div class="notice-ref notice-sm"><?php echo Globals::get('gDelivery.'.$val['deliveryFl']);?> <?php if ($val['deliveryFl'] == 'free') { echo '('.$arrDeliveryFree[$val['deliveryFree']].')'; }?></div>
		<div>
<?php
		// 기간 제한용 아이콘
		if (empty($val['goodsIconCdPeriod']) === false && is_array($val['goodsIconCdPeriod']) === true) {
			foreach ($val['goodsIconCdPeriod'] as $iKey => $iVal) {
				echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']).' ';
			}
		}
		// 상품 아이콘
		if (empty($val['goodsIconCd']) === false && is_array($val['goodsIconCd']) === true) {
			foreach ($val['goodsIconCd'] as $iKey => $iVal) {
				echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']).' ';
			}
		}
		// 품절 체크
		if ($val['soldOutFl'] == 'y' || ($val['stockFl'] == 'y' && $val['totalStock'] <= 0)) {
			echo gd_html_icon('soldout').' ';
		}
?>
		</div>
	</td>
	<td class="center date"><?php echo gd_date_format('Y-m-d', $val['regDt']);?></td>
	<td class="center">
		<div><span class="font-num"><?php echo number_format($val['goodsPrice']);?></span> 원</div>
		<div class="font-kor">(<?php echo $arrGoodsTax[$val['taxFreeFl']];?><?php echo ($val['taxFreeFl'] == 't' ? ''.$val['taxPercent'].'%' : '');?>)</div>
	</td>
	<td class="center number"><?php echo number_format($val['mileage']);?></td>
	<td class="center number"><?php echo $totalStock;?></td>
	<td class="center lmenu"><?php echo $arrGoodsDisplay[$val['goodsDisplayFl']];?><br /><?php echo $arrGoodsSell[$val['goodsSellFl']];?></td>
	<td class="center"><span class="button small blue"><a href="./goods_register.php?goodsNo=<?php echo $val['goodsNo'];?>">수정</a></span></td>
	<td class="center"><span class="button small black"><input type="button" value="복사" onclick="list_process('copy','<?php echo gd_htmlspecialchars_addslashes($val['goodsNm']);?>','<?php echo $val['goodsNo'];?>');" /></a></span></td>
	<td class="center"><span class="button small red"><input type="button" value="삭제" onclick="list_process('delete','<?php echo gd_htmlspecialchars_addslashes($val['goodsNm']);?>','<?php echo $val['goodsNo'];?>');" /></a></span></td>
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

<div class="center"><?php echo $page->getPage();?></div>

<script type="text/javascript">
	<!--
	$(document).ready(function(){
		$('#sort_<?php echo str_replace('.','',$sort['fieldName']);?>_<?php echo $sort['sortMode'];?>').attr('class','sort<?php echo $sort['sortMode'] == 'desc' ? '_down_' : '_up_';?>clicked');
		$('input[name*=\'goodsPrice\']').number_only();

		delivery_switch('<?php echo $search['deliveryFl'];?>');
	});

	/**
	 * 프로세스 처리
	 *
	 * @param string modeStr 프로세스 종류(삭제, 복사)
	 * @param string titleName 상품명
	 * @param string goodsNo 상품번호
	 */
	function list_process(modeStr, titleName, goodsNo)
	{
		var alertMsg	= '';
		if (modeStr == 'delete') {
			alertMsg	= '['+titleName+']를\n정말로 삭제 하시겠습니까?\n\n삭제시 정보는 복구 되지 않습니다.';
		} else {
			alertMsg	= '['+titleName+']를 복사 하시겠습니까?';
		}
		if (confirm(alertMsg) == true){
			$.post('goods_ps.php', { mode : modeStr, goodsNo : goodsNo }, function(data){
				if (data == '') {
					if (modeStr == 'delete') {
						alert('삭제되었습니다.');
					}
					location.reload();
				} else {
					if (data == 'IMG_ERROR') {
						alert('이미지에 오류가 있습니다.\n(이미지경로, 이미지저장소, 폴더권한을 확인하세요.!)');
						location.reload();
					} else {
						alert('오류로 인해 처리 되지 않았습니다.');
					}
				}
			});
		}
	}

	/**
	 * 배송 정책 종류 선택
	 *
	 * @param string thisID 종류 ID
	 */
	function delivery_switch(thisID) {
		if (thisID == 'free') {
			$('#deliveryConf_free').show();
		} else {
			$('#deliveryConf_free').hide();
		}
	}
	//-->
</script>
