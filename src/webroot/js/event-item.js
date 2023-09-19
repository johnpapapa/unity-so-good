$(function(){
    function changeDisplayElement(element){
        if (element.style.display === "none"){
            element.style.display = "block";
        } else {
            element.style.display = "none";
        }
    }

    $description_toggle_obj = $('.description_toggle');
    $comments_toggle_obj = $('.comments_toggle');
    [$description_toggle_obj, $comments_toggle_obj].forEach(function(element){
        element.on('click', function(){
            changeDisplayElement($(this).next()[0]);
        });  
    })

    $('.delete-lnk').on('click', function(event){
        var res = confirm('このイベントを削除しますか?');
        if(!res){
           event.preventDefault();
        }
    });
});