<style>
    .ps_select_box{
        margin: 0 5px;
    }
    .replace_code {
        font-weight: bold;
    }
    #widget_icon_area {
        display:none;
    }
    #self_icon_control_area{
        display:none;
    }
    #mobile_self_icon_area{
        display:none;
    }
</style>

<div class="page-header">
    <h3><?=end($naviMenu->location);?></h3>
    <div class="btn-group">
        <input id="save_btn" type="button" value="저장" class="btn btn-red">
    </div>
</div>

<div class="table-title">위젯 설정</div>
<table id="widget-config" class="table table-cols">
    <colgroup>
        <col class="width-sm" />
        <col/>
    </colgroup>
    <tr>
        <th>노출설정</th>
        <td>
            <label class="radio-inline">
                <input type="radio" name="widget_display" value="true"<?=$widget_display ? ' checked="checked"':''?> />노출함
            </label>
            <label class="radio-inline">
                <input type="radio" name="widget_display" value="false"<?=$widget_display ? '':' checked="checked"'?> />노출안함
            </label>
            <p class="notice-info">‘노출안함’ 으로 설정하는 경우, 치환코드 삽입여부와 관계없이 쇼핑몰 위젯 노출이 중지 됩니다.</p>
        </td>
    </tr>
    <tr>
        <th>치환코드</th>
        <td>
            <span id="replace_code" class="replace_code">&lt;!--&lcub;&num; nhngodo/exchange_rate_widget_free &num;&rcub;--></span>
            <input type="button" id="copy_replace_code" class="btn btn-white btn-group-xs" value="복사">
            <p class="notice-info">
                해당 치환코드는 PC/모바일 쇼핑몰 구분 없이 공통으로 사용하실 수 있습니다.<br>
                원하는 쇼핑몰의 디자인 페이지에 치환코드를 각각 삽인하여 이용하시면 됩니다.
            </p>
        </td>
    </tr>
    <tr>
        <th>기본 통화 설정</th>
        <td>
            <div class="form-inline">
                <span>기준 통화</span>
                <select name="base_cur_type" class="form-control mgr20 ps_select_box">
                    <option value="KRW"<?=$base_cur_type == 'KRW' ? ' selected="selected"':''?>>대한민국 KRW (￦)</option>
                    <option value="USD"<?=$base_cur_type == 'USD' ? ' selected="selected"':''?>>미국 USD ($)</option>
                    <option value="CNY"<?=$base_cur_type == 'CNY' ? ' selected="selected"':''?>>중국 CNY (￥)</option>
                    <option value="JPY"<?=$base_cur_type == 'JPY' ? ' selected="selected"':''?>>일본 JPY (￥)</option>
                    <option value="EUR"<?=$base_cur_type == 'EUR' ? ' selected="selected"':''?>>유럽 EUR (€)</option>
                </select>&nbsp;&nbsp;
                <span>환산 통화</span>&nbsp;&nbsp;
                <select name="exchange_cur_type" class="form-control">
                    <option value="KRW"<?=$exchange_cur_type == 'KRW' ? ' selected="selected"':''?>>대한민국 KRW (￦)</option>
                    <option value="USD"<?=$exchange_cur_type == 'USD' ? ' selected="selected"':''?>>미국 USD ($)</option>
                    <option value="CNY"<?=$exchange_cur_type == 'CNY' ? ' selected="selected"':''?>>중국 CNY (￥)</option>
                    <option value="JPY"<?=$exchange_cur_type == 'JPY' ? ' selected="selected"':''?>>일본 JPY (￥)</option>
                    <option value="EUR"<?=$exchange_cur_type == 'EUR' ? ' selected="selected"':''?>>유럽 EUR (€)</option>
                </select>
            </div>
            <p class="notice-info">쇼핑몰에 기본으로 출력되는 통화를 설정합니다.</p>
        </td>
    </tr>
    <tr>
        <th>위젯 유형</th>
        <td>
            <div class="form-inline">
                <div class="pd10 pdl0" style="float:left">
                    <label class="radio-inline mgt5">
                        <img src="<?=PATH_ADMIN_GD_SHARE?>img/icon_view1.gif"><br>
                        <input type="radio" name="widget_type" value="spread_type"
                            <?=$widget_type == 'spread_type' ? ' checked="checked"':''?>>펼침형
                    </label>
                </div>
                <div class="pd10" style="float:left">
                    <label class="radio-inline mgt5">
                        <img src="<?=PATH_ADMIN_GD_SHARE?>img/icon_view2.gif"><br>
                        <input type="radio" name="widget_type" value="icon_type"
                            <?=$widget_type == 'icon_type' ? ' checked="checked"':''?>>아이콘형
                    </label>
                </div>
                <div class="notice-info" style="clear:both">
                    펼침형 : 환율 계산 위젯이 펼쳐진 상태로 고정되어 노출됩니다.<br>
                    아이콘형 : 설정해놓은 아이콘 클릭 시 환율 계산 위젯이 노출됩니다.
                </div>
            </div>
        </td>
    </tr>
    <tr id="widget_icon_area">
        <th>위젯 아이콘</th>
        <td>
            <label class="nobr radio-inline">
                <input type="radio" name="widget_icon_type" value="basic_icon"
                    <?=$widget_icon_type == 'basic_icon' ? ' checked="checked"':''?>> 기본 아이콘
                <img src="<?=PATH_ADMIN_GD_SHARE?>img/icon_exchange.gif" alt="기본 아이콘" title="기본 아이콘" class="vtop mgl10">
            </label>
            <div class="mgt15">
                <label class="nobr radio-inline">
                    <input type="radio" name="widget_icon_type" value="self_icon"
                    <?=$widget_icon_type == 'self_icon' ? ' checked="checked"':''?>> 직접 등록
                </label>
                <div id="self_icon_control_area" class="pdl30 mgt10">
                    <table class="table table-cols">
                        <tbody>
                        <tr>
                            <td colspan="2" class="width-xs left pd10">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="use_self_icon_both"
                                        <?=$widget_icon_use_both == 'true' ? ' checked="checked"':''?>>
                                    PC/모바일 아이콘 동일사용
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th class="width-md">PC 아이콘</th>
                            <td>
                                <div class="form-inline display-inline-block mgr10">
                                    <div class="form-inline">
                                        <input type='file' id="imgInput_pc" accept="image/*" class="form-control input-sm"/>
                                        <img id="temp_preview_pc" style="display: inline-block" src="<?=$pc_icon?>" />
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr id="mobile_self_icon_area">
                            <th class="width-md">모바일 아이콘</th>
                            <td>
                                <div class="form-inline display-inline-block mgr10" >
                                    <div class="form-inline">
                                        <input type="file" id="imgInput_mb" accept="image/*" class="form-control"/>
                                        <img id="temp_preview_mb" src="<?=$mb_icon?>" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="notice-info" style="clear:both">
                    최대 5MB 이하의 GIF, PNG, JPG, JPEG 이미지 파일만 등록할 수 있습니다.
                </div>
                <div class="notice-danger">
                    이미지가 5MB 이상이거나 확장자가 다른 경우 기본 아이콘으로 저장됩니다.
                </div>
            </div>
        </td>
    </tr>
</table>
<div class="notice-info">
    환율은 KEB하나은행 전일 최종고시 금액을 기준으로 매일 1회(02:00) 갱신됩니다.
    (KED하나은행 환율정보 확인 ☞<a href="https://www.kebhana.com/cont/mall/mall15/mall1501/index.jsp?_menuNo=23100" target="_blank">바로가기</a>)<br>
    네트워크 장애 등으로 인하여 부득이하게 해당 정보를 정상적으로 인지하지 못할 경우에는 그 전일 최종적으로 성공한 값을 표시합니다.
</div>
<?php if (!$installed) {?>
<div class="notice-danger">
    환율 계산 위젯 기능 이용시 아래 스킨패치 파일을 적용하셔야만 정상적인 이용이 가능합니다.<br>
    <span style="color: #999999">- 패치 & 업그레이드 게시판 > 환율 계산 위젯 기능 개선 - 2019.00.00</span>
</div>
<?php }?>

<script type="text/javascript">
    $(document).ready(function () {
        if ($("input:radio[name='widget_type']:checked").val() === 'icon_type'){
            $('#widget_icon_area').show();
        }
        if ($("input:radio[name='widget_icon_type']:checked").val() === 'self_icon'){
            $('#self_icon_control_area').show();
        }
        if($(':checkbox[name="use_self_icon_both"]').is(':checked') === false){
            $('#mobile_self_icon_area').show();
        }

        if ($("input:radio[name='widget_display']:checked").val() == 'false') {
            $('#widget-config').find('tr').not(':first').hide();
        }

        $("input:radio[name='widget_display']").change(function(e) {
            var value = e.target.value;

            if (value == 'true') {
                $('#widget-config').find('tr').not(':first').show();
            } else {
                $('#widget-config').find('tr').not(':first').hide();
                dialog_confirm('노출설정이 변경되었습니다. ‘저장‘ 버튼을 클릭하여 완료하시기 바랍니다.', function (result) {
                    if (!result) {
                        $("input:radio[name='widget_display'][value=true]").trigger('click');
                    }
                });
            }
        });
        $("input:radio[name='widget_type']").change(function(){
            $('#widget_icon_area').toggle();
        });
        $("input:radio[name='widget_icon_type']").change(function(){
            $('#self_icon_control_area').toggle();
        });
        $("input:checkbox[name='use_self_icon_both']").change(function(){
            $('#mobile_self_icon_area').toggle();
        });
        $("#imgInput_pc").change(function(){
            __readURL(this, 'pc');
        });

        $("#imgInput_mb").change(function(){
            __readURL(this, 'mb');
        });
        $('#save_btn').click(function(){
            __save();
        });
        $("#copy_replace_code").on("click", function() {
            var replace_code = $('#replace_code').text();
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(replace_code).select();
            document.execCommand("copy");
            $temp.remove();
            alert('[치환코드 복사] 정보를 클립보드에 복사했습니다. Ctrl + v를 이용해서 사용하세요.');
            return false;
        });

        function __readURL(input, type) {
            if (input.files && input.files[0]) {
                if(input.files[0].type !== 'image/jpeg'
                    && input.files[0].type !== 'image/jpg'
                    && input.files[0].type !== 'image/png'
                    && input.files[0].type !== 'image/gif') {
                    $(':radio[name="widget_icon_type"]:checked').val('basic_icon');
                    return '';
                }

                if(input.files[0].size > 5000000) {
                    $(':radio[name="widget_icon_type"]:checked').val('basic_icon');
                    return '';
                }

                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#temp_preview_' + type).attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
                return input.files[0];
            }
            return null;
        }

        function __fileAjax(url, formData, func, method) {
            $.ajax({
                url: url,
                processData: false,
                contentType: false,
                data: formData,
                type: method,
                success: function (result) {
                    func(result);
                }
            });
        }

        function __save() {
            var formData = new FormData();

            formData.append('widget_display', $(':radio[name="widget_display"]:checked').val());
            formData.append('self_icon_pc', __readURL($('#imgInput_pc')[0], 'pc'));
            formData.append('self_icon_mb', __readURL($('#imgInput_mb')[0], 'mb'));
            formData.append('base_cur_type', $("select[name=base_cur_type] option:selected").val());
            formData.append('exchange_cur_type', $("select[name=exchange_cur_type] option:selected").val());
            formData.append('widget_type', $(':radio[name="widget_type"]:checked').val());
            formData.append('widget_icon_type', $(':radio[name="widget_icon_type"]:checked').val());
            formData.append('widget_icon_use_both', $(':checkbox[name="use_self_icon_both"]').is(':checked'));

            var register = function (data) {
                data = JSON.parse(data);
                dialog_alert(data['msg'], '경고', {'isReload': true});
            };
            var url = 'exchange_rate_widget_ps.php';
            __fileAjax(url, formData, register, 'POST');
        }
    });
</script>
