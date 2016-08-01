<nav id="navbar" class="navbar">
  <h1 class="dib">
    <a class="logo" href="/">
      <div class="hide-desktop dib">
        <i class="glyphicon glyphicon-map-marker" aria-hidden="true"></i>
      </div>
      <div class="hide-mobile dib"><span>mapi</span><span>coin</span></div>
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
          <span class="hide-tablet">Afficher les résultats</span>
          <i class="hide-desktop glyphicon glyphicon-search" aria-hidden="true"></i>
      </button>
    </form>
    <div class="navbar-extra pull-right">
      <div class="hide-tablet dib">
        Ma localisation :
        <span
          id="geolocalize-info"
          data-default="<i>inconnue</i>"
          data-loader="<i class='glyphicon glyphicon-refresh glyphicon-spin'></i>">
          inconnue
        </span>
      </div>
      <button
        id="geolocalize-me"
        class="btn btn-default"
        title="Localisez vous automatiquement (nécessite la permission de votre navigateur)"
        data-default="<i class='glyphicon glyphicon-map-marker'></i>"
        data-loader="<i class='glyphicon glyphicon-refresh glyphicon-spin'></i>"
        onclick="get_user_location()">
        <i class="glyphicon glyphicon-map-marker" aria-hidden="true"></i>
      </button>
    </div>
</nav>