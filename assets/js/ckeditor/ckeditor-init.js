CKSource.Editor
    .create(document.querySelector('#editor'), {
        language: "{{ attr['data-locale'] }}",
    })
    .then( editor => {
        console.log('Editor was initialized', editor);

        const submitButtons = document.querySelectorAll('button[type="submit"]');
        submitButtons.forEach(button => {
            button.addEventListener('click', (event) => {
                checkDataLimitEvent(editor, event);
            })
        })
    })
    .catch(error => {
        console.error(error);
    });

function checkDataLimitEvent(editor, event) {
    const ckMainInput = document.querySelector('.ck-editor__main');
    const hasErrorClass = ckMainInput.classList.contains('ck-has-error');
    let ckErrorDiv = document.querySelector('.invalid-feedback.d-block');
    const ckDataLength = editor.getData().length;
    const dataMinLength = 500;

    function addError() {
        if (!hasErrorClass) {
            ckMainInput.classList.add('ck-has-error');
            const ckFormWidget = document.querySelector('.field-textarea .form-widget');
            ckErrorDiv = document.createElement("div");
            ckErrorDiv.classList.add('invalid-feedback', 'd-block');
            ckErrorDiv.textContent = `Le contenu de l'article est trop court, il doit contenir au moins ${dataMinLength} caractères.`;
            ckFormWidget.appendChild(ckErrorDiv);
        }
    }

    function removeError() {
        if (hasErrorClass) {
            ckMainInput.classList.remove('ck-has-error');
            ckErrorDiv.remove();
        }
    }

    if (ckDataLength >= dataMinLength) {
        removeError();
    } else {
        addError();
        event.preventDefault();
    }
}