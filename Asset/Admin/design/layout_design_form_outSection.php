<div class="table-title gd-help-manual">
    디자인 페이지 설정
</div>
<div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-xs"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">파일설명</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="text" value="<?php echo $designInfo['file']['text'];?>" maxlength="30" class="form-control js-maxlength width-2xl" />
                </div>
            </td>
        </tr>
        <?php if (dirname($getPageID) == 'outline/header') { ?>
        <tr>
            <th>배경이미지</th>
            <td>
                <div class="form-inline">
                    <input type="file" name="inbg_img_up" class="form-control width80p"/>
                    <input type="hidden" name="inbg_img" value="<?php echo gd_isset($designInfo['file']['inbg_img']); ?>"/>
                    <?php if (empty($designInfo['file']['inbg_img']) === false) { ?>
                        <input type="button" onclick="image_viewer('<?php echo UserFilePath::frontSkin(Globals::get('gSkin.frontSkinWork'), 'img', 'codi', $designInfo['file']['inbg_img'])->www(); ?>');" value="VIEW" class="btn btn-success btn-xs" />
                        <label><input type="checkbox" name="inbg_img_del" value="Y"><span class="text-red">삭제</span></label>
                    <?php } ?>
                </div>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
