<?php $this->assign('title', 'event index'); ?>
<?php $this->assign('content-title', 'イベント一覧'); ?>
<p class="p-note mb20">
    14日前までのイベントが表示されます
</p>
<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>
