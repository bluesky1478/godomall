<?php
if ($cateMode == 'category') {
    $cateStr    = '카테고리';
} else if ($cateMode == 'brand') {
    $cateStr    = '브랜드';
}
?>

<script type="text/javascript">
    <!--
    /**
     * 폼 체크
     */
    $(document).ready(function () {

        $('select[name=\'pageNum\']').change(function () {
            $('input[name=\'pageNum\']').val($(this).val());
            $('#frmSearchGoods').submit();
        });

        $(document).on("click", "#tbl_add_goods_result tbody tr", function(e){
            if(e.target.type != 'checkbox') {
                if ($(this).find("input[name='itemGoodsNo[]']").is(":checked")) {
                    $(this).find("input[name='itemGoodsNo[]']").prop("checked", false);
                    $(this).css('background-color', '');
                } else {
                    $(this).find("input[name='itemGoodsNo[]']").prop("checked", true);
                    $(this).css('background-color', '#f7f7f7');
                }
            }
        })

    });

    function form_process(sortMode) {
        <?php if($cateInfo) { ?>
        var formName = $('#frmSort');
        formName.find('input[name=sortMode]').val(sortMode);
        $('input[name="goodsNo[]"]').prop('checked', true);
        formName.submit();
        <?php } else { ?>
        alert("<?=$cateStr;?>를 먼저 선택해주세요.");
        <?php } ?>
    }



    //-->
</script>
<style>
    .add_goods_fix{background:#E8E8E8;}
    .goodsChoice_outlineTable .goodsChoice_registeredTdArea #goodsChoice_registerdOutlineDiv {
        min-height:590px;
        overflow-y:auto;
    }
</style>
<div class="page-header js-affix">
    <h3><?=end($naviMenu->location);?> </h3>
    <div class="btn-group">
        <input type="button"   onclick="form_process('save');" value="저장" class="btn btn-red" />

    </div>
</div>

<form id="frmSearchGoods" name="frmSearchGoods" method="get">
    <input type="hidden" name="pageNum"/>
    <div class="table-title gd-help-manual">
        <?=$cateStr;?> 선택
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-sm" /><col/></colgroup>
        <tr>
            <th><?=$cateStr;?> 선택</th>
            <td> <div class="form-inline">
                    <?=$cate->getMultiCategoryBox('cateGoods', $search['cateGoods']); ?>
                    <input type="submit" value="검색" class="btn btn-sm btn-black" /></div>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        선택된 <?=$cateStr;?> 정보
    </div>

    <table class="table table-cols">
        <colgroup><col class="width-md" /><col/><col class="width-md" /><col/><col class="width-md" /><col/><col class="width-md" /><col/><col class="width-md" /><col/></colgroup>
        <tr>
            <th >진열타입</th>
            <td ><?php if($cateInfo['sortAutoFl'] == "y") { echo "자동진열"; } else if($cateInfo['sortAutoFl'] == "n") { echo "수동진열"; }?></td>
            <th >진열방법</th>
            <td ><?=$cateInfo['sortTypeNm']?></td>
            <th >PC쇼핑몰 테마</th>
            <td ><?=$cateInfo['pcThemeInfo']['themeNm']?></td>
            <th >모바일쇼핑몰 테마</th>
            <td ><?=$cateInfo['mobileThemeInfo']['themeNm']?></td>
            <th >상품개수</th>
            <td ><?= is_array($data) ? number_format($page->recode['total']) : '' ;?></td>
        </tr>
    </table>

    <div class="table-btn clearfix">
        <div class="pull-right">
            <a href="/goods/category_tree.php?cateCd=<?=$search['cateGoods']?><?= $cateMode == 'brand'? '&cateType=brand' : ''?>" class="btn btn-black" target="_blank">진열방법 수정</a>
        </div>
    </div>
</form>



<div class="table-title gd-help-manual">
    진열 상품 설정
</div>

<form id="frmSort" name="frmSort" action="./goods_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="goods_sort_change" />
    <input type="hidden" name="sortAutoFl" value="<?=$cateInfo['sortAutoFl']?>" />
    <input type="hidden" name="cateMode" value="<?=$cateMode;?>" />
    <input type="hidden" name="sortMode" value="" />
    <input type="hidden" name="cateCd" value="<?=$search['cateGoods'];?>" />
    <input type="hidden" name="totalGoodsSort" value="<?=$page->recode['total'];?>" />
    <input type="hidden" name="fixCount" value="<?=$fixCount;?>" />
    <input type="hidden" name="pageNow" value="<?=$page->page['now'];?>" />
    <input type="hidden" name="pagePnum" value="<?=$page->page['list'];?>" />
    <input type="hidden" name="startNum" value="<?=1+(($page->page['now']-1)*$page->page['list']); ?>" />
    <input type="hidden" id="goodsMoveChk" value="" />

    <div class="table-responsive">
        <table cellpadding="0" cellspacing="0" width="100%" border="0" class="goodsChoice_outlineTable" style="width:100%;" >
            <colgroup>
                <col />
            </colgroup>

            <tr>

                <!-- 상품선택 리스트-->

                <!-- 등록상품 리스트-->
                <td valign="top" class="goodsChoice_outlineTd" >
                    <table cellpadding="0" cellpadding="0" width="100%" style="">
                        <?php if(gd_isset($cateInfo['sortAutoFl'])) { ?>
                            <tr>

                                <td class="goodsChoice_outlineSort">

                                    <table cellpadding="0" cellspacing="0" width="100%" height="30">
                                        <tr>
                                            <td>

                                                <table cellpadding="0" cellspacing="0" width="100%" >
                                                    <tr>
                                                        <td width="100"><input type="button" value="상단 고정" class="btn btn-black goodsChoice_fixUpBtn"></td>
                                                        <?php if($cateInfo['sortAutoFl'] == "n") { ?>
                                                            <td width="150">

                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-white btn-icon-bottom js-moverow goodsChoice_downArrowMore" data-direction="bottom">
                                                                        맨아래
                                                                    </button>
                                                                    <button type="button" class="btn btn-white btn-icon-down js-moverow goodsChoice_downArrow" data-direction="down">
                                                                        아래
                                                                    </button>
                                                                    <button type="button" class="btn btn-white btn-icon-up js-moverow goodsChoice_upArrow" data-direction="up">
                                                                        위
                                                                    </button>

                                                                    <button type="button" class="btn btn-white btn-icon-top js-moverow goodsChoice_upArrowMore" data-direction="top">
                                                                        맨위
                                                                    </button>
                                                                </div>

                                                            </td>
                                                            <td width="180">선택한 상품을 <input type="text" name="goodsChoice_sortText"
                                                                                           class="goodsChoice_sortText" data-page="true" /> 번 위치로
                                                            </td>
                                                            <td width="30"><input type="button" value="이동" class="btn btn-black goodsChoice_cate_moveBtn"></td> <?php }  else { ?>
                                                            <input type="hidden" name="goodsChoice_sortText" class="goodsChoice_sortText" data-page="true"/>
                                                        <?php } ?>
                                                        <td>&nbsp;</td>
                                                    </tr>
                                                </table>

                                            </td>
                                            <td style="float:right"><?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?></td>
                                        </tr>
                                    </table>
                                </td></tr>	<?php } ?>
                        <tr>
                            <td valign="top" class="goodsChoice_registeredTdArea" style="width:100%">
                                <table cellpadding="0" cellpadding="0" width="100%" class="table table-cols" style="margin:0;">
                                    <colgroup>
                                        <col class="width3p center">
                                        <col class="width5p">
                                        <col class="width5p">
                                        <col>
                                        <col class="width10p">
                                        <col class="width10p">
                                        <col class="width5p">
                                        <col class="width5p">
                                        <col class="width8p">
                                        <col class="width8p">
                                        <col class="width8p">
                                    </colgroup>
                                    <thead>
                                    <tr id="goodsRegisteredTrArea">
                                        <th><input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'itemGoodsNo');"/></th>
                                        <th>진열순서</th>
                                        <th>이미지</th>
                                        <th>상품명</th>
                                        <th>판매가</th>
                                        <th>공급사</th>
                                        <th>재고</th>
                                        <th>품절상태</th>
                                        <th>등록일/수정일</th>
                                        <th>PC쇼핑몰 노출상태</th>
                                        <th>모바일쇼핑몰 노출상태</th>
                                    </tr>
                                    </thead>
                                </table>
                                <div id="goodsChoice_registerdOutlineDiv" style="width:100%">
                                    <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_result" class="table table-cols" style="border-top:none; width:1613px;" data-result="self">
                                        <colgroup>
                                            <col class="width3p center">
                                            <col class="width5p center number">
                                            <col class="width5p">
                                            <col>
                                            <col class="width10p center">
                                            <col class="width10p center">
                                            <col class="width5p center">
                                            <col class="width5p center">
                                            <col class="width8p center">
                                            <col class="width8p center">
                                            <col class="width8p center">
                                        </colgroup>
                                        <tbody>
                                        <?php
                                        if ($data) {

                                            foreach ($data as $key => $val) {

                                                list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);

                                                $index = ($key+1)+(($page->page['now']-1)*$page->page['list']);

                                                ?>
                                                <tr id="tbl_add_goods_<?=$val['goodsNo'];?>" <?php if($val['fixSort'] > 0) { echo "class='add_goods_fix'"; } else { echo 'class="add_goods_free"'; } ?>>
                                                    <td style="text-align:center;">
                                                        <input type="hidden" id="goodsSort_<?=$val['goodsNo'];?>" name="goodsSort[]" value="<?=$index?>" />
                                                        <input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?=$val['goodsNo'];?>"  value="<?=$val['goodsNo']; ?>"/></td>
                                                    <td id="addGoodsNumber_<?=$val['goodsNo'];?>"  data-sort-num ="<?=$index?>" style="text-align:center;">
                                                        <?php if($val['fixSort'] == 0) { ?>	<?=$index?><?php } ?></td>
                                                    <td style="text-align:center;"><?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank');?></td>
                                                    <td >
                                                        <a class="text-blue hand" onclick="goods_register_popup('<?=$val['goodsNo']; ?>' <?php if(gd_is_provider() === true) { echo ",'1'"; } ?>);"><?php echo $val['goodsNm']; ?></a>
                                                        <input type="hidden" name="goodsNoData[<?=$key?>]" value="<?=$val['goodsNo']?>" />
                                                        <input type="checkbox" name="sortFix[]" class="layer_sort_fix_<?=$val['goodsNo'];?>"  value="<?=$val['goodsNo']; ?>"  <?php if( $val['fixSort'] > 0) echo "checked='checked'"; ?> style="display:none"   >
                                                        <div>
                                                            <?php

                                                            // 상품 아이콘
                                                            if (empty($val['goodsIconCd']) === false && is_array($val['goodsIconCd']) === true) {
                                                                foreach ($val['goodsIconCd'] as $iKey => $iVal) {
                                                                    echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                                                }
                                                            }

                                                            // 기간 제한용 아이콘
                                                            if (empty($val['goodsIconStartYmd']) === false && empty($val['goodsIconEndYmd']) === false && empty($val['goodsIconCdPeriod']) === false && strtotime($val['goodsIconStartYmd']) <= time() && strtotime($val['goodsIconEndYmd']) >= time()) {
                                                                foreach ($val['goodsIconCdPeriod'] as $iKey => $iVal) {
                                                                    echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                                                }
                                                            }

                                                            // 품절 체크
                                                            if ($val['soldOutFl'] == 'y' || ($val['stockFl'] == 'y' && $val['totalStock'] <= 0)) {
                                                                echo gd_html_image(UserFilePath::icon('goods_icon')->www() . '/' . 'icon_soldout.gif', '품절상품') . ' ';
                                                            }

                                                            if($val['timeSaleSno']) {
                                                                echo "<img src='" . PATH_ADMIN_GD_SHARE . "img/time-sale.png' alt='타임세일' /> ";
                                                            }

                                                            ?>
                                                        </div>
                                                    </td>
                                                    <td class="center"><?=gd_currency_display($val['goodsPrice']); ?></td>
                                                    <td class="center"><?=$val['scmNm']; ?></td>
                                                    <td class="center"><?=$totalStock?></td>
                                                    <td class="center"><?=$stockText ?></td>
                                                    <td class="center"><?=gd_date_format('Y-m-d', $val['regDt']); ?><br /><?=gd_date_format('Y-m-d', $val['modDt']); ?></td>
                                                    <td class="center"><?=$val['goodsDisplayFl'] == "y" ?  "노출함" : "노출안함"; ?></td>
                                                    <td class="center"><?=$val['goodsDisplayMobileFl'] == "y" ?  "노출함" : "노출안함"; ?></td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr id="tbl_add_goods_tr_none"><td colspan="11" class="no-data">선택된 상품이 없습니다.</td></tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td></tr>
                        <?php if(gd_isset($cateInfo['sortAutoFl'])) { ?>
                            <tr><td class="goodsChoice_outlineSort">
                                    <table cellpadding="0" cellspacing="0" width="100%" height="30">
                                        <tr>
                                            <td>

                                                <table cellpadding="0" cellspacing="0" width="100%">
                                                    <tr>
                                                        <td width="100"><input type="button" value="상단 고정" class="btn btn-black goodsChoice_fixUpBtn"></td>
                                                        <?php if($cateInfo['sortAutoFl'] == "n") { ?>
                                                            <td width="150">
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-white btn-icon-bottom js-moverow goodsChoice_downArrowMore" data-direction="bottom">
                                                                        맨아래
                                                                    </button>
                                                                    <button type="button" class="btn btn-white btn-icon-down js-moverow goodsChoice_downArrow" data-direction="down">
                                                                        아래
                                                                    </button>
                                                                    <button type="button" class="btn btn-white btn-icon-up js-moverow goodsChoice_upArrow" data-direction="up">
                                                                        위
                                                                    </button>

                                                                    <button type="button" class="btn btn-white btn-icon-top js-moverow goodsChoice_upArrowMore" data-direction="top">
                                                                        맨위
                                                                    </button>
                                                                </div>

                                                            </td>
                                                            <td width="180">선택한 상품을 <input type="text" name="goodsChoice_sortText"
                                                                                           class="goodsChoice_sortText" data-page="true"/> 번 위치로
                                                            </td>
                                                            <td width="30"><input type="button" value="이동" class="btn btn-black goodsChoice_moveBtn"></td> <?php } else  {  ?>
                                                            <input type="hidden" name="goodsChoice_sortText" class="goodsChoice_sortText" data-page="true"/>
                                                        <?php } ?>

                                                        <td>&nbsp;</td>
                                                    </tr>
                                                </table>

                                            </td>
                                        </tr>
                                    </table>
                                </td></tr>
                        <?php } ?>
                    </table>
                </td>
                <!-- 등록상품 리스트-->
            </tr>
        </table>
    </div>
</form>

<div class="center mgt10"><?=$page->getPage(); ?></div>

