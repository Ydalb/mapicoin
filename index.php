<html>
    <head>
        <title>Lebonocoin2Map</title>
        <link href="leboncoin.css" rel="stylesheet">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
        <script src="//maps.googleapis.com/maps/api/js?key=AIzaSyA797A1ZQxzPqs2oaWLeaFvvGEySX9EVCw"></script>
        <script src="leboncoin.js"></script>
    </head>
    <body>

        <!-- Header -->
        <header>
            <div class="text-vertical-center">
                <h1><span>mapi</span>coin</h1>
                <h3>Prévisualisez les annonces de votre recherche leboncoin.fr sur une carte</h3>
                <br />
                <form id="form-search" action="#">
                    <input
                        id="input-url"
                        class="big-input"
                        name="u"
                        value="https://www.leboncoin.fr/voitures/offres/bretagne/bonnes_affaires/?o=1&amp;pe=9"
                        placeholder="Copiez/collez votre lien de recherche..." />
                    <br />
                    <input
                        id="input-submit"
                        class="submit"
                        type="submit"
                        data-value="Et hop !"
                        value="Et hop !" />
                </form>
            </div>
        </header>
        
        <section id="map" style="display:none;">

        </section>

    </body>
</html>