<?php if ($mainServiceAccess === true && $mainServiceBoardAccess == '') return; ?>
<div class="main-section">
    <div class="table-title btm-line">
        <span class="gd-help-manual">문의/답변관리</span>
        <div class="pull-right"><a href="#" class="btn btn-icon-setting btn-sm btn-white js-setting-cs">세팅</a></div>
    </div>
    <div class="qa-board-box reform">
        <?php
        $boardList = [];
        $boardList[] = '<ol class="content list-unstyled reform">';
        $boardDataKeys = array_keys($boardData);
        $boardLinkKeys = array_keys($boardLink);
        for ($i = 0; $i < 4; $i++) {
            if (empty($boardDataKeys[$i])) {
                $boardList[] = '<li></li>';
                continue;
            }
            //플러스리뷰 사용중인경우 출력
            if($boardDataKeys[$i] == 'plusReview' && gd_is_plus_shop(PLUSSHOP_CODE_REVIEW) === false){
                continue;
            }
            if (key_exists('na', $boardData[$boardDataKeys[$i]])) {
                $boardList[] = '<li><a href="' . $boardLink[$boardLinkKeys[$i]] . '">' . $boardData[$boardDataKeys[$i]]['name'] . '<span class="qa-board-val">';
                $boardList[] = '<b class="c-gdred">' . $boardData[$boardDataKeys[$i]]['na'] . '</b>/';
                $boardList[] = $boardData[$boardDataKeys[$i]]['count'] . '건</span></a></li>';
            } else {
                $boardList[] = '<li><a href="' . $boardLink[$boardLinkKeys[$i]] . '">' . $boardData[$boardDataKeys[$i]]['name'] . '<span class="qa-board-val"><b class="c-gdred">' . $boardData[$boardDataKeys[$i]]['count'] . '</b>건</span></a></li>';
            }
        }
        $boardList[] = '</ol>';
        echo implode('', $boardList);
        ?>
    </div>
</div>
