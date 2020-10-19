<form id="frmKakao" name="frmKakao" action="settle_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="kakao_config" />
    <input type="hidden" name="pgName" value="kakaopay" />
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>

        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title gd-help-manual">카카오 페이 연동 설정</div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>카카오 페이 사용 설정</th>
            <td>
                <div>
                    <label class="radio-inline">
                        <input type="radio" name="testYn" value="Y" <?=$checked['testYn']['Y'];?> <?=$radioDisabled;?> /> 테스트하기
                    </label>
                    &nbsp;
                    <label class="radio-inline">
                        <input type="radio" name="testYn" value="N" <?=$checked['testYn']['N'];?> <?=$radioDisabled;?> /> 실제 사용하기
                    </label>
                </div>
                <?php if ($radioDisabled === true) { ?>
                <input type="hidden" name="testYn" value="Y"/>
                <p class="notice-info">‘테스트하기’를 선택하면 결제버튼이 관리자 로그인 시에만 보여지며, 쇼핑몰에서 결제 시 구매 과정 및 실제 결제는 동일하게 처리됩니다.</p>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>카카오 페이 가맹점 코드</th>
            <td>
                <input type="hidden" name="pgId" value="<?= $data['pgId'];?>"/>
                <?php if ($radioDisabled == '') { ?>
                    <span class="text-blue bold"><?php echo $data['pgId']; ?></span> <span class="text-blue">(자동 설정 완료)</span>
                <?php } else { ?>
                    <div class="notice-info notice-danger">카카오 페이 신청 전 또는 승인대기 상태입니다. <a href="https://www.godo.co.kr/echost/power/add/payment/easypg-intro.gd" target="_blank" class="btn btn-gray btn-sm">카카오 페이 신청</a></div>
                    <div class="notice-info">카카오 페이 신청 전인 경우 먼저 서비스를 신청하세요.</div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>카카오 페이 이용 영역</th>
            <td>
                <label class="radio-inline"><input type="radio" name="useYn" value="all" class="payco_borderZ" <?=$checked['useYn']['all'];?> <?=$radioDisabled;?> /> PC+모바일 &nbsp;</label>
                <label class="radio-inline"><input type="radio" name="useYn" value="pc" class="payco_borderZ" <?=$checked['useYn']['pc'];?> <?=$radioDisabled;?> /> PC 쇼핑몰 &nbsp;</label>
                <label class="radio-inline"><input type="radio" name="useYn" value="mobile" class="payco_borderZ" <?=$checked['useYn']['mobile'];?> <?=$radioDisabled;?> /> 모바일 쇼핑몰</label>
                <p class="notice-info">
                    쇼핑몰에서 카카오 페이 이용 영역을 선택하세요.
                </p>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">카카오 페이 예외상품 설정</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>예외 조건</th>
            <td>
                <span id="presentFlExcept_goods"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="goods" onclick="presentExcept_conf(this.value)">예외상품</label></span>
                <span id="presentFlExcept_category"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="category" onclick="presentExcept_conf(this.value)">예외 카테고리</label></span>
                <span id="presentFlExcept_brand"><label class="checkbox-inline"><input type="checkbox" name="presentExceptFl[]" value="brand" onclick="presentExcept_conf(this.value)">예외 브랜드</label></span>
            </td>
        </tr>
        <tr id="presentFlExcept_goods_tbl" style="display:none">
            <th>예외 상품
                <div>
                    <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('goods');">상품 선택</button>
                </div>
            </th>
            <td>

                <table id="exceptGoodsTable" class="table table-cols" style="width:80%">
                    <thead <?php if (is_array($data['exceptGoodsNo']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <th class="width5p">번호</th>
                        <th class="width10p">이미지</th>
                        <th>상품명</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="exceptGoods">
                    <?php
                    if (is_array($data['exceptGoodsNo'])) {
                        foreach ($data['exceptGoodsNo'] as $key => $val) {
                            echo '<tr id="idExceptGoods_' . $val['goodsNo'] . '">' . chr(10);
                            echo '<td>' . ($key + 1) . '<input type="hidden" name="exceptGoods[]" value="' . $val['goodsNo'] . '" /></td>' . chr(10);
                            echo '<td>' . gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank') . '</td>' . chr(10);
                            echo '<td><a href="../goods/goods_register.php?goodsNo=' . $val['goodsNo'] . '" target="_blank">' . $val['goodsNm'] . '</a></td>' . chr(10);
                            echo '<td><input type="button" class="btn btn-gray btn-sm" onclick="field_remove(\'idExceptGoods_' . $val['goodsNo'] . '\');" value="삭제" /></td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot <?php if (is_array($data['exceptGoodsNo']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptGoods').html('');"></td>
                    </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        <tr id="presentFlExcept_category_tbl" style="display:none">
            <th>예외 카테고리
                <div>
                    <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('category');">카테고리 선택</button>
                </div>
            </th>
            <td>
                <table id="exceptCategoryTable" class="table table-cols" style="width:80%">
                    <thead <?php if (is_array($data['exceptCateCd']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <th class="width5p">번호</th>
                        <th>카테고리</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="exceptCategory">
                    <?php
                    if (is_array($data['exceptCateCd'])) {
                        foreach ($data['exceptCateCd']['code'] as $key => $val) {
                            echo '<tr id="idExceptCategory_' . $val . '">' . chr(10);
                            echo '<td>' . ($key + 1) . '<input type="hidden" name="exceptCategory[]" value="' . $val . '" /></td>' . chr(10);
                            echo '<td>' . $data['exceptCateCd']['name'][$key] . '</td>' . chr(10);
                            echo '<td><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExceptCategory_' . $val . '\');" value="삭제" /></td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot <?php if (is_array($data['exceptCateCd']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#exceptCategory').html('');"></td>
                    </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        <tr id="presentFlExcept_brand_tbl" style="display:none">
            <th>예외 브랜드
                <div>
                    <button type="button" class="btn btn-xs btn-gray" onclick="layer_register('brand');">브랜드 선택</button>
                </div>
            </th>
            <td>
                <table id="exceptBrandTable" class="table table-cols mgt20" style="width:80%">
                    <thead <?php if (is_array($data['exceptBrandCd']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <th class="width5p">번호</th>
                        <th>브랜드</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="exceptBrand">
                    <?php
                    if (is_array($data['exceptBrandCd'])) {
                        foreach ($data['exceptBrandCd']['code'] as $key => $val) {
                            echo '<tr id="idExceptBrand_' . $val . '">' . chr(10);
                            echo '<td>' . ($key + 1) . '<input type="hidden" name="exceptBrand[]" value="' . $val . '" /></td>' . chr(10);
                            echo '<td>' . $data['exceptBrandCd']['name'][$key] . '</td>' . chr(10);
                            echo '<td><button type="button" class="btn btn-gray btn-xs"  onclick="field_remove(\'idExceptBrand_' . $val . '\');">삭제</button>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot <?php if (is_array($data['exceptBrandCd']) == false) {
                        echo "style='display:none'";
                    } ?>>
                    <tr>
                        <td colspan="4"><input type="button" value="전체삭제" class="btn btn-default btn-xs btn-gray" onclick="$('#exceptBrand').html('');">
                        </td>
                    </tr>
                    </tfoot>
                </table>

            </td>

        </tr>
    </table>

</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmKakao').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
            },
            messages: {
            }
        });
    });

    <?php if (is_array($data['exceptGoodsNo'])) { ?> $('input[name="presentExceptFl[]"][value=goods]').click();<?php  } ?>
    <?php if (is_array($data['exceptCateCd'])) { ?> $('input[name="presentExceptFl[]"][value=category]').click();<?php  } ?>
    <?php if (is_array($data['exceptBrandCd'])) { ?> $('input[name="presentExceptFl[]"][value=brand]').click();<?php  } ?>

    /**
     * 예외 조건 출력
     * @param string thisValue
    */
    function presentExcept_conf(thisValue) {

        if ($('#presentFlExcept_' + thisValue + "_tbl").is(':hidden')) $('#presentFlExcept_' + thisValue + "_tbl").show();
        else  $('#presentFlExcept_' + thisValue + "_tbl").hide();

    }

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string typeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_register(typeStr) {
        var layerFormID = 'addPresentForm';

        typeStrId = typeStr.substr(0, 1).toUpperCase() + typeStr.substr(1);

        var parentFormID = 'except' + typeStrId;
        var dataFormID = 'idExcept' + typeStrId;
        var dataInputNm = 'except' + typeStrId;
        var layerTitle = '카카오 페이 예외 조건 - ';

        // 레이어 창
        if (typeStr == 'goods') {
            var layerTitle = layerTitle + '상품';
            var mode = 'simple';

            $("#" + parentFormID + "Table thead").show();
            $("#" + parentFormID + "Table tfoot").show();
        }
        if (typeStr == 'category') {
            var layerTitle = layerTitle + '카테고리';
            var mode = 'simple';

            $("#" + parentFormID + "Table thead").show();
            $("#" + parentFormID + "Table tfoot").show();
        }
        if (typeStr == 'brand') {
            var layerTitle = layerTitle + '브랜드';
            var mode = 'simple';

            $("#" + parentFormID + "Table thead").show();
            $("#" + parentFormID + "Table tfoot").show();
        }

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
            "fl": 'kakao'
        };

        layer_add_info(typeStr, addParam);

    }

    /**
     * 출력 여부1
     *
     * @param string arrayID 해당 ID
     * @param string modeStr 출력 여부 (show or hide)
     */
    function display_toggle(thisID, modeStr) {
        if (modeStr == 'show') {
            $('#' + thisID).show();
        } else if (modeStr == 'hide') {
            $('#' + thisID).hide();
        }
    }
    //-->
</script>
