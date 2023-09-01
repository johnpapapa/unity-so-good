<?php $this->assign('title', 'event created'); ?>
<?php $this->assign('content-title', '作成済イベント一覧'); ?>


<!-- <div class="pure-button mb30 pure-u-1-2">
    <a class="nostyle-a" href="<?= $this->Url->build(['controller' => 'events','action' => 'add']); ?>">新規作成</a>
</div> -->
<a class="nostyle-a" href="<?= $this->Url->build(['controller' => 'events','action' => 'add']); ?>">
    <div class="pure-button pure-u-1-2 mb30">
        新規作成
    </div>
</a>
<?php foreach($events as $event): ?>
    <?php echo $this->element('event-item', array('event' => $event, 'displayResponseBtn' => false)); ?>
<?php endforeach; ?>