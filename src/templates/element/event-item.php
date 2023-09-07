<?= $this->Html->script('event-item', array('inline' => false)); ?>
<?php
    use Cake\Core\Configure;
    $user_response_state = (!is_null($event->user_response_state)) ? Configure::read('response_states')[$event->user_response_state] : null;
    $event_state = Configure::read('event_states')[$event->event_state];
    $day_of_weeks = Configure::read('day_of_weeks');

    $response_count = [
        0 => count($event->event_responses[0]),
        1 => count($event->event_responses[1]),
        2 => count($event->event_responses[2]),
    ];
?>

<div class="event-item event-outer mb20 <?= ($event->deleted_at)?'delete-item':'' ?>">
    <?php if(isset($displayCreatedBtn)): ?>
        <div class="disp-flex align-center ">
            <a class="buttons pure-u-2-3" href="<?= $this->Url->build(['controller' => 'events','action' => 'edit', $event->id]); ?>">
                <button class="pure-button w100" type="button" style="background-color:#dfb31d7d;">
                    編集
                </button>
            </a>

            <?php if($event->deleted_at): ?>
                <a class="buttons pure-u-1-3" href="<?= $this->Url->build(['controller' => 'events','action' => 'restore', $event->id]); ?>">
                    <button class="pure-button w100" type="button">
                        削除済
                    </button>
                </a>
            <?php else: ?>
                <a class="buttons pure-u-1-3 delete-lnk" href="<?= $this->Url->build(['controller' => 'events','action' => 'delete', $event->id]); ?>">
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
            <?php if(!is_null($user_response_state)): ?>
                <div class="tag disp-iblock" , style="background-color: <?= $user_response_state['tag_color'] ?>;">
                    <?= $user_response_state['text'] ?>
                    
                </div>
            <?php endif; ?>
            
            <?php if($event->participants_limit <= 0): ?>
                <div class="tag disp-iblock" , style="background-color: #5f9ea0;">
                    人数無制限
                </div>
            <?php endif; ?>
        </div>

        <div class="schedule mb5 bold">
            <?= $event->start_time->i18nFormat('yyyy-MM-dd'); ?>
            <span>(<?= $day_of_weeks[$event->start_time->dayOfWeek] ?>)</span>
        </div>

        <div class="location mb10 bold over-ellipsis">
            <?= h($event->location->display_name) ?>
        </div>

        <div class="inner-content disp-flex mb10">
            <div class="content-left w50">
                <div class="area over-ellipsis mb5">
                    <?= is_null($event->area) ? '' : h($event->area) . 'コート' ?>
                </div>

                <div class="time mb5">
                    <?= $event->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event->end_time->i18nFormat('HH:mm');  ?>
                </div>

                <div class="limit mb5">
                    人数制限:<?= $event->participants_limit <= 0 ? 'なし' : h($event->participants_limit) . '人' ?>
                </div>

                <div class="count mb5">
                    o:<?= $response_count[1] ?>
                    ?:<?= $response_count[0] ?>
                    x:<?= $response_count[2] ?>
                </div>
                <div class="total mb5">
                    (合計:<?= count($event->event_responses[0]) + count($event->event_responses[1]) + count($event->event_responses[2]) ?>人)
                </div>
            </div>


            <div class="content-right w50 disp-flex just-center align-center">
                <a class="buttons" href="<?= $this->Url->build(['controller' => 'events','action' => 'detail', $event->id]); ?>">
                    <button class="pure-button" type="button">
                    参加表明/詳細
                    </button>
                </a>
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
                        <div class="state-title mb10">
                            <?= Configure::read('response_states')[$state_idx]["text"] ?>
                            (<?= $response_count[$state_idx] ?>)
                        </div>
                        <div class="state-content">
                            <?php foreach($event->event_responses[$state_idx] as $idx=>$event_response): ?>
                                <div class="over-ellipsis"><?= h($event_response['name']); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <?php $state_idx = 0; ?>
                    <div class="state state-<?= $state_idx ?> pure-u-1-2 text-center p10">
                        <div class="state-title mb10">
                            <?= Configure::read('response_states')[$state_idx]["text"] ?>
                            (<?= $response_count[$state_idx] ?>)
                        </div>
                        <div class="state-content">
                            <?php foreach($event->event_responses[$state_idx] as $event_response): ?>
                                <div class="over-ellipsis"><?= h($event_response['name']); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
            </div>
        <?php endif; ?>
    </div>
</div>