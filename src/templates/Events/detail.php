<?php

/**
 * @var \App\View\AppView $this
 * @var mixed $current_user
 * @var mixed $event_next_id
 * @var mixed $event_prev_id
 * @var object $event_data
 */
?>
<?php $this->assign('title', 'event detail'); ?>
<?php $this->assign('content-title', 'イベント詳細'); ?>
<?= $this->Html->script('event-response', array('inline' => false));  ?>
<?= $this->Html->script('event-comment', array('inline' => false));  ?>
<p class="note-p mb20">
    イぺントの詳細です。
    <br>イベントへの参加表明やコメント記入が可能です。
</p>
<?php

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

$user_response_state = (!is_null($event_data->user_response_state)) ? Configure::read('response_states')[$event_data->user_response_state] : null;
$event_state = Configure::read('event_states')[$event_data->event_state];
$day_of_weeks = Configure::read('day_of_weeks');

$area_count = count(explode(",", h($event_data->area)));

$usage_price_total = 0;
$night_price_total = 0;
$usage_price_per_responses = 0;
$night_price_per_responses = 0;
$count_responses = count($event_data->event_responses[1]);

if ($count_responses > 0) {
    if ($event_data->location->usage_price > 0) {
        $usage_price_total = ($event_data->location->usage_price > 0 ? $event_data->location->usage_price : 0) * $area_count;
        $usage_price_per_responses = ceil($usage_price_total / count($event_data->event_responses[1]));
    }
    if ($event_data->location->night_price > 0) {
        $night_price_total = ($event_data->location->night_price > 0 ? $event_data->location->night_price : 0) * $area_count;
        $night_price_per_responses = ceil($night_price_total / count($event_data->event_responses[1]));
    }
}
?>
<script>
    let current_user = <?= json_encode($current_user) ?>;
    let event_data = <?= json_encode($event_data) ?>;
    let response_ajax_send_url = "<?= $this->Url->build(['controller' => 'Events', 'action' => 'ajaxChangeResponseState']) ?>";
    let comment_submit_ajax_send_url = "<?= $this->Url->build(['controller' => 'Events', 'action' => 'ajaxSubmitComment']) ?>";
    let comment_delete_ajax_send_url = "<?= $this->Url->build(['controller' => 'Events', 'action' => 'ajaxDeleteComment']) ?>";
    let ajax_send_token = "<?= $this->request->getAttribute('csrfToken') ?>";
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

    .detail .detail-content .undecided,
    .detail .detail-content .state-0 {
        background-color: #d3d3d37F;
    }

    .detail .detail-content .present,
    .detail .detail-content .state-1 {
        background-color: #90ee907F;
    }

    .detail .detail-content .absent {
        background-color: #f080807F;
    }

    .detail .state-title {
        color: #00000055;
    }

    .detail .state {
        border: 1px solid #ccc;
    }

    .detail .comments .comments-title {
        font-size: 1.2rem;
    }

    .detail .comments .comment {
        border: black 1px solid;
        border-radius: 5px;
    }

    .detail .comment-header .time {
        font-size: .7rem;
    }

    .detail .comment-header .delete-comment-btn {
        font-size: .7rem;
    }

    .detail .comment-header .name {
        font-size: 1rem;
    }

    .detail .comment-body {
        font-size: .9rem;
    }

    .star {
        position: absolute;
        display: block;
        width: 10px;
        /* キラキラの横幅を指定 */
        height: 10px;
        /* キラキラの縦幅を指定 */
        background-image: url("<?= $this->Url->image('star-on.png'); ?>");
        /* キラキラの画像のパスを記入 */
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center center;
        animation: glitter 1s;
        pointer-events: none;
    }

    dialog {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    @keyframes glitter {
        0% {
            transform: scale(0);
            opacity: 0;
        }

        50% {
            transform: scale(1);
            opacity: 1;
        }

        100% {
            transform: scale(0);
            opacity: 0;
        }
    }
</style>


<div class="detail">
    <?php if ($is_admin) : ?>
        <div class="event-edit mb20 disp-flex just-space">
            <a class="buttons pure-u-1-5" href="<?= $this->Url->build(['controller' => 'events', 'action' => 'edit', $event_data->id]); ?>">
                <button class="pure-button w100" type="button" style="background-color:#dfb31d7d;">
                    編集
                </button>
            </a>
        </div>
    <?php endif; ?>
    <div class="event-jump disp-flex just-space mb20">
        <?php if ($event_prev_id) : ?>
            <div class="disp-iblock fl">
                <a href="<?= $this->Url->build(['controller' => 'events', 'action' => 'detail', $event_prev_id]); ?>">前のイベントへ移動</a>
            </div>
        <?php endif; ?>

        <?php if ($event_next_id) : ?>
            <div class="disp-iblock fr">
                <a href="<?= $this->Url->build(['controller' => 'events', 'action' => 'detail', $event_next_id]); ?>">次のイベントへ移動</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="detail-content">
        <div class="row mb20">
            <div class="label mb5">場所</div>
            <div class="content">
                <?= h($event_data->location->display_name) ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">コート</div>
            <div class="content">
                <?= is_null($event_data->area) ? '' : h($event_data->area) . 'コート' ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">日付</div>
            <div class="content">
                <?= $event_data->start_time->i18nFormat('yyyy-MM-dd'); ?>
                <span>(<?= $day_of_weeks[$event_data->start_time->dayOfWeek] ?>)</span>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">時間</div>
            <div class="content">
                <?= $event_data->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event_data->end_time->i18nFormat('HH:mm');  ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">人数制限</div>
            <div class="content">
                <?= $event_data->participants_limit <= 0 ? 'なし' : $event_data->participants_limit . '人' ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">コート代</div>
            <div class="content">


                <div class="mt10" style="border-left: 10px orange solid;padding-left: 10px;">
                    昼間料金 : <?= $usage_price_total ?>円
                    <br>(<?= (($event_data->location->usage_price > 0) ? $event_data->location->usage_price : 0) . '円' ?> × <?= $area_count ?>コート)
                    <span style="color:light-gray;">
                        <?php if ($event_data->location->usage_price > 0 && count($event_data->event_responses[1])) : ?>
                            <br>一人あたり<?= $usage_price_per_responses ?>円
                        <?php else : ?>
                            <br>一人あたり0円
                        <?php endif; ?>
                        <br>(参加人数<?= count($event_data->event_responses[1]) ?>人の場合)
                    </span>
                </div>
                <div class="mt10" style="border-left: 10px blue solid;padding-left: 10px;">
                    夜間料金 : <?= $night_price_total ?>円
                    <br>(<?= (($event_data->location->night_price > 0) ? $event_data->location->night_price : 0) . '円' ?> × <?= $area_count ?>コート)
                    <span style="color:light-gray;">
                        <?php if ($event_data->location->night_price > 0 && count($event_data->event_responses[1])) : ?>
                            <br>一人あたり<?= $night_price_per_responses ?>円
                        <?php else : ?>
                            <br>一人あたり0円
                        <?php endif; ?>
                        <br>(参加人数<?= count($event_data->event_responses[1]) ?>人の場合)
                    </span>
                </div>


                <p class="note-p mt10">
                    コート料金が設定されていない場合0円が表示されます。
                    <br>ナイター料金が設定されていない場合、夜の料金は昼と同じ合計金額が表示されます。
                </p>

            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">コメント・注意事項</div>
            <div class="content">
                <?= ($event_data->comment == "") ? '特になし' : h($event_data->comment) ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">イベントの参加人数</div>
            <div class="content">
                参加未定 : <span id="state-count-0"><?= count($event_data->event_responses[0]) ?></span>
                参加 : <span id="state-count-1"><?= count($event_data->event_responses[1]) ?></span>
                不参加 : <span id="state-count-2"><?= count($event_data->event_responses[2]) ?></span>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">あなたの参加情報</div>
            <div class="content">
                <?php if ($event_data->user_response_state) : ?>
                    <?= $user_response_state['text']; ?>
                <?php else : ?>
                    未表明
                <?php endif; ?>
            </div>
        </div>
        <div class="row mb20">
            <div class="label mb5">参加表明</div>
            <div class="content disp-flex just-center" style="gap:10px;">
                <?php $is_closed = FrozenTime::now() > $event_data->end_time; ?>
                <button class="pure-button response-btn pure-u-1 undecided" value="0" <?= ($event_data->user_response_state === 0 | $is_closed) ? 'disabled' : '' ?>>参加未定</button>
                <button class="pure-button response-btn pure-u-1 present" value="1" <?= ($event_data->user_response_state === 1 | $is_closed) ? 'disabled' : '' ?>>参加</button>
                <button class="pure-button response-btn pure-u-1 absent " value="2" <?= ($event_data->user_response_state === 2 | $is_closed) ? 'disabled' : '' ?>>不参加</button>
            </div>
        </div>
    </div>

    <div class="row mb20">
        <div class="label mb5">コメント</div>
        <div class="content">
            <p class="note-p mb5">遅刻などの連絡事項に使用してください</p>
            <textarea name="comment" class="w100" id="comment_body" maxlength="255" placeholder="コメント・連絡事項" rows="3"></textarea>
            <button class="pure-button submit-comment-btn w100">コメントの投稿</button>

        </div>
    </div>
    <div class="row">
        <div class="label mb5">参加者一覧</div>
        <div class="content states">
            <?php $state_idx = 1; ?>
            <div class="states-active-perhaps disp-flex">
                <div class="state state-<?= $state_idx ?> p10 pure-u-1-2">
                    <div class="state-title text-center p5">
                        <?= Configure::read('response_states')[$state_idx]["text"] ?>
                    </div>
                    <div id="state-contents-<?= $state_idx ?>">
                        <?php foreach ($event_data->event_responses[$state_idx] as $event_response) : ?>
                            <div <?= ($event_response['id'] == $current_user['id']) ? "id='user-state'" : ""  ?> class="state-content over-ellipsis disp-iblock pure-u-1 mt10">
                                <div class="name disp-m-block disp-iblock over-ellipsis fs-large fs-m-large"><?= h($event_response["display_name"]); ?></div>
                                <?php if ($event_data->participants_limit > 0) : ?>
                                    <div class="time disp-iblock fr fs-small fs-m-small"><?= $event_response["time"]->i18nFormat('MM-dd HH:mm:ss') ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php $state_idx = 0; ?>
                <div class="state state-<?= $state_idx ?> p10 pure-u-1-2">
                    <div class="state-title text-center p5">
                        <?= Configure::read('response_states')[$state_idx]["text"] ?>
                    </div>
                    <div id="state-contents-<?= $state_idx ?>">
                        <?php foreach ($event_data->event_responses[$state_idx] as $event_response) : ?>
                            <div <?= ($event_response['id'] == $current_user['id']) ? "id='user-state'" : ""  ?> class="state-content over-ellipsis disp-iblock pure-u-1 mt10">
                                <div class="name disp-m-block disp-iblock over-ellipsis fs-large fs-m-large"><?= h($event_response["display_name"]); ?></div>
                                <?php if ($event_data->participants_limit > 0) : ?>
                                    <div class="time disp-iblock fr fs-small fs-m-small"><?= $event_response["time"]->i18nFormat('MM-dd HH:mm:ss') ?></div>
                                <?php endif ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php $state_idx = 2; ?>
            <div class="state-<?= $state_idx ?> p10">
                <div class="state-title mb10">
                    <?= Configure::read('response_states')[$state_idx]["text"] ?>
                </div>
                <div style="height:200px; overflow:scroll;" id="state-contents-<?= $state_idx ?>">
                    <?php foreach ($event_data->event_responses[$state_idx] as $event_response) : ?>
                        <div <?= ($event_response['id'] == $current_user['id']) ? "id='user-state'" : "" ?> class="state-content over-ellipsis">
                            <div class="fs-medium  fs-m-midium">
                                <?= h($event_response["display_name"]); ?>
                                <?php if ($is_admin) : ?>
                                    <div class="time disp-iblock fr fs-small fs-m-small"><?= $event_response["time"]->i18nFormat('MM-dd HH:mm') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (count($event_data->comments) > 0) : ?>
        <div class="row mb20">
            <div class="label mb5">コメント一覧</div>
            <div class="content comments">
                <?php foreach ($event_data->comments as $comment) : ?>
                    <div class="comment w100 mb5 p10 disp-flex align-center dir-column">
                        <div class="comment-header mb5 w100 disp-flex just-center align-center dir-row">
                            <div class="name w100 over-ellipsis"><?= $comment->user->display_name ?></div>
                            <div class="w100 disp-flex align-center" style="justify-content: flex-end;">
                                <?php if ($comment->user_id == $current_user->id) : ?>
                                    <div class="delete-comment-btn w100 tr">コメントを削除<input type="hidden" value="<?= $comment->id ?>"></div>
                                <?php endif; ?>
                                <div class="time w100 tr"><?= $comment->updated_at->i18nFormat('yyyy-MM-dd HH:mm') ?></div>
                            </div>
                        </div>
                        <div class="comment-body w100">
                            <div class="body w100"><?= h($comment->body) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</div>