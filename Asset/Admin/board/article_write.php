<?php
if ($bdWrite['cfg']['bdReplyStatusFl'] == 'y' && $req['mode'] == 'reply') {
    include "_article_qa_form.php";
} else {
    include "_article_write_form.php";
}
