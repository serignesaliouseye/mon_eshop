// assets/js/toggle-password.js
// Afficher / masquer le mot de passe avec icône SVG

function togglePassword(inputId, btn) {
    const input  = document.getElementById(inputId);
    const isHide = input.type === 'password';

    // Basculer le type
    input.type = isHide ? 'text' : 'password';

    // Icône œil ouvert
    const oeilOuvert = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke="currentColor" stroke-width="2" style="width:20px;height:20px">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0
                     8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542
                     7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>`;

    // Icône œil barré
    const oeilBarre = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
             stroke="currentColor" stroke-width="2" style="width:20px;height:20px">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0
                     -8.268-2.943-9.542-7a9.97 9.97 0 012.63-4.36
                     M9.88 9.88A3 3 0 0114.12 14.12M3 3l18 18
                     M6.53 6.53A9.97 9.97 0 012.458 12C3.732 16.057
                     7.523 19 12 19c1.636 0 3.176-.393 4.53-1.09"/>
        </svg>`;

    btn.innerHTML = isHide ? oeilBarre : oeilOuvert;

    // Remettre le focus sur le champ
    input.focus();
}