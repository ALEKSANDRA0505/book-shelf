import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { SearchRoutingModule } from './search-routing.module';
import { SearchComponent } from './search.component';
@NgModule({
  declarations: [],
  imports: [
    CommonModule,
    SearchRoutingModule,
    FormsModule,
    SearchComponent
  ]
})
export class SearchModule { }
