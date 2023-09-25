<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
    <meta name="google-site-verification" content="UQrGAuT7PirzIi15OAbXemRlbpo68bOqPNV5t7khHSE" />
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.js"  integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>

    <?= $this->Html->css(['normalize.min', 'common']) ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/grids-responsive-min.css" />

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>

<style>
    /* 全てのページで有効するstyle */
    * { margin: 0;padding: 0;box-sizing: border-box;}
    a, a:visited {color: black;text-decoration: none;}
    input[type=text],input[type=textarea], textarea,input[type=number],input[type=password] {font-size: 20px !important;}
    @media screen and (max-width: 768px) {
        body {font-size: 14px;}
    }

    #default-main {
        padding-top: 50px;
        padding-bottom: 100px;
    }
    /* 背景色->背景画像の周りの色に合わせてる */
    #default-body {background-image: linear-gradient(to right, rgb(92, 224, 229), rgb(0, 74, 173));}
    /* 背景画像->alpha0.8 */
    #default-body::before {
        content: "";
        display: block;
        position: fixed;
        background-image:url("<?= $this->Url->image('unity-background.jpg'); ?>"); 
        background-color: rgba(255, 255, 255, 0.8);
        background-blend-mode: overlay;
        background-size: contain;
        background-repeat: no-repeat;
        background-position-x: center;
        background-position-y: center;
        min-height: 100vh;
        width: 100%;
        z-index:-1;
    }
    #container {
        padding: 0px 1rem;
        max-width: 800px;   
    }
    #content-title {
        font-size: 2rem;
        padding: 10px;
    }
    
</style>
<body id="default-body">
    <div id="default-main">
        <header>
            <?= $this->element('top-nav'); ?>  
        </header>
        <?php echo $this->Flash->render(); ?>
        <main class="main disp-flex just-center">
            <div id="container" class="w100">
                <h1 id="content-title">
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
