<style>
    .weather_widget_notice {
        float: left;
        width: 50px;
        font-size: 11px;
        font-weight: normal;
        color: #999999;
    }
    .weather_widget_content {
        margin-left: 15px;
        font-size: 11px;
        font-weight: normal;
        color: #999999;
    }
    .weather_widget_type{
        cursor: pointer;
    }
    .widget_type_wrapper {
        float: left;
        margin-left: 10px;
        margin-right: 10px;
    }
    .widget_type_radio_button {
        margin-bottom: 10px;
        margin-top: 10px;
    }
    .widget_type {
        margin-bottom: 20px;
    }
    .widget_type_alert {
        margin-top: 15px;
    }
</style>

<form method="post" action="weather_widget_ps.php" target="ifrmProcess">

<div class="page-header">
    <h3><?=end($naviMenu->location);?></h3>
    <div class="btn-group">
        <input type="submit" class="btn btn-red" value="저장">
    </div>
</div>

<h5 class="table-title">기본 설정</h5>

<table class="table table-cols">
    <tr>
        <th width="180">사용 설정</th>
        <td>
            <label class="radio-inline">
                <input type="radio" name="active" value="1"
                    <?=!isset($item['active']) || $item['active'] === true ? ' checked="checked"' : ''?>>사용함
            </label>
            <label class="radio-inline">
                <input type="radio" name="active" value="0"
                    <?=$item['active'] === false ? ' checked="checked"' : ''?>>사용안함
            </label>
        </td>
    </tr>
    <tr>
        <th>치환코드</th>
        <td>
            <span id="replace-code">&lt;!--&lcub;&num; nhngodo/weather_widget_free &num;&rcub;--></span>
            <input id="btn-copy" type="button" class="btn btn-white" value="복사">
            <div class="widget_type_alert">
                <div class="notice-info memo_board_alert">해당 치환코드는 PC/모바일 쇼핑몰 구분 없이 공통으로 사용하실 수 있습니다.<br>원하는 쇼핑몰의 디자인 페이지에 치환코드를 각각 삽입하여 이용하시면 됩니다.</div>
            </div>
        </td>
    </tr>
    <tr>
        <th>기본 지역 설정</th>
        <td>
            <div class="form-inline" style="margin-bottom: 10px;">
                <select name="base_location" class="form-control">
                    <?php foreach ($locations as $location) {?>
                    <option value="<?=$location?>"<?=$item['base_location'] == $location ? ' selected="selected"': ''?>>
                        <?=$location?></option>
                    <?php }?>
                </select>
            </div>
        </td>
    </tr>
    <tr>
        <th>기상청 링크 사용설정</th>
        <td>
            <label class="radio-inline">
                <input name="widget_link_usable_setting" value="0" type="radio"
                    <?=$item['widget_link_usable_setting'] == 0 ? ' checked="checked"' : ''?>>&nbsp;사용함
            </label>
            <label class="radio-inline">
                <input name="widget_link_usable_setting" value="1" type="radio"
                    <?=$item['widget_link_usable_setting'] == 1 ? ' checked="checked"' : ''?>>&nbsp;사용안함
            </label>
        </td>
    </tr>
    <tr>
        <th>위젯 배경색</th>
        <td>
            <label class="radio-inline weather_background_setting">
                <input name="widget_background_color_usable_setting" value="0" type="radio"
                    <?=$item['widget_background_color_usable_setting'] == 0 ? ' checked="checked"' : ''?>>&nbsp;사용함
            </label>
            <label class="radio-inline weather_background_setting">
                <input name="widget_background_color_usable_setting" value="1" type="radio"
                    <?=$item['widget_background_color_usable_setting'] == 1 ? ' checked="checked"' : ''?>>&nbsp;사용안함
            </label>
        </td>
    </tr>
    <tr>
        <th>위젯 테두리</th>
        <td>
            <label class="radio-inline">
                <input name="widget_border_usable_setting" value="0" type="radio"
                    <?=$item['widget_border_usable_setting'] == 0 ? ' checked="checked"' : ''?>>&nbsp;사용함
            </label>
            <label class="radio-inline">
                <input name="widget_border_usable_setting" value="1" type="radio"
                    <?=$item['widget_border_usable_setting'] == 1 ? ' checked="checked"' : ''?>>&nbsp;사용안함
            </label>
        </td>
    </tr>
    <tr>
        <th>색상 설정</th>
        <td>
            <div class="form-inline">
                <label class="radio-inline weather_widget_color_setting" style="position: relative">
                    폰트 색상
                    <input name="font_color" value="<?=$item['font_color']?>" maxlength="7"
                           class="form-control width-xs center color-selector" type="text">
                </label>
                <label class="radio-inline weather_widget_color_setting"
                       style="position: relative<?=$item['widget_background_color_usable_setting'] == 1 ? '; display: none"' : ''?>">
                    배경 색상
                    <input name="background_color"
                           value="<?=$item['widget_background_color_usable_setting'] == 0 ? $item['background_color'] : '#ffffff'?>"
                           maxlength="7" class="form-control width-xs center color-selector" type="text">
                </label>
                <label class="radio-inline weather_widget_color_setting" style="position: relative;<?=$item['widget_border_usable_setting'] == 1 ? '; display: none' : ''?>">
                    테두리 색상
                    <input name="border_color"
                           value="<?=$item['widget_border_usable_setting'] == 0 ? $item['border_color'] : '#e4e4e4'?>"
                           maxlength="7" class="form-control width-xs center color-selector" type="text">
                </label>
            </div>
        </td>
    </tr>
    <tr>
        <th>위젯 타입</th>
        <td>
            <div class="form-inline widget_type" style="margin-bottom: 10px;">
                <div class="checkbox-inline">
                    <div class="widget_type_wrapper">
                        <label class="weather_widget_type">
                            <div class="widget_type_radio_button">
                                <input name="widget_type" value="0" type="radio"
                                       <?=$item['widget_type'] == 0 ? ' checked="checked"' : ''?>>&nbsp;일반형
                            </div>
                            <img src="<?=PATH_ADMIN_GD_SHARE?>image/weather_widget/general_type.png">
                        </label>
                    </div>
                    <div class="widget_type_wrapper">
                        <label class="weather_widget_type">
                            <div class="widget_type_radio_button">
                                <input name="widget_type" value="1" type="radio"
                                       <?=$item['widget_type'] == 1 ? ' checked="checked"' : ''?>>&nbsp;리스트형
                            </div>
                            <img src="<?=PATH_ADMIN_GD_SHARE?>image/weather_widget/list_type.png">
                        </label>
                    </div>
                    <div class="widget_type_wrapper">
                        <label class="weather_widget_type">
                            <div class="widget_type_radio_button">
                                <input name="widget_type" value="2" type="radio"
                                       <?=$item['widget_type'] == 2 ? ' checked="checked"' : ''?>>&nbsp;심플형
                            </div>
                            <img src="<?=PATH_ADMIN_GD_SHARE?>image/weather_widget/simple_type.png">
                        </label>
                    </div>
                </div>
            </div>
            <div class="widget_type_alert">
                <div class="notice-info memo_board_alert">위젯의 사이즈는 설정한 타입에 따라 자동으로 조정됩니다 사이즈는 아래를 참고하시기 바랍니다.</div>
                <div class="memo_board_alert"><span class="weather_widget_notice">일반형&nbsp; </span><span class="weather_widget_content">PC : 260 x 290 px / 모바일 : 260 x 227 px</span></div>
                <div class="memo_board_alert"><span class="weather_widget_notice">리스트형 </span><span  class="weather_widget_content">PC : 190 x 302 px / 모바일 : 150 x 238 px</span></div>
                <div class="memo_board_alert"><span class="weather_widget_notice">심플형&nbsp;</span><span class="weather_widget_content">PC : 210 x 165 px / 모바일 : 160 x 120 px</span></div>
            </div>
        </td>
    </tr>
</table>

</form>

<script type="text/javascript">
$(document).ready(function () {
    $('#btn-copy').click(function () {
        var replace_code = $('#replace-code').text();
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(replace_code).select();
        document.execCommand("copy");
        $temp.remove();
        alert('치환코드를 클립보드에 복사했습니다. Ctrl+v를 이용하여 사용하세요.');
    });

    function _remove_color_column(column) {
        var elem = $('.weather_widget_color_setting');

        switch (column) {
            case 'background' :
                elem.eq(1).css('display', 'none');
                break;
            case 'border' :
                elem.eq(2).css('display', 'none');
                break;

        }
    }

    function _add_color_column(column) {
        var elem = $('.weather_widget_color_setting');

        switch (column) {
            case 'background' :
                elem.eq(1).css('display', '');
                break;
            case 'border' :
                elem.eq(2).css('display', '');
                break;

        }
    }

    $('input[name="widget_background_color_usable_setting"]').change(function (e) {
        if ($(e.currentTarget).val() == 1) {
            _remove_color_column('background');
        } else {
            _add_color_column('background');
        }
    });

    $('input[name="widget_border_usable_setting"]').change(function (e) {
        if ($(e.currentTarget).val() == 1) {
            _remove_color_column('border');
        } else {
            _add_color_column('border');
        }
    });
});
</script>
