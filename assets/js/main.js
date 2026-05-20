// assets/js/main.js – SenFood (version finale)

document.addEventListener('DOMContentLoaded', () => {

    // ── 1. Menu hamburger mobile ──
    const hamburger = document.getElementById('hamburger');
    const navMenu   = document.getElementById('nav-menu');

    if (hamburger && navMenu) {

        hamburger.addEventListener('click', (e) => {
            e.stopPropagation();
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('open');
        });

        // Fermer si on clique sur un lien du menu
        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('open');
            });
        });

        // Fermer si on clique en dehors du menu
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('open');
            }
        });
    }

    // ── 2. Animations au scroll (Intersection Observer) ──
    const elementsAOS = document.querySelectorAll('[data-aos]');
    if (elementsAOS.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });

        elementsAOS.forEach(el => observer.observe(el));
    }

    // ── 3. Validation formulaire inscription ──
    const formInscription = document.getElementById('form-inscription');
    if (formInscription) {
        formInscription.addEventListener('submit', (e) => {
            let valide = true;

            const nom      = document.getElementById('nom');
            const email    = document.getElementById('email');
            const password = document.getElementById('password');
            const confirm  = document.getElementById('confirm');

            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');

            if (nom && nom.value.trim().length < 2) {
                document.getElementById('err-nom').textContent = 'Le nom doit contenir au moins 2 caractères.';
                valide = false;
            }
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email.value.trim())) {
                document.getElementById('err-email').textContent = 'Adresse email invalide.';
                valide = false;
            }
            if (password && password.value.length < 8) {
                document.getElementById('err-password').textContent = 'Minimum 8 caractères.';
                valide = false;
            }
            if (password && confirm && password.value !== confirm.value) {
                document.getElementById('err-confirm').textContent = 'Les mots de passe ne correspondent pas.';
                valide = false;
            }

            if (!valide) e.preventDefault();
        });
    }

    // ── 4. Validation formulaire connexion ──
    const formConnexion = document.getElementById('form-connexion');
    if (formConnexion) {
        formConnexion.addEventListener('submit', (e) => {
            let valide = true;
            document.querySelectorAll('.form-error').forEach(el => el.textContent = '');

            const email    = document.getElementById('email');
            const password = document.getElementById('password');

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email && !emailRegex.test(email.value.trim())) {
                document.getElementById('err-email').textContent = 'Adresse email invalide.';
                valide = false;
            }
            if (password && password.value.length < 1) {
                document.getElementById('err-password').textContent = 'Mot de passe requis.';
                valide = false;
            }

            if (!valide) e.preventDefault();
        });
    }

    // ── 5. Modale de confirmation suppression ──
    window.confirmerSuppression = function(message, callback) {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal-box">
                <h3>Confirmer la suppression</h3>
                <p>${message}</p>
                <div class="modal-actions">
                    <button class="btn btn-outline" id="modal-annuler">Annuler</button>
                    <button class="btn btn-danger"  id="modal-confirmer">Supprimer</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        document.getElementById('modal-annuler').addEventListener('click', () => {
            document.body.removeChild(overlay);
        });
        document.getElementById('modal-confirmer').addEventListener('click', () => {
            document.body.removeChild(overlay);
            callback();
        });
    };

    // ── 6. Mise à jour dynamique du compteur panier ──
    window.updateCompteurPanier = function(nombre) {
        let badge = document.getElementById('compteur-panier');
        if (nombre > 0) {
            if (!badge) {
                const lienPanier = document.querySelector('a[href*="panier"]');
                if (lienPanier) {
                    badge = document.createElement('span');
                    badge.className = 'badge-panier';
                    badge.id = 'compteur-panier';
                    lienPanier.appendChild(badge);
                }
            }
            if (badge) badge.textContent = nombre;
        } else if (badge) {
            badge.remove();
        }
    };

});