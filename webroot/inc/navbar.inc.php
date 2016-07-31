<nav id="navbar" class="navbar">
  <h1 class="dib">
    <a class="logo" href="/">
      <span hide>mapi</span><span>coin</span>
    </a>
  </h1>

    <form id="form-search" action="#" class="form-search dib" role="search">
      <input
            id="input-url"
            class="input-url input-lg"
            name="u"
            value=""
            size="45"
            type="text"
            placeholder="Copiez/collez ici votre URL de recherche leboncoin ..."
            />
      <button
          id="input-submit"
          class="input-submit btn-lg btn btn-warning hand"
          type="submit"
          data-text="Afficher les résultats"
          data-loading-text="<span class='hide-mobile'>Chargement... </span><i class='glyphicon glyphicon-refresh glyphicon-spin'></i>">
          <span class="hide-mobile">Afficher les résultats</span>
          <i class="hide-desktop glyphicon glyphicon-search" aria-hidden="true"></i>
      </button>
    </form>
    <div class="navbar-extra pull-right">
      Ma localisation :
      <span
        id="geolocalize-info"
        data-default="<i>inconnue</i>"
        data-loader="<i class='glyphicon glyphicon-refresh glyphicon-spin'></i>">
        inconnue
      </span>
      <button
        id="geolocalize-me"
        class="btn btn-default"
        title="Localisez vous automatiquement (nécessite la permission de votre navigateur)"
        onclick="get_user_location()">
        <i class="glyphicon glyphicon-map-marker" aria-hidden="true"></i>
      </button>
    </div>
</nav>