<?php $this->assign('title', 'Events'); ?>
<?php $this->assign('content-title', 'イベント詳細'); ?>
<?php 
    use Cake\Core\Configure; 
    $response_states = Configure::read('response_states');
    
    echo $this->Html->script('event-response', array('inline' => false)); 
    // echo $this->Html->css(['event-item']) 
?>
<script>
    let current_user = <?= json_encode($current_user) ?>;
    let event_data = <?= json_encode($event) ?>;
    let response_ajax_send_url = "<?= $this->Url->build(['controller' => 'Events', 'action' => 'ajaxChangeResponseState']) ?>";
    let response_ajax_send_token = "<?= $this->request->getAttribute('csrfToken') ?>";
</script>

<? if($event_prev_id): ?>
<a href="<?= $this->Url->build(['controller' => 'events','action' => 'detail', $event_prev_id]); ?>">前のイベントへ移動</a>
<? endif; ?>

<? if($event_next_id): ?>
<a href="<?= $this->Url->build(['controller' => 'events','action' => 'detail', $event_next_id]); ?>">次のイベントへ移動</a>
<? endif; ?>
<div><a onclick="history.back()">直前のページに戻る</a></div>


<div class="container">
    <div class="row">
        <div class="column">場所</div>
        <div class="column"><?= $event->location->display_name ?>あああああああああ</div>
    </div>
    <div class="row">
        <div class="column">コート</div>
        <div class="column"><?= is_null($event->area) ? '' : $event->area . ',1,2,3,4,5,6,7,8,9コート' ?></div>
    </div>
    <div class="row">
        <div class="column">日付</div>
        <div class="column"><?= $event->date_y ?>年 <?= $event->date_m ?>月 <?= $event->date_m ?>日(<?= $event->day_of_week ?>)</div>
    </div>
    <div class="row">
        <div class="column">時間</div>
        <div class="column"><?= $event->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event->end_time->i18nFormat('HH:mm');  ?></div>
    </div>
    <div class="row">
        <div class="column">人数制限</div>
        <div class="column"><?= $event->participants_limit <= 0 ? 'なし' : $event->participants_limit . '人' ?></div>
    </div>
    <div class="row">
        <div class="column">参加情報</div>
        <div class="column">
            ?:<?= $event->participants_0_count ?>
            o:<?= $event->participants_1_count ?>
            x:<?= $event->participants_2_count ?>
        </div>
    </div>
    <div class="row">
        <div class="column">ユーザー参加情報</div>
        <div class="column">
            <?php if ($event->user_response_state) : ?>
                <?php //$user_response_state = $response_states[$event->user_response_state]['text']; ?>
                <?= $response_states[$event->user_response_state]['text']; ?>
            <?php else : ?>
                未表明
            <?php endif; ?>
        </div>
    </div>
    <div class="row">
        <div class="column">参加表明</div>
        <div class="column">
            <button class="undecided" value="0" <?= ($event->user_response_state === 0)? 'disabled':'' ?>>参加未定</button>
            <button class="present" value="1" <?= ($event->user_response_state === 1)? 'disabled':'' ?>>参加</button>
            <button class="absent" value="2" <?= ($event->user_response_state === 2)? 'disabled':'' ?>>不参加</button>
        </div>
    </div>
    <div class="row">
        <div class="column">参加者一覧</div>
        <div class="column">
            <?php foreach ($event['event_responses'] as $event_responses) : ?>
                <?= $response_states[$event_responses->response_state]['text']; ?> | <?= $event_responses->user->display_name ?><br>
            <?php endforeach; ?>
        </div>
    </div>
</div>