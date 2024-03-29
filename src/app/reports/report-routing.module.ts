import { NgModule } from "@angular/core";
import { Routes, RouterModule } from "@angular/router";
import { MonthwisepurchasesComponent } from "./monthwisepurchases/monthwisepurchases.component";
import { AllsalesComponent } from "./allsales/allsales.component";
import { PrintsaleinvoiceComponent } from "./printsaleinvoice/printsaleinvoice.component";
import { PrintdispatchchallanComponent } from "./printdispatchchallan/printdispatchchallan.component";
import { SearchinvoiceComponent } from "./searchinvoice/searchinvoice.component";
import { SearchdispatchchallanComponent } from "./searchdispatchchallan/searchdispatchchallan.component";
import { SundrydebitorsComponent } from "./sundrydebitors/sundrydebitors.component";
import { SundrycreditorsComponent } from "./sundrycreditors/sundrycreditors.component";
import { BillsandcollectionComponent } from "./billsandcollection/billsandcollection.component";
import { LedgersalesComponent } from "./ledgersales/ledgersales.component";
import { LedgerpurchaseComponent } from "./ledgerpurchase/ledgerpurchase.component";
import { DistrictwisesalesComponent } from "./districtwisesales/districtwisesales.component";
import { StockstatementComponent } from "./stockstatement/stockstatement.component";
import { AlloformsComponent } from "./alloforms/alloforms.component";

const reportRoutes: Routes = [
  {
    path: "reports/monthwisepurchases",
    component: MonthwisepurchasesComponent,
  },
  { path: "reports/allsales", component: AllsalesComponent },
  {
    path: "reports/printsaleinvoice/:invoiceno",
    component: PrintsaleinvoiceComponent,
  },
  { path: "reports/searchinvoice", component: SearchinvoiceComponent },
  {
    path: "reports/printdispatchchallan/:dcno/:orderno",
    component: PrintdispatchchallanComponent,
  },
  { path: "reports/searchdc", component: SearchdispatchchallanComponent },
  { path: "reports/sundrydebt", component: SundrydebitorsComponent },
  { path: "reports/sundrycredit", component: SundrycreditorsComponent },
  { path: "reports/billsncollection", component: BillsandcollectionComponent },
  { path: "reports/salesledger", component: LedgersalesComponent },
  { path: "reports/purchaseledger", component: LedgerpurchaseComponent },
  { path: "reports/districtwisesales", component: DistrictwisesalesComponent },
  { path: "reports/stockstatement", component: StockstatementComponent },
  { path: "reports/alloforms", component: AlloformsComponent },
];

@NgModule({
  imports: [RouterModule.forChild(reportRoutes)],
  exports: [RouterModule],
})
export class ReportRoutingModule {}
