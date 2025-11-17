document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.filter-title').forEach(title => {
        title.addEventListener('click', () => {
            let options = title.nextElementSibling;
            options.classList.toggle('open');
        });
    });
});
