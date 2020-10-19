<!--공급사 수수료 달력-->
<?php if($search['mode'] == 'calendar') { ?>
<div class="table-wrap">

    <div class="dim"></div>
    <table class="table table-rows cal">
        <colgroup>
            <col width="14.28%"/>
            <col width="14.28%"/>
            <col width="14.28%"/>
            <col width="14.28%"/>
            <col width="14.28%"/>
            <col width="14.28%"/>
            <col width="14.28%"/>
        </colgroup>
        <tr class="week">
            <?php for($i = 0; $i < count($calendarData['calendar']['setDay']); $i++) { ?>
                <td><?=$calendarData['calendar']['setDay'][$i]?></td>
            <?php } ?>
        </tr>
        <?php for($calendarAllRow = 0; $calendarAllRow < $calendarData['calendar']['setRows']; $calendarAllRow++) { ?>
            <tr>
                <?php
                for($cols=0; $cols < 7; $cols++ ) {
                    $cellIndex = (7 * $calendarAllRow) + $cols;
                    $convertDays = sprintf("%02d", $calendarData['calendar']['nowDayCount']);
                    $nowDateMergeData = implode('-', [$calendarData['calendar']['setYear'], $calendarData['calendar']['setMonth'] , $convertDays]);
                    // 이번달
                    if ($calendarData['calendar']['startDay'] <= $cellIndex && $calendarData['calendar']['nowDayCount'] <= $calendarData['calendar']['days'] ) {
                        $dayDataCount =  count($calendarData['calendar']['data'][$nowDateMergeData]);
                        if ( date( "w", mktime( 0, 0, 0, $calendarData['calendar']['setMonth'], $calendarData['calendar']['nowDayCount'], $calendarData['calendar']['setYear'] ) ) == 6 ) {
                            $dayTdClassName = "holiday";
                        } else if ( date( "w", mktime( 0, 0, 0, $calendarData['calendar']['setMonth'], $calendarData['calendar']['nowDayCount'], $calendarData['calendar']['setYear'] ) ) == 0 ) {
                            $dayTdClassName = "sun";
                        } else {
                            $dayTdClassName = "";
                        }
                        if($convertDays == $calendarData['calendar']['checkToday']['day'] && $checkToday) {
                            $toDayStyle = 'style="background-color:#fcfce6;"';
                        } else {
                            $toDayStyle = '';
                        }
                        ?>
                        <td class="<?=$dayTdClassName;?>" <?=$toDayStyle;?>>
                            <?php if($dayDataCount >= 5) { ?>
                            <div class="list-wrap">
                                <h4>[<?php echo str_replace('-', '.', $nowDateMergeData);?>] 공급사 수수료 <span class="pull-right"><input type="button" class="btn btn_icon_minus btn-white btn-commission-layer-close" name="" value="닫기"></span></h4>
                                <div class="table_scroll">
                                    <table class="table table-rows">
                                        <colgroup>
                                            <col width="40%"/>
                                            <col width="60%"/>
                                        </colgroup>
                                        <thead>
                                        <tr>
                                            <th>공급사</th>
                                            <th>수수료</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach($calendarData['calendar']['data'][$nowDateMergeData] as $scmKey => $scmVal) {
                                            if(empty($scmVal['scmCommissionDeliveryData']) === false && ($scmVal['scmCommissionData'] != $scmVal['scmCommissionDeliveryData'])) {
                                                $scmCommissionDataMergeLayer = implode(' / ', [$scmVal['scmCommissionData'] . '%', $scmVal['scmCommissionDeliveryData'] . '%']);
                                            } else {
                                                $scmCommissionDataMergeLayer = implode(' / ', [$scmVal['scmCommissionData'] . '%']);
                                            }
                                            ?>
                                            <tr>
                                                <th><a href="javascript:void(0);" onclick="scmScheduleModify('<?=$scmVal['scmNo'];?>', '<?=$scmVal['sno'];?>');"><?=$scmVal['companyNm'];?></a></th>
                                                <td style="vertical-align:middle;"><?=$scmCommissionDataMergeLayer;?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="top <?php if($dayDataCount >= 5) { echo 'more'; } ?>">
                                <strong><?=$calendarData['calendar']['nowDayCount']++?></strong>
                                <?php if($dayDataCount >= 5) { ?>
                                    <span class="btn-icon-plus" style="color:#ffffff;">전체보기</span>
                                <?php } ?>
                            </div>
                            <?php if(empty($calendarData['calendar']['data'][$nowDateMergeData]) === false) { ?>
                                <div class="cont">
                                    <ul>
                                        <?php foreach($calendarData['calendar']['data'][$nowDateMergeData] as $scmKey => $scmVal) {
                                            if($scmKey > 4) continue;
                                            if(empty($scmVal['scmCommissionDeliveryData']) === false && ( $scmVal['scmCommissionData'] != $scmVal['scmCommissionDeliveryData'])) {
                                                $scmCommissionDataMerge = implode(' / ', [$scmVal['scmCommissionData'], $scmVal['scmCommissionDeliveryData']]);
                                            } else {
                                                $scmCommissionDataMerge = implode(' / ', [$scmVal['scmCommissionData']]);
                                            }
                                            ?>
                                            <li><a href="javascript:void(0);" onclick="scmScheduleModify('<?=$scmVal['scmNo'];?>', '<?=$scmVal['sno'];?>');"><?=$scmVal['companyNmCut'];?> (<?=$scmCommissionDataMerge;?>)</a><span class="layer"><?=$scmVal['companyNm'];?> (<?=$scmCommissionDataMerge;?>)</span></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>
                        </td>
                        <?php
                        // 이전달
                    } else if ( $cellIndex < $calendarData['calendar']['startDay'] ) {
                        $convertDays = sprintf("%02d", $calendarData['calendar']['prevDayCount']);
                        $prevDateMergeData = implode('-', [$calendarData['calendar']['prevYear'], $calendarData['calendar']['prevMonth'] , $convertDays]);
                        $dayDataCount =  count($calendarData['calendar']['data'][$prevDateMergeData]);
                        ?>
                        <td class="before">
                            <?php if($dayDataCount >= 5) { ?>
                                <div class="list-wrap">
                                    <h4>[<?php echo str_replace('-', ' . ', $prevDateMergeData);?>] 공급사 수수료 <span class="pull-right"><input type="button" class="btn btn_icon_minus btn-white btn-commission-layer-close" name="" value="닫기"></span></h4>
                                    <div class="table_scroll">
                                        <table class="table table-rows">
                                            <colgroup>
                                                <col width="40%"/>
                                                <col width="60%"/>
                                            </colgroup>
                                            <thead>
                                            <tr>
                                                <th>공급사</th>
                                                <th>수수료</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach($calendarData['calendar']['data'][$prevDateMergeData] as $scmKey => $scmVal) {
                                                if(empty($scmVal['scmCommissionDeliveryData']) === false && ( $scmVal['scmCommissionData'] != $scmVal['scmCommissionDeliveryData'])) {
                                                    $scmCommissionDataMergeLayer = implode(' / ', [$scmVal['scmCommissionData'] . '%', $scmVal['scmCommissionDeliveryData'] . '%']);
                                                } else {
                                                    $scmCommissionDataMergeLayer = implode(' / ', [$scmVal['scmCommissionData'] . '%']);
                                                }
                                                ?>
                                                <tr>
                                                    <th><a href="javascript:void(0);" onclick="scmScheduleModify('<?=$scmVal['scmNo'];?>', '<?=$scmVal['sno'];?>');"><?=$scmVal['companyNm'];?></a></th>
                                                    <td style="vertical-align:middle;"><?=$scmCommissionDataMergeLayer;?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="top <?php if($dayDataCount >= 5) { echo 'more'; } ?>">
                                <strong><?=$calendarData['calendar']['prevDayCount']++?></strong>
                                <?php if($dayDataCount >= 5) { ?>
                                    <span class="btn-icon-plus" style="color:#ffffff;">전체보기</span>
                                <?php } ?>
                            </div>
                            <?php if(empty($calendarData['calendar']['data'][$prevDateMergeData]) === false) { ?>
                                <div class="cont">
                                    <ul>
                                        <?php foreach($calendarData['calendar']['data'][$prevDateMergeData] as $scmKey => $scmVal) {
                                            if($scmKey > 4) continue;
                                            if(empty($scmVal['scmCommissionDeliveryData']) === false && ( $scmVal['scmCommissionData'] != $scmVal['scmCommissionDeliveryData'])) {
                                                $scmCommissionDataMerge = implode(' / ', [$scmVal['scmCommissionData'], $scmVal['scmCommissionDeliveryData']]);
                                            } else {
                                                $scmCommissionDataMerge = implode(' / ', [$scmVal['scmCommissionData']]);
                                            }
                                            ?>
                                            <li><a href="javascript:void(0);" onclick="scmScheduleModify('<?=$scmVal['scmNo'];?>', '<?=$scmVal['sno'];?>');"><?=$scmVal['companyNmCut'];?> (<?=$scmCommissionDataMerge;?>)</a><span class="layer"><?=$scmVal['companyNm'];?> (<?=$scmCommissionDataMerge;?>)</span></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>
                        </td>
                        <?php
                        // 다음달
                    } else if ( $cellIndex >= $calendarData['calendar']['days'] ) {
                        $convertDays = sprintf("%02d", $calendarData['calendar']['nextDayCount']);
                        $nextDateMergeData = implode('-', [$calendarData['calendar']['nextYear'], $calendarData['calendar']['nextMonth'] , $convertDays]);
                        $dayDataCount =  count($calendarData['calendar']['data'][$nextDateMergeData]);
                        ?>
                        <td class="after">
                            <?php if($dayDataCount >= 5) { ?>
                                <div class="list-wrap">
                                    <h4>[<?php echo str_replace('-', ' . ', $nextDateMergeData);?>] 공급사 수수료 <span class="pull-right"><input type="button" class="btn btn_icon_minus btn-white btn-commission-layer-close" name="" value="닫기"></span></h4>
                                    <div class="table_scroll">
                                        <table class="table table-rows">
                                            <colgroup>
                                                <col width="40%"/>
                                                <col width="60%"/>
                                            </colgroup>
                                            <thead>
                                            <tr>
                                                <th>공급사</th>
                                                <th>수수료</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach($calendarData['calendar']['data'][$nextDateMergeData] as $scmKey => $scmVal) {
                                                if(empty($scmVal['scmCommissionDeliveryData']) === false && ( $scmVal['scmCommissionData'] != $scmVal['scmCommissionDeliveryData'])) {
                                                    $scmCommissionDataMergeLayer = implode(' / ', [$scmVal['scmCommissionData'] . '%', $scmVal['scmCommissionDeliveryData'] . '%']);
                                                } else {
                                                    $scmCommissionDataMergeLayer = implode(' / ', [$scmVal['scmCommissionData'] . '%']);
                                                }
                                                ?>
                                                <tr>
                                                    <th><a href="javascript:void(0);" onclick="scmScheduleModify('<?=$scmVal['scmNo'];?>', '<?=$scmVal['sno'];?>');"><?=$scmVal['companyNm'];?></a></th>
                                                    <td style="vertical-align:middle;"><?=$scmCommissionDataMergeLayer;?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="top <?php if($dayDataCount >= 5) { echo 'more'; } ?>">
                                <strong><?=$calendarData['calendar']['nextDayCount']++?></strong>
                                <?php if($dayDataCount >= 5) { ?>
                                    <span class="btn-icon-plus" style="color:#ffffff;">전체보기</span>
                                <?php } ?>
                            </div>
                            <?php if(empty($calendarData['calendar']['data'][$nextDateMergeData]) === false) { ?>
                                <div class="cont">
                                    <ul>
                                        <?php foreach($calendarData['calendar']['data'][$nextDateMergeData] as $scmKey => $scmVal) {
                                            if($scmKey > 4) continue;
                                            if(empty($scmVal['scmCommissionDeliveryData']) === false && ( $scmVal['scmCommissionData'] != $scmVal['scmCommissionDeliveryData'])) {
                                                $scmCommissionDataMerge = implode(' / ', [$scmVal['scmCommissionData'], $scmVal['scmCommissionDeliveryData']]);
                                            } else {
                                                $scmCommissionDataMerge = implode(' / ', [$scmVal['scmCommissionData']]);
                                            }
                                            ?>
                                            <li><a href="javascript:void(0);" onclick="scmScheduleModify('<?=$scmVal['scmNo'];?>', '<?=$scmVal['sno'];?>');"><?=$scmVal['companyNmCut'];?> (<?=$scmCommissionDataMerge;?>)</a><span class="layer"><?=$scmVal['companyNm'];?> (<?=$scmCommissionDataMerge;?>)</span></li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            <?php } ?>
                        </td>
                    <?php }
                    }
                ?>
            </tr>
        <?php } ?>
    </table>
</div>
<?php } ?>
