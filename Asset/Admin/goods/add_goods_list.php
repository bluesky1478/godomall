<script type="text/javascript">
    <!--
    $(document).ready(function () {


        // 삭제
        $('button.checkDelete').click(function () {
            var chkCnt = $('input[name*="addGoodsNo["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 상품을  정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('delete');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './add_goods_ps.php');
                    $('#frmList').submit();
                }
            });
        });

        $('button.checkCopy').click(function () {
            var chkCnt = $('input[name*="addGoodsNo["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 상품을  정말로 복사하시겠습니까?', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('copy');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './add_goods_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        // 등록
        $('#checkRegister').click(function () {
            location.href = './add_goods_register.php';
        });

        $('select[name=\'pageNum\']').change(function () {
            $('input[name=\'pageNum\']').val($(this).val());
            $('#frmSearchBase').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('input[name=\'sort\']').val($(this).val());
            $('#frmSearchBase').submit();
        });

    });


    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }
    //-->
</script>

<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?> </h3>
    <div class="btn-group">
        <input type="button" id="checkRegister" value="추가상품 등록" class="btn btn-red-line" />
    </div>
</div>

<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
    <div class="table-title gd-help-manual">
        추가상품 검색
    </div>

    <div class="search-detail-box">
        <input type="hidden" name="detailSearch" value="<?=$search['detailSearch']; ?>"/>
        <input type="hidden" name="sort"/>
        <input type="hidden" name="pageNum"/>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <?php if(gd_use_provider() === true) { ?>
            <?php if(gd_is_provider() === false) { ?>
            <tr>
                <th>공급사 구분</th>
                <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="all" <?=gd_isset($checked['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/>전체
                        </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="n" <?=gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?> onclick="layer_register('scm','checkbox')"/>공급사
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
                <th>검색어</th>
                <td colspan="3"><div class="form-inline">
                        <?=gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th >기간검색</th>
                <td colspan="3"> <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="ag.regDt" <?=gd_isset($selected['searchDateFl']['ag.regDt']); ?>>등록일</option>
                            <option value="ag.modDt" <?=gd_isset($selected['searchDateFl']['ag.modDt']); ?>>수정일</option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][0]; ?>" >
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>

                        ~  <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][1]; ?>" >
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>
                        <?=gd_search_date($search['searchPeriod'])?>
                    </div>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail" class="display-none">
            <tr>
                <th>브랜드</th>
                <td>
                    <label><input type="text" name="brandCdNm" value="<?=$search['brandCdNm']; ?>"
                                  class="form-control"  readonly onclick="layer_register('brand', 'radio')"/> </label>

                    <button type="button" class="btn btn-sm  btn-gray" onclick="layer_register('brand', 'radio')">브랜드 선택</button>
                    &nbsp;&nbsp;<label class="checkbox-inline"><input type="checkbox" name="brandNoneFl" value="y" <?=gd_isset($checked['brandNoneFl']['y']); ?>> 브랜드 미지정 상품</label>
                    <div id="brandLayer" class="width100p">
                        <?php if ($search['brandCd']) { ?>
                            <span id="idbrand<?= $search['brandCd'] ?>" class="pull-left">
                        <input type="hidden" name="brandCd" value="<?= $search['brandCd'] ?>"/>
                        </span>
                        <?php } ?>
                    </div>
                </td>
                <th>제조사</th>
                <td>
                    <input type="text" name="makerNm" value="<?=$search['makerNm']; ?>" class="form-control width-sm"/>
                </td>
            </tr>
            <tr>
                <th>상품재고 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="stockUseFl"
                                  value="all" <?=gd_isset($checked['stockUseFl']['all']); ?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="stockUseFl"
                                  value="n" <?=gd_isset($checked['stockUseFl']['n']); ?>/>제한없음</label>
                    <label class="radio-inline"><input type="radio" name="stockUseFl"
                                  value="u" <?=gd_isset($checked['stockUseFl']['u']); ?>/>재고있음</label>
                    <label class="radio-inline"><input type="radio" name="stockUseFl"
                                  value="z" <?=gd_isset($checked['stockUseFl']['z']); ?>/>재고없음</label>
                </td>
                <th>판매가</th>
                <td> <div class="form-inline">
                    <input type="text" name="goodsPrice[0]" value="<?=$search['goodsPrice'][0]; ?>"
                           class="form-control"/> ~ <input type="text" name="goodsPrice[1]"
                                                         value="<?=$search['goodsPrice'][1]; ?>"
                                                         class="form-control"/> </div>
                </td>
            </tr>
            <tr>
                <th>노출상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="viewFl"
                                  value="all" <?=gd_isset($checked['viewFl']['all']); ?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="viewFl"
                                  value="y" <?=gd_isset($checked['viewFl']['y']); ?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="viewFl"
                                  value="n" <?=gd_isset($checked['viewFl']['n']); ?>/>노출안함</label>
                </td>
                <th>품절상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="soldOutFl"
                                  value="all" <?=gd_isset($checked['soldOutFl']['all']); ?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="soldOutFl"
                                  value="n" <?=gd_isset($checked['soldOutFl']['n']); ?>/>정상</label>
                    <label class="radio-inline"><input type="radio" name="soldOutFl"
                                  value="y" <?=gd_isset($checked['soldOutFl']['y']); ?>/>품절</label>
                </td>
            </tr>
            <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) { ?>
                <tr>
                    <th>매입처</th>
                    <td colspan="3">
                        <div class="form-inline">

                            <label><input type="button" value="매입처 선택" class="btn btn-sm btn-gray"  onclick="layer_register('purchase', 'checkbox')"/></label>

                            <label class="checkbox-inline mgl10"><input type="checkbox" name="purchaseNoneFl" value="y" <?=gd_isset($checked['purchaseNoneFl']['y']); ?>> 매입처 미지정 상품</label>

                            <div id="purchaseLayer" class="selected-btn-group <?=!empty($search['purchaseNo']) ? 'active' : ''?>">
                                <h5>선택된 매입처 : </h5>

                                <?php if (empty($search['purchaseNo']) === false) {
                                    foreach ($search['purchaseNo'] as $k => $v) { ?>
                                        <div id="info_purchase_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="purchaseNo[]" value="<?= $v ?>"/>
                                <input type="hidden" name="purchaseNoNm[]" value="<?= $search['purchaseNoNm'][$k] ?>"/>
                                <span class="btn"><?= $search['purchaseNoNm'][$k] ?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_purchase_<?= $v ?>">삭제</button>
                                </div>
                                    <?php }
                                } ?>
                                <label><input type="button" value="전체 삭제" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#purchaseLayer div"/></label>
                            </div>

                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색 <span>펼침</span></button>
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
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>

</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p" ><input type="checkbox" id="allCheck" value="y"
                                       onclick="check_toggle(this.id,'addGoodsNo');"/></th>
            <th class="width5p">번호</th>
            <th class="width-2xs">이미지</th>
            <th >상품명</th>
            <th class="width15p">옵션</th>
            <th class="width10p">판매가</th>
            <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) { ?><th class="width7p">매입처</th><?php } ?>
            <th class="width7p">공급사</th>
            <th class="width5p">승인상태</th>
            <th class="width5p">노출상태</th>
            <th class="width5p">재고</th>
            <th class="width10p">등록일 / 수정일</th>
            <th class="width5p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {

            foreach ($data as $key => $val) {

                if($val['stockUseFl'] =='0') {
                    $stockUseFl = "n";
                } else {
                    $stockUseFl = "y";
                }

                list($totalStock,$stockText) = gd_is_goods_state($stockUseFl,$val['stockCnt'],$val['soldOutFl']);

                $arrGoodsApply = array('a'    => '승인요청','y'   => '승인완료','r'  => '반려','n'  => '철회',);
                $arrGoodsDisplay = array('y' => '노출함', 'n' => '노출안함');

                ?>

                <tr>
                    <td class="center"><input type="checkbox" name="addGoodsNo[<?=$val['addGoodsNo']; ?>]" value="<?=$val['addGoodsNo']; ?>"  <?php if($val['applyFl'] =='a') { echo "disabled = 'true'"; }  ?>/></td>
                    <td class="center number"><?=number_format($page->idx--); ?></td>
                    <td class="center"><?=gd_html_add_goods_image($val['addGoodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></td>
                    <td onclick="addgoods_register_popup('<?=$val['addGoodsNo']; ?>'<?php if(gd_is_provider() === true) { echo ",'1'"; } ?>);"class="hand">
                        <?=$val['goodsNm']; ?>
                    </td>
                    <td class="center"><?=$val['optionNm']; ?></td>
                    <td class="center"><?=gd_currency_display($val['goodsPrice']); ?></td>
                    <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) { ?>  <td class="center lmenu"><?=$val['purchaseNm']; ?><?php } ?>
                    <td class="center"><?=$val['scmNm']; ?></td>
                    <td class="center"><?=$arrGoodsApply[$val['applyFl']]?></td>
                    <td class="center"><?=$arrGoodsDisplay[$val['viewFl']]; ?></td>
                    <td class="center"><?=$totalStock; ?></td>
                    <td class="center date"><?=gd_date_format('Y-m-d', $val['regDt']); ?><?php if ($val['modDt']) {
                            echo "<br/>" . gd_date_format('Y-m-d', $val['modDt']);
                        } ?></td>
                    <td class="center padlr10">
                        <a href="./add_goods_register.php?addGoodsNo=<?=$val['addGoodsNo']; ?>" class="btn btn-white btn-xs">수정</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="12">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>




    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white  checkCopy">선택 복사</button>
            <button type="button" class="btn btn-white  checkDelete">선택 삭제</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchBase" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>" data-target-list-form="frmList" data-target-list-sno="addGoodsNo">엑셀다운로드</button>
        </div>
    </div>


</form>

<div class="center"><?=$page->getPage();?></div>
