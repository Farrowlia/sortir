const BASE_URL = 'http://localhost/sortir/public/';

/**
 * Alimente en lieux le select des lieux quand on clique sur le select des villes
 * @param url
 * @param params
 * @param selectorReponse
 */
function ajaxGet(url, params, selectorReponse) {

    const Url = new URL(BASE_URL + url);

    fetch(Url.pathname + "?" + params.toString(), {
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    }).then(response =>
        response.json()
    ).then(data => {

        const contentPage = document.querySelector(selectorReponse);
        contentPage.innerHTML = data.content;

    }).catch(e => alert(e));

}

/**
 * Envoi du formulaire en Ajax
 * @param url
 * @param params
 * @param callback
 */
function ajaxPost(url, params, callback) {

    const Url = new URL(BASE_URL + url);

    fetch(Url.pathname, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(params)
    }).then(response =>
        response.json()
    ).then(data => {
        callback(data);

    }).catch(e => alert(e));

}

/**
 * Génère une requête Ajax
 * @param {string} selectorFormulaire Sur quel formulaire voulez-vous activer la requête Ajax.
 * @param {string} selectorElementAction Sur quel élément HTML souhaitez-vous placer le déclenchement de la requête Ajax.
 * @param {string} typeAction Type d'action mis en place sur le selectorElementAction (exemple : "click", "change", etc...).
 * @param {string} selectorReponse Dans quel élément HTML souhaitez-vous que le bloc réponse s'affiche.
 * @param {boolean} modificationUrl (facultatif) Affiche la requête dans l'Url. Par default à false.
 */
function requeteAjaxFormulaire(selectorFormulaire, selectorElementAction, typeAction, selectorReponse, modificationUrl= false) {

    document.querySelector(selectorElementAction).addEventListener(typeAction, (event) => {

        event.preventDefault();

        const Form = new FormData(document.querySelector(selectorFormulaire));
        const Params = new URLSearchParams();

        Form.forEach((value, key) => {
            Params.append(key, value);
        });

        const Url = new URL(window.location.href);

        fetch(Url.pathname + "?" + Params.toString() + "&ajax=1", {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        }).then(response =>
            response.json()
        ).then(data => {

            const contentPage = document.querySelector(selectorReponse);
            contentPage.innerHTML = data.content;

            if (modificationUrl) {
                history.pushState({}, null, Url.pathname + "?" + Params.toString());
            }
            else {
                history.pushState({}, null, Url.pathname);
            }

        }).catch(e => alert(e));

    });

}


/**
 * Génère une requête Ajax
 * @param {string} selectorElementAction Sur quel élément HTML souhaitez-vous placer le déclenchement de la requête Ajax.
 * @param {string} typeAction Type d'action mis en place sur le selectorElementAction (exemple : "click", "change", etc...).
 * @param {string} selectorReponse Dans quel élément HTML souhaitez-vous que le bloc réponse s'affiche.
 * @param {boolean} modificationUrl (facultatif) Affiche la requête dans l'Url. Par default à false.
 */
function requeteAjaxGet(selectorElementAction, typeAction, selectorReponse, modificationUrl= false) {

    document.querySelector(selectorElementAction).addEventListener(typeAction, (event) => {

        event.preventDefault();

        const Params = new URLSearchParams();
        Params.append(document.querySelector(selectorElementAction).name, document.querySelector(selectorElementAction).value);

        const Url = new URL(window.location.href);

        fetch(Url.pathname + "?" + Params.toString() + "&ajax=1", {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        }).then(response =>
            response.json()
        ).then(data => {

            const contentPage = document.querySelector(selectorReponse);
            contentPage.innerHTML = data.content;

            if (modificationUrl) {
                history.pushState({}, null, Url.pathname + "?" + Params.toString());
            }
            else {
                history.pushState({}, null, Url.pathname);
            }

        }).catch(e => alert(e));

    });

}


