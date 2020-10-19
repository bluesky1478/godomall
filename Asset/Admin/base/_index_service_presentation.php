<?php if ($mainServiceAccess === true && $mainServicePresentationAccess == '') return; ?>
<?php if (array_sum($mainStatisticsAccess) > 0) { ?>
<iframe name="mainStatistics" src="./main_statistics.php" class="main-statistics pd0 mg0" scrolling="no" frameborder="0"></iframe>
<?php } ?>

