<div class="table-title gd-help-manual">
    디자인 페이지 설정
</div>
<div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md" />
            <col />
        </colgroup>
        <?php if ($skinType === 'front') { ?>
        <tr>
            <th>측면디자인 영역 위치</th>
            <td>
                <div class="form-inline">
                    <?php foreach ($designInfo['sidefloat'] as $opt){ ?>
                    <label class="radio-inline"><input type="radio" name="outline_sidefloat" id="outline_sidefloat" onclick="DCMAPM.file_float(this)" value="<?php echo gd_isset($opt['value']);?>" <?php echo gd_isset($opt['checked']);?> float="<?php echo gd_isset($opt['float']);?>" /> <?php echo gd_isset($opt['text']);?></label>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th>배경 색상</th>
            <td>
                <div class="form-inline">
                    <label for="colorSelector1">전체 배경색상 &nbsp; &nbsp; </label>
                    <input type="text" class="color-selector form-control width-xs center " name="outbg_color" id="colorSelector1" value="<?php echo gd_isset($designInfo['file']['outbg_color']);?>" readonly maxlength="7"  />
                </div>
                <div class="form-inline mgt10">
                    <label for="outbg_img_up" >전체 배경이미지</label>
                    <input type="file" name="outbg_img_up" id="outbg_img_up" class="form-control width-2xl" />
                    <input type="hidden" name="outbg_img" value="<?php echo gd_isset($designInfo['file']['outbg_img']);?>" />
                    <?php if (empty($designInfo['file']['outbg_img']) === false) {?>
                    <input type="button" onclick="image_viewer('<?php echo UserFilePath::frontSkin(Globals::get('gSkin.frontSkinWork'), 'img', 'codi', $designInfo['file']['outbg_img'])->www();?>');" value="VIEW" class="btn btn-success btn-xs" />
                    <label><input type="checkbox" name="outbg_img_del" value="Y"><span class="text-red">삭제</span></label>
                    <?php }?>
                </div>
            </td>
        </tr>
    </table>
</div>
