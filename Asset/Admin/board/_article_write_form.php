<script id="selectGoodsTblTr" type="text/html">
    <tr>
        <td>
            <input type="hidden" name="goodsNo[]" value="<%=goodsNo%>">
            <a href="<?= URI_HOME ?>goods/goods_view.php?goodsNo=<%=goodsNo%>" target="_blank"><img src="<%=goodsImgageSrc%>" width="50" height="50"></a></td>
        <td><a href="../goods/goods_register.php?goodsNo=<%=goodsNo%>" target="_blank"><%=goodsName%></a></td>
        <td><%=goodsPrice%></td>
        <td>
            <button class="btn btn-white btn-sm js-select-remove">삭제</button>
        </td>
    </tr>
</script>

<script id="selectOrderTblTr" type="text/html">
    <tr>
        <td>
            <input type="hidden" name="orderGoodsNo[]" value="<%=orderGoodsNo%>">
            <%=goodsLinkTag%><img src="<%=goodsImgageSrc%>" width="50" height="50"><%=goodsLinkTagClose%>
        </td>
        <td><a href="../order/order_view.php?orderNo=<%=orderNo%>" target="_blank"><%=orderNo%></a> | <%=regDt%><br>
            <% if (goodsType == 'addGoods') { %>
            <a href="javascript:void(0)" onclick="addgoods_register_popup('<%=goodsNo%>' <?php if(gd_is_provider()) { echo ",'1'"; } ?>);">
            <% } else { %>
            <a href="javascript:void(0)" onclick="goods_register_popup('<%=goodsNo%>' <?php if(gd_is_provider()) { echo ",'1'"; } ?>);">
            <% } %>
                <b><%=goodsName%></b>
            </a>
            <br><%=optionName%>
        </td>
        <td><%=orderStatus%><br><%=goodsPrice%></td>
        <td>
            <button class="btn btn-white btn-sm js-select-remove">삭제</button>
        </td>
    </tr>
</script>

<script type="text/javascript">
    var bdId = '<?=$bdWrite['cfg']['bdId']?>';

    function setAddGoods(frmData) {
        $.each(frmData.info, function (key, val) {
            var selectGoodsTblTr = _.template($('#selectGoodsTblTr').html());
            var param = {goodsNo: val.goodsNo, goodsImgageSrc: val.goodsImgageSrc, goodsName: val.goodsName, goodsPrice: val.goodsPrice};
            selectGoodstblTrHtml = selectGoodsTblTr(param);
        });
        $("#selectGoodsTbl tbody").empty();
        $("#selectGoodsTbl tbody").append(selectGoodstblTrHtml);
    }

    function setAddOrder(frmData) {
        $.each(frmData.info, function (key, val) {

            var selectOrderTblTr = _.template($('#selectOrderTblTr').html());
            var goodsLinkTag = '';
            var goodsLinkTagClose = '';
            if (val.goodsType == 'goods') {
                goodsLinkTag = '<a href="<?=URI_HOME?>goods/goods_view.php?goodsNo=' + val.goodsNo + '" target="_blank">';
                goodsLinkTagClose = '</a>';
            }

            var param = {
                goodsNo: val.goodsNo,
                orderGoodsNo: val.orderGoodsNo,
                orderNo: val.orderNo,
                regDt: val.regDt,
                goodsPrice: val.goodsPrice,
                optionName: val.optionName,
                orderStatus: val.orderStatus,
                goodsImgageSrc: val.goodsImgageSrc,
                goodsName: val.goodsName,
                goodsType: val.goodsType,
                goodsLinkTag: goodsLinkTag,
                goodsLinkTagClose: goodsLinkTagClose
            };
            selectOrdertblTrHtml = selectOrderTblTr(param);
        });
        $("#selectOrderTbl tbody").empty();
        $("#selectOrderTbl tbody").append(selectOrdertblTrHtml);
    }

    $(document).ready(function () {
        var bdId = '<?=$req['bdId']?>';
        var bdSecretFl = '<?=$bdWrite['cfg']['bdSecretFl']?>';
        var mode = '<?=$mode?>';
        var flag = true;

        $('#frmWrite').find('[name=queryString]').val(getUrlVars());
        $('select[name=bdId]').bind('change', function () {
            var flag = true;
            $('#board-table').find('input[type=text]').each(function (index, item) {
                if ($(item).val() != '') {
                    flag = false;
                    return false;
                }
            })

            if(flag){
                var editorcontent = oEditors.getById['editor'].getIR();	// 에디터의 내용 가져오기.
                editorcontent = editorcontent.replace(/<(?!img).*?>/ig, '').replace('&nbsp;', '');
                flag = editorcontent.length < 1 ;
            }

            if (flag === false) {
                dialog_confirm('게시판 변경 시 입력된 정보가 초기화됩니다. <br> 변경하시겠습니까?', function (result) {
                    if (result) {
                        location.href = 'article_write.php?bdId=' + $('select[name=bdId]').val();
                    }
                    else {
                        $('select[name=bdId]').val(bdId);
                    }
                });
            }
            else {
                location.href = 'article_write.php?bdId=' + $('select[name=bdId]').val();
            }
        })

        $('body').on('click', '.js-select-remove', function () {
            $(this).closest('tr').remove();
        })

        $('body').on('click', '.addUploadBtn', function () {
            var uploadBoxCount = $('#uploadBox').find('input[name="upfiles[]"]').length;
            if (uploadBoxCount >= 10) {
                alert("업로드는 최대 10개만 지원합니다");
                return;
            }

            var addUploadBox = _.template(
                $("script.template").html()
            );
            $(this).closest('ul').append(addUploadBox);
            init_file_style();
        });

        $('body').on('click', '.minusUploadBtn', function () {
            index = $(this).prevAll('input:file').attr('index'); //$('.file-upload button.uploadremove').index(target)+1;
            $("input[name='uploadFileNm[" + index + "]']").remove();
            $("input[name='saveFileNm[" + index + "]']").remove();
            $(this).closest('li').remove();
        })

        $('.js-add-goods').bind('click', function () {
            window.open('/share/popup_goods.php?checkType=radio', 'popup_goods_select', 'width=650, height=850, scrollbars=no');
        })

        $('.js-add-order').bind('click', function () {
            window.open('/share/popup_order.php?checkType=radio', 'popup_order_select', 'width=900, height=850, scrollbars=no');
        })

        $('input[name=isMove]').bind('change', function () {
            $('select[name=moveBdId]').attr('disabled', true);
            if ($(this).is(':checked')) {
                $('select[name=moveBdId]').attr('disabled', false);
            }
        });

        $("input[name=isMove]").trigger('change');

        $.validator.addMethod("checkEventDate", function (value) {
            var startDateTime = $('input[name="eventStart"]').val();
            var endDateTime = value;

            var startDate = new Date(startDateTime.replace(' ', 'T'));
            var endDate = new Date(endDateTime.replace(' ', 'T'));

            return endDate > startDate ? true : false;
        }, "종료일이 시작일보다 빠를수 없습니다.");

        $("#frmWrite").validate({
            submitHandler: function (form) {
                if($(form).find('[name=uploadType]').val() == 'ajax') {
                    $('input:file').prop('disabled', true);
                }
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                form.target = 'ifrmProcess';
                form.submit();
            },
            // onclick: false, // <-- add this option
            rules: {
                bdId: 'required',
                subject: 'required',
                contents: {
                    required: function (textarea) {
                        var editorcontent = oEditors.getById[textarea.id].getIR();	// 에디터의 내용 가져오기.
                        editorcontent = editorcontent.replace(/<(?!img).*?>/ig, '').replace('&nbsp;', '');
                        return editorcontent.length === 0;
                    }
                },
                eventStart: 'required',
                eventEnd: {
                    required: true,
                    checkEventDate: true,
                },
            },
            messages: {
                bdId: {
                    required: '게시판 아이디를 선택해주세요.'
                },
                subject: {
                    required: '제목을 입력해주세요.'
                },
                contents: {
                    required: '내용을 입력해주세요'
                },
                eventStart: {
                    required: '이벤트 기간을 입력해주세요'
                },
                eventEnd: {
                    required: '이벤트 기간을 입력해주세요'
                }
            }
        });

        $(document).on("change", "input:file", function () {
            //ajax업로드 처리
            var thisObj = $(this);
            var uploadImages = [];
            var name = this.name;
            var idx = $('input[name="' + name + '"]').index(this);
            gdAjaxUpload.upload(
                {
                    formObj: $("#frmWrite"),
                    thisObj: thisObj,
                    actionUrl: './article_ps.php',
                    params: {bdId: $('[name=bdId]').val(), 'mode': 'ajaxUpload'},
                    onbeforeunload: function () {
                        if (uploadImages.length == 0) {
                            return false;
                        }
                        $.ajax({
                            method: "POST",
                            url: "./article_ps.php",
                            async: false,
                            data: {mode: 'deleteGarbageImage', bdId: $('[name=bdId]').val(), deleteImage: uploadImages.join('^|^')},
                            cache: false,
                        }).success(function (data) {
                        }).error(function (e) {
                        });
                    },
                    successAfter: function (data) {
                        thisObj.attr('index',data.index);
                        uploadImages.push(data.saveFileNm);
                    },
                    failAfter : function(data) {
                        if (data.result == 'fail' && name == 'upfiles[]') {
                            $('input[name="' + name + '"]').eq(idx).val('');
                        }
                    }
                }
            )
        });

        $('#bdTemplateSno').change(function(){
            if($(this).val() == '' || $(this).val() == 0){
                return false;
            }

            var editorcontent = oEditors.getById['editor'].getIR();	// 에디터의 내용 가져오기.
            editorcontent = editorcontent.replace(/<[^>]*>/gi, '').replace('&nbsp;', '');
            if (editorcontent.length > 0) {
                if (!confirm('본문이 삭제되고 게시글양식 내용이 삽입됩니다. 진행하시겠습니까?')) {
                    return false;
                }
            }

            $.ajax({
                method: "POST",
                url: "./template_ps.php",
                data: {mode : 'getData',sno : $(this).val()},
                dataType: 'json'
            }).success(function (result) {
                console.log(result);
                var contents = result['contents'];
                oEditors.getById["editor"].exec("SET_CONTENTS", [contents]);
            }).error(function (e) {
                alert(e.responseText);
            });
        })

        $('.js-template-register').bind('click',function(){
            window.open('template_write.php?mode=popup&templateType=admin','template','width=850,height=600');
        })


        $('#w_isNotice').on('click', function(){
            if(mode != '답변'){
                if(bdSecretFl == 3) {
                    if ($(this).is(':checked')) {
                        $('#w_isSecret').prop('disabled', false);
                        $('#w_isSecret').prop('checked', false);
                        $('input[name=\'isSecret\']').prop('value', 'n');
                    } else {
                        $('#w_isSecret').prop('checked', true);
                        $('input[name=\'isSecret\']').prop('value', 'y');
                        $('#w_isSecret').prop('disabled', true);
                    }
                }
            }
        })

        $('#w_isSecret').on('change', function(){
            if ($(this).is(':checked')) {
                $(this).prop('value', 'y');
                $('input[name=\'isSecret\']').prop('value', 'y');
            } else {
                $(this).prop('value', 'n');
                $('input[name=\'isSecret\']').prop('value', 'n');
            }
        })
    });
</script>
<script type="text/template" class="template">
    <li class="form-inline mgb5">
        <input type="file" name="upfiles[]">
        <a class="btn btn-white btn-icon-minus minusUploadBtn btn-sm">삭제</a>
    </li>
</script>

<form name="frmWrite" id="frmWrite" action="article_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="queryString" value=""/>
    <input type="hidden" name="isAdmin" value="true"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <?php if($req['popupMode'] !='yes') { // CRM 팝업모드가 아닐 경우 ?>
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('<?= $adminList; ?>');"/>
            <?php } ?>
            <input type="submit" value="저장하기" class="btn btn-red"/>
        </div>
    </div>
    <?php
    if ($req['mode'] == 'reply') {
        include "_article_detail.php";
    }
    ?>
    <div class="table-title gd-help-manual">게시글 <?= $mode ?></div>
    <input type="hidden" name="sno" value="<?= $req['sno'] ?>">
    <input type="hidden" name="mode" value="<?= $req['mode'] ?>">

    <table class="table table-cols" id="board-table">
        <colgroup>
            <col class="width10p"/>
            <col/>

        <tr>
            <th>게시판</th>
            <td>
        </colgroup>
        <?php
        if($req['mode'] == 'write' && gd_is_provider() === false) {?>
                <select name="bdId" id="bdId">
                    <?php foreach ($boardList as $data) { ?>
                        <option value="<?= $data['bdId'] ?>" <?php if ($req['bdId'] == $data['bdId']) echo 'selected' ?>><?= $data['bdNm'] ?>(<?= $data['bdId'] ?>)</option>
                    <?php } ?>
                </select>
        <?php }
        else {?>
            <?= $bdWrite['cfg']['bdNm'].'('.$bdWrite['cfg']['bdId'].')' ?>
            <input type="hidden" name="bdId" value="<?= $bdWrite['cfg']['bdId'] ?>">
        <?php }?>
            </td>
        </tr>
        <?php if($bdWrite['cfg']['bdAnswerStatusFl'] == 'y' || $bdWrite['cfg']['bdReplyStatusFl'] == 'y'){?>
        <tr>
            <th>답변 상태</th>
            <td>
                <select name="replyStatus" class="form-control">
                    <?php
                    foreach ($listReplyStatus as $key => $val) { ?>
                        <option
                                value="<?= $key ?>" <?php if ($bdWrite['data']['replyStatus'] == $key) echo 'selected' ?>><?= $val ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <?php }?>
        <tr>
            <th class="require">제목</th>
            <td>
                <input type="text" name="subject" id="subject" class="form-control"
                       value="<?= gd_isset($bdWrite['data']['subject']) ?>"
                       class="input_text width80p">
            </td>
        </tr>
        <?php if(gd_is_provider() === false) {?>
        <tr>
            <th>게시글 양식</th>
            <td class="form-inline">
                <?= gd_select_box('bdTemplateSno', 'bdTemplateSno', $templateList); ?>
                <input type="button" value="게시글 양식 등록" class="btn btn-black js-template-register">
            </td>
        </tr>
        <?php }?>
        <?php if ($bdWrite['data']['canWriteGoodsSelect'] == 'y') { ?>
            <tr>
                <th>상품 선택</th>
                <td>
                    <div>
                        <button type="button" class="btn btn-white js-add-goods">상품선택</button>
                    </div>
                    <table id="selectGoodsTbl" class="table table-cols mgt15">
                        <colgroup>
                            <col class="width10p"/>
                            <col class="width70p"/>
                            <col class="width10p"/>
                            <col class="width10p"/>
                        </colgroup>
                        <tbody>
                        <?php if ($bdWrite['data']['goodsNo']) { ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="goodsNo[]" value="<?= $bdWrite['data']['goodsNo'] ?>">
                                    <a href="<?= URI_HOME ?>goods/goods_view.php?goodsNo=<?= $bdWrite['data']['goodsNo'] ?>" target="_blank">
                                        <img src="<?= $bdWrite['data']['goodsData']['goodsImageSrc']; ?>" width="100" height="100"></a></td>
                                <td><b><a href="../goods/goods_register.php?goodsNo=<?= $bdWrite['data']['goodsNo'] ?>" target="_blank"><?= $bdWrite['data']['goodsData']['goodsNm'] ?></a></b></td>
                                <td><?= gd_currency_display($bdWrite['data']['goodsData']['goodsPrice']) ?></td>
                                <td>
                                    <button class="btn btn-white btn-sm js-select-remove">삭제</button>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4" class="empty">상품이 없습니다.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
        <?php } ?>
        <?php if ($bdWrite['data']['canWriteOrderSelect'] == 'y') { ?>
            <tr>
                <th>주문 선택</th>
                <td>
                    <div><!--선택된 주문이 없습니다. -->
                        <button type="button" class="btn btn-white js-add-order">주문 선택</button>
                    </div>
                    <table id="selectOrderTbl" class="table table-cols mgt15">
                        <colgroup>
                            <col class="width10p"/>
                            <col class="width70p"/>
                            <col class="width10p"/>
                            <col class="width10p"/>
                        </colgroup>
                        <tbody>
                        <?php if ($bdWrite['data']['extraData']['arrOrderGoodsData']) {
                            foreach ($bdWrite['data']['extraData']['arrOrderGoodsData'] as $val) {
                                ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="orderGoodsNo[]" value="<?= $val['sno'] ?>">
                                        <a href="<?= URI_HOME ?>goods/goods_view.php?goodsNo=<?= $val['goodsNo'] ?>" target="_blank"><img src="<?= $val['goodsImageSrc'] ?>"
                                                                                                                                          width="100" height="100"></a>
                                    </td>
                                    <td><a href="../order/order_view.php?orderNo=<?= $val['orderNo']; ?>" title="상품주문번호" target="_blank"><?=$val['orderNo']?></a> | <?= $val['orderGoodsRegDt'] ?><br>
                                        <?php if($val['goodsType'] == 'addGoods') {?>
                                        <a href="javascript:void(0)" onclick="addgoods_register_popup('<?=$val['goodsNo'] ?>' <?php if(gd_is_provider()) { echo ",'1'"; } ?>);">
                                        <?php } else {?>
                                        <a href="javascript:void(0)" onclick="goods_register_popup('<?=$val['goodsNo'] ?>' <?php if(gd_is_provider()) { echo ",'1'"; } ?>);">
                                        <?php }?>
                                            <b><?= $val['goodsNm'] ?></b>
                                        </a>
                                        <br><?= $val['optionName'] ?>
                                    </td>
                                    <td><?= $val['orderStatusText'] ?><br><?= gd_currency_display($val['totalGoodsPrice']) ?></td>
                                    <td>
                                        <button class="btn btn-white btn-sm js-select-remove">삭제</button>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="4" class="empty">주문이 없습니다.</td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
        <?php } ?>
        <?php
        if ($bdWrite['cfg']['bdGoodsPtFl'] == 'y') { ?>
            <tr>
                <th>별점</th>
                <td>
                    <?php for ($i = 5; $i >= 0; $i--) { ?>
                        <label for="rating<?= $i ?>">
                            <input type="radio" name="goodsPt" value="<?= $i ?>" class="radio" id="rating<?= $i ?>" <?php if ($i == $bdWrite['data']['goodsPt']) echo 'checked' ?>
                                   name="rating">
                            <span class="rating"><span style="width:<?= $i * 20 ?>%;">별<?= $i ?></span></span></label>
                    <?php } ?>

                </td>
            </tr>
        <?php } ?>
        <?php
        if ($bdWrite['cfg']['bdMobileFl'] == 'y') {
            ?>
            <tr>
                <th>휴대폰</th>
                <td><input type="text" name="writerMobile"
                           value="<?= gd_isset($bdWrite['data']['writerMobile']) ?>"
                           class="form-control"/></td>
            </tr>
            <?php
        }
        if ($bdWrite['cfg']['bdEmailFl'] == 'y') {
            ?>
            <tr>
                <th>이메일</th>
                <td><input type="text" name="writerEmail"
                           value="<?= gd_isset($bdWrite['data']['writerEmail']) ?>"
                           class="form-control"/></td>
            </tr>
            <?php
        }
        ?>
        <?php if ($bdWrite['cfg']['bdCategoryFl'] == 'y') { ?>
            <tr>
                <th>말머리</th>
                <td>
                    <?= $bdWrite['categoryBox']; ?>
                </td>
            </tr>
        <?php } ?>
        <?php if ($bdWrite['cfg']['bdFl'] == 'y') { ?>
            <tr>
                <th>점수</th>
                <td>
                    <select name="" class="form-control">
                        <?php for ($i = 0; $i < 6; $i++) { ?>
                            <option
                                value="<?= $i ?>" <?php if ($bdWrite['data'][''] == $i) echo 'selected' ?>><?= $i ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        <?php } ?>
        <?php if ($req['mode'] != 'write') { ?>
            <tr>
                <th>게시판 이동</th>
                <td class="form-inline">
                    <?php if($bdWrite['data']['parentSno'] == 0) {?>
                    <input type="checkbox" name="isMove" value="y"/>
                    <?php
                    echo $bdWrite['cfg']['bdNm'];
                    ?> ->
                    <label>
                        <select name="moveBdId" class="form-control">
                            <?php
                            if (isset($moveBoardList) && is_array($moveBoardList)) {
                                foreach ($moveBoardList as $val) {
                                    ?>
                                    <option
                                        value="<?= $val['bdId'] ?>" <?php if ($val['bdId'] == $bdWrite['cfg']['bdId'])
                                        echo "selected='selected'" ?>><?= $val['bdNm'] . '(' . $val['bdId'] . ')' ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    </label>
                        <?php }
                        else {?>
                    답변글은 이동할 수 없습니다. 부모글을 이동하면 답변글도 자동으로 이동됩니다.
                    <?php }?>
                </td>
            </tr>
        <?php } ?>
        <?php
        if ($bdWrite['cfg']['bdEventFl'] == 'y') { ?>
            <?php if ($bdWrite['cfg']['bdSubSubjectFl'] == 'y') { ?>
                <tr>
                    <th>부가설명</th>
                    <td>
                        <input type="text" name="subSubject"
                               value="<?= gd_isset($bdWrite['data']['subSubject']) ?>"
                               class="form-control"/>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th class="require">이벤트 기간</th>
                <td>
                    <div class="form-inline">
                        <div class="input-group js-datetimepicker">
                            <input class="form-control" type="text" name="eventStart"
                                   value="<?= gd_isset($bdWrite['data']['eventStart']) ?>"
                                   placeholder="시작일">
                                        <span class="input-group-addon">
                                            <span class="btn-icon-calendar">
                                            </span>
                                        </span>
                        </div>
                        ~
                        <div class="input-group js-datetimepicker endDatetime">
                            <input class="form-control" type="text" name="eventEnd"
                                   value="<?= gd_isset($bdWrite['data']['eventEnd']) ?>"
                                   placeholder="종료일">
                                        <span class="input-group-addon">
                                            <span class="btn-icon-calendar">
                                            </span>
                                        </span>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>

        <?php
        if ($bdWrite['cfg']['bdUploadFl'] == 'y') {
            ?>
            <tr>
                <th>파일첨부</th>
                <td>
                    <ul class="pdl0" id="uploadBox">
                        <?php
                        if (gd_isset($bdWrite['data']['upfilesCnt'], 0) > 0) {
                            for ($i = 0; $i < $bdWrite['data']['upfilesCnt']; $i++) {
                                ?>
                                <li class="form-inline mgb5">
                                    <input type="file" name="upfiles[]"/><br>
                                    <input type="checkbox" name="delFile[<?= $i ?>]" value="y"/>
                                    Delete Uploaded File
                                    .. <?= $bdWrite['data']['uploadFileNm'][$i] ?>
                                </li>
                                <?php
                            }
                        }
                        ?>
                        <?php
                        if (gd_isset($bdWrite['data']['upfilesCnt'], 0) < 10) { ?>
                            <li class="form-inline mgb5">
                                <input type="file" name="upfiles[]">
                                <a class="btn btn-white btn-icon-plus addUploadBtn btn-sm">추가</a>
                            </li>
                        <?php } ?>
                    </ul>
                    <div class="notice-info">
                        파일은 최대 10개까지 다중업로드가 지원됩니다.
                        <?php if ($bdWrite['cfg']['bdStrMaxSize'] != '') echo '<br>파일 업로드 최대 사이즈는 ' . $bdWrite['cfg']['bdStrMaxSize'] . '입니다.'; ?>
                    </div>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($bdWrite['cfg']['bdLinkFl'] == 'y') {
            ?>
            <tr>
                <th>링크</th>
                <td>
                    <input type="text" name="urlLink"
                           value="<?= gd_isset($bdWrite['data']['urlLink']) ?>"
                           class="form-control">
                </td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <th class="require">내용</th>
            <td>
                <div>
                    <?php
                    if (gd_isset($bdWrite['data']['groupThread']) == '' && $req['mode'] != 'reply') {
                        ?>
                        <input type="checkbox" name="isNotice" id="w_isNotice"
                               value="y" <?php if (gd_isset($bdWrite['data']['isNotice']) == 'y') echo 'checked="checked"' ?>
                            <?php if (gd_isset($bdWrite['cfg']['bdKind']) == 'qa' && $bdWrite['data']['memNo'] >= 0) echo 'placeholder disabled'?>/>
                        <label for="w_isNotice" class="mgr20">공지사항</label>
                        <?php
                    }
                   ?>
                    <input type="checkbox" name="isSecret" id="w_isSecret" value="y" <?=$bdWrite['data']['checked']['isSecret']?>
                        <?php
                        if($mode != '답변')
                        if(($bdWrite['cfg']['bdSecretFl'] == 2 && gd_isset($bdWrite['data']['checked']['isSecret']) != "checked='checked'") || (gd_isset($bdWrite['data']['isNotice']) != 'y' && $bdWrite['cfg']['bdSecretFl'] == 3 && gd_isset($bdWrite['data']['checked']['isSecret']) == "checked='checked'")) echo 'placeholder disabled' ?> />
                    <?php if($mode != '답변') { ?>
                    <input type="hidden" name="isSecret" id="w_isSecret"/>
                    <?php } ?>
                    <label for="w_isSecret">비밀글</label>
                </div>
                <div class="mgt5">
                    <textarea name="contents" id="editor" rows="10" style="width:98%; height:412px; "><?= gd_isset($bdWrite['data']['contents']); ?></textarea>
                </div>

            </td>
        </tr>
    </table>
    <?php if($req['popupMode'] !='yes') { // CRM 팝업모드가 아닐 경우 ?>
    <div class="text-center">
        <button class="btn btn-white" type="button" onclick="btnList('<?= $req['bdId'] ?>')">목록가기</button>
    </div>
    <?php } ?>
</form>

<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js?ss=<?= date('YmdHis') ?>" charset="utf-8"></script>
