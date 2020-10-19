<form id="recomGoodsfrm" name="recomGoodsfrm" action="./display_config_ps.php" method="post" target="ifrmProcess" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="recom_goods" />
    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="저장" class="btn btn-red" onclick="save_recom()" />
        </div>
    </div>


    <div class="table-title">
        리스트 영역 상세 설정
    </div>
    <div>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /><col class="width" /><col /></colgroup>
            <tr>
                <th>PC쇼핑몰 노출상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="pcDisplayFl" value="y" <?=gd_isset($checked['pcDisplayFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="pcDisplayFl" value="n" <?=gd_isset($checked['pcDisplayFl']['n']);?>/>노출안함</label>
                </td>
                <th>모바일쇼핑몰 노출상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="mobileDisplayFl" value="y" <?=gd_isset($checked['mobileDisplayFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="mobileDisplayFl" value="n" <?=gd_isset($checked['mobileDisplayFl']['n']);?>/>노출안함</label>
                </td>
            </tr>
            <tr>
                <th >이미지설정 </th>
                <td  colspan="3"><div class="form-inline">
                        <?php
                        foreach ($confImage as $key => $val) {
                            if ($key == 'imageType') {
                                continue;
                            }
                            $arrImage[$key] = $val['text'] . ' - ' . $val['size1'] . ' pixel';
                            if($confImage['imageType'] == 'fixed') {
                                $arrImage[$key] .= ' / 세로 ' . $val['hsize1'] . ' pixel';
                            }
                        }

                        echo gd_select_box('imageCd','imageCd',$arrImage,null,$config['imageCd'],null);
                        ?>
                        <span class="notice-info">이미지는 <a href="/policy/goods_images.php" target="_blank" class="btn-link">기본설정>상품 정책>상품 이미지 사이즈 설정</a>에서 관리할 수 있습니다.</span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>상품 노출 방식</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="displayType" value="random"  <?=gd_isset($checked['displayType']['random']);?>/>랜덤</label>
                </td>
                <th>품절상품 노출</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="soldOutFl" value="y" <?=gd_isset($checked['soldOutFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="soldOutFl" value="n" <?=gd_isset($checked['soldOutFl']['n']);?>/>노출안함</label>
                </td>
            </tr>
            <tr>
                <th class="require">노출항목 설정 </th>
                <td  colspan="3">
                    <?php foreach($themeDisplayField as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="displayField[]" value="<?=$k?>" <?php if(@in_array($k,array_values($config['displayField']))) { echo "checked"; } ?>>  <?=$v?></label></span>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th >할인적용가 설정</th>
                <td >
                    <?php foreach($themeGoodsDiscount as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="goodsDiscount[]" value="<?=$k?>" <?php if(in_array($k,array_values($config['goodsDiscount']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                    <?php } ?>
                    <div class="notice-info">할인적용가 노출 시 적용할 할인금액을 설정합니다.</div>
                </td>
                <th >취소선 추가 설정</th>
                <td >
                    <?php foreach($themePriceStrike as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="priceStrike[]" value="<?=$k?>" <?php if(in_array($k,array_values($config['priceStrike']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                    <?php } ?>
                    <div class="notice-info">
                        체크시 쇼핑몰에 취소선 효과가 적용되어 출력됩니다. (예시. 정가 <span style="text-decoration: line-through;">12,000</span>원)<br />
                        단, 판매가의 경우 할인가가 없는 상품에는 취소선이 적용되지 않습니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th >노출항목 추가 설정</th>
                <td colspan="3">
                    <?php foreach($themeDisplayAddField as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="displayAddField[]" value="<?=$k?>" <?php if(in_array($k,array_values($config['displayAddField']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                    <?php } ?>
                    <div class="notice-info">[할인율] 체크 시 판매가 대비 할인율이 할인금액에 노출됩니다. (쿠폰가와 할인적용가에 적용됩니다.)</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title">
        노출상품 설정 <span class="notice-danger"> 추천 상품은 최대 <?= $defaultRecommendGoodsCnt; ?>개 까지만 선택 가능합니다.</span>
    </div>

    <div>
        <div class="js-recom-manual-goods" <?php if($data['recomSortAutoFl'] =='y') { echo "style='display:none'"; }  ?>>
            <table cellpadding="0" cellpadding="0" width="100%" id="tbl_recom_goods_set" class="table table-rows">
                <thead>
                <tr>
                    <th class="center width-3xs "><input type="checkbox" value="y" class="js-checkall" data-target-name="itemGoodsNo[]"/></th>
                    <th class="center width-3xs ">번호</th>
                    <th class="width-2xs">이미지</th>
                    <th >상품명</th>
                    <th class="center width10p">판매가</th>
                    <th class="center width10p" >공급사</th>
                    <th class="center width5p">재고</th>
                    <th class="center width5p">품절상태</th>
                    <th class="center width10p">PC쇼핑몰 노출상태</th>
                    <th class="center width10p">모바일쇼핑몰 노출상태</th>
                </tr>
                </thead>

                <tbody id="recomGoods"  class="active">
                <?php if (empty($data) === false) {
                    foreach ($data as $key => $val) {
                        list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);
                        $goodsDisplay = $goodsDisplayMobile = '노출함';
                        if ($val['goodsDisplayFl'] != 'y') $goodsDisplay = '노출안함';
                        if ($val['goodsDisplayMobileFl'] != 'y') $goodsDisplayMobile = '노출안함';
                        ?>
                        <tr id="idGoods_<?=$val['goodsNo']; ?>" class="recom_tr">
                            <td class="center">
                                <input type="checkbox" name="del[]" value="<?=$val['goodsNo']; ?>">
                            </td>
                            <td class="center number recomGoodsNumber_<?=$val['goodsNo']; ?>"><span><?=number_format($key+1);?></span><input type="hidden" name="recomGoods[]" value="<?=$val['goodsNo']; ?>" /></td>
                            <td class="center"><?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank'); ?></td>
                            <td>
                                <a href="../goods/goods_register.php?goodsNo=<?=$val['goodsNo'];?>" target="_blank"><?=$val['goodsNm'];?></a>
                            </td>
                            <td class="center"><?= number_format($val['goodsPrice']); ?> 원</td>
                            <td class="center"><?= $val['scmNm']; ?></td>
                            <td class="center"><?= $totalStock ?></td>
                            <td class="center"><?= $stockText ?></td>
                            <td class="center js-goodschoice-hide"><?= $goodsDisplay; ?></td>
                            <td class="center js-goodschoice-hide"><?= $goodsDisplayMobile; ?></td>
                        </tr>
                        <?php
                    } ?>
                <?php } else {  ?>
                    <tr id="tbl_recom_goods_tr_none"><td colspan="11" class="no-data">선택된 상품이 없습니다.</td></tr>
                <?php }
                ?>
                </tbody>
            </table>
        </div>

        <div class="table-action">
            <div class="pull-left">
                <button type="button" class="btn  btn-white"  onclick="delete_recom()">선택 삭제</button>
            </div>
            <div class="pull-right">
                <button type="button" class="btn btn-white" onclick="layer_register('goods')">상품 선택</button>
            </div>
        </div>

        <br/>
    </div>
</form>

<script type="text/javascript">
    $(function(){
        $('.js-checkall').click(function(){
            if ($(this).prop('checked') === true) {
                $('#recomGoods').find('input[type="checkbox"]').prop('checked', true);
            } else {
                $('#recomGoods').find('input[type="checkbox"]').prop('checked', false);
            }
        });
    });
    /**
     * 상품 선택
     *
     * @author artherot
     * @param string orderNo 주문 번호
     */
    function layer_register(typeStr)
    {
        var layerFormID		= 'recomForm';

        typeStrId =  typeStr.substr(0,1).toUpperCase() + typeStr.substr(1);

        var parentFormID	= 'recom'+typeStrId;
        var dataFormID		= 'id'+typeStrId;
        var dataInputNm		= 'recom'+typeStrId;
        var layerTitle		= '상품선택 ';
        var mode =  'recom';

        $("#"+parentFormID+"Table thead").show();
        $("#"+parentFormID+"Table tfoot").show();

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
            "childRow": $("#"+parentFormID + " tr.recom_tr").length
        };

        if(typeStr == 'goods'){
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
        }

        layer_add_info(typeStr,addParam);
    }

    function save_recom()
    {
        var limit = Number('<?= $defaultRecommendGoodsCnt; ?>');
        var recomGoodsCnt = $('input[name="recomGoods[]"]').length;

        if (!$('input[name="displayField[]"]:checked').length) {
            alert('노출항목을 선택하세요.');
            return false;
        }

        if (recomGoodsCnt > limit) {
            alert('추천 상품은 최대 ' + limit + '개 까지만 선택 가능합니다.');
            return false;
        }
        $('input[name="mode"]').val('recom_goods');
        $('#recomGoodsfrm').submit();
    }

    function delete_recom()
    {
        var cnt = $('input[name="del[]"]:checked').length;
        if (!cnt) {
            alert('선택된 상품이 없습니다.');
            return false;
        }
        if (confirm('선택한 ' + cnt + '개 상품을 삭제하시겠습니까?')) {
            $('input[name="del[]"]:checked').each(function(){
                $('#idGoods_' + $(this).val()).remove();
            });
            $('#recomGoods .number').each(function(index){
                $(this).find('span').html(index + 1);
            });
        }
    }
</script>
