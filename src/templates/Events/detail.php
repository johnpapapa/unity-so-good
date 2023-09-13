<?php $this->assign('title', 'event detail'); ?>
<?php $this->assign('content-title', 'イベント詳細'); ?>
<?= $this->Html->script('event-response', array('inline' => false));  ?>

<?php 
    use Cake\Core\Configure;
    $user_response_state = (!is_null($event->user_response_state)) ? Configure::read('response_states')[$event->user_response_state] : null;
    $event_state = Configure::read('event_states')[$event->event_state];
    $day_of_weeks = Configure::read('day_of_weeks');

?>
<script>
    let current_user = <?= json_encode($current_user) ?>;
    let event_data = <?= json_encode($event) ?>;
    let response_ajax_send_url = "<?= $this->Url->build(['controller' => 'Events', 'action' => 'ajaxChangeResponseState']) ?>";
    let response_ajax_send_token = "<?= $this->request->getAttribute('csrfToken') ?>";
</script>

<style>
    .detail .detail-content .label {
        background-color: lightgray;
        font-size: 1.05rem;
        padding: 5px;
    }
    .detail .detail-content .content {
        font-size: 1.2rem;
    }

    .detail .detail-content .undecided, .detail .detail-content .state-0 {background-color: #d3d3d37F;}
    .detail .detail-content .present, .detail .detail-content .state-1 {background-color: #90ee907F;}
    .detail .detail-content .absent {background-color: #f080807F;}

    .detail .state-title {
        color: #00000055;
    }

    .detail .state {border: 1px solid #ccc;}
    /* .detail .state-content .name {font-size: 1.2rem;}
    .detail .state-content .time {font-size: .7rem;} */
</style>


<div class="detail">
    <div class="event-jump disp-flex just-space mb20">
        <? if($event_prev_id): ?>
            <div class="disp-iblock fl">
                <a href="<?= $this->Url->build(['controller' => 'events','action' => 'detail', $event_prev_id]); ?>">前のイベントへ移動</a>
            </div>
        <? endif; ?>
        
        <? if($event_next_id): ?>
            <div class="disp-iblock fr">
                <a href="<?= $this->Url->build(['controller' => 'events','action' => 'detail', $event_next_id]); ?>">次のイベントへ移動</a>
            </div>
        <? endif; ?>
    </div>

    <div class="detail-content">
        <div class="row mb20">
            <div class="label mb5">場所</div>
            <div class="content">
                <?= h($event->location->display_name) ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">コート</div>
            <div class="content">
                <?= is_null($event->area) ? '' : h($event->area) . 'コート' ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">日付</div>
            <div class="content">
                <?= $event->start_time->i18nFormat('yyyy-MM-dd'); ?>
                <span>(<?= $day_of_weeks[$event->start_time->dayOfWeek] ?>)</span>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">時間</div>
            <div class="content">
                <?= $event->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event->end_time->i18nFormat('HH:mm');  ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">人数制限</div>
            <div class="content">
                <?= $event->participants_limit <= 0 ? 'なし' : $event->participants_limit . '人' ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">コート代</div>
            <div class="content">
                <?= ($event->location->usage_price) ? $event->location->usage_price : '---' ?>円
                (ナイター:<?= ($event->location->night_price) ? $event->location->night_price : '---' ?>円)
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">イベントの参加人数</div>
            <div class="content">
                ?:<?= count($event->event_responses[0]) ?>
                o:<?= count($event->event_responses[1]) ?>
                x:<?= count($event->event_responses[2]) ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">あなたの参加情報</div>
            <div class="content">
                <?php if ($event->user_response_state) : ?>
                    <?= $user_response_state['text']; ?>
                <?php else : ?>
                    未表明
                <?php endif; ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">参加表明</div>
            <div class="content disp-flex just-center g10">
                <button class="pure-button response-btn pure-u-1 undecided" value="0" <?= ($event->user_response_state === 0)? 'disabled':'' ?>>参加未定</button>
                <button class="pure-button response-btn pure-u-1 present" value="1" <?= ($event->user_response_state === 1)? 'disabled':'' ?>>参加</button>
                <button class="pure-button response-btn pure-u-1 absent" value="2" <?= ($event->user_response_state === 2)? 'disabled':'' ?>>不参加</button>
            </div>
        </div>
        <div class="row">
            <div class="label mb5">参加者一覧</div>
            <div class="content states">
                <?php $state_idx = 1; ?>
                <div class="states-active-perhaps disp-flex">
                    <div class="state state-<?= $state_idx ?> p10 pure-u-1-2">
                        <div class="state-title text-center mb10">
                            <?= Configure::read('response_states')[$state_idx]["text"] ?>
                        </div>
                        <?php foreach($event->event_responses[$state_idx] as $event_response): ?>
                            <div class="state-content over-ellipsis disp-iblock pure-u-1 mb5">
                                <div class="name disp-m-block disp-iblock over-ellipsis fs-large fs-m-large"><?= h($event_response["name"]); ?></div>
                                <div class="time disp-iblock fr fs-small fs-m-small"><?= $event_response["time"]->i18nFormat('MM/dd HH:mm:ss') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                        
                    <?php $state_idx = 0; ?>
                    <div class="state state-<?= $state_idx ?> p10 pure-u-1-2">
                        <div class="state-title text-center mb10">
                            <?= Configure::read('response_states')[$state_idx]["text"] ?>
                        </div>
                        <?php foreach($event->event_responses[$state_idx] as $event_response): ?>
                            <div class="state-content over-ellipsis disp-iblock pure-u-1 mb5">
                                <div class="name disp-m-block disp-iblock over-ellipsis fs-large fs-m-large"><?= h($event_response["name"]); ?></div>
                                <div class="time disp-iblock fr fs-small fs-m-small"><?= $event_response["time"]->i18nFormat('MM/dd HH:mm:ss') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                            
                <?php $state_idx = 2; ?>
                <div class="state-<?= $state_idx ?> p10">
                    <div class="state-title mb10">
                        <?= Configure::read('response_states')[$state_idx]["text"] ?>
                    </div>
                    <?php foreach($event->event_responses[$state_idx] as $event_response): ?>
                        <div class="state-content over-ellipsis">
                            <div class="fs-medium  fs-m-midium"><?= h($event_response["name"]); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>