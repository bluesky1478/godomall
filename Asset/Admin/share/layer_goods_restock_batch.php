<div>
    <div class="mgt10"></div>
    <div>
        <form id="frmSearchRestockGoods">
            <table class="table-cols no-title-line mgb10">
                <colgroup>
                    <col class="width-sm" />
                    <col />
                </colgroup>
                <?php if(gd_use_provider() === true) { ?>
                    <?php if (!isset($isProvider) && $isProvider != true) { ?>
                <tr>
                    <th>공급사 구분</th>
                    <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="all" <?=gd_isset($checked['scmFl']['all']);?>/>전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="n" <?=gd_isset($checked['scmFl']['n']);?> onclick="$('#restockScmLayer').html('');"/>본사
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']);?> onclick="restock_layer_register('scm', 'checkbox')"/>공급사
                        </label>
                        <label>
                            <button type="button" class="btn btn-sm btn-gray" onclick="restock_layer_register('scm','checkbox')">공급사 선택</button>
                        </label>
                        <div id="restockScmLayer" class="selected-btn-group <?=$search['scmFl'] == 'y' && !empty($search['scmNo']) ? 'active' : '';?>">
                            <h5>선택된 공급사 : </h5>
                            <?php if ($search['scmFl'] == 'y' && empty($search['scmNo']) === false) { ?>
                                <?php foreach ($search['scmNo'] as $k => $v) { ?>
                                    <div id="layer_info_scm_<?=$v;?>" class="btn-group btn-group-xs">
                                        <input type="hidden" name="scmNo[]" value="<?=$v;?>"/>
                                        <input type="hidden" name="scmNoNm[]" value="<?=$search['scmNoNm'][$k];?>"/>
                                        <span class="btn"><?=$search['scmNoNm'][$k];?></span>
                                        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#layer_info_scm_<?=$v;?>">삭제</button>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                    <?php } ?>
                <?php } ?>
                <tr>
                    <th>검색어</th>
                    <td>
                        <div class="form-inline">
                            <?=gd_select_box('key','key',$search['combineSearch'],null,$search['key']);?>
                            <input type="text" name="keyword" value="<?=$search['keyword'];?>" class="form-control" />
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>카테고리 선택</th>
                    <td>
                        <div class="form-inline">
                            <?=$cate->getMultiCategoryBox('layerCateGoods', gd_isset($search['cateGoods']), 'class="form-control"');?>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="categoryNoneFl" value="y" <?=gd_isset($checked['categoryNoneFl']['y']);?>> 미지정 상품
                            </label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>브랜드</th>
                    <td>
                        <div class="form-inline">
                            <?=$brand->getMultiCategoryBox(null, gd_isset($search['brand']), 'class="form-control"');?>
                            <label class="checkbox-inline"><input type="checkbox" name="brandNoneFl" value="y" <?=gd_isset($checked['brandNoneFl']['y']);?>> 미지정 상품</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>기간검색</th>
                    <td>
                        <div class="form-inline">
                            <select name="searchDateFl" class="form-control">
                                <option value="regDt" <?=gd_isset($selected['searchDateFl']['regDt']);?>>등록일</option>
                                <option value="modDt" <?=gd_isset($selected['searchDateFl']['modDt']);?>>수정일</option>
                            </select>

                            <div class="input-group js-datepicker">
                                <input type="text" class="form-control width-xs" name="searchDate[0]" value="<?=$search['searchDate'][0];?>" />
                                <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                            </div>
                            ~
                            <div class="input-group js-datepicker">
                                <input type="text" class="form-control width-xs" name="searchDate[1]" value="<?=$search['searchDate'][1];?>" />
                                <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>재입고 알림<br/>사용여부</th>
                    <td>
                        <div class="form-inline">
                            <label class="radio-inline">
                                <input type="radio" name="restockFl" value="all" <?=gd_isset($checked['restockFl']['all']);?>/>전체
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="restockFl" value="y" <?=gd_isset($checked['restockFl']['y']);?>/>사용함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="restockFl" value="n" <?=gd_isset($checked['restockFl']['n']);?>/>사용안함
                            </label>
                        </div>
                    </td>
                </tr>
            </table>
            <p class="center"><input type="button" value="검색" class="btn btn-hf btn-black" onclick="restock_layer_list_search();"></p>
            <div class="table-header">
                <div class="pull-left">
                    검색 <strong><?=number_format($page->recode['total']);?></strong>개 /
                    전체 <strong><?=number_format($page->recode['amount']);?></strong>개
                </div>
                <div class="pull-right form-inline">
                    <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum', 20), null);?>
                </div>
            </div>
        </form>
    </div>
</div>
<div>
    <form id="frmRestockBatch" method="post" target="ifrmProcess">
        <input type="hidden" name="mode" value="batch_restock">
        <?php
        foreach ($batchAll as $key => $val) {
            echo '<input type="hidden" name="queryAll['.$key.']" value="'.$val.'" />'.chr(10);
        }
        ?>
        <input type="hidden" name="batchAll" value="">
        <div class="restock-batch-area">
            <div class="restock-batch-menu">
                <span class="width6p"><input type="checkbox" class="js-checkall" data-target-name="arrGoodsNo[]" /></span>
                <span class="width7p">번호</span>
                <span class="width7p">이미지</span>
                <span class="width27p">상품명</span>
                <span class="width10p">판매가</span>
                <span class="width15p">공급사</span>
                <span class="width10p">재고</span>
                <span class="width18p">재입고 알림 사용여부</span>
            </div>
            <div class="restock-batch-list">
            <?php
            if (gd_isset($data) && is_array($data)) {
                $i = 0;
                foreach ($data as $key => $val) {
                    list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);
                    ?>
                    <div class="restock-batch-data<?php if (count($data) > 5) echo ' width102p';?>">
                        <span class="width6p center"><input type="checkbox" name="arrGoodsNo[]" value="<?=$val['goodsNo'];?>" /></span>
                        <span class="width7p center"><?=number_format($page->idx--);?></span>
                        <span class="width7p pdt2 center">
                            <div>
                                <?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank', 'id="goodsImage_'.$val['goodsNo'].'"');?>
                            </div>
                        </span>
                        <span class="width27p" style="white-space: normal;">
                            <a href="../goods/goods_register.php?goodsNo=<?=$val['goodsNo'];?>" target="_blank"><?=gd_html_cut(gd_remove_tag($val['goodsNm']), 20, '...');?></a>
                            <input type="hidden" id="goodsNm_<?=$val['goodsNo'];?>" value="<?=gd_remove_tag($val['goodsNm']);?>" />
                            <input type="hidden" id="regDt_<?=$val['goodsNo'];?>" value="<?=gd_date_format('Y-m-d', $val['regDt']);?>" />
                        </span>
                        <span class="width10p center"><?=number_format($val['goodsPrice']);?></span>
                        <span class="width15p"><?=$val['scmNm'];?></span>
                        <span class="width10p center"><?=$totalStock;?></span>
                        <span class="width15p center">
                            <?php
                                if ($val['restockFl'] == 'y') {
                                    echo '사용함';
                                } else {
                                    echo '사용안함';
                                }
                            ?>
                        </span>
                    </div>
                    <?php
                    $i++;
                }
            } else {
                ?>
                <div class="restock-batch-data center pd5">검색을 이용해 주세요.</div>
                <?php
            }
            ?>
            </div>
        </div>
        <div class="center"><?=$page->getPage('restock_layer_list_search(\'PAGELINK\')');?></div>
        <div>
            <table class="table-cols no-title-line mgb10 width100p">
                <colgroup>
                    <col class="width-sm" />
                    <col />
                </colgroup>
                <th>사용설정</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="restockBatchTarget" class="form-control">
                            <option value="">= 상품 선택 =</option>
                            <option value="all">검색된 상품</option>
                            <option value="select">선택된 상품</option>
                        </select>
                        의 재입고 알림 사용상태를
                        <select name="restockBatchStatus" class="form-control">
                            <option value="">= 선택 =</option>
                            <option value="y">사용함</option>
                            <option value="n">사용안함</option>
                        </select>
                        으로
                        <div class="btn-group">
                            <input type="submit" value="수정" class="btn btn-red">
                        </div>
                    </div>
                </td>
            </table>
        </div>
    </form>
    <span class="notice-danger">검색된 상품수가 많은 경우 가능하면 한 페이지씩 선택하여 수정하세요.</span>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function(){
        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                restock_layer_list_search();
                return false;
            }
        });

        $('select[name="pageNum"]').change(function () {
            restock_layer_list_search();
        });

        $('#frmRestockBatch').validate({
            dialog: false,
            submitHandler: function(form) {
                if ($('select[name="restockBatchTarget"]').val() === 'all') {
                    $('input[name="batchAll"]').val('y');
                }
                $.ajax({
                    method: 'POST',
                    cache: false,
                    url: '../goods/goods_ps.php',
                    data: $(form).serializeArray(),
                    success: function (data) {
                        if (data.applyFl === 'y') {
                            var msg = '수정이 완료 되었습니다.';
                        } else {
                            var msg = '승인을 요청하였습니다.';
                        }
                        BootstrapDialog.show({
                            title: '재입고 알림 상품 관리',
                            message: msg,
                            closable: true
                        });

                        restock_layer_list_search();
                    },
                    error: function (data) {
                        alert(data.message);
                    }
                });

            },
            rules: {
                'arrGoodsNo[]': {
                    required: function () {
                        return $('select[name="restockBatchTarget"]').val() === 'select';
                    }
                },
                'restockBatchTarget': 'required',
                'restockBatchStatus': 'required'
            },
            messages: {
                'arrGoodsNo[]': {
                    required: '선택된 상품이 없습니다'
                },
                'restockBatchTarget': '선택된 항목이 없습니다.',
                'restockBatchStatus': '선택된 항목이 없습니다.'
            }
        });
    });

    function restock_layer_list_search(pagelink) {
        var frm = $("#frmSearchRestockGoods").serializeArray();
        var cateGoods	= '';
        var brandGoods	= '';
        var scmNoArr = [];
        var scmNoNmArr = [];

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }

        var parameters		= {
            'layerFormID'	: '<?=$layerFormID;?>',
            'parentFormID'	: '<?=$parentFormID;?>',
            'dataFormID'	: '<?=$dataFormID;?>',
            'pagelink': pagelink
        };

        $.each(frm, function(i, field){
            if (field.name) {
                if (field.name == 'scmNo[]') {
                    scmNoArr.push(field.value);
                } else if (field.name == 'scmNoNm[]') {
                    scmNoNmArr.push(field.value);
                } else {
                    parameters[field.name] = field.value;
                }
            }
        });

        for (var i = <?=DEFAULT_DEPTH_CATE;?>; i > 0; i--) {
            if ($('#layerCateGoods'+i).val()) {
                cateGoods	= $('#layerCateGoods'+i).val();
                break;
            }
        }
        for (var i = <?=DEFAULT_DEPTH_BRAND;?>; i > 0; i--) {
            if ($('#brand'+i).val()) {
                brandGoods	= $('#brand'+i).val();
                break;
            }
        }

        parameters['scmNo[]'] = scmNoArr;
        parameters['scmNoNm[]'] = scmNoNmArr;
        parameters['cateGoods[]'] = cateGoods;
        parameters['brand[]'] = brandGoods;

        $.get('../share/layer_goods_restock_batch.php', parameters, function(data){
            $('#<?=$layerFormID;?>').html(data);
        });
    }

    function restock_layer_register(typeStr, mode, isDisabled) {
        var addParam = {
            'mode': mode,
            'parentFormID': '<?=$parentFormID;?>',
            'dataFormID': '<?=$dataFormID;?>',
            'layerType': 'restock'
        };

        if (typeStr == 'scm') {
            $('input:radio[name="scmFl"][value="y"]', '#frmSearchRestockGoods').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }
    //-->
</script>
