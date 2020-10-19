<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $("#frmMenuLayer").validate({
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

        <?php if($data['category']['naviUse'] =='n') { ?>display_switch('category','hide');<?php } ?>
        <?php if($data['brand']['naviUse'] =='n') { ?>display_switch('brand','hide');<?php } ?>
    });
    //-->
</script>
<form id="frmMenuLayer" name="frmMenuLayer" action="./display_config_ps.php" method="post"
      enctype="multipart/form-data">
    <input type="hidden" name="mode" value="navi_register"/>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location);?> </h3>
        <div class="btn-group">
            <input type="submit"   value="저장" class="btn btn-red" />

        </div>
    </div>

    <div class="table-title gd-help-manual">
        하위브랜드 상품진열 설정
    </div>
    <table class="table table-cols" style="margin-bottom:0;">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">진열여부</th>
            <td>
                <label class="radio-inline"><input type="radio" name="brand[linkUse]" value="y"<?=gd_isset($checked['brand']['linkUse']['y']);?>>진열함</label>
                <label class="radio-inline"> <input type="radio"  name="brand[linkUse]" value="n" <?=gd_isset($checked['brand']['linkUse']['n']);?>>진열안함</label>
            </td>
        </tr>
    </table>
    <div class="notice-info mgb25">하위브랜드 상품진열을 "진열함"으로 설정하면, 현재 브랜드에 속한 모든 하위브랜드의 상품들이 함께 진열됩니다.</div>

    <div class="table-title gd-help-manual">
        상위카테고리 자동등록 설정
    </div>

    <table class="table table-cols" style="margin-bottom:0;">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require" >사용여부</th>
            <td  >
                <label class="radio-inline"><input type="radio" name="categoryAuto[autoUse]" value="y"<?=gd_isset($checked['categoryAuto']['autoUse']['y']);?> >사용함</label>
                <label class="radio-inline"> <input type="radio"  name="categoryAuto[autoUse]" value="n" <?=gd_isset($checked['categoryAuto']['autoUse']['n']);?> >사용안함</label>
            </td>
        </tr>
    </table>
    <div class="notice-info mgb25">상위카테고리 자동등록을 “사용함"으로 설정하면, 상품 엑셀 업로드 시 카테고리 코드에 기재된 카테고리에 속한 모든 상위카테고리가 상품에 자동 등록됩니다.</div>

    <div class="table-title gd-help-manual">
        네비게이션 영역 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="width-sm"/>
            <col/>
            <col width="100"/>
        </colgroup>
        <tr>
            <th class="require" rowspan="2" >사용여부</th>
            <td>카테고리</td>
            <td>
                <label class="radio-inline"><input type="radio" name="category[naviUse]" value="y"<?=gd_isset($checked['category']['naviUse']['y']);?>>사용함</label>
                <label class="radio-inline"> <input type="radio"  name="category[naviUse]" value="n" <?=gd_isset($checked['category']['naviUse']['n']);?>>사용안함</label>
            </td>
            <td rowspan="2"><img src="<?=PATH_ADMIN_GD_SHARE?>img/ex_03.png"></td>
        </tr>
        <tr>
            <td>브랜드</td>
            <td>
                <label class="radio-inline"><input type="radio" name="brand[naviUse]" value="y"<?=gd_isset($checked['brand']['naviUse']['y']);?>>사용함</label>
                <label class="radio-inline"> <input type="radio"  name="brand[naviUse]" value="n" <?=gd_isset($checked['brand']['naviUse']['n']);?>>사용안함 </label>
            </td>
        </tr>
        <tr>
            <th class="require" rowspan="2" >상품수 노출여부</th>
            <td>카테고리</td>
            <td>
                <label class="radio-inline"><input type="radio" name="category[naviCount]" value="y"<?=gd_isset($checked['category']['naviCount']['y']);?> >사용함</label>
                <label class="radio-inline"> <input type="radio"  name="category[naviCount]" value="n" <?=gd_isset($checked['category']['naviCount']['n']);?> >사용안함 </label>
            </td>
            <td rowspan="2"><img src="<?=PATH_ADMIN_GD_SHARE?>img/ex_01.png"></td>
        </tr>
        <tr>
            <td>브렌드</td>
            <td>
                <label class="radio-inline"><input type="radio" name="brand[naviCount]" value="y"<?=gd_isset($checked['brand']['naviCount']['y']);?> >사용함</label>
                <label class="radio-inline"> <input type="radio"  name="brand[naviCount]" value="n" <?=gd_isset($checked['brand']['naviCount']['n']);?> > 사용안함</label>
            </td>
        </tr>
    </table>
</form>
