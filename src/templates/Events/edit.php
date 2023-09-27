<?php
/**
 * @var \App\View\AppView $this
 * @var object $event_data
 * @var mixed $locations
 */
?>
<?php $this->assign('title', 'event edit'); ?>
<?php $this->assign('content-title', 'イベントの編集'); ?>

<script>
    let locations = <?= json_encode($locations) ?>;
</script>
<style>
    .disabled {
        background-color: gray !important;
        cursor: not-allowed;
    }

    .events .ui-datepicker-trigger {
        font-family: inherit;
        font-size: 100%;
        padding: 0.5em 1em;
        color: rgba(0,0,0,.8);
        border: none transparent;
        background-color: #e6e6e6;
        text-decoration: none;
        border-radius: 2px;

        display: inline-block;
        line-height: normal;
        white-space: nowrap;
        vertical-align: middle;
        text-align: center;
        cursor: pointer;
        -webkit-user-drag: none;
        -webkit-user-select: none;
        user-select: none;
        box-sizing: border-box;
    }

    /* コート名入力の際の候補の文字サイズ */
    .ui-menu-item-wrapper { 
        font-size: 20px;
    }

    .events .location-data-exist-status .is-exist{ color: green;  }
    .events .location-data-exist-status .is-notexist{ color: red;  }

    /* カレンダーのサイズ */
    .ui-datepicker{
        font-size: 20px;
    }

    /* 時刻入力時のアイコン非表示かつ選択範囲全体化 */
    .events input[type="time"] {
        position: relative;
    }
    .events input[type="time"]::-webkit-calendar-picker-indicator {
        position: absolute;
        opacity: 0;
        width: 100%;
    }
</style>

<div class="events form pure-form pure-form-stacked">
<?= $this->Form->create() ?>
    <fieldset>
        <div class="location_name_input mb20">
            <div class="mb20">
                <label for="display_name">コート名</label>
                <p class="note-p">
                入力フォームの候補に該当するコートが存在する場合、<br>
                候補のタップを優先してください<br>
                </p>
                <input type="text" class="pure-u-1" name="display_name" id="display_name" placeholder="コート名" value="<?= $event_data->location->display_name ?>" required="required">
                <div class="location-data-exist-status">
                    コート情報 : <span id="status-text" class="is-notexist">新規コート</span>
                </div>
            </div>

            <div class="location_data_input_collapse_btn mb20">
                <div class="location_data_input_expand disp-flex align-center">
                    <span class="material-symbols-outlined ">
                    expand_all
                    </span>
                    コート情報の入力画面展開
                </div>
                <div class="location_data_input_collapse disp-flex align-center" style="display: none;">
                    <span class="material-symbols-outlined" >
                    collapse_all
                    </span>
                    コート情報の入力画面収納
                </div>
            </div>
            <div class="location_data_input" style="display: none;">
                <p class="note-p mb20">
                    ・既存のコート名を指定した場合、<br>
                    コート情報の入力画面は自動で収納します<br>
                    ・既存のコート情報の編集も可能です<br>
                </p>
                <label for="address">住所</label>
                <input type="text" class="pure-u-1" style="margin-bottom: 20px;" name="address" id="address" placeholder="住所"  value="<?= $event_data->location->address ?>">
    
                <label for="usage_price">コート使用料</label>
                <input type="number" class="pure-u-1" style="margin-bottom: 20px;" name="usage_price" id="usage_price" placeholder="コート使用料"  value="<?= $event_data->location->usage_price ?>">
                
                <label for="night_price">コート使用料(ナイター)</label>
                <input type="number" class="pure-u-1" style="margin-bottom: 20px;" name="night_price" id="night_price" placeholder="コート使用料(ナイター)"  value="<?= $event_data->location->night_price ?>">

                <input type="hidden" id="location_id" name="location_id" value="<?= $event_data->location->id ?>">
            </div>
        </div>

        <div class="input text mb20">
            <label for="area">コート番号</label>
            <p style="font-size: 12px; color:gray;">
                無入力可
                <br>カンマ区切りの英数字を入力してください
            </p>
            <input type="text" class="pure-u-1" name="area" id="area" value="<?= $event_data->area ?>" maxlength="255"  placeholder="コート番号 (例:A,B,1,2)">
        </div>        
        <div class="input number required mb20">
            <label for="participants_limit">参加人数上限</label>
            <input type="number" class="pure-u-1 <?=  ($event_data->participants_limit <= 0)? 'disabled' : '' ?>" id="participants_limit" name="participants_limit" required="required" 
                data-validity-message="This field cannot be left empty" aria-required="true" value="<?= $event_data->participants_limit ?>" placeholder="参加人数上限" <?=  ($event_data->participants_limit <= 0)? 'readonly' : 'read' ?>>
            <div class="participants_limit_none">
                <input type="checkbox" id="participants_limit_none_check" <?= ($event_data->participants_limit <= 0)? 'checked' : '' ?>>
                参加人数無制限
            </div>
        </div>       
        <div class="input text mb20">
            <label for="comment">コメント・注意事項</label>
            <p style="font-size: 12px; color:gray;">無入力可</p>
            <textarea name="comment" class="pure-u-1" id="comment" maxlength="255" placeholder="コメント・注意事項" rows="5"><?= $event_data->comment ?></textarea>
        </div>

        <div class="input date mb20">
            <label for="event_date">日付</label>
            <p class="note-p">
                カレンダーで日付を指定してください    
            </p>
            
            <input type="text" class="pure-u-1" name="event_date" id="event_date" required="required" placeholder="日付" readonly tabindex="-1" value="<?= $event_data->start_time->i18nFormat('yyyy-MM-dd'); ?>">
        </div>
        <div class="input datetime required mb20">
            <label for="start_time">開始時刻</label>
            <input type="time" class="pure-u-1" name="start_time" id="start_time" value="<?= $event_data->start_time->i18nFormat('HH:mm') ?>" placeholder="開始時刻" required>
        </div>        
        <div class="input datetime required mb20">
            <label for="end_time">終了時刻</label>
            <input type="time" class="pure-u-1" name="end_time" id="end_time" value="<?= $event_data->end_time->i18nFormat('HH:mm') ?>" placeholder="終了時刻" required>
        </div>
   </fieldset>
   <div class="mb10">
        <button type="submit" name="submit" class="pure-button pure-button-primary">変更を保存</button>
    </div>
<?= $this->Form->end() ?>
</div>


<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css">
<?= $this->Html->script('jquery.ui.autocomplete.scroll.min.js', array('inline' => false)); ?>
<script>
    $(function(){
        let obj_display_name = $("#display_name");
        let obj_display_name_select = $("#display_name_select");
        let obj_address = $('#address');
        let obj_usage_price = $('#usage_price');
        let obj_night_price = $('#night_price');
        let obj_location_id = $('#location_id');
        let obj_location_data_exist_status = $('#status-text');

        let obj_participants_limit = $('#participants_limit');
        let obj_participants_limit_none_check = $('#participants_limit_none_check');

        let obj_location_data_input_expand = $(".location_data_input_expand");
        let obj_location_data_input_collapse = $(".location_data_input_collapse");
        let obj_location_data_input = $('.location_data_input');

        let obj_event_date = $('#event_date');

        $(obj_participants_limit_none_check).on('click', function(){
            if ($(this).prop("checked")){
                obj_participants_limit.prop('readonly', true);
                obj_participants_limit.addClass('disabled');
                obj_participants_limit.val(-1);
            } else {
                obj_participants_limit.prop('readonly', false);
                obj_participants_limit.removeClass('disabled');
                obj_participants_limit.val(8);
            }
        });

        location_data_input_toggle_view(false);
        obj_location_data_input_expand.on('click', function(){
            location_data_input_toggle_view(true)
        });
        obj_location_data_input_collapse.on('click', function(){
            location_data_input_toggle_view(false)
        });

        function location_data_input_toggle_view(is_show=false){
            if(is_show){
                obj_location_data_input.show();
                obj_location_data_input_collapse.show();
                obj_location_data_input_expand.hide();
            } else {
                obj_location_data_input.hide();
                obj_location_data_input_expand.show();
                obj_location_data_input_collapse.hide();
            }
            
        }

        obj_display_name.autocomplete({
            source: Object.keys(locations),
            maxShowItems: 5,
            width: '100%',
            select: function (event, ui) { //autocompleteで候補を選択した時に発火
                onSelected(locations[ui.item.value]);
            }
        });

        //selectされた時にコート情報を入力するかリセットするかの処理
        function onSelected(location = ""){
            if (location != ""){
                obj_address.val(location['address']);
                obj_usage_price.val(location['usage_price']);
                obj_night_price.val(location['night_price']);
                obj_location_id.val(location['id']);
                obj_location_data_exist_status[0].innerHTML='既存コート';
                obj_location_data_exist_status.removeClass('is-notexist')
                obj_location_data_exist_status.addClass('is-exist')
                location_data_input_toggle_view(false);
            } else {
                obj_address.val("");
                obj_usage_price.val("");
                obj_night_price.val("");
                obj_location_id.val("");
                obj_location_data_exist_status[0].innerHTML='新規コート';
                obj_location_data_exist_status.removeClass('is-exist')
                obj_location_data_exist_status.addClass('is-notexist')
                location_data_input_toggle_view(true);
            }
        }

        obj_display_name.on("input", function(){
            onSelected();
        });

        $.datepicker.setDefaults($.datepicker.regional["ja"]);
        obj_event_date.datepicker({
            showOtherMonths: true, //他の月を表示
            selectOtherMonths: true, //他の月を選択可能
            showOn: "button",
            dateFormat: 'yy-mm-dd',
            buttonText:"カレンダーで日付を指定"

        });
    });
</script>