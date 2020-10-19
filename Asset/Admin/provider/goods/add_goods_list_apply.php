<script type="text/javascript">
    <!--
    $(document).ready(function () {


        // 삭제
        $('button.checkWithdraw').click(function () {
            var chkCnt = $(':checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }
            dialog_confirm('선택한 ' + chkCnt + '개 상품을  정말로 승인 철회 하시겠습니까?', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('applyWithdraw');
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
     * 반려사유
     */
    function layer_apply_msg(msg) {

        layer_popup("<div>" + msg + "</div>", '반려 사유');
    }


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

        layer_add_info(typeStr, addParam);
    }
    //-->
</script>

<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?> </h3>
</div>

<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
    <input type="hidden" name="sort"/> <input type="hidden" name="pageNum"/>
    <input type="hidden" name="searchFl" value="y"/>
    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location); ?>
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="ag.regDt" <?php echo gd_isset($selected['searchDateFl']['ag.regDt']); ?>>등록일</option>
                            <option value="ag.modDt" <?php echo gd_isset($selected['searchDateFl']['ag.modDt']); ?>>수정일</option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][0]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>

                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][1]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>
                        <?= gd_search_date($search['searchPeriod']) ?>
                    </div>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail" class="display-none">
            <tr>
                <th>브랜드</th>
                <td>
                    <label><input type="text" name="brandCdNm" value="<?php echo $search['brandCdNm']; ?>"
                                  class="form-control"/></label>

                    <button type="button" class="btn btn-sm btn-default" onclick="layer_register('brand', 'radio')">브랜드검색</button>

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
                    <input type="text" name="makerNm" value="<?php echo $search['makerNm']; ?>" class="form-control width-sm"/>
                </td>
            </tr>
            <tr>
                <th>재고</th>
                <td>
                    <label><input type="radio" name="stockUseFl"
                                  value="all" <?php echo gd_isset($checked['stockUseFl']['all']); ?>/>전체
                    </label>
                    <label><input type="radio" name="stockUseFl"
                                  value="n" <?php echo gd_isset($checked['stockUseFl']['n']); ?>/>제한없음
                    </label>
                    <label><input type="radio" name="stockUseFl"
                                  value="u" <?php echo gd_isset($checked['stockUseFl']['u']); ?>/>재고있음
                    </label>
                    <label><input type="radio" name="stockUseFl"
                                  value="z" <?php echo gd_isset($checked['stockUseFl']['z']); ?>/>재고없음
                    </label>
                </td>
                <th>판매가</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="goodsPrice[0]" value="<?php echo $search['goodsPrice'][0]; ?>"
                               class="form-control"/> ~ <input type="text" name="goodsPrice[1]"
                                                               value="<?php echo $search['goodsPrice'][1]; ?>"
                                                               class="form-control"/></div>
                </td>
            </tr>
            <tr>
                <th>노출여부</th>
                <td>
                    <label><input type="radio" name="viewFl"
                                  value="all" <?php echo gd_isset($checked['viewFl']['all']); ?>/>전체
                    </label>
                    <label><input type="radio" name="viewFl"
                                  value="y" <?php echo gd_isset($checked['viewFl']['y']); ?>/>노출함
                    </label>
                    <label><input type="radio" name="viewFl"
                                  value="n" <?php echo gd_isset($checked['viewFl']['n']); ?>/>노출안함
                    </label>
                </td>
                <th>품절여부</th>
                <td>
                    <label><input type="radio" name="soldoutFl"
                                  value="all" <?php echo gd_isset($checked['soldoutFl']['all']); ?>/>전체
                    </label>
                    <label><input type="radio" name="soldoutFl"
                                  value="y" <?php echo gd_isset($checked['soldoutFl']['y']); ?>/>정상
                    </label>
                    <label><input type="radio" name="soldoutFl"
                                  value="n" <?php echo gd_isset($checked['soldoutFl']['n']); ?>/>품절
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색 <span>펼침</span></button>
    </div>

    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>


    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?php echo number_format($page->recode['total']); ?></strong>개 / 전체
            <strong><?php echo number_format($page->recode['amount']); ?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
            <?php echo gd_select_box(
                'pageNum', 'pageNum', gd_array_change_key_value(
                [
                    10,
                    20,
                    30,
                    40,
                    50,
                    60,
                    70,
                    80,
                    90,
                    100,
                    200,
                    300,
                    500,
                ]
            ), '개 보기', Request::get()->get('pageNum'), null
            ); ?>
        </div>
    </div>

</form>

<div>
    <form id="frmList" action="" method="get" target="ifrmProcess">
        <input type="hidden" name="mode" value=""> <input type="hidden" name="applyMsg" value="">
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="addGoodsNo"></th>
                <th class="width2p">번호</th>
                <th class="width5p">승인상태</th>
                <th class="width10p">승인구분</th>
                <th class="width-2xs">이미지</th>
                <th>상품명</th>
                <th class="width10p">판매가</th>
                <th class="width5p">재고</th>
                <th class="width10p">승인요청일</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {

                foreach ($data as $key => $val) {
                    // 상품 재고

                    if($val['stockUseFl'] =='0') {
                        $stockUseFl = "n";
                    } else {
                        $stockUseFl = "y";
                    }

                    list($totalStock,$stockText) = gd_is_goods_state($stockUseFl,$val['stockCnt'],$val['soldOutFl']);

                    ?>
                    <tr  <?php if($val['applyFl'] =='a') { echo "style=background:#efefef"; } ?>>
                        <td class="center">
                            <input type="checkbox" name="addGoodsNo[<?php echo $val['addGoodsNo']; ?>]" value="<?php echo $val['addGoodsNo']; ?>" <?php if ($val['applyFl'] != 'a') {
                                echo "disabled='disabled'";
                            } ?> /></td>
                        <td class="center number"><?php echo number_format($page->idx--); ?></td>
                        <td class="center number"><?php echo $search['applyFlList'][$val['applyFl']]; ?> <?php if ($val['applyFl'] == 'r') { ?>
                                <input type="button" class="btn btn-gray btn-xs" onclick="layer_apply_msg('<?= $val['applyMsg'] ?>')" value="사유"> <?php } ?>
                        </td>
                        <td class="center number"><?php echo $search['applyTypeList'][$val['applyType']]; ?></td>
                        <td>
                            <div class="width-2xs">
                                <?php echo gd_html_add_goods_image($val['addGoodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                            </div>
                        </td>
                        <td>
                            <div onclick="addgoods_register_popup('<?php echo $val['addGoodsNo']; ?>'<?php if(gd_is_provider() === true) { echo ",'1'"; } ?>);"
                                 class="hand"><?php echo $val['goodsNm']; ?></div>
                        </td>
                        <td class="center">
                            <div><span class="font-num"><?php echo gd_currency_display($val['goodsPrice']); ?></span>
                            </div>
                            <div class="font-kor">
                                (<?php echo $arrGoodsTax[$val['taxFreeFl']]; ?><?php echo($val['taxFreeFl'] == 't' ? '' . $val['taxPercent'] . '%' : ''); ?>
                                )
                            </div>
                        </td>
                        <td class="center number"><?php echo $totalStock; ?></td>
                        <td class="center date">
                            <?php echo gd_date_format('Y-m-d', $val['applyDt']); ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="9">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <div class="table-action">
            <div class="pull-left">
                <button type="button" class="btn btn-white checkWithdraw">선택상품 철회</button>
            </div>
            <div class="pull-right">
                <!-- <button type="button" class="btn btn-white btn-icon-excel">엑셀다운로드</button> -->
            </div>
        </div>


    </form>

    <div class="center"><?php echo $page->getPage(); ?></div>


</div>
