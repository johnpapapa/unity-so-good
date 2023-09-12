<?php $this->assign('title', 'admin user-detail'); ?>
<?php $this->assign('content-title', 'ユーザーの詳細'); ?>
<?php
use Cake\I18n\FrozenTime;
use Cake\Core\Configure;
$day_of_weeks = Configure::read('day_of_weeks');
?>
<style>
    .event-detail .header {
        background-color: darkgray;
        font-size: 1.05rem;
        padding: 5px;
    }
    .event-detail .content {
        font-size: 1.2rem;
    }

</style>

<div class="event-detail">
    <div class="mb30">
        <div class="header mb10">
            ユーザー名
        </div>
        <div class="content">
            <?= $user_data->display_name ?>
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            参加履歴
        </div>
        <div class="content">
            <div class="history-list" style="height: 300px; overflow:scroll;">
                <?php foreach($event_response_history_list as $event_response): ?>
                    <div class="history pb10">
                        <div class="disp-flex just-center align-center">
                            <?php if(is_null($event_response["response_state"])): ?>
                                <div class="state tc" style="width:120px; background-color: white;">
                                    未反応
                                </div>
                            <?php else: ?>
                                <div class="state tc" style="width:120px; background-color: <?= Configure::read('response_states')[$event_response["response_state"]]["tag_color"] ?>;">
                                    <?= Configure::read('response_states')[$event_response["response_state"]]["text"] ?>
                                </div>
                            <?php endif; ?>
                            <div class="location-name w100 over-ellipsis">
                                <?= $event_response["display_name"] ?>
                            </div>
                        </div>
                        <div class="date" style="font-size:small;">
                            <?php
                                $start_time = new FrozenTime($event_response["start_time"]);
                                $end_time = new FrozenTime($event_response["end_time"]);
                            ?>
                            <?= $start_time->i18nFormat('yyyy-MM-dd'); ?>
                            (<?= $day_of_weeks[$start_time->dayOfWeek] ?>)
                            <?= $start_time->i18nFormat('HH:mm'); ?> ~ <?= $end_time->i18nFormat('HH:mm');  ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            反応回数
        </div>
        <div class="content">
            <div>参加:<?= (isset($event_response_history_count_list["1"]))?$event_response_history_count_list["1"]:0 ?>回</div>
            <div>参加未定:<?= (isset($event_response_history_count_list["0"]))?$event_response_history_count_list["0"]:0 ?>回</div>
            <div>不参加:<?= (isset($event_response_history_count_list["2"]))?$event_response_history_count_list["2"]:0 ?>回</div>
            <div>未反応:<?= (isset($event_response_history_count_list["null"]))?$event_response_history_count_list["null"]:0 ?>回</div>
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            直近10イベントの反応履歴
        </div>
        <div class="content">
            <div class="history-list" style="height: 300px; overflow:scroll;">
                <?php foreach($event_response_history_limit_list  as $event_response): ?>
                    <div class="history pb10">
                        <div class="disp-flex just-center align-center">
                            <?php if(is_null($event_response["response_state"])): ?>
                                <div class="state tc" style="width:120px; background-color: white;">
                                    未反応
                                </div>
                            <?php else: ?>
                                <div class="state tc" style="width:120px; background-color: <?= Configure::read('response_states')[$event_response["response_state"]]["tag_color"] ?>;">
                                    <?= Configure::read('response_states')[$event_response["response_state"]]["text"] ?>
                                </div>
                            <?php endif; ?>
                            <div class="location-name w100 over-ellipsis">
                                <?= $event_response["display_name"] ?>
                            </div>
                        </div>
                        <div class="date" style="font-size:small;">
                            <?php
                                $start_time = new FrozenTime($event_response["start_time"]);
                                $end_time = new FrozenTime($event_response["end_time"]);
                            ?>
                            <?= $start_time->i18nFormat('yyyy-MM-dd'); ?>
                            (<?= $day_of_weeks[$start_time->dayOfWeek] ?>)
                            <?= $start_time->i18nFormat('HH:mm'); ?> ~ <?= $end_time->i18nFormat('HH:mm');  ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            コート別参加率
        </div>
        <div class="content">
            実装予定
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            曜日別参加率
        </div>
        <div class="content">
            実装予定
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            時間別参加率
        </div>
        <div class="content">
            実装予定
        </div>
    </div>
</div>

