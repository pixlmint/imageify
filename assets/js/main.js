document.addEventListener('DOMContentLoaded', function () {
    var createGalleryForm = document.getElementById('child-gallery-form')
    var showCreateGalleryFormButton = document.getElementById('child-gallery-button');

    showCreateGalleryFormButton.addEventListener('click', function () {
        triggerGalleryForm(true)
    });

    document.getElementById('cancel-create-gallery').addEventListener('click', function() {
        triggerGalleryForm(false)
    });

    var triggerGalleryForm = function (showForm) {
        if (showForm) {
            createGalleryForm.classList.remove('hidden');
            showCreateGalleryFormButton.classList.add('hidden');
        } else {
            createGalleryForm.classList.add('hidden');
            showCreateGalleryFormButton.classList.remove('hidden');
        }
    }
});