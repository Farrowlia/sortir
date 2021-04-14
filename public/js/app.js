$(document).on('change','#sortie_form_ville', function () {
    let $field = $(this)
    let $form = $field.closest('form')
    let data = {}
    data[$field.attr('name')] = $field.val()
    console.log(data)
    //va chercher data.lieux :
/*    $.post($form.attr('action'), data).then(function (data) {
        debugger
    })*/
})
