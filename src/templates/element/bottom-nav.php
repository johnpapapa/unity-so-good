<?php
  $action_name = $this->getRequest()->getParam('action');
  if($this->getRequest()->getParam('prefix') == 'Admin'){ //同名のactionについてはprefix次第で空文字にする
    $action_name = '';
  }
?>

<style>
  #bottom-nav {
    left: 0;
    width: 100%;
    background: #6a6766;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    position: fixed;
    bottom: 0;
    z-index: 99;
  }

  #bottom-nav-links{
      display: flex;
      align-items: center;
      justify-content: space-between;
  }

  #bottom-nav-links li{
      margin-bottom: 0;
      width: 100%;
      height: 100%;
      display: block;
      text-align: center;
  }
  #bottom-nav-links a{
      padding: 20px 10px;
      display: block;
      width: 100%;
      height: 100%;
      color: white;
      text-decoration: none;
  }
  #bottom-nav-links a.enable {
    background-color: #d25100;
  }

  #bottom-nav-links a:hover {
      background-color: white;
      color: black;
      transition: all 0.2s linear;
  }

  @media screen and (max-width: 768px) {
    #bottom-nav-links a{
      padding: 20px 10px;
      font-size: 18px;
    }
  }
</style>

<nav id="bottom-nav">
  <ul id="bottom-nav-links">
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