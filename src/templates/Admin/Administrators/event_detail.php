<?php $this->assign('title', 'admin event-detail'); ?>
<?php $this->assign('content-title', 'イベントの詳細'); ?>
<?php
use Cake\Core\Configure;
$day_of_weeks = Configure::read('day_of_weeks');
?>
<style>
    .user-detail .header {
        background-color: darkgray;
        font-size: 1.05rem;
        padding: 5px;
    }

    .user-detail .content {
        font-size: 1.2rem;
    }

    .user-detail .participant-history .switch-state-btn-list {
        font-size: 1.05rem;
        border: black 1px solid;
    }
    .user-detail .participant-history .switch-state-btn {padding: 15px 0px;}
    .user-detail .participant-history .switch-state-btn.enable {background-color: lightcoral;}
    .user-detail .participant-history .state-list {display: none;}
    .user-detail .participant-history .state-list.enable {display: block;}
</style>

<div class="user-detail">
    <div class="mb30">
        <div class="header mb10">
            ID
        </div>
        <div class="content">
            <?= $event->id ?>
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            イベント状況
        </div>
        <div class="content">
            <?= ($event->deleted_at)?"削除済":"公開中" ?>
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10 over-ellipsis">
            場所
        </div>
        <div class="content">
            <?= $event->location->display_name ?>
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            コート
        </div>
        <div class="content">
            <?= $event->area ?>
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            開催日
        </div>
        <div class="content">
            <?= $event->start_time->i18nFormat('yyyy-MM-dd'); ?>
            (<?= $day_of_weeks[$event->start_time->dayOfWeek] ?>)
        </div>
    </div>
    <div class="mb30">
        <div class="header mb10">
            時刻
        </div>
        <div class="content">
            <?= $event->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event->end_time->i18nFormat('HH:mm');  ?>
        </div>
    </div>
    


    <div class="participant-history mb30">
        <div class="header mb10">
            メンバー反応状況
        </div>
        <div>
            <div class="switch-state-btn-list disp-flex just-center align-center tc mb10">
                <?php foreach (Configure::read('response_states') as $state_idx => $state) : ?>
                    <div class="switch-state-btn w100 <?= ($state_idx===0)?'enable':''?>" id="<?= $state_idx ?>"><?= $state["text"] ?></div>
                <?php endforeach; ?>
            </div>

            <?php foreach ($categorized_event_response_list as $response_state => $event_response_list) : ?>
                <div class="state-list state-<?= $response_state ?> <?= ($response_state===0)?'enable':''?>" style="height: 300px; overflow: scroll;">
                    <?php foreach ($event_response_list as $event_response) : ?>
                        <div class="pb10 disp-flex just-center align-center">
                            <div class="name w50">
                                <?= $event_response["display_name"] ?>
                            </div>
                            <div class="lnk w50">
                                <a href="<?= $this->Url->build(['prefix'=>'Admin', 'controller' => 'administrators','action' => 'userDetail', $event_response["id"]]); ?>">
                                    <button class="w100 p10">
                                        詳細
                                    </button>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<script>
    $(function(){
        $(".switch-state-btn").on("click", function(){
            let clicked_id = $(this).attr("id");
            ['0', '1', '2', 'null'].forEach((state) => {
                if(state == clicked_id){
                    $(".state-list.state-"+state).addClass('enable');  
                    $("#"+state).addClass('enable');
                    console.log("#"+state);
                } else {
                    $(".state-list.state-"+state).removeClass('enable');
                    $("#"+state).removeClass('enable');
                }

            });
            

            
        });
    });
</script>