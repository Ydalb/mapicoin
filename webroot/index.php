<?php
    require_once '../inc/config.inc.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Mapicoin, votre recherche leboncoin.fr sur une carte</title>

        <meta name="description" content="Visualiser les annonces d'une recherche leboncoin sur une carte ? C'est possible ! Grâce à Mapicoin, entrez votre lien de recherche et on s'occupe du reste !" />

        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />

        <link href="/css/mapicoin.css?<?= VERSION ?>" rel="stylesheet" />
        <link href="/css/ribbon.min.css?<?= VERSION ?>" rel="stylesheet" />

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="//maps.googleapis.com/maps/api/js?key=AIzaSyA797A1ZQxzPqs2oaWLeaFvvGEySX9EVCw"></script>
        <script src="/js/map-icons.min.js?<?= VERSION ?>"></script>

        <script src="/js/mapicoin.js?<?= VERSION ?>"></script>

        <link rel="icon" type="image/png" href="favicon.png" />

    </head>
    <body>

        <header id="header">

            <a class="github-fork-ribbon right-top" href="https://mapicoin.fr/plugins/chrome-mapicoin.crx" title="Plugin Chrome">
                Plugin Chrome !
            </a>

            <div class="text-vertical-center">
                <h1><span>mapi</span>coin</h1>
                <h3>Prévisualisez les annonces de votre recherche <a href="https://www.leboncoin.fr/" rel-"external" target="_blank">leboncoin.fr</a> sur une carte.</h3>
                <form id="form-search" action="#">
                    <input
                        id="input-url"
                        class="big-input"
                        name="u"
                        value=""
                        placeholder="Copiez/collez votre lien de recherche..." />
                    <br />
                    <div class="form-footer">
                        <input
                            id="input-submit"
                            class="submit"
                            type="submit"
                            data-value="Et hop !"
                            value="Et hop !" />
                        <div id="loader" class="cssload-loader-walk" style="display:none;">
                            <div></div><div></div><div></div><div></div><div></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="footer">
                © 2016 Mapicoin - Mapicoin est un site indépendant du site Leboncoin.fr
            </div>
        </header>


        <!-- Back to search -->
        <a
            id="new-search"
            class="submit"
            style="display:none;">Nouvelle recherche</a>


        <!-- Map -->
        <section id="map" style="display:none;"></section>


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