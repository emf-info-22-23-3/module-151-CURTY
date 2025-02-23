class PortfolioStats {
    constructor(httpServ) {
        this.stats = {
            "stats": {
                "totalValue": 0,//
                "plTodayPercentage": 0,//
                "plTodayMoney": 0,//
                "yesterdayValue": 0,//
                "amountOfProfitFirms": 0,//
                "totalPlMoney": 0,//
                "totalPlPercentage": 0,//
                "totalInvested": 0,//
                "amountPostions": 0,//
                "biggestWinnerName": "",//
                "biggestWinnerGainPercentage": 0//
            },
            "positions": [
            ]
        }
        this.textRed = "text-danger";
        this.textGreen = "text-rolex";
        this.httpServ = httpServ;
    }
    refreshStats(positions) {
        this.stats.stats.amountPostions = positions.length;
        for (let i = 0; i < positions.length; i++) {
            let position = positions[i];
            let avgBuyPrice = position.avgBuyPrice;
            let boughtQuantity = position.boughtQuantity;
            let soldQuantity = position.soldQuantity;
            let avgSoldPrice = position.avgSoldPrice;
            let name = position.name;
            this.httpServ.getStockPrice(name, (data) => { this.addPositionToStat(data, avgBuyPrice, boughtQuantity, soldQuantity, avgSoldPrice, name) });
        }
    }
    addPositionToStat(data, avgBuyPrice, boughtQuantity, soldQuantity, avgSoldPrice, name) {
        let currentPrice = data.c;
        let previousClose = data.pc
        let currentHolding = boughtQuantity - soldQuantity;
        if (currentHolding != 0) {
            let holdingValue = currentHolding * currentPrice;
            this.stats.stats.totalValue += holdingValue;

            let totalPl = currentHolding * (currentPrice - avgBuyPrice);
            this.stats.stats.totalPlMoney += totalPl;
            if (totalPl >= 0) {
                this.stats.stats.amountOfProfitFirms++;

            }
            let boughtFor = currentHolding * avgBuyPrice;
            this.stats.stats.totalInvested += boughtFor;

            let totalPlPercentage = -1 * (100 - holdingValue * 100 / boughtFor);
            if (this.stats.stats.biggestWinnerGainPercentage < totalPlPercentage) {
                this.stats.stats.biggestWinnerGainPercentage = totalPlPercentage;
                this.stats.stats.biggestWinnerName = name;
            }
            let yesterdayValue = currentHolding * previousClose;
            this.stats.stats.yesterdayValue += yesterdayValue;

            let yesterdayPl = currentHolding * (previousClose - avgBuyPrice);

            let todayPL = totalPl - yesterdayPl;
            this.stats.stats.plTodayMoney += todayPL;
            let todayPlPercentage = -1 * (100 - holdingValue * 100 / yesterdayValue);
            let position = {
                "symbol": name,
                "shares": currentHolding,
                "entryPrice": avgBuyPrice,
                "currentPrice": currentPrice,
                "marketValue": holdingValue,
                "dailyPl": todayPlPercentage,
                "totalPl": totalPlPercentage
            }
            this.stats.positions.push(position);
            this.stats.stats.plTodayPercentage = -1 * (100 - this.stats.stats.totalValue * 100 / this.stats.stats.yesterdayValue);
            this.stats.stats.totalPlPercentage = -1 * (100 - this.stats.stats.totalValue * 100 / this.stats.stats.totalInvested);
            this.refreshGui(position);
            $("#pfValue").text("$ " + this.formatNumber(this.stats.stats.totalValue));
            $("#todaysPLPercentage").html(`<i class="bi ${(this.stats.stats.plTodayPercentage >= 0) ? 'bi-arrow-up' : 'bi-arrow-down'}"></i>` + this.formatNumber(this.stats.stats.plTodayPercentage) + "% ajourd'hui");
            $("#todaysPLPercentage").addClass(((this.stats.stats.plTodayPercentage >= 0) ? this.textGreen : this.textRed));
            $("#todayPLMoney").text("$ " + this.formatNumber(this.stats.stats.plTodayMoney));
            $("#todayPLMoney").addClass(((this.stats.stats.plTodayMoney >= 0) ? this.textGreen : this.textRed));
            $("#nbrProfitPositions").text(this.stats.stats.amountOfProfitFirms + " positions dans le vert");
            $("#totalPLMoney").text("$ " + this.formatNumber(this.stats.stats.totalPlMoney));
            $("#totalPLMoney").removeClass(this.textGreen + " " + this.textRed).addClass(((this.stats.stats.totalPlMoney >= 0) ? this.textGreen : this.textRed));
            $("#totalPlPercentage").html(`<i class="bi ${(this.stats.stats.totalPlPercentage >= 0) ? 'bi-arrow-up' : 'bi-arrow-down'}"></i>` + this.formatNumber(this.stats.stats.totalPlPercentage) + "% total");
            $("#totalPlPercentage").removeClass(this.textGreen + " " + this.textRed).addClass(((this.stats.stats.totalPlPercentage >= 0) ? this.textGreen : this.textRed));
            $("#totalInvested").text("$ " + this.formatNumber(this.stats.stats.totalInvested));
            $("#biggestPerf").text("Sur la lune " + this.stats.stats.biggestWinnerName + " " + this.formatNumber(this.stats.stats.biggestWinnerGainPercentage) + "%");
        }

    }
    refreshGui(position) {

        let tr = document.createElement("tr");
        let trName = document.createElement("td");
        trName.textContent = position.symbol;
        let trShare = document.createElement("td");
        trShare.textContent = position.shares;
        let trEntryPrice = document.createElement("td");
        trEntryPrice.textContent = "$ " + this.formatNumber(position.entryPrice);
        let trCurrentPrice = document.createElement("td");
        trCurrentPrice.textContent = "$ " + this.formatNumber(position.currentPrice);
        let trMarketValue = document.createElement("td");
        trMarketValue.textContent = "$ " + this.formatNumber(position.marketValue);
        let trDailyPl = document.createElement("td");
        trDailyPl.textContent = this.formatNumber(position.dailyPl) + " %";
        trDailyPl.classList.add((position.dailyPl >= 0) ? this.textGreen : this.textRed);
        let trTotalPl = document.createElement("td");
        trTotalPl.textContent = this.formatNumber(position.totalPl) + " %";
        trTotalPl.classList.add((position.totalPl >= 0) ? this.textGreen : this.textRed);
        let trClosePosition = document.createElement("td");
        let closePositionButton = document.createElement("button");
        closePositionButton.textContent = "Vendre";
        closePositionButton.id = position.symbol;
        closePositionButton.classList.add("btn");
        closePositionButton.classList.add("btn-rolex");
        closePositionButton.classList.add("btn-sm");
        $("#positions").prepend(tr);
        $(tr).append(trName);
        $(tr).append(trShare);
        $(tr).append(trEntryPrice);
        $(tr).append(trCurrentPrice);
        $(tr).append(trMarketValue);
        $(tr).append(trDailyPl);
        $(tr).append(trTotalPl);
        $(tr).append(trClosePosition);
        $(trClosePosition).append(closePositionButton);
        $(closePositionButton).click((e) => {
            let stock = e.target.id;
            let quantity = prompt("Veuillez entrer la quantité vendue");
            let avgSellPrice = prompt("Veuillez entrer le prix moyen de vente: ");
            this.httpServ.sellStock(stock, avgSellPrice, quantity, (json) => { console.log(json) })
        });
    }
    formatNumber(number) {
        let numberFormatOption = {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        };
        return parseFloat(number).toLocaleString(undefined, numberFormatOption);
    }
}