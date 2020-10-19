<style>
    body { overflow:hidden; }
</style>
<table cellpadding="0" cellspacing="0" width="100%" border="0" class="goodsChoice_outlineTable" style="margin-left:-20px;">
    <colgroup>
        <col style="width:560px;"/>
        <col/>
        <col style="width:560px;"/>
    </colgroup>
    <tr>
        <td class="goodsChoice_outlineTdCenter">
            <span class="goodsChoice_title"><span class="goodsChoice_titleArrow">▼</span>상품선택
            </span>
            <span class="goodsChoice_title_sub">최대 등록 가능한 상품수는 <span class="text-orange-red">500</span>개 입니다. 500개 초과시 기존 등록된 상품은 <span class="text-orange-red">자동 삭제</span> 됩니다</span>
        </td>
        <td rowspan="3" valign="middle" class="goodsChoice_outlineTdCenter">
            <table cellpadding="0" cellspacing="0" class="goodsChoice_addDelButtonArea">
                <tr>
                    <td>
                        <p><input type="button" class="btn btn-lg btn-white btn-icon-plus-bottom" value="추가" id="addGoods"/></p>
                        <p><input type="button" class="btn btn-lg btn-icon-check-bottom goodsChoiceConfirm" value="완료"  id="goodsChoiceConfirmSmall" /></p>
                        <p><input type="button" class="btn btn-lg btn-white btn-icon-minus-bottom" value="삭제" id="delGoods"/></p>
                    </td>
                </tr>
            </table>
        </td>
        <td class="goodsChoice_outlineTdCenter">
            <div class="goodsChoice_title"><span class="goodsChoice_titleArrow">▼</span>등록 상품 리스트</div>
        </td>
    </tr>
    <tr>
        <!-- 상품선택 리스트-->
        <td valign="top" class="goodsChoice_outlineTd"  id="iframe_goodsChoiceList">



            <div  style="width:550px;height:600px;overflow-x:hidden;overflow-y:auto">
                <form id="frmSearchBase" name="frmSearchBase" method="post">
                    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
                    <input type="hidden" name="sort"/>
                    <input type="hidden" name="page"/>
                    <input type="hidden" name="pageNum"/>
                    <input type="hidden" name="setGoodsList">
                    <input type="hidden" id="selectedGoodsList" name="selectedGoodsList" value="<?=$selectedGoodsList?>" />

                    <div class="search-detail-box">
                        <table class="table table-cols">
                            <colgroup>
                                <col class="width-sm"/>
                                <col/>
                            </colgroup>
                            <tbody>
                            <?php if(gd_use_provider()) { ?>
                            <?php if(!gd_is_provider()) { ?>
                            <tr>
                                <th>공급사 구분</th>
                                <td>
                                    <label  class="radio-inline" ><input type="radio" name="scmFl" value="n" <?php echo gd_isset($checked['scmFl']['n']); ?> disabled='true'/>본사</label>
                                    <label  class="radio-inline" ><input type="radio" name="scmFl" value="y" <?php echo gd_isset($checked['scmFl']['y']); ?> disabled='true'/>공급사
                                    </label>
                                    <input type="hidden" name="scmFl" value="<?=$search['scmFl']?>" />
                                    <div id="scmLayer" class="width100p ">
                                        <?php if ($search['scmFl'] == 'y') {
                                            foreach ($search['scmNo'] as $k => $v) { ?>
                                                <span id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                            <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                            <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                            <span class="btn"><?= $search['scmNoNm'][$k] ?></span>

                            </span>

                                            <?php }
                                        } ?>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php } ?>
                            <tr>
                                <th>검색어</th>
                                <td ><div class="form-inline">
                                        <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th >기간검색</th>
                                <td > <div class="form-inline">
                                        <select name="searchDateFl" class="form-control width-xs">
                                            <option value="ag.regDt" <?php echo gd_isset($selected['searchDateFl']['ag.regDt']); ?>>등록일</option>
                                            <option value="ag.modDt" <?php echo gd_isset($selected['searchDateFl']['ag.modDt']); ?>>수정일</option>
                                        </select>

                                        <div class="input-group js-datepicker">
                                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][0]; ?>" >
                <span class="input-group-addon">
                    <span class="btn-icon-calendar">
                    </span>
                </span>
                                        </div>

                                        ~  <div class="input-group js-datepicker">
                                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][1]; ?>" >
                <span class="input-group-addon">
                    <span class="btn-icon-calendar">
                    </span>
                </span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                            <tbody class="js-search-detail" class="display-none">
                            <tr>
                                <th>품절여부</th>
                                <td>
                                    <label  class="radio-inline" ><input type="radio" name="stockUseFl"
                                                  value="all" <?php echo gd_isset($checked['stockUseFl']['all']); ?>/>전체</label>
                                    <label  class="radio-inline" ><input type="radio" name="stockUseFl"
                                                  value="n" <?php echo gd_isset($checked['stockUseFl']['n']); ?>/>제한없음</label>
                                    <label  class="radio-inline" ><input type="radio" name="stockUseFl"
                                                  value="u" <?php echo gd_isset($checked['stockUseFl']['u']); ?>/>재고있음</label>
                                    <label  class="radio-inline" ><input type="radio" name="stockUseFl"
                                                  value="z" <?php echo gd_isset($checked['stockUseFl']['z']); ?>/>재고없음</label>
                                </td>
                            </tr>
                            <tr>
                                <th>판매가</th>
                                <td> <div class="form-inline">
                                        <input type="text" name="goodsPrice[0]" value="<?php echo $search['goodsPrice'][0]; ?>"
                                               class="form-control width-sm"/> ~ <input type="text" name="goodsPrice[1]"
                                                                                        value="<?php echo $search['goodsPrice'][1]; ?>"
                                                                                        class="form-control width-sm"/> </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-link js-search-toggle">상세검색 <span>펼침</span></button>
                    </div>

                    <div class="table-btn">
                        <input type="button" value="검색" class="btn btn-lg btn-black search-goods-btn">
                    </div>


                    <div class="table-header">
                        <div class="pull-right">
                            <ul>
                                <li>
                                    <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
                                </li>
                                <li>
                                    <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                                </li>
                            </ul>
                        </div>
                    </div>

                </form>

                <form id="frmList" action="" method="get" target="ifrmProcess">
                    <input type="hidden" name="mode" value="">
                    <table class="table table-rows" id="tbl_add_goods" >
                        <thead>
                        <tr id="goodsRegisteredTrArea">
                            <th class="width5p center"><input type="checkbox" id="allCheck" value="y" onclick="all_checkbox(this,'tbl_add_goods')"></th>
                            <th class="width5p">번호</th>
                            <th class="width7p">이미지</th>
                            <th class="width10p">상품명</th>
                            <th class="width15p">옵션</th>
                            <th class="width10p">판매가</th>
                            <th class="width10p">공급사</th>
                            <th class="width10p">재고</th>
                            <th class="width10p">품절상태</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (is_array(gd_isset($data))) {

                            foreach ($data as $key => $val) {

                                if($val['stockUseFl'] =='0') {
                                    $stockUseFl = "n";
                                } else {
                                    $stockUseFl = "y";
                                }

                                list($totalStock,$stockText) = gd_is_goods_state($stockUseFl,$val['stockCnt'],$val['soldOutFl']);

                                ?>

                                <tr id="tbl_add_goods_<?php echo $val['addGoodsNo'];?>" class="add_goods_free">
                                    <td class="center">
                                        <input type="hidden" name="itemGoodsNm[]" value="<?=strip_tags($val['goodsNm'])?>" />
                                        <input type="hidden" name="itemGoodsPrice[]" value="<?=gd_currency_display($val['goodsPrice'])?>" />
                                        <input type="hidden" name="itemScmNm[]" value="<?=$val['scmNm']?>" />
                                        <input type="hidden" name="itemTotalStock[]" value="<?=$val['stockCnt']?>" />
                                        <input type="hidden" name="itemSoldOutFl[]" value="<?=gd_isset($val['soldOutFl'])?>" />
                                        <input type="hidden" name="itemStockFl[]" value="<?=gd_isset($val['stockUseFl'])?>" />
                                        <input type="hidden" name="itemBrandNm[]" value="<?=gd_isset($val['brandNm'])?>" />
                                        <input type="hidden" name="itemMakerNm[]" value="<?=gd_isset($val['makerNm'])?>" />
                                        <input type="hidden" name="itemOptionNm[]" value="<?=gd_isset($val['optionNm'])?>" />
                                        <input type="hidden" name="itemApplyFl[]" value="<?=gd_isset($val['applyFl'])?>" />
                                        <input type="hidden" name="itemImage[]" value="<?=rawurlencode(gd_html_add_goods_image($val['addGoodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>" />

                                        <input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?php echo $val['addGoodsNo'];?>"  value="<?php echo $val['addGoodsNo']; ?>"/></td>
                                    <td class="center number addGoodsNumber_<?php echo $val['addGoodsNo'];?>" ><?php echo number_format($page->idx--); ?></td>
                                    <td class="center"><?php echo gd_html_add_goods_image($val['addGoodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></td>
                                    <td>
                                        <a href="../goods/add_goods_register.php?addGoodsNo=<?php echo $val['addGoodsNo'];?>" target="_blank"><?php echo $val['goodsNm']; ?></a>
                                        <input type="hidden" name="goodsNoData[]" value="<?=$val['addGoodsNo']?>" />
                                        <input type="checkbox" name="sortFix[]" class="layer_sort_fix_<?php echo $val['addGoodsNo'];?>"  value="<?php echo $val['addGoodsNo']; ?>" style="display:none"/>
                                    </td>
                                    <td><?php echo $val['optionNm']; ?></td>
                                    <td><?php echo gd_currency_display($val['goodsPrice']); ?></td>
                                    <td><?php echo $val['scmNm']; ?></td>
                                    <td class="center"><?php echo $totalStock; ?></td>
                                    <td class="center"><?=$stockText?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td class="center" colspan="11">검색된 정보가 없습니다.</td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </form>

                <div class="center"><?php echo $page->getPage("#"); ?></div>
            </div>


        </td>
        <!-- 상품선택 리스트-->

        <!-- 등록상품 리스트-->
        <td valign="top" class="goodsChoice_outlineTd">
            <table cellpadding="0" cellpadding="0" width="100%">
                <tr>
                    <td>
                    <table cellpadding="0" cellspacing="0" width="100%" height="30">
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" width="100%">
                            <tr>
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
                                                               class="goodsChoice_sortText"/> 번 위치로
                                </td>
                                <td width="30"><input type="button" value="이동" class="btn btn-sm btn-white goodsChoice_moveBtn"></td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
                        </td></tr><td valign="top" class="goodsChoice_registeredTdArea" >
            <div id="goodsChoice_registerdOutlineDiv">
            <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_result" class="table table-rows">
                <thead>
                <tr id="goodsRegisteredTrArea">
                    <th class="width5p center"><input type="checkbox" id="allCheck" value="y" onclick="all_checkbox(this,'tbl_add_goods_result')"/></th>
                    <th class="width5p">번호</th>
                    <th class="width10p">이미지</th>
                    <th class="width10p">상품명</th>
                    <th class="width15p">옵션</th>
                    <th class="width10p">판매가</th>
                    <th class="width10p">공급사</th>
                    <th class="width10p display-none" >브랜드</th>
                    <th class="width10p display-none">제조사</th>
                    <th class="width10p">재고</th>
                    <th class="width10p">품절상태</th>
                </tr>
                </thead>
                <tbody contents-length="<?=is_null($setGoodsList) ? 0 : strlen(trim($setGoodsList))?>">
                <?php if($setGoodsList) { echo $setGoodsList; } ?>
                </tbody>
            </table>
</div>
</td></tr><tr><td>
            <table cellpadding="0" cellspacing="0" width="100%" height="30">
                <tr>
                    <td >
                        <table cellpadding="0" cellspacing="0" width="100%">
                            <tr>
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
                                                               class="goodsChoice_sortText"/> 번 위치로
                                </td>
                                <td width="30"><input type="button" value="이동" class="btn btn-sm btn-white goodsChoice_moveBtn"></td>
                                <td>&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
</td></tr></table>
        </td>
        <!-- 등록상품 리스트-->
    </tr>

    <tr class="goodChoice_buttonArea">
        <td colspan="3">
            <!--<div class="registeredGoodsCountMsgArea">선택상품 개수 : <span id="registeredCheckedGoodsCountMsg"
                                                                     class="registeredGoodsCountInfo">0</span>개 / 등록상품
                개수 : <span id="registeredGoodsCountMsg" class="registeredGoodsCountInfo">0</span>개
            </div> -->
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td>
                        <input type="button" value="취 소" id="goodsChoiceCancel" class="btn btn-lg btn-white" onclick="self.close();"/>
                        &nbsp;
                        <input type="button" value="선택완료" id="goodsChoiceConfirm" class="goodsChoiceConfirm btn btn-lg btn-black"/>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


<script type="text/javascript">
    <!--
    $(document).ready(function(){

        /**
         * [전체 삭제]인 경우를 체크하여 html() 강제 치환 - 2018.11.21 parkjs
         * selectedGoodsList 값이 none인 상태에서 넘겨받은 html의 length가 0일 경우 전체 삭제로 간주함.
         **/
        if ($('#selectedGoodsList').val() == "none" && $('#tbl_add_goods_result > tbody').attr('contents-length') == 0) {
            $('#tbl_add_goods_result > tbody').html("");
        }

        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                $("#frmSearchBase").submit();
                return false
            }
        });

        $('.search-goods-btn').click(function() {
            $("input[name='setGoodsList']").val( encodeURIComponent($("#tbl_add_goods_result tbody").html()));
            $("#frmSearchBase").submit();

        });

        $('.pagination li a').click(function() {

            $("input[name='page']").val($(this).data('page'));
            $('.search-goods-btn').click();
        });

        $('select[name=\'pageNum\']').change(function () {
            $('.search-goods-btn').click();
        });

        $('select[name=\'sort\']').change(function () {
            $('.search-goods-btn').click();
        })

    });

    function search_register() {
        $("#allCheck").click();

        $("#addGoods").click();


    }



    //-->
</script>
