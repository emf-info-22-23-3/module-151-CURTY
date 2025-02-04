class HttpService{
    constructor(){
        this.newsUrl = "https://finnhub.io/api/v1/news?category=general&token=cudscnhr01qiosq11fb0cudscnhr01qiosq11fbg";
    }

    getLatestNews(successCallback){
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
        let msg;
        if (xhr.status === 0) {
          msg = "No access to the server side requested data !";
        } else if (xhr.status === 404) {
          msg = "Page not found [404] !";
        } else if (xhr.status === 500) {
          msg = "Internal serveur error [500] !";
        } else if (exception === "parsererror") {
          msg = "Error while reading throught the JSON file !";
        } else if (exception === "timeout") {
          msg = "Resquest time out !";
        } else if (exception === "abort") {
          msg = "The request has been stopped !";
        } else {
          msg = "Unknown error : \n" + xhr.responseText;
        }
        msg += ` [${xhr.status}]\nPlease try to refresh the website`;
        callback(msg);
      },
    });
  }
}