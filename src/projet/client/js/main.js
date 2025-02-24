$(document).ready(function () {
  window.ctrl = new Ctrl();
  ctrl.loadPage();
});
/**
 * Contrôleur principal de la page d'accueil.
 */
class Ctrl {

  /**
   * Constructeur de la classe Ctrl.
   */
  constructor() {
    this.marketOptionClass = ".marketOption";
    this.marketOptionClickedClass = "marketOptionSelected";
    this.httpServ = new HttpService();
    this.httpServ.setErrorHandling((message) => this.displayError(message));
    this.userAuthenticated = false;
  }
   /**
   * Charge  de la page d'accueil, configure les écouteurs d'événements et affiche les graphiques et les nouvelles.
   */
  loadPage() {
    //Hide every chart
    $(".tradingViewChart").css("display", "none");
    //Listener for the market option button
    $("#marketOptionsContainer").on(
      "click",
      this.marketOptionClass,
      (event) => {
        $(this.marketOptionClass).removeClass(this.marketOptionClickedClass);
        $(event.currentTarget).addClass(this.marketOptionClickedClass);
        $(".tradingViewChart").css("display", "none");
        if ($(event.currentTarget).hasClass("indice")) {
          $("#charts")
            .find(".indice")
            .find(".tradingViewChart")
            .css("display", "flex");
        } else if ($(event.currentTarget).hasClass("crypto")) {
          $("#charts")
            .find(".crypto")
            .find(".tradingViewChart")
            .css("display", "flex");
        } else if ($(event.currentTarget).hasClass("stock")) {
          $("#charts")
            .find(".stock")
            .find(".tradingViewChart")
            .css("display", "flex");
        }
      }
    );
    $(this.marketOptionClass).first().click();
    this.getLatestNews();
    this.httpServ.getUserState((state) => this.setUserMenu(state));
  }
  /**
   * Met à jour le menu de l'utilisateur en fonction de son état d'authentification.
   * Affiche "Connexion" ou "Déconnexion" dans le menu et ajoute un lien vers le portfolio si l'utilisateur est authentifié.
   * @param {boolean} userState - L'état d'authentification de l'utilisateur (vrai ou faux).
   */
  setUserMenu(userState) {
    this.userAuthenticated = userState;
    let element = document.createElement("a");
    element.classList.add("nav-link");
    element.id = "logInOut";
    if (this.userAuthenticated) {
      element.href = "#";
      element.text = "Déconnexion";
      let portfolio = document.createElement("li");
      portfolio.classList.add("nav-item");
      let link = document.createElement("a");
      link.classList.add("nav-link");
      link.href = "../client/portfolio.html";
      link.textContent = "Mon portfolio";
      $("#navMenu").append(portfolio);
      $(portfolio).append(link);
    } else {
      element.href = "#";
      element.text = "Connexion";
    }
    $("#connexionMenu").empty();
    $("#connexionMenu").append(element);
    $("#logInOut").click((e) => {
      if (this.userAuthenticated) {
        this.httpServ.logOut(() => { window.location.href = "../client/login.html" });
      } else {
        window.location.href = "../client/login.html";
      }
    });
  }
  /**
   * Récupère les dernières nouvelles via le service HTTP demande leurs affichage.
   */
  getLatestNews() {
    let latestNews = this.httpServ.getLatestNews((news) =>
      this.displayLatestNews(news)
    );
    console.log(latestNews);
  }
  /**
   * Affiche les dernières nouvelles dans des cartes formatées.
   * @param {Array} news - Liste des nouvelles à afficher.
   */
  displayLatestNews(news) {
    news.forEach((element) => {
      let card = document.createElement("div");
      card.style.width = "30rem"
      card.classList.add("card");

      let image = document.createElement("img");
      image.src = element["image"];
      image.alt = element["headline"];
      image.classList.add("card-img-top");

      let cardBody = document.createElement("div");
      cardBody.classList.add("card-body");
      cardBody.classList.add("d-flex");
      cardBody.classList.add("flex-column");

      let source = document.createElement("span");
      source.classList.add("news-source");
      source.classList.add("d-flex");
      source.classList.add("gap-2");
      source.innerHTML = '<i class="bi bi-newspaper"></i>' + element["source"];

      let title = document.createElement("h5");
      title.textContent = element["headline"];
      title.classList.add("card-title");

      let cardText = document.createElement("p");
      cardText.innerHTML = element["summary"];
      cardText.classList.add("card-text");
      cardText.classList.add("mb-auto");

      let publishedTime = document.createElement("p");
      let timestamp = element["datetime"];
      let articleDate = new Date(timestamp * 1000);
      let day = String(articleDate.getDate()).padStart(2, '0');
      let month = String(articleDate.getMonth() + 1).padStart(2, '0');
      let year = articleDate.getFullYear();
      let formattedDate = day + "." + month + "." + year;
      publishedTime.classList.add("card-text");
      publishedTime.classList.add("text-muted");
      publishedTime.innerText = "Published on the " + formattedDate;

      let readFullArticleBtn = document.createElement("a");
      readFullArticleBtn.href = element["url"];
      readFullArticleBtn.innerHTML = "Read full article " + '<i class="bi bi-box-arrow-up-right"></i>';
      readFullArticleBtn.target = "blank:";
      readFullArticleBtn.classList.add("mt-auto");
      readFullArticleBtn.classList.add("d-flex");
      readFullArticleBtn.classList.add("gap-2");


      $("#newsContainer").append(card);
      $(card).append(image);
      $(card).append(cardBody);
      $(cardBody).append(source);
      $(cardBody).append(title);
      $(cardBody).append(cardText);
      $(cardBody).append(publishedTime);
      $(cardBody).append(readFullArticleBtn);
    });
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
