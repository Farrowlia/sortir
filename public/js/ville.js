
function modifierVille(id) {
    document.getElementById("afficherNomVille" + id).innerHTML="<input name='nom' id=\"nomVille" + id + "\" type=\"text\" placeholder=\"Ville ...\">";
    document.getElementById("afficherCPville" + id).innerHTML="<input name='codePostal' id=\"cpVille" + id + "\" type=\"text\" placeholder=\"Code postal ...\">" + "<input type='hidden' name='id' value=" + id + ">";
    document.getElementById("modifier" + id).innerHTML="<input value='Enregistrer' type='button' onclick='enregistrerVille(" + id + ")'>";
}

function enregistrerVille(id) {

    const nameId = "#formVille" + id;
    const formAjax = document.querySelector(nameId);

    // On boucle sur les buttons
    // document.querySelectorAll("#formVille1 button").forEach(button => {
    //     button.addEventListener("click", () => {

    const Form = new FormData(formAjax);
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

        const content = document.querySelector(nameId);
        content.innerHTML = data.content;

        // On met à jour l'url
        history.pushState({}, null, Url.pathname);
    }).catch(e => alert(e));

}
