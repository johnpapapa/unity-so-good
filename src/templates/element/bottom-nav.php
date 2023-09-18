<?php

  $action_name = $this->getRequest()->getParam('action');
  if($this->getRequest()->getParam('prefix') == 'Admin'){ //同名のactionについてはprefix次第で空文字にする
    $action_name = '';
  }
  
?>

<nav class="bottom-nav">
  <ul class="nav-links">
      <li>
        <a class="<?= ($action_name == 'index')?'enable':'' ?>" href="<?= $this->Url->build(['prefix'=>false, 'controller' => 'events','action' => 'index']); ?>">一覧</a>
      </li>
      <li>
        <a class="<?= ($action_name == 'unresponded')?'enable':'' ?>" href="<?= $this->Url->build(['prefix'=>false, 'controller' => 'events','action' => 'unresponded']); ?>">未表明</a>
      </li>
      <li>
        <a class="<?= ($action_name == 'participate')?'enable':'' ?>" href="<?= $this->Url->build(['prefix'=>false, 'controller' => 'events','action' => 'participate']); ?>">参加予定</a>
      </li>
      <li>
        <a class="<?= ($action_name == 'add' | $action_name == 'created')?'enable':'' ?>" href="<?= $this->Url->build(['prefix'=>false, 'controller' => 'events','action' => 'add']); ?>">作成</a>
      </li>
  </ul>
</nav>