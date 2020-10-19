<form id="unstoringList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <div class="table-title gd-help-manual" style="padding-bottom: 5px;">
        <?= $subTitle ?> 목록
        <div class="btn-group" style="float: right; margin-right:1px;">
            <input type="button" value="<?= $subTitle ?> 등록" class="btn btn-red-line js-register"/>
        </div>
    </div>
    <div id="addressList">
        <table id="addressTable" class="table table-rows">
            <thead>
            <tr>
                <th class="width5p"></th>
                <th class="width15p">관리 명칭</th>
                <th>주소</th>
                <th>연락처</th>
                <th class="width10p">수정/삭제</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data) && is_array($data)) {
                foreach ($data as $key => $val) {
                    ?>
                    <tr class="text-center"
                        data-sno="<?= $val['sno']; ?>"
                        data-mainfl="<?= $val['mainFl'] ?>" <?php if ($val['checkedNo'] == true) { ?>style="background-color: rgb(247,247,247)" <?php } ?>>
                        <td>
                            <input type="checkbox" name="chk" id="address_<?= $val['sno']; ?>" value="<?= $val['sno']; ?>" <?php if ($val['checkedNo'] == true) { ?> checked="checked" <?php } ?>
                                   data-unstoringnm="<?= $val['unstoringNm'] ?>"
                                   data-zipcode="<?= $val['unstoringZipcode'] ?>"
                                   data-zonecode="<?= $val['unstoringZonecode'] ?>"
                                   data-postfl="<?= $val['postFl'] ?>"
                                   data-unstoringadd="<?= $val['unstoringAddress'] ?>"
                                   data-unstoringaddsub="<?= $val['unstoringAddressSub'] ?>"
                                   data-maincont="<?= $val['mainContact'] ?>"
                                   data-additionalcont="<?= $val['additionalContact'] ?>"
                                   data-mainfl="<?= $val['mainFl'] ?>"
                                   data-addressfl="<?= $val['addressFl'] ?>"
                                   data-mallfl="<?= $val['mallFl'] ?>" />
                        </td>
                        <td>
                            <?php if ($val['mainFl'] != 'n') { ?>
                                <span>
                                (기본 <?= $subTitle ?>)
                            </span></br>
                            <?php } ?>
                            <span><?= $val['unstoringNm'] ?></span>
                        </td>
                        <td>
                   <span>
                       <?php if ($val['postFl'] != 'y') { ?>
                           <?= $val['unstoringZonecode'] ?>
                           <?php if (strlen($val['unstoringZipcode']) === 7) { echo '(' . $val['unstoringZipcode'] . ')'; } ?>
                       <?php } ?> <?= $val['unstoringAddress'] ?> <?= $val['unstoringAddressSub'] ?>
                   </span>
                        </td>
                        <td>
                            <span>기본 : <?= $val['mainContact'] ?></span></br>
                            <span>추가 : <?= $val['additionalContact'] ?></span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-white btn-sm btnModify">수정</button></br>
                            <button type="button" class="btn btn-white btn-sm btnDelete">삭제</button>
                        </td>
                    </tr>
                    <?php
                }
            } ?>
            </tbody>
        </table>
        <div class="notice-info">
            <?= $subTitle ?> 관리에서 등록/수정된 주소 정보는 “선택적용”을 눌러야 최종 반영됩니다.
        </div>
        <div class="center js-pagination"><?=$page->getPage('getPageList(\'PAGELINK\')');?></div>
    </div>
    <div class="left"><input type="button" value="선택 적용" class="btn btn-lg btn-black js-confirm" /></span></div>
</form>
<script type="text/javascript">

    var $parentFormID = '<?= $parentFormID ?>';
    var $tableType = '<?= $tableID ?>';
    var title = '<?= $subTitle ?>';
    var ecKindCount = '<?= $ecKindCount ?>';
    var mallName = '<?= $mallName ?>';
    var mallFl = '<?= $mallFl ?>';
    var checkedNo = '<?php echo json_encode($checkedNo) ?>';
    var checkedRowData = [];
    var checkedCnt = 0;
    var page = '';

    $(document).ready(function () {

        $('input[name=\'chk\']').each(function() {
            if ($(this).is(':checked')) {
                checkedCnt++;
                insertCheckedRow($(this));
            }
        })

        $(document).off('click', 'input[name=\'chk\']').on('click', 'input[name=\'chk\']', function(){
            if ($(this).is(':checked')) {
                if (checkedCnt == ecKindCount) {
                    $(this).prop('checked', false);
                    alert(title + ' 주소는 ' + ecKindCount + '개까지만 추가할 수 있습니다.');
                } else {
                    checkedCnt++;
                    insertCheckedRow($(this));
                }
            } else {
                checkedCnt--;
                deleteCheckedRow($(this));
            }
        })

        // 등록
        $('.js-register').on('click', function(){
            registerUnstoringLayer(title);
        });

        $('.js-confirm').on('click', function(){
            confirmUnstoring();
        });

        $(document).off('click', '.btnModify').on('click', '.btnModify', function(){
            var unstoringNo = $(this).parent().parent().data('sno');
            modifyUnstoringLayer(unstoringNo, $(this));
        })


        function modifyUnstoringLayer(sno) {

            var loadChk = $('#layerUnstoringRegisterForm').length;

            var data = {
                'unstoringNo' : sno,
                'subTitle'  : title,
                'mallName'  : mallName,
                'mallFl'  : mallFl,
                'checkedNo'   : checkedNo,
                // 'checkedList'   : checkedRowData,
                'page'      : page,
            };

            $.ajax({
                url : '../share/layer_unstoring_register.php',
                data : data,
                type : 'GET',
                success : function(data) {
                    if (loadChk == 0) {
                        data = '<div id="layerUnstoringRegisterForm">' + data + '</div>';
                    }
                    var layerForm = data;
                    layer_popup(layerForm, title + ' 관리', 'wide');
                }
            });
        }

        $(document).off('click', '.btnDelete').on('click', '.btnDelete', function(){
            var mainFl = $(this).parent().parent().data('mainfl');
            var unstoringNo = $(this).parent().parent().data('sno');
            var deleteFl = false;
            var deleteNo = checkedNo.substring(1);
            deleteNo = deleteNo.substring(deleteNo.length-1, 0);
            deleteNo = deleteNo.split(',');

            for (var i in deleteNo) {
                if ("\"" + unstoringNo + "\"" == deleteNo[i]) {
                    deleteFl = true;
                    break;
                }
            }

            if (mainFl != 'n') {
                alert('기본 ' + title + '는 삭제할 수 없습니다. 변경 후 삭제해주세요.');
            } else if (deleteFl) {
                alert('선택 적용된 주소는 삭제하실 수 없습니다.');
            } else {
                deleteUnstoringLayer(unstoringNo);
            }
        })
    });

    function insertCheckedRow(checkedList) {
        checkedRowData.push({
            tableType             : $tableType,
            sno                   : checkedList.val(),
            unstoringNm          : checkedList.data('unstoringnm'),
            unstoringZipcode    : checkedList.data('zipcode'),
            unstoringZonecode   : checkedList.data('zonecode'),
            postFl                 : checkedList.data('postfl'),
            unstoringAddress        : checkedList.data('unstoringadd'),
            unstoringAddressSub    : checkedList.data('unstoringaddsub'),
            mainContact             : checkedList.data('maincont'),
            additionalContact           : checkedList.data('additionalcont'),
            mainFl                  : checkedList.data('mainfl'),
            addressFl               : checkedList.data('addressfl'),
            mallFl               : checkedList.data('mallfl'),
        });
    }

    function deleteCheckedRow(checkedList) {
        for (var i in checkedRowData) {
            if (checkedList.val() == checkedRowData[i].sno) {
                checkedRowData.splice(i, 1);
                break;
            }
        }
    }

    function getPageList(pageNum) {
        page = pageNum.split('=')[1];
        var data = {
            'subTitle'  : title,
            'parentFormID' : $parentFormID,
            'tableID' : $tableType,
            'mallName'  : mallName,
            'mallFl'    : mallFl,
            'unstoringNo'       : checkedNo,
            'page'   : page,
        };

        $.ajax({
            url : '../share/layer_unstoring_list.php',
            data : data,
            type : 'GET',
            success : function(data) {
                data = '<div id="layerUnstoringRegisterForm">' + data + '</div>';
                var newData = $(data).find('#addressList')
                $('#addressList').replaceWith(newData);

                $('input[name=\'chk\']').each(function() {
                    $(this).prop('checked', false);
                });

                $('input[name=\'chk\']').each(function() {
                    for (var i in checkedRowData) {
                        if ($(this).val() == checkedRowData[i].sno) {
                            $(this).prop('checked', true);
                        }
                    }
                });
            }
        });
    }

    function registerUnstoringLayer(title) {
        var loadChk = $('#layerUnstoringRegisterForm').length;
        var data = {
            'subTitle'  : title,
            'mallName'  : mallName,
            'mallFl'  : mallFl,
            'checkedNo' : checkedNo,
            'checkedList'   : checkedRowData,
        };

        $.ajax({
            url : '../share/layer_unstoring_register.php',
            data : data,
            type : 'GET',
            success : function(data) {
                if (loadChk == 0) {
                    data = '<div id="layerUnstoringRegisterForm">' + data + '</div>';
                }
                var layerForm = data;
                layer_popup(layerForm, title + ' 관리', 'wide');
            }
        });
    }

    function resetAddTableList() {     // 새로 적용했던 테이블 다시 적용할 경우 기존 테이블 리셋
        $('.' + $tableType).remove();
    }

    function checkedListSorting() {     // 기본 출고지(반품/교환지) 주소를 가장 윗 줄로 이동
        for (var i in checkedRowData) {
            if(checkedRowData[i]['mainFl'] != 'n') {
                checkedRowData.unshift(checkedRowData.splice(i, 1)[0]);
                break;
            }
        }
    }

    function confirmUnstoring() {
        resetAddTableList();
        checkedListSorting();

        $.each(checkedRowData, function(key, val){
            var addHtml = '';
            var compiled = _.template($('#unstoringListTemplate').html());

            addHtml += compiled({
                tableType     :   val.tableType,
                sno :   val.sno,
                unstoringNm :   val.unstoringNm,
                unstoringZipcode    : val.unstoringZipcode,
                unstoringZonecode   : val.unstoringZonecode,
                postFl                 : val.postFl,
                unstoringAddress        : val.unstoringAddress,
                unstoringAddressSub    : val.unstoringAddressSub,
                mainContact             : val.mainContact,
                additionalContact           : val.additionalContact,
                mainFl                  : val.mainFl,
                addressFl               : val.addressFl,
                inputArr        :   '[]',
            });
            $('#' + $parentFormID).append(addHtml);
        })

        layer_close();

    }

    /*function htmlSpecialChars(str) {
        str = str.replace('<', '&lt');
        str = str.replace('>', '&gt')

        return str;
    }*/

    function deleteUnstoringLayer(sno) {
        var data = {
            mode : 'delete',
            sno : sno,
        };

        $.ajax({
            type        : "POST",
            url         : '../share/layer_unstoring_ps.php',
            data        : data,
            success      : function(){
                $.ajax({
                    type    : 'GET',
                    url     : '../share/layer_unstoring_list.php',
                    data     : {
                        subTitle    :   title,
                        mallFl      :   mallFl,
                        unstoringNo : checkedNo,
                    }
                }).success(function(data){
                    var loadChk = $('#layerUnstoringListForm').length;
                    if (loadChk == 0) {
                        data = '<div id="layerUnstoringListForm">' + data + '</div>';
                    }
                    var newData = $(data).find('#addressList')
                    $('#addressList').replaceWith(newData);

                    $('input[name=\'chk\']').each(function() {
                        $(this).prop('checked', false);
                    });

                    $('input[name=\'chk\']').each(function() {
                        for (var i in checkedRowData) {
                            if ($(this).val() == checkedRowData[i].sno) {
                                $(this).prop('checked', true);
                            }
                        }
                    });
                });
            },
            error: function (data) {
                alert(data.message);
            }
        })
    }

</script>

<script type="text/html" id="unstoringListTemplate">
    <table class="form-inline mgt10 mgb5 <%=$tableType%> table table-cols" value="<%=sno%>">
        <input type="hidden" name="addressNo<%=inputArr%>" value="<%=sno%>"/>
        <!--        <input type="hidden" name="addressNm<%=inputArr%>" value="<%=unstoringNm%>"/>-->
        <input type="hidden" name="addressZipcode<%=inputArr%>" value="<%=unstoringZipcode%>"/>
        <input type="hidden" name="addressZonecode<%=inputArr%>" value="<%=unstoringZonecode%>"/>
        <!--        <input type="hidden" name="postFl<%=inputArr%>" value="<%=postFl%>"/>-->
        <input type="hidden" name="stdAddress<%=inputArr%>" value="<%=unstoringAddress%>"/>
        <input type="hidden" name="stdAddressSub<%=inputArr%>" value="<%=unstoringAddressSub%>"/>
        <!--        <input type="hidden" name="mainContact<%=inputArr%>" value="<%=mainContact%>"/>-->
        <!--        <input type="hidden" name="additionalContact<%=inputArr%>" value="<%=additionalContact%>"/>-->
        <input type="hidden" name="mainFl<%=inputArr%>" value="<%=mainFl%>"/>
        <input type="hidden" name="addressFl<%=inputArr%>" value="<%=addressFl%>"/>

        <colgroup>
            <col class="width-md"/>
            <col class="width-2lg"/>
            <col class="width-md"/>
        </colgroup>
        <tbody>
        <tr>
            <th>관리 명칭
                <% if( mainFl != 'n' ) { %>
                <span style="color: #117ef9"> (기본)</span>
                <% } %>
            </th>
            <td colspan="3"><%= unstoringNm %></td>
        </tr>
        <tr>
            <th>주소</th>
            <td colspan="3">
                <% if ( postFl != 'y' ) { %>
                <%= unstoringZonecode %>
                <% if ( unstoringZipcode && unstoringZipcode != '-' ) { %>(<%= unstoringZipcode %>)<% } %>
                <% } %>
                <%= unstoringAddress %> <%= unstoringAddressSub %>
            </td>
        </tr>
        <tr>
            <th>연락처</th>
            <td><%= mainContact %></td>
            <th>추가 연락처</th>
            <td><%= additionalContact %></td>
        </tr>
        </tbody>
    </table>
    </div>
</script>
