<?php
    require_once '../inc/config.inc.php';
    $center      = $_SITES[$_SITE]['map-center']       ?? $_SITES[SITE_LEBONCOIN]['map-center'];
    $title       = $_SITES[$_SITE]['site-title']       ?? $_SITES[SITE_LEBONCOIN]['site-title'];
    $description = $_SITES[$_SITE]['site-description'] ?? $_SITES[SITE_LEBONCOIN]['site-description'];
?>
<!DOCTYPE html>
<html lang="fr">

    <head>

        <title><?= $title ?? 'Mapicoin' ?></title>
        <meta name="description" content="<?= $description ?>" />

        <!-- Open Graph -->
        <meta property="og:title" content="<?= $title ?? 'Mapicoin' ?>" />
        <meta property="og:description" content="<?= $description ?>" />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://<?= $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] ?>" />
        <meta property="og:image" content="https://<?= $_SERVER['HTTP_HOST']?>/img/mapicoin-meta-og.png" />


        <?php include 'inc/header.inc.php'; ?>

        <script src="//maps.googleapis.com/maps/api/js?key=<?= $_CONFIG->api->google_browser_key ?>"></script>
        <script src="/js/mapicoin-map.js?<?= VERSION ?>"></script>
        <script src="/js/mapicoin-tools.js?<?= VERSION ?>"></script>
        <script src="/js/mapicoin.js?<?= VERSION ?>"></script>
        <script src="/js/jquery.lazyload.min.js"></script>

    </head>
    <body>

        <?php include 'inc/navbar.inc.php' ?>

        <?php include 'inc/left-panel.inc.php' ?>

        <div id="map"></div>

        <div id="container">
            <div class="container-center">
                <div class="text-center">

                    <a href="/"><img id="logo" src="/img/mapicoin-logo.png" width="409" height="112" /></a>

                    <form id="form-search" action="#" role="search" class="form-search">
                        <div class="form-group">
                            <input
                                id="input-url"
                                class="input-url input-lg"
                                name="u"
                                value=""
                                size="45"
                                type="text"
                                placeholder="Copiez/collez ici votre URL de recherche leboncoin ..."
                                />
                        </div>
                        <div class="form-group">
                            <button
                                id="input-submit"
                                class="input-submit btn btn-warning btn-lg hand"
                                type="submit"
                                data-text="Afficher les résultats"
                                data-loading-text="Chargement... <i class='glyphicon glyphicon-refresh glyphicon-spin'></i>">
                                Afficher les résultats
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /#container -->

        <footer>
            <a href="/comment-ca-marche.php"><strong>Comment ça marche ?</strong></a>
            <a class="hide-mobile" href="https://www.facebook.com/mapicoin/">Facebook</a>
            <a class="hide-mobile" href="http://twitter.com/mapicoin">@Twitter</a>
            <span class="rightlinks">© <?php echo date('Y');?><span class="hide-mobile"> - Mapicoin est un site indépendant du site Leboncoin.fr</span></span>
        </footer>

        <?php include 'inc/ga.inc.php' ?>

    </body>
</html>
