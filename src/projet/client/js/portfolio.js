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
                    this.portfolioStats.refreshStats(positions)
                });
            }
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