<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $("#frmGoods").validate({
            submitHandler: function (form) {
                form.target='ifrmProcess';
                form.submit();
            },
            // onclick: false, // <-- add this option
            rules: {
                groupNm: 'required'
            },
            messages: {
                groupNm: {
                    required: '그룹명을 입력하세요.'
                }
            }
        });


        <?php if(($data['mode'] =='register' &&  Request::get()->get('scmFl')) ||  $data['mode'] =='modify') { ?>
        $('input:radio[name=scmFl]').prop("disabled", true);
        $('button.scmBtn').attr("disabled", true);
        <?php }?>


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


    /**
     * 추가 상품 선택
     *
     * @author artherot
     * @param string orderNo 주문 번호
     */
    function add_goods_search_popup()
    {
        var scmNo = '';
        var scmNoNm = '';
        var scmFl =  $('input:radio[name=scmFl]:checked').val();
        if(scmFl =='y') {
            scmNo =  $('input[name=scmNo]').val();
            scmNoNm =  $('input[name=scmNoNm]').val();
        }

        window.open('../share/popup_add_goods.php?scmFl='+scmFl+'&scmNo='+scmNo+'&scmNoNm='+scmNoNm, 'member_crm', 'width=1210, height=710, scrollbars=no');
    };

    /**
     * 추가 상품 등록
     *
     * @author artherot
     * @param string orderNo 주문 번호
     */
    function add_goods_register_popup()
    {
        var scmNo = '';
        var scmNoNm = '';
        var scmFl =  $('input:radio[name=scmFl]:checked').val();
        if(scmFl =='y') {
            scmNo =  $('input[name=scmNo]').val();
            scmNoNm =  $('input[name=scmNoNm]').val();
        }

        window.open('../goods/add_goods_register.php?popupMode=yes&addGroup=true&scmFl='+scmFl+'&scmNo='+scmNo+'&scmNoNm='+scmNoNm, 'member_crm', 'width=1410, height=700, scrollbars=yes');
    };



    function delete_option() {

        var chkCnt = $('input[name="itemGoodsNo[]"]:checked').length;
        if (chkCnt == 0) {
            alert('선택된 추가상품이 없습니다.');
            return;
        }

        dialog_confirm('선택한 ' + chkCnt + '개 추가상품을 삭제하시겠습니까?', function (result) {
            if (result) {
                $('input[name="itemGoodsNo[]"]:checked').each(function () {
                    field_remove('tbl_add_goods_' + $(this).val());
                });

                var cnt = $('input[name="itemGoodsNo[]"]').length;

                $('input[name="itemGoodsNo[]"]').each(function () {
                    $(".addGoodsNumber_"+$(this).val()).html(cnt);
                    cnt--;
                });
            }
        });

    }




    function setAddGoods(frmData) {

        var addHtml = "";
        var cnt = frmData.info.length;
        var mode = frmData.mode;

        $.each(frmData.info, function (key, val) {

            // 상품 재고
            if (val.stockFl == '0') {
                totalStock    = '∞';
            } else {
                totalStock    = val.totalStock;
            }

            if(val.soldOutFl =='y' || totalStock =='0') stockText = "품절";
            else stockText="정상";



            if(val.sortFix == true) {
                sortFix = "checked = 'checked'";
                tableCss = "style='background:#d3d3d3' class='add_goods_fix'";
            }
            else {
                sortFix = '';
                tableCss = "class='add_goods_free'";
            }


            addHtml += '<tr id="tbl_add_goods_'+val.goodsNo+'" '+tableCss+'>';
            addHtml += '<td class="center">';

            addHtml += '<input type="hidden" name="itemGoodsNm[]" value="'+val.goodsNm+'" />';
            addHtml += '<input type="hidden" name="itemGoodsPrice[]" value="'+val.goodsPrice+'" />';
            addHtml += '<input type="hidden" name="itemScmNm[]" value="'+val.scmNm+'" />';
            addHtml += '<input type="hidden" name="itemTotalStock[]" value="'+val.totalStock+'" />';
            addHtml += '<input type="hidden" name="itemBrandNm[]" value="'+val.brandNm+'" />';
            addHtml += '<input type="hidden" name="itemMakerNm[]" value="'+val.makerNm+'" />';
            addHtml += '<input type="hidden" name="itemOptionNm[]" value="'+val.optionNm+'" />';
            addHtml += '<input type="hidden" name="itemImage[]" value="'+val.image+'" />';
            addHtml += '<input type="hidden" name="itemSoldOutFl[]" value="'+val.soldOutFl+'" />';
            addHtml += '<input type="hidden" name="itemStockFl[]" value="'+val.stockFl+'" />';
            addHtml += '<input type="checkbox" name="itemGoodsNo[]" id="layer_goods_'+val.goodsNo+'"  value="'+val.goodsNo+'"/></td>';
            addHtml += '<td class="center number addGoodsNumber_'+val.goodsNo+'">'+(cnt)+'</td>';
            addHtml += '<td class="center">'+decodeURIComponent(val.image)+'</td>';
            addHtml += '<td><a href="../goods/add_goods_register.php?addGoodsNo='+val.goodsNo+'" target="_blank">'+val.goodsNm+'</a><input type="hidden" name="addGoodsNoData[]" value="'+val.goodsNo+'" /><input type="checkbox" name="sortFix[]" class="layer_sort_fix_'+val.goodsNo+'"  value="'+val.goodsNo+'" '+sortFix+'  style="display:none"></td>';
            addHtml += '<td class="center">'+val.optionNm+'</td>';
            addHtml += '<td class="center">'+val.goodsPrice+'</td>';
            addHtml += '<td class="center">'+val.scmNm+'</td>';
            addHtml += '<td class="center">'+totalStock+'</td>';
            addHtml += '<td class="center">'+stockText+'</td>';
            addHtml += '</tr>';


            cnt--;
        });


        if(mode =='register_ajax' && $('input[name="itemGoodsNo[]"]').length > 0)  $("#tbl_add_goods_set tbody").append(addHtml);
        else $("#tbl_add_goods_set tbody").html(addHtml);

        var cnt = $('input[name="itemGoodsNo[]"]').length;

        $('input[name="itemGoodsNo[]"]').each(function () {
            $(".addGoodsNumber_"+$(this).val()).html(cnt);
            cnt--;
        });


    }



    //-->
</script>
<form id="frmGoods" name="frmGoods" action="./add_goods_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="group_<?=$data['mode']; ?>"/>
    <?php if ($data['mode'] == 'modify') { ?><input type="hidden" name="sno" value="<?=gd_isset($data['sno']); ?>" /><?php } ?>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./add_goods_group_list.php');" />
            <input type="submit" value="저장" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title gd-help-manual">
        기본정보
    </div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <?php if(gd_use_provider()) { ?>
            <?php if(gd_is_provider()) { ?>
                <input type="hidden" name="scmNo" value="<?=$data['scmNo']?>">
            <?php }  else { ?>
            <tr>
                <th class="input_title r_space ">공급사 구분</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="scmFl"
                                  value="n" <?=gd_isset($checked['scmFl']['n']); ?>    onclick="$('#scmLayer').html('')";/>본사</label>
                    <label class="radio-inline"><input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?>
                                  onclick="layer_register('scm','radio',true)"/>공급사</label>
                    <label > <button type="button" class="btn btn-sm btn-gray scmBtn" onclick="layer_register('scm','radio',true)">공급사 선택</button></label>
                    <div id="scmLayer" class="selected-btn-group <?= $data['scmNo'] != DEFAULT_CODE_SCMNO && $data['scmNoNm'] ? 'active' : ''?>">
                        <h5>선택된 공급사 : </h5>
                        <?php if ($data['scmNo']) { ?>
                            <span id="info_scm_<?= $data['scmNo'] ?>" class="btn-group btn-group-xs">
							<input type="hidden" name="scmNo" value="<?= $data['scmNo'] ?>"/>
							<input type="hidden" name="scmNoNm" value="<?= $data['scmNoNm'] ?>"/>
                                <?php if($data['scmNo'] != DEFAULT_CODE_SCMNO) { ?>
                                    <span class="btn"><?= $data['scmNoNm'] ?></span>
                                        <?php if($data['mode'] =='register' &&  !Request::get()->get('scmFl')) { ?>
                                        <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#info_scm_<?= $data['scmNo'] ?>">삭제</button> <?php } ?>
                                <?php }?>
					        </span>
                        <?php } ?>
                    </div>

                </td>
            </tr>
            <?php } ?>
            <?php } ?>
            <tr>
                <th class="input_title r_space ">그룹코드</th>
                <td>
                    <?php if ($data['groupCd']) { ?><?= $data['groupCd'] ?> <label title=""><input type="hidden"
                                                                                                   name="groupCd"
                                                                                                   value="<?=gd_isset($data['groupCd']); ?>"/></label>
                    <?php } else {
                        echo '추가 상품 그룹 등록 저장 시 자동 생성됩니다.';
                    } ?>
                </td>
            </tr>
            <tr>
                <th class="input_title r_space require">그룹명</th>
                <td class="input_area" >
                    <label title=""><input type="text" name="groupNm" value="<?=gd_isset($data['groupNm']); ?>"
                                           class="form-control width-3xl js-maxlength" maxlength="250"/></label>
                </td>
            </tr>
            <tr>
                <th>그룹 설명</th>
                <td class="input_area" >
                    <label title=""><input type="text" name="groupDescription" value="<?=gd_isset($data['groupDescription']); ?>"
                                           class="form-control width-3xl js-maxlength" maxlength="250"/></label>
                </td>
            </tr>
        </table>

    <div class="table-title gd-help-manual">
        선택된 추가상품
    </div>
    <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_set" class="table table-rows">
        <thead>
        <tr id="goodsRegisteredTrArea">
            <th class="width5p"><input type="checkbox" id="allCheck" value="y" class="js-checkall" data-target-name="itemGoodsNo"/></th>
            <th class="width5p">번호</th>
            <th class="width5p">이미지</th>
            <th >상품명</th>
            <th class="width15p">옵션</th>
            <th class="width10p">판매가</th>
            <th class="width10p">공급사</th>
            <th class="width10p">재고</th>
            <th class="width10p">품절상태</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (count(gd_isset($addGoodsList))) {
            foreach ($addGoodsList as $key => $val) {

                    if($val['stockUseFl'] =='0') {
                        $stockUseFl = "n";
                    } else {
                        $stockUseFl = "y";
                    }

                    list($totalStock,$stockText) = gd_is_goods_state($stockUseFl,$val['stockCnt'],$val['soldOutFl']);

                ?>

                <tr id="tbl_add_goods_<?=$val['addGoodsNo'];?>" class="add_goods_free">
                    <td class="center">
                        <input type="hidden" name="itemGoodsNm[]" value="<?=strip_tags($val['goodsNm'])?>" />
                        <input type="hidden" name="itemGoodsPrice[]" value="<?=$val['goodsPrice']?>" />
                        <input type="hidden" name="itemScmNm[]" value="<?=$val['scmNm']?>" />
                        <input type="hidden" name="itemTotalStock[]" value="<?=$val['stockCnt']?>" />
                        <input type="hidden" name="itemSoldOutFl[]" value="<?=gd_isset($val['soldOutFl'])?>" />
                        <input type="hidden" name="itemStockFl[]" value="<?=gd_isset($val['stockUseFl'])?>" />
                        <input type="hidden" name="itemBrandNm[]" value="<?=gd_isset($val['brandNm'])?>" />
                        <input type="hidden" name="itemMakerNm[]" value="<?=gd_isset($val['makerNm'])?>" />
                        <input type="hidden" name="itemOptionNm[]" value="<?=gd_isset($val['optionNm'])?>" />
                        <input type="hidden" name="itemImage[]" value="<?=rawurlencode(gd_html_add_goods_image($val['addGoodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>" />
                        <input type="checkbox" name="itemGoodsNo[]" id="layer_goods_<?=$val['addGoodsNo'];?>"  value="<?=$val['addGoodsNo']; ?>"/></td>
                    <td class="center number addGoodsNumber_<?=$val['addGoodsNo'];?>"><?=$key+1?></td>
                    <td><?=gd_html_add_goods_image($val['addGoodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></td>
                    <td>
                        <a href="../goods/add_goods_register.php?addGoodsNo=<?=$val['addGoodsNo']?>" target="_blank"><?=$val['goodsNm'];?> </a>
                        <input type="hidden" name="addGoodsNoData[]" value="<?=$val['addGoodsNo']?>" /> <input type="checkbox" name="sortFix[]" class="layer_sort_fix_<?=$val['addGoodsNo'];?>"  value="<?=$val['addGoodsNo']; ?>" <?php  if($data['fixGoodsNo'] && in_array($val['addGoodsNo'],$data['fixGoodsNo'])) { echo "checked='true'"; }  ?> style="display:none">
                    </td>
                    <td class="center"><?=$val['optionNm']; ?></td>
                    <td class="center"><?=gd_currency_display($val['goodsPrice']); ?></td>
                    <td class="center"><?=$val['scmNm']; ?></td>
                    <td class="center"><?=$totalStock; ?></td>
                    <td class="center"><?=$stockText?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr id="tbl_add_goods_tr_none"><td class="no-data" colspan="9">선택된 추가 상품이 없습니다.</td></tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left">
            <button class="checkDelete btn btn-white" type="button" onclick="delete_option()">선택 삭제</button>
        </div>

        <div class="pull-right">
           <button class="checkRegister btn btn-white" type="button"  onclick="add_goods_search_popup()">추가상품 불러오기</button>
            <button class="checkRegister btn btn-white" type="button"  onclick="add_goods_register_popup()">추가상품 직접등록</button>
        </div>
    </div>
</form>
