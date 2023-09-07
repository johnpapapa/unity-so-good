$(function(){
    $('.description_toggle').on('click', function() {
        var description = $(this).next()[0];
        if (description.style.display === "none") {
            description.style.display = "block";
        } else {
            description.style.display = "none";
        }
    }); 

    $('.delete-lnk').on('click', function(event){
        var res = confirm('このイベントを削除しますか?');
        if(!res){
           event.preventDefault();
        }
    });

    // let selected_event_ids = [];
    let selected_cnt = 0; //チェック操作メニュー出現のトリガー
    let obj_selected_menu = $('.sticky-hover-selected-menu');
    let obj_selected_cnt = $('#selected-cnt');

    $('.select-chk').on('click', function(){
        if($(this).prop("checked")){
            selected_cnt++;
        } else {
            selected_cnt--;
        }
        
        //チェック操作メニューの出現
        if(selected_cnt > 0){
            obj_selected_menu[0].style.display = "block";
        } else {
            //display none
            obj_selected_menu[0].style.display = "none";
        }

        //カウントの個数変化
        obj_selected_cnt[0].innerHTML = selected_cnt;
    });
});