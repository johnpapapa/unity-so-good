<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $current_user
 * @var mixed $displayCreatedBtn
 * @var mixed $events
 */
    //一覧内部で繰り返し使用する変数の宣言
    use Cake\Core\Configure;
    //ユーザーの参加情報をconstから読出
    $response_states = Configure::read('response_states');
    //イベントの開催状況をconstから読出
    $event_states = Configure::read('event_states');
    //dayOfWeekの返り値に対応する曜日をconstから読出
    $day_of_weeks = Configure::read('day_of_weeks');
?>
<style>
    .event-item.event-outer {
        border: 2px solid;
        border-radius: 5px;
        box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
    }

    .event-item.delete-item {
    background-color: darkgray;
    }

    .event-item .event-inner {
        margin: 5px;
    }

    .event-item .tag {
        padding: 5px 15px;
        border-radius: 5px;
        font-size: .7rem;
    }

    .event-item .location {
        font-size: 1.5rem;
    }

    .event-item .state-title {
        color: #00000055;
        font-size: 1.2rem;
    }

    .event-item .comments .comments-title {
        font-size: 1.2rem;
    }

    .event-item .comments .comment {
    border: black 1px solid;
    border-radius: 5px;
    }

    .event-item .comment-header .time {
    font-size: .7rem;
    }

    .event-item .state-0 {background-color: #d3d3d37F;}
    .event-item .state-1 {background-color: #90ee907F;}
    .event-item .state-2 {background-color: #f080807F;}
</style>
<div>
    <?php foreach($events as $event): ?> 
        <?php 
            $user_response_state = (!is_null($event->user_response_state)) ? $response_states[$event->user_response_state] : null;
            $event_state = $event_states[$event->event_state];
            $day_of_week = $day_of_weeks[$event->start_time->dayOfWeek]; 
            $response_count = [0 => count($event->event_responses[0]),1 => count($event->event_responses[1]),2 => count($event->event_responses[2])];
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
                                復元
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
                    <?php if($event->deleted_at): ?>
                        <div class="tag disp-iblock" style="background-color: indianred;">
                            削除済
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
                    <span>(<?= $day_of_week ?>)</span>
                </div>

                <div class="location mb10 bold over-ellipsis">
                    <?= h($event->location->display_name) ?>
                </div>

                <div class="inner-content disp-flex mb10">
                    <div class="content-left w50">
                        <div class="area over-ellipsis mb5">
                            <?= is_null($event->area) ? '' : h($event->area)."コート" ?>
                        </div>

                        <div class="time mb5">
                            <?= $event->start_time->i18nFormat('HH:mm'); ?> ~ <?= $event->end_time->i18nFormat('HH:mm');  ?>
                        </div>

                        <div class="limit mb5">
                            人数制限:<?= $event->participants_limit <= 0 ? 'なし' : h($event->participants_limit)."人" ?>
                        </div>

                        <div class="count mb5">
                            o:<?= $response_count[1] ?>
                            ?:<?= $response_count[0] ?>
                            x:<?= $response_count[2] ?>
                        </div>
                        <div class="total mb5">
                            (合計:<?= array_sum($response_count) ?>人)
                        </div>
                    </div>

                    <div class="content-right w50 disp-flex dir-column dir-m-column just-space align-center" style="gap: 10px">
                        <a class="buttons" href="<?= $this->Url->build(['controller' => 'events','action' => 'detail', $event->id]); ?>">
                            <button class="pure-button" type="button">
                            参加表明/詳細
                            </button>
                        </a>
                        <div class="disp-flex" style="gap: 10px">
                            <button class="pure-button" type="button">
                                参加
                            </button>
                            <button class="pure-button" type="button">
                                不参加
                            </button>

                        </div>
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
                                <?php if($response_count[$state_idx] > 0): ?>
                                <div class="state-content mt10">
                                    <?php foreach($event->event_responses[$state_idx] as $event_response): ?>
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
                                <?php if($response_count[$state_idx] > 0): ?>
                                <div class="state-content mt10">
                                    <?php foreach($event->event_responses[$state_idx] as $event_response): ?>
                                        <div class="over-ellipsis"><?= h($event_response['display_name']); ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if(count($event->comments)): ?>
                            <div class="comments_toggle disp-flex just-center align-center" style="margin: 10px;">
                                <span class="material-symbols-outlined">expand_all</span>
                                コメント確認
                            </div>
                            
                            <div class="comments">
                                <?php foreach($event->comments as $comment): ?>
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
    $(function(){
        function changeDisplayElement(element){
            if (element.style.display === "none"){
                element.style.display = "block";
            } else {
                element.style.display = "none";
            }
        }

        $description_toggle_obj = $('.description_toggle');
        $comments_toggle_obj = $('.comments_toggle');
        [$description_toggle_obj, $comments_toggle_obj].forEach(function(element){
            element.on('click', function(){
                changeDisplayElement($(this).next()[0]);
            });  
        })

        $('.delete-lnk').on('click', function(event){
            var res = confirm('このイベントを削除しますか?');
            if(!res){
            event.preventDefault();
            }
        });
    });
</script>