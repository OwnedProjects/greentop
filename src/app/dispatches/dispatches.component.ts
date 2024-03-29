import { Component, OnInit } from "@angular/core";
import { RESTService } from "../rest.service";
import * as moment from "moment";
import { IntervalService } from "../interval.service";
import { GlobalService } from "../global.service";
import { SessionService } from '../session.service';

@Component({
  selector: "app-dispatches",
  templateUrl: "./dispatches.component.html",
  styleUrls: ["./dispatches.component.css"]
})
export class DispatchesComponent implements OnInit {
  selectedorder: any = null;
  orderdetails: any = null;
  openorders: any = null;
  allconsignees: any = null;
  dispatchdate: any = null;
  dcno: any = null;
  vehicalno: any = null;
  alltrucks: any = null;
  allbatches: any = null;
  selbatch: any = null;
  selqty: any = null;
  selbatchquantity: any = null;
  qtyremerror: any = false;
  successmsg: any = false;
  addedbatches: any = new Array();
  disabledispbtn: boolean = false;
  packing: string = null;
  noofbags: string = null;
  nochallan: boolean = false;
  deliveryremarks: string = null;

  constructor(
    private _rest: RESTService,
    private _interval: IntervalService,
    private _global: GlobalService,
    private _session: SessionService
  ) { }

  ngOnInit() {
    this.initialize();
  }

  initialize() {
    let now = moment().format("DD-MM-YYYY");
    //this.dispatchdate = now;
    this.getOpenOrders();
    this.getTransportTrucks();
    this.getAllProductionBatches();
  }

  getTransportTrucks() {
    let transtruck = new Array();
    this.alltrucks = null;
    let promise = new Promise((resolve, reject) => {
      this._rest
        .getData("transport.php", "getTransportTrucks", null)
        .subscribe(Response => {
          if (Response) {
            for (let i in Response["data"]) {
              let obj = {
                lorryno: Response["data"][i].lorryno
              }
              transtruck.push(obj);
            }
          }

          this._rest.getData("transport.php", "getDispatchTrucks", null)
            .subscribe(Response => {
              if (Response) {
                for (let j in Response["data"]) {
                  let obj = {
                    lorryno: Response["data"][j].vehicalno
                  }
                  transtruck.push(obj);
                }
              }

              this._rest.getData("transport.php", "getPurchaseTrucks", null)
                .subscribe(Response => {
                  if (Response) {
                    for (let k in Response["data"]) {
                      let obj = {
                        lorryno: Response["data"][k].vehicalno
                      }
                      transtruck.push(obj);
                    }
                  }

                  resolve(transtruck);
                });
            });
        });
    });

    promise.then((RespTruck: any[]) => {
      if (RespTruck) {
        for (let i = 0; i < RespTruck.length; i++) {
          for (let j = i + 1; j < RespTruck.length; j++) {
            if (RespTruck[i].lorryno.toLowerCase() === RespTruck[j].lorryno.toLowerCase()) {
              RespTruck.splice(j, 1);
              j--;
            }
          }
        }
      }
      this.alltrucks = RespTruck;
    });
  }

  getOpenOrders() {
    this.openorders = null;
    this._rest
      .getData("order.php", "getOpenOrders", null)
      .subscribe(Response => {
        //console.log(Response);
        if (Response) {
          this.openorders = Response["data"];
        }
      });
  }

  selectOrder() {
    for (let i in this.openorders) {
      if (this.selectedorder == this.openorders[i].orderid) {
        this.orderdetails = this.openorders[i];
        break;
      }
    }
  }

  getOrderConsignees() {
    this.allconsignees = null;
    let urldata = "orderid=" + this.orderdetails.orderid;
    this._rest
      .getData("order.php", "getOrderConsignees", urldata)
      .subscribe(Response => {
        console.log(Response);
        if (Response) {
          this.allconsignees = Response["data"];
        }
      });
  }

  getAllProductionBatches() {
    this.allbatches = null;
    this._rest
      .getData("production.php", "getAllProductionBatches", null)
      .subscribe(Response => {
        if (Response) {
          this.allbatches = Response["data"];
        }
      });
  }

  getAllQuantities() {
    for (let i in this.allbatches) {
      if (this.selbatch.split("$")[0] == this.allbatches[i].batchmastid) {
        this.selbatchquantity = this.allbatches[i].qtyremained;
        break;
      }
    }
  }

  addBatch() {
    //console.log(this.selbatch, this.selqty);
    if (parseFloat(this.selqty) > parseFloat(this.selbatchquantity)) {
      this.qtyremerror =
        "Quantity remained cannot be greater than Quantity Entered.";
      return;
    }
    //console.log(this.selqty, this.orderdetails.quantity);
    if (parseFloat(this.selqty) > parseFloat(this.orderdetails.quantity)) {
      this.qtyremerror =
        "Quantity Entered cannot be greater than Order Quantity";
      return;
    }
    let qtyrem = parseFloat(this.selqty);
    let tmpqty = 0;
    for (let i in this.addedbatches) {
      tmpqty += parseFloat(this.addedbatches[i].selqty);
    }
    let qty = parseFloat(this.orderdetails.quantity) - (tmpqty + qtyrem);
    //console.log(tmpqty, qtyrem, qty);
    if (qty < 0) {
      this.qtyremerror =
        "Total Quantity Entered cannot be greater than Order Quantity";
      return;
    }

    this.qtyremerror = false;
    let tmpbatch: any = {
      batchmastid: this.selbatch.split("$")[0],
      batchid: this.selbatch.split("$")[1],
      selqty: this.selqty,
      qtyrem: 0
    };
    let qtyremained = 0;
    for (let i in this.allbatches) {
      if (this.selbatch.split("$")[0] == this.allbatches[i].batchmastid) {
        qtyremained =
          parseFloat(this.allbatches[i].qtyremained) - parseFloat(this.selqty);
        this.allbatches.splice(i, 1);
        break;
      }
    }
    tmpbatch.qtyrem = qtyremained;
    this.addedbatches.push(tmpbatch);
    this.selbatchquantity = null;
    this.selbatch = null;
    this.selqty = null;
    setTimeout(function () {
      window.scrollTo(0, document.body.scrollHeight);
    }, 100);
  }

  dispatchOrder() {
    this.disabledispbtn = true;
    let totalqty = 0;
    for (const i in this.addedbatches) {
      totalqty += parseFloat(this.addedbatches[i].selqty);
    }
    if (totalqty != this.orderdetails.quantity) {
      alert("Order quantity and total batches quantities does not match.");
      return;
    }

    let dispatchdate = moment(this.dispatchdate, "DD-MM-YYYY").format("MM-DD-YYYY");
    
    let dispatchObj = {
      todaydt: new Date().getTime(),
      orderid: this.orderdetails.orderid,
      prodid: this.orderdetails.prodid,
      quantity: this.orderdetails.quantity,
      dispdate: new Date(dispatchdate).getTime(),
      dcno: this.dcno,
      vehicalno: this.vehicalno,
      packing: this.packing,
      noofbags: this.noofbags,
      deliveryremarks: this.deliveryremarks,
      nochallan: this.nochallan,
      addedbatches: this.addedbatches,
      remarks: "Sales / " + this.orderdetails.name + " / Order No: " + this.orderdetails.orderno
    };

    let finanyr = this._global.getCurrentFinancialYear();

    if (sessionStorage.getItem("userkey")) {
      this._session.getData("userkey").then(Response => {
        if (Response[0].sessiontime == finanyr.fromdt) {
          //When the date is 1st April of every year.
          /**
           * Before Purchase check if there is any opening balance available for current financial year.
           */
          let geturl = "prodid=" + this.orderdetails.prodid + "&fromdt=" + finanyr.fromdt + "&todt=" + finanyr.todt;
          this._rest
            .getData("stock.php", "checkRawMatOpenStockForCrntFinanYear", geturl)
            .subscribe(Resp => {
              if (!Resp) {
                //There is no opening bal for current year, so get latest stock for raw material
                let newgeturl = "prodid=" + this.orderdetails.prodid;
                this._rest
                  .getData("stock.php", "getProductStock", newgeturl)
                  .subscribe(RespStk => {
                    //console.log(RespStk);
                    if (RespStk) {
                      let postobj = {
                        stockid: RespStk["data"].stockid,
                        quantity: RespStk["data"].quantity,
                        stkdt: new Date().getTime(),
                        openbaldt: new Date().getTime()
                      };
                      this._rest
                        .postData("stock.php", "insertOpeningStock", postobj, null)
                        .subscribe(RespOpenBal => {

                          //console.log(this.orderdetails,dispatchObj);
                          this._rest
                            .postData("dispatch.php", "dispatchOrder", dispatchObj, null)
                            .subscribe(Response => {
                              //console.log(Response);
                              if (Response) {
                                window.scrollTo(0, 0);
                                this.successmsg = true;
                                this._interval.settimer(null).then(resp => {
                                  this.resetForm();
                                  this.disabledispbtn = false;
                                  this.initialize();
                                });
                              } else {
                                alert("Dispatch Failed, Please try again later.");
                              }
                            }, err => {
                              alert("Dispatch Failed, Please try again later.");
                            });
                        });
                    }
                  });
              }
              else {
                this._rest
                  .postData("dispatch.php", "dispatchOrder", dispatchObj, null)
                  .subscribe(Response => {
                    //console.log(Response);
                    if (Response) {
                      window.scrollTo(0, 0);
                      this.successmsg = true;
                      this._interval.settimer(null).then(resp => {
                        this.resetForm();
                        this.initialize();
                      });
                    } else {
                      alert("Dispatch Failed, Please try again later.");
                    }
                  }, err => {
                    alert("Dispatch Failed, Please try again later.");
                  });
              }
            });
        }
        else {
          //console.log(this.orderdetails,dispatchObj);
          this._rest
            .postData("dispatch.php", "dispatchOrder", dispatchObj, null)
            .subscribe(Response => {
              //console.log(Response);
              if (Response) {
                window.scrollTo(0, 0);
                this.successmsg = true;
                this._interval.settimer(null).then(resp => {
                  this.resetForm();
                  this.initialize();
                });
              } else {
                alert("Dispatch Failed, Please try again later.");
              }
            }, err => {
              alert("Dispatch Failed, Please try again later.");
            });
        }
      })
    }

  }

  resetForm() {
    this.selectedorder = null;
    this.orderdetails = null;
    this.dispatchdate = null;
    this.dcno = null;
    this.vehicalno = null;
    this.selbatch = null;
    this.selqty = null;
    this.selbatchquantity = null;
    this.qtyremerror = false;
    this.successmsg = false;
    this.noofbags = null;
    this.nochallan = false;
    this.packing = null;
    this.deliveryremarks = null;
    this.addedbatches = new Array();
  }

  autoFillDate() {
    if (!this.dispatchdate) return;

    this.dispatchdate = this._global.getAutofillFormattedDt(this.dispatchdate);
  }
}
