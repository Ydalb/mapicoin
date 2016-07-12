<?php
$_default_distance = $_GET['distance'] ?? false;
$_default_age      = $_GET['age'] ?? false;
$_select_distance = [
    "10"  =>"10 km",
    "20"  =>"20 km",
    "30"  =>"30 km",
    "50"  =>"50 km",
    "100" =>"100 km",
    "200" =>"200 km",
];
$_select_age = [
    "1"  => "1 jour",
    "3"  => "3 jours",
    "5"  => "5 jours",
    "7"  => "1 semaine",
    "14" => "2 semaines",
    "30" => "1 mois",
];
?>
<div id="sidebar-wrapper">
    <div class="sidebar-header">
        <h2 class="sidebar-title"></h2>
        <!-- Filtre de recherche -->
        <div id="sidebar-advanced-search">
            <!-- Ancienneté -->
            <div class="filter-item filter-distance">
                <span class="filter-text">Dans un rayon de</span>
                <div class="filter-select-wrapper">
                    <select id="filter-distance" name="radius" id="radius" class="filter-select">
                        <option value="">...</option>
                    <?php foreach ($_select_distance as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $_default_distance == $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach;?>
                    </select>
                    <i class="glyphicon glyphicon-chevron-down" aria-hidden="true"></i>
                </div>
            </div>
            <!-- Ancienneté -->
            <div class="filter-item filter-age">
                <span class="filter-text">Plus récente de</span>
                <div class="filter-select-wrapper">
                    <select id="filter-age" name="radius" id="radius" class="filter-select">
                        <option value="">...</option>
                    <?php foreach ($_select_age as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $_default_age == $k ? 'selected' : '' ?>><?= $v ?></option>
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
        </p>
    </div>
    <ul id="sidebar" class="sidebar"></ul>
</div>