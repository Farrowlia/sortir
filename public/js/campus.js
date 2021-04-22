function modifierCampus(id, nom) {
    document.getElementById("afficherNomCampus" + id).innerHTML="<input name='nom' type='text' placeholder='Campus...' value='" + nom + "'>" +
                                                                            "<input name='id' type='hidden' value=" + id + ">";
    document.getElementById("modifier" + id).innerHTML="<input class='btn btn-outline-secondary' type='button' value='Enregistrer'>";

    requeteAjaxFormulaire('#formCampus' + id, '#formCampus' + id + ' input[type="button"]', "click", '#formCampus' + id, true);
}