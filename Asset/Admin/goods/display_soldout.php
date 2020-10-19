<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $("#frmGoods").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
                return false;
            },
            rules: {
                pc_soldout_overlay : {
                    required : function(){
                        return $("input[name='pc[soldout_overlay]'][value=custom]").is(':checked') && $('input[name=isPcCustomImage]').val() == 'n';
                    }
                },
                mobile_soldout_overlay : {
                    required : function(){
                        return $("input[name='mobile[soldout_overlay]'][value=custom]").is(':checked') && $('input[name=isMobileCustomImage]').val() == 'n';
                    }
                },
            },
            messages: {
                pc_soldout_overlay : {
                    required : "사용자 이미지를 업로드해주세요.",
                },
                mobile_soldout_overlay : {
                    required : "모바일 사용자 이미지를 업로드해주세요.",
                }
            }
        });

        <?php if(gd_isset($checked['pc']['soldout_icon']['custom']))  { ?> display_switch('div_pc_icon', 'show');<?php } ?>
        <?php if(gd_isset($checked['pc']['soldout_price']['custom']))  { ?> display_switch('div_pc_price_icon', 'show');  <?php } ?>
        <?php if(gd_isset($checked['pc']['soldout_price']['text']))  { ?> display_switch('div_pc_price_text', 'show');  <?php } ?>

        <?php if(gd_isset($checked['mobile']['soldout_icon']['custom']))  { ?> display_switch('div_mobile_icon', 'show');<?php } ?>
        <?php if(gd_isset($checked['mobile']['soldout_price']['custom']))  { ?> display_switch('div_mobile_price_icon', 'show');  <?php } ?>
        <?php if(gd_isset($checked['mobile']['soldout_price']['text']))  { ?> display_switch('div_mobile_price_text', 'show');  <?php } ?>

        // maxlength의 경우 display none으로 되어있으면 정상작동 하지 않는다 따라서 페이지 로딩 후 maxlength가 적용된 후 display none으로 강제 처리 (임시방편 처리)
        setTimeout(function () {
            $('#frmGoods').find('input[name="pc[soldout_price_text]"]').next('span.bootstrap-maxlength').css({top: '1px', left: '170px'});
            $('#frmGoods').find('input[name="mobile[soldout_price_text]"]').next('span.bootstrap-maxlength').css({top: '1px', left: '170px'});

        }, 1000);

    });

    function display_switch(prefix, state) {
        if (state == 'show')  $('.' + prefix).show();
        else $('.' + prefix).hide();
    }
    //-->
</script>
<form id="frmGoods" name="frmGoods" action="./display_ps.php" method="post"
      enctype="multipart/form-data">
    <input type="hidden" name="mode" value="soldout_register"/>
    <input type="hidden" name="isMobile" value="<?= $isMobile ?>"/>

    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?> </h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>

        </div>
    </div>


    <div class="table-title gd-help-manual">
        PC 품절상품 표시 설정
    </div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th class="require">상품 이미지 <br/>오버레이</th>
                <td>

                    <table border="0" cellpadding="5">
                        <tr>
                            <td align="center" class="noline width-md" style="border:0px;">
                                <div style="width:130px;height:130px; margin-bottom:10px;border:1px solid #CCCCCC;padding-top:55px">사용안함</div>
                                <input type="radio" name="pc[soldout_overlay]" value="0"
                                       onclick="display_switch('div_pc_overlay','hide')" <?= gd_isset($checked['pc']['soldout_overlay'][0]); ?> >
                            </td>
                            <?php
                            // 기본 제공 아이콘
                            for ($i = 1; $i <= 7; $i++) {
                                ?>
                                <td align="center" class="noline width-md" style="border:0px;">
                                    <div
                                        style="width:130px;height:130px; margin-bottom:10px; background:url('/data/icon/goods_icon/soldout-<?= $i ?>.png') no-repeat center center;border:1px solid #CCCCCC;"></div>
                                    <input type="radio" name="pc[soldout_overlay]" value="<?= $i ?>"
                                           onclick="display_switch('div_pc_overlay','hide')" <?= gd_isset($checked['pc']['soldout_overlay'][$i]); ?> >
                                </td>
                            <?php } ?>
                            <td align="center" class="noline width-md" style="border:0px;">
                                <?php  if($data['pc']['soldout_overlay_custom_exists'] == 'y'){?>
                                <div
                                    style="width:130px;height:130px; margin-bottom:10px; background:url('<?=$data['pc']['defaultCustomSoldoutOverlayPath']?>') no-repeat center center;border:1px solid #CCCCCC;"
                                    id="el-user-soldout-overlay">
                                <?php } else {?>
                                    <div style="width:130px;height:130px;padding-top:55px; margin-bottom:10px; border:1px solid #CCCCCC;" id="el-user-soldout-overlay">
                                        사용자 설정
                                <?php } ?>
                                </div>
                                <div style="position: relative">
                                  <input type="radio" name="pc[soldout_overlay]" value="custom"
                                       onclick="display_switch('div_pc_overlay','show')" <?= gd_isset($checked['pc']['soldout_overlay']['custom']); ?> >
                                <?php  if($data['pc']['soldout_overlay_custom_exists'] == 'y'){?>
                                    <div style="position:absolute;left:90px;top:-3px">(<label class="checkbox-inline"><span class="text-red">삭제</span><input type="checkbox" name="pc[deleteOverlayCustomImage]" value="y" ></label>)</div>
                                <?php }?>
                                 </div>
                            </td>
                    </table>

                    <div class="div_pc_overlay form-inline" <?php if ($data['pc']['soldout_overlay'] != 'custom') { ?> style="display:none;" <?php } ?>>
                        <br/>
                        사용자 이미지 <input type="file" name="pc_soldout_overlay" class="form-control"> <span>(권장 사이즈 : 300px)</span>
                        <input type="hidden" name="isPcCustomImage" value="<?=$data['pc']['soldout_overlay_custom_exists']?>" />
                        <div class="notice-ref notice-sm">* 사용자 이미지 업로드시 반드시 배경이 투명처리된 png, gif 파일로 업로드 하셔야 합니다.</div>

                    </div>
                </td>
            </tr>

            <tr>
                <th class="require">품절 아이콘</th>
                <td>
                    <div class="form-inline mgt5">
                        <label class="radio-inline">
                            <input type="radio" name="pc[soldout_icon]" value="disable"
                                   onclick="display_switch('div_pc_icon','hide')" <?= gd_isset($checked['pc']['soldout_icon']['disable']); ?> >
                            사용안함
                        </label>
                    </div>
                    <div class="form-inline mgt5">
                        <label class="radio-inline"><input type="radio" name="pc[soldout_icon]" value="basic"
                                                           onclick="display_switch('div_pc_icon','hide')" <?= gd_isset($checked['pc']['soldout_icon']['basic']); ?> >
                            기본 아이콘 <img src="<?= UserFilePath::icon('goods_icon')->www() ?>/icon_soldout.gif">
                        </label>
                    </div>
                    <div class="form-inline mgt5">
                        <label class="radio-inline">
                            <input type="radio" name="pc[soldout_icon]" value="custom" <?= gd_isset($checked['pc']['soldout_icon']['custom']); ?>
                                   onclick="display_switch('div_pc_icon','show')"> 대체 아이콘 <span class="div_pc_icon display-none">
                        </label>
                        <input type="file" name="pc_soldout_icon" class="form-control">
                        <?php if ($data['pc']['soldout_icon'] == 'custom' && FileHandler::isExists(UserFilePath::icon('goods_icon')->getBasepath().$data['pc']['soldout_icon_img'])) { ?>
                            <img src="<?= $data['pc']['soldout_icon_img'] ?>">
                        <?php } ?>
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">가격 표시 설정</th>
                <td>
                    <div><label class="radio-inline"><input type="radio" name="pc[soldout_price]" value="price" <?= gd_isset($checked['pc']['soldout_price']['price']); ?>
                                                            onclick="display_switch('div_pc_price','hide')"> 가격 표시</div>
                    </label>

                    <div class="form-inline mgt5"><label class="radio-inline"><input type="radio" name="pc[soldout_price]"
                                                                                     value="text" <?= gd_isset($checked['pc']['soldout_price']['text']); ?>
                                                                                     onclick="display_switch('div_pc_price','hide');display_switch('div_pc_price_text','show')""> 가격
                            대체 문구 <span class="div_pc_price div_pc_price_text display-none"><input type="text" name="pc[soldout_price_text]" class="form-control js-maxlength"
                                                                                                   value="<?= gd_isset($data['pc']['soldout_price_text']) ?>" maxlength="30"></span></label>
                    </div>
                    <div class="form-inline mgt5"><label class="radio-inline"><input type="radio" name="pc[soldout_price]"
                                                                                     value="custom" <?= gd_isset($checked['pc']['soldout_price']['custom']); ?>
                                                                                     onclick="display_switch('div_pc_price','hide');display_switch('div_pc_price_icon','show')"">이미지
                            노출  <span class="display-none div_pc_price div_pc_price_icon">
                    <input type="file" name="pc_soldout_price" class="form-control">
                                <?php if ($data['pc']['soldout_price'] == 'custom' && FileHandler::isExists(UserFilePath::icon('goods_icon')->getBasepath().$data['pc']['soldout_price_img'])) { ?>
                                    <img src="<?= $data['pc']['soldout_price_img'] ?>">
                                <?php } ?>
                </span></label></div>

                </td>
            </tr>
        </table>
    </div>


    <div class="table-title gd-help-manual">
        모바일 품절상품 표시 설정
    </div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th class="require">상품 이미지 <br/>오버레이</th>
                <td>

                    <table border="0" style="" cellpadding="5">
                        <tr>
                            <td align="center" class="noline width-md" style="border:0px;">
                                <div style="width:130px;height:130px; margin-bottom:10px;border:1px solid #CCCCCC;padding-top:55px">사용안함</div>
                                <input type="radio" name="mobile[soldout_overlay]" value="0"
                                       onclick="display_switch('div_mobile_overlay','hide')" <?= gd_isset($checked['mobile']['soldout_overlay'][0]); ?> >
                            </td>
                            <?php
                            // 기본 제공 아이콘
                            for ($i = 1; $i <= 7; $i++) {
                                ?>
                                <td align="center" class="noline width-md" style="border:0px;">
                                    <div
                                        style="width:130px;height:130px; margin-bottom:10px; background:url(<?= UserFilePath::icon('goods_icon')->www() ?>/<?php if ($isMobile) { ?>m-<?php } ?>soldout-<?= $i ?>.png) no-repeat center center;<?php if ($isMobile) { ?>background-size:100%;<?php } ?>border:1px solid #CCCCCC;"></div>
                                    <input type="radio" name="mobile[soldout_overlay]" value="<?= $i ?>"
                                           onclick="display_switch('div_mobile_overlay','hide')" <?= gd_isset($checked['mobile']['soldout_overlay'][$i]); ?> >
                                </td>
                            <?php } ?>
                            <td align="center" class="noline width-md" style="border:0px;">
                                <?php  if($data['mobile']['soldout_overlay_custom_exists'] == 'y'){?>
                                <div
                                    style="width:130px;height:130px; margin-bottom:10px; background:url(<?=$data['mobile']['defaultCustomSoldoutOverlayPath']?>) no-repeat center center;border:1px solid #CCCCCC;"
                                    id="el-user-soldout-overlay">
                                    <?php } else {?>
                                    <div style="width:130px;height:130px;padding-top:55px; margin-bottom:10px; border:1px solid #CCCCCC;" id="el-user-soldout-overlay">
                                        사용자 설정
                                    <?php }?>
                                </div>
                                    <div style="position: relative">
                                <input type="radio" name="mobile[soldout_overlay]" value="custom"
                                       onclick="display_switch('div_mobile_overlay','show')" <?= gd_isset($checked['mobile']['soldout_overlay']['custom']); ?> >
                                <?php  if($data['mobile']['soldout_overlay_custom_exists'] == 'y'){?>
                                        <div style="position:absolute;left:90px;top:-3px">(<label class="checkbox-inline"><span class="text-red">삭제</span><input type="checkbox" name="mobile[deleteOverlayCustomImage]" value="y" ></label>)</div>
                                    <?php }?>
                                    </div>
                            </td>
                    </table>

                    <div class="div_mobile_overlay form-inline" style="display:none;">
                        <br/>
                        사용자 이미지 <input type="file" name="mobile_soldout_overlay" class="form-control"> <span>(권장 사이즈 : 300px)</span>
                        <input type="hidden" name="isMobileCustomImage" value="<?=$data['mobile']['soldout_overlay_custom_exists']?>" />
                        <div class="notice-ref notice-sm">* 사용자 이미지 업로드시 반드시 배경이 투명처리된 png, gif 파일로 업로드 하셔야 합니다.</div>

                    </div>
                </td>
            </tr>

            <tr>
                <th class="require">품절 아이콘</th>
                <td>
                    <div class="form-inline mgt5">
                        <label class="radio-inline">
                            <input type="radio" name="mobile[soldout_icon]" value="disable"
                                   onclick="display_switch('div_mobile_icon','hide')" <?= gd_isset($checked['mobile']['soldout_icon']['disable']); ?> >
                            사용안함
                        </label>
                    </div>
                    <div class="form-inline mgt5">
                        <label class="radio-inline">
                            <input type="radio" name="mobile[soldout_icon]" value="basic" onclick="display_switch('div_mobile_icon','hide')" <?= gd_isset($checked['mobile']['soldout_icon']['basic']); ?> > 기본 아이콘
                            <img src="<?= UserFilePath::icon('goods_icon')->www() ?>/icon_soldout.gif"></label>
                    </div>
                    <div class="form-inline mgt5">
                        <label class="radio-inline">
                            <input type="radio" name="mobile[soldout_icon]" value="custom" <?= gd_isset($checked['mobile']['soldout_icon']['custom']); ?>
                                   onclick="display_switch('div_mobile_icon','show')"> 대체 아이콘
                            <span class="div_mobile_icon display-none">
                            <input type="file" name="mobile_soldout_icon" class="form-control">
                                <?php if ($data['mobile']['soldout_icon'] == 'custom' && FileHandler::isExists(UserFilePath::icon('goods_icon')->getBasepath().$data['mobile']['soldout_icon_img'])) { ?>
                                    <img src="<?= $data['mobile']['soldout_icon_img'] ?>">
                                <?php } ?>
                            </span>
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">가격 표시 설정</th>
                <td>
                    <div><label class="radio-inline"><input type="radio" name="mobile[soldout_price]" value="price" <?= gd_isset($checked['mobile']['soldout_price']['price']); ?>
                                                            onclick="display_switch('div_mobile_price','hide')"> 가격 표시</label></div>

                    <div class="form-inline mgt5"><label class="radio-inline"><input type="radio" name="mobile[soldout_price]"
                                                                                     value="text" <?= gd_isset($checked['mobile']['soldout_price']['text']); ?>
                                                                                     onclick="display_switch('div_mobile_price','hide');display_switch('div_mobile_price_text','show')"">
                            가격 대체 문구 <span class="div_mobile_price div_mobile_price_text display-none"><input type="text" class="form-control js-maxlength"
                                                                                                              name="mobile[soldout_price_text]"
                                                                                                              value="<?= gd_isset($data['mobile']['soldout_price_text']) ?>"
                                                                                                              maxlength="30"></span></label></div>
                    <div class="form-inline mgt5"><label class="radio-inline"><input type="radio" name="mobile[soldout_price]"
                                                                                     value="custom" <?= gd_isset($checked['mobile']['soldout_price']['custom']); ?>
                                                                                     onclick="display_switch('div_mobile_price','hide');display_switch('div_mobile_price_icon','show')"">이미지
                            노출  <span class="display-none div_mobile_price div_mobile_price_icon">
                                <input type="file" name="mobile_soldout_price" class="form-control">
                     <?php if ($data['mobile']['soldout_price'] == 'custom' && FileHandler::isExists(UserFilePath::icon('goods_icon')->getBasepath().$data['mobile']['soldout_price_img'])) { ?>
                         <img src="<?= $data['mobile']['soldout_price_img'] ?>">
                     <?php } ?>
                                </span>
                        </label>
                    </div>
                </td>
            </tr>
        </table>
    </div>


</form>
