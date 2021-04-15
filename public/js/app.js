function afficherLieuForm()
{
        $('#sortie_form_lieuForm').show();
        $('#sortie_form_lieuForm_nom').attr("required", "true");
        $('#sortie_form_lieuForm_rue').attr("required", "true");
        $('#sortie_form_lieuForm_ville').attr("required", "true");
        /*let formLieu = document.getElementById("sortie_form_lieuForm").childNodes
        for (var i = 0;i<formLieu.length;i++) {
                formLieu[i].lastChild.style.display = block;
        }*/
        // clear le champ Lieu et les champs inhérents
/*
        $('#formLieu').disabled = false;
*/

}

function cacherLieuForm() {
        $('#sortie_form_lieuForm').hide();
        $('#sortie_form_lieuForm_nom').attr("required", "false");
        $('#sortie_form_lieuForm_rue').attr("required", "false");
        $('#sortie_form_lieuForm_ville').attr("required", "false");
}

function onLieuSelected(id) {
        let lieuID = $('#sortie_form_lieu').val()
        // console.log("onlieuselected",id, lieuID) // s'affiche mais le reste ne fonctionne pas
        // $.ajax({
        //         method: "GET",
        //         url: "/sortir/public/sortie/create",
        //         data: {id: id},
        //         success: function (result) {
        //                 console.log("result ", result)
        //         }
        //     }
        //
        // )

}
$(document).on('change', '#sortie_form_lieu', function () {
        let $field = $(this);
        let $lieuField = $('#sortie_form_lieu');
        let $form = $field.closest('sortie_form_lieuForm');
        let data = {}
        data[$lieuField.attr('name')] = $lieuField.val()
        data[$field.attr('name')] = $field.val()
        console.log(data)
        $.post($form.attr('action'), data).then(function (data) {
                console.log(data)
        })

})



/*
//Fonction pour modifier dynamiquement le formulaire de création de sorties
$(document).on('change','#sortie_form_ville', function () {
    let $field = $(this);
    let $form = $field.closest('form');
    let $villeField = $('#sortie_form_ville');
    let data = {};
    data[$field.attr('name')] = $field.val();
    console.log(data);
    console.log($villeField);
    //va chercher data.lieux :
    $.post($form.attr('action'), data).then(function (data) {
        console.log('post :', data);
       let $input = $(data).find('#sortie_form_lieu');
        console.log($input);
        $('#sortie_form_lieu').replaceWith($input);
    })
})*/
// display: none et s'affiche quand on clique sur le bouton
