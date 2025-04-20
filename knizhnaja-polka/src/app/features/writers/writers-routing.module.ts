import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { WritersListComponent } from './writers-list/writers-list.component';
const routes: Routes = [
  { path: '', component: WritersListComponent }
];
@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class WritersRoutingModule { }
