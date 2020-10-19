<div>
    <div class="table-title gd-help-manual">SMS / LMS 문구 등록</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>그룹 선택</th>
            <td class="form-inline">
                <div class="display-inline-block width-xl">
                    <?php echo gd_select_box(null, 'smsContentsGroup', $smsContentsGroup, null, $data['smsContentsGroup']); ?>
                </div>
                <div class="display-inline-block">
                    <button type="button" class="btn btn-red btn-sm js-sms-contents-register">등록</button>
                </div>
            </td>
        </tr>
        <tr>
            <th>SMS 문구</th>
            <td class="form-inline">
                <textarea name="smsContentsText" rows="8" class="smsContents form-control width-xl"></textarea>
                <div class="pdt5"><input type="text" id="smsTextStringCount" value="0" readonly="readonly" class="form-control width-3xs"> Bytes</div>
            </td>
        </tr>
    </table>
</div>

<div>
    <div class="mgt10"></div>
    <div class="table-title gd-help-manual">SMS / LMS 문구 리스트</div>
    <div>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td>
                    <div class="form-inline">
                        <?php echo gd_select_box('key', 'key', ['all' => '=통합검색=', 'contents' => '문구 내용'], '', $search['key']); ?>
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control width-md" placeholder="키워드를 입력해 주세요." />
                    </div>
                </td>
            </tr>
            <tr>
                <th>그룹 선택</th>
                <td>
                    <div class="form-inline">
                        <?php echo gd_select_box(null, 'smsAutoCode', $smsContentsGroup, null, $data['smsContentsGroup']); ?>
                        <input type="button" value="검색" class="btn btn-sm btn-gray" onclick="layer_list_search();" />
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<form id="frmList" name="frmList" action="" method="post">
    <input type="hidden" name="mode" />
    <table class="table table-rows table-rows">
        <colgroup>
            <col class="10%" />
            <col class="20%" />
            <col class="30%" />
            <col class="" />
            <col class="10%" />
        </colgroup>
        <thead>
        <tr>
            <th><input class="js-checkall" type="checkbox" data-target-name="sno"></th>
            <th>번호</th>
            <th>그룹</th>
            <th>내용</th>
            <th>선택</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (is_array($data) === true) {
            $arrGroup = $smsContentsGroup;
            unset($arrGroup[0]);
            foreach ($data as $key => $val) {
                ?>
                <tr class="text-center">
                    <td><input name="sno[]" type="checkbox" value="<?php echo $val['sno']; ?>" /></td>
                    <td class="font-num"><?php echo number_format($page->idx--); ?></td>
                    <td><?php echo $arrGroup[$val['smsAutoCode']]; ?></td>
                    <td class="smsContents-<?php echo $val['sno']; ?>"><?php echo $val['contents']; ?></td>
                    <td><input type="button" value="선택" class="btn btn-sm btn-black js-sms-contents-select" data-sno="<?php echo $val['sno']; ?>" /></span></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="5">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left">
            <input type="button" value="선택 삭제" class="btn btn-white js-remove-selected" />
        </div>
    </div>
</form>

<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
<div class="text-center"></div>

<script type="text/javascript">
    $(document).ready(function () {
        // 글자수 체크
        $('textarea[name=smsContentsText]').keyup(getSmsContentsLength).change(getSmsContentsLength);

        // SMS 문구 등록
        $('.js-sms-contents-register').click(function (e) {
            var smsContentsGroup = $('select[name=\'smsContentsGroup\']').val();
            var smsContentsText = $('textarea[name=\'smsContentsText\']').val();

            if (smsContentsGroup == '' || smsContentsGroup == '0') {
                alert('그룹을 선택해 주세요.');
                return;
            }

            if (smsContentsText == '') {
                alert('내용을 작성해 주세요.');
                return;
            }

            var params = {
                mode: 'registerSmsContents',
                smsContentsGroup: smsContentsGroup,
                smsContentsText: smsContentsText
            };

            $.post('<?php echo $pageUrl?>', params, function (data) {
                if (data == 'OK') {
                    layer_list_search('page=<?php echo $search['page'];?>');
                } else {
                    alert('등록시 오류가 발생하였습니다.');
                }
            });
        });

        // SMS 문구 선택
        $('.js-sms-contents-select').click(function(e){
            var smsSno = $(this).data('sno');
            var smsContentsText = $('.smsContents-' + smsSno).text();

            $('textarea[name=\'smsContents\']').val(smsContentsText);
            setSendLength();
            $('div.bootstrap-dialog-close-button').click();
        });

        // 선택한 팝업 삭제
        $('.js-remove-selected').click(function () {
            var chkCnt = $('input[name=\'sno[]\']:checkbox:checked').length;

            if (chkCnt < 1) {
                BootstrapDialog.show({
                    title: '선택한 SMS 문구 삭제',
                    type: BootstrapDialog.TYPE_WARNING,
                    message: '삭제할 SMS 문구를 선택해 주세요.',
                });
                return;
            }

            // 선택한 sno
            var delSno = [];
            $.each($('input:checkbox[name="sno[]"]:checked'), function(key, value) {
                var $value = $(value);
                delSno.push($($value).val());
            });

            BootstrapDialog.show({
                title: '선택한 SMS 문구 삭제',
                message: '선택한 ' + chkCnt + ' 개의 SMS 문구를 정말로 삭제 하시겠습니까? 삭제시 복구가 불가능합니다.',
                buttons: [
                    {
                        id: 'btn-cancel',
                        label: '삭제 취소',
                        action: function(dialogItself){
                            dialogItself.close();
                        }
                    },
                    {
                        id: 'btn-del',
                        label: '선택한 SMS 문구 삭제',
                        cssClass: 'btn-danger',
                        action: function(dialog) {
                            var $delButton = this;
                            var $cancelButton = dialog.getButton('btn-cancel');
                            $delButton.disable();
                            $cancelButton.disable();
                            $delButton.spin();
                            dialog.setClosable(false);
                            dialog.setMessage('선택한 ' + chkCnt + ' 개의 SMS 문구를 삭제 중입니다.');

                            var params = {
                                mode: 'deleteSmsContents',
                                delSno: delSno
                            };

                            $.post('<?php echo $pageUrl?>', params, function (data) {
                                dialog.close();
                                if (data == 'OK') {
                                    layer_list_search('page=<?php echo $search['page'];?>');
                                } else {
                                    alert('삭제시 오류가 발생하였습니다.');
                                }
                            });
                        }
                    }
                ]
            });
            return;
        });
    });

    // 글자수 체크
    function getSmsContentsLength() {
        setContentsLength('smsContentsText', 'smsTextStringCount');
    }

    // 페이지 출력
    function layer_list_search(pagelink) {
        var keyword = $('input[name=\'keyword\']').val();
        var smsAutoCode = $('select[name=\'smsAutoCode\']').val();

        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'layerFormID': '<?php echo $layerFormID?>',
            'parentFormID': '<?php echo $parentFormID?>',
            'dataFormID': '<?php echo $dataFormID?>',
            'dataInputNm': '<?php echo $dataInputNm?>',
            'callFunc': '<?php echo $callFunc?>',
            'mode': '<?php echo $mode?>',
            'key': 'contents',
            'keyword': keyword,
            'smsAutoCode': smsAutoCode,
            'pagelink': pagelink
        };

        $.get('<?php echo $pageUrl?>', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }
</script>
