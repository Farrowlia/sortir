function modifierCampus(id) {
    document.getElementById("afficherNomCampus" + id).innerHTML="<input name='nom' type='text' placeholder='Campus...'>" +
                                                                            "<input name='id' type='hidden' value=" + id + ">";
    document.getElementById("modifier" + id).innerHTML="<input type='button' value='Enregistrer'>";

    requeteAjaxFormulaire('#formCampus' + id, '#formCampus' + id + ' input[type="button"]', "click", '#formCampus' + id, true);
}