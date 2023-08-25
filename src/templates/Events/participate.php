<?php $this->assign('title', 'Events'); ?>
<?php $this->assign('content-title', 'イベント一覧'); ?>

<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>