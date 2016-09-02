<?php
$_default_distance = $_GET['distance'] ?? false;
$_default_day      = $_GET['day'] ?? false;
$_default_sort     = $_GET['sort'] ?? false;
$_select_distance = [
    "10"  =>"10 km",
    "20"  =>"20 km",
    "30"  =>"30 km",
    "50"  =>"50 km",
    "100" =>"100 km",
    "200" =>"200 km",
    "500" =>"500 km",
];
$_select_day = [
    "1"  => "1 jour",
    "3"  => "3 jours",
    "5"  => "5 jours",
    "7"  => "1 semaine",
    "14" => "2 semaines",
    "30" => "1 mois",
    "60" => "2 mois",
];
$_select_sort = [
    "price" => 'Prix &#8599;',
    "date"  => 'Date &#8600;',
];
?>
<span id="toggle"></span>
<div id="sidebar-wrapper">
    <div class="sidebar-header">
        <h2 class="sidebar-title"></h2>
        <!-- Filtre de recherche -->
        <div id="sidebar-advanced-search">
            <!-- Distance -->
            <div
                class="filter-item filter-distance disabled"
                title="Vous devez autoriser la localisation GPS du navigateur pour accéder à ce filtre">
                <!-- <label for="filter-distance" class="filter-text">Dans un rayon de</label> -->
                <div class="filter-select-wrapper">
                    <select id="filter-distance" name="radius" id="radius" class="filter-select" disabled="disabled">
                        <option value="">Dans un rayon de...</option>
                    <?php foreach ($_select_distance as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $_default_distance == $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach;?>
                    </select>
                    <i class="glyphicon glyphicon-chevron-down" aria-hidden="true"></i>
                </div>
            </div>
            <!--  Ancienneté -->
            <div class="filter-item filter-day">
                <!-- <label for="filter-day" class="filter-text"></label> -->
                <div class="filter-select-wrapper">
                    <select id="filter-day" name="radius" id="radius" class="filter-select">
                        <option value="">Plus récente de...</option>
                    <?php foreach ($_select_day as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $_default_day == $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach;?>
                    </select>
                    <i class="glyphicon glyphicon-chevron-down" aria-hidden="true"></i>
                </div>
            </div>
            <!--  Tri -->
            <div class="filter-item filter-sort">
                <!-- <label for="filter-sort" class="filter-text">Trier par</label> -->
                <div class="filter-select-wrapper">
                    <select id="filter-sort" name="sort" id="sort" class="filter-select">
                        <option value="">Trier par...</option>
                    <?php foreach ($_select_sort as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $_default_sort == $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach;?>
                    </select>
                    <i class="glyphicon glyphicon-chevron-down" aria-hidden="true"></i>
                </div>
            </div>
        </div>
        <p class="sidebar-details">
        <!--
            <a target="_blank" class="sidebar-edit-search" href="">Changer ma recherche</a>
        -->
            <span class="sidebar-count"></span>
            <span class="sep">|</span>
            <span class="sidebar-average-price"></span>
        </p>
    </div>
    <ul id="sidebar" class="sidebar"></ul>
</div>