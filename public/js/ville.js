// let mv = document.getElementById("modifier_ville");
// let mc = document.getElementById("modifier_codePostal");
// let av = document.getElementById("afficher_ville");
// let ac = document.getElementById("afficher_codePostal");
// let mod = document.getElementById("modifier_tout");
// let sv = document.getElementById("supprimer_ville");
//
// let btn = document.createElement("BUTTON");
//
// mod.addEventListener("click", ()=> {
//     if(getComputedStyle(mv, mc).display != "none"){
//         // mv.style.display = "block";
//         // mc.style.display = "block";
//         // sv.style.display = "none";
//         // mod.style.display = "none";
//         // av.style.display = "none";
//         // ac.style.display = "none";
//         afficher();
//         disparaitre();
//     }
//     document.body.appendChild(btn);
//     btn.innerHTML = "Valider";
//
// })
//
// function afficher() {
//
//     mv.style.display = "block";
//     mc.style.display = "block";
// }
//
// function disparaitre() {
//
//     sv.style.display = "none";
//     mod.style.display = "none";
//     av.style.display = "none";
//     ac.style.display = "none";
// }
//
// function ajouterChamps() {
//
// }
//
//
// jQuery('[id^=modifier]').click(function () {
//     $("#afficher_ville").append("<input type=\"text\" placeholder=\"Ville ...\">");
//     // $("#afficher_codePostal").remove();
//     // $("#afficher_codePostal").append("<input type=\"text\" placeholder=\"Code postal ...\">");
//     // $("#modifier_tout").remove();
//     // $("#supprimer_ville").remove();
//     // $("#validerModifVille").append("<button id='validerModifVille'>Valider</button>");
//     console.log("test");
// })

function modifierVille(id) {
    //
    // document.getElementById("afficherNomVille" + id).innerHTML="<input type=\"text\" placeholder=\"Ville ...\">";
    // document.getElementById("afficherCPville" + id).innerHTML="<input type=\"text\" placeholder=\"Code postal ...\">";
    // document.getElementById("modifier" + id).innerHTML="<button onclick=\"enregistrerVille({{ ville.id }})\">Enregistrer</button>";
    //
    // let parentElement = document.getElementById('supprimer' + id);
    //
    // // Obtient le premier enfant du parent
    // let theFirstChild = parentElement.firstChild
    //
    // // Crée un nouvel élément
    // let newElement = "</form>";
    //
    // // Insert le nouvel élément avant le premier enfant
    // parentElement.insertBefore(newElement, theFirstChild)
    //
    // // $("afficherNomVille" + id).remove();
    //
    // document.getElementById("afficherNomVille" + id).parentNode.innerHTML = "" +
    //     "<form method='post' id='formVille" + id + "'></form>";

    // document.getElementById("formVille" + id).innerHTML = "" +

    // document.getElementById("formVille" + id).innerHTML = "" +
    //     "<td id=\"afficherNomVille{{ ville.id }}\"><input type=\"text\" placeholder=\"Ville...\"></td>" +
    //     "<td id=\"afficherCPville{{ ville.id }}\"><input type=\"text\" placeholder=\"Code postal ...\"></td>" +
    //     "<td id=\"modifier{{ ville.id }}\"><button type='submit'>Enregistrer</button></td>" +
    //     "<td id=\"supprimer{{ ville.id }}\"><button>Supprimer</button></td>";

    document.getElementById("afficherNomVille" + id).innerHTML="<input type=\"text\" placeholder=\"Ville ...\">";
    document.getElementById("afficherCPville" + id).innerHTML="<input type=\"text\" placeholder=\"Code postal ...\">";
    document.getElementById("modifier" + id).innerHTML="<button onclick=\"enregistrerVille({{ ville.id }})\">Enregistrer</button>";
}
