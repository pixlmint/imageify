document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('child-gallery-button').addEventListener('click', function () {
        this.classList.add('hidden');
        document.getElementById('child-gallery-form').classList.remove('hidden');
    });
});