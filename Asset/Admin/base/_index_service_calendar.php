<?php if ($mainServiceAccess === true && $mainServiceCalendarAccess == '') return; ?>
<div class="main-section">
    <div class="table-title">
        <span class="gd-help-manual">주요일정</span>
    </div>
    <div class="calendar-box">
        <div class="col-calendar">
        <!-- <div class="col-xs-6">-->
            <div class="calendar">
                <!-- 스케줄(일정관리) -->
                <div class="work_calender" id="schedule">
                    <h3 class="hidden">일정관리</h3>
                </div>
                <script type="text/javascript">
                    var schObj;
                    $(function(){
                        schObj = $('#schedule').schedule();
                    });
                </script>
                <!-- //스케줄(일정관리) -->
            </div>
        </div>
        <div class="col-schedule">
        <!--            <div class="col-xs-6">-->
            <div class="memo">
                <div class="title">
                    <?php if (count($schedule['todayList']) < 1) { ?>
                        <span style="font-size:14px;font-weight:bold;color:#333"><?= $schedule['todayDate'] ?></span> 일정이 없습니다.
                    <?php } else { ?>
                        <span style="font-size:14px;font-weight:bold;color:#333"><?= $schedule['todayDate'] ?></span>
                        <span class="count"><?= count($schedule['todayList']) ?>
                            건
                        </span>의 일정이 있습니다.
                    <?php } ?>
                    <div class="pull-right" style="padding-top:6px;color:#888;font-size:11px">
                        <a href="javascript:Schedule.addToday()" class="btn-link">일정추가</a>
                    </div>
                </div>
                <ul>
                    <?php foreach ($schedule['todayList'] as $row) { ?>
                        <li><?= gd_htmlspecialchars_stripslashes($row['contents']) ?></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="clear-both"></div>
    </div>
</div>
