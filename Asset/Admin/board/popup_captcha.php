<style type="text/css">
    .colorPickerHolder {
        float: left;
        width: 14px;
        height: 14px;
        margin-left: 5px;
        cursor: pointer;
        border: solid #efefef 2px;
    }
</style>

<form id="frm" action="../board/board_ps.php" method="post">
    <input type="hidden" name="mode" id="mode" value="captcha"/>
    <input type="hidden" name="sno" id="sno" value="<?php echo gd_isset($data['sno']) ?>"/>
    <!--	<div class="phead_wrap mgt0"><div class="phead">-->
    <!--        <h2>자동등록방지문자 이미지 설정<span>자동등록방지문자 관련사항을 설정하세요</span></h2>-->
    <!--		 <input type="submit" value="저장" class="btn btn-primary"  />-->
    <!--	</div></div>-->
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>이미지 배경색</th>
            <td class="form-inline">
                <input type=text name="bdCaptchaBgClr" id="bdCaptchaBgClr"
                       value="<?php echo gd_isset($data['bdCaptchaBgClr']) ?>" maxlength="6" class="form-control">

                <div id="captchaBgColorPicker" class="colorPickerHolder"
                     style="background-color:#<?php echo gd_isset($data['bdCaptchaBgClr']) ?>"></div>
                <div class="text-violet">기본색상값 을 사용하려면 공란으로 두세요</div>
            </td>
        </tr>
        <tr>
            <th>이미지 글자색</th>
            <td class="form-inline">
                <input type=text name="bdCaptchaClr" id="bdCaptchaClr"
                       value="<?php echo gd_isset($data['bdCaptchaClr']) ?>" maxlength="6" class="form-control">

                <div id="captchaColorPicker" class="colorPickerHolder"
                     style="background-color:#<?php echo gd_isset($data['bdCaptchaClr']) ?>"></div>
                <div class="text-violet">기본색상값 (262626) 을 사용하려면 공란으로 두세요</div>
            </td>
        </tr>
        <tr>
            <th height=50>현재 적용된<br>등록방지 이미지</th>
            <td><IMG id="imgCaptcha" src="/board/captcha.php?sno=<?php echo gd_isset($data['sno']) ?>"
                     align="absmiddle"></td>
        </tr>
    </table>
    <div class="table-btn">
        <input type="submit" value="저장하기" class="btn btn-red"/>
    </div>
</form>
<div id="codeDetail" style="display:none"></div>

<script type="text/javascript">
    $(document).ready(function () {
        document.body.scroll = 'no';
        // Form Process
        $("#frm").validate({
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

        /**
         * 색상 ColorPicker
         */
        $('#bdCaptchaBgClr,#bdCaptchaClr').ColorPicker({
                onSubmit: function (hsb, hex, rgb, el) {
                    $(el).val(hex.toUpperCase());
                    $(el).data('target').css("background-color", "#" + hex);
                    $(el).ColorPickerHide();
                    $("#imgCaptcha").attr("src", "/board/captcha.php?bgColor=" + $("#bdCaptchaBgClr").val() + "&color=" + $("#bdCaptchaClr").val());
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                },
                onChange: function (hsb, hex, rgb) {
                    $(this.data('colorpicker').el).val(hex.toUpperCase());
                    $(this.data('colorpicker').el).data('target').css("background-color", "#" + hex);
                },
                onHide: function () {
                    $("#imgCaptcha").attr("src", "/board/captcha.php?bgColor=" + $("#bdCaptchaBgClr").val() + "&color=" + $("#bdCaptchaClr").val());
                }
            })
            .bind('keyup', function () {
                this.value = this.value.replace(/[^0-9a-fA-F]*/g, "").toUpperCase();
                $(this).ColorPickerSetColor(this.value);
            });
        $('#captchaBgColorPicker,#captchaColorPicker').each(function () {
            var el = null;
            switch (this.id) {
                case "captchaBgColorPicker" :
                {
                    el = $("#bdCaptchaBgClr");
                    break;
                }
                case "captchaColorPicker" :
                {
                    el = $("#bdCaptchaClr");
                    break;
                }
            }
            el.data("target", $(this));

            $(this).click(function () {
                el.ColorPickerShow();
            });
        });
    });
</script>
