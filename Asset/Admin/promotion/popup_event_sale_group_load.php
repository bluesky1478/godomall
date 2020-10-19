<form id="frmEventSaleGroupLoad" name="frmEventSaleGroupLoad" method="get">
    <div class="page-header">
        <h3>기획전 그룹 불러오기</h3>
    </div>

    <!-- 기획전 그룹 검색 -->
    <div class="table-title gd-help-manual">
        기획전 검색
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col />
            <col class="width-md" />
        </colgroup>
        <tr>
            <th>검색어</th>
            <td>
                <div class="form-inline">
                    <?= gd_select_box('key', 'key', $search['eventSaleListSelect'], null, $search['key'], null, ''); ?>
                    <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                </div>
            </td>
            <td rowspan="4">
                <input type="submit" value="검색" class="btn btn-lg btn-black" style="width: 100% !important; height: 100% !important;"/>
            </td>
        </tr>
        <tr>
            <th>진열유형</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="displayCategory" value="" <?=gd_isset($checked['displayCategory']['']);?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="displayCategory" value="n" <?=gd_isset($checked['displayCategory']['n']);?>/>일반형</label>
                    <label class="radio-inline"><input type="radio" name="displayCategory" value="g" <?=gd_isset($checked['displayCategory']['g']);?>/>그룹형</label>
                </div>
            </td>
        </tr>
        <tr>
            <th>노출범위</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="device" value="" <?=gd_isset($checked['device']['']);?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="device" value="yy" <?=gd_isset($checked['device']['yy']);?>/>PC+모바일</label>
                    <label class="radio-inline"><input type="radio" name="device" value="yn" <?=gd_isset($checked['device']['yn']);?>/>PC</label>
                    <label class="radio-inline"><input type="radio" name="device" value="ny" <?=gd_isset($checked['device']['ny']);?>/>모바일</label>
                </div>
            </td>
        </tr>
        <tr>
            <th>진행상태</th>
            <td>
                <div class="form-inline">
                    <label class="radio-inline"><input type="radio" name="statusText" value="" <?=gd_isset($checked['statusText']['']);?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="statusText" value="order" <?=gd_isset($checked['statusText']['order']);?>/>진행중</label>
                    <label class="radio-inline"><input type="radio" name="statusText" value="delivery" <?=gd_isset($checked['statusText']['delivery']);?>/>종료</label>
                    <label class="radio-inline"><input type="radio" name="statusText" value="product" <?=gd_isset($checked['statusText']['product']);?>/>대기</label>
                </div>
            </td>
        </tr>
    </table>
    <!-- 기획전 그룹 검색 -->
</form>

<!-- 기획전 그룹 리스트 -->
<table class="table table-rows" id="eventGroupList">
    <colgroup>
        <col class="width-3xs" />
        <col class="width-3xs" />
        <col />
        <col class="width-xs" />
        <col />
        <col class="width-xs" />
        <col class="width-3xs" />
    </colgroup>
    <thead>
    <tr>
        <th><input type="checkbox" id="chk_all" class="js-checkall" data-target-name="eventGroupCheckNo"/></th>
        <th>번호</th>
        <th>기획전명</th>
        <th>진열유형</th>
        <th>그룹명</th>
        <th>노출범위</th>
        <th>진행상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $nowDate = strtotime(date("Y-m-d H:i:s"));
    if (gd_isset($data)) {
        foreach ($data as $key => $val) {
            if($val['displayCategory'] === 'g'){
                $displayCategory = '그룹형';
            }
            else {
                $displayCategory = '일반형';
            }

            if($val['pcFl'] === 'y' && $val['mobileFl'] === 'y'){
                $displayType = 'PC+모바일';
            }
            else if($val['pcFl'] === 'y'){
                $displayType = 'PC';
            }
            else if($val['mobileFl'] === 'y'){
                $displayType = '모바일';
            }
            else {}

            $displayStartDate = strtotime($val['displayStartDate']);
            $displayEndDate = strtotime($val['displayEndDate']);
            if ($nowDate < $displayStartDate) {
                $val['statusText'] = '대기';
            } else if ($nowDate > $displayStartDate && $nowDate < $displayEndDate) {
                $val['statusText'] = '진행중';
            } else if ($nowDate > $displayEndDate) {
                $val['statusText'] = '종료';
            } else {
                $val['statusText'] = '오류';
            }
    ?>
        <tr class="center">
            <td>
                <input type="checkbox" name="eventGroupCheckNo[]" value="<?php echo $val['sno']; ?>||<?php echo $val['groupSno']; ?>"/>
            </td>
            <td><?=number_format($page->idx--); ?></td>
            <td><?php echo $val['themeNm']; ?></td>
            <td><?php echo $displayCategory; ?></td>
            <td><?php echo $val['groupName']; ?></td>
            <td><?php echo $displayType; ?></td>
            <td><?php echo $val['statusText']; ?></td>
        </tr>
    <?php
        }
    }
    else {
    ?>
        <tr>
            <td class="center" colspan="7">검색된 정보가 없습니다.</td>
        </tr>
     <?php
    }
    ?>
    </tbody>
</table>
<!-- 기획전 그룹 리스트 -->

<div class="text-center"><?=$page->getPage(); ?></div>

<div class="text-center">
    <button type="button" class="btn btn-lg btn-black" id="eventGroupAdjust">확인</button>
</div>

<script type="text/javascript">
    <!--
        $(document).ready(function () {
            $('#eventGroupAdjust').click(function() {

                var parameters = {
                    mode : 'event_group_load',
                    eventNo : $('input[name="eventGroupCheckNo[]"]:checked').serializeArray()
                };

                $.ajax({
                    method: "POST",
                    data: parameters,
                    cache: false,
                    url: "../goods/display_ps.php",
                    dataType: "json",
                    success: function (data) {
                        var eventGroupThemeLayout = _.template($('#eventGroupThemeLayout').html());

                        $.each(data, function( key, value ) {
                            var param = {
                                eventGroupTmpNo : value.eventGroupTmpNo,
                                groupIndex: $("#eventGroupLayout>tbody>tr", opener.document).length + 1,
                                groupName: value.groupName,
                                groupGoodsCnt: value.groupGoodsCnt,
                            };
                            var html = eventGroupThemeLayout(param);
                            $("#eventGroupLayout>tbody", opener.document).append(html);
                        });

                        alert("적용되었습니다.");

                        setTimeout(function(){
                            window.close();
                        }, 2000);
                    },
                    error: function (data) {
                    }
                });
            });
        });
    //-->
</script>
<script id="eventGroupThemeLayout" type="text/html">
    <tr class="center">
        <td>
            <input type="checkbox" name="eventGroupCheckNo[]" value="" />
            <input type="hidden" name="eventGroupNo[]" value="" />
            <input type="hidden" name="eventGroupTmpNo[]" value="<%=eventGroupTmpNo%>" />
        </td>
        <td class="js-eventGroupIndexArea"><%=groupIndex%></td>
        <td class="js-eventGroupNameArea"><%=groupName%></td>
        <td class="js-eventGroupCountArea"><%=groupGoodsCnt%></td>
        <td><button type="button" class="btn btn-white btn-sm js-eventGroupModify">정보수정</button></td>
        <td><button type="button" class="btn btn-white btn-sm js-eventGroupCopy">복사</button></td>
    </tr>
</script>
