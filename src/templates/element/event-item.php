<!-- this element argument is [event, displayResponseBtn] -->

<?= $this->Html->css(['event-item']) ?>
<?php use Cake\Core\Configure; ?>
<?= $this->Html->script('event-item', array('inline' => false)); ?>

<div class="event-item">
    <div class="event-item-inner">
        <div class="event-item-innter-top disp-flex align-center">
            <div class="schedule disp-iblock">
                <div class="date disp-inline"><?= $event->date ?></div>
                <div class="date disp-inline">(<?= $event->day_of_week ?>)</div>
            </div>

            <?php $event_state = Configure::read('event_states')[$event->event_state]; ?>
            <div class="state-tag disp-iblock" style="background-color: <?= $event_state['tag_color'] ?>;">
                <?= $event_state['text'] ?>
            </div>

            <?php if($event->user_response_state): ?>
                <?php $user_response_state = Configure::read('response_states')[$event->user_response_state]; ?>
                <div class="state-tag disp-iblock" , style="background-color: <?= $user_response_state['tag_color'] ?>;">
                    <?= $user_response_state['text'] ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="location">
            <?= $event->location->display_name ?>あああああああああ
        </div>
        <div class="event-item-content disp-flex align-center">
            <div class="event-item-content-left disp-flex">
                <div class="area disp-iblock">
                    <?= is_null($event->area) ? '' : $event->area . ',1,2,3,4,5,6,7,8,9コート' ?>
                </div>
                <div class="time disp-iblock">
                    <?= $event->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event->end_time->i18nFormat('HH:mm');  ?>
                </div>
                <div class="limit disp-iblock">人数制限:<?= $event->participants_limit <= 0 ? 'なし' : $event->participants_limit . '人' ?></div>
                <div class="count disp-iblock">
                    ?:<?= (isset($event->participants_count['0'])) ? $event->participants_count['0']:0 ?>
                    o:<?= (isset($event->participants_count['1'])) ? $event->participants_count['1']:0 ?>
                    x:<?= (isset($event->participants_count['2'])) ? $event->participants_count['2']:0 ?>
                </div>
            </div>

            <div class="event-item-content-right">
                <a class="buttons" href="<?= $this->Url->build(['controller' => 'events','action' => 'detail', $event->id]); ?>">
                    参加表明/詳細
                </a>
            </div>
        </div>

        <?php if ($current_user) : ?>
            <div class="description_toggle" id="desc_<?= $event->id ?>">
                参加者<span class="material-symbols-outlined">expand_all</span>
            </div>
            <div class="description" style="display: none;">
                <?php foreach ($event['event_responses'] as $event_responses) : ?>
                    <?= $event_responses->response_state ?> | <?= $event_responses->user->display_name ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>