<form id="frmCommonContent" name="frmCommonContent" action="common_content_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="regist" />
    <input type="hidden" name="sno" value="<?=$data['sno'];?>" />

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./common_content_list.php');" />
            <input type="submit" value="저장" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title ">
        기본정보
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md" />
            <col>
            <col class="width-md" />
            <col>
        </colgroup>
        <tr>
            <th>공통정보 제목</th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="commonTitle" value="<?=$data['commonTitle'];?>" maxlength="60" class="form-control js-maxlength width-2xl" />
                </div>
            </td>
        </tr>
        <tr>
            <th>노출기간</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="commonStatusFl" value="n" <?=$checked['commonStatusFl']['n']; ?>>제한없음</label>
                    <label class="radio-inline"><input type="radio" name="commonStatusFl" value="y" <?=$checked['commonStatusFl']['y']; ?>>시작일/종료일</label>
                    <div class="input-group js-datetimepicker">
                        <input type="text" class="form-control width-md" name="commonStartDt" value="<?=$data['commonStartDt'];?>" onclick="$('input[name=\'commonStatusFl\']').eq(1).prop('checked',true);"  >
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datetimepicker">
                        <input type="text" class="form-control width-md" name="commonEndDt"  value="<?=$data['commonEndDt'];?>" onclick="$('input[name=\'commonStatusFl\']').eq(1).prop('checked',true);">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                </div>
            </td>
            <th>노출상태</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="commonUseFl" value="y" <?=$checked['commonUseFl']['y']; ?>>노출함</label>
                    <label class="radio-inline"><input type="radio" name="commonUseFl" value="n" <?=$checked['commonUseFl']['n']; ?>>노출안함</label>
                </div>
            </td>
        </tr>
    </table>

    <div class="table-title ">
        상품 조건 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md" />
            <col>
        </colgroup>
        <tr>
            <th>노출 상품 선택</th>
            <td>
                <div class="form-inline">
                    <?php foreach ($targetFl as $key => $value) { ?>
                        <label class="radio-inline"><input type="radio" name="commonTargetFl" value="<?=$key; ?>" <?=$checked['commonTargetFl'][$key]; ?>><?=$value; ?></label>
                    <?php } ?>
                </div>
            </td>
        </tr>
    </table>

    <div class="common-target-all <?php if (empty($data['commonTargetFl']) === false && $data['commonTargetFl'] != 'all') {echo 'display-none';} ?>">
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th>전체 상품</th>
                <td>
                    <div class="notice-info">전체 상품의 쇼핑몰 상품상세정보 상단 영역에 공통정보를 노출하게 됩니다.<br />단, 예외조건에 해당되는 상품은 노출되지 않습니다.</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="common-target-goods <?php if ($data['commonTargetFl'] != 'goods') {echo 'display-none';} ?>">
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th>
                    특정 상품
                    <div><input type="button" value="상품 선택" onclick="layer_register('goods');"  class="btn btn-sm btn-gray"/></div>
                </th>
                <td>
                    <div class="notice-info">선택된 상품에 대해서 쇼핑몰 상품상세정보 상단 영역에 공통정보를 노출하게 됩니다.<br />단, 예외조건에 해당되는 상품은 노출되지 않습니다.</div>
                    <table id="commonGoodsTable"class="table table-cols" style="width:80%">
                        <thead class="<?php if ($data['commonTargetFl'] != 'goods') {echo 'display-none';}?>">
                        <tr>
                            <th class="width5p">번호</th>
                            <th class="width10p">이미지</th>
                            <th>상품명</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody id="commonGoods" >
                        <?php
                        if ($data['commonTargetFl'] == 'goods' && is_array($data['commonCd'])) {
                            foreach ($data['commonCd'] as $key => $val) {
                                echo '<tr id="idGoods_'.$val['goodsNo'].'">'.chr(10);
                                echo '<td align="center"><span class="number">'.($key+1).'</span><input type="hidden" name="commonGoods[]" value="'.$val['goodsNo'].'" /></td>'.chr(10);
                                echo '<td align="center">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</td>'.chr(10);
                                echo '<td><a href="../goods/goods_register.php?goodsNo='.$val['goodsNo'].'" target="_blank">'.$val['goodsNm'].'</a></td>'.chr(10);
                                echo '<td align="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idGoods_'.$val['goodsNo'].'\');" value="삭제" /></td>'.chr(10);
                                echo '</tr>'.chr(10);
                            }
                        }
                        ?>
                        </tbody>
                        <tfoot class="<?php if ($data['commonTargetFl'] != 'goods') {echo 'display-none';}?>">
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#commonGoods').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div class="common-target-category <?php if ($data['commonTargetFl'] != 'category') {echo 'display-none';} ?>">
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th>
                    특정 카테고리
                    <div><input type="button" value="카테고리 선택" onclick="layer_register('category');"  class="btn btn-sm btn-gray"/></div>
                </th>
                <td>
                    <div class="notice-info">선택된 카테고리내 상품의 쇼핑몰 상품상세정보 상단 영역에 공통정보를 노출하게 됩니다.<br />단, 예외조건에 해당되는 상품은 노출되지 않습니다.</div>
                    <table id="commonCategoryTable"class="table table-cols" style="width:80%">
                        <thead class="<?php if ($data['commonTargetFl'] != 'category') {echo 'display-none';}?>">
                        <tr>
                            <th class="width5p">번호</th>
                            <th>카테고리</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody id="commonCategory" >
                        <?php
                        if ($data['commonTargetFl'] == 'category' && is_array($data['commonCd'])) {
                            foreach ($data['commonCd']['code'] as $key => $val) {
                                echo '<tr id="idCategory_'.$val.'">'.chr(10);
                                echo '<td align="center"><span class="number">'.($key+1).'</span><input type="hidden" name="commonCategory[]" value="'.$val.'" /></td>'.chr(10);
                                echo '<td>'.$data['commonCd']['name'][$key].'</td>'.chr(10);
                                echo '<td align="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idCategory_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                echo '</tr>'.chr(10);
                            }
                        }
                        ?>
                        </tbody>
                        <tfoot class="<?php if ($data['commonTargetFl'] != 'category') {echo 'display-none';}?>">
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#commonCategory').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div class="common-target-brand <?php if ($data['commonTargetFl'] != 'brand') {echo 'display-none';} ?>">
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th>
                    특정 브랜드
                    <div><input type="button" value="브랜드 선택" onclick="layer_register('brand');"  class="btn btn-sm btn-gray"/></div>
                </th>
                <td>
                    <div class="notice-info">선택된 브랜드내 상품의 쇼핑몰 상품상세정보 상단 영역에 공통정보를 노출하게 됩니다.<br />단, 예외조건에 해당되는 상품은 노출되지 않습니다.</div>
                    <table id="commonBrandTable"class="table table-cols" style="width:80%">
                        <thead class="<?php if ($data['commonTargetFl'] != 'brand') {echo 'display-none';}?>">
                        <tr>
                            <th class="width5p">번호</th>
                            <th>브랜드</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody id="commonBrand" >
                        <?php
                        if ($data['commonTargetFl'] == 'brand' && is_array($data['commonCd'])) {
                            foreach ($data['commonCd']['code'] as $key => $val) {
                                echo '<tr id="idBrand_'.$val.'">'.chr(10);
                                echo '<td align="center"><span class="number">'.($key+1).'</span><input type="hidden" name="commonBrand[]" value="'.$val.'" /></td>'.chr(10);
                                echo '<td>'.$data['commonCd']['name'][$key].'</td>'.chr(10);
                                echo '<td align="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idBrand_'.$val.'\');" value="삭제" /></td>'.chr(10);
                                echo '</tr>'.chr(10);
                            }
                        }
                        ?>
                        </tbody>
                        <tfoot class="<?php if ($data['commonTargetFl'] != 'brand') {echo 'display-none';}?>">
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#commonBrand').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <div class="common-target-scm <?php if ($data['commonTargetFl'] != 'scm') {echo 'display-none';} ?>">
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th>
                    특정 공급사
                    <div><input type="button" value="상품 선택" onclick="layer_register('scm');"  class="btn btn-sm btn-gray"/></div>
                </th>
                <td>
                    <div class="notice-info">선택된 공급사내 상품의 쇼핑몰 상품상세정보 상단 영역에 공통정보를 노출하게 됩니다.<br />단, 예외조건에 해당되는 상품은 노출되지 않습니다.</div>
                    <table id="commonScmTable"class="table table-cols" style="width:80%">
                        <thead class="<?php if ($data['commonTargetFl'] != 'scm') {echo 'display-none';}?>">
                        <tr>
                            <th class="width5p">번호</th>
                            <th>공급사</th>
                            <th class="width8p">삭제</th>
                        </tr>
                        </thead>
                        <tbody id="commonScm" >
                        <?php
                        if ($data['commonTargetFl'] == 'scm' && is_array($data['commonCd'])) {
                            foreach ($data['commonCd'] as $key => $val) {
                                echo '<tr id="idScm_'.$val['scmNo'].'">'.chr(10);
                                echo '<td align="center"><span class="number">'.($key+1).'</span><input type="hidden" name="commonScm[]" value="'.$val['scmNo'].'" /></td>'.chr(10);
                                echo '<td>'.$val['companyNm'].'</td>'.chr(10);
                                echo '<td align="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idScm_'.$val['scmNo'].'\');" value="삭제" /></td>'.chr(10);
                                echo '</tr>'.chr(10);
                            }
                        }
                        ?>
                        </tbody>
                        <tfoot class="<?php if ($data['commonTargetFl'] != 'scm') {echo 'display-none';}?>">
                        <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#commonScm').html('');"></td></tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <div class="table-title ">
        예외 상품 조건 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md" />
            <col>
        </colgroup>
        <tr>
            <th>노출 예외 상품 선택</th>
            <td>
                <div class="form-inline">
                    <?php foreach ($exTargetFl as $key => $value) { ?>
                        <label class="checkbox-inline <?php if ($data['commonTargetFl'] == $key) {echo 'display-none';}?>"><input type="checkbox" name="commonExTargetFl[]" value="<?=$key; ?>" <?=$checked['commonExTargetFl'][$key]; ?>><?=$value; ?></label>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <tr class="common-ex-goods <?php if (is_array($data['commonExGoods']) === false) {echo 'display-none';} ?>">
            <th>
                예외 상품
                <div><input type="button" value="상품 선택" onclick="layer_register('goods', 'ex');"  class="btn btn-sm btn-gray"/></div>
            </th>
            <td>
                <table id="commonExGoodsTable"class="table table-cols" style="width:80%">
                    <thead class="<?php if (is_array($data['commonExGoods']) === false) {echo 'display-none';} ?>">
                    <tr>
                        <th class="width5p">번호</th>
                        <th class="width10p">이미지</th>
                        <th>상품명</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="commonExGoods" >
                    <?php
                    if (is_array($data['commonExGoods']) === true) {
                        foreach ($data['commonExGoods'] as $key => $val) {
                            echo '<tr id="idExGoods_'.$val['goodsNo'].'">'.chr(10);
                            echo '<td align="center"><span class="number">'.($key+1).'</span><input type="hidden" name="commonExGoods[]" value="'.$val['goodsNo'].'" /></td>'.chr(10);
                            echo '<td align="center">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</td>'.chr(10);
                            echo '<td><a href="../goods/goods_register.php?goodsNo='.$val['goodsNo'].'" target="_blank">'.$val['goodsNm'].'</a></td>'.chr(10);
                            echo '<td align="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExGoods_'.$val['goodsNo'].'\');" value="삭제" /></td>'.chr(10);
                            echo '</tr>'.chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot class="<?php if (is_array($data['commonExGoods']) === false) {echo 'display-none';} ?>">
                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#commonExGoods').html('');"></td></tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        <tr class="common-ex-category <?php if (is_array($data['commonExCategory']) === false) {echo 'display-none';} ?>">
            <th>
                예외 카테고리
                <div><input type="button" value="카테고리 선택" onclick="layer_register('category', 'ex');"  class="btn btn-sm btn-gray"/></div>
            </th>
            <td>
                <table id="commonExCategoryTable"class="table table-cols" style="width:80%">
                    <thead class="<?php if (is_array($data['commonExCategory']) === false) {echo 'display-none';} ?>">
                    <tr>
                        <th class="width5p">번호</th>
                        <th>카테고리</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="commonExCategory" >
                    <?php
                    if (is_array($data['commonExCategory']) === true) {
                        foreach ($data['commonExCategory']['code'] as $key => $val) {
                            echo '<tr id="idExCategory_'.$val.'">'.chr(10);
                            echo '<td align="center"><span class="number">'.($key+1).'</span><input type="hidden" name="commonExCategory[]" value="'.$val.'" /></td>'.chr(10);
                            echo '<td>'.$data['commonExCategory']['name'][$key].'</td>'.chr(10);
                            echo '<td align="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExCategory_'.$val.'\');" value="삭제" /></td>'.chr(10);
                            echo '</tr>'.chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot class="<?php if (is_array($data['commonExCategory']) === false) {echo 'display-none';} ?>">
                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#commonExCategory').html('');"></td></tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        <tr class="common-ex-brand <?php if (is_array($data['commonExBrand']) === false) {echo 'display-none';} ?>">
            <th>
                예외 브랜드
                <div><input type="button" value="브랜드 선택" onclick="layer_register('brand', 'ex');"  class="btn btn-sm btn-gray"/></div>
            </th>
            <td>
                <table id="commonExBrandTable"class="table table-cols" style="width:80%">
                    <thead class="<?php if (is_array($data['commonExBrand']) === false) {echo 'display-none';} ?>">
                    <tr>
                        <th class="width5p">번호</th>
                        <th>브랜드</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="commonExBrand" >
                    <?php
                    if (is_array($data['commonExBrand']) === true) {
                        foreach ($data['commonExBrand']['code'] as $key => $val) {
                            echo '<tr id="idExBrand_'.$val.'">'.chr(10);
                            echo '<td align="center"><span class="number">'.($key+1).'</span><input type="hidden" name="commonExBrand[]" value="'.$val.'" /></td>'.chr(10);
                            echo '<td>'.$data['commonExBrand']['name'][$key].'</td>'.chr(10);
                            echo '<td align="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExBrand_'.$val.'\');" value="삭제" /></td>'.chr(10);
                            echo '</tr>'.chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot class="<?php if (is_array($data['commonExBrand']) === false) {echo 'display-none';} ?>">
                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#commonExBrand').html('');"></td></tr>
                    </tfoot>
                </table>
            </td>
        </tr>
        <tr class="common-ex-scm <?php if (is_array($data['commonExScm']) === false) {echo 'display-none';} ?>">
            <th>
                예외 공급사
                <div><input type="button" value="공급사 선택" onclick="layer_register('scm', 'ex');"  class="btn btn-sm btn-gray"/></div>
            </th>
            <td>
                <table id="commonExScmTable"class="table table-cols" style="width:80%">
                    <thead class="<?php if (is_array($data['commonExScm']) === false) {echo 'display-none';} ?>">
                    <tr>
                        <th class="width5p">번호</th>
                        <th>공급사</th>
                        <th class="width8p">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="commonExScm" >
                    <?php
                    if (is_array($data['commonExScm']) === true) {
                        foreach ($data['commonExScm'] as $key => $val) {
                            echo '<tr id="idExScm_'.$val['scmNo'].'">'.chr(10);
                            echo '<td align="center"><span class="number">'.($key+1).'</span><input type="hidden" name="commonExScm[]" value="'.$val['scmNo'].'" /></td>'.chr(10);
                            echo '<td>'.$val['companyNm'].'</td>'.chr(10);
                            echo '<td align="center"><input type="button" class="btn btn-sm btn-gray" onclick="field_remove(\'idExScm_'.$val['scmNo'].'\');" value="삭제" /></td>'.chr(10);
                            echo '</tr>'.chr(10);
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot class="<?php if (is_array($data['commonExScm']) === false) {echo 'display-none';} ?>">
                    <tr><td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#commonExScm').html('');"></td></tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <div class="table-title ">
        상품상세 공통정보 설정
    </div>
    <div id="descriptionArea">
        <ul class="nav nav-tabs nav-tabs-sm">
            <li class="active display-inline" id="btnDescriptionShop">
                <a href="#textareaDescriptionShop">PC쇼핑몰 상세 공통정보</a></li>
            <li class="nav-none display-inline" id="btnDescriptionMobile">
                <a href="#textareaDescriptionMobile">모바일쇼핑몰 상세 공통정보</a></li>
            <li style="padding-left:10px;padding-top:5px"> <label class="checkbox-inline"><input type="checkbox" value="y"  <?=gd_isset($checked['commonHtmlContentSameFl']['y']); ?> name="commonHtmlContentSameFl" /> PC/모바일 동일사용</label></li>
        </ul>
        <div id="textareaDescriptionShop">
            <textarea name="commonHtmlContent" rows="3" style="width:100%; height:400px;" id="editor" class="form-control"><?=stripslashes($data['commonHtmlContent']); ?></textarea>
        </div>
        <div id="textareaDescriptionMobile">
            <textarea name="commonHtmlContentMobile" rows="3" style="width:100%; height:400px;" id="editor2" class="form-control"><?=stripslashes($data['commonHtmlContentMobile']); ?></textarea>
        </div>
    </div>
</form>

<script type="text/javascript" src="/admin/gd_share/script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="/admin/gd_share/script/smart/js/editorLoad.js" charset="utf-8"></script>
<script type="text/javascript">
    nhn.husky.EZCreator.createInIFrame({
        oAppRef: oEditors,
        elPlaceHolder: "editor2",
        sSkinURI: "/admin/gd_share/script/smart/SmartEditor2Skin.html",
        htParams: {
            bUseToolbar: true,				// 툴바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseVerticalResizer: true,		// 입력창 크기 조절바 사용 여부 (true:사용/ false:사용하지 않음)
            bUseModeChanger: true,			// 모드 탭(Editor | HTML | TEXT) 사용 여부 (true:사용/ false:사용하지 않음)
            //aAdditionalFontList : aAdditionalFontSet,		// 추가 글꼴 목록
            fOnBeforeUnload: function () {
                //alert("완료!");
            }
        }, //boolean
        fOnAppLoad: function () {
            //예제 코드
            //oEditors.getById["editor"].exec("PASTE_HTML", ["로딩이 완료된 후에 본문에 삽입되는 text입니다."]);
            $("#textareaDescriptionMobile").hide();
            toggleSelectionDisplay('goodsDetail');
        },
        fCreator: "createSEditor2"
    });

    $(document).ready(function () {
        $('#frmCommonContent').validate({
            submitHandler: function (form) {
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                oEditors.getById["editor2"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.

                if ($('input[name="commonStatusFl"]:checked').val() == 'y') {
                    if (!$('input[name="commonStartDt"]').val() || !$('input[name="commonEndDt"]').val() || $('input[name="commonStartDt"]').val() > $('input[name="commonEndDt"]').val()) {
                        alert('노출기간의 시작일/종료일을 확인해주세요.');
                        return false;
                    }
                }
                form.submit();
            },
            rules: {
                'commonTitle': 'required'
            },
            messages: {
                'commonTitle': {
                    required: '공통정보 제목을 입력해주세요'
                }
            }
        });

        $('input[name="commonTargetFl"]').click(function(){
            $('div[class^="common-target"]').hide();
            $('.common-target-' + this.value).show();

            $('input[name="commonExTargetFl[]"]').closest('label').show();
            $('input[name="commonExTargetFl[]"][value="' + this.value + '"]:checked').trigger('click');
            $('input[name="commonExTargetFl[]"][value="' + this.value + '"]').closest('label').prop('checked', false).hide();
        });

        $('input[name="commonExTargetFl[]"]').click(function(){
            $('input[name="commonExTargetFl[]"]').each(function(key){
                if (this.checked === true) {
                    $('.common-ex-' + this.value).show();
                } else {
                    $('.common-ex-' + this.value).hide();
                }
            });
        });

        $('#btnDescriptionShop, #btnDescriptionMobile').click(function () {

            if (this.id == 'btnDescriptionShop') {
                $('#btnDescriptionShop').addClass('active');
                $('#btnDescriptionMobile').removeClass('active');
                $("#textareaDescriptionShop").show();
                $("#textareaDescriptionMobile").hide();
            } else {
                if($("input[name='commonHtmlContentSameFl']").prop('checked') == false) {
                    $('#btnDescriptionShop').removeClass('active');
                    $('#btnDescriptionMobile').addClass('active');
                    $("#textareaDescriptionShop").hide();
                    $("#textareaDescriptionMobile").show();
                }
            }
            return false;
        });
    });

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string typeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_register(typeStr, modeStr,isDisabled)
    {
        var layerFormID		= 'commonContentForm';

        typeStrId =  typeStr.substr(0,1).toUpperCase() + typeStr.substr(1);

        if (typeof modeStr == 'undefined') {
            var parentFormID	= 'common'+typeStrId;
            var dataFormID		= 'id'+typeStrId;
            var dataInputNm		= 'common'+typeStrId;
            var layerTitle		= '노출 ';
        } else {
            var parentFormID	= 'commonEx'+typeStrId;
            var dataFormID		= 'idExcept'+typeStrId;
            var dataInputNm		= 'commonEx'+typeStrId;
            var layerTitle		= '예외 ';
        }

        // 레이어 창
        if (typeStr == 'goods') {
            var layerTitle		= layerTitle+' 상품';
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
        if (typeStr == 'scm') {
            var layerTitle = layerTitle+'공급사';
            var mode =  'check';

            $("#"+parentFormID+"Table thead").show();
            $("#"+parentFormID+"Table tfoot").show();
        }
        layerTitle += ' 선택';

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
            "childRow": $("#"+parentFormID + " tr").length
        };
        console.log(addParam);

        if(typeStr == 'goods'){
            addParam['scmFl'] = $('input[name="scmFl"]:checked').val();
            addParam['scmNo'] = $('input[name="scmNo"]').val();
        }

        if(typeStr == 'scm'){
            addParam['callFunc'] = 'set_scm_select';
        }


        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }

    function set_scm_select(data) {
        displayTemplate(data);
    }
</script>
