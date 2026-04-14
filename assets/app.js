document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        element.addEventListener('click', function (event) {
            if (!window.confirm(element.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('form[data-password-check]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var passwordField = form.querySelector('input[name="password"]');
            if (!passwordField || passwordField.value === '') {
                return;
            }
            var regex = /^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;
            if (!regex.test(passwordField.value)) {
                event.preventDefault();
                window.alert('Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.');
            }
        });
    });

    document.querySelectorAll('[data-table-filter]').forEach(function (input) {
        input.addEventListener('input', function () {
            var target = document.getElementById(input.getAttribute('data-table-filter'));
            if (!target) {
                return;
            }
            var value = input.value.toLowerCase().trim();
            target.querySelectorAll('tbody tr').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().indexOf(value) !== -1 ? '' : 'none';
            });
        });
    });
});
