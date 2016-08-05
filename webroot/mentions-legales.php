<?php
    require_once '../inc/config.inc.php';

    $title       = "Mentions légales";
    $description = "Mapicoin, mentions légales - Découvrez comment fonctionne Mapicoin et apprenez à améliorer vos recherches leboncoin de manière significative !";
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

        <?php include 'inc/cookies-cnil-banner.inc.php' ?>

        <div class="page-container">

            <div class="text-center">
                <a href="/">
                    <img id="logo" src="/img/mapicoin-logo.png" width="409" height="112" />
                </a>
            </div>

            <div class="page-body">

                <div class="page-breadcrumb">
                    <a href="/">Accueil</a> &raquo; Mapicoin : Mentions légales
                </div>

                <h1><span>Mapicoin : <?= $title ?></span></h1>

<div class="page-content">
    <p>Directeur de la technique : Quentin - contact@mapicoin.fr</p>
    <p>Directeur de la publication : Vincent - contact@mapicoin.fr</p>

    <h2>Hébergeur</h2>

    <p>ONLINE SAS<br />
BP 438<br />
75366 PARIS CEDEX 08<br />
Tél : + 33 (0) 899 173 788</p>


    <h2>Mentions relatives aux cookies</h2>

    <h3>Qu’est-ce qu’un cookie et à quoi sert-il ?</h3>

    <p>Un cookie (ou témoin de connexion) est un fichier texte susceptible d’être enregistré, sous réserve de vos choix, dans un espace dédié du disque dur de votre terminal (ordinateur, tablette …) à l’occasion de la consultation d’un service en ligne grâce à votre logiciel de navigation.</p>
    <p>Il est transmis par le serveur d’un site internet à votre navigateur. A chaque cookie est attribué un identifiant anonyme. Le fichier cookie permet à son émetteur d’identifier le terminal dans lequel il est enregistré pendant la durée de validité ou d’enregistrement du cookie concerné. Un cookie ne permet pas de remonter à une personne physique.</p>
    <p>Lorsque vous consultez les sites jeanviet.info et blogbuster.fr, nous pouvons être amenés à installer, sous réserve de votre choix, différents cookies et notamment des cookies publicitaires.</p>

 

    <h3>Quels types de cookies sont déposés par le site Web ?</h3>
 

    <h4>Cookie publicitaires Google AdSense</h4>

    <p>Des services tiers de publicités comme la régie Adsense (et ses partenaires) contrôlent des cookies depuis leurs espaces publicitaires. Pour en savoir plus sur les annonces ciblées de Google, cliquez sur le lien règles de confidentialité.</p>

    <p>Le présent site Internet n’est pas responsable de la gestion et de la durée de vie de ces cookies tiers et vous invite à prendre connaissance de la politique de chacun de ces prestataires.</p>

    <p>Ils ont pour but de vous adresser des publicités personnalisées adaptées à vos attentes. Aucune données personnelles telles que vos nom, prénom, adresse postale ou électronique, etc. ne sera transmis à ces tiers partenaires dont l’intervention sur le site web se limite au dépôt de cookie par le biais des contenus publicitaires qu’ils gèrent.</p>

    <p>Le présent site Web et n’a aucun contrôle sur ces cookies.</p>

 

    <h4>Cookies de Statistiques Google Analytics</h4>

    <p>Ces cookies permettent d’établir des statistiques de fréquentation des sites <a href="https://mapicoin.fr">mapicoin.fr</a> et de détecter des problèmes de navigation afin de suivre et d’améliorer la qualité de nos services.</p>

    <p>Exercez vos choix selon le navigateur que vous utilisez.</p>

    <p>Vous pouvez à tout moment paramétrer votre navigateur afin d’exprimer et de modifier vos souhaits en matière de cookies et notamment concernant les cookies de statistique. Vous pouvez exprimer vos choix en paramétrant votre navigateur de façon à refuser certains cookies.</p>

    <p>Si vous refusez nos cookies et ceux de nos partenaires, votre des sites <a href="https://mapicoin.fr">mapicoin.fr</a> ne sera plus comptabilisée dans Google Analytics et vous ne pourrez plus bénéficier d’un certain nombre de fonctionnalités qui sont néanmoins nécessaires pour naviguer dans certaines de nos pages.</p>
    <p>Nous vous informons que vous pouvez toutefois vous opposer à l’enregistrement de cookies en suivant le mode opératoire disponible ci-dessous :</p>

    <p>Sur Internet Explorer</p>
    <ul>
        <li>1. Allez dans Outils > Options Internet.</li>
        <li>2. Cliquez sur l’onglet confidentialité.</li>
        <li>3. Cliquez sur le bouton avancé, cochez la case  » Ignorer la gestion automatique des cookies ».</li>
    </ul>

    <p>Sur Firefox</p>
    <ul>
        <li>1. En haut de la fenêtre de Firefox, cliquez sur le bouton Firefox (menu Outils sous Windows XP), puis sélectionnez Options.</li>
        <li>2. Sélectionnez le panneau Vie privée.</li>
        <li>3. Paramétrez Règles de conservation : à utiliser les paramètres personnalisés pour l’historique.</li>
        <li>4. Décochez Accepter les cookies.</li>
    </ul>

    <p>Sur Chrome</p>
    <ul>
        <li>1. Cliquez sur l’icône représentant une clé à molette qui est située dans la barre d’outils du navigateur.</li>
        <li>2. Sélectionnez Paramètres.</li>
        <li>3. Cliquez sur Afficher les paramètres avancés.</li>
        <li>4. Dans la section « Confidentialité », cliquez sur le bouton Paramètres de contenu.</li>
        <li>5. Dans la section « Cookies », vous pouvez bloquer les cookies et données de sites tiers</li>
    </ul>

    <p>Sur Safari<p>
    <ul>
        <li>1. Allez dans Réglages > Préférences
        <li>2. Cliquez sur l’onglet Confidentialité
        <li>3. Dans la zone  » Bloquer les cookies « , cochez la case « toujours »
    </ul>

    <p>Sur Opéra</p>
    <ul>
        <li>1. Allez dans Réglages > Préférences</li>
        <li>2. Cliquez sur l’onglet avancées</li>
        <li>3. Dans la zone  » Cookies « , cochez la case  » Ne jamais accepter les cookies »</li>
    </ul>

 

    <h4>Les cookies de partage des réseaux sociaux</h4>

    <p>Sur certaines pages de <a href="https://mapicoin.fr">mapicoin.fr</a> figurent des boutons ou modules de réseaux sociaux tiers qui vous permettent d’exploiter les fonctionnalités de ces réseaux et en particulier de partager des contenus présents sur jeanviet.info et blogbuster.fr avec d’autres personnes.</p>
    <p>Lorsque vous vous rendez sur une page internet sur laquelle se trouve un de ces boutons ou modules, votre navigateur peut envoyer des informations au réseau social qui peut alors associer cette visualisation à votre profil.</p>

    <p>Des cookies des réseaux sociaux, dont nous n’avons pas la maîtrise, peuvent être alors être déposés dans votre navigateur par ces réseaux. Nous vous invitons à consulter les politiques de confidentialité propres à chacun de ces sites de réseaux sociaux, afin de prendre connaissance des finalités d’utilisation des informations de navigation que peuvent recueillir les réseaux sociaux grâce à ces boutons et modules.</p>


</div><!-- /.page-content -->

            </div><!-- /.page-body -->

            <div class="page-footer">
                © <?php echo date('Y'); ?><span class="hidden-xs"> - Mapicoin est un site indépendant du site Leboncoin.fr</span>
                <div class="right">
                    <a href="https://www.facebook.com/mapicoin/">Facebook</a>
                    |
                    <a href="https://twitter.com/mapicoin">@Twitter</a>
                </div>
            </div>

        </div><!-- /.page-container -->

        <?php include 'inc/ga.inc.php' ?>

    </body>
</html>
