<?php
    require_once '../inc/config.inc.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Mapicoin, votre recherche leboncoin.fr sur une carte</title>

        <meta name="description" content="Visualiser les annonces d'une recherche leboncoin sur une carte ? C'est possible ! Grâce à Mapicoin, entrez votre lien de recherche et on s'occupe du reste !" />

        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta charset=utf-8>
        <meta http-equiv=X-UA-Compatible content="IE=edge">
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" /> -->

        <link href="/css/bootstrap.min.css" rel="stylesheet" />
        <link href="/css/todc-bootstrap.min.css" rel="stylesheet" />
        <!-- <link href="/css/ribbon.min.css" rel="stylesheet" /> -->
        <link href="/css/mapicoin.css?<?= VERSION ?>" rel="stylesheet" />

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="//maps.googleapis.com/maps/api/js?key=<?= $_CONFIG->api->google_browser_key ?>"></script>

        <script src="/js/bootstrap.min.js"></script>
        <script src="/js/jquery.lazyload.min.js"></script>
        <!-- <script src="/js/geolocation-marker.min.js"></script> -->
        <script src="/js/mapicoin-map.js?<?= VERSION ?>"></script>
        <script src="/js/mapicoin.js?<?= VERSION ?>"></script>

        <link rel="icon" href="favicon.ico?<?= VERSION ?>" />

    </head>
    <body>

        <!-- <a class="github-fork-ribbon right-top" href="https://mapicoin.fr/plugins/chrome-mapicoin.crx" title="Plugin Chrome">
            Plugin Chrome !
        </a> -->

        <?php include 'navbar.inc.php' ?>

        <?php include 'left-panel.inc.php' ?>

        <!-- <header id="header">
            <h3>Prévisualisez les annonces de votre recherche <a href="https://www.leboncoin.fr/" rel-"external" target="_blank">leboncoin.fr</a> sur une carte.</h3>
            <div class="footer">
                © 2016 Mapicoin - Mapicoin est un site indépendant du site Leboncoin.fr
            </div>
        </header> -->

        <!-- Overlay -->
        <section id="overlay"></section>
        <!-- Map -->
        <section id="map"></section>

        <!-- Modal -->
        <?php include 'modal.inc.php' ?>

        <!-- Google analytics -->
        <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

            ga('create', 'UA-79412271-1', 'auto');
            ga('send', 'pageview');
        </script>

    </body>
</html>