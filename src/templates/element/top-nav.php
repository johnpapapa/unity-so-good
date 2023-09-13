<head>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link href="https://fonts.googleapis.com/earlyaccess/nicomoji.css" rel="stylesheet">
</head>

<style>
  .top-nav .logo {
    font-family: "Nico Moji";
    padding: 0px;
  }
</style>

<nav class="top-nav">
  <span class="material-symbols-outlined navOpenBtn">double_arrow</span>
  <a href="<?= $this->Url->build(['prefix'=>false, 'controller' => 'informations','action' => 'about']); ?>" class="logo">UNITY
  </a>
  <ul class="nav-links">
    <span class="material-symbols-outlined navCloseBtn">close</span>
    <li><a href="<?= $this->Url->build(['prefix'=>false, 'controller' => 'informations','action' => 'about']); ?>">About</a></li>
    <li><a href="<?= $this->Url->build(['prefix'=>false, 'controller' => 'informations','action' => 'about']); ?>#contact">Contact</a></li>
    <li><a href="<?= $this->Url->build(['prefix'=>'Admin', 'controller' => 'administrators','action' => 'index']); ?>">Admin</a></li>
  </ul>
  <div class="user-links">
    <span class="material-symbols-outlined">person</span>
    <?php if($current_user): ?>
      
      <a href="<?= $this->Url->build(['prefix'=>false, 'controller' => 'users','action' => 'detail']); ?>">
        <?= h($current_user->display_name) ?>
      </a>
    <?php else: ?>
      <a href="<?= $this->Url->build(['prefix'=>false, 'controller' => 'users','action' => 'login']); ?>">
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