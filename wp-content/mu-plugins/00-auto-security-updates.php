<?php
/**
 * Plugin Name: Auto-aktualizacje bezpieczenstwa (wymuszone mimo repo git)
 * Description: WordPress sam wylacza automatyczne aktualizacje, gdy w katalogu wykryje repozytorium
 *              wersjonowania (.git/.svn/.hg/.bzr) - patrz WP_Automatic_Updater::is_vcs_checkout().
 *              Ten plik zdejmuje tamta blokade i wymusza aktualizacje mniejsze, czyli te z latkami
 *              bezpieczenstwa. Aktualizacji WIEKSZYCH celowo NIE wlacza - te zostaja decyzja czlowieka.
 * Author: wellmade.online
 * Version: 1.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Repozytorium git w katalogu nie moze blokowac latek bezpieczenstwa.
add_filter( 'automatic_updates_is_vcs_checkout', '__return_false', 999 );

// Aktualizacje mniejsze (7.0.1 -> 7.0.2) wlaczone na sztywno.
add_filter( 'allow_minor_auto_core_updates', '__return_true', 999 );

// Gdyby ktos w przeszlosci wylaczyl aktualizator stala albo filtrem - odblokuj.
add_filter( 'automatic_updater_disabled', '__return_false', 999 );

// Aktualizacje WIEKSZE zostaja wylaczone swiadomie (ryzyko dla motywu i wtyczek).
add_filter( 'allow_major_auto_core_updates', '__return_false', 999 );
