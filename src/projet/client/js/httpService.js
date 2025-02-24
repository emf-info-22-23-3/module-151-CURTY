/**
 * Class permettant de gérer les interactions HTTP avec le serveur et les APIs
 */
class HttpService {
  /**
   * Constructeur de la classe qui initialise les différents URLs des endpoints
   */
  constructor() {
    this.finHubApiKey = "cudscnhr01qiosq11fb0cudscnhr01qiosq11fbg";
    this.newsUrl =
      "https://finnhub.io/api/v1/news?category=general&token=" +
      this.finHubApiKey;
    this.finHubURL = "https://finnhub.io/api/v1/quote?symbol=";
    this.endpoint = "../../projet/server/server.php";
  }
  /**
   * Authentifie un utilisateur avec ses informations de connexion.
   *
   * @param {function} successCallback - Fonction callback appelée en cas de succès.
   * @param {string} email - L'email de l'utilisateur.
   * @param {string} password - Le mot de passe de l'utilisateur.
   */
  authenticateUser(successCallback, email, password) {
    let body = {
      action: "login",
      email: email,
      password: password,
    };
    $.ajax({
      type: "POST",
      url: this.endpoint,
      data: JSON.stringify(body),
      dataType: "JSON",
      contentType: "application/json",
      success: successCallback,
    });
  }
  /**
   * Déconnecte l'utilisateur courant.
   *
   * @param {function} successCallback - Fonction callback appelée en cas de succès.
   */
  logOut(successCallback) {
    let body = { action: "disconnect" };
    $.ajax({
      type: "POST",
      url: this.endpoint,
      data: JSON.stringify(body),
      contentType:"JSON",
      success: successCallback,
    });
  }
  /**
   * Crée un nouveau compte utilisateur.
   *
   * @param {string} name - Le prénom de l'utilisateur.
   * @param {string} familyName - Le nom de famille de l'utilisateur.
   * @param {string} email - L'email de l'utilisateur.
   * @param {string} password - Le mot de passe de l'utilisateur.
   * @param {function} successCallback - Fonction callback appelée en cas de succès.
   */
  createAccount(name, familyName, email, password, successCallback) {
    let body = {
      action: "register",
      name: name,
      familyName: familyName,
      email: email,
      password: password,
    };
    $.ajax({
      type: "POST",
      dataType: "JSON",
      url: this.endpoint,
      data: JSON.stringify(body),
      success: successCallback,
    });
  }
  /**
   * Récupère l'état de l'utilisateur actuel.
   *
   * @param {function} successCallback - Fonction callback appelée en cas de succès.
   */
  getUserState(successCallback) {
    $.ajax({
      type: "GET",
      url: this.endpoint + "?action=userState",
      dataType: "JSON",
      success: successCallback,
    });
  }
  /**
   * Récupère les positions (stocks) de l'utilisateur.
   *
   * @param {function} successCallback - Fonction callback appelée en cas de succès.
   */
  getUserPositions(successCallback) {
    $.ajax({
      type: "GET",
      url: this.endpoint + "?action=getPositions",
      dataType: "JSON",
      success: successCallback,
    });
  }
  /**
   * Récupère les dernières actualités à partir de l'API Finnhub.
   *
   * @param {function} successCallback - Fonction callback appelée en cas de succès.
   */
  getLatestNews(successCallback) {
    $.ajax({
      type: "GET",
      url: this.newsUrl,
      dataType: "JSON",
      success: successCallback,
    });
  }
  /**
   * Récupère le prix d'une action donnée à partir de l'API Finnhub.
   *
   * @param {string} symbol - Le symbole de l'action (ex: 'AAPL' pour Apple).
   * @param {function} successCallback - Fonction callback appelée en cas de succès.
   */
  getStockPrice(symbol, successCallback) {
    $.ajax({
      type: "GET",
      url: this.finHubURL + symbol + "&token=" + this.finHubApiKey,
      dataType: "JSON",
      success: successCallback,
    });
  }
  /**
   * Vend des actions pour l'utilisateur.
   *
   * @param {string} symbol - Le symbole de l'action (ex: 'AAPL' pour Apple).
   * @param {number} avgSellPrice - Le prix moyen de vente des actions.
   * @param {number} soldQuantity - La quantité d'actions vendues.
   * @param {function} successCallback - Fonction callback appelée en cas de succès.
   */
  sellStock(symbol, avgSellPrice, soldQuantity, successCallback) {
    let body = {
      action: "sellStock",
      avgSellPrice: avgSellPrice,
      soldQuantity: soldQuantity,
      asset: symbol,
    };
    $.ajax({
      type: "POST",
      url: this.endpoint,
      data: JSON.stringify(body),
      dataType: "JSON",
      success: successCallback,
    });
  }
  /**
   * Achète des actions pour l'utilisateur.
   *
   * @param {string} symbol - Le symbole de l'action (ex: 'AAPL' pour Apple).
   * @param {number} avgBuyPrice - Le prix moyen d'achat des actions.
   * @param {number} boughtQuantity - La quantité d'actions achetées.
   * @param {function} successCallback - Fonction callback appelée en cas de succès.
   */
  buyStock(symbol, avgBuyPrice, boughtQuantity, successCallback) {
    let body = {
      action: "addStock",
      avgBuyPrice: avgBuyPrice,
      boughtQuantity: boughtQuantity,
      asset: symbol,
    };
    $.ajax({
      type: "POST",
      url: this.endpoint,
      data: JSON.stringify(body),
      dataType: "JSON",
      success: successCallback,
    });
  }
  /**
   * Configure la gestion des erreurs pour les requêtes AJAX.
   * Cette méthode centralise la gestion des erreurs pour toutes les requêtes AJAX.
   *
   * @param {function} callback - La fonction de gestion des erreurs, appelée avec le message d'erreur.
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
