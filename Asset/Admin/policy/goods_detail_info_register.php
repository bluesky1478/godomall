<form id="frmGoodsInfo" name="frmGoodsInfo" action="goods_ps.php" method="post">
    <input type="hidden" name="mode" value="goods_info"/>
    <input type="hidden" name="informCd" value="<?php echo gd_isset($data['informCd']); ?>"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./goods_detail_info.php');"/>
            <?php if ($data['saveFl'] == 'y') { ?><input type="submit" value="저장" class="btn btn-red"><?php } ?>
        </div>
    </div>


    <div class="table-title gd-help-manual">
        상품 상세 이용안내 내용
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <?php if (gd_use_provider() === true) { ?>
            <input type="hidden" name="scmNo" value="<?= $data['scmNo'] ?>">

            <?php if (gd_is_provider() === false) { ?>
                <tr>
                    <th class="input_title r_space require">공급사 구분</th>
                    <td class="input_area">
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="n" <?php echo gd_isset($checked['scmFl']['n']); ?> <?php if ($data['informCd']) {
                                echo "disabled='true'";
                            } ?> />본사
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="y" <?php echo gd_isset($checked['scmFl']['y']); ?> onclick="layer_register('scm','radio')" <?php if ($data['informCd']) {
                                echo "disabled='true'";
                            }; ?>/>공급사
                        </label>
                        <?php
                        if ($data['informCd']) {
                            ?>
                            <input type="hidden" name="scmFl" value="<?=$data['scmFl'];?>">
                            <?php
                        }
                        ?>
                        <label>
                            <button type="button" class="btn btn-sm btn-gray scmBtn" onclick="layer_register('scm','radio')">공급사 선택</button>
                        </label>
                        <div id="scmLayer" class="selected-btn-group <?= $data['companyNm'] && $data['scmNo'] != DEFAULT_CODE_SCMNO ? 'active' : '' ?>">
                            <?php if ($data['scmNo']) { ?>
                            <?php if ($data['scmNo'] != DEFAULT_CODE_SCMNO) { ?><h5>선택된 공급사 : </h5> <?php } ?>
                            <span id="info_scm_<?= $data['scmNo'] ?>" class="btn-group btn-group-xs">
                            <input type="hidden" name="scmNo" value="<?= $data['scmNo'] ?>"/>
                            <input type="hidden" name="scmNoNm" value="<?= $data['companyNm'] ?>"/>
                                <?php if ($data['scmNo'] != DEFAULT_CODE_SCMNO) { ?>
                                <span class="btn"> <?= $data['companyNm'] ?></button>
                                    <?php if (empty($data['informCd'])) { ?>
                                        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $data['scmNo'] ?>">삭제</button>
                                    <?php } ?>
                                    <?php } ?>
                                </span>
                                <?php } ?>
                        </div>
                    </td>
                </tr>
            <?php } else { ?>
                <?php if ($data['saveFl'] == 'n') { ?>
                    <tr>
                        <th>공급사 구분</th>
                        <td><?= $data['scmNm'] ?></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        <?php } ?>
        <tr>
            <th class="input_title r_space require">이용안내 종류</th>
            <td>
                <?php
                $arrInfo = ['002' => '배송안내', '003' => 'AS안내', '004' => '환불안내', '005' => '교환안내'];
                if (empty($data['informCd'])) {
                    foreach ($arrInfo as $key => $val) {
                        echo '	<label class="radio-inline"><input type="radio" name="groupCd" value="' . $key . '" />' . $val . '</label>';
                    }
                } else {
                    echo '<input type="hidden" name="groupCd" value="' . gd_isset($data['groupCd']) . '" />' . $arrInfo[$data['groupCd']];
                }
                ?>
            </td>
        </tr>
        <tr>
            <th class="input_title r_space require">이용안내 제목</th>
            <td>
                <input type="text" name="informNm" value="<?php echo gd_isset($data['informNm']); ?>" class="form-control"/>
            </td>
        </tr>
        <tr>
            <th class="input_title r_space require">이용안내 내용</th>
            <td>
                <textarea name="content" rows="26" style="width:100%; height:400px;" id="editor" class="form-control"><?php echo gd_isset($data['content']); ?></textarea>
                <?php if ($data['saveFl'] == 'n') { ?>
                    <?php if ($data['scmModeFl'] == 'y') { ?>
                        <p class="notice-info">본사에서 공급사용으로 기본노출 설정된 정보입니다. 공급사 별도 설정이 없을 경우
                            <a href="/goods/goods_register.php" target="_blank">[상품>상품관리>상품등록]</a> 시 우선 노출됩니다.
                        </p>
                    <?php } ?>
                    <p class="notice-info">본사에서 등록된 상품상세이용안내는 공급사에서 내용을 수정할 수 없습니다. 내용 수정이 필요한 경우 복사하여 사용하거나 본사에 문의하세요.</p>
                <?php } ?>

            </td>
        </tr>
        <?php if ($data['saveFl'] == 'y') { ?>
            <?php if (gd_use_provider() === true && gd_is_provider() === false) { ?>
                <tr>
                    <th class="input_title r_space ">공급사 사용 여부</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="scmDisplayFl" value="y" <?php echo gd_isset($checked['scmDisplayFl']['y']); ?> />사용 가능
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmDisplayFl" value="n" <?php echo gd_isset($checked['scmDisplayFl']['n']); ?> />사용 불가
                        </label>
                        <div class="mgt5 js-scm-mode-fl" <?php if ($data['scmDisplayFl'] == 'n') { ?>style="display:none"<?php } ?>>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="scmModeFl" value="y" <?php echo gd_isset($checked['scmModeFl']['y']); ?> />공급사에서 상품 등록 시 기본으로 노출되도록 설정합니다.
                            </label>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th class="input_title r_space ">상품 등록시 기본</th>
                <td>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="modeFl" value="y" <?php echo gd_isset($checked['modeFl']['y']); ?> />상품 등록 시 기본으로 노출되도록 설정합니다.
                    </label>
                </td>
            </tr>
        <?php } ?>
    </table>
</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $("#frmGoodsInfo").validate({
            submitHandler: function (form) {
                <?php
                if (gd_use_provider()) {
                if (gd_is_provider()) {
                ?>
                // 공급사에서 상품 등록시 현재 안내를 기본 설정하려고 할 때 본사의 공급사 허용 안내가 공급사 기본으로 된 것이 있는지 체크
                if ($('input:checkbox[name="modeFl"]').prop("checked") == true) {
                    var groupCdData = '';
                    if ($('input[name="groupCd"]:checked').val()) {
                        groupCdData = $('input[name="groupCd"]:checked').val();
                    } else {
                        groupCdData = $('input[name="groupCd"]').val();
                    }
                    $.ajax({
                        method: "POST",
                        cache: false,
                        url: "./goods_ps.php",
                        data: "mode=goods_info_scm_relation&scmCode=scm&groupCd=" + groupCdData,
                        success: function (data) {
                            if (data > 0) {
                                dialog_alert('본사의 이용안내가 기본 설정으로 되어 있습니다.<br/>상품 등록시 기본의 체크 해제해 주세요.', layer_close());
                                return false;
                            } else {
                                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                                form.target = 'ifrmProcess';
                                form.submit();
                            }
                        },
                        error: function (data) {
                            dialog_alert(data, layer_close());
                            return false;
                        }
                    });
                } else {
                    oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                    form.target = 'ifrmProcess';
                    form.submit();
                }
                <?php
                } else {
                ?>
                // 본사에서 공급사 허용 안내로 공급사 기본 제공 하려고 할 때 공급사에서 등록한 안내가 기본으로 되어 있는 것이 있는지 체크
                if ($('input[name="scmFl"]:checked').val() == 'n' && $('input:checkbox[name="scmModeFl"]').prop("checked") == true) {
                    var groupCdData = '';
                    if ($('input[name="groupCd"]:checked').val()) {
                        groupCdData = $('input[name="groupCd"]:checked').val();
                    } else {
                        groupCdData = $('input[name="groupCd"]').val();
                    }
                    $.ajax({
                        method: "POST",
                        cache: false,
                        url: "./goods_ps.php",
                        data: "mode=goods_info_scm_relation&scmCode=center&groupCd=" + groupCdData,
                        success: function (data) {
                            if (data > 0) {
                                dialog_confirm('공급사에 이용안내가 기본 설정으로 되어 있는 것이 있습니다.<br/>변경하면 공급사의 이용안내가 기본설정에서 제외되고 본사의 이용안내가 기본 적용됩니다.', function (result) {
                                    if (result) {
                                        oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                                        form.target = 'ifrmProcess';
                                        form.submit();
                                    } else {
                                        layer_close();
                                        return false;
                                    }
                                });
                            } else {
                                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                                form.target = 'ifrmProcess';
                                form.submit();
                            }
                        },
                        error: function (data) {
                            dialog_alert(data, layer_close());
                            return false;
                        }
                    });
                } else {
                    oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                    form.target = 'ifrmProcess';
                    form.submit();
                }
                <?php
                }
                } else {
                ?>
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                form.target = 'ifrmProcess';
                form.submit();
                <?php
                }
                ?>
            },
            // onclick: false, // <-- add this option
            rules: {
                informNm: 'required',
                groupCd: {
                    required: true
                },
                content: {
                    required: function (textarea) {
                        var editorcontent = oEditors.getById[textarea.id].getIR();	// 에디터의 내용 가져오기.
                        console.log(editorcontent);
                        editorcontent = editorcontent.replace(/<img[^>]*>/gi, '이미지').replace(/<[^>]*>/gi, '').replace('&nbsp;', '');
                        console.log(editorcontent);
                        return editorcontent.length === 0;
                    }
                }
            },
            messages: {
                informNm: {
                    required: '이용안내 제목을 입력해주세요.'
                },
                groupCd: {
                    required: '이용안내 종류를 선택해주세요.'
                },
                content: {
                    required: '이용안내 내용을 입력해주세요'
                },
            }
        });



        <?php    if ($data['informCd']) { ?>
        $('input:radio[name=scmFl]').prop("disabled", true);
        $('button.scmBtn').attr("disabled", true);
        <?php }?>

        $('input[name="scmDisplayFl"]').click(function (e) {
            if ($(this).val() == 'y') {
                $(".js-scm-mode-fl").show();
            } else {
                $(".js-scm-mode-fl").hide();
            }
        });

    });


    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr, addParam);
    }
    //-->
</script>
