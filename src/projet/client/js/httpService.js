class HttpService {
  constructor() {
    this.newsUrl =
      "https://finnhub.io/api/v1/news?category=general&token=cudscnhr01qiosq11fb0cudscnhr01qiosq11fbg";
    this.endpoint = "../../projet/server/server.php";
  }

  authenticateUser(successCallback, email, password) {
    let body = { "action": "login", "email": email, "password": password };
    $.ajax({
      type: "POST",
      dataType: "JSON",
      url: this.endpoint,
      data: JSON.stringify(body),
      contentType: "application/json",
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
    }
    $.ajax({
      type: "POST",
      dataType: "JSON",
      url: this.endpoint,
      data: JSON.stringify(body),
      contentType: "application/json",
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
  /**
   * Méthode permettant de centraliser la gestion d'erreur
   * @param {function} callback
   */
  setErrorHandling(callback) {
    $.ajaxSetup({
      error: function (xhr, exception) {
        callback(`[${xhr.status}] ` + JSON.parse(xhr.responseText).message);
      },
    });
  }
}
