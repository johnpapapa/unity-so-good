<?php $this->assign('title', 'event add'); ?>
<?php $this->assign('content-title', 'イベントの追加'); ?>
<script>
    let locations = <?= json_encode($locations) ?>;
</script>
<style>
    .disabled {
        background-color: gray !important;
        cursor: not-allowed;
    }

    /* input[type=textarea] {
        padding: 0.5em 0.6em;
    display: inline-block;
    border: 1px solid #ccc;
    box-shadow: inset 0 1px 3px #ddd;
    border-radius: 4px;
    vertical-align: middle;
    box-sizing: border-box;
    } */
</style>

<div class="events form pure-form pure-form-stacked">
<?= $this->Form->create() ?>
    <fieldset>
        <div class="location_name_input">
            <label for="display_name">コート名</label>
            <p style="font-size: 12px; color:gray;">
                入力フォームの候補に該当するコートが存在する場合、<br>
                候補のタップを優先してください(新規コートのチェックを外せます)<br>
            </p>
            <input type="text" class="pure-u-1" name="display_name" id="display_name" autocomplete="on" list="locations" placeholder="コート名">
            <datalist id="locations">
                <?php foreach($locations as $location): ?>
                    <option value="<?= $location->display_name ?>"></option>
                <?php endforeach; ?>
            </datalist>

            <div class="location_new">
                <input type="checkbox" name="location_new_check" id="location_new_check" checked>
                新規コート
            </div>
            
            <div class="location_data_input_collapse_btn">
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
                <label for="address">住所</label>
                <input type="text" name="address" id="address" placeholder="住所">
    
                <label for="usage_price">コート使用料</label>
                <input type="number" name="usage_price" id="usage_price" placeholder="コート使用料">
                
                <label for="night_price">コート使用料(ナイター)</label>
                <input type="number" name="night_price" id="night_price" placeholder="コート使用料(ナイター)">

                <input type="hidden" id="location_id" name="location_id" value="">
            </div>

        </div>

        <div class="input text">
            <label for="area">コート番号</label>
            <p style="font-size: 12px; color:gray;">無入力可</p>
            <input type="text" name="area" id="area" value="" maxlength="255"  placeholder="コート番号">
        </div>        
        <div class="input number required">
            <label for="participants_limit">参加人数上限</label>
            <input type="number" id="participants_limit" name="participants_limit" required="required" 
                data-validity-message="This field cannot be left empty" aria-required="true" value="8"  placeholder="参加人数上限" read>
            <div class="participants_limit_none">
                <input type="checkbox" id="participants_limit_none_check">
                参加人数無制限
            </div>
        </div>       
        <div class="input text">
            <label for="comment">コメント・注意事項</label>
            <p style="font-size: 12px; color:gray;">無入力可</p>
            <!-- <input type="textarea" name="comment" id="comment" maxlength="255" placeholder="コメント・注意事項" rows="5"> -->
            <textarea name="comment" id="comment" maxlength="255" placeholder="コメント・注意事項" rows="5"></textarea>
        </div>

        <div class="input date">
            <label for="event_date">日付</label>
            <input type="text" name="event_date" id="event_date" required="required" placeholder="日付">
        </div>
        <div class="input datetime required">
            <label for="start_time">開始時刻</label>
            <p style="font-size: 12px; color:gray;">
                時刻を表現する半角英数字4文字を入力してください<br>
                (例)9時45分=>0945, 23時05分=>2305 (24時間形式)
            </p>
            <input type="text" name="start_time" id="start_time" required="required" maxlength="4" pattern="^([01][0-9]|2[0-3])[0-5][0-9]$" title="Please enter 4 characters that can represent the time." placeholder="開始時刻">
        </div>        
        <div class="input datetime required">
            <label for="end_time">終了時刻</label>
            <p style="font-size: 12px; color:gray;">
                時刻を表現する半角英数字4文字を入力してください<br>
                (例)9時45分=>0945, 23時05分=>2305 (24時間形式)
            </p>
            <input type="text" name="end_time" id="end_time" required="required" maxlength="4" pattern="^([01][0-9]|2[0-3])[0-5][0-9]$" title="Please enter 4 characters that can represent the time." placeholder="終了時刻">
        </div>
   </fieldset>
<?= $this->Form->button(__('Submit')); ?>
<?= $this->Form->end() ?>
</div>

<script>
    $(function(){
        let obj_display_name = $("#display_name");
        let obj_address = $('#address');
        let obj_usage_price = $('#usage_price');
        let obj_night_price = $('#night_price');
        let obj_location_id = $('#location_id');
        let obj_location_new_check = $('#location_new_check');

        let obj_participants_limit = $('#participants_limit');
        let obj_participants_limit_none_check = $('#participants_limit_none_check');

        let obj_location_data_input_expand = $(".location_data_input_expand");
        let obj_location_data_input_collapse = $(".location_data_input_collapse");
        let obj_location_data_input = $('.location_data_input');

        let obj_event_date = $('#event_date');
        

        obj_location_data_input_expand.on('click', function(){
            obj_location_data_input.show();
            obj_location_data_input_collapse.show()
            $(this).hide()
        });

        obj_location_data_input_collapse.on('click', function(){
            obj_location_data_input.hide();
            obj_location_data_input_expand.show()
            $(this).hide()
        });

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


        $("#display_name").keyup(function(){
            let textbox_val = $(this).val();

            if(textbox_val in locations){
                let location = locations[textbox_val];

                obj_address.val(location['address']);
                obj_usage_price.val(location['usage_price']);
                obj_night_price.val(location['night_price']);
                obj_location_id.val(location['id']);

                obj_location_new_check.prop("checked", false);
            } else {
                if(obj_location_id.val() !== ""){
                    obj_address.val("");
                    obj_usage_price.val("");
                    obj_night_price.val("");
                    obj_location_id.val("");
                }

                obj_location_new_check.prop("checked", true);
            }
        });

        $.datepicker.setDefaults($.datepicker.regional["ja"]);
        obj_event_date.datepicker({
            showOtherMonths: true, //他の月を表示
            selectOtherMonths: true //他の月を選択可能
        });
        
    });
</script>