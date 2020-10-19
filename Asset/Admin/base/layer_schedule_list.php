<div class="title">
    <?php if (count($schedule['requestDateList']) < 1) { ?>
        <span style="font-size:14px;font-weight:bold;color:#333"><?= date('m/d', strtotime($schedule['requestDate'])) ?></span> 일정이 없습니다.
    <?php } else { ?>
        <span style="font-size:14px;font-weight:bold;color:#333"><?= date('m/d', strtotime($schedule['requestDate'])) ?></span> <span class="count"><?= count($schedule['requestDateList']) ?>
            건</span>의 일정이 있습니다.
    <?php } ?>
    <div class="pull-right" style="padding-top:6px;color:#888;font-size:11px">
        <a href="javascript:Schedule.addToday('<?= $schedule['requestDate'] ?>')" class="btn-link">일정추가</a>
    </div>
</div>

<ul>
    <?php foreach ($schedule['requestDateList'] as $row) { ?>
        <li><?= gd_htmlspecialchars_stripslashes($row['contents']) ?></li>
    <?php } ?>
</ul>
