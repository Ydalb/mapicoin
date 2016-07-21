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

        <?php include 'inc/header.inc.php'; ?>

        <script src="//maps.googleapis.com/maps/api/js?key=<?= $_CONFIG->api->google_browser_key ?>"></script>
        <script src="/js/mapicoin-map.js?<?= VERSION ?>"></script>
        <script src="/js/mapicoin.js?<?= VERSION ?>"></script>
        <script src="/js/jquery.lazyload.min.js"></script>

    </head>
    <body>

        <!-- <a class="github-fork-ribbon right-top" href="https://mapicoin.fr/plugins/chrome-mapicoin.crx" title="Plugin Chrome">
            Plugin Chrome !
        </a> -->

        <?php include 'inc/navbar.inc.php' ?>

        <?php include 'inc/left-panel.inc.php' ?>

        <!-- Overlay -->
        <section id="overlay"></section>
        <!-- Map -->
        <section id="map"></section>

        <!-- Modal -->
        <?php include 'inc/modal.inc.php' ?>

        <?php include 'inc/ga.inc.php' ?>

    </body>
</html>