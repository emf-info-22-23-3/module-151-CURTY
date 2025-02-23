$(document).ready(function () {
  window.loginCtrl = new Login();
});
class Login {
  constructor() {
    this.httpServ = new HttpService();
    this.httpServ.setErrorHandling((message) => this.displayError(message));
    this.setListeners();
    this.isLogin = true;
  }
  setListeners() {
    $("#btnSubmit").click((e) => {
      this.handleUserConnexionRequest();
    });
    $("#otherOption").on("click", "#register", (e) => {
      e.preventDefault();
      if (this.isLogin) {
        this.isLogin = false;
        $("#name-cont").removeClass("d-none");
        $("#famName-cont").removeClass("d-none");
        $("#password-conf-cont").removeClass("d-none");
        $("#otherOption").html(`<p >Vous avez déja un compte? <a href="#" id="register" class="text-decoration-none text-rolex fw-medium">Se connecter</a></p>`);
      } else {
        this.isLogin = true;
        $("#name-cont").addClass("d-none");
        $("#famName-cont").addClass("d-none");
        $("#password-conf-cont").addClass("d-none");
        $("#otherOption").html(`<p >Vous n'avez pas de compte? <a href="#" id="register" class="text-decoration-none text-rolex fw-medium">S'inscrire</a></p>`);
      }
    });
  }
  handleUserConnexionRequest() {
    let email = $("#email").val();
    let password = $("#password").val();
    if (this.isLogin) {
      this.httpServ.authenticateUser(() => {
        window.location.href = "../client/portfolio.html";
      }, email, password);
    } else {
      let name = $("#name").val();
      let familyName = $("#familyName").val();
      let email = $("#email").val();
      let passwordConfirmation = $("#password-confirmation").val();
      if (passwordConfirmation === password) {
        this.httpServ.createAccount(name, familyName, email, password, () => { window.location.href = "../client/portfolio.html"; });
      } else {
        this.displayError("Les mots de passe ne correspondent pas");
      }
    }

  }
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