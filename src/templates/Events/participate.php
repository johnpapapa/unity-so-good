<?php $this->assign('title', 'event participate'); ?>
<?php $this->assign('content-title', '参加予定イベント'); ?>

<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>