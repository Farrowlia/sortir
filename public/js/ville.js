function modifierVille(id, nom, codePostal) {

    document.getElementById("afficherNomVille" + id).innerHTML="<input name='nom' type='text' placeholder='Ville ...' value='" + nom + "'>";
    document.getElementById("afficherCPville" + id).innerHTML="<input name='codePostal' type='text' placeholder='Code postal ...' value='" + codePostal + "'>" +
                                                                        "<input name='id' type='hidden' value=" + id + ">";
    document.getElementById("modifier" + id).innerHTML="<input class='btn btn-outline-secondary' type='button' value='Enregistrer'>";
    document.getElementById("delete" + id).innerHTML="<input class='btn btn-outline-secondary' type='button' value='Supprimer'>";

    requeteAjaxFormulaire('#formVille' + id, '#formVille' + id + ' input[type="button"]', "click", '#formVille' + id, true);
}
