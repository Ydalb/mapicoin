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
                        />
                </div>
                <button
                    id="input-submit"
                    class="btn btn-warning hand"
                    type="submit"
                    data-text="Afficher les résultats"
                    data-loading-text="Chargement... <i class='glyphicon glyphicon-refresh glyphicon-spin'></i>">
                    Afficher les résultats
                    <i class="glyphicon glyphicon-map-marker" aria-hidden="true"></i>
                </button>
              </form>
              <button type="button" class="btn btn-default navbar-btn hand pull-right" data-toggle="modal" data-target="#modal-help">Besoin d'aide ?</button>
            </div><!-- /.navbar-collapse -->
          </div><!-- /.container-fluid -->
        </nav>


        <!-- Modal -->
        <div id="modal-help" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 class="modal-title">Comment ça marche ?</h2>
              </div>
              <div class="modal-body">

              <!-- Carousel -->
                <div id="carousel-mapicoin-howto" class="carousel slide" data-ride="carousel">
                  <!-- Indicators -->
                  <ol class="carousel-indicators">
                    <li data-target="#carousel-mapicoin-howto" data-slide-to="0" class="active"></li>
                    <li data-target="#carousel-mapicoin-howto" data-slide-to="1"></li>
                    <li data-target="#carousel-mapicoin-howto" data-slide-to="2"></li>
                    <li data-target="#carousel-mapicoin-howto" data-slide-to="3"></li>
                  </ol>

                  <!-- Wrapper for slides -->
                  <div class="carousel-inner" role="listbox">
                    <div class="item active">
                      <img src="/img/howto/mapicoin-howto-1.png" alt="Mapicoin howto 1">
                      <div class="carousel-caption">
                        <h3>Étape 1</h3>
                        <p>Effectuez une recherche sur leboncoin avec vos critères.</p>
                      </div>
                    </div>
                    <div class="item">
                      <img src="/img/howto/mapicoin-howto-2.png" alt="Mapicoin howto 2">
                      <div class="carousel-caption">
                        <h3>Étape 2</h3>
                        <p>Copiez l'URL de la barre d'adresse de votre navigateur.</p>
                      </div>
                    </div>
                    <div class="item">
                      <img src="/img/howto/mapicoin-howto-3.png" alt="Mapicoin howto 3">
                      <div class="carousel-caption">
                        <h3>Étape 3</h3>
                        <p>Collez l'URL dans la barre de recherche de mapicoin et lancez la recherche (La recherche peut prendre quelques secondes, le temps de récupérer les annonces)</p>
                      </div>
                    </div>
                    <div class="item">
                      <img src="/img/howto/mapicoin-howto-4.png" alt="Mapicoin howto 4">
                      <div class="carousel-caption">
                        <h3>Étape 4</h3>
                        <p>Enfin, visualisez les annonces sur une carte Google, et en un coup d'oeil, retenez celles qui sont le plus proche de chez vous ! En cliquant sur les marqueurs, la distance ainsi que le temps de trajet (pour une voiture) s'affichera. Facile !</p>
                      </div>
                    </div>
                  </div>

                  <!-- Controls -->
                  <a class="left carousel-control" href="#carousel-mapicoin-howto" role="button" data-slide="prev">
                    <span class="icon-prev" aria-hidden="true"></span>
                    <span class="sr-only">Précédent</span>
                  </a>
                  <a class="right carousel-control" href="#carousel-mapicoin-howto" role="button" data-slide="next">
                    <span class="icon-next" aria-hidden="true"></span>
                    <span class="sr-only">Suivant</span>
                  </a>
                </div>

              </div>
            </div>
          </div>
        </div>

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