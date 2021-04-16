
window.onload = () => {
    const formAjax = document.querySelector("#formAjax");

    // On boucle sur les buttons
    document.querySelectorAll("#formAjax input").forEach(input => {
        input.addEventListener("click", () => {

            // On récupère les données du formulaire
            const Form = new FormData(formAjax);

            // On fabrique la "queryString"
            const Params = new URLSearchParams();

            Form.forEach((value, key) => {
                Params.append(key, value);
            });

            // On récupère l'url active
            const Url = new URL(window.location.href);

            // On lance la requête ajax
            fetch(Url.pathname + "?" + Params.toString() + "&ajax=1", {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            }).then(response =>
                response.json()
            ).then(data => {
                // On va chercher la zone de contenu
                const content = document.querySelector("#contentCommentaire");

                // On remplace le contenu
                content.innerHTML = data.content;

                // On met à jour l'url
                // history.pushState({}, null, Url.pathname + "?" + Params.toString());
            }).catch(e => alert(e));

        });
    });
}
