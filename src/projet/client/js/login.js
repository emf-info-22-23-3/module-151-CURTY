$(document).ready(function () {
  window.loginCtrl = new Login();
});
/**
 * Classe gérant la logique de connexion et d'inscription de l'utilisateur.
 */
class Login {
  /**
   * Constructeur de la classe Login.
   */
  constructor() {
    this.httpServ = new HttpService();
    this.httpServ.setErrorHandling((message) => this.displayError(message));
    this.setListeners();
    this.isLogin = true;
  }
  /**
   * Configure les écouteurs d'événements pour les interactions de l'utilisateur.
   * - Lors de la soumission du formulaire : envoie la requête de connexion ou d'inscription.
   * - Change l'état actuelle du formulaire (connexion/inscription).
   */
  setListeners() {
    $("#btnSubmit").click((e) => {
      e.preventDefault();
      this.handleUserConnexionRequest();
    });
    $("#otherOption").on("click", "#register", (e) => {
      e.preventDefault();
      if (this.isLogin) {
        this.isLogin = false;
        $("#name-cont").removeClass("d-none");
        $("#famName-cont").removeClass("d-none");
        $("#password-conf-cont").removeClass("d-none");
        $("#btnSubmit").text("S'enregistrer");
        $("#otherOption").html(
          `<p >Vous avez déja un compte? <a href="#" id="register" class="text-decoration-none text-rolex fw-medium">Se connecter</a></p>`
        );
      } else {
        this.isLogin = true;
        $("#name-cont").addClass("d-none");
        $("#famName-cont").addClass("d-none");
        $("#password-conf-cont").addClass("d-none");
        $("#btnSubmit").text("Se connecter");
        $("#otherOption").html(
          `<p >Vous n'avez pas de compte? <a href="#" id="register" class="text-decoration-none text-rolex fw-medium">S'inscrire</a></p>`
        );
      }
    });
  }
  /**
   * Gère la requête de connexion ou d'inscription de l'utilisateur en fonction de l'état de `isLogin`.
   * Si l'utilisateur est en mode "connexion", il tente de se connecter avec les informations fournies.
   * Si l'utilisateur est en mode "inscription", il tente de créer un compte avec les informations fournies.
   */
  handleUserConnexionRequest() {
    let email = $("#email").val();
    let password = $("#password").val();
    if (this.isLogin) {
      if (document.getElementById("form").checkValidity()) {
        this.httpServ.authenticateUser(
          () => {
            window.location.href = "../client/portfolio.html";
          },
          email,
          password
        );
      }
    } else {
      let name = $("#name").val();
      let familyName = $("#familyName").val();
      let email = $("#email").val();
      let passwordConfirmation = $("#password-confirmation").val();
      if (passwordConfirmation === password) {
        this.httpServ.createAccount(name, familyName, email, password, () => {
          window.location.href = "../client/portfolio.html";
        });
      } else {
        this.displayError("Les mots de passe ne correspondent pas");
      }
    }
  }
  /**
   * Affiche un message d'erreur à l'utilisateur.
   *
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
