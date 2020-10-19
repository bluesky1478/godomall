<?php
// 현재 화일의 확장자 체크 후 출력 여부 체크
$ext = explode('.', trim($designInfo['file']['name']));
$tmp = array_pop($ext);
?>
<?php if (in_array($tmp, ['js', 'css']) === false) {?>
<div class="table-title gd-help-manual">
    파일 설명
    <?php if($skinType === 'front'){?>
    <div style="float: right;"><button type="button" name="connectPageList" class="btn btn-white bold js-connect-page-list" data-skin="<?=$currentWorkSkin?>">연결 페이지 보기</button></div>
    <?php }?>
</div>
<div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
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
        <?php if($connectFl === true && $skinType === 'front'){?>
        <tr>
            <th>모바일 페이지 연결</th>
            <td colspan="3">
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="connectFl" value="y" onclick="display_toggle_class('connectFl', 'js-connect-page');" <?php if (gd_isset($designInfo['file']['connectFl'], 'n') === 'y') { echo 'checked="checked"'; }?> /> 연결</label>
                    <label class="radio-inline"><input type="radio" name="connectFl" value="n" onclick="display_toggle_class('connectFl', 'js-connect-page');" <?php if ($designInfo['file']['connectFl'] === 'n') { echo 'checked="checked"'; }?> /> 미연결</label>
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
        display_toggle_class('connectFl', 'js-connect-page');

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
    function display_toggle_class(thisName, thisClass) {
        var modeStr = $('input[name="' + thisName + '"]:checked').val();
        if (modeStr == 'y') {
            $('.' + thisClass).removeClass('display-none');
        } else if (modeStr == 'n') {
            $('.' + thisClass).addClass('display-none');
        }
    }

</script>
<?php }?>
