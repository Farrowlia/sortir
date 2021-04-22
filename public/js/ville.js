function modifierVille(id, xnom, xcodePostal) {

    document.getElementById("afficherNomVille" + id).innerHTML="<input name='nom' type='text' placeholder='" + xnom + "'>";
    document.getElementById("afficherCPville" + id).innerHTML="<input name='codePostal' type='text' placeholder='" + xcodePostal + "'>" +
                                                                        "<input name='id' type='hidden' value=" + id + ">";
    document.getElementById("modifier" + id).innerHTML="<input class='btn btn-outline-secondary' type='button' value='Enregistrer'>";
    document.getElementById("delete" + id).innerHTML="<input class='btn btn-outline-secondary' type='button' value='Supprimer'>";

    requeteAjaxFormulaire('#formVille' + id, '#formVille' + id + ' input[type="button"]', "click", '#formVille' + id, true);
}

// <input className='btn btn-outline-secondary' type='button' value='Supprimer'>

// <a className='btn btn-outline-secondary' id='annuler'>Annuler</a>
// href='{{ path ('villes') }}'