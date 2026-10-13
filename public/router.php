<?php
// routeur pour le serveur PHP integre : sert les fichiers statiques
// et renvoie le reste vers le front controller
if ($_SERVER['REQUEST_URI'] !== '/' && is_file(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}
require __DIR__ . '/index.php';
