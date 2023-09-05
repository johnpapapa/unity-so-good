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
</style>

<div class="events form pure-form pure-form-stacked">
<?= $this->Form->create() ?>
    <fieldset>
        <div class="location_name_input mb20">
            <div class="mb20">
                <label for="display_name">コート名</label>
                <input type="text" class="pure-u-1" name="display_name" id="display_name" placeholder="コート名">

                <select name="display_name_select" id="display_name_select" size="5" style="height: 200px;">
                    <!-- <option value="new">新規</option>
                    <?php foreach($locations as $location): ?>
                        <option value="<?= $location->display_name ?>"><?= $location->display_name ?></option>
                    <?php endforeach; ?> -->
                
                </select>
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
                <label for="address">住所</label>
                <input type="text" name="address" id="address" placeholder="住所">
    
                <label for="usage_price">コート使用料</label>
                <input type="number" name="usage_price" id="usage_price" placeholder="コート使用料">
                
                <label for="night_price">コート使用料(ナイター)</label>
                <input type="number" name="night_price" id="night_price" placeholder="コート使用料(ナイター)">

                <input type="hidden" id="location_id" name="location_id" value="">
            </div>

        </div>

        <div class="input text mb20">
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
<?= $this->Form->button(__('新規登録')); ?>
<?= $this->Form->end() ?>
</div>

<script>
    $(function(){
        let obj_display_name = $("#display_name");
        let obj_display_name_select = $("#display_name_select");
        let obj_address = $('#address');
        let obj_usage_price = $('#usage_price');
        let obj_night_price = $('#night_price');
        let obj_location_id = $('#location_id');

        let obj_participants_limit = $('#participants_limit');
        let obj_participants_limit_none_check = $('#participants_limit_none_check');

        let obj_location_data_input_expand = $(".location_data_input_expand");
        let obj_location_data_input_collapse = $(".location_data_input_collapse");
        let obj_location_data_input = $('.location_data_input');

        let obj_event_date = $('#event_date');
        // let obj_event_date = $('.date-btn');
        

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

        //コート名の候補に関するoption初期状態
        setOptions("");

        //候補を出力する処理のためにコート名入力時に発火するイベント
        let before_input = ''; //コート名入力される直前の値
        obj_display_name.on("input", function(){
            let input_location_name = $(this).val();
            let is_delete = before_input > input_location_name;
            setOptions(input_location_name, is_delete);
            before_input = input_location_name;
        });

        //入力されたコート名が既存のコート名と一致する場合候補として出力する処理
        function setOptions(input_location_name, is_delete=false){
            let option_arr = ["<option value='new'>新規</option>"]; //候補
            let last_idx = -1; //候補が新規の他に一個しかない時のidx
            for (var location_idx = 0; location_idx < locations.length; location_idx++) { //何も入力してない場合は全部表示
                if (locations[location_idx]["display_name"].indexOf(input_location_name) != -1 || input_location_name == "") {
                    option_arr.push("<option value="+ location_idx +">" + locations[location_idx]["display_name"] + "</option>");
                    last_idx = location_idx;
                }
            }
            
            if(is_delete){ //何も入力してない場合はコート情報全消し
                onSelected(is_delete);
            }
            
            //候補が特定の個数しかない時選択済みにする処理
            if (option_arr.length == 1){
                option_arr[0] = "<option value='new' selected>新規</option>";
                onSelected(is_delete);
            }
            if (option_arr.length == 2 && is_delete === false){
                option_arr[1] = "<option value="+ last_idx +" selected>" + locations[last_idx]["display_name"] + "</option>";
                onSelected(is_delete);
            }
            obj_display_name_select[0].innerHTML = option_arr.join("");
        }

        obj_display_name_select.change(function(){
            onSelected();
        });
        
        //selectされた時にコート情報を入力するかリセットするかの処理
        function onSelected(is_delete=false){
            let location_idx = obj_display_name_select.children('option:selected').val();
            let location = locations[location_idx];
            if(location && !is_delete){
                obj_display_name.val(location['display_name']);
                obj_address.val(location['address']);
                obj_usage_price.val(location['usage_price']);
                obj_night_price.val(location['night_price']);
                obj_location_id.val(location['id']);
                before_input = location['display_name'];
            } else {
                obj_address.val("");
                obj_usage_price.val("");
                obj_night_price.val("");
                obj_location_id.val("");
                before_input = "";
            }


        }


        // $("#display_name").keyup(function(){
        //     let textbox_val = $(this).val();

        //     if(textbox_val in locations){
        //         let location = locations[textbox_val];

        //         obj_address.val(location['address']);
        //         obj_usage_price.val(location['usage_price']);
        //         obj_night_price.val(location['night_price']);
        //         obj_location_id.val(location['id']);

        //         obj_location_new_check.prop("checked", false);
        //     } else {
        //         if(obj_location_id.val() !== ""){
        //             obj_address.val("");
        //             obj_usage_price.val("");
        //             obj_night_price.val("");
        //             obj_location_id.val("");
        //         }

        //         obj_location_new_check.prop("checked", true);
        //     }
        // });

        $.datepicker.setDefaults($.datepicker.regional["ja"]);
        obj_event_date.datepicker({
            showOtherMonths: true, //他の月を表示
            selectOtherMonths: true, //他の月を選択可能
            showOn: "button",
            // buttonImage:"https://icons8.jp/icon/h2TDxANl6COB/%E5%88%86%E6%95%A3%E3%83%8D%E3%83%83%E3%83%88%E3%83%AF%E3%83%BC%E3%82%AF",
            buttonText:"カレンダーで日付を指定"
            // buttonImage: "ui/datepicker/jquery-ui-datepicker-buttonimage.png",
            // buttonImageOnly: true
        });
        
    });
</script>