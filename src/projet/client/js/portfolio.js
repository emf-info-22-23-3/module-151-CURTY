$(document).ready(function () {
  window.portfolioCtrl = new Portfolio();
});
/**
 * Contrôleur pour gérer le portfolio de l'utilisateur.
 * Permet à l'utilisateur de visualiser ses positions, acheter des actions et gérer les erreurs.
 */
class Portfolio {
  /**
   * Constructeur de la classe Portfolio.
   */
  constructor() {
    this.httpServ = new HttpService();
    this.httpServ.setErrorHandling((message) => this.displayError(message));
    this.checkUserState();
    this.portfolioStats = new PortfolioStats(this.httpServ);
    this.getPositions();
    this.setListener();
  }
  /**
   * Vérifie l'état de l'utilisateur (si l'utilisateur est authentifié).
   * Redirige l'utilisateur vers la page de connexion si l'utilisateur n'est pas authentifié.
   */
  checkUserState() {
    this.httpServ.getUserState((state) => {
      if (!state) {
        window.location.href = "../client/login.html";
      }
    });
  }
  /**
   * Configure les écouteurs d'événements pour les actions de l'utilisateur (connexion, achat d'actions, déconnexion).
   */
  setListener() {
    $("#logOut").click((e) => {
      e.preventDefault();
      this.httpServ.logOut(() => {
        window.location.href = "../client/login.html";
      });
    });
    $("#buyBtn").click((e) => {
      let symbol = $("#symbol").val();
      let avgBuyPrice = $("#avgBuyPrice").val();
      let quantity = $("#boughtAmount").val();
      let paramsValid = true;
      if (!(symbol.trim().length >= 1)) {
        $("#symbol").addClass("border-danger");
        paramsValid = false;
      }
      if (!isNaN(avgBuyPrice) && !(avgBuyPrice > 0)) {
        $("#avgBuyPrice").addClass("border-danger");
        paramsValid = false;
      }
      if (!isNaN(quantity) && !(quantity > 0)) {
        $("#boughtAmount").addClass("border-danger");
        paramsValid = false;
      }
      if (paramsValid) {
        $("#symbol").removeClass("border-danger");
        $("#avgBuyPrice").removeClass("border-danger");
        $("#boughtAmount").removeClass("border-danger");
        this.httpServ.buyStock(symbol, avgBuyPrice, quantity, (positions) => {
          $("#symbol").val("");
          $("#avgBuyPrice").val("");
          $("#boughtAmount").val("");
          this.portfolioStats.refreshStats(positions);
        });
      }
    });
  }
  /**
   * Récupère les positions actuelles de l'utilisateur et demande de les afficher.
   */
  getPositions() {
    this.httpServ.getUserPositions((positions) =>
      this.portfolioStats.refreshStats(positions)
    );
  }
  /**
   * Affiche un message d'erreur dans l'interface utilisateur.
   * @param {string} message - Le message d'erreur à afficher.
   */
  displayError(message) {
    $("#error-message").text(message);
    $("#error-container").hide().removeClass("d-none").fadeIn(300);
    setTimeout(() => {
      $("#error-container").fadeOut(300, () => {
        $("#error-container").addClass("d-none");
      });
    }, 5000);
  }
}
