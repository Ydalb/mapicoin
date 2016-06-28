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
      <h1>
        <a class="navbar-brand" href="/">
          <i class="glyphicon glyphicon-map-marker" aria-hidden="true"></i>
          <span>mapi</span><span>coin</span>
        </a>
      </h1>
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
                type="url"
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

      <p id="ads-count" class="navbar-text"></p>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>