$(document).ready(function () {
  window.ctrl = new Ctrl();
  ctrl.loadIndex();
});
class Ctrl {
  constructor() {
    this.marketOptionClass = ".marketOption";
    this.marketOptionClickedClass = "marketOptionSelected";
    this.httpServ = new HttpService();
    this.httpServ.setErrorHandling((message) => this.displayError(message));
  }
  loadIndex() {
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
  }
  getLatestNews() {
    let latestNews = this.httpServ.getLatestNews((news) =>
      this.displayLatestNews(news)
    );
    console.log(latestNews);
  }
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
      source.innerHTML = '<i class="bi bi-newspaper"></i>'+element["source"];

      let title = document.createElement("h5");
      title.textContent = element["headline"]; 
      title.classList.add("card-title");

      let cardText = document.createElement("p");
      cardText.innerHTML = element["summary"];
      cardText.classList.add("card-text");
      cardText.classList.add("mb-auto");

      let publishedTime = document.createElement("p");
      let timestamp = element["datetime"];
      let articleDate  = new Date(timestamp*1000);
      let day = String(articleDate.getDate()).padStart(2, '0');
      let month = String(articleDate.getMonth() + 1).padStart(2, '0');
      let year = articleDate.getFullYear();
      let formattedDate = day+"."+month+"."+year;
      publishedTime.classList.add("card-text");
      publishedTime.classList.add("text-muted");
      publishedTime.innerText = "Published on the "+formattedDate;

      let readFullArticleBtn = document.createElement("a");
      readFullArticleBtn.href = element["url"];
      readFullArticleBtn.innerHTML = "Read full article "+'<i class="bi bi-box-arrow-up-right"></i>';
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
