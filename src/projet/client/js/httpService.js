class HttpService {
  constructor() {
    this.finHubApiKey = "cudscnhr01qiosq11fb0cudscnhr01qiosq11fbg";
    this.newsUrl =
      "https://finnhub.io/api/v1/news?category=general&token=" + this.finHubApiKey;
    this.finHubURL = "https://finnhub.io/api/v1/quote?symbol=";
    this.endpoint = "../../projet/server/server.php";
  }

  authenticateUser(successCallback, email, password) {
    let body = { 
      "action": "login", 
      "email": email, 
      "password": password 
    };
    $.ajax({
      type: "POST",
      url: this.endpoint,
      data: JSON.stringify(body),
      dataType: "JSON",
      contentType: "application/json",
      success: successCallback
    });
  }

  logOut(successCallback) {
    let body = { "action": "disconnect" };
    $.ajax({
      type: "POST",
      dataType: "JSON",
      url: this.endpoint,
      data: JSON.stringify(body),
      success: successCallback
    });
  }
  createAccount(name, familyName, email, password, successCallback) {
    let body = {
      "action": "register",
      "name": name,
      "familyName": familyName,
      "email": email,
      "password": password
    };
    $.ajax({
      type: "POST",
      dataType: "JSON",
      url: this.endpoint,
      data: JSON.stringify(body),
      success: successCallback
    });
  }
  getUserState(successCallback) {
    $.ajax({
      type: "GET",
      url: this.endpoint + "?action=userState",
      dataType: "JSON",
      success: successCallback
    });
  }
  getUserPositions(successCallback) {
    $.ajax({
      type: "GET",
      url: this.endpoint + "?action=getPositions",
      dataType: "JSON",
      success: successCallback
    });
  }
  getLatestNews(successCallback) {
    $.ajax({
      type: "GET",
      url: this.newsUrl,
      dataType: "JSON",
      success: successCallback,
    });
  }
  getStockPrice(symbol, successCallback) {
    $.ajax({
      type: "GET",
      url: this.finHubURL + symbol + "&token=" + this.finHubApiKey,
      dataType: "JSON",
      success: successCallback
    });
  }
  sellStock(symbol, avgSellPrice, soldQuantity, successCallback) {
    let body = {
      "action": "sellStock",
      "avgSellPrice": avgSellPrice,
      "soldQuantity": soldQuantity,
      "asset": symbol
    }
    $.ajax({
      type: "POST",
      url: this.endpoint,
      data: JSON.stringify(body),
      dataType: "JSON",
      success: successCallback
    });
  }
  buyStock(symbol, avgBuyPrice, boughtQuantity, successCallback) {
    let body = {
      "action": "addStock",
      "avgBuyPrice": avgBuyPrice,
      "boughtQuantity": boughtQuantity,
      "asset": symbol
    }
    $.ajax({
      type: "POST",
      url: this.endpoint,
      data: JSON.stringify(body),
      dataType: "JSON",
      success: successCallback
    });
  }
  /**
   * Méthode permettant de centraliser la gestion d'erreur
   * @param {function} callback
   */
  setErrorHandling(callback) {
    $.ajaxSetup({
      error: function (xhr, exception) {
        if (xhr.responseText != "") {
          callback(`[${xhr.status}] ${JSON.parse(xhr.responseText).message}`);
        } else {
          callback(`[${xhr.status}] Erreur non identifiée`);
        }
      },
    });
  }
}
