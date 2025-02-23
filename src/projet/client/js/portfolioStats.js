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
            this.refreshGui(position);
            this.stats.stats.plTodayPercentage = -1 * (100 - this.stats.stats.yesterdayValue * 100 / this.stats.stats.totalValue);
            this.stats.stats.totalPlPercentage = -1 * (100 - this.stats.stats.totalValue * 100 / this.stats.stats.totalInvested);
            $("#pfValue").text("$ " + this.formatNumber(this.stats.stats.totalValue));
            $("#todaysPLPercentage").html(`<i class="bi ${(this.stats.stats.plTodayPercentage >= 0) ? 'bi-arrow-up' : 'bi-arrow-down'}"></i>` + this.formatNumber(this.stats.stats.plTodayPercentage) + "% today");
            $("#todaysPLPercentage").addClass(((this.stats.stats.plTodayPercentage >= 0) ? 'text-success' : 'text-danger'));
            $("#todayPLMoney").text("$ " + this.formatNumber(this.stats.stats.plTodayMoney));
            $("#todayPLMoney").addClass(((this.stats.stats.plTodayMoney >= 0) ? 'text-success' : 'text-danger'));
            $("#nbrProfitPositions").text(this.stats.stats.amountOfProfitFirms + " in profit");
            $("#totalPLMoney").text("$ " + this.formatNumber(this.stats.stats.totalPlMoney));
            $("#totalPLMoney").addClass(((this.stats.stats.totalPLMoney >= 0) ? 'text-success' : 'text-danger'));
            $("#totalPlPercentage").html(`<i class="bi ${(this.stats.stats.totalPlPercentage >= 0) ? 'bi-arrow-up' : 'bi-arrow-down'}"></i>` + this.formatNumber(this.stats.stats.totalPlPercentage) + "% overall");
            $("#totalPlPercentage").addClass(((this.stats.stats.totalPlPercentage >= 0) ? 'text-success' : 'text-danger'));
            $("#totalInvested").text("$ " + this.formatNumber(this.stats.stats.totalInvested));
            $("#biggestPerf").text("Biggest perf. " + this.stats.stats.biggestWinnerName + " " + this.formatNumber(this.stats.stats.biggestWinnerGainPercentage) + "%");
            console.log(this.stats.stats)
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
        trDailyPl.classList.add((position.dailyPl >= 0) ? "text-success" : "text-danger");
        let trTotalPl = document.createElement("td");
        trTotalPl.textContent = this.formatNumber(position.totalPl) + " %";
        trTotalPl.classList.add((position.totalPl >= 0) ? "text-success" : "text-danger");
        let trClosePosition = document.createElement("td");
        let closePositionButton = document.createElement("button");
        closePositionButton.textContent = "Close position";
        closePositionButton.id = position.symbol;
        closePositionButton.classList.add("btn");
        closePositionButton.classList.add("btn-rolex");
        closePositionButton.classList.add("btn-sm");

        $("#positions").append(tr);
        $(tr).append(trName);
        $(tr).append(trShare);
        $(tr).append(trEntryPrice);
        $(tr).append(trCurrentPrice);
        $(tr).append(trMarketValue);
        $(tr).append(trDailyPl);
        $(tr).append(trTotalPl);
        $(tr).append(trClosePosition);
        $(trClosePosition).append(closePositionButton);
    }
    formatNumber(number) {
        let numberFormatOption = {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        };
        return parseFloat(number).toLocaleString(undefined, numberFormatOption);
    }
}