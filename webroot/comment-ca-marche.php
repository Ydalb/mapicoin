<?php
    require_once '../inc/config.inc.php';

    $title       = "Comment ça marche ?";
    $description = "Mapicoin, comment ça marche - Découvrez comment fonctionne Mapicoin et apprenez à améliorer vos recherches leboncoin de manière significative !";
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title><?= $title ?> - Mapicoin</title>
        <meta name="description" content="<?= $description ?>" />

        <!-- Open Graph -->
        <meta property="og:title" content="<?= $title ?? 'Mapicoin' ?>" />
        <meta property="og:description" content="<?= $description ?>" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://<?= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>" />
        <meta property="og:image" content="https://<?= $_SERVER['HTTP_HOST']?>/img/mapicoin-meta-og.png" />

        <?php include 'inc/header.inc.php'; ?>

        <link href="/css/mapicoin-page.css?<?= VERSION ?>" rel="stylesheet" />

    </head>

    <body class="page">

        <div class="page-container">

            <div class="text-center">
                <a href="/">
                    <img id="logo" src="/img/mapicoin-logo.png" width="409" height="112" />
                </a>
            </div>

            <!-- <div class="page-logo">
                <a href="/">
                  <i class="glyphicon glyphicon-map-marker" aria-hidden="true"></i>
                  <span>mapi</span><span>coin</span>
                  <p class="baseline">votre recherche leboncoin.fr sur une carte</p>
                </a>
            </div> -->

            <div class="page-body">

                <div class="page-breadcrumb">
                    <a href="/">Accueil</a> &raquo; Mapicoin : Mode d'emploi
                </div>

                <h1><span>Mapicoin : <?= $title ?></span></h1>

<div class="page-content">
    <h2>Comment ça marche ?</h2>
    <p>Mapicoin permet de visualiser les annonces du site leboncoin.fr sur une carte interactive.</p>
    <p>À partir d'une recherche LeBonCoin, Mapicoin vous aide à localiser l'ensemble des annonces sur une carte pour que vous puissiez avoir un regard global sur la situation géographique de chaque annonce.</p>
    <p>Sans plus attendre, voici une vidéo vous expliquant le principe :</p>
    <div class="text-center">
        <iframe width="560" height="315" src="https://www.youtube.com/embed/twtMFZpPMHc" frameborder="0" allowfullscreen></iframe>
    </div>

    <h2>Mode automatique <span class="sup">(recommandé)</span></h2>
    <p>Nous vous recommandons d'utiliser notre plugin Mapicoin afin de passer rapidement du site d'annonce à mapicoin!</p>
    <p>Concrètement, une fois le plugin installé, il vous suffit, à partir d'une page de recherche, de cliquer sur ce dernier pour ouvrir automatiquement le site Mapicoin avec votre recherche déjà paramétrée. Ainsi, les résultats s'afficheront directement sur la carte, sans avoir à copier/coller l'URL dans le champ prévu à cet effet.</p>
    <p>Nous supportons actuellement deux navigateurs :</p>
    <p>
        <a class="download-plugin" target="_blank" href="<?= URL_EXTENSION_CHROME ?>">
            <img src="/img/chrome-64x64.png" width="64" height="64" alt="Plugin Mapicoin Chrome" />
            <span>Téléchargez l'extension pour Chrome</span>
        </a>
        <a class="download-plugin" target="_blank" href="<?= URL_EXTENSION_FIREFOX ?>">
            <img src="/img/firefox-64x64.png" width="64" height="64" alt="Plugin Mapicoin Firefox" />
            <span>Téléchargez l'extension pour Firefox</span>
        </a>
    </p>
    <p>Pour utiliser l'extension, c'est très simple :</p>
    <ul>
        <li>1. Installez-la ;-)</li>
        <li>2. Effectuez votre recherche sur leboncoin</li>
        <li>3. L'icône de l'extension change de couleur (Elle passe du gris à l'orange)</li>
        <li>4. Cliquez sur l'icône de l'extension et explorez ce fabuleux service!<br />
        (Vous pouvez aussi cliquer droit sur la page > "Voir sur mapicoin")</li>
    </ul>


    <h2>Mode manuel</h2>
    <p>Pour fonctionner, mapicoin nécessite l'URL de votre recherche leboncoin.</p>
    <p>Pour utiliser Mapicoin manuellement, procédez ainsi :</p>
    <ul>
        <li>1. Effectuez votre recherche sur leboncoin</li>
        <li class="img hidden-xs">
            <img src="/img/howto/mapicoin-how-to-1-min.png" alt="Mapicoin howto 1" />
        </li>
        <li>2. Copiez ensuite l'URL du site leboncoin</li>
        <li class="img hidden-xs">
            <img src="/img/howto/mapicoin-how-to-2-min.png" alt="Mapicoin howto 2" />
        </li>
        <li>3. Rendez-vous ensuite sur <a href="https://mapicoin.fr">mapicoin.fr</a> et collez l'URL dans le champ prévu à cet effet</li>
        <li class="img hidden-xs">
            <img src="/img/howto/mapicoin-how-to-3-min.png" alt="Mapicoin howto 3" />
        </li>
        <li>4. Lancez la recherche en cliquant sur "Afficher les résutlats"</li>
        <li class="img hidden-xs">
            <img src="/img/howto/mapicoin-how-to-4-min.png" alt="Mapicoin howto 4" />
        </li>
        <li>5. Profitez du service ;-)</li>
    </ul>

    <p>That's all folk's !</p>
</div><!-- /.page-content -->

            </div><!-- /.page-body -->

            <?php include 'inc/page-footer.inc.php' ?>

        </div><!-- /.page-container -->

        <?php include 'inc/ga.inc.php' ?>

    </body>
</html>
