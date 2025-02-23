$(document).ready(function () {
    window.portfolioCtrl = new Portfolio();
});
class Portfolio {
    constructor() {
        this.httpServ = new HttpService();
        this.httpServ.setErrorHandling((message) => this.displayError(message));
        this.portfolioStats = new PortfolioStats(this.httpServ);
        this.getPositions();
        this.setListener();
    }
    setListener() {
        $("#logOut").click((e) => {
            this.httpServ.logOut(() => { window.location.href = "../client/login.html" });
        });
    }
    getPositions() {
        this.httpServ.getUserPositions((positions) => this.portfolioStats.refreshStats(positions));
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