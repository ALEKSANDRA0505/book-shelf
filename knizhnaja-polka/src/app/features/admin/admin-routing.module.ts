import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AdminPanelComponent } from './admin-panel/admin-panel.component';
import { AdminAuthGuard } from '../../core/guards/admin-auth.guard';
const routes: Routes = [
  {
    path: '',
    component: AdminPanelComponent,
    canActivate: [AdminAuthGuard]
  }
];
@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AdminRoutingModule { } 