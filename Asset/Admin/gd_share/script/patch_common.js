function remove(sno) {
    dialog_confirm('삭제하시겠습니까?',function(data){
       if(data){
           frmList.mode.value ='delete';
           frmList.sno.value = sno;
           frmList.submit();
       }
    });
}

function modify(sno) {
    top.location.href='./patch_register.php?mode=modify&sno='+sno;
}

function view(sno) {
    top.location.href='./patch_view.php?sno='+sno;
}

function register() {
    top.location.href='./patch_register.php';
}

function patchFileAdd(sno,deviceType) {
    window.open('./patch_file_register.php?patchArticleSno='+sno+'&deviceType='+deviceType,'popup','width=1200,height=700,scrollbar=y');
}

function goSourceDiff(sno,type){
    var url = './patch_file_diff.php?sno='+sno;
    if(type == 'popup'){
        window.open(url,'popup','width=1600,height=800,scrollbar=y,left=300');
    }
    else {
        location.href=url;
    }
}
