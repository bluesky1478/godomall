<?php
//펼침,닫힘 정보
$toggle = gd_policy('display.toggle');
$SessScmNo = Session::get('manager.scmNo');
?>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        initDepthToggle(<?=$SessScmNo?>);//4depth 메뉴 보임안보임처리
        set_range();
        set_except();
        mobile_same();
        set_type();
        set_soldOutDisplay('front');
        set_soldOutDisplay('mobile');

        <?php
        if(empty($data['sno']) && $totalCnt >= 10){
        ?>
            alert('인기상품 노출 관리는 최대 10개까지만 등록 가능합니다.');
            document.location.href = 'populate_list.php';
        <?php
        }
        ?>

        $("#frmGoods").validate({
            submitHandler: function (form) {
                if ($('input[name="displayNm"]')[0].value.trim() == '') {
                    alert('\'인기상품 노출명\'은 필수항목 입니다.');
                    return false;
                }
                if ($('input[name="same"]').prop('checked') === false && !$('input[name="mobileDisplayField[]"]:checked').length) {
                    alert('노출항목을 선택하세요.');
                    return false;
                }

                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                displayNm:{
                    required: true,
                    regex: /[:'"<>`]/
                }
            },
            messages: {
                displayNm: {
                    required: '인기상품 노출명은 필수항목 입니다.',
                    regex: '인기상품 노출명에 사용할 수 없는 문자가 있습니다.'
                }
            }
        });

        $.validator.addMethod('regex', function (value, element, regexpr){
            return regexpr.test(value) !== true;
        });
    });

    var set_range = function() {
        var value = $('input:radio[name="range"]:checked').val();

        $('.range-all').hide();
        $('.range-goods').hide();
        $('.range-category').hide();
        $('.range-brand').hide();

        $('input[name="except[]"]')[0].disabled = false;
        $('input[name="except[]"]')[1].disabled = false;
        $('input[name="except[]"]')[2].disabled = false;
        $('#exceptItemGoods').show();
        $('#exceptItemCategory').show();
        $('#exceptItemBrand').show();

        if(value == 'all'){
            $('.range-all').show();
        }else if(value == 'goods'){
            $('.range-goods').show();
            $('input[name="except[]"]')[0].disabled = true;
            $('input[name="except[]"]')[0].checked = false;
            $('#exceptItemGoods').hide();
        }else if(value == 'category'){
            $('.range-category').show();
            $('input[name="except[]"]')[1].disabled = true;
            $('input[name="except[]"]')[1].checked = false;
            $('#exceptItemCategory').hide();
        }else if(value == 'brand'){
            $('.range-brand').show();
            $('input[name="except[]"]')[2].disabled = true;
            $('input[name="except[]"]')[2].checked = false;
            $('#exceptItemBrand').hide();
        }
        set_except();
    };

    var set_except = function() {
        var checked_goods = $('input[name="except[]"]')[0].checked;  //예외상품
        var checked_category = $('input[name="except[]"]')[1].checked;  //예외카테고리
        var checked_brand = $('input[name="except[]"]')[2].checked;  //예외브랜드

        $('.except-goods').hide();
        $('.except-category').hide();
        $('.except-brand').hide();

        if(checked_goods === true){
            $('.except-goods').show();
        }
        if(checked_category === true){
            $('.except-category').show();
        }
        if(checked_brand === true){
            $('.except-brand').show();
        }

    };

    var mobile_same = function() {
        var checked = $('input[name="same"]').prop('checked');

        if (checked === true) {
            $('.mobile-config-area').hide();
        } else {
            $('.mobile-config-area').show();
        }
    };

    var set_soldOutDisplay = function(key) {
        var name = key == 'mobile' ? 'mobileSoldOut' : 'soldOut';
        var soldOutFl = $('input[name="' + name + 'Fl"]:checked').val();

        if(soldOutFl =='n') $('input[name="' + name + 'DisplayFl"]').attr('disabled',true);
        else $('input[name="' + name + 'DisplayFl"]').attr('disabled',false);

    };

    var set_type = function() {
        var type = $('input[name="type"]:checked').val();
        var msg = '상품 판매 데이터(판매금액)';

        switch (type) {
            case 'sell':
                break;
            case 'hit':
                msg = '상품 클릭수 데이터';
                break;
            case 'sellCnt':
                msg = '상품 판매 데이터(판매횟수)';
                break;
            case 'view':
                msg = '상품 조회수 데이터';
                break;
            case 'cart':
                msg = '장바구니 담기 데이터';
                break;
            case 'wishlist':
                msg = '찜리스트 담기 데이터';
                break;
            case 'review':
                msg = '상품 후기 작성 데이터';
                break;
            case 'score':
                msg = '상품 후기 평점 데이터';
                break;
        }

        $('.msg-type').html(msg);
    };

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string typeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_register(typeStr, modeStr,isDisabled)
    {
        var layerFormID		= 'addPresentForm';
        var layerTitle		= '';

        typeStrId =  typeStr.substr(0,1).toUpperCase() + typeStr.substr(1);

        if (modeStr == 'collect') {
            var parentFormID	= 'collect'+typeStrId;
            var dataFormID		= 'idExcept'+typeStrId;
            var dataInputNm		= 'collect'+typeStrId;
            var layerTitle		= '특정  ';
        }

        if (modeStr == 'except') {
            var parentFormID	= 'except'+typeStrId;
            var dataFormID		= 'idExcept'+typeStrId;
            var dataInputNm		= 'except'+typeStrId;
            var layerTitle		= '예외  ';
        }

        // 레이어 창
        if (typeStr == 'goods') {
            var layerTitle		= layerTitle+'상품';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }
        if (typeStr == 'category') {
            var layerTitle		= layerTitle+'카테고리';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }
        if (typeStr == 'brand') {
            var layerTitle		= layerTitle+'브랜드';
            var mode =  'simple';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }

        layerTitle  = layerTitle + " 선택";

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
        };

        if(typeStr == 'display_main'){
            addParam['callFunc'] = 'copy_display_main';
        }


        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }



    //-->
</script>
<script type="text/javascript" src="/admin/gd_share/script/jquery/jquery.multi_select_box.js"></script>
<form id="frmGoods" name="frmGoods" action="./goods_ps.php" method="post">
    <?php
    if(empty($data['sno'])) {
        ?>
        <input type="hidden" name="mode" value="populate_register"/>
        <?php
    }else {
        ?>
        <input type="hidden" name="mode" value="populate_update"/>
        <input type="hidden" name="sno" value="<?= $data['sno'] ?>"/>
        <?php
    }
    ?>
    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./populate_list.php?page=<?= $req['page']?>');" />
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        기본 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="defaultConfig"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-defaultConfig" value="<?=$toggle['defaultConfig_'.$SessScmNo]?>">
    <div id="depth-toggle-line-defaultConfig" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-defaultConfig">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>인기상품 노출명</th>
                <td>
                    <label title="일반 상품명은 HTML Tag를 지원 하지 않습니다.">
                        <input type="text" name="displayNm" value="<?=$data['populateName']; ?>" class="form-control width-2xl js-maxlength" maxlength="30"/>
                    </label>
                </td>
            </tr>
            <tr>
                <th>노출상태</th>
                <td><label class="radio-inline"><input type="radio" name="displayFl" value="y" <?=gd_isset($checked['displayFl']['y']);?> />노출함</label>
                    <label class="radio-inline"><input type="radio" name="displayFl" value="n" <?=gd_isset($checked['displayFl']['n']);?> />노출안함</label></td>
            </tr>
            <tr>
                <th>순위타입 선택</th>
                <td>
                    <div><label class="radio-inline"><input type="radio" name="type" value="sell" <?=gd_isset($checked['type']['sell']);?> onclick="set_type();" />상품 판매 순위 - 판매금액</label>
                        <label class="radio-inline"><input type="radio" name="type" value="hit" <?=gd_isset($checked['type']['hit']);?> onclick="set_type();" />상품 클릭수 순위</label></div>
                    <div><label class="radio-inline"><input type="radio" name="type" value="sellCnt" <?=gd_isset($checked['type']['sellCnt']);?> onclick="set_type();" />상품 판매 순위 - 판매횟수</label>
                    <div><label class="radio-inline"><input type="radio" name="type" value="cart" <?=gd_isset($checked['type']['cart']);?> onclick="set_type();" />장바구니 담기 순위</label>
                        <label class="radio-inline"><input type="radio" name="type" value="wishlist" <?=gd_isset($checked['type']['wishlist']);?> onclick="set_type();" />찜리스트 담기 순위</label></div>
                    <div><label class="radio-inline"><input type="radio" name="type" value="review" <?=gd_isset($checked['type']['review']);?> onclick="set_type();" />상품 후기 작성 순위</label>
                        <label class="radio-inline"><input type="radio" name="type" value="score" <?=gd_isset($checked['type']['score']);?> onclick="set_type();" />상품 후기 평점 순위</label></div>
                </td>
            </tr>
            <tr>

            </tr>
            <tr>
                <th>노출순위 선택</th>
                <td>
                    <div class="form-inline">
                        1위 ~ <?=gd_select_box('rank', 'rank', array_combine($rank = range(1, 100), $rank), null, $data['rank'], null); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>갱신주기 선택</th>
                <td>
                    <div class="form-inline">
                        등록 시점을 기준으로 <?=gd_select_box('renewal', 'renewal', $renewal, null, $data['renewal'], null); ?>마다 순위를 갱신함
                    </div>
                </td>
            </tr>
            <tr>
                <th>수집기간 선택</th>
                <td>
                    <div class="form-inline">
                        갱신 시점을 기준으로 <?=gd_select_box('collect', 'collect', $collect, null, $data['collect'], null); ?>동안의 `<b><span class="msg-type">상품 판매 데이터</span></b>`를 수집하여 순위를 결정함
                    </div>
                </td>
            </tr>
            <tr>
                <th>인기상품수집범위선택</th>
                <td><label class="radio-inline"><input type="radio" name="range" value="all" <?=gd_isset($checked['range']['all']);?> onclick="set_range()" />전체상품</label>
                    <label class="radio-inline"><input type="radio" name="range" value="goods" <?=gd_isset($checked['range']['goods']);?> onclick="set_range()" />특정상품</label>
                    <label class="radio-inline"><input type="radio" name="range" value="category" <?=gd_isset($checked['range']['category']);?> onclick="set_range()" />특정카테고리</label>
                    <label class="radio-inline"><input type="radio" name="range" value="brand" <?=gd_isset($checked['range']['brand']);?> onclick="set_range()" />특정브랜드</label>
                </td>
            </tr>
            <tr class="range-all">
                <th>전체상품</th>
                <td><div class="notice-info">전체 상품에 대해서 데이터를 수집하게 됩니다.<br/>단, 예외조건에 해당되는 상품은 데이터를 수집하지 않습니다.</div></td>
            </tr>
            <tr class="range-goods">
                <th>특정상품<br/><button type="button" class="btn btn-sm btn-gray" onclick="layer_register('goods','collect');">상품선택</button></th>
                <td><div class="notice-info">선택된 상품에 대해서 데이터를 수집하게 됩니다.<br/>단, 예외조건에 해당되는 상품은 데이터를 수집하지 않습니다.</div>
                    <table id="collectGoodsTable" class="table table-cols" style="width:80%">
                        <thead>
                        <tr>
                            <th class="width5p">번호</th>
                            <th class="width10p">이미지</th>
                            <th>상품명</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:691px;">
                                    <table  class="table table-cols" style="padding:0px;margin:0px;">
                                        <colgroup><col class="width5p" /><col class="width10p" /><col /><col class="width8p" /></colgroup>
                                        <tbody id="collectGoods">
                                        <?php
                                        if (is_array($data['collectGoodsNo'])) {
                                            foreach ($data['collectGoodsNo'] as $key => $val) {
                                                echo '<tr id="idCollectGoods_'.$val['goodsNo'].'">'.chr(10);
                                                echo '<td class="width5p center"><span class="number">'.($key+1).'</span><input type="hidden" name="collectGoods[]" value="'.$val['goodsNo'].'" /></td>'.chr(10);
                                                echo '<td  class="width10p center">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</td>'.chr(10);
                                                echo '<td><a href="../goods/goods_register.php?goodsNo='.$val['goodsNo'].'" target="_blank">'.$val['goodsNm'].'</a></td>'.chr(10);
                                                echo '<td  class="width8p center"><input type="button" class="btn btn-gray btn-sm" onclick="field_remove(\'idCollectGoods_'.$val['goodsNo'].'\');" value="삭제" /></td>'.chr(10);
                                                echo '</tr>'.chr(10);
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot <?php if (is_array($data['collectGoodsNo']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#collectGoods').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <tr class="range-category">
                <th>특정카테고리<button type="button" class="btn btn-sm btn-gray" onclick="layer_register('category','collect')">카테고리선택</button></th>
                <td>
                    <div class="notice-info">선택된 카테고리 내 상품에 대해서 데이터를 수집하게 됩니다.<br/>단, 예외조건에 해당되는 상품은 데이터를 수집하지 않습니다.</div>
                    <table id="collectCategoryTable"class="table table-cols" style="width:80%">
                        <thead>
                        <tr>
                            <th class="width5p">번호</th>
                            <th>카테고리</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:450px;">
                                    <table  class="table table-cols" style="padding:0px;margin:0px;">
                                        <colgroup><col class="width5p" /><col /><col class="width8p" /></colgroup>
                                        <tbody id="collectCategory" >
                                        <?php
                                        if (is_array($data['collectCateCd'])) {
                                            foreach ($data['collectCateCd']['code'] as $key => $val) {
                                                echo '<tr id="idCollectCategory_'.$val.'">'.chr(10);
                                                echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="collectCategory[]" value="'.$val.'" /></td>'.chr(10);
                                                echo '<td>'.$data['collectCateCd']['name'][$key].'</td>'.chr(10);
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idCollectCategory_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                                echo '</tr>'.chr(10);
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot <?php if (is_array($data['collectCateCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#collectCategory').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <tr class="range-brand">
                <th>특정브랜드<button type="button" class="btn btn-sm btn-gray" onclick="layer_register('brand','collect')">브랜드선택</button></th>
                <td>
                    <div class="notice-info">선택된 브랜드 내 상품에 대해서 데이터를 수집하게 됩니다.<br/>단, 예외조건에 해당되는 상품은 데이터를 수집하지 않습니다.</div>
                    <table id="collectBrandTable"class="table table-cols" style="width:80%">
                        <thead>
                        <tr>
                            <th class="width5p">번호</th>
                            <th>브랜드</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:450px;">
                                    <table  class="table table-cols" style="padding:0px;margin:0px;">
                                        <colgroup><col class="width5p" /><col /><col class="width8p" /></colgroup>
                                        <tbody id="collectBrand" >
                                        <?php
                                        if (is_array($data['collectBrandCd'])) {
                                            foreach ($data['collectBrandCd']['code'] as $key => $val) {
                                                echo '<tr id="idCollectBrand_'.$val.'">'.chr(10);
                                                echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="collectBrand[]" value="'.$val.'" /></td>'.chr(10);
                                                echo '<td>'.$data['collectBrandCd']['name'][$key].'</td>'.chr(10);
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idCollectBrand_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                                echo '</tr>'.chr(10);
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot <?php if (is_array($data['collectBrandCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#collectBrand').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <tr>
                <th>인기상품수집예외설정</th>
                <td>
                    <span id="exceptItemGoods"><label class="checkbox-inline"><input type="checkbox" name="except[]" value="goods" <?=gd_isset($checked['except_goods']['y']);?> onclick="set_except()" />예외상품</label></span>
                    <span id="exceptItemCategory"><label class="checkbox-inline"><input type="checkbox" name="except[]" value="category" <?=gd_isset($checked['except_category']['y']);?> onclick="set_except()" />예외카테고리</label></span>
                    <span id="exceptItemBrand"><label class="checkbox-inline"><input type="checkbox" name="except[]" value="brand" <?=gd_isset($checked['except_brand']['y']);?> onclick="set_except()" />예외브랜드</label></span>
                </td>
            </tr>
            <tr class="except-goods">
                <th>예외상품<br /><button type="button" class="btn btn-sm btn-gray" onclick="layer_register('goods', 'except')">상품선택</button></th>
                <td>
                    <table id="exceptGoodsTable" class="table table-cols" style="width:80%">
                        <thead>
                        <tr>
                            <th class="width5p">번호</th>
                            <th class="width10p">이미지</th>
                            <th>상품명</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:691px;">
                                    <table  class="table table-cols" style="padding:0px;margin:0px;">
                                        <colgroup><col class="width5p" /><col class="width10p" /><col /><col class="width8p" /></colgroup>
                                        <tbody id="exceptGoods">
                                        <?php
                                        if (is_array($data['exceptGoodsNo'])) {
                                            foreach ($data['exceptGoodsNo'] as $key => $val) {
                                                echo '<tr id="idExceptGoods_'.$val['goodsNo'].'">'.chr(10);
                                                echo '<td class="width5p center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptGoods[]" value="'.$val['goodsNo'].'" /></td>'.chr(10);
                                                echo '<td  class="width10p center">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</td>'.chr(10);
                                                echo '<td><a href="../goods/goods_register.php?goodsNo='.$val['goodsNo'].'" target="_blank">'.$val['goodsNm'].'</a></td>'.chr(10);
                                                echo '<td  class="width8p center"><input type="button" class="btn btn-gray btn-sm" onclick="field_remove(\'idExceptGoods_'.$val['goodsNo'].'\');" value="삭제" /></td>'.chr(10);
                                                echo '</tr>'.chr(10);
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot <?php if (is_array($data['exceptGoodsNo']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptGoods').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <tr class="except-category">
                <th>예외카테고리<br /><button type="button" class="btn btn-sm btn-gray" onclick="layer_register('category', 'except')">카테고리선택</button></th>
                <td>
                    <table id="exceptCategoryTable"class="table table-cols" style="width:80%">
                        <thead>
                        <tr>
                            <th class="width5p">번호</th>
                            <th>카테고리</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:450px;">
                                    <table  class="table table-cols" style="padding:0px;margin:0px;">
                                        <colgroup><col class="width5p" /><col /><col class="width8p" /></colgroup>
                                        <tbody id="exceptCategory" >
                                        <?php
                                        if (is_array($data['exceptCateCd'])) {
                                            foreach ($data['exceptCateCd']['code'] as $key => $val) {
                                                echo '<tr id="idExceptCategory_'.$val.'">'.chr(10);
                                                echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptCategory[]" value="'.$val.'" /></td>'.chr(10);
                                                echo '<td>'.$data['exceptCateCd']['name'][$key].'</td>'.chr(10);
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptCategory_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                                echo '</tr>'.chr(10);
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot <?php if (is_array($data['exceptCateCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptCategory').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <tr class="except-brand">
                <th>예외브랜드<br /><button type="button" class="btn btn-sm btn-gray" onclick="layer_register('brand', 'except')">브랜드선택</button></th>
                <td>
                    <table id="exceptBrandTable"class="table table-cols" style="width:80%">
                        <thead>
                        <tr>
                            <th class="width5p">번호</th>
                            <th>브랜드</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="4" style="padding:0px;margin:0px;width:100%;">
                                <div style="overflow-x:hidden;overflow-y:auto;width:100%;max-height:450px;">
                                    <table  class="table table-cols" style="padding:0px;margin:0px;">
                                        <colgroup><col class="width5p" /><col /><col class="width8p" /></colgroup>
                                        <tbody id="exceptBrand" >
                                        <?php
                                        if (is_array($data['exceptBrandCd'])) {
                                            foreach ($data['exceptBrandCd']['code'] as $key => $val) {
                                                echo '<tr id="idExceptBrand_'.$val.'">'.chr(10);
                                                echo '<td  class="center"><span class="number">'.($key+1).'</span><input type="hidden" name="exceptBrand[]" value="'.$val.'" /></td>'.chr(10);
                                                echo '<td>'.$data['exceptBrandCd']['name'][$key].'</td>'.chr(10);
                                                echo '<td  class="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptBrand_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                                echo '</tr>'.chr(10);
                                            }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot <?php if (is_array($data['exceptBrandCd']) == false)  { echo "style='display:none'"; } ?>>
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptBrand').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <tr>
                <th>템플릿 형태</th>
                <td>
                    <div class="form-inline">
                        <div  class="pd10" style="float:left">
                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/open_type.png"><br/>
                            <label class="radio-inline mgt5"><input type="radio" name="template" value="01" <?=gd_isset($checked['template']['01']);?> />펼침형</label>
                        </div>
                        <div  class="pd10" style="float:left">
                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/hover_type.png"><br/>
                            <label class="radio-inline mgt5"><input type="radio" name="template" value="02" <?=gd_isset($checked['template']['02']);?> />롤오버형</label>
                        </div>
                        <div class="notice-info" style="clear:both">
                            펼침형 : 타이틀 부분과 순위부분이 펼쳐진 상태로 고정되어 노출됩니다.<br />
                            롤오버형 : 타이틀 부분은 순위가 자동으로 돌아가면서 보여지며 타이틀에 마우스오버시 순위부분이 펼쳐보여집니다.
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>


    <div class="table-title gd-help-manual">
        PC 인기상품 페이지 설정
        <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="frontConfig"><span>닫힘</span></button></span>
    </div>
    <input type="hidden" id="depth-toggle-hidden-frontConfig" value="<?=$toggle['frontConfig_'.$SessScmNo]?>">
    <div id="depth-toggle-line-frontConfig" class="depth-toggle-line display-none"></div>
    <div id="depth-toggle-layer-frontConfig">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>적용범위</th>
                <td colspan="3">
                    <label class="checkbox-inline"><input type="checkbox" name="same" value="y" <?=gd_isset($checked['same']['y']);?> onclick="mobile_same();" />모바일 쇼핑몰 동일 적용</label>
                </td>
            </tr>
            <tr>
                <th>사용여부</th>
                <td colspan="3">
                    <label class="radio-inline"><input type="radio" name="useFl" value="y" <?=gd_isset($checked['useFl']['y']);?> />사용함</label>
                    <label class="radio-inline"><input type="radio" name="useFl" value="n" <?=gd_isset($checked['useFl']['n']);?> />사용안함</label>
                    <div style="color: rgb(136, 136, 136); line-height: 20px; font-size: 11px;">
                        <?php
                        if(empty($data['sno'])) {
                            ?>ㆍ페이지 URL :  인기상품 노출 등록시 자동 생성됩니다.<?php
                        }else{
                            ?>ㆍ페이지 URL : <?=URI_HOME?>goods/populate.php?sno=<?=$data['sno']?> <a href="<?=URI_HOME?>goods/populate.php?sno=<?=$data['sno']?>" target="_blank" class="text-info btn-link">[PC쇼핑몰 화면보기]</a><br />
                            ㆍ페이지 URL : <?=URI_MOBILE?>goods/populate.php?sno=<?=$data['sno']?> <a href="<?=URI_MOBILE?>goods/populate.php?sno=<?=$data['sno']?>" target="_blank" class="text-info btn-link"> [모바일쇼핑몰 화면보기] </a><?php
                        }
                        ?>
                    </div>
                    <div class="notice-info">
                        인기상품의 상품 리스트페이지 사용여부를 설정할 수 있으며, "사용함" 설정시 [더보기] 버튼이 출력됩니다.<br />
                        버튼 클릭시 상품 리스트페이지로 이동되어 최대 100위까지의 인기상품을 확인할 수 있습니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th>이미지설정</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box('image', 'image', $image, null, $data['image'], null); ?>
                        이미지는 <a href="../policy/goods_images.php" target="_blank" class="text-info btn-link">[기본설정>상품 정책>상품 이미지 사이즈 설정]</a> 에서 관리할 수 있습니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th>품절상품 노출</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="soldOutFl" value="y" <?=gd_isset($checked['soldOutFl']['y']);?> onclick="set_soldOutDisplay('front')"/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="soldOutFl" value="n" <?=gd_isset($checked['soldOutFl']['n']);?> onclick="set_soldOutDisplay('front')"/>노출안함</label>
                </td>
                <th>품절상품 진열</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="soldOutDisplayFl" value="y" <?=gd_isset($checked['soldOutDisplayFl']['y']);?>/>정렬 순서대로 보여주기</label>
                    <label class="radio-inline"><input type="radio" name="soldOutDisplayFl" value="n" <?=gd_isset($checked['soldOutDisplayFl']['n']);?>/>리스트 끝으로 보내기</label>
                </td>
            </tr>
            <tr>
                <th>품절 아이콘 노출</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="soldOutIconFl" value="y" <?=gd_isset($checked['soldOutIconFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="soldOutIconFl" value="n" <?=gd_isset($checked['soldOutIconFl']['n']);?>/>노출안함</label>
                </td>
                <th>아이콘노출</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="iconFl" value="y" <?=gd_isset($checked['iconFl']['y']);?>/>노출함</label>
                    <label class="radio-inline"><input type="radio" name="iconFl" value="n" <?=gd_isset($checked['iconFl']['n']);?>/>노출안함</label>
                </td>
            </tr>
            <tr>
                <th>노출항목 설정</th>
                <td colspan="3">
                    <?php foreach($themeDisplayField as $k => $v) { ?>
                        <label class="checkbox-inline"  title=""><input type="checkbox" name="displayField[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['displayField']))) { echo "checked"; } ?>  >  <?=$v?></label>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th >할인적용가 설정</th>
                <td >
                    <?php foreach($themeGoodsDiscount as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="goodsDiscount[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['goodsDiscount']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                    <?php } ?>
                    <div class="notice-info">할인적용가 노출 시 적용할 할인금액을 설정합니다.</div>
                </td>
                <th >취소선 추가 설정</th>
                <td >
                    <?php foreach($themePriceStrike as $k => $v) { ?>
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="priceStrike[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['priceStrike']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
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
                        <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="displayAddField[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['displayAddField']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                    <?php } ?>
                    <div class="notice-info">[할인율] 체크 시 판매가 대비 할인율이 할인금액에 노출됩니다. (쿠폰가와 할인적용가에 적용됩니다.)</div>
                </td>
            </tr>
            <tr>
                <th>디스플레이 유형</th>
                <td colspan="3">
                    <?php foreach($themeDisplayType as $k => $v) { ?>
                        <div  class="pd10  display_ <?=$v['class']?> <?php if($v['mobile'] != 'y') { echo ' display_pc'; }?> " style="float:left"><span>
                                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/display_theme_<?=$k?>.jpg"><br/>
                                            <label class="radio-inline mgt5" title="" ><input type="radio" name="displayType" value="<?=$k?>" <?=gd_isset($checked['displayType'][$k]);?> /><?=$v['name']?></label></span></div>
                    <?php } ?>
                </td>
            </tr>

            <tr>
                <th>치환코드</th>
                <td colspan="3">
                    <?php if ($checkPathFront === true) { ?>
                        <div class="form-inline">
                            <?php
                            if(empty($data['sno'])) {
                                ?>ㆍ페이지 URL :  인기상품 노출 등록시 자동 생성됩니다.<?php
                            }else{
                                ?>{=includeWidget('proc/_populate.html', 'sno', '<?=$data['sno']?>')} <button type="button" title="복사" class="btn btn-white btn-sm js-clipboard" data-clipboard-text="{=includeWidget('proc/_populate.html', 'sno', '<?=$data['sno']?>')}">복사</button><?php
                            }
                            ?>
                        </div>
                        <div class="notice-danger">치환코드를 복사하여 PC와 모바일 각각의 “디자인" 메뉴에서 HTML소스에 삽입해야 인기상품 정보가 쇼핑몰에 노출됩니다.</div>
                    <?php } else { ?>
                        <div class="notice-danger">
                            "디자인" 메뉴의 사용 스킨과 작업 스킨에 인기상품 관련 페이지 파일(proc/_populate.html)이 존재하여야만 정상적으로 치환코드 노출 및 사용이 기능합니다.<br />
                            인기상품 노출 기능 사용을 위한 자세한 사항은 마이고도> 패치게시판에서 확인하시기 바랍니다.
                        </div>
                    <?php } ?>
                </td>
            </tr>

        </table>
    </div>

    <div class="mobile-config-area">
        <div class="table-title gd-help-manual">
            모바일 인기상품 페이지 설정
            <span class="depth-toggle"><button type="button" class="btn btn-sm btn-link bold depth-toggle-button" depth-name="mobileConfig"><span>닫힘</span></button></span>
        </div>
        <input type="hidden" id="depth-toggle-hidden-mobileConfig" value="<?=$toggle['mobileConfig_'.$SessScmNo]?>">
        <div id="depth-toggle-line-mobileConfig" class="depth-toggle-line display-none"></div>
        <div id="depth-toggle-layer-mobileConfig">
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col/>
                    <col class="width-md"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>사용여부</th>
                    <td colspan="3">
                        <label class="radio-inline"><input type="radio" name="mobileUseFl" value="y" <?=gd_isset($checked['mobileUseFl']['y']);?> />사용함</label>
                        <label class="radio-inline"><input type="radio" name="mobileUseFl" value="n" <?=gd_isset($checked['mobileUseFl']['n']);?> />사용안함</label>
                        <div class="notice-info">
                            인기상품의 상품 리스트페이지 사용여부를 설정할 수 있으며, "사용함" 설정시 [더보기] 버튼이 출력됩니다.<br />
                            버튼 클릭시 상품 리스트페이지로 이동되어 최대 100위까지의 인기상품을 확인할 수 있습니다.
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>이미지설정</th>
                    <td colspan="3">
                        <div class="form-inline">
                            <?php echo gd_select_box('mobileImage', 'mobileImage', $image, null, $data['mobileImage'], null); ?>
                            이미지는 <a href="../policy/goods_images.php" target="_blank" class="text-info btn-link">[기본설정>상품 정책>상품 이미지 사이즈 설정]</a> 에서 관리할 수 있습니다.
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>품절상품 노출</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="mobileSoldOutFl" value="y" <?=gd_isset($checked['mobileSoldOutFl']['y']);?> onclick="set_soldOutDisplay('mobile')"/>노출함</label>
                        <label class="radio-inline"><input type="radio" name="mobileSoldOutFl" value="n" <?=gd_isset($checked['mobileSoldOutFl']['n']);?> onclick="set_soldOutDisplay('mobile')"/>노출안함</label>
                    </td>
                    <th>품절상품 진열</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="mobileSoldOutDisplayFl" value="y" <?=gd_isset($checked['mobileSoldOutDisplayFl']['y']);?>/>정렬 순서대로 보여주기</label>
                        <label class="radio-inline"><input type="radio" name="mobileSoldOutDisplayFl" value="n" <?=gd_isset($checked['mobileSoldOutDisplayFl']['n']);?>/>리스트 끝으로 보내기</label>
                    </td>
                </tr>
                <tr>
                    <th>품절 아이콘 노출</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="mobileSoldOutIconFl" value="y" <?=gd_isset($checked['mobileSoldOutIconFl']['y']);?>/>노출함</label>
                        <label class="radio-inline"><input type="radio" name="mobileSoldOutIconFl" value="n" <?=gd_isset($checked['mobileSoldOutIconFl']['n']);?>/>노출안함</label>
                    </td>
                    <th>아이콘노출</th>
                    <td>
                        <label class="radio-inline"><input type="radio" name="mobileIconFl" value="y" <?=gd_isset($checked['mobileIconFl']['y']);?>/>노출함</label>
                        <label class="radio-inline"><input type="radio" name="mobileIconFl" value="n" <?=gd_isset($checked['mobileIconFl']['n']);?>/>노출안함</label>
                    </td>
                </tr>
                <tr>
                    <th>노출항목 설정</th>
                    <td colspan="3">
                        <?php foreach($themeDisplayField as $k => $v) { ?>
                            <label class="checkbox-inline"  title=""><input type="checkbox" name="mobileDisplayField[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['mobileDisplayField']))) { echo "checked"; } ?>  >  <?=$v?></label>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th >할인적용가 설정</th>
                    <td >
                        <?php foreach($themeGoodsDiscount as $k => $v) { ?>
                            <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="mobileGoodsDiscount[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['mobileGoodsDiscount']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                        <?php } ?>
                        <div class="notice-info">할인적용가 노출 시 적용할 할인금액을 설정합니다.</div>
                    </td>
                    <th >취소선 추가 설정</th>
                    <td >
                        <?php foreach($themePriceStrike as $k => $v) { ?>
                            <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="mobilePriceStrike[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['mobilePriceStrike']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
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
                            <span><label  class="checkbox-inline"  title=""><input type="checkbox" name="mobileDisplayAddField[]" value="<?=$k?>" <?php if(in_array($k,array_values($data['mobileDisplayAddField']))) { echo "checked"; } ?>  >  <?=$v?></label></span>
                        <?php } ?>
                        <div class="notice-info">[할인율] 체크 시 판매가 대비 할인율이 할인금액에 노출됩니다. (쿠폰가와 할인적용가에 적용됩니다.)</div>
                    </td>
                </tr>
                <tr>
                    <th>디스플레이 유형</th>
                    <td colspan="3">
                        <?php foreach($themeDisplayType as $k => $v) { ?>
                            <div  class="pd10  display_ <?=$v['class']?> <?php if($v['mobile'] != 'y') { echo ' display_pc'; }?> " style="float:left"><span>
                                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/display_theme_<?=$k?>.jpg"><br/>
                                            <label class="radio-inline mgt5" title="" ><input type="radio" name="mobileDisplayType" value="<?=$k?>" <?=gd_isset($checked['mobileDisplayType'][$k]);?> /><?=$v['name']?></label></span></div>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th>치환코드</th>
                    <td colspan="3">
                        <?php if ($checkPathMobile === true) { ?>
                            <div class="form-inline">
                                {=includeWidget('proc/_populate.html')} <button type="button" title="복사" class="btn btn-white btn-sm js-clipboard" data-clipboard-text="{=includeWidget('proc/_populate.html')}">복사</button>
                            </div>
                            <div class="notice-danger">치환코드를 복사하여 PC와 모바일 각각의 “디자인" 메뉴에서 HTML소스에 삽입해야 인기상품 정보가 쇼핑몰에 노출됩니다.</div>
                        <?php } else { ?>
                            <div class="form-inline">
                                <div class="notice-danger">
                                    "디자인" 메뉴의 사용 스킨과 작업 스킨에 인기상품 관련 페이지 파일(proc/_populate.html)이 존재하여야만 정상적으로 치환코드 노출 및 사용이 기능합니다.<br />
                                    인기상품 노출 기능 사용을 위한 자세한 사항은 마이고도> 패치게시판에서 확인하시기 바랍니다.
                                </div>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>
