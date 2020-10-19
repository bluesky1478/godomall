<script type="text/javascript">
<!--
$(document).ready(function(){
    $("#frmTheme").validate({
        submitHandler: function (form) {
            form.target = 'ifrmProcess';
            form.submit();
            return false;
        },
        rules: {
        },
        messages: {
        }
    });

    add_data_sortable('goodsNoForm');        // 상품 이동 소트

    $('input[name=\'imageSize\']').number_only();
});

/**
 * 진열할 상품 선정 Ajax layer
 *
 * @param string typeStr 타입
 */
function layer_register(typeStr)
{
    var layerFormID        = 'addThemeForm';

    var parentFormID    = 'goodsNoForm';
    var dataFormID        = 'idGoodsNo';
    var dataInputNm        = 'goodsNo';
    var layerTitle        = '모바일샵 메인 테마에 진열할 상품 설정';

    layer_add_info(typeStr, layerFormID, parentFormID, dataFormID, dataInputNm, layerTitle, 'y');
}
//-->
</script>

<form id="frmTheme" name="frmTheme" action="mobile_ps.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="mode" value="<?php echo $data['mode'];?>" />
<input type="hidden" name="sno" value="<?php echo $data['sno'];?>" />
<input type="hidden" name="themeTopImgTmp" value="<?php echo gd_isset($data['themeTopImg']);?>" />
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location);?> <small>모바일샵 메인에 원하는 상품을 진열할 수 있습니다</small></h3>
        <input type="submit" value="저장" class="btn btn-red">
    </div>

    <table class="table table-cols">
    <tr>
<?php
    if (empty($theme) === false) {
        foreach ($theme as $key => $val) {
            $no    = $key % 2;
            if ($no == 0 && $key > 0) {
                echo '</tr><tr>';
            }
            if ($val['sno'] == $data['sno']) {
                $strClass    = 'theme_selected';
            } else {
                $strClass    = 'theme_unselect';
            }
            echo '        <td class="'.$strClass.'" id="pgTitle_'.$val['sno'].'"><span class="button"><a href="./mobile_goods_display.php?sno='.$val['sno'].'">'.$val['themeNm'].'</a></span></td>'.chr(10);
        }
        for ($i = ($no+1); $i < 2; $i++) {
            echo '        <td class="theme_unselect"></td>'.chr(10);
        }
    }
?>
    </tr>
    </table>

    <div>&nbsp;</div>

    <div class="table-title gd-help-manual">
        모바일샵 메인 테마 관리
    </div>
    <div>
        <table class="table table-cols">
        <colgroup><col class="width-md" /><col /></colgroup>
        <tr>
            <th>메인 테마명</th>
            <td><input type="text" name="themeNm" value="<?php echo $data['themeNm'];?>" class="form-control width60p" /></td>
        </tr>
        <tr>
            <th>사용상태</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="themeUseFl" value="y" <?php echo gd_isset($checked['themeUseFl']['y']);?>/>사용함</label>
                <label class="radio-inline"><input type="radio" name="themeUseFl" value="n" <?php echo gd_isset($checked['themeUseFl']['n']);?>/>사용안함</label>
            </td>
        </tr>
        <tr>
            <th>진열할 상품선정</th>
            <td>
                <div><span class="button small black"><input type="button" value="상품 선택하기" onclick="layer_register('goods');" /></span></div>
                <div id="goodsNoForm" class="width100p">
<?php
    if (is_array($data['goodsNo'])) {
        foreach ($data['goodsNo'] as $key => $val) {
            echo '<span id="idGoodsNo_'.$val['goodsNo'].'" class="pull-left">'.chr(10);
            echo '<input type="hidden" name="goodsNo[]" value="'.$val['goodsNo'].'" />'.chr(10);
            echo '<span class="outline">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</span>'.chr(10);
            echo '<span class="button gray small"><input type="button" onclick="field_remove(\'idGoodsNo_'.$val['goodsNo'].'\');" value="삭제" /></span>'.chr(10);
            echo '</span>'.chr(10);
        }
    }
?>
                </div>
            </td>
        </tr>
        <tr>
            <th>타이틀 이미지</th>
            <td>
                <div class="pull-left width70p">
                    <input type="file" name="themeTopImg" class="form-control" /><br />
                    <span class="notice-ref notice-sm">※ 기본사이즈 : 100px X 20px</span>
                </div>
                <div>
<?php
    if (empty($data['themeTopImg']) === false) {
        echo gd_html_image(UserFilePath::data('mobile', $data['themeTopImg'])->www(), '타이틀');
        echo '<label><input type="checkbox" name="imageDel[]" value="themeTopImg" /> 체크시 이미지 삭제</label>';
    }
?>
                </div>
            </td>
        </tr>
        </table>
    </div>

    <div class="table-title gd-help-manual">상품 영역 상세 설정 <span>메인 테마 리스트 영역을 상세히 설정합니다. </span></div>
    <div>
        <table class="table table-cols">
        <colgroup><col class="width-md" /><col class="width-xl"/><col class="width-md" /><col/></colgroup>
        <tr>
            <th>이미지 설정</th>
            <td class="input_area" colspan="3">
                <input type="hidden" name="imageCd" value="main" />
<?php
    echo '&quot;'.$confImage['main']['text'].'&quot;를 사용을 하며, 사이즈는 가로를 '.$data['imageSize'].' pixel 로 줄여 사용을 하게 됩니다.'
?>
            </td>
        </tr>
        <tr>
            <th>이미지 사이즈</th>
            <td class="input_area" colspan="3">
                <input type="text" name="imageSize" value="<?php echo $data['imageSize'];?>" class="input_int" />
                <span class="notice-ref notice-sm">※ 기본 사이즈는 가로 70pixel 입니다.</span>
            </td>
        </tr>
        <tr>
            <th>디스플레이 유형</th>
            <td class="input_area" colspan="3">
                <ul>
                    <li class="pull-left width-sm center">
                        <label>
                            <img src="<?php echo PATH_ADMIN_GD_SHARE?>/img/event/goodalign_style_01.gif" /><br />
                            <input type="radio" name="listType" value="gallery" <?php echo gd_isset($checked['listType']['gallery']);?> />
                        </label>
                    </li>
                    <li class="pull-left width-sm center">
                        <label>
                            <img src="<?php echo PATH_ADMIN_GD_SHARE?>/img/event/goodalign_style_02.gif" /><br />
                            <input type="radio" name="listType" value="list" <?php echo gd_isset($checked['listType']['list']);?> />
                        </label>
                    </li>
                </ul>
            </td>
        </tr>
        <tr>
            <th>품절상품 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="soldOutFl" value="y" <?php echo gd_isset($checked['soldOutFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="soldOutFl" value="n" <?php echo gd_isset($checked['soldOutFl']['n']);?>/>아니요</label>
            </td>
            <th>품절 아이콘 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="soldOutIconFl" value="y" <?php echo gd_isset($checked['soldOutIconFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="soldOutIconFl" value="n" <?php echo gd_isset($checked['soldOutIconFl']['n']);?>/>아니요</label>
            </td>
        </tr>
        <tr>
            <th>이미지 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="imageFl" value="y" <?php echo gd_isset($checked['imageFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="imageFl" value="n" <?php echo gd_isset($checked['imageFl']['n']);?>/>아니요</label>
            </td>
            <th>아이콘 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="iconFl" value="y" <?php echo gd_isset($checked['iconFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="iconFl" value="n" <?php echo gd_isset($checked['iconFl']['n']);?>/>아니요</label>
            </td>
        </tr>
        <tr>
            <th>상품명 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="goodsNmFl" value="y" <?php echo gd_isset($checked['goodsNmFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="goodsNmFl" value="n" <?php echo gd_isset($checked['goodsNmFl']['n']);?>/>아니요</label>
            </td>
            <th>짧은설명 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="shortDescFl" value="y" <?php echo gd_isset($checked['shortDescFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="shortDescFl" value="n" <?php echo gd_isset($checked['shortDescFl']['n']);?>/>아니요</label>
            </td>
        </tr>
        <tr>
            <th>가격 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="priceFl" value="y" <?php echo gd_isset($checked['priceFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="priceFl" value="n" <?php echo gd_isset($checked['priceFl']['n']);?>/>아니요</label>
            </td>
            <th>마일리지 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="mileageFl" value="y" <?php echo gd_isset($checked['mileageFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="mileageFl" value="n" <?php echo gd_isset($checked['mileageFl']['n']);?>/>아니요</label>
            </td>
        </tr>
        <tr>
            <th>브랜드 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="brandFl" value="y" <?php echo gd_isset($checked['brandFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="brandFl" value="n" <?php echo gd_isset($checked['brandFl']['n']);?>/>아니요</label>
            </td>
            <th>제조사 출력</th>
            <td class="font-eng">
                <label class="radio-inline"><input type="radio" name="makerFl" value="y" <?php echo gd_isset($checked['makerFl']['y']);?>/>예</label>
                <label class="radio-inline"><input type="radio" name="makerFl" value="n" <?php echo gd_isset($checked['makerFl']['n']);?>/>아니요</label>
            </td>
        </tr>
        </table>
    </div>

    <div class="text-center">
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>
</form>
<script type="text/javascript" src="<?php echo PATH_ADMIN_GD_SHARE;?>script/meditor/mini_editor.js"></script>
<script type="text/javascript">mini_editor();</script>
