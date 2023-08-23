<!-- <nav class="top-nav">
  <a href="#default" class="logo">Unity</a>
  <div class="nav-links">
    <div><a href="#contact">Contact</a></div>
    <div><a href="#about">About</a></div>
  </div>    
  <ion-icon name="notifications-outline"></ion-icon>
  </div>
</nav> -->
<head>
  <!-- <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" /> -->
  <!-- <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css"> -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head>


<nav class="nav">
  <!-- <i class="uil uil-bars navOpenBtn"></i> -->
  <!-- <span class="material-symbols-outlined navOpenBtn">menu</span> -->
  <span class="material-symbols-outlined navOpenBtn">double_arrow</span>
  <a href="#" class="logo">Unity</a>
  <ul class="nav-links">
    <!-- <i class="uil uil-times navCloseBtn"></i> -->
    <span class="material-symbols-outlined navCloseBtn">close</span>
    <li><a href="#">About</a></li>
    <li><a href="#">Contact</a></li>
  </ul>
  <div style="color:white">
    <span class="material-symbols-outlined">person</span>
    <?php if($current_user): ?>
      <?= $current_user->display_name ?>
    <?php else: ?>
      ゲスト
    <?php endif; ?>
  </div>
  
</nav>


<script>
  const nav = document.querySelector(".nav");
  // searchIcon = document.querySelector("#searchIcon"),
  const navOpenBtn = document.querySelector(".navOpenBtn");
  const navCloseBtn = document.querySelector(".navCloseBtn");

  // searchIcon.addEventListener("click", () => {
  //   nav.classList.toggle("openSearch");
  //   nav.classList.remove("openNav");
  //   if (nav.classList.contains("openSearch")) {
  //     return searchIcon.classList.replace("uil-search", "uil-times");
  //   }
  //   searchIcon.classList.replace("uil-times", "uil-search");
  // });

  navOpenBtn.addEventListener("click", () => {
    nav.classList.add("openNav");
    nav.classList.remove("openSearch");
    // searchIcon.classList.replace("uil-times", "uil-search");
  });
  navCloseBtn.addEventListener("click", () => {
    nav.classList.remove("openNav");
  });
</script>