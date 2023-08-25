<?php $this->assign('title', 'Events'); ?>
<?php $this->assign('content-title', '作成済イベント一覧'); ?>


<div class="buttons">
    <a href="<?= $this->Url->build(['controller' => 'events','action' => 'add']); ?>">作成</a>
</div>
<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>