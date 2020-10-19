<script type="text/javascript">
    <!--
    $(document).ready(function () {

        //custom validation rule - text only
        $.validator.addMethod(
            'optionCheck', function (value, element) {
                var result = true;

                var type = $('input[name="optionType"]:checked').val();

                if (type == '0') {
                    return true;
                } else {
                    $('#tbl_multi_option  input[name*="optionNm["]').each(function () {
                        if ($(this).val() == '')  result = false;
                        ;
                    });
                }

                return result;

            }, '옵션명을 입력해주세요.'
        );

        $("#frmGoods").validate({
            submitHandler: function (form) {
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                form.target = 'ifrmProcess';
                form.submit();
            },
            // onclick: false, // <-- add this option
            rules: {
                goodsNm: {
                    required: true
                },
                mode: {
                    optionCheck: true
                }
            },
            messages: {
                goodsNm: {
                    required: "상품명을 입력하세요."
                }
            }
        });


        <?php

            if ($data['taxFreeFl'] == 'f') {
                echo '	disabled_switch(\'taxPercent\',true);'.chr(10);
            }

         ?>

        <?php if(($data['mode'] =='register' &&  Request::get()->get('scmFl')) ||  $data['mode'] =='modify') { ?>
        $('input:radio[name=scmFl]').prop("disabled", true);
        $('button.scmBtn').attr("disabled", true);
        <?php }?>

        $('#imageStorage').trigger('change');

        <?php if ($gGlobal['isUse'] === true) { ?>
        $(".js-global-name  input:checkbox").click(function () {
            var globalName = $(this).closest("tr").find("input[type='text']");
            if($(this).is(":checked")) {
                var gloablNameText = $(globalName).val();
                if(gloablNameText) $(globalName).data('global-name',gloablNameText);
                $(globalName).val('');
                $(globalName).prop('disabled',true);
            } else {
                var gloablNameOriText = $(globalName).data('global-name');
                if(gloablNameOriText) $(globalName).val(gloablNameOriText);
                $(globalName).prop('disabled',false);
            }
        });
        <?php } ?>
    });


    /**
     * disabled 여부
     *
     * @param string  inputName 해당 input Box의 name
     * @param boolean modeBool 출력 여부 (true or false)
     */
    function disabled_switch(inputName, modeBool) {
        $('input[name=\'' + inputName + '\']').prop('disabled', modeBool);
    }


    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            addParam['callFunc'] = 'setScmSelect';
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }

    /**
     * 공급사 선택 후 세팅
     *
     * @param object data 공급사 내용
     */
    function setScmSelect(data) {
        //공급사 값 세팅
        displayTemplate(data);
        //수수료 세팅
        $('input[name="commission"]').val(data.info[0].scmCommission);

    }


    /**
     * 이미지 저장소에 따른 상품 이미지 종류
     *
     * @param string modeType 이미지 저장소 종류
     */
    function image_storage_selector(storageName) {
        $('span[id*=\'imageStorageMode_\']').removeClass();
        <?php  if ($data['mode'] == "register") {?>
        var addPath = '코드1/코드2/코드3/상품코드/';
        <?php }
        else {?>
        var addPath = '<?=$data['imagePath']?>';
        <?php }?>
        $('.imageStorageText').html($('#imageStorage option:selected').text());
        $.post("goods_ps.php", {mode: "getStorage",type: "add_goods", storage: storageName })
            .done(function (data) {
                $("#imageStorageModeNm").html(data+addPath);
            });

        image_storage_selector_option();

      /*  $('span[id*=\'imageStorageMode_\']').removeClass();

        if (modeType == 'url') {
            $('#imageStorageMode_url').addClass('display-block');
            $('#imageStorageMode_local').addClass('display-none');
            $('#imageStorageMode_etc').addClass('display-none');
            $('#imageStorageMode_none').addClass('display-none');
        } else if (modeType == 'local') {
            $('#imageStorageMode_url').addClass('display-none');
            $('#imageStorageMode_local').addClass('display-block');
            $('#imageStorageMode_etc').addClass('display-none');
            $('#imageStorageMode_none').addClass('display-none');
        } else if (modeType == '') {
            $('#imageStorageMode_url').addClass('display-none');
            $('#imageStorageMode_local').addClass('display-block');
            $('#imageStorageMode_etc').addClass('display-none');
            $('#imageStorageMode_none').addClass('display-block');
        } else {
            $('#imageStorageMode_url').addClass('display-none');
            $('#imageStorageMode_local').addClass('display-none');
            $('#imageStorageMode_etc').addClass('display-block');
            $('#imageStorageMode_none').addClass('display-none');
            $('#imageStorageModeNm').html(modeType);
        }*/

        image_storage_selector_option();
    }

    /**
     * 이미지 저장소에 따른 상품 옵션 추가노출 이미지 종류
     *
     * @param string modeType 이미지 저장소 종류
     */
    function image_storage_selector_option() {
        $(".cla_image_filed").attr("type", "file");
    }

    function change_option(showOption, hideOption) {
        $("#" + showOption).show();
        $("#" + hideOption).hide();
    }


    function set_opotion() {
        $('#tbl_multi_option .cla_option_info').remove();

        var optionStr = $('input[name=\'optionStr\']').val();

        var option_arr = optionStr.split(',');

        if(option_arr.length > 50 ) {
            alert('옵션 복수 등록은 50개 상품까지 가능합니다.');
            return false;
        }

        for (var i = 0; i < option_arr.length; i++) {
            if (option_arr[i]) {

                var addHtml = '<tr class="cla_option_info" id="tblAddOptionTr' + i + '"><td><input type="checkbox" name="chkOption" value="' + i + '"></td>';
                addHtml += '<td id="tblAddOptionNumber' + i + '">' + (i + 1) + '</td>';
                addHtml += '<td><input type="text" name="optionNm[' + i + ']"  class="form-control " value="' + option_arr[i] + '"/></td>';
                addHtml += '<td><div class="form-inline"> <?=gd_currency_symbol(); ?><input type="text" name="goodsPrice[' + i + ']"  class="width80p form-control" value="<?=gd_money_format($data['goodsPrice'], false); ?>"/><?=gd_currency_string(); ?></div></td>';
                addHtml += '<td><div class="form-inline"> <?=gd_currency_symbol(); ?><input type="text" name="costPrice[' + i + ']"  class="width80p form-control" value="<?=gd_money_format($data['costPrice'], false); ?>"/><?=gd_currency_string(); ?></div></td>';
                addHtml += '<td style="white-space:nowrap"><div class="form-inline"> <label class="radio-inline"><input type="radio" name="stockUseFl[' + i + ']"  value="0" checked onclick="disabled_switch(\'stockCnt[' + i + ']\',true)"/>제한없음</label>';
                addHtml += '<label class="radio-inline"><input type="radio" name="stockUseFl[' + i + ']" value="1"  onclick="disabled_switch(\'stockCnt[' + i + ']\',false)"/><input type="text" name="stockCnt[' + i + ']"  class="form-control width-2xs"  id="stockCnt' + i + '"  disabled="true"/></label></div></td>';
                addHtml += '<td><input type="text" name="goodsCd[' + i + ']"  class="form-control width60p js-maxlength" maxlength="30" /></td>';
                addHtml += '<td><div class="form-inline"><input type="file" name="imageNm[' + i + ']"  class="form-control  cla_image_filed"/></div></td>';
                addHtml += '<td class="center"><select class="form-control"  name="viewFl[' + i + ']"><option value="y">노출함</optiton><option value="n" >노출안함</optiton></select></td>';
                addHtml += '<td class="center"><select class="form-control"  name="soldOutFl[' + i + ']"><option value="n" >정상</optiton><option value="y" >품절</optiton></select></td></tr>';


                $('#tbl_multi_option > tbody:last-child').append(addHtml);
            }
        }

        $("input.js-type-normal").bind('keyup', function () {
            $(this).val($(this).val().replace(/[^a-z0-9_]*/gi, ''));
        });

        $('#lay_multi_option input[maxlength]').maxlength({
            showOnReady: true,
            alwaysShow: true
        });

        $('#lay_multi_option textarea[maxlength]').maxlength({
            placement: 'top-right-inside',
            showOnReady: true,
            alwaysShow: true
        });

        init_file_style();

    }

    function delete_option() {

        var chkCnt = $('input[name=chkOption]:checked').length;
        if (chkCnt == 0) {
            alert('선택된 추가상품이 없습니다.');
            return;
        }

        dialog_confirm('선택한 ' + chkCnt + '개 추가상품을 삭제하시겠습니까?', function (result) {
            if (result) {
                $("input[name=chkOption]:checked").each(function () {
                    field_remove('tblAddOptionTr' + $(this).val());
                });

                $('td[id*=\'tblAddOptionNumber\']').each(function (key) {
                    $(this).html(key + 1);
                });
            }
        });

    }

    function setAddGoods(addGoodsList) {

        var addGoodsHtml = addGoodsList.replace(/display-none/g, "");

        $("#tbl_add_goods_tr_none", opener.document).remove();
        $("#tbl_add_goods_set", opener.document).append(addGoodsHtml);

        $("td[id^='addGoodsNumber_']", opener.document).each(function (i) {
            $(this).html(i + 1);
        });
    }

    /**
     * 팝업등록인경우
     *
     * @param string modeStr 사은품 모드
     */
    function popup_submit() {

        $('input[name=\'mode\']').val('<?=$data['mode']; ?>_ajax');

        var data = new FormData($("#frmGoods")[0]);

        $.ajax({
            type: 'POST',
            data: data,
            url: 'add_goods_ps.php',
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {

                var addGoods = $.parseJSON(data);

                opener.parent.setAddGoods(addGoods);

                BootstrapDialog.show({
                    type: BootstrapDialog.TYPE_PRIMARY,
                    message: addGoods.msg,
                    onshown: function (dialogRef) {
                        setTimeout(function () {
                            dialogRef.close();
                            self.close();
                        }, 1000);
                    }
                });
            }
        });

    }

    function reset_scm() {
        $('#scmLayer').html('');
        $('input[name="commission"]').val('0');
    }


    //-->
</script>
<form id="frmGoods" name="frmGoods" action="./add_goods_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?=$data['mode']; ?>"/>
    <input type="hidden" name="addGroup" value="<?= $addGroup ?>"/>
    <?php if ($data['mode'] == 'modify') { ?>
        <input type="hidden" name="addGoodsNo" value="<?=gd_isset($data['addGoodsNo']); ?>" />
        <input type="hidden" name="applyFl" value="<?=$data['applyFl'];?>" />
    <?php } ?>

    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./add_goods_list.php');" />
            <?php if(gd_is_provider() && $data['applyFl'] =='a') { ?>
                <input type="button" value="승인처리 진행 중" class="btn btn-red"  />
            <?php } else { ?>
                <?php if ($addGroup && Request::get()->get('popupMode') == 'yes') { ?>
                    <input type="button" value="저장" class="btn btn-red" onclick="popup_submit()"/>
                <?php } else { ?>
                    <input type="submit" value="저장" class="btn btn-red"/>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        기본정보
    </div>
        <?php if(gd_is_provider()) { ?>
            <input type="hidden" name="scmNo" value="<?=$data['scmNo']?>">
        <?php } ?>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <?php if(gd_use_provider()) { ?>
                <?php if(gd_is_provider()) { ?>
                    <input type="hidden" name="scmNo" value="<?=$data['scmNo']?>">
                <?php }  else { ?>
                    <tr>
                <th>공급사 구분</th>
                <td <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === false) { ?>colspan="3"<?php } ?>>
                    <label class="radio-inline"><input type="radio" name="scmFl"
                                  value="n" <?=gd_isset($checked['scmFl']['n']); ?> onclick="reset_scm()" />본사</label>
                    <label class="radio-inline"><input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?>
                                  onclick="layer_register('scm','radio',true)"/>공급사</label>
                    <label >
                        <button type="button" class="btn btn-sm btn-gray scmBtn" onclick="layer_register('scm','radio',true)">공급사 선택</button>
                    </label>

                        <div id="scmLayer" class="selected-btn-group <?=$data['scmNoNm'] && $data['scmNo'] != DEFAULT_CODE_SCMNO ? 'active' : ''?>">
                        <?php if ($data['scmNo']) { ?>
                            <h5>선택된 공급사 : </h5>
                            <span id="info_scm_<?= $data['scmNo'] ?>" class="btn-group btn-group-xs">
							<input type="hidden" name="scmNo" value="<?= $data['scmNo'] ?>"/>
                                <input type="hidden" name="scmNoNm" value="<?= $data['scmNoNm'] ?>"/>
                                <?php if ($data['scmNo'] != DEFAULT_CODE_SCMNO) { ?>

                                    <span class="btn"><?= $data['scmNoNm'] ?></span>

                                    <?php if ($data['mode'] == 'register' && !Request::get()->get('scmFl')) { ?>
                                        <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#info_scm_<?= $data['scmNo'] ?>">삭제</button> <?php } ?>
                                <?php } ?>
					        </span>
                        <?php } ?>
                    </div>

                </td>
            <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) { ?>

                    <th>매입처</th>
                    <td class="input_area" <?php if(!gd_use_provider() || gd_is_provider()) {?>colspan="3"<?php } ?>>
                        <label><input type="text" name="purchaseNoNm" value="<?=$data['purchaseNoNm']; ?>"
                                      class="form-control"  onclick="layer_register('purchase', 'radio')" readonly/></label>
                        <label>
                            <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('purchase', 'radio')">매입처 선택</button>
                        </label>
                        <?php if (gd_is_provider() === false) { ?>
                            <a href="./purchase_register.php" target="_blank" class="btn btn-sm btn-white btn-icon-plus">매입처 추가</a>
                        <?php } ?>
                        <label id="purchaseNoDel" style="display:<?= $data['purchaseNoNm'] ? '':'none'; ?>"><input type="checkbox" name="purchaseNoDel" value="y"> <span class="text-red">체크시 삭제</span></label>
                        <div id="purchaseLayer" class="width100p">
                            <?php if ($data['purchaseNo']) { ?>
                                <span id="info_parchase_<?= $data['purchaseNo'] ?>" class="pull-left">
                        <input type="hidden" name="purchaseNo" value="<?= $data['purchaseNo'] ?>"/>
                        </span>
                            <?php } ?>
                        </div>
                    </td>
            <?php } ?>
            </tr>
            <?php } ?>
            <?php } ?>
            <tr>
                <th>수수료</th>
                <td>
                    <div class="form-inline"><label title=""><input type="text" name="commission" value="<?=gd_isset($data['commission']); ?>"
                                                                    class="form-control width-width-xs"/></label>%
                    </div>
                </td>
                <th>과세/면세</th>
                <td>
                    <div class="form-inline">
                        <label class="radio-inline" title="과세상품인 경우 &quot;과세&quot;를 선택후 과세율을 입력하세요!"><input type="radio" name="taxFreeFl" value="t" <?=gd_isset($checked['taxFreeFl']['t']);?> onclick="disabled_switch('taxPercent',false);" />과세</label>
                        <label title="과세율을 입력하세요">
                            <select  class="form-control" name="taxPercent" >
                                <?php foreach($conf['tax']['goodsTax'] as $k => $v) { ?>
                                    <?php if($v > 0 ) { ?><option value="<?=$v?>" <?php if($v == $data['taxPercent']) {  echo "selected"; } ?> ><?=$v?></option><?php } ?>
                                <?php } ?>
                            </select> <span class="align">%</span>
                        </label>
                        <label class="radio-inline mgl10" title="면세 상품인경우 &quot;면세&quot;를 선택하세요!"><input type="radio" name="taxFreeFl" value="f" <?=gd_isset($checked['taxFreeFl']['f']);?> onclick="disabled_switch('taxPercent',true);" />면세</label>
                </td>
            </tr>
            <tr>
                <th>상품코드</th>
                <td>
                    <?php if ($data['addGoodsNo']) { ?><?= $data['addGoodsNo'] ?> <label title=""><input type="hidden"
                                                                                                         name="addGoodsNo"
                                                                                                         value="<?=gd_isset($data['addGoodsNo']); ?>"/></label>
                    <?php } else {
                        echo '상품 등록 저장 시 자동 생성됩니다.';
                    } ?>
                </td>
                <th>모델번호</th>
                <td ><label title=""><input type="text" name="goodsModelNo"
                                                       value="<?=gd_isset($data['goodsModelNo']); ?>"
                                                       class="form-control width-lg js-maxlength" maxlength="30"/></label>
                </td>
            </tr>
            <?php if ($gGlobal['isUse'] === true) { ?>
            <tr>
                <th class="input_title r_space require">상품명</th>
                <td class="input_area" colspan="5">
                    <table class="table table-cols">
                        <colgroup>
                            <col class="width-md"/>
                            <col/>
                        </colgroup>
                        <tr>
                            <th>기준몰</th>
                            <td>
                                <label title=""><input type="text" name="goodsNm" value="<?=gd_isset($data['goodsNm']); ?>"
                                                       class="form-control width-3xl js-maxlength" maxlength="250"/></label>
                            </td>
                        </tr>
                        <tbody class="js-global-name">
                        <?php
                        foreach ($gGlobal['useMallList'] as $val) {
                            if ($val['standardFl'] == 'n') {
                                ?>
                                <tr>
                                    <th>
                                        <span class="js-popover flag flag-16 flag-<?= $val['domainFl']?>" data-content="<?=$val['mallName']?>"></span>
                                    </th>
                                    <td>
                                        <input type="text" name="globalData[<?= $val['sno'] ?>][goodsNm]" value="<?= $data['globalData'][ $val['sno'] ]['goodsNm']; ?>" class="form-control  width-2xl js-maxlength" maxlength="250" <?php if(empty($data['globalData'][ $val['sno'] ]['goodsNm'])) { ?>disabled="disabled" <?php } ?> data-global=''/>
                                        <div>
                                            <label class="checkbox-inline">
                                                <input type="checkbox" name="goodsNmGlobalFl[<?= $val['sno'] ?>]" value="y" <?= gd_isset($checked['goodsNmGlobalFl'][$val['sno']]); ?>> 기준몰 기본 상품명 공통사용
                                            </label>
                                            <a class="btn btn-sm btn-black js-translate-google" data-language="<?= $val['domainFl'] ?>" data-target-name="goodsNm">참고 번역</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php }
                        }?>
                        </tbody>
                    </table>

                </td>
            </tr>
            <?php } else { ?>
            <tr>
                <th class="input_title r_space require">상품명</th>
                <td class="input_area" colspan="5">
                    <label title=""><input type="text" name="goodsNm" value="<?=gd_isset($data['goodsNm']); ?>"
                                           class="form-control width-3xl js-maxlength" maxlength="250"/></label>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <th>브랜드</th>
                <td>
                    <label><input type="text" name="brandCdNm" value="<?=$data['brandCdNm']; ?>"
                                  class="form-control"  readonly onclick="layer_register('brand', 'radio')"/> </label>
                    <label><button type="button" class="btn btn-sm btn-gray" onclick="layer_register('brand', 'radio')">브랜드 선택</button></label>
                    <?php if (gd_is_provider() === false) { ?><a href="./category_tree.php?cateType=brand" target="_blank" class="btn btn-sm btn-white btn-icon-plus" >브랜드 추가</a><?php } ?>
                    <label id="brandCdDel" style="display:<?= $data['brandCdNm'] ? '':'none'; ?>"><input type="checkbox" name="brandCdDel" value="y"> <span class="text-red">체크시 삭제</span></label>

                    <div id="brandLayer" class="width100p">
                        <?php if ($data['brandCd']) { ?>
                            <span id="idbrand<?= $data['brandCd'] ?>" class="pull-left">
                        <input type="hidden" name="brandCd" value="<?= $data['brandCd'] ?>"/>
                        </span>
                        <?php } ?>
                    </div>
                </td>
                <th>제조사</th>
                <td>
                    <label title=""><input type="text" name="makerNm" value="<?=gd_isset($data['makerNm']); ?>"
                                           class="form-control width-lg js-maxlength" maxlength="30"/></label>
                </td>
            </tr>
            <tr>
                <th>상품설명</th>
                <td class="input_area" colspan="3">
                    <textarea name="goodsDescription" rows="3" style="width:100%; height:400px;" id="editor"
                              class="form-control"><?=$data['goodsDescription']; ?></textarea>

                </td>
            </tr>
        </table>



    <div class="table-title gd-help-manual">
        이미지 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
        </colgroup>
        <tr>
            <th>저장소 선택</th>
            <td>
                <div class="form-inline">
                    <?=gd_select_box('imageStorage', 'imageStorage', $conf['storage'], null, $data['imageStorage'], '=저장소 선택=', 'onchange="image_storage_selector(this.value);"'); ?></div>
            </td>
        </tr>
        <tr>
            <th>저장 경로</th>
            <td>
                <span id="imageStorageModeNm"></span>
                <input type="hidden" name="imagePath" value="<?=$data['imagePath']; ?>"/>
            </td>
        </tr>
    </table>


    <div class="table-title gd-help-manual">
        옵션 및 가격/재고 설정
    </div>


        <?php if ($data['mode'] == 'register') { ?>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-sm"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>등록방법 선택</th>
                    <td class="input_area" colspan="5">
                        <label class="radio-inline"><input type="radio" name="optionType" value="0"
                                      onclick="change_option('lay_option','lay_multi_option')" checked/>단일등록</label>
                        <label class="radio-inline"><input type="radio" name="optionType" value="1"
                                      onclick="change_option('lay_multi_option','lay_option')"/>복수등록</label>
                    </td>
                </tr>
            </table>
        <?php } ?>
        <div id="lay_option">
            <table class="table table-cols">
                <colgroup>
                    <col/>
                    <col/>
                    <col/>
                    <col/>
                    <col/>
                    <col/>
                    <col/>
                </colgroup>
                <tr>
                    <th>옵션값</th>
                    <th><img src="<?=PATH_ADMIN_GD_SHARE;?>img/bl_required.png" style="padding-right: 5px">판매가</th>
                    <th>매입가</th>
                    <th>재고</th>
                    <th>자체 상품코드</th>
                    <th>상품 이미지</th>
                    <th>노출상태</th>
                    <th>품절상태</th>
                </tr>
                <tr>
                    <td><input type="text" name="optionNm[]" class="form-control"
                               value="<?=gd_isset($data['optionNm']); ?>"/></td>
                    <td>
                        <div class="form-inline">
                            <?=gd_currency_symbol(); ?>
                            <input type="text" name="goodsPrice[]" value="<?=gd_money_format($data['goodsPrice'], false); ?>" class="form-control width80p" >
                            <?=gd_currency_string(); ?>
                        </div>
                    </td>
                    <td>
                        <div class="form-inline">
                            <?=gd_currency_symbol(); ?>
                            <input type="text" name="costPrice[]" value="<?=gd_money_format($data['costPrice'], false); ?>" class="form-control width80p" >
                            <?=gd_currency_string(); ?>
                        </div>
                    </td>
                    <td style="white-space:nowrap">
                        <div class="form-inline"><label class="radio-inline"><input type="radio" name="stockUseFl[]" value="0"
                                                               onclick="disabled_switch('stockCnt[]',true);" <?=gd_isset($checked['stockUseFl']['0']); ?> />제한없음</label>
                            <label class="radio-inline"><input type="radio" name="stockUseFl[]" value="1"
                                          onclick="disabled_switch('stockCnt[]',false);" <?=gd_isset($checked['stockUseFl']['1']); ?> /><input
                                type="text" name="stockCnt[]" id="stockCnt"
                                class="form-control width-2xs" <?php if ($data['stockUseFl'] == '0') {
                                echo "disabled='true'";
                            } ?> value="<?=gd_isset($data['stockCnt']); ?>"/></label></div>
                    </td>
                    <td><input type="text" name="goodsCd[]" class="form-control width60p js-maxlength"
                               value="<?=gd_isset($data['goodsCd']); ?>" maxlength="30"/></td>
                    <td>
                        <div class="form-inline">
                            <input type="file" name="imageNm[]" class="form-control  cla_image_filed"/>
                            <?php if ($data['imageNm']) { ?>
                                <input type="hidden" name="imageNm[]" value="<?= $data['imageNm'] ?>">
                                <?=gd_html_add_goods_image($data['addGoodsNo'], $data['imageNm'], $data['imagePath'], $data['imageStorage'], 30, $data['goodsNm'], '_blank'); ?> <?= $data['imageNm'] ?>
                            <?php } ?>
                        </div>
                    </td>
                    <td class="center"><select class="form-control" name="viewFl[]">
                            <option value="y" <?php if ($data['viewFl'] == 'y') {
                                echo "selected";
                            } ?>>노출함</option>
                            <option value="n" <?php if ($data['viewFl'] == 'n') {
                                echo "selected";
                            } ?>>노출안함</option>
                        </select></td>
                    <td class="center"><select class="form-control" name="soldOutFl[]">
                            <option value="y" <?php if ($data['soldOutFl'] == 'y') {
                                echo "selected";
                            } ?>>품절</option>
                            <option value="n" <?php if ($data['soldOutFl'] == 'n') {
                                echo "selected";
                            } ?>>정상</option>
                        </select></td>

                </tr>
            </table>
        </div>
        <div id="lay_multi_option" style="display:none">
            <table class="table table-cols" id="tbl_multi_option">
                <colgroup>
                    <col/>
                    <col/>
                    <col />
                    <col />
                    <col />
                    <col />
                    <col />
                    <col />
                    <col />
                </colgroup>
                <thead>
                <tr>
                    <th colspan="2">옵션값</th>
                    <td class="input_area" colspan="7">
                        <div class="form-inline">
                            <input type="text" name="optionStr" placeholder="콤마(,)로 구분 ex)빨강,파랑" class="form-control width-lg"/><label>&nbsp;&nbsp;<input type="button"
                                                                                                             value="적용"
                                                                                                             onclick="set_opotion()" class="btn btn-sm btn-grey"/></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><input type="checkbox" class="js-checkall" data-target-name="chkOption"></th>
                    <th>번호</th>
                    <th>옵션값</th>
                    <th>판매가</th>
                    <th>매입가</th>
                    <th>재고</th>
                    <th>자체상품코드</th>
                    <th>상품이미지</th>
                    <th>노출상태</th>
                    <th>품절상태</th>
                </tr>
                </thead>
                <tr class="cla_option_info">
                    <td colspan="9" class="no-data">등록된 추가상품이 없습니다.</td>
                </tr>

            </table>
            <div class="table-action">
            <div class="pull-left">
                <button class="checkDelete btn btn-white" type="button" onclick="delete_option()" >선택 삭제
                    </button>
            </div>
            </div>
            <br/>
        </div>



</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
