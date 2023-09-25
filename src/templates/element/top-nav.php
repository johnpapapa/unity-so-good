<head>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head>

<style>
  #top-nav #top-nav-logo {
    font-family: "Nico Moji";
    padding: 0px;
  }


  #top-nav {
    top: 0;
    left: 0;
    width: 100%;
    padding: 10px 50px;
    background: #6a6766;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    justify-content: space-between;
    max-height: 50px;
    position: fixed;
  }

  #top-nav,
  #top-nav-links {
    display: flex;
    align-items: center;
    column-gap: 30px;
    list-style: none;
  }

  #top-nav-user-links {
    display: flex;
    align-items: center;
    justify-content: flex-end;
  }

  #top-nav-user-links a {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }

  #top-nav a {
    color: #fff;
    text-decoration: none;
  }

  #top-nav a:hover {
    color: yellow;
  }

  #top-nav .navOpenBtn,
  #top-nav-user-links {
    width: 100px;
  }

  #top-nav .material-symbols-outlined {
    color: white;
    font-size: 25px;
  }

  #top-nav-logo {
    font-size: 22px;
    font-weight: 500;
  }

  #top-nav #top-nav-links li {
    margin-bottom: 0;
    display: block;
    width: 100%;
    text-align: center;
    font-size: 1.5em;
  }

  #top-nav #top-nav-links a {
    display: block;
    width: 100%;
    height: 100%;
    padding: 30px 0;
    font-size: 16px;
  }

  #top-nav .navOpenBtn,
  #top-nav .navCloseBtn {
    display: none;
  }

  @media screen and (max-width: 768px) {

    #top-nav .navOpenBtn,
    #top-nav .navCloseBtn {
      display: block;
    }

    #top-nav {
      padding: 5px 10px;
    }

    #top-nav-links {
      position: fixed;
      top: 0;
      left: -100%;
      height: 100%;
      max-width: 280px;
      width: 100%;
      padding-top: 100px;
      padding-bottom: 100px;
      flex-direction: column;
      background-color: #11101d;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      transition: all 0.4s ease;
      z-index: 100;
    }

    #top-nav #top-nav-links a {
      font-size: 24px;
    }

    #top-nav.openNav #top-nav-links {
      left: 0;
    }

    #top-nav .navOpenBtn {
      color: #fff;
      font-size: 30px;
      cursor: pointer;
      padding: 0 20px;
    }

    #top-nav .navCloseBtn {
      position: absolute;
      top: 20px;
      right: 20px;
      color: #fff;
      font-size: 35px;
      cursor: pointer;
    }
  }
</style>

<nav id="top-nav">
  <span class="material-symbols-outlined navOpenBtn">double_arrow</span>
  <a href="<?= $this->Url->build(['prefix' => false, 'controller' => 'informations', 'action' => 'about']); ?>" id="top-nav-logo">UNITY
  </a>
  <ul id="top-nav-links">
    <span class="material-symbols-outlined navCloseBtn">close</span>
    <li><a href="<?= $this->Url->build(['prefix' => false, 'controller' => 'informations', 'action' => 'about']); ?>">About</a></li>
    <li><a href="<?= $this->Url->build(['prefix' => false, 'controller' => 'informations', 'action' => 'about']); ?>#contact">Contact</a></li>
    <li><a href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'administrators', 'action' => 'index']); ?>">Admin</a></li>
  </ul>
  <div id="top-nav-user-links">
    <span class="material-symbols-outlined">person</span>
    <?php if ($current_user) : ?>

      <a href="<?= $this->Url->build(['prefix' => false, 'controller' => 'users', 'action' => 'detail']); ?>">
        <?= h($current_user->display_name) ?>
      </a>
    <?php else : ?>
      <a href="<?= $this->Url->build(['prefix' => false, 'controller' => 'users', 'action' => 'login']); ?>">
        ゲスト
      </a>
    <?php endif; ?>
  </div>
</nav>

<script>
  const nav = document.getElementById('top-nav');
  // const nav = document.querySelector("#top-nav");
  
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