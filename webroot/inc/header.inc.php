    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta charset=utf-8>
    <meta http-equiv=X-UA-Compatible content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, minimal-ui" />
    <link rel="icon" href="favicon.ico?<?= VERSION ?>" />
    <!-- <link href="/css/ribbon.min.css" rel="stylesheet" /> -->
    <!-- <link href="/css/bootstrap.min.css" rel="stylesheet" /> -->
    <!-- <link href="/css/todc-bootstrap.min.css" rel="stylesheet" /> -->

    <link href="/css/mapicoin.css?<?= VERSION ?>" rel="stylesheet" />
    <link href="/css/mapicoin-<?= $_SITE ?>.css?<?= VERSION ?>" rel="stylesheet" />
    <link href="/css/mapicoin-mobile.css?<?= VERSION ?>" rel="stylesheet" />

<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>-->
    <script src="/js/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>

    <script>
        var centerMap = {lat: <?= $center[0] ?? 0 ?>, lng: <?= $center[1] ?? 0 ?>};
    </script>

</head>
