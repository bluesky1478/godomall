<div class="table-title gd-help-manual" xmlns="http://www.w3.org/1999/html">
    디자인 페이지 설정

    <?php if($skinType === 'front'){?>
    <div style="float: right;"><button type="button" name="connectPageList" class="btn btn-white bold js-connect-page-list" data-skin="<?=$currentWorkSkin?>">연결 페이지 보기</button></div>
    <?php }?>
</div>
<div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md" />
            <col class="width40p"/>
            <col class="width-md" />
            <col />
        </colgroup>
        <tr>
            <th>파일설명</th>
            <td <?php if ($skinType !== 'front') { echo 'colspan="3"'; } ?>>
                <div class="form-inline">
                    <input type="text" name="text" value="<?php echo $designInfo['file']['text'];?>" maxlength="30" class="form-control js-maxlength width90p" />
                </div>
            </td>
            <?php if ($skinType === 'front') { ?>
            <th>측면디자인 영역 위치</th>
            <td>
                <div class="form-inline">
                    <?php foreach ($designInfo['sidefloat'] as $opt){ ?>
                    <label class="radio-inline"><input type="radio" name="outline_sidefloat" id="outline_sidefloat" onclick="DCMAPM.file_float(this)" value="<?php echo gd_isset($opt['value']);?>" <?php echo gd_isset($opt['checked']);?> float="<?php echo gd_isset($opt['float']);?>" /> <?php echo gd_isset($opt['text']);?></label>
                    <?php } ?>
                </div>
            </td>
            <?php } ?>
        </tr>
        <tr>
            <th>현재위치</th>
            <td colspan="3">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="current_page" value="y" <?php if (gd_isset($designInfo['file']['current_page'], 'n') === 'y') { echo 'checked="checked"'; }?> /> 출력</label>
                    <label class="radio-inline"><input type="radio" name="current_page" value="n" <?php if ($designInfo['file']['current_page'] === 'n') { echo 'checked="checked"'; }?> /> 미출력</label>
                </div>
            </td>
        </tr>
        <tr>
            <th>전체색상</th>
            <td>
                <div class="form-inline">
                    <label for="colorSelector1">전체 배경색상 &nbsp; &nbsp; </label>
                    <input type="text" name="outbg_color" id="colorSelector1" value="<?php echo gd_isset($designInfo['file']['outbg_color']);?>" readonly maxlength="7" class="form-control width-xs center color-selector" />
                </div>
                <div class="form-inline mgt10">
                    <label for="outbg_img_up" >전체 배경이미지</label>
                    <input type="file" name="outbg_img_up" id="outbg_img_up" class="form-control width-xl" />
                    <input type="hidden" name="outbg_img" value="<?php echo gd_isset($designInfo['file']['outbg_img']);?>" />
                    <?php if (empty($designInfo['file']['outbg_img']) === false) {
                        if( $skinType == 'front') {?>
                            <input type="button" onclick="image_viewer('<?php echo UserFilePath::frontSkin(Globals::get('gSkin.frontSkinWork'), 'img', 'codi', $designInfo['file']['outbg_img'])->www();?>');" value="VIEW" class="btn btn-success btn-xs" />
                    <?php } else {?>
                            <input type="button" onclick="image_viewer('<?php echo UserFilePath::mobileSkin(Globals::get('gSkin.mobileSkinWork'), 'img', 'codi', $designInfo['file']['outbg_img'])->www();?>');" value="VIEW" class="btn btn-success btn-xs" />
                        <?php }?>
                            <label><input type="checkbox" name="outbg_img_del" value="Y"><span class="text-red">삭제</span></label>
                    <?php }?>
                </div>
            </td>
            <th>본문색상</th>
            <td>
                <div class="form-inline">
                    <label for="colorSelector2">배경색상 &nbsp; &nbsp; </label>
                    <input type="text" name="inbg_color" id="colorSelector2" value="<?php echo gd_isset($designInfo['file']['inbg_color']);?>" readonly maxlength="7" class="form-control width-xs center color-selector" />
                </div>
                <div class="form-inline mgt10">
                    <label for="inbg_img_up" >배경이미지</label>
                    <input type="file" name="inbg_img_up" id="inbg_img_up" class="form-control width-xl" />
                    <input type="hidden" name="inbg_img" value="<?php echo gd_isset($designInfo['file']['inbg_img']);?>" />
                    <?php if (empty($designInfo['file']['inbg_img']) === false) {
                        if( $skinType == 'front'){ ?>
                            <input type="button" onclick="image_viewer('<?php echo UserFilePath::frontSkin(Globals::get('gSkin.frontSkinWork'), 'img', 'codi', $designInfo['file']['inbg_img'])->www();?>');" value="VIEW" class="btn btn-success btn-xs" />
                    <?php } else { ?>
                            <input type="button" onclick="image_viewer('<?php echo UserFilePath::mobileSkin(Globals::get('gSkin.mobileSkinWork'), 'img', 'codi', $designInfo['file']['inbg_img'])->www();?>');" value="VIEW" class="btn btn-success btn-xs" />
                        <?php }?>
                            <label><input type="checkbox" name="inbg_img_del" value="Y"><span class="text-red">삭제</span></label>
                    <?php }?>
                </div>
            </td>
        </tr>
        <?php if($connectFl === true && $skinType === 'front'){?>
        <tr>
            <th>모바일 페이지 연결</th>
            <td colspan="3">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="connectFl" value="y" onclick="connectToggle('connectFl', 'js-connect-page');" <?php if (gd_isset($designInfo['file']['connectFl'], 'n') === 'y') { echo 'checked="checked"'; }?> /> 연결</label>
                    <label class="radio-inline"><input type="radio" name="connectFl" value="n" onclick="connectToggle('connectFl', 'js-connect-page');" <?php if ($designInfo['file']['connectFl'] === 'n') { echo 'checked="checked"'; }?> /> 미연결</label>
                </div>
                <div class="form-inline pdt5 js-connect-page display-none">
                    모바일 연결 페이지 <input type="text" class="form-control width-2xl" name="connectPage" value="<?=$designInfo['file']['connectPage']?>" placeholder="ex)/main/html.php?htmid=intro/test.html" />
                </div>
                <p class="notice-info">
                    모바일 연결 페이지는 [모바일샵 > 모바일샵 디자인 관리 > 디자인 페이지 수정] 화면에서 [화면보기] 버튼을 클릭하여 출력되는<br/>
                    모바일 쇼핑몰 미리보기 페이지의 도메인을 제외한 URL정보를 입력하시기 바랍니다. <br/>
                    기준몰 입력 예시 : http:/nhngodo.godomall.com<strong style="color:#fa2828;">/main/html.php?htmid=intro/test.html</strong><br/>
                    해외몰 입력 예시 : http:/nhngodo.godomall.com/us<strong style="color:#fa2828;">/main/html.php?htmid=intro/test.html</strong><br/>
                </p>
                <p class="notice-info">
                    모바일 환경에서 해당 페이지에 접근 시 출력할 모바일 쇼핑몰 페이지를 등록합니다.<br/>
                    연결된 모바일 페이지가 정상적으로 출력되는지 모바일 환경에서 확인하시기 바랍니다.
                </p>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        connectToggle('connectFl', 'js-connect-page');

        // 모바일 페이지 파일명 한글 입력 불가
        $('input[name="connectPage"]').on('keyup', function () {
            var value = $(this).val().replace(/[^\da-zA-Z\s\-/!@#$%^&=_~,.?]/g, '');
            $(this).val(value);
        });

        // 연결 페이지 보기
        $('.js-connect-page-list').bind('click',function () {
            var skinNm = $('button[name="connectPageList"]').attr('data-skin');
            $.ajax({
                url: './layer_connect_list.php',
                type: 'post',
                data: {mode: 'connectPage', skinName: skinNm},
                async: false,
                success: function (data) {
                    BootstrapDialog.show({
                        title: '연결 페이지 보기',
                        size: BootstrapDialog.SIZE_WIDE_LARGE,
                        message: $(data),
                        closable: true
                    });
                }
            });
        });

    });

    // 모바일 페이지 연결/미연결
    function connectToggle(thisName, thisClass) {
        var modeStr = $('input[name="' + thisName + '"]:checked').val();
        if (modeStr == 'y') {
            $('.' + thisClass).removeClass('display-none');
        } else if (modeStr == 'n') {
            $('.' + thisClass).addClass('display-none');
        }
    }

</script>
