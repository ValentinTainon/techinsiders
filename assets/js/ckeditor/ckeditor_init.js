import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { SimpleUploadAdapter } from '@ckeditor/ckeditor5-upload';

if (document.querySelector( '#editor' )) {
    ClassicEditor
        .create(document.querySelector('#editor'), {
            plugins: [ SimpleUploadAdapter ],
            simpleUpload: {
                // The URL that the images are uploaded to.
                uploadUrl: 'public/uploads/images',
    
                // Enable the XMLHttpRequest.withCredentials property.
                withCredentials: true,
    
                // Headers sent along with the XMLHttpRequest to the upload server.
                headers: {
                    'X-CSRF-TOKEN': 'CSRF-Token',
                    Authorization: 'Bearer <JSON Web Token>'
                }
            }
        })
        .then(editor => {
            console.log(editor);
        })
        .catch(error => {
            console.error(error);
        });
}