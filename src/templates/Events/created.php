<?php $this->assign('title', 'event created'); ?>
<?php $this->assign('content-title', '作成済イベント一覧'); ?>


<script>
    let delete_ajax_send_url = "<?= $this->Url->build(['controller' => 'Events', 'action' => 'ajaxDeleteEvent']) ?>";
    let share_ajax_send_url = "<?= $this->Url->build(['controller' => 'Events', 'action' => 'ajaxShareEvent']) ?>";
    let ajax_send_token = "<?= $this->request->getAttribute('csrfToken') ?>";
</script>

<a class="nostyle-a" href="<?= $this->Url->build(['controller' => 'events','action' => 'add']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        イベントの新規作成
    </div>
</a>
<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayCreatedBtn' => true)); ?>
<?php endforeach; ?>

<div class="sticky-hover-selected-menu" style="display: none;">
    <div class="disp-iblock">
        選択済:<span id="selected-cnt">0</span>個
    </div>
    
    <a href="" id="select-delete-lnk">
        <button style="width: 70px; height: 30px;" id="select-delete-btn">
            削除
        </button>
    </a>

    <a href="" id="select-share-lnk">
        <button style="width: 70px; height: 30px;" id="select-share-btn">
            共有
        </button>
    </a>
    <div id="share-result" style="display: none;">
        <textarea id="share-text" rows="10" style="width: 100%;"></textarea>
        <button style="width: 100%; height: 30px;" id="copy-text-btn">
            文章をコピー
        </button>
    </div>
</div>

<style>
    .sticky-hover-selected-menu{
        position: fixed;
        top: 100px;
        left: 50%;
        right: 0px;
        width: 70%;
        
        text-align: center;
        padding: 5px 15px;
        background-color: white;
        transform: translateX(-50%);
        box-sizing: content-box;
        border: black 1px solid;
        border-radius: 10px;
    }
</style>

<script>
    let obj_select_delete_lnk = $('#select-delete-lnk');
    let obj_select_share_lnk = $('#select-share-lnk');
    let obj_share_result = $('#share-result');
    let obj_share_text = $('#share-text');
    let obj_copy_text_btn = $('#copy-text-btn');

    obj_copy_text_btn.on('click', function(){
        obj_share_result[0].style.display = "none";
        navigator.clipboard.writeText(obj_share_text.val());
    });

    obj_select_delete_lnk.on('click', function(){
        event.preventDefault();
        let selected_event_ids = [];
        $('.select-chk').each(function(){
            if($(this).prop("checked")){
                selected_event_ids.push($(this).val());
            }
        });

        let send_data = {
            "event_ids": selected_event_ids,
        };

        $.ajax({
            type: "post",
            url: delete_ajax_send_url,
            data: send_data,
            headers: { 'X-CSRF-Token' : ajax_send_token },
        }).done(function(response){
            console.log(response);
        }).fail(function(jqXHR){
            console.error('Error : ', jqXHR.status, jqXHR.statusText);
        });
    })

    obj_select_share_lnk.on('click', function(){
        event.preventDefault();
        let selected_event_ids = [];
        $('.select-chk').each(function(){
            if($(this).prop("checked")){
                selected_event_ids.push($(this).val());
            }
        });

        let send_data = {
            "event_ids": selected_event_ids,
        };

        $.ajax({
            type: "post",
            url: share_ajax_send_url,
            data: send_data,
            headers: { 'X-CSRF-Token' : ajax_send_token },
        }).done(function(response){
            console.log(response);
            obj_share_result[0].style.display = "block";
            obj_share_text.val(response['content']);
            
        }).fail(function(jqXHR){
            console.error('Error : ', jqXHR.status, jqXHR.statusText);
        });
    })
</script>