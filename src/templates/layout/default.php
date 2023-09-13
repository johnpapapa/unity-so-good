<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.js"  integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/overcast/jquery-ui.min.css">

    <?= $this->Html->css(['normalize.min', 'templates', 'common']) ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/grids-responsive-min.css" />

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>

<style>
    #default-body {
        /* 背景色->背景画像の周りの色に合わせてる */
        background-image: linear-gradient(to right, rgb(0 230 255), rgb(0 80 185));
    }
    #default-main {
        /* 背景画像->alpha0.8 */
        background-image:url("<?= $this->Url->image('unity-background.jpg'); ?>"); 
        background-color: rgba(255, 255, 255, 0.8);
        background-blend-mode: overlay;
        background-attachment: fixed;
        background-size: contain;
        background-repeat: no-repeat;
        background-position-x: center;
        background-position-y: center;

        min-height: 100vh; /* 最低表示領域 */
        padding-bottom: 100px; /* bottom-nav分の余剰領域 */
    }
</style>
<body class="" id="default-body">
    <div id="default-main">
        <header>
            <?= $this->element('top-nav'); ?>  
        </header>
        <?php echo $this->Flash->render(); ?>
        <main class="main disp-flex just-center">
            <div class="container w100">
                <h1 class="content-title">
                    <?= $this->fetch('content-title') ?>
                </h1>
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
            </div>
        </main>
        <footer>
            <?= $this->element('bottom-nav'); ?>
        </footer>
    </div>
</body>
</html>
