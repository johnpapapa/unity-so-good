<nav class="bottom-nav">
  <ul class="nav-links">
      <li><a href="<?= $this->Url->build(['controller' => 'events','action' => 'index']); ?>">一覧</a></li>
      <li><a href="<?= $this->Url->build(['controller' => 'events','action' => 'unresponded']); ?>">未表明</a></li>
      <li><a href="<?= $this->Url->build(['controller' => 'events','action' => 'participate']); ?>">参加予定</a></li>
      <li><a href="<?= $this->Url->build(['controller' => 'events','action' => 'add']); ?>">作成</a></li>
  </ul>
</nav>