<?php $this->assign('title', 'event archived'); ?>
<?php $this->assign('content-title', 'アーカイブ一覧'); ?>
<p class="p-note mb20">
    開催済みのイベントが表示されます
</p>
<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>
