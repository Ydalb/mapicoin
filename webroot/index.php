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
        <meta name=viewport content="width=device-width,initial-scale=1">

        <link href="/css/bootstrap.min.css" rel="stylesheet" />
        <link href="/css/todc-bootstrap.min.css" rel="stylesheet" />
        <link href="/css/ribbon.min.css" rel="stylesheet" />
        <link href="/css/mapicoin.css?<?= VERSION ?>" rel="stylesheet" />

        <!-- <link href="/css/font-awesome.min.css" rel="stylesheet" /> -->

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="//maps.googleapis.com/maps/api/js?key=AIzaSyA797A1ZQxzPqs2oaWLeaFvvGEySX9EVCw"></script>

        <script src="/js/bootstrap.min.js"></script>
        <script src="/js/geolocation-marker.min.js"></script>
        <script src="/js/mapicoin-map.js?<?= VERSION ?>"></script>
        <script src="/js/mapicoin.js?<?= VERSION ?>"></script>

        <link rel="icon" href="favicon.ico?<?= VERSION ?>" />

    </head>
    <body>

        <!-- <a class="github-fork-ribbon right-top" href="https://mapicoin.fr/plugins/chrome-mapicoin.crx" title="Plugin Chrome">
            Plugin Chrome !
        </a> -->

        <nav id="navbar" class="navbar navbar-masthead navbar-inverse">
          <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <h1><a class="navbar-brand" href="#"><span>mapi</span><span>coin</span></a></h1>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <form id="form-search" action="#" class="navbar-form navbar-left" role="search">
                <div class="form-group">
                  <input
                        id="input-url"
                        class="form-control"
                        name="u"
                        value=""
                        size="35"
                        placeholder="Copiez/collez votre lien de recherche..."
                        data-trigger="focus"
                        data-toggle="popover"
                        data-placement="bottom"
                        title="Comment ça marche ?"
                        data-content="Renseignez dans ce champ (copiez/collez) votre URL de recherche leboncoin (la page correspondant à la liste des résultats), puis cliquez sur 'Affichez' !"
                        />
                </div>
                <button
                    id="input-submit"
                    class="btn btn-warning"
                    type="submit"
                    data-text="Afficher les résultats">
                    Afficher les résultats
                    <i class="glyphicon glyphicon-map-marker" aria-hidden="true"></i>
                </button>
              </form>
              <ul class="nav navbar-nav navbar-right">
                <li><a href="/help.php">Besoin d'aide ?</a></li>
                <li class="dropdown">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li><a href="#">Action</a></li>
                    <li><a href="#">Another action</a></li>
                    <li><a href="#">Something else here</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                  </ul>
                </li>
              </ul>
            </div><!-- /.navbar-collapse -->
          </div><!-- /.container-fluid -->
        </nav>

        <!-- <header id="header">
            <h3>Prévisualisez les annonces de votre recherche <a href="https://www.leboncoin.fr/" rel-"external" target="_blank">leboncoin.fr</a> sur une carte.</h3>
            <div class="footer">
                © 2016 Mapicoin - Mapicoin est un site indépendant du site Leboncoin.fr
            </div>
        </header> -->

        <!-- Map -->
        <section id="map"></section>

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