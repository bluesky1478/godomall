<form id="frmGoods" name="frmGoods" target="ifrmProcess" action="../goods/display_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="event_config"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        다른 기획전 보기 사용 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md" />
            <col class="width-3xl" />
            <col />
            <col />
        </colgroup>
        <tr>
            <th>사용여부</th>
            <td>
                <div style="float: left; line-height: 170px;">
                    <label class="radio-inline"><input type="radio" name="otherEventUseFl" value="y" <?= gd_isset($checked['otherEventUseFl']['y']); ?>/>사용함</label>
                    <label class="radio-inline"><input type="radio" name="otherEventUseFl" value="n" <?= gd_isset($checked['otherEventUseFl']['n']); ?>/>사용안함</label>
                </div>
            </td>
            <td colspan="2" rowspan="2">
                <div>[쇼핑몰 예시화면]</div>
                <div><img src="/admin/gd_share/img/event/event_sample_other_event.png" border="0" /></div>
            </td>
        </tr>
        <tr>
            <th>문구설정</th>
            <td>
                <input type="text" name="otherEventDefaultText" class="form-control js-maxlength" placeholder="설정할 문구를 입력하여 주시기 바랍니다." value="<?= gd_isset($data['otherEventDefaultText']) ?>" maxlength="30" style="width: 90% !important;" />
            </td>
        </tr>
        <tr>
            <th>미진행 기획전<br />노출 여부</th>
            <td colspan="3">
                <label class="radio-inline"><input type="radio" name="otherEventDisplayFl" value="n" <?= gd_isset($checked['otherEventDisplayFl']['n']); ?>/>미진행 기획전 노출안함</label>
                <label class="radio-inline"><input type="radio" name="otherEventDisplayFl" value="y" <?= gd_isset($checked['otherEventDisplayFl']['y']); ?>/>미진행 기획전 노출함</label>
                &nbsp;&nbsp;&nbsp;
                <label class="checkbox-inline"><input type="checkbox" value="y"  <?=gd_isset($checked['otherEventBottomFirstFl']['y']); ?> name="otherEventBottomFirstFl" /> 미진행 기획전을 하단에 노출합니다.</label>
            </td>
        </tr>
        <tr>
            <th>기획전 진열</th>
            <td colspan="3">
                <div class="pdb5">
                    <label class="radio-inline">
                        <input type="radio" name="otherEventSortType" value="auto" <?= gd_isset($checked['otherEventSortType']['auto']); ?>/>자동진열
                        <label id="js-otherEventSortTypeTa" class="select-inline display-none">
                            <?=gd_select_box('otherEventSortTypeTa', 'otherEventSortTypeTa', $data['otherEventSortTypeTaList'], null, $data['otherEventSortTypeTa'], null, null); ?>
                        </label>
                    </label>
                </div>

                <div>
                    <label class="radio-inline">
                        <input type="radio" name="otherEventSortType" value="hand" <?= gd_isset($checked['otherEventSortType']['hand']); ?>/>수동진열
                        <label id="js-otherEventSortTypeTb" class="select-inline display-none">
                            <?=gd_select_box('otherEventSortTypeTb', 'otherEventSortTypeTb', $data['otherEventSortTypeTbList'], null, $data['otherEventSortTypeTb'], null, null); ?>
                        </label>
                    </label>
                </div>
            </td>
        </tr>
        <tr class="js-event-config-list">
            <th>
                기획전 순서설정
                <br />
                <button type="button" class="btn btn-sm btn-gray js-event-select">기획전 선택</button>
            </th>
            <td colspan="3">
                <div class="table-action mgb0 mgt0 bg-clear" style="border-top: 0px;">
                    <div class="pull-left">
                        <div class="btn-group">
                            <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                                맨아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                                아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                                위
                            </button>

                            <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                                맨위
                            </button>
                        </div>
                    </div>
                </div>
                <table class="table table-rows" id="tblEventList">
                    <colgroup>
                        <col class="width-3xs"/>
                        <col class="width-3xs"/>
                        <col/>
                        <col class="width-xs"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="chk_all" class="js-checkall" data-target-name="eventNo"/></th>
                        <th>번호</th>
                        <th>기획전명</th>
                        <th>삭제</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($data['otherEventData'] as $key => $otherEventData){
                    ?>
                        <tr class="center">
                            <td>
                                <input type="checkbox" name="eventNo[]" value="<?php echo $otherEventData['eventNo']; ?>" />
                                <input type="hidden" name="otherEventNo[]" value="<?php echo $otherEventData['eventNo']; ?>" />
                            </td>
                            <td  class="js-eventConfigSortIndexNo"><?php echo ++$key; ?></td>
                            <td><?php echo $otherEventData['eventName']; ?></td>
                            <td><button type="button" class="btn btn-white btn-sm btn-delete js-otherEventDelete">삭제</button></td>
                        </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
                <div class="table-action">
                    <div class="pull-left">
                        <button type="button" class="btn btn-white js-otherEventSelectDelete">선택 삭제</button>
                    </div>
                </div>
            </td>
        </tr>
        <tr class="js-event-config-extra-list">
            <th>
                진열 예외 기획전 설정
                <br />
                <button type="button" class="btn btn-sm btn-gray js-event-select">기획전 선택</button>
            </th>
            <td colspan="3">
                <div class="table-action mgb0 mgt0 bg-clear" style="border-top: 0px;">
                    <div class="pull-left">
                        <div class="btn-group">
                            <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                                맨아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                                아래
                            </button>
                            <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                                위
                            </button>

                            <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                                맨위
                            </button>
                        </div>
                    </div>
                </div>
                <table class="table table-rows" id="tblEventExtraList">
                    <colgroup>
                        <col class="width-3xs"/>
                        <col class="width-3xs"/>
                        <col/>
                        <col class="width-xs"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th><input type="checkbox" id="chk_all" class="js-checkall" data-target-name="eventExtraNo"/></th>
                        <th>번호</th>
                        <th>기획전명</th>
                        <th>삭제</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach($data['otherEventExtraData'] as $key => $otherEventData){
                        ?>
                        <tr class="center">
                            <td>
                                <input type="checkbox" name="eventExtraNo[]" value="<?php echo $otherEventData['eventNo']; ?>" />
                                <input type="hidden" name="otherEventExtraNo[]" value="<?php echo $otherEventData['eventNo']; ?>" />
                            </td>
                            <td><?php echo ++$key; ?></td>
                            <td><?php echo $otherEventData['eventName']; ?></td>
                            <td><button type="button" class="btn btn-white btn-sm btn-delete js-otherEventDelete">삭제</button></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <div class="table-action">
                    <div class="pull-left">
                        <button type="button" class="btn btn-white js-otherEventSelectDelete">선택 삭제</button>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 미진행 기획전 노출 여부 클릭시
        $("input[name='otherEventDisplayFl']:radio").click(function(){
            if($(this).val() === 'n'){
                $("input:checkbox[name='otherEventBottomFirstFl']").attr("disabled", true);
            }
            else {
                $("input:checkbox[name='otherEventBottomFirstFl']").attr("disabled", false);
            }
        });
        // 기획전 진열 클릭시
        $("input[name='otherEventSortType']:radio").click(function(){
            if($(this).val() === 'hand'){
                $("#js-otherEventSortTypeTb").show();
                $("#js-otherEventSortTypeTa").hide();

                $(".js-event-config-extra-list").hide();
                $(".js-event-config-list").show();
            }
            else {
                $("#js-otherEventSortTypeTb").hide();
                $("#js-otherEventSortTypeTa").show();

                $(".js-event-config-extra-list").show();
                $(".js-event-config-list").hide();
            }
        });
        // 기획전 선택
        $(".js-event-select").click(function(){
            var targetTableName = getOtherEventSortType('targetTable');
            var eventNoName = getOtherEventSortType('eventNoName');
            var otherEventNoName = getOtherEventSortType('otherEventNoName');
            var addParam = {
                "layerFormID" : "event_select",
                "layerTargetTable" : targetTableName,
                "layerTargetCheckboxName" : eventNoName,
                "layerTargetHiddenValueName" : otherEventNoName,
                "layerTitle" : "기획전 선택",
            };

            layer_add_info('event_select', addParam);
        });
        //선택삭제
        $(".js-otherEventSelectDelete").click(function(){
            var eventNoName = getOtherEventSortType('eventNoName');
            var targetEventEl = $(':checkbox[name="'+eventNoName+'[]"]:checked');
            if(targetEventEl.length < 1){
                alert("삭제할 기획전을 선택해 주세요.");
                return;
            }
            $(targetEventEl).each(function() {
                $(this).closest('tr').remove();
            });
        });
        //삭제
        $(document).on("click",  ".js-otherEventDelete",function(){
            $(this).closest('tr').remove();
        });

        $("input[name='otherEventDisplayFl']:radio:checked").trigger('click');
        $("input[name='otherEventSortType']:radio:checked").trigger('click');

        var move_row = {
            up: function () {
                var targetTableName = getOtherEventSortType('targetTable');
                var eventNoName = getOtherEventSortType('eventNoName');
                var $checkbox = $("#"+targetTableName).find(':checkbox[name="'+eventNoName+'[]"]:checked');
                $checkbox.each(function (idx, item) {
                    var $row = $(item).closest('tr');
                    $row.insertBefore($row.prev());
                });
            }, down: function () {
                var targetTableName = getOtherEventSortType('targetTable');
                var eventNoName = getOtherEventSortType('eventNoName');
                var $checkbox = $("#"+targetTableName).find(':checkbox[name="'+eventNoName+'[]"]:checked');
                $($checkbox.get().reverse()).each(function (idx, item) {
                    var $row = $(item).closest('tr');
                    var $next = $row.next();
                    var enableCheckboxLength = $next.find(':checkbox[name="'+eventNoName+'[]"]').length;
                    if (enableCheckboxLength > 0) {
                        $row.insertAfter($next);
                    }
                });
            }, top: function () {
                var targetTableName = getOtherEventSortType('targetTable');
                var eventNoName = getOtherEventSortType('eventNoName');
                var $checkbox = $("#"+targetTableName).find(':checkbox[name="'+eventNoName+'[]"]:checked');
                $checkbox.each(function (idx, item) {
                    var $row = $(item).closest('tr');
                    var $targetRow = $(':checkbox[name="'+eventNoName+'[]"]').first().closest('tr');
                    $row.insertBefore($targetRow);
                });
            }, bottom: function () {
                var targetTableName = getOtherEventSortType('targetTable');
                var eventNoName = getOtherEventSortType('eventNoName');
                var $checkbox = $("#"+targetTableName).find(':checkbox[name="'+eventNoName+'[]"]:checked');
                $($checkbox.get().reverse()).each(function (idx, item) {
                    var $row = $(item).closest('tr');
                    var $targetRow = $(':checkbox[name="'+eventNoName+'[]"]').last().closest('tr');
                    $row.insertAfter($targetRow);
                });

            }
        };

        $('.js-moverow').on('click', function (e) {
            var eventNoName = getOtherEventSortType('eventNoName');
            var $target = $(e.target);
            $moveChecked = $(':checked[name="'+eventNoName+'[]"]', 'tbody');
            $moveUnChecked = $(':checkbox[name="'+eventNoName+'[]"]:not(:checked)', 'tbody');
            if ($moveChecked.length > 0) {
                var direction = $target.data('direction');
                if (_.isUndefined(direction)) {
                    direction = $target.closest('button').data('direction');
                }
                switch (direction) {
                    case 'up':
                        move_row.up();
                        break;
                    case 'down':
                        move_row.down();
                        break;
                    case 'top':
                        move_row.top();
                        break;
                    case 'bottom':
                        move_row.bottom();
                        break;
                }
            }
            else {
                alert("선택된 기획전이 없습니다.");
            }
        });
    });

    function getOtherEventSortType($returnType)
    {
        if($("input[name='otherEventSortType']:radio:checked").val() === 'auto'){
            var returnData = {'targetTable' : 'tblEventExtraList', 'eventNoName' : 'eventExtraNo', 'otherEventNoName' : 'otherEventExtraNo'};
        }
        else {
            var returnData = {'targetTable' : 'tblEventList', 'eventNoName' : 'eventNo', 'otherEventNoName' : 'otherEventNo'};
        }

        return returnData[$returnType];
    }

    function reSortEventIndexNo()
    {
        if($("#tblEventList>tbody>tr").length > 0){
            $($("#tblEventList>tbody>tr")).each(function(index) {
                $(this).find(".js-eventConfigSortIndexNo").html(index+1);
            });
        }
    }
    //-->
</script>
