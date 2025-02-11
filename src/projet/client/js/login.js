$(document).ready(function () {
    window.loginCtrl = new Login();
  });

class Login{
    constructor(){
        this.httpServ = new HttpService();
        this.httpServ.setErrorHandling((message) => this.displayError(message));
        this.setListeners();
    }
    setListeners(){
        $("#btnSubmit").click((e) =>{
            let email = $("#emailInput").val();
            let password = $("#passwordInput").val();
            this.httpServ.authenticateUser(()=>{window.location.replace("/projet/client/myPortfolio.html");;
          },email,password);
        });
    }
    displayError(messsage) {
        let html = `<div class="alert bg-danger d-flex justify-content-between align-items-center" role="alert">
                    <p class="m-1">${messsage}</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
        $("#popUp").html(html);
        $("#popUp").removeClass("d-none");
        $("#popUp").addClass("d-flex");
        setTimeout(() => {
          $("#popUp").fadeOut(function () {
            $("#popUp").addClass("d-none");
            $("#popUp").html("");
          });
        }, 5000);
      }
}