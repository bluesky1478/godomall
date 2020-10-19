<form id="frmGoodsCategory" name="frmGoodsCategory" method="post">
    <input type="hidden" name="mode" value=""/>
    <input type="hidden" name="goodsNo" value=""/>
    <div class="popup-display-main-list">
        <div class="popup-page-header js-affix">
            <h3>상품 분류 관리</h3>
        </div>

        <input type="hidden" id="depth-toggle-hidden-categoryLink" value="<?=$toggle['categoryLink_'.$SessScmNo]?>">
        <div id="depth-toggle-line-categoryLink" class="depth-toggle-line display-none"></div>
        <div id="depth-toggle-layer-categoryLink" class="depth-check-toggle-layer">
            <div class="table-title gd-help-manual">
                카테고리 선택
            </div>
            <table class="table table-cols" style="margin: 0;">
                <colgroup>
                    <col class="width-lg">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <td style="width:700px;">
                        <div class="form-inline">
                            <?=$cate->getMultiCategoryBox('cateGoods', '', 'size="5" style="width:23%;height:100px;"'); ?>
                        </div>
                    </td>
                    <td class="border-left text-center" style="width:100px;">
                        <input type="button" value="선택" class="btn btn-2xl btn-black" id="btn_category_select">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="table-title gd-help-manual">
            선택된 카테고리
        </div>
        <input type="hidden" id="depth-toggle-hidden-category" value="<?=$toggle['category_'.$SessScmNo]?>">
        <div id="depth-toggle-line-category" class="depth-toggle-line display-none"></div>
        <div id="depth-toggle-layer-category" >
            <table class="table table-cols" style="margin:0;">
                <colgroup>
                    <col class="width-lg">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <td>
                        <p class="notice-info">
                            카테고리 등록 시 상위카테고리는 자동 등록되며, 등록된 카테고리에 상품이 노출됩니다.
                            <br/> 상품 노출을 원하지 않는 카테고리는 ‘삭제’버튼을 이용하여 삭제할 수 있습니다.<br/> 등록하신 카테고리들 중 체크된 카테고리가 대표 카테고리로 설정됩니다.
                        </p>
                        <table class="table table-rows table-fixed mgt10" id="cateGoodsInfo">
                            <thead <?php if (empty($data['link'])) {
                                echo "style='display:none'";
                            } ?>>
                            <tr>
                                <?php if ($gGlobal['isUse'] === true) { ?><th class="width12p">노출상점</th><?php } ?>
                                <th class="width10p">대표설정</th>
                                <th class="width62p">연결된 카테고리</th>
                                <th class="width20p">카테고리 코드</th>
                                <th class="width10p">연결해제</th>
                            </tr>
                            </thead>
                            <tbody <?php if (empty($data['link'])) {
                                echo "style='display:none'";
                            } ?>>
                            <?php
                            if (!empty($data['link'])) {
                                foreach ($data['link'] as $key => $val) {
                                    if ($val['cateLinkFl'] == 'y') {
                            ?>
                                        <tr id="cateGoodsInfo<?=$val['cateCd']; ?>">
                            <?php
                                    if ($gGlobal['isUse'] === true) {
                                        $flagData = $cate->getCategoryFlag($val['cateCd']);
                            ?>
                                        <td>
                            <?php
                                       foreach($flagData as $k1 => $v1) {
                            ?>
                                        <span class="js-popover flag flag-16 flag-<?= $k1?>" data-content="<?=$v1?>"></span>
                            <?php
                                       }
                            ?>
                                        </td>
                            <?php
                                    }
                                    if ($applyGoodsCopy === false) {
                            ?>
                                        <input type="hidden" name="link[sno][]" value="<?=$val['sno']; ?>" />
                            <?php
                                    }
                            ?>
                                        <input type="hidden" name="link[cateCd][]" value="<?=$val['cateCd']; ?>"/>
                                        <input type="hidden" name="link[cateLinkFl][]" value="<?=$val['cateLinkFl']; ?>"/>
                            <?php
                                    if ($applyGoodsCopy === false) {
                            ?>
                                        <input type="hidden" name="link[goodsSort][]" value="<?=$val['goodsSort']; ?>" />
                            <?php
                                    }
                            ?>
                                        <td class="center">
                                            <input type="radio" name="cateCd" value="<?=$val['cateCd']; ?>" <?=gd_isset($checked['cateCd'][$val['cateCd']]); ?> />
                                        </td>
                                        <td><?=$cate->getCategoryPosition($val['cateCd'],0,' &gt; ',false,false); ?></td>
                                        <td class="center"><?=$val['cateCd']; ?></td>
                                        <td class="center">
                                            <input type="button" onclick="field_remove('cateGoodsInfo<?=$val['cateCd']; ?>');" value="삭제" class="btn btn-sm btn-white btn-icon-minus"/>
                                        </td>
                                        </tr>
                            <?php
                                    }
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                        <p>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="goodsSortTop" value="y" /> <span class="bold">상단 고정진열 적용</span> (체크 시 선택된 모든 카테고리의 쇼핑몰 상품 페이지 최상단에 진열됩니다.)
                            </label>
                        </p>
                    </td>
                </tr>
            </table>

            <table class="table table-cols">
                <colgroup>
                    <col class="width-sm"/>
                    <col/>
                </colgroup>
                <tbody>
                <tr>
                    <th>처리방법 선택</th>
                    <td>
                        선택된 카테고리를 일괄로
                        <input type="radio" name="displayStatusChk" value="popup_category_change"/>변경
                        <input type="radio" name="displayStatusChk" value="popup_category_add">추가
                        <input type="radio" name="displayStatusChk" value="popup_category_delete">삭제
                        합니다.
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="text-center">
                <input type="button" class="btn btn-white js-check-close" value="닫기">
                <input type="button" class="btn btn-black js-check-save" value="적용">
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        //카테고리 선택
        $('#btn_category_select').click(function () {

            var cateGoods = '';
            var cateName = new Array();

            $("#cateGoodsInfo thead, #cateGoodsInfo tbody").show();

            for (var i = 0; i <= <?=DEFAULT_DEPTH_CATE;?>; i++) {
                if ($('#cateGoods' + i).val()) {
                    var cate = $('#cateGoods' + i + " option:selected");
                    cateName[i] = cate.text();
                    if ($("#cateGoodsInfo" + cate.val()).length == 0) {
                        addHtml = "<tr id='cateGoodsInfo" + cate.val() + "'>";
                    <?php
                        if ($gGlobal['isUse'] === true) {
                    ?>
                        var flagHtml = [];
                        var tmpFlag = (cate.data('flag')).split(',');
                        var tmpMallName = (cate.data('mall-name')).split(',');
                        for (var f = 0; f < tmpFlag.length; f++) {
                            flagHtml.push('<span class="js-popover flag flag-16 flag-' + tmpFlag[f] + '" data-content="' + tmpMallName[f] + '"></span>');
                        }
                        addHtml += "<td>" + flagHtml.join("&nbsp;") + "</td>";
                    <?php
                        }
                    ?>
                        addHtml += "<td class='center'><input type='hidden' name='link[cateCd][]' value='" + cate.val() + "'><input type='hidden' name='link[cateLinkFl][]' value='y' id='cateLink_" + cate.val() + "'><input type='radio' name='cateCd' value='" + cate.val() + "'></td>";
                        addHtml += "<td>" + (cateName.join(' &gt; ')).replace('&gt;', '') + "</td>";
                        addHtml += "<td class='center'>" + cate.val() + "</td>";
                        addHtml += "<td class='center'><input type='button' class='btn btn-sm btn-white btn-icon-minus' onclick='field_remove(\"cateGoodsInfo" + cate.val() + "\")' value='삭제'></td>";

                        $("#cateGoodsInfo tbody").append(addHtml);
                    }

                }
            }

            if ($('input[name="cateCd"]:checked').length == 0) {
                $('input[name="cateCd"]:first').prop('checked', true);
            }
        });

        $('.js-check-save').click(function () {
            frmSubmit();
        });

        $('.js-check-close').click(function () {
            close();
        });
    });

    function frmSubmit(){

        var goodsNo = $('input[name*="goodsNo"]:checked', opener.document).map(function(){
            return this.value;
        }).get().join(',');

        var chkStatus = $('input[name="displayStatusChk"]:checked').val();
        var chkSnoCnt = $('#cateGoodsInfo >tbody >tr').length;
        var chkStatusLength = $('input:radio[name="displayStatusChk"]').is(':checked');

        if(chkSnoCnt == 0 || chkStatusLength == false){
            alert('카테고리의 처리방법을 선택해 주세요.');
            return false;
        }

        $('input[name="goodsNo"]').attr('value',goodsNo);
        $('input[name="mode"]').attr('value',chkStatus);

        var params = jQuery("#frmGoodsCategory").serializeArray();

        $.ajax({
            method: "POST",
            url: "../goods/goods_ps.php",
            data : params,
            success : function(data){
                window.close();
                opener.parent.location.reload();
            },
            error: function (data){
            }
        });
    }
    //-->
</script>