$(document).ready(function(){
    let optionFormat = function(item) {
        if ( !item.id ) {
            return item.text;
        }

        return $(`<span>
                    <img src="${item.element.getAttribute('data-url')}" class="rounded-circle h-20px me-2" alt="${item.text}"/>
                    ${item.text}
                </span>`)
    }

    $('#input-flag').select2({
        templateSelection: optionFormat,
        templateResult: optionFormat
    });
})
