<head>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head>

<nav class="top-nav">
  <span class="material-symbols-outlined navOpenBtn">double_arrow</span>
  <a href="#" class="logo">Unity</a>
  <ul class="nav-links">
    <span class="material-symbols-outlined navCloseBtn">close</span>
    <li><a href="<?= $this->Url->build(['controller' => 'informations','action' => 'about']); ?>">About</a></li>
    <li><a href="<?= $this->Url->build(['controller' => 'informations','action' => 'about']); ?>#contact">Contact</a></li>
  </ul>
  <div class="user-links">
    <span class="material-symbols-outlined">person</span>
    <?php if($current_user): ?>
      
      <a href="<?= $this->Url->build(['controller' => 'users','action' => 'detail']); ?>">
        <?= h($current_user->display_name) ?>
      </a>
    <?php else: ?>
      <a href="<?= $this->Url->build(['controller' => 'users','action' => 'login']); ?>">
        ゲスト
      </a>
    <?php endif; ?>
  </div>
</nav>

<script>
  const nav = document.querySelector(".top-nav");
  const navOpenBtn = document.querySelector(".navOpenBtn");
  const navCloseBtn = document.querySelector(".navCloseBtn");

  navOpenBtn.addEventListener("click", () => {
    nav.classList.add("openNav");
    nav.classList.remove("openSearch");
  });
  navCloseBtn.addEventListener("click", () => {
    nav.classList.remove("openNav");
  });
</script>