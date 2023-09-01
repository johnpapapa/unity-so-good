<?php // $this->Html->css(['event-item']) ?>
<?= $this->Html->script('event-item', array('inline' => false)); ?>
<?php
    use Cake\Core\Configure;
    $user_response_state = (!is_null($event->user_response_state)) ? Configure::read('response_states')[$event->user_response_state] : null;
    $event_state = Configure::read('event_states')[$event->event_state];
?>

<style>
    .event-outer {
        border: 2px solid;
        border-radius: 5px;
        box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
    }

    .event-inner {
        margin: 5px;
    }

    .tag {
        padding: 5px;
        border-radius: 5px;
        font-size: .5rem;
    }

    .location {
        font-size: 1.5rem;
    }
</style>

<div class="event-outer mb20">
    <div class="event-inner ">

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
            <?= $event->date ?>
            <span>(<?= $event->day_of_week ?>)</span>
        </div>

        <div class="location mb10 bold over-ellipsis">
            <?= $event->location->display_name ?>県立長浜新杉田本牧テニスコート
        </div>

        <div class="inner-content disp-flex mb10">
            <div class="content-left w50">
                <div class="area over-ellipsis mb5">
                    <?= is_null($event->area) ? '' : $event->area . ',1,2,B,6,C,8,9コート' ?>
                </div>

                <div class="time mb5">
                    <?= $event->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event->end_time->i18nFormat('HH:mm');  ?>
                </div>

                <div class="limit mb5">
                    人数制限:<?= $event->participants_limit <= 0 ? 'なし' : $event->participants_limit . '人' ?>
                </div>

                <div class="count mb5">
                    ?:<?= (isset($event->participants_count['0'])) ? $event->participants_count['0']:0 ?>
                    o:<?= (isset($event->participants_count['1'])) ? $event->participants_count['1']:0 ?>
                    x:<?= (isset($event->participants_count['2'])) ? $event->participants_count['2']:0 ?>
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
            <div class="description_toggle disp-flex just-center align-center">
                <span class="material-symbols-outlined">expand_all</span>
                参加者
            </div>

            <div class="description" style="display: none;">
                <?php foreach ($event['event_responses'] as $event_responses) : ?>
                    <?= Configure::read('response_states')[$event_responses->response_state]["icon"] ?> | <?= $event_responses->user->display_name ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>