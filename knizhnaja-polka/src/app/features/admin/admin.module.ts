import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminRoutingModule } from './admin-routing.module';
import { AdminPanelComponent } from './admin-panel/admin-panel.component';
@NgModule({
  declarations: [
  ],
  imports: [
    CommonModule,
    FormsModule,
    AdminRoutingModule,
    AdminPanelComponent
  ]
})
export class AdminModule { } 