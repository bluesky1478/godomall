<form name="frmWrite" id="frmWrite" action="plus_review_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?= $req['mode'] ?>">
    <input type="hidden" name="sno" value="<?= $req['sno'] ?>">
    <input type="hidden" name="popupMode" value="<?= $req['popupMode'] ?>">
    <input type="hidden" name="queryString" value="<?= $queryString ?>">
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장하기" class="btn btn-red"/>
    </div>
    <div class="table-title gd-help-manual">플러스리뷰 게시글 수정</div>


    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>승인</th>
            <td colspan="3">
                <label class="radio-inline"><input type="radio" name="applyFl"
                                                   value="y" <?= $data['applyFl'] == 'y' ? 'checked':'' ?> /> 승인</label>
                <label class="radio-inline"><input type="radio" name="applyFl"
                                                   value="n" <?= $data['applyFl'] == 'n' ? 'checked':'' ?> /> 미승인</label>
            </td>
        </tr>
        <tr>
            <th>상품정보</th>
            <td colspan="3">
                <table id="selectGoodsTbl" class="table table-cols mgt15">
                    <colgroup>
                        <col class="width10p"/>
                        <col class="width30p"/>
                        <col class="width60p"/>
                    </colgroup>
                    <tbody>
                    <tr>
                        <td>
                            <a href="<?= URI_HOME ?>goods/goods_view.php?goodsNo=<?= $data['goodsNo'] ?>" target="_blank">
                                <img src="<?= $data['goodsImageSrc']; ?>" width="100"></a></td>
                        <td>
                            <div onclick="goods_register_popup('<?= $data['goodsNo']; ?>' <?php if (gd_is_provider()) {
                                echo ",'1'";
                            } ?>);" class="hand">
                                <b><?= $data['goodsNm'] ?></b></div>
                        </td>
                        <td align="left"><?= gd_currency_display($data['goodsPrice']) ?></td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php
        if ($config['addFormFl'] == 'y' || $config['displayOptionFl'] == 'y') { ?>
            <tr>
                <th>추가정보/옵션</th>
                <td>
                    <table class="table table-cols">
                        <?php
                        foreach ($config['serviceAddForm'] as $val) {
                            ?>
                            <tr>
                                <th class="width-md"><?= $val['labelName'] ?>
                                    <input type="hidden" name="addFormLabel[]" value="<?= $val['labelName'] ?>">
                                </th>
                                <td>
                                    <?php if ($val['inputType'] == 'select') { ?>
                                        <select name="addFormValue[]" <?php if ($val['requireFl'] == 'y') echo 'required' ?>>
                                            <?php foreach ($val['labelValue'] as $opt) { ?>
                                                <option value="<?= $opt ?>" <?php if ($opt == $data['addFormData'][$val['labelName']]) echo 'selected' ?>><?= $opt ?></option>
                                            <?php } ?>
                                        </select>
                                    <?php } else { ?>
                                        <input type="text" class="form-control width-2xl" name="addFormValue[]" placeholder="<?= $val['labelValue'][0] ?>"
                                               value="<?= $data['addFormData'][$val['labelName']] ?>" <?php if ($val['requireFl'] == 'y') echo 'required' ?>>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php
                        } ?>

                        <?php foreach($data['option'] as $val) {?>
                        <tr>
                            <th class="width-md"><?= $val['name'] ?>
                            </th>
                            <td> <?= $val['value'] ?></td>
                        </tr>
                        <?php }?>
                    </table>
                </td>
            </tr>
        <?php } ?>
        <?php if ($config['pointFl'] == 'y') { ?>
            <tr>
                <th>평가</th>
                <td>
                    <?php for ($i = 5; $i >= 0; $i--) { ?>
                        <label for="rating<?= $i ?>">
                            <input type="radio" name="goodsPt" value="<?= $i ?>" class="radio" id="rating<?= $i ?>" <?php if ($i == $data['goodsPt']) echo 'checked' ?>
                                   name="rating">
                            <span class="rating"><span style="width:<?= $i * 20 ?>%;">별<?= $i ?></span></span></label>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th>파일첨부</th>
            <td>
                <ul class="pdl0" id="uploadBox">
                    <?php
                    for ($i = 0; $i < count($data['uploadedFile']); $i++) {
                        ?>
                        <li class="form-inline mgb5">
                            <div class="mgb5"><input type="file" name="upfiles[]"/></div>
                            <input type="checkbox" name="delFile[<?= $i ?>]" value="y"/>
                            <img src="<?= $data['uploadedFile'][$i]['thumSrc'] ?>" width="50" height="50">
                            Delete Uploaded File
                            .. <?= $data['uploadedFile'][$i]['uploadFileNm'] ?>
                        </li>
                        <?php
                    }
                    ?>
                    <?php
                    if (count($data['uploadedFile']) <= $config['uploadMaxCount']) { ?>
                        <li class="form-inline mgb5">
                            <input type="file" name="upfiles[]">
                            <a class="btn btn-white btn-icon-plus js-btn-add-upload btn-sm">추가</a>
                        </li>
                    <?php } ?>
                </ul>
                <div class="notice-info">
                    파일은 최대 <?= $config['uploadMaxCount'] ?>개까지 다중업로드가 지원됩니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>내용</th>
            <td colspan="3" style="margin:0px">
                <textarea name="contents" class="width50p" rows="10"><?= $data['contents'] ?></textarea>
            </td>
        </tr>
    </table>
</form>
<?php if($req['popupMode'] != 'yes') { // CRM 팝업모드가 아닐 경우 ?>
<div class="text-center">
    <button type="button" class="btn btn-white btn-lg js-btn-list">목록</button>
</div>
<?php } ?>
<script>
    $(document).ready(function () {
        $("#frmWrite").validate({
            dialog: false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                contents: {
                    required: true,
                },
            },
            messages: {
                contents: {
                    required: '내용을 입력해주세요'
                },

            }
        });


        $('.js-btn-list').bind('click', function () {
            location.href = "plus_review_list.php?" + getUrlVars();
        })

        $('.js-btn-add-upload').on('click', function () {
            var uploadBoxCount = $('input[name="upfiles[]"]').length;
            if (uploadBoxCount >= 4) {
                alert("업로드는 최대 4개만 지원합니다");
                return;
            }

            var addUploadBox = _.template($("script.addUploadTemplate").html());
            $(this).closest('ul').append(addUploadBox);
            init_file_style();
        });

        $('body').on('click', '.minusUploadBtn', function () {
            var index = $(this).prevAll('input:file').attr('index'); //$('.file-upload button.uploadremove').index(target)+1;
            $("input[name='uploadFileNm[" + index + "]']").remove();
            $("input[name='saveFileNm[" + index + "]']").remove();
            $(this).closest('li').remove();
        })
        $(document).on("change", "input:file", function () {
            //ajax업로드 처리
            var thisObj = $(this);
            var uploadImages = [];
            gdAjaxUpload.upload(
                {
                    formObj: $("#frmWrite"),
                    thisObj: thisObj,
                    method: 'post',
                    actionUrl: './plus_review_ps.php',
                    params: {goodsNo: <?=$data['goodsNo']?>, 'mode': 'ajaxUpload'},
                    onbeforeunload: function () {
                        if (uploadImages.length == 0) {
                            return false;
                        }
                        /* $.ajax({
                         method: "POST",
                         url: "./article_ps.php",
                         async: false,
                         data: {mode: 'deleteGarbageImage', bdId: $('[name=bdId]').val(), deleteImage: uploadImages.join('^|^')},
                         cache: false,
                         }).success(function (data) {
                         }).error(function (e) {
                         });*/
                    },
                    successAfter: function (data) {
                        thisObj.attr('index', data.index);
                        uploadImages.push(data.saveFileNm);
                    },
                    failAfter: function (data) {
                    }
                }
            )
        });
    })

    function getUrlVars(paramKey) {
        if (typeof paramKey == 'undefined') {
            paramKey = '';
        }
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        if (window.location.href.indexOf('?') < 0) {
            return '';
        }
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            key = hash[0];
            val = hash[1];
            if (paramKey != '') {
                if (key == paramKey) {
                    return val;
                }
            }

            if (key == 'sno' || key == 'mode') {
                continue;
            }

            vars.push(hashes[i]);
        }

        if (paramKey != '') {
            return '';
        }

        return vars.join('&');
    }
</script>
<script type="text/template" class="addUploadTemplate">
    <li class="form-inline mgb5">
        <input type="file" name="upfiles[]">
        <a class="btn btn-white btn-icon-minus minusUploadBtn btn-sm">삭제</a>
    </li>
</script>
