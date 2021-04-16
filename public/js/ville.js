
function modifierVille(id) {
    document.getElementById("afficherNomVille" + id).innerHTML="<input id=\"nomVille" + id + "\" type=\"text\" placeholder=\"Ville ...\">";
    document.getElementById("afficherCPville" + id).innerHTML="<input id=\"cpVille" + id + "\" type=\"text\" placeholder=\"Code postal ...\">" + "<input id=\"test" + id + "\" type=\"hidden\" value='jsmodif'>";
    document.getElementById("modifier" + id).innerHTML="<button onclick=\"enregistrerVille("+id+")\">Enregistrer</button>";
}

function enregistrerVille(id) {

    // console.log("clic ok sur enregistrer");
    // alert("la valeur du bouton afficherNomVille " + document.getElementById("nomVille" + id).value);

            // On récupère les données du formulaire
            const Form = new FormData(document.querySelector("#formVille" + id));

            // On fabrique la "queryString"
            const Params = new URLSearchParams();
            const Inputs = document.querySelectorAll("#formVille" + id + " input");

            Inputs.forEach((value, key) => {
                Params.append(key, value.value);
            });

            // On récupère l'url active
            const Url = new URL(window.location.href);
            alert(Url.pathname + "?" + Params.toString() + "&ajax=1");


            // On lance la requête ajax
            fetch(Url.pathname + "?" + Params.toString() + "&ajax=1", {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            }).then(response =>
                response.json()
            ).then(data => {
                // On va chercher la zone de contenu
                // const content = document.querySelector("#contentCommentaire");
                console.log("super ça marche !")

                // On remplace le contenu
                // content.innerHTML = data.content;

                // On met à jour l'url
                history.pushState({}, null, Url.pathname + "?" + Params.toString());
            }).catch(e => alert(e));

        }

