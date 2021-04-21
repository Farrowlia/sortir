function modifierVille(id) {

    document.getElementById("afficherNomVille" + id).innerHTML="<input name='nom' type='text' placeholder='Ville ...'>";
    document.getElementById("afficherCPville" + id).innerHTML="<input name='codePostal' type='text' placeholder='Code postal ...'>" +
                                                                        "<input name='id' type='hidden' value=" + id + ">";
    document.getElementById("modifier" + id).innerHTML="<input class='btn btn-outline-secondary' type='button' value='Enregistrer'>";

    requeteAjaxFormulaire('#formVille' + id, '#formVille' + id + ' input[type="button"]', "click", '#formVille' + id, true);
}
