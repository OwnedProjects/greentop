import { Component, OnInit } from "@angular/core";
import { GlobalService } from "src/app/global.service";
import { RESTService } from "src/app/rest.service";
import { CONSTANTS } from "src/app/app.constants";

@Component({
  selector: "app-cashaccledger",
  templateUrl: "./cashaccledger.component.html",
  styleUrls: ["./cashaccledger.component.css"],
})
export class CashaccledgerComponent implements OnInit {
  finanyr: any = null;
  ledgerhist: any = null;
  cashopenbal: any = null;
  cashexpenditures: any = null;
  cashacctrans: any = null;
  cashsalesreceipts: any = null;
  custmakepays: any = null;
  cashpays: any = null;
  supprecpays: any = null;
  totalFinanyrs: any = null;
  totaldeposit: number = 0;
  totalpayments: number = 0;
  annualMonthWiseData: any = null;

  constructor(private _global: GlobalService, private _rest: RESTService) {}

  ngOnInit() {
    this.finanyr = this._global.getCurrentFinancialYear();
    this.getAllFinancialYears();
    this.initializeData();
  }

  initializeData() {
    this.getFinanYrAccOpeningBalance()
      .then((cashaccbal) => {
        this.getExpendituresFromTo()
          .then((cashexp) => {
            this.getCashAccountExpenditure()
              .then((cashacc) => {
                this.getAllCustMakePayments()
                  .then((custmakepays) => {
                    this.getAllReceiveSupplierPayments()
                      .then((supprecpay) => {
                        // this.filterData();
                        this.getAllCashPaymentsToSuppliers()
                          .then((cashpays) => {
                            // this.filterData();
                            this.getAllCashReceiptsFromCustomers()
                              .then((cashrecepts) => {
                                this.filterData();
                              })
                              .catch((err) => {
                                alert(
                                  "Cannot get CASH Payments from to Customers"
                                );
                              });
                          })
                          .catch((err) => {
                            alert("Cannot get CASH Payments made to suppliers");
                          });
                      })
                      .catch((err) => {
                        alert("Cannot get Supplier Receipt Payments");
                      });
                  })
                  .catch((err) => {
                    alert("Cannot get Customer Make Payments");
                  });
              })
              .catch((error) => {
                alert(
                  "Cannot get Cash Account Transactions, please try again later."
                );
              });
          })
          .catch((err) => {
            alert("Cannot find cash expenditures, kindly try again.");
          });
      })
      .catch((err) => {
        alert("Cannot fetch cash account balance, kindly try again.");
      });
  }

  getAllFinancialYears() {
    this._rest.getData("sales_payments.php", "getAllFinancialYears").subscribe(
      (Response: any) => {
        // console.log(Response.data);
        this.totalFinanyrs = Response ? Response.data : null;
        for (let index in this.totalFinanyrs) {
          this.totalFinanyrs[index].finanyr =
            this._global.getSpecificFinancialYear(
              parseInt(this.totalFinanyrs[index].finanyr)
            );
        }
        // console.log(this.totalFinanyrs);
      },
      (err) => {
        console.log(err);
      }
    );
  }

  changeFinancialyrs(finanyrs) {
    // console.log("change finanyrs", finanyrs);
    this.finanyr = this._global.getSpecificFinancialYear(
      parseInt(finanyrs.fromdt)
    );
    // console.log(this.finanyr);
    this.initializeData();
  }

  // Deposit - Cash Account Opening balance
  getFinanYrAccOpeningBalance() {
    const _this = this;
    const promise = new Promise((resolve, reject) => {
      const urldata =
        "fromdt=" + _this.finanyr.fromdt + "&todt=" + _this.finanyr.todt;
      _this._rest
        .getData("accounts.php", "getFinanYrAccOpeningBalance", urldata)
        .subscribe(
          (Response) => {
            _this.cashopenbal =
              Response && Response["data"] ? Response["data"][1] : 0;
            resolve(_this.cashopenbal);
          },
          (err) => {
            reject(err);
          }
        );
    });
    return promise;
  }

  // Payments -  Get Expenditures for Accounts
  getExpendituresFromTo() {
    const _this = this;
    const promise = new Promise((resolve, reject) => {
      const urldata =
        "fromdt=" + _this.finanyr.fromdt + "&todt=" + _this.finanyr.todt;
      _this._rest
        .getData("expenditure.php", "getExpendituresFromTo", urldata)
        .subscribe(
          (Response) => {
            _this.cashexpenditures =
              Response && Response["data"] ? Response["data"] : null;
            _this.cashexpenditures =
              _this.cashexpenditures && _this.cashexpenditures.length > 0
                ? _this.cashexpenditures.filter((res) => res.exptype === "1")
                : null;
            resolve(_this.cashexpenditures);
          },
          (err) => {
            reject(err);
          }
        );
    });
    return promise;
  }

  // Deposit - Get Cash Accounts expenses made from bank expense type
  getCashAccountExpenditure() {
    const _this = this;
    const promise = new Promise((resolve, reject) => {
      const urldata =
        "fromdt=" + _this.finanyr.fromdt + "&todt=" + _this.finanyr.todt;
      _this._rest
        .getData("accounts.php", "getCashAccountExpenditure", urldata)
        .subscribe(
          (Response) => {
            _this.cashacctrans =
              Response && Response["data"] ? Response["data"] : null;
            resolve(_this.cashacctrans);
          },
          (err) => {
            reject(err);
          }
        );
    });
    return promise;
  }

  // Payments - Get Customer Make Payments
  getAllCustMakePayments() {
    const _this = this;
    const promise = new Promise((resolve, reject) => {
      const urldata =
        "fromdt=" + _this.finanyr.fromdt + "&todt=" + _this.finanyr.todt;
      _this._rest
        .getData("accounts.php", "getAllCustMakePayments", urldata)
        .subscribe(
          (Response) => {
            _this.custmakepays =
              Response && Response["data"] ? Response["data"] : null;
            _this.custmakepays = _this.custmakepays
              ? _this.custmakepays.filter((res) => {
                  return res.paymode === CONSTANTS.CASH;
                })
              : null;
            resolve(_this.custmakepays);
          },
          (err) => {
            reject(err);
          }
        );
    });
    return promise;
  }

  // Payments - Get Cash Payments made to Suppliers
  getAllCashPaymentsToSuppliers() {
    const _this = this;
    const promise = new Promise((resolve, reject) => {
      const urldata =
        "fromdt=" + _this.finanyr.fromdt + "&todt=" + _this.finanyr.todt;
      _this._rest
        .getData(
          "purchase_payments.php",
          "getAllCashPaymentsToSuppliers",
          urldata
        )
        .subscribe(
          (Response) => {
            _this.cashpays =
              Response && Response["data"] ? Response["data"] : null;
            /* _this.cashpays = _this.cashpays
              ? _this.cashpays.filter((res) => {
                  return res.paymode === CONSTANTS.CASH;
                })
              : null; */
            resolve(_this.cashpays);
          },
          (err) => {
            reject(err);
          }
        );
    });
    return promise;
  }
  // Payments - Get Cash Payments from Customers
  getAllCashReceiptsFromCustomers() {
    const _this = this;
    const promise = new Promise((resolve, reject) => {
      const urldata =
        "fromdt=" + _this.finanyr.fromdt + "&todt=" + _this.finanyr.todt;
      _this._rest
        .getData("sales_payments.php", "getAllCashOrderReceipts", urldata)
        .subscribe(
          (Response) => {
            _this.cashsalesreceipts =
              Response && Response["data"] ? Response["data"] : null;
            resolve(_this.cashsalesreceipts);
          },
          (err) => {
            reject(err);
          }
        );
    });
    return promise;
  }

  // Receipts - Get Receive Payments From Supplier
  getAllReceiveSupplierPayments() {
    const _this = this;
    const promise = new Promise((resolve, reject) => {
      const urldata =
        "fromdt=" + _this.finanyr.fromdt + "&todt=" + _this.finanyr.todt;
      _this._rest
        .getData("accounts.php", "getAllReceiveSupplierPayments", urldata)
        .subscribe(
          (Response) => {
            _this.supprecpays =
              Response && Response["data"] ? Response["data"] : null;
            _this.supprecpays = _this.supprecpays
              ? _this.supprecpays.filter((res) => {
                  return res.paymode === CONSTANTS.CASH;
                })
              : null;
            resolve(_this.supprecpays);
          },
          (err) => {
            reject(err);
          }
        );
    });
    return promise;
  }

  filterData() {
    let tmparr = [];

    this.totaldeposit = 0;
    this.totalpayments = 0;
    //Deposit - Opening Balance
    let tmpobj = {
      id: tmparr.length,
      dated: this.cashopenbal.yeardt,
      particular: `Opening balance`,
      deposit: this.cashopenbal.amount,
      payments: 0,
      balance: this.cashopenbal.amount,
    };
    tmparr.push(tmpobj);

    //Deposit - Cash Account Deposits
    if (this.cashacctrans && this.cashacctrans.length > 0) {
      for (let i = 0; i < this.cashacctrans.length; i++) {
        let tmpobj = {
          id: tmparr.length,
          dated: this.cashacctrans[i].expdate,
          particular: this.cashacctrans[i].particulars,
          deposit: this.cashacctrans[i].amount,
          payments: 0,
          balance: 0,
        };
        tmparr.push(tmpobj);
      }
    }

    //Payments - Expenditure Payments
    if (this.cashexpenditures && this.cashexpenditures.length > 0) {
      for (let i = 0; i < this.cashexpenditures.length; i++) {
        const tmpparticular =
          this.cashexpenditures[i].personalaccnm != CONSTANTS.NA
            ? `${this.cashexpenditures[i].particulars} - <span class="text-primary">${this.cashexpenditures[i].personalaccnm}</span>`
            : `${this.cashexpenditures[i].particulars}`;
        let tmpobj = {
          id: tmparr.length,
          dated: this.cashexpenditures[i].expdate,
          particular: tmpparticular,
          deposit: 0,
          payments: this.cashexpenditures[i].amount,
          balance: 0,
        };
        tmparr.push(tmpobj);
      }
    }

    //Payments - Customer Made payments
    if (this.custmakepays && this.custmakepays.length > 0) {
      for (let i = 0; i < this.custmakepays.length; i++) {
        let tmpobj = {
          id: tmparr.length,
          dated: this.custmakepays[i].paydate,
          particular: `${this.custmakepays[i].particulars} - ${this.custmakepays[i].name}`,
          deposit: 0,
          payments: this.custmakepays[i].amountpaid,
          balance: 0,
        };
        tmparr.push(tmpobj);
      }
    }

    //Payments - Purchase CASH payments
    if (this.cashpays && this.cashpays.length > 0) {
      for (let i = 0; i < this.cashpays.length; i++) {
        let tmpobj = {
          id: tmparr.length,
          dated: this.cashpays[i].paydate,
          particular: `${this.cashpays[i].particulars} Purchase Account - <span class="text-primary">${this.cashpays[i].name}<span>`,
          deposit: 0,
          payments: this.cashpays[i].amount,
          balance: 0,
        };
        tmparr.push(tmpobj);
      }
    }

    //Payments - Receipt Customer CASH payments
    if (this.cashsalesreceipts && this.cashsalesreceipts.length > 0) {
      for (let i = 0; i < this.cashsalesreceipts.length; i++) {
        let tmpobj = {
          id: tmparr.length,
          dated: this.cashsalesreceipts[i].paydate,
          particular: `${this.cashsalesreceipts[i].particulars} - <span class="text-primary">${this.cashsalesreceipts[i].name}<span>`,
          deposit: this.cashsalesreceipts[i].amount,
          payments: 0,
          balance: 0,
        };
        tmparr.push(tmpobj);
      }
    }

    //Receipts - Supplier Receipt payments
    if (this.supprecpays && this.supprecpays.length > 0) {
      for (let i = 0; i < this.supprecpays.length; i++) {
        let tmpobj = {
          id: tmparr.length,
          dated: this.supprecpays[i].paydate,
          particular: `${this.supprecpays[i].particulars} - ${this.supprecpays[i].name}`,
          deposit: this.supprecpays[i].amountpaid,
          payments: 0,
          balance: 0,
        };
        tmparr.push(tmpobj);
      }
    }

    tmparr.sort(this._global.sortArr("dated"));
    this.ledgerhist = tmparr;

    let tmpbalance = parseFloat(this.ledgerhist[0].deposit);
    for (let k = 1; k < this.ledgerhist.length; k++) {
      this.ledgerhist[k].balance =
        tmpbalance +
        parseFloat(this.ledgerhist[k].deposit) -
        parseFloat(this.ledgerhist[k].payments);
      tmpbalance = parseFloat(this.ledgerhist[k].balance);
    }

    for (const j in this.ledgerhist) {
      this.totaldeposit += parseFloat(this.ledgerhist[j].deposit);
      this.totalpayments += parseFloat(this.ledgerhist[j].payments);
    }

    // console.log(this.ledgerhist);
    if (this.ledgerhist && this.ledgerhist.length) {
      // Updating the lastest balance back to DB
      // this.ledgerhist[this.ledgerhist.length - 1].balance
      this.updateLatestCashBalanceToDB(
        this.ledgerhist[this.ledgerhist.length - 1].balance
      );
    }

    // CALCULATE MONTHWAISE TOTALS FOR RECEIPTS AND PAYMENTS
    this.calculateMonthWiseTotals();
  }

  calculateMonthWiseTotals() {
    const ledhist = JSON.parse(JSON.stringify(this.ledgerhist)); //Creating a copy so as not to change the original data
    // console.log(ledhist);
    let annualdata = new Array();
    let selmonth = new Date(parseInt(ledhist[0].dated));
    let monthMM = new Date(parseInt(ledhist[0].dated));
    const monthnm = `${monthMM.toLocaleString("default", {
      month: "long",
    })} , ${monthMM.getFullYear()}`;
    annualdata.push({
      depositAmt: 0,
      paymentAmt: 0,
      monthName: monthnm,
    });

    // Starting from 1 as leaving the first entry
    for (let i = 1; i < ledhist.length; i++) {
      let monthMM = new Date(parseInt(ledhist[i].dated));
      const monthnm = `${monthMM.toLocaleString("default", {
        month: "long",
      })} , ${monthMM.getFullYear()}`;
      if (selmonth.getMonth() === monthMM.getMonth()) {
        //console.log(ledhist[i]);
        annualdata.find((o, index) => {
          if (o.monthName === monthnm) {
            //console.log(parseFloat(ledhist[i].deposit));
            const anndepamt =
              annualdata[index].depositAmt + parseFloat(ledhist[i].deposit);
            const annpayamt =
              annualdata[index].paymentAmt + parseFloat(ledhist[i].payments);
            annualdata[index] = {
              depositAmt: anndepamt,
              paymentAmt: annpayamt,
              monthName: monthnm,
            };
            return true;
          }
        });
      } else {
        selmonth = new Date(parseInt(ledhist[i].dated));
        let monthMM = new Date(parseInt(ledhist[i].dated));
        const monthnm = `${monthMM.toLocaleString("default", {
          month: "long",
        })} , ${monthMM.getFullYear()}`;
        annualdata.push({
          depositAmt: parseFloat(ledhist[i].deposit),
          paymentAmt: parseFloat(ledhist[i].payments),
          monthName: monthnm,
        });
      }
      // console.log(monthMM, monthnm);
      this.annualMonthWiseData = annualdata;
      // console.log(annualdata);
    }
  }

  updateLatestCashBalanceToDB(balance) {
    const curryrfromdt = this._global.getCurrentFinancialYear().fromdt;

    if (curryrfromdt !== this.finanyr.fromdt) {
      return;
    }
    const _this = this;
    const urldata = {
      acctype: "CASH",
      balanceamt: parseFloat(balance),
      curryr: _this.finanyr.fromdt,
    };
    // console.log(urldata);
    _this._rest
      .postData("accounts.php", "updateLatestBalanceToDB", urldata)
      .subscribe(
        (Response) => {
          console.log("Balance updated");
        },
        (err) => {
          console.log(err);
        }
      );
  }
}
