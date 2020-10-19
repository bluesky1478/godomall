<style>
    input[name="effect_name"] { width: 90%; }
    #snow-target { width: 250px; height: 150px; margin-right: 50px; position: relative; overflow: hidden; float: left; }
    .effect_limited_group { margin-bottom: 10px; }
    th { font-size: 12px; background-color: #F6F6F6; border-right: 1px solid #EAEAEA; border-left: 1px solid #EAEAEA; }
    label.col { width: 100px; }
    p.notice-info { margin-left: 20px; }
    #image-preview-layer { margin-top: 10px; display: none; }
    .image-preview-frame {
        margin-left: 20px; width: 40px; height: 40px; float: left; overflow: hidden;
        border: solid 1px lightgray; position: relative; }
    .image-preview-frame img { position: absolute; top: 50%; left: 50%; transform: translateY(-50%) translateX(-50%);
        -ms-transform: translateY(-50%) translateX(-50%); width:40px; height:40px; }
    .image-preview-details { margin-left: 20px; float: left; width: 200px; height: 40px; position: relative; }
    .image-preview-details ul {
        position: absolute; top: 50%; width: 150px; transform: translateY(-50%); -ms-transform: translateY(-50%); }
</style>

<div class="page-header">
    <h3><?=end($naviMenu->location);?></h3>
    <div class="btn-group">
        <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./screen_effect_list.php');"/>
        <input onclick="AdminForm.submit();" id="save_btn" type="button" value="저장" class="btn btn-red">
    </div>
</div>

<h5 class="table-title"><?=end($naviMenu->location)?></h5>

<form id="frmEffect" method="post" enctype="multipart/form-data" action="screen_effect_ps.php" target="ifrmProcess">
    <input type="hidden" name="mode" value="upsert">
    <?php if ($item['sno']) {?>
    <input type="hidden" name="sno" value="<?=$item['sno']?>">
    <?php }?>

    <table class="table table-cols">
        <colgroup>
            <col width="width-md"/>
            <col width="width-2xl"/>
        </colgroup>

        <tbody>

        <tr>
            <th>효과명</th>
            <td>
                <input oninput="AdminForm.wordCount()" type="text" name="effect_name" maxlength="50"
                       value="<?=$item['effect_name']?>">
                <span id="word_count">0</span>
                <span> / 50</span>
            </td>
        </tr>
        <tr>
            <th>적용기간</th>
            <td>
                <div class="form-inline effect_limited_group">
                    <label class="radio-inline">
                        <input type="radio" onclick="AdminForm.clearEffectDatetime()" name="effect_limited" value="0"
                            <?=$item['effect_limited'] == 0 ? ' checked="checked"' : ''?>>
                        <span>제한 없음</span>
                    </label>
                </div>
                <div class="form-inline">
                    <div class="input-group">
                        <input type="radio" name="effect_limited" value="1" onclick="AdminForm.setDefaultDate()"
                            <?=$item['effect_limited'] == 1 ? 'checked="checked"' : ''?>>
                    </div>
                    <div class="input-group js-datepicker">
                        <input type="text" name="effect_start_date" onclick="AdminForm.setDefaultDate()"
                               value="<?=$item['effect_start_date']?>" class="form-control" placeholder="수기입력 가능">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar" onclick="AdminForm.setDefaultDate()"></span>
                        </span>
                    </div>
                    <input type="text" name="effect_start_time" class="form-control js-timepicker effect_time"
                           placeholder="수기입력 가능" value="<?=$item['effect_start_time']?>"
                           onclick="AdminForm.setDefaultDate()">
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="effect_end_date" onclick="AdminForm.setDefaultDate($(this))"
                               class="form-control" placeholder="수기입력 가능" value="<?=$item['effect_end_date']?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar" onclick="AdminForm.setDefaultDate()"></span>
                        </span>
                    </div>
                    <input type="text" name="effect_end_time" class="form-control js-timepicker effect_time"
                           placeholder="수기입력 가능" value="<?=$item['effect_end_time']?>"
                           onclick="AdminForm.setDefaultDate($(this))">
                </div>
            </td>
        </tr>
        <tr>
            <th>효과 종류</th>
            <td>
                <table class="table table-cols">
                    <tr>
                        <div id="snow-target">
                            <img src="<?=PATH_ADMIN_GD_SHARE?>img/screen_effect/bg_effect_preview.png"  />
                        </div>
                        <div style="margin-top: 45px; float: left">
                            <ul>
                                <li>
                                    <label>
                                        <input type="radio" name="effect_type" onclick="AdminForm.effect(this.value)"
                                               value="1"
                                            <?=!$item['effect_type'] || $item['effect_type'] == 1 ? ' checked="checked"' : ''?>>
                                        눈내림(일직선 하강)
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input type="radio" name="effect_type" onclick="AdminForm.effect(this.value)"
                                               value="2"
                                            <?=$item['effect_type'] == 2 ? ' checked="checked"' : ''?>>
                                        바람 날림(좌우로 이동하며 하강)
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input type="radio" name="effect_type" onclick="AdminForm.effect(this.value)"
                                               value="3"
                                            <?=$item['effect_type'] == 3 ? ' checked="checked"' : ''?>>
                                        팝콘 (나타났다 사라짐)
                                    </label>
                                </li>
                                <li>
                                    <label>
                                        <input type="checkbox" name="effect_type_twinkle" onclick="AdminForm.effect(4)"
                                            <?=$item['effect_type_twinkle'] ? ' checked="checked"' : ''?>>
                                        반짝거림 (투명도 부분 적용)<br>
                                    </label>
                                    <p class="notice-info">팝콘 효과에서는 반짝거림 효과가 적용되지 않습니다.</p>
                                </li>
                            </ul>
                        </div>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th>이미지 설정</th>
            <td>
                <div>
                    <label>
                        <input type="radio" name="image_type" value="default" onclick="AdminForm.imageType(this.value)"
                            <?=!$item['image_type'] || $item['image_type'] == 'default' ? ' checked="checked"' : ''?>>
                        기본 이미지
                    </label>
                </div>
                <table style="margin-top: 10px; margin-left: 20px">
                <tr>
                    <td class="image_setting">
                        <table class="table">
                        <tr>
                            <th>눈</th>
                            <td>
                                <label>
                                    <input type="radio" class="effect_image" name="effect_image" value="snowflake_1.png"
                                           onclick="AdminForm.imageShape(this.value)"
                                        <?=!$item['effect_image'] || $item['effect_image'] == 'snowflake_1.png' ? ' checked="checked"' : ''?>>
                                    <img width="30" src="<?=$imagePath?>/thumbnail/snowflake_1.png" />
                                </label>
                            </td>
                            <td>
                                <label>
                                    <input type="radio" class="effect_image" name="effect_image" value="snowflake_2.png"
                                           onclick="AdminForm.imageShape(this.value)"
                                        <?=$item['effect_image'] == 'snowflake_2.png' ? ' checked="checked"' : ''?>>
                                    <img width="30" src="<?=$imagePath?>/thumbnail/snowflake_2.png" />
                                </label>
                            </td>
                            <td>
                                <label>
                                    <input type="radio" class="effect_image" name="effect_image" value="snowflake_3.png"
                                           onclick="AdminForm.imageShape(this.value)"
                                        <?=$item['effect_image'] == 'snowflake_3.png' ? ' checked="checked"' : ''?>>
                                    <img width="30" src="<?=$imagePath?>/thumbnail/snowflake_3.png" />
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th>꽃잎</th>
                            <td>
                                <label>
                                    <input type="radio" class="effect_image" name="effect_image" value="flower_1.png"
                                           onclick="AdminForm.imageShape(this.value)"
                                        <?=$item['effect_image'] == 'flower_1.png' ? ' checked="checked"' : ''?>>
                                    <img width="30" src="<?=$imagePath?>/thumbnail/flower_1.png" />
                                </label>                                    </td>
                            <td>
                                <label>
                                    <input type="radio" class="effect_image" name="effect_image" value="flower_2.png"
                                           onclick="AdminForm.imageShape(this.value)"
                                        <?=$item['effect_image'] == 'flower_2.png' ? ' checked="checked"' : ''?>>
                                    <img width="30" src="<?=$imagePath?>/thumbnail/flower_2.png" />
                                </label>
                            </td>
                            <td>
                                <label>
                                    <input type="radio" class="effect_image" name="effect_image" value="flower_3.png"
                                           onclick="AdminForm.imageShape(this.value)"
                                        <?=$item['effect_image'] == 'flower_3.png' ? ' checked="checked"' : ''?>>
                                    <img width="30" src="<?=$imagePath?>/thumbnail/flower_3.png" />
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th>낙엽</th>
                            <td>
                                <label>
                                    <input type="radio" class="effect_image" name="effect_image" value="fallen-leaves_1.png"
                                           onclick="AdminForm.imageShape(this.value)"
                                        <?=$item['effect_image'] == 'fallen-leaves_1.png' ? ' checked="checked"' : ''?>>
                                    <img width="30" src="<?=$imagePath?>/thumbnail/fallen-leaves_1.png" />
                                </label>
                            </td>
                            <td>
                                <label>
                                    <input type="radio" class="effect_image" name="effect_image" value="fallen-leaves_2.png"
                                           onclick="AdminForm.imageShape(this.value)"
                                        <?=$item['effect_image'] == 'fallen-leaves_2.png' ? ' checked="checked"' : ''?>>
                                    <img width="30"  src="<?=$imagePath?>/thumbnail/fallen-leaves_2.png" />
                                </label>
                            </td>
                            <td>
                                <label>
                                    <input type="radio" class="effect_image" name="effect_image" value="fallen-leaves_3.png"
                                           onclick="AdminForm.imageShape(this.value)"
                                        <?=$item['effect_image'] == 'fallen-leaves_3.png' ? ' checked="checked"' : ''?>>
                                    <img width="30" src="<?=$imagePath?>/thumbnail/fallen-leaves_3.png" />
                                </label>
                            </td>
                        </tr>
                        </table>
                    </td>
                    <td class="image_setting">
                        <table class="table">
                            <tr>
                                <th>물방울</th>
                                <td>
                                    <label>
                                        <input type="radio" class="effect_image" name="effect_image" value="waterdrop_1.png"
                                               onclick="AdminForm.imageShape(this.value)"
                                            <?=$item['effect_image'] == 'waterdrop_1.png' ? ' checked="checked"' : ''?>>
                                        <img width="30" src="<?=$imagePath?>/thumbnail/waterdrop_1.png" />
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="radio" class="effect_image" name="effect_image" value="waterdrop_2.png"
                                               onclick="AdminForm.imageShape(this.value)"
                                            <?=$item['effect_image'] == 'waterdrop_2.png' ? ' checked="checked"' : ''?>>
                                        <img width="30" src="<?=$imagePath?>/thumbnail/waterdrop_2.png" />
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="radio" class="effect_image" name="effect_image" value="waterdrop_3.png"
                                               onclick="AdminForm.imageShape(this.value)"
                                            <?=$item['effect_image'] == 'waterdrop_3.png' ? ' checked="checked"' : ''?>>
                                        <img width="30" src="<?=$imagePath?>/thumbnail/waterdrop_3.png" />
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>하트</th>
                                <td>
                                    <label>
                                        <input type="radio" class="effect_image" name="effect_image" value="heart_1.png"
                                               onclick="AdminForm.imageShape(this.value)"
                                            <?=$item['effect_image'] == 'heart_1.png' ? ' checked="checked"' : ''?>>
                                        <img width="30" src="<?=$imagePath?>/thumbnail/heart_1.png" />
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="radio" class="effect_image" name="effect_image" value="heart_2.png"
                                               onclick="AdminForm.imageShape(this.value)"
                                            <?=$item['effect_image'] == 'heart_2.png' ? ' checked="checked"' : ''?>>
                                        <img width="30" src="<?=$imagePath?>/thumbnail/heart_2.png" />
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="radio" class="effect_image" name="effect_image" value="heart_3.png"
                                               onclick="AdminForm.imageShape(this.value)"
                                            <?=$item['effect_image'] == 'heart_3.png' ? ' checked="checked"' : ''?>>
                                        <img width="30" src="<?=$imagePath?>/thumbnail/heart_3.png"/>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>돈</th>
                                <td>
                                    <label>
                                        <input type="radio" class="effect_image" name="effect_image" value="money_1.png"
                                               onclick="AdminForm.imageShape(this.value)"
                                            <?=$item['effect_image'] == 'money_1.png' ? ' checked="checked"' : ''?>>
                                        <img width="30"  src="<?=$imagePath?>/thumbnail/money_1.png" />
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="radio" class="effect_image" name="effect_image" value="money_2.png"
                                               onclick="AdminForm.imageShape(this.value)"
                                            <?=$item['effect_image'] == 'money_2.png' ? ' checked="checked"' : ''?>>
                                        <img width="30" src="<?=$imagePath?>/thumbnail/money_2.png" />
                                    </label>
                                </td>
                                <td>
                                    <label>
                                        <input type="radio" class="effect_image" name="effect_image" value="money_3.png"
                                               onclick="AdminForm.imageShape(this.value)"
                                            <?=$item['effect_image'] == 'money_3.png' ? ' checked="checked"' : ''?>>
                                        <img width="30" src="<?=$imagePath?>/thumbnail/money_3.png" />
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                </table>
                <div class="form-inline">
                    <label><input type="radio" name="image_type" value="custom" onclick="AdminForm.imageType(this.value)"
                        <?=$item['image_type'] == 'custom' ? ' checked="checked"' : ''?>>&nbsp;이미지 직접 등록&nbsp;&nbsp;&nbsp;</label>
                    <input id="effect_image_custom" name="effect_image_custom" type="file" accept="image/*"
                           onchange="ImageModule.imageCustom(this)" style="margin-left: 20px">(권장 사이즈 40X40px)
                    <div id="image-preview-layer">
                        <div class="image-preview-frame">
                            <img id="image-preview" onload="ImageModule.details(this)">
                        </div>
                        <div class="image-preview-details">
                            <ul>
                                <li>
                                    <span>크기 : </span><span id="image-preview-dimensions"></span>
                                </li>
                                <li>
                                    <span>용량 : </span><span id="image-preview-size"></span>
                                </li>
                                <li>
                                    <span>형식 : </span><span id="image-preview-type"></span>
                                </li>
                            </ul>
                        </div>
                        <div style="margin-bottom: 10px; clear: both"></div>
                    </div>
                    <p class="notice-info">등록된 이미지는 비율 유지하여 사이즈 40X40px로 자동 리사이징 됩니다.</p>
                    <p class="notice-info">100KB 이하의 JPG, GIF, PNG 확장자의 이미지 파일만 등록 가능합니다.</p>
                    <p class="notice-info">이미지 직접 등록 시 효과 종류 미리 보기는 제한됩니다.</p>
                </div>
            </td>
        </tr>
        <tr>
            <th>효과 속도 설정 </th>
            <td>
                <label class="col">
                    <input type="radio" name="effect_speed" onclick="AdminForm.setSpeed(this.value)" value="1"
                        <?=$item['effect_speed'] == '1' ? ' checked="checked"' : ''?>>&nbsp;매우 느리게
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_speed" onclick="AdminForm.setSpeed(this.value)" value="2"
                        <?=$item['effect_speed'] == '2' ? ' checked="checked"' : ''?>>&nbsp;약간 느리게
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_speed" onclick="AdminForm.setSpeed(this.value)" value="3"
                        <?=!$item['effect_speed'] || $item['effect_speed'] == '3' ? ' checked="checked"' : ''?>>&nbsp;보통
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_speed" onclick="AdminForm.setSpeed(this.value)" value="4"
                        <?=$item['effect_speed'] == '4' ? ' checked="checked"' : ''?>>&nbsp;약간 빠르게
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_speed" onclick="AdminForm.setSpeed(this.value)" value="5"
                        <?=$item['effect_speed'] == '5' ? ' checked="checked"' : ''?>>&nbsp;매우 빠르게
                </label>
            </td>
        </tr>
        <tr>
            <th>효과 양 설정</th>
            <td>
                <label class="col">
                    <input type="radio" name="effect_amount" onclick="AdminForm.setFlakeCount(this.value)" value="1"
                        <?=$item['effect_amount'] == '1' ? ' checked="checked"' : ''?>>&nbsp;매우 조금
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_amount" onclick="AdminForm.setFlakeCount(this.value)" value="2"
                        <?=$item['effect_amount'] == '2' ? ' checked="checked"' : ''?>>&nbsp;약간 조금
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_amount" onclick="AdminForm.setFlakeCount(this.value)" value="3"
                        <?=!$item['effect_amount'] || $item['effect_amount'] == '3' ? ' checked="checked"' : ''?>>&nbsp;보통
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_amount" onclick="AdminForm.setFlakeCount(this.value)" value="4"
                        <?=$item['effect_amount'] == '4' ? ' checked="checked"' : ''?>>&nbsp;약간 많음
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_amount" onclick="AdminForm.setFlakeCount(this.value)" value="5"
                        <?=$item['effect_amount'] == '5' ? ' checked="checked"' : ''?>>&nbsp;매우 많음
                </label>
            </td>
        </tr>
        <tr>
            <th>효과 투명도 설정</th>
            <td>
                <label class="col">
                    <input type="radio" name="effect_opacity" onclick="AdminForm.setOpacity(this.value)" value="20"
                        <?=$item['effect_opacity'] == 20 ? ' checked="checked"' : ''?>>&nbsp;20%
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_opacity" onclick="AdminForm.setOpacity(this.value)" value="40"
                        <?=$item['effect_opacity'] == 40 ? ' checked="checked"' : ''?>>&nbsp;40%
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_opacity" onclick="AdminForm.setOpacity(this.value)" value="60"
                        <?=$item['effect_opacity'] == 60 ? ' checked="checked"' : ''?>>&nbsp;60%
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_opacity" onclick="AdminForm.setOpacity(this.value)" value="80"
                        <?=$item['effect_opacity'] == 80 ? ' checked="checked"' : ''?>>&nbsp;80%
                </label>&nbsp;
                <label class="col">
                    <input type="radio" name="effect_opacity" onclick="AdminForm.setOpacity(this.value)" value="100"
                        <?=!$item['effect_opacity'] || $item['effect_opacity'] == 100 ? ' checked="checked"' : ''?>>
                    100%
                </label>
                <p class="notice-info">퍼센트 숫자가 높을수록 투명도는 낮습니다. EX) 100%는 투명도 없음</p>
                <p class="notice-info">팝콘 효과에는 투명도가 적용되지 않습니다.</p>
            </td>
        </tr>
        </tbody>
        <tfoot>
        <tr>
            <td style="border: 0px solid white"  colspan="100%">
                <div style="float: left;: left"> - 화면 효과 등록 후 효과를 적용할 페이지에 삽입할 수 있는 치환코드가 생성되며 화면효과 리스트에서 확인 가능합니다.</div>
            </td>
        </tr>
        </tfoot>
    </table>
</form>

<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/screeneffect/snowfall.js"></script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/screeneffect/snowfallWrapper.js"></script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/screeneffect/popcorn.js"></script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/screeneffect/popcornWrapper.js"></script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/screeneffect/regist.js"></script>
<script type="text/javascript">
$(document).ready(function () {
    AdminForm.init({
        commonImages: '<?=$imagePath?>/effect/',
        effectImage: '<?=$item['effect_image']?>'
    });
});
</script>
