/*
 * Couche de services HTTP (worker).
 *
 */

var BASE_URL = "http://127.0.0.1:8080/exercices/exercice10/serveur/";

/**
 * Fonction permettant de demander la liste des pays au serveur.
 * @param {type} Fonction de callback lors du retour avec succès de l'appel.
 * @param {type} Fonction de callback en cas d'erreur.
 */
function chargerPays(successCallback, errorCallback) {
  $.ajax({
    type: "GET",
    dataType: "xml",
    url: BASE_URL + "paysManager.php",
    success: successCallback,
    error: errorCallback
  });
}

/**
 * Fonction permettant de demander la liste des skieurs au serveur.
 * @param {type} paysId. Id du pays dans lequel chercher les skieurs.
 * @param {type} Fonction de callback lors du retour avec succès de l'appel.
 * @param {type} Fonction de callback en cas d'erreur.
 */
function chargerSkieurs(paysId, successCallback, errorCallback) {
  $.ajax({
    type: "GET",
    dataType: "xml",
    url: BASE_URL + "skieurManager.php",
    data:'paysId=' + paysId,
    success: successCallback,
    error: errorCallback
  });
}

