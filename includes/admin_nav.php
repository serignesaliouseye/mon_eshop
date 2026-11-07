<?php
// includes/admin_nav.php – icônes SVG et menu latéral partagés par toutes les pages admin

if (!function_exists('icone')) {
    function icone(string $nom, int $taille = 20): string {
        $chemins = [
            'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
            'plat'      => '<path d="M3 11h18a9 9 0 0 1-18 0z"/><path d="M8 7c0-1.5 1-1.5 1-3M12 7c0-1.5 1-1.5 1-3M16 7c0-1.5 1-1.5 1-3"/>',
            'commande'  => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.27 6.96 12 12.01l8.73-5.05M12 22.08V12"/>',
            'clients'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'argent'    => '<path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
            'site'      => '<path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
            'check'     => '<path d="M20 6 9 17l-5-5"/>',
            'x'         => '<path d="M18 6 6 18M6 6l12 12"/>',
            'edit'      => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/>',
            'trash'     => '<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
            'retour'    => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        ];
        return '<svg viewBox="0 0 24 24" width="' . $taille . '" height="' . $taille . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
             . ($chemins[$nom] ?? '') . '</svg>';
    }
}

if (!function_exists('sidebar_admin')) {
    function sidebar_admin(string $actif): void {
        $liens = [
            'dashboard' => ['dashboard.php', 'Tableau de bord', 'dashboard'],
            'produits'  => ['produits.php',  'Produits',        'plat'],
            'commandes' => ['commandes.php', 'Commandes',       'commande'],
        ];
        echo '<aside class="admin-sidebar"><h2>Administration</h2><nav aria-label="Navigation admin">';
        foreach ($liens as $cle => [$href, $libelle, $ico]) {
            $courant = $cle === $actif ? ' class="active" aria-current="page"' : '';
            echo '<a href="' . $href . '"' . $courant . '>' . icone($ico) . ' ' . $libelle . '</a>';
        }
        echo '</nav><a href="../index.php" class="admin-sidebar-foot">' . icone('site') . ' Voir le site</a></aside>';
    }
}