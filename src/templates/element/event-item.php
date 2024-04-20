<?php

/**
 * @var \App\View\AppView $this
 * @var mixed $current_user
 * @var mixed $displayCreatedBtn
 * @var mixed $events
 */
//一覧内部で繰り返し使用する変数の宣言
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
//ユーザーの参加情報をconstから読出
$response_states = Configure::read('response_states');
//イベントの開催状況をconstから読出
$event_states = Configure::read('event_states');
//dayOfWeekの返り値に対応する曜日をconstから読出
$day_of_weeks = Configure::read('day_of_weeks');
?>
<?= $this->Html->css(['event-item']) ?>
<?= $this->Html->script('event-response-method', array('inline' => false));  ?>

<script>
    let currentUser = <?= json_encode($current_user) ?>;
    let responseAjaxSendUrl = "<?= $this->Url->build(['controller' => 'Events', 'action' => 'ajaxChangeResponseState']) ?>";
    let ajaxSendToken = "<?= $this->request->getAttribute('csrfToken') ?>";
</script>
<div>
    <?php foreach ($events as $event) : ?>
        <?php
        $user_response_state = (!is_null($event->user_response_state)) ? $response_states[$event->user_response_state] : null;
        $event_state = $event_states[$event->event_state];
        $day_of_week = $day_of_weeks[$event->start_time->dayOfWeek];
        $response_count = [0 => count($event->event_responses[0]), 1 => count($event->event_responses[1]), 2 => count($event->event_responses[2])];
        ?>
        <div id='<?= $event->id ?>' class="event-item event-outer mb20 <?= ($event->deleted_at) ? 'delete-item' : '' ?>">
            <?php if (isset($displayCreatedBtn)) : ?>
                <div class="disp-flex align-center">
                    <a class="buttons pure-u-2-3" href="<?= $this->Url->build(['controller' => 'events', 'action' => 'edit', $event->id]); ?>">
                        <button class="pure-button w100" type="button" style="background-color:#dfb31d7d;">
                            編集
                        </button>
                    </a>
                    <?php if ($event->deleted_at) : ?>
                        <a class="buttons pure-u-1-3" href="<?= $this->Url->build(['controller' => 'events', 'action' => 'restore', $event->id]); ?>">
                            <button class="pure-button w100" type="button">
                                復元
                            </button>
                        </a>
                    <?php else : ?>
                        <a class="buttons pure-u-1-3 delete-lnk" href="<?= $this->Url->build(['controller' => 'events', 'action' => 'delete', $event->id]); ?>">
                            <button class="pure-button w100" type="button">
                                削除
                            </button>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="event-inner">
                <div class="tags mb5">
                    <div class="tag disp-iblock" style="background-color: <?= $event_state['tag_color'] ?>;">
                        <?= $event_state['text'] ?>
                    </div>
                    <?php if (!is_null($user_response_state)) : ?>
                        <div class="tag disp-iblock" , style="background-color: <?= $user_response_state['tag_color'] ?>;">
                            <?= $user_response_state['text'] ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($event->deleted_at) : ?>
                        <div class="tag disp-iblock" style="background-color: indianred;">
                            削除済
                        </div>
                    <?php endif; ?>
                    <?php if ($event->participants_limit <= 0) : ?>
                        <div class="tag disp-iblock" , style="background-color: #5f9ea0;">
                            人数無制限
                        </div>
                    <?php endif; ?>
                </div>

                <div class="disp-flex schedule mb10">
                    <div class="disp-flex schedule-inner bold">
                        <span class="year"><?= sprintf('%4d', $event->start_time->year) ?></span>
                        <span class="year-label">年</span>
                        <span class="month"><?= sprintf('%2d', $event->start_time->month) ?></span>
                        <span class="month-label">月</span>
                        <span class="day"><?= sprintf('%2d', $event->start_time->day) ?></span>
                        <span class="day-label">日</span>
                    </div>
                    <div class="week <?php if ($day_of_week == "土") {
                                            echo "week-color-sat";
                                        } elseif ($day_of_week == "日") {
                                            echo "week-color-sun";
                                        } ?>">
                        <?= $day_of_week ?>
                    </div>

                    <div class="time">
                        <?= $event->start_time->i18nFormat('HH:mm'); ?> - <?= $event->end_time->i18nFormat('HH:mm');  ?>
                    </div>
                </div>

                <div class="mb20">
                    <div class="location bold over-ellipsis">
                        <?= h($event->location->display_name) ?>
                    </div>
                    <div class="area over-ellipsis">
                        <?= is_null($event->area) ? '' : h($event->area) . "コート" ?>
                    </div>
                </div>

                <div class="inner-content disp-flex mb20">
                    <div class="content-left w50">


                        <div class="limit text-center mb5">
                            人数制限:<?= $event->participants_limit <= 0 ? 'なし' : h($event->participants_limit) . "人" ?>
                        </div>

                        <div class="count mb5">
                            <table border="1" style="border-collapse: collapse">
                                <tr>
                                    <th style="width:30px;background-color: #90ee907F;">o</th>
                                    <th style="width:30px;background-color: #d3d3d37F;">?</th>
                                    <th style="width:30px;background-color: #f080807F;">x</th>
                                </tr>
                                <tr>
                                    <td class="tc"><?= $response_count[1] ?></td>
                                    <td class="tc"><?= $response_count[0] ?></td>
                                    <td class="tc"><?= $response_count[2] ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="total text-center mb10">
                            (合計:<?= array_sum($response_count) ?>人)
                        </div>

                        <?php if ($current_user) : ?>
                            <div class="text-center mb10">
                                <a class="buttons" href="<?= $this->Url->build(['controller' => 'events', 'action' => 'detail', $event->id]); ?>">
                                    <button class="pure-button button-detail" type="button">
                                        詳細
                                    </button>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="content-right w50 disp-flex dir-column dir-m-column just-space align-center">
                        <?php if ($current_user) : ?>
                            <?php $is_closed = FrozenTime::now(null) > $event->end_time; ?>
                            <input type="hidden" name="event_id" value="<?= $event->id ?>">
                            <button class="pure-button response-btn pure-u-4-5 undecided" style="padding:.25em .5em; background-color: #d3d3d37F;" value="0" <?= ($event->user_response_state === 0 | $is_closed) ? 'disabled' : '' ?>>参加未定</button>
                            <button class="pure-button response-btn pure-u-4-5 present" style="padding:.25em .5em; background-color: #90ee907F;" value="1" <?= ($event->user_response_state === 1 | $is_closed) ? 'disabled' : '' ?>>参加</button>
                            <button class="pure-button response-btn pure-u-4-5 absent " style="padding:.25em .5em; background-color: #f080807F;" value="2" <?= ($event->user_response_state === 2 | $is_closed) ? 'disabled' : '' ?>>不参加</button>
                        <?php else : ?>
                            <div class="text-center mb10 w100">
                                <a class="buttons" href="<?= $this->Url->build(['controller' => 'events', 'action' => 'detail', $event->id]); ?>">
                                    <button class="pure-button button-detail" type="button">
                                        詳細
                                    </button>
                                </a>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <?php if ($current_user) : ?>
                    <div class="description_toggle disp-flex just-center align-center mb10">
                        <span class="material-symbols-outlined">expand_all</span>
                        参加者確認
                    </div>

                    <div class="description mb10" style="display: none;">
                        <div class="states disp-flex">
                            <?php $state_idx = 1; ?>
                            <div class="state state-<?= $state_idx ?> pure-u-1-2 text-center p10">
                                <div class="state-title">
                                    <?= Configure::read('response_states')[$state_idx]["text"] ?>
                                    (<?= $response_count[$state_idx] ?>)
                                </div>
                                <?php if ($response_count[$state_idx] > 0) : ?>
                                    <div class="state-content mt10">
                                        <?php foreach ($event->event_responses[$state_idx] as $event_response) : ?>
                                            <div class="over-ellipsis"><?= h($event_response['display_name']); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php $state_idx = 0; ?>
                            <div class="state state-<?= $state_idx ?> pure-u-1-2 text-center p10">
                                <div class="state-title">
                                    <?= Configure::read('response_states')[$state_idx]["text"] ?>
                                    (<?= $response_count[$state_idx] ?>)
                                </div>
                                <?php if ($response_count[$state_idx] > 0) : ?>
                                    <div class="state-content mt10">
                                        <?php foreach ($event->event_responses[$state_idx] as $event_response) : ?>
                                            <div class="over-ellipsis"><?= h($event_response['display_name']); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (count($event->comments)) : ?>
                            <div class="comments_toggle disp-flex just-center align-center" style="margin: 10px;">
                                <span class="material-symbols-outlined">expand_all</span>
                                コメント確認
                            </div>

                            <div class="comments">
                                <?php foreach ($event->comments as $comment) : ?>
                                    <div class="comment w100 mb5 p10 disp-flex align-center dir-column">
                                        <div class="comment-header mb5 w100 disp-flex just-center align-center dir-row">
                                            <div class="name w100"><?= $comment->user->display_name ?></div>
                                            <div class="time w100 tr"><?= $comment->updated_at->i18nFormat('yyyy-MM-dd HH:mm') ?></div>
                                        </div>
                                        <div class="comment-body w100">
                                            <div class="body w100"><?= h($comment->body) ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    $(function() {
        function changeDisplayElement(element) {
            if (element.style.display === "none") {
                element.style.display = "block";
            } else {
                element.style.display = "none";
            }
        }

        $description_toggle_obj = $('.description_toggle');
        $comments_toggle_obj = $('.comments_toggle');
        [$description_toggle_obj, $comments_toggle_obj].forEach(function(element) {
            element.on('click', function() {
                changeDisplayElement($(this).next()[0]);
            });
        })

        $('.delete-lnk').on('click', function(event) {
            var res = confirm('このイベントを削除しますか?');
            if (!res) {
                event.preventDefault();
            }
        });
    });
</script>