<?php if($search['mode'] == 'schedule') { ?>
<div class="table-wrap">
    <div class="table_day">
        <table class="table table-rows schedule">
            <tr class="week">
                <?php if(!$isProvider) { ?>
                <td rowspan="2" width="150px;" style="font-weight: bold;">공급사</td>
                <?php } ?>
                <?php
                $checkIndex = 0;
                for($loopIndex=1; $loopIndex < $calendarData['calendar']['setRows']+1; $loopIndex ++ ) {
                        if($loopIndex == 1) {
                            $weekStartIndex = $calendarData['calendar']['startDay'];
                        } else {
                            $weekStartIndex = 0;
                        }
                        for($weekI=$weekStartIndex; $weekI < 7; $weekI++) {
                            if($checkIndex >= $calendarData['calendar']['days']) continue;
                            $convertDays = sprintf("%02d", $checkIndex+1);
                            if($convertDays == $calendarData['calendar']['checkToday']['day'] && $checkToday) {
                                $toDayStyle = 'background-color:#fcfce6;';
                            } else {
                                $toDayStyle = '';
                            }
                            if($weekI == 0) {
                                $sunDayStyle = "color:red;";
                            } else {
                                $sunDayStyle = "";
                            }
                            ?>
                    <td style="font-weight: bold;<?=$sunDayStyle;?><?=$toDayStyle;?>"><?=$calendarData['calendar']['setDay'][$weekI];?></td>
                <?php $checkIndex ++; }
                } ?>
            </tr>
            <tr class="day">
                <?php for($dayI=1; $dayI < $calendarData['calendar']['days']+1; $dayI++) {
                    $convertDays = sprintf("%02d", $dayI);
                    if($convertDays == $calendarData['calendar']['checkToday']['day'] && $checkToday) {
                        $toDayStyle = 'style="background-color:#fcfce6;"';
                    } else {
                        $toDayStyle = '';
                    }
                    $nowDateMergeData = implode('-', [$calendarData['calendar']['setYear'], $calendarData['calendar']['setMonth'] , $convertDays]);
                    if(date('w', strtotime($nowDateMergeData)) == 0) {
                        $sunDayStyle = "color:red;";
                    } else {
                        $sunDayStyle = "";
                    }
                    ?>
                <td <?=$toDayStyle;?>><span style="font-weight: bold;<?=$sunDayStyle;?>"><?=$dayI;?></span></td>
                <?php } ?>
            </tr>
            <?php foreach($calendarData['schedule']['companyNm'] as $scmNo => $companyNm) { ?>
            <tr>
                <?php if(!$isProvider) { ?>
                <td><?=$companyNm;?></td>
                <?php } ?>
                <!-- 일정 시작일에 start 클래스 추가 -->
                <?php for($dataI=1; $dataI < $calendarData['calendar']['days']+1; $dataI++) {
                    $convertDays = sprintf("%02d", $dataI);
                    if($convertDays == $calendarData['calendar']['checkToday']['day'] && $checkToday) {
                        $toDayStyle = 'style="background-color:#fcfce6;"';
                    } else {
                        $toDayStyle = '';
                    }
                    $nowDateMergeData = implode('-', [$calendarData['calendar']['setYear'], $calendarData['calendar']['setMonth'] , $convertDays]);
                    if(empty($calendarData['calendar']['data'][$nowDateMergeData]) == false) {
                        $includeFl = false;
                        foreach($calendarData['calendar']['data'][$nowDateMergeData] as $scmKey => $scmVal) {
                            if($scmVal['scmNo'] == $scmNo && $nowDateMergeData == $scmVal['startDateConvert']) {
                                if(empty($scmVal['scmCommissionDeliveryData']) === false && ( $scmVal['scmCommissionData'] != $scmVal['scmCommissionDeliveryData'])) {
                                    $scmCommissionDataMergeLayer = implode(' / ', [$scmVal['scmCommissionData'], $scmVal['scmCommissionDeliveryData']]);
                                } else {
                                    $scmCommissionDataMergeLayer = implode(' / ', [$scmVal['scmCommissionData']]);
                                }
                                $includeFl = true;
                                ?>
                                <td class="start" <?=$toDayStyle;?>>
                                    <div class="<?= $scmVal['dateDiff']; ?>">
                                        <ul class="cont">
                                        <li>
                                            <a href="javascript:void(0);" onclick="scmScheduleModify('<?=$scmVal['scmNo'];?>', '<?=$scmVal['sno'];?>');">(<?= $scmCommissionDataMergeLayer; ?>)</a>
                                            <span class="layer">(<?=$scmCommissionDataMergeLayer;?>)</span>
                                        </li>
                                        </ul>
                                    </div>
                                </td>
                                <?php
                            }
                        }
                        if($includeFl == false) {
                            echo "<td " . $toDayStyle . "></td>";
                        }
                    } else { ?>
                    <td <?=$toDayStyle;?>></td>
                <?php } } ?>
            </tr>
            <?php }
            if(empty($calendarData['schedule']['companyNm']) === true) {
                if(!$isProvider) {
                    $noDataColSpanCnt = $calendarData['calendar']['days'] + 2;
                } else {
                    $noDataColSpanCnt = $calendarData['calendar']['days'];
                }
                echo "<tr align='center'><td colspan='" . $noDataColSpanCnt . "'>등록된 정보가 없습니다.</td></tr>";
            }
            ?>
        </table>
    </div>

    <div class="center"><?=$page->getPage();?></div>
</div>
<?php } ?>

<script type="text/javascript">

    $(window).load(function() {
        $("table.schedule tr td.start").each(function(){
            var _div = $(this).find('div');
            var _width = $(this).outerWidth();
            var _class = _div.attr('class');
            var totalWidth = _width;
            for(var i=1; i < _class; i++) {
                totalWidth = totalWidth + $(this).siblings('td:eq(' + i +')').outerWidth();
            }
            var _total = totalWidth -1 ;
            _div.css("width",_total);
        });

        $(window).resize(function (){
            $("table.schedule tr td.start").each(function(){
                var _div = $(this).find('div');
                var _width = $(this).outerWidth();
                var _class = _div.attr('class');
                var totalWidth = _width;
                for(var i=1; i < _class; i++) {
                    totalWidth = totalWidth + $(this).siblings('td:eq(' + i +')').outerWidth();
                }
                var _total = totalWidth -1 ;
                _div.css("width",_total);
            });
        })
    });
</script>