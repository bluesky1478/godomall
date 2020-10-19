<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" value="재입고 알림 상품 관리" class="btn btn-red-box js-batch-restock"/>
    </div>
</div>

<!-- 검색 -->
<div class="table-title">
    신청 내역 검색
</div>

<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="js-form-enter-submit">
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tbody>
    <?php if(gd_use_provider() === true) { ?>
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
    <tr>
        <th>검색어</th>
        <td colspan="3">
            <div class="form-inline">
                <?=gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
            </div>
        </td>
    </tr>
    <tr>
        <th>재고</th>
        <td colspan="3">
            <div class="form-inline">
                <input type="text" name="stock[]" value="<?=$search['stock'][0]; ?>" class="form-control width-sm"/>개 이상 ~
                <input type="text" name="stock[]" value="<?=$search['stock'][1]; ?>" class="form-control width-sm"/>개 이하
            </div>
        </td>
    </tr>
    </tbody>
</table>

<div class="table-btn">
    <input type="submit" value="검색" class="btn btn-lg btn-black">
</div>

<div class="table-header">
    <div class="pull-left">
        검색결과 <strong><?=number_format($page->recode['total']);?></strong>개 /
        전체 <strong><?=number_format($page->recode['amount']);?></strong>개
    </div>
    <div class="pull-right form-inline">
        <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
        <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
    </div>
</div>
</form>
<!-- 검색 -->

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="diffKey"></th>
            <th class="width5p">번호</th>
            <th class="width10p">공급사</th>
            <th class="width40p">상품명</th>
            <th class="width10p">옵션</th>
            <th class="width10p">재고</th>
            <th class="width10p">신청자</th>
            <th class="width10p">발송/미발송</th>
            <th class="width5p">상세보기</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $key => $val) {
                $diffKey = $option = $trBackground = '';
                $totalStock = is_numeric($val['stockCnt']) ? number_format((int)$val['stockCnt']) : '-';
                $goodsImage = gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['ori_goodsNm'], '_blank');
                $goodsName = "<a href=\"javascript:;\" onclick=\"javascript:goods_register_popup('".$val['goodsNo']."');\">".$val['goodsNm']."</a>";

                //옵션
                $option = $goods->getGoodsRestockOptionDisplay($val);

                //상품 상태변화에 따른 색상
                list($status, $color) = $goods->getGoodsRestockStatus($val);
                if(trim($color) !== ''){
                    $trBackground = "style='background-color:".$color.";'";
                }
                //완전 삭제된 상품
                if($status === 'deleteComplete'){
                    $totalStock = $option = $val['companyNm'] = '-';
                    $goodsName = '완전 삭제된 상품입니다.';
                    $goodsImage = '';
                }
        ?>
                <tr <?=$trBackground?>>
                    <td class="center"><input type="checkbox" name="diffKey[]" value="<?=$val['diffKey']; ?>" /></td>
                    <td class="center number"><?=number_format($page->idx--); ?></td>
                    <td class="center number"><?=$val['companyNm']?></td>
                    <td>
                        <?=$goodsImage?>
                        <?=$goodsName?>
                    </td>
                    <td class="center"><?=$option?></td>
                    <td class="center lmenu"><?=$totalStock?></td>
                    <td class="center lmenu"><?=number_format($val['requestCount'])?>명</td>
                    <td class="center number"><strong><?=number_format($val['smsSendY'])?></strong>/<?=number_format($val['smsSendN'])?></td>
                    <td class="center date">
                        <label>
                            <button type="button" class="btn btn-sm btn-gray goodsRestock-requestList" data-diffKey="<?=$val['diffKey']?>">신청자 목록</button>
                        </label>
                    </td>
                </tr>
                <?php
            }
        }
        else {
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
            <button type="button" class="btn btn-white js-check-delete">선택 삭제</button>
        </div>
        <div class="pull-right"></div>
    </div>
</form>
<div>
    <p class="notice-danger">
        노란색 리스트는 상품정보(상품명/옵션명/옵션값)가 변경된 리스트이며 재고량이 다를 수 있습니다.<br />
        빨간색 리스트는 상품이 삭제된 리스트이며, 완전 삭제된 상품의 경우 “완전삭제＂로 표기됩니다.<br />
        해당 상품의 상품정보를 확인하신 후 재입고 알림 메세지를 전송해 주세요.
    </p>
</div>
<div class="text-center"><?=$page->getPage(); ?></div>


<script type="text/javascript">
    <!--
    $(document).ready(function () {
        //신청자 목록
        $('.goodsRestock-requestList').click(function () {
            window.location.href='./goods_restock_view.php?diffKey=' + $(this).attr('data-diffKey');
        });

        // 삭제
        $('button.js-check-delete').click(function () {
            var chkCnt = $('input[name*="diffKey"]:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            dialog_confirm("선택한 " + chkCnt + "개 신청리스트를 정말로 삭제하시겠습니까?\n리스트 삭제시 신청자 목록도 함께 삭제되며 삭제된 정보는 복구되지 않습니다.", function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('delete_goodsRestock');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './goods_ps.php');
                    $('#frmList').submit();
                }
            });
        });

        //정렬, 리스팅 개수
        $("select[name='pageNum'], select[name='sort']").change(function () {
            $('#frmSearchGoods').submit();
        });

        //재고
        $("input[name*='stock']").number_only();

        // 재입고 알림 상품 관리
        $('.js-batch-restock').on('click', function (e) {
            var addParam = {
                "layerFormID": 'restockBatchLayer',
                "parentFormID": 'restockScmLayer',
                "dataFormID": 'restock_info_scm',
                "layerTitle": '재입고 알림 상품 관리'
            };
            layer_add_info('goods_restock_batch', addParam);
        });
    });

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {
        var addParam = { "mode": mode };

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
