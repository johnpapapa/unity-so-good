<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $events
 */
?>
<?php $this->assign('title', 'event index'); ?>
<?php $this->assign('content-title', 'イベント一覧'); ?>

<p class="note-p mb20">
    今日以降のイベントが表示されます
</p>

<a href="<?= $this->Url->build(['controller' => 'events','action' => 'archived']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        イベントのアーカイブ
    </div>
</a>


<?php echo $this->element('event-item', array('events' => $events, 'displayResponseBtn' => false)); ?>