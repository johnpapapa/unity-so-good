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
});