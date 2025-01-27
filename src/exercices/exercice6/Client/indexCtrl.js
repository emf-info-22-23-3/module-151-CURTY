var BASE_URL = "http://localhost:8080/exercices/exercice6/server/server.php?action=getTeams";

function onload(){
    chargerTeam(chargerTeamSuccess, chargerTeamError);
}
function chargerTeamSuccess(data, text, jqXHR) {
	var tblContent = $("#teams");
    var txt = '';
    equipes = JSON.parse(data)
    for (let i = 0; i < equipes.length; i++) {
        const element = equipes[i];
        txt += "<tr><td>" + i + "</td><td>" + element + "</td></tr>";
    }
    $(txt).appendTo(tblContent);
}

/**
 * Méthode appelée en cas d'erreur lors de la lecture du webservice
 * @param {type} data
 * @param {type} text
 * @param {type} jqXHR
 */
function chargerTeamError(request, status, error) {
	// appelé s'il y a une erreur lors du retour
    alert("erreur : " + error + ", request: " + request + ", status: 1" + status);
}
/**
 * Fonction permettant de charger les données d'équipe.
 * @param {type} Fonction de callback lors du retour avec succès de l'appel.
 * @param {type} Fonction de callback en cas d'erreur.
 */
function chargerTeam(successCallback, errorCallback) {
    $.ajax({
    type: "GET",
    url: BASE_URL,
    success: successCallback,
    error: errorCallback
    });
}